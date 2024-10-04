<?php


class RestoreCronHandler {

    use BucketGetter;

    static $uid;
    static $task_id;
    static $date;
    static $type;

    static $restoreFiles = false;
    static $restoreDb = false;

    private $copys;
    private $task;

    private $currentType;
    private $nowBucketId;

    # Инициализация

    /**
     * RestoreCronHandler constructor
     *
     * @param $max_exec_time - максимальное время работы
     * @param false $web - откуда запущен. Если через cmd = true
     * @param array $args - аргументы запуска в случае если $web = true
     */
    public function __construct($max_exec_time, $web = false, $args = []) {
        if (!$web) {
            $args = getopt("", ["type:", "taskid:", "uid:", "date:"]);
        }

        if (isset($args['type'])) {
            self::$type = $args['type'];
        }

        if (isset($args['taskid'])) {
            self::$task_id = $args['taskid'];
        }

        if (isset($args['uid'])) {
            self::$uid = $args['uid'];
        }

        if (isset($args['date'])) {
            self::$date = $args['date'];
        }

        BackupCronHandler::enableLongWaitForQueries($max_exec_time);
    }

    /**
     * Проверить аргументы запуска
     *
     * @return bool
     * @throws BackupException
     */
    public function validateArgs() {

        if (RestoreCronHandler::$task_id == 0) {
            $this->task = SmartBackup::getSmartTask(0);
        } else {
            $this->task = BackupTables::getTaskById(RestoreCronHandler::$task_id);
        }

        if (!$this->task) {
            throw new BackupException("Задания " . self::$task_id . "не существует");
        }

        $this->copys = BackupTables::findCopy(self::$task_id, self::$date, self::$type);

        if (!$this->copys) {
            throw new BackupException("Резервная копия не найдена", ["task_id" => self::$task_id, "uid" => self::$uid, "date" => self::$date, "type" => self::$type]);
        }

        if (self::$type == "db") {
            self::$restoreDb = true;
        }
        if (self::$type == "file") {
            self::$restoreFiles = true;
        }
        if (self::$type == "0") {
            self::$restoreDb = true;
            self::$restoreFiles = true;
        }

        return true;
    }

    # Восстановление

    /**
     * Запустить восстановление из бекапов
     */
    public function restore() {
        foreach ($this->copys as $copy) {
            $this->restoreHandler($copy);
        }

    }

    /**
     * Обработчик восстановления из бекапа
     *
     * @param $copy
     *
     * @return bool
     * @throws BackupException
     * @throws BucketException
     */
    private function restoreHandler($copy) {
        //Определить тип
        $this->currentType = BackupTables::getBackupType($copy['filename']);
        if ($this->currentType == "clients") {
            return true;
        }

        if ($this->currentType == "db") {
            RestoreProgress::progress()->setActionAsExecutable("download_db_archive");
        } else {
            RestoreProgress::progress()->setActionAsExecutable("download_files_archive");
        }

        //Получить копию
        $copy['params'] = json_decode($copy['params'], true);
        $pathToCopyTmp = $this->getCopy($copy);

        //Восстановить
        $restoreResult = $this->restoreCopy($pathToCopyTmp);
    }


    /**
     * Получить копию из хранилища и поместить в tmp
     *
     * @param $copy
     *
     * @return string - путь до файла
     * @throws BucketException
     */
    private function getCopy($copy) {

        $params = $copy['params'];
        $this->filename = $copy['filename'];
        $this->nowBucketId = $copy['bucket_id'];

        switch ($copy['params']['bucketType']) {

            case 0://ftp
                $result = $this->getFileByFTP($copy['params']['data']['server_filename'], $params['data']['params']);
                break;

            case 1://Я.диск
                $result = $this->getFileFromYandexDisk($copy['params']['data']['diskPath'], $params['data']['params']);
                break;

            case 2://DROPBOX
                $result = $this->getFileFromDropbox($params);
                break;

            case 3://S3
                $result = $this->getFileFromS3($params);
                break;

            case 4:
                $result = $this->getFileFromLocal($params);
                break;

            case 5:
                $result = $this->getFileFromGoogleDrive($params);
                break;

            default:
                throw new BucketException("Не известный тип хранилища", ["copy" => $copy]);
                break;
        }


        return $result;
    }


    /**
     * Запустить установку бекапа
     *
     * @param $copyPath
     *
     * @return bool|void
     * @throws BackupException
     */
    private function restoreCopy($copyPath) {

        switch ($this->currentType) {
            case "db":
                $result = $this->restoreDb($copyPath);
                break;
            case "file":
                $result = $this->restoreFiles($copyPath);
                break;
            default:
                throw new BackupException("Не известный тип копии", ["copy" => $copyPath]);
        }


        return $result;
    }

    # Восстановление бд

    /**
     * Восстановить бд
     *
     * @param $file
     *
     * @return bool
     * @throws BackupException
     */
    private function restoreDb($file) {
        if (!self::$restoreDb) {
            return true;
        }

        if (empty($file)) {
            throw new BackupException("Нет файла для восстановления");
        }

        RestoreProgress::progress()->setActionAsExecutable("unpacking_db_archive");

        $time = time();
        @mkdir($this->tmp_dir . $time);

        $zip = new ZipArchive();

        $open = $zip->open($file);

        if ($open !== true) {
            throw new BackupException("Не получается открыть файл $file для восстановления. Код ошибки ZipArchive::$open", ["zip" => $zip]);
        }

        $result = $zip->extractTo($this->tmp_dir . $time);
        if ($result !== true) {
            throw new BackupException("Не получилось распокавать архив $file", ["zip" => $zip]);
        }

        $res = $zip->close();

        $filename = str_replace(".zip", ".sql", $this->filename);
        $filepath = $this->tmp_dir . $time . "/" . $filename;


        $this->installMysqlDump($this->tmp_dir . $time . "/" . $filename);

        unlink($this->tmp_dir . $time . "/" . $filename);

        return true;
    }

    /**
     * Установить дамп
     *
     * @param $dump_path
     *
     * @throws BackupException
     */
    private function installMysqlDump($dump_path) {
        if (!is_file($dump_path)) {
            throw new BackupException("Установка дампа $dump_path не возможна, файл не найден");
        }

        require (ROOT.'/config/config.php');
        /**
         * @var $user
         * @var $password
         * @var $host
         * @var $dbname
         */
        $command = 'mysql --user='.$user.' --password="'.addslashes($password).'" --host='.$host.' '.$dbname.' < '.$dump_path;
        exec($command);
    }

    # Восстановление файлов

    /**
     * Восстановить файлы
     *
     * @param $file
     *
     * @return bool
     * @throws BackupException
     */
    private function restoreFiles($file) {
        if (!self::$restoreFiles) {
            return true;
        }

        if (empty($file)) {
            throw new BackupException("Нет файла для восстановления");
        }

        RestoreProgress::progress()->setActionAsExecutable("unpacking_files_archive");

        $zip = new ZipArchive();
        $open = $zip->open($file);

        if ($open !== true) {
            throw new BackupException("Не получается открыть файл $file для восстановления. Код ошибки ZipArchive::$open", ["zip" => $zip]);
        }

        $result = $zip->extractTo(ROOT);
        $zip->close();

        unlink($file);

        return $result;
    }

}