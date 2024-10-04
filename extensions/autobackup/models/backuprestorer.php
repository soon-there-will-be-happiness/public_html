<?php


class BackupRestorer {

    use BucketGetter;

    private $copy_clones;
    private $nowBucketData;
    private $nowBucketId;

    /**
     * Создать объект восстановления
     *
     * @param $filename - имя файла бекапа
     * @param array $copies_of_this_backup_file - копии этого бекапа в различных хранилищах
     */
    public function __construct($filename, $copies_of_this_backup_file) {
        $this->filename = $filename;
        $this->copy_clones = $copies_of_this_backup_file;
    }

    public function getPathToFile() {
        return $this->backup_file;
    }


    /**
     * Получить файл бекапа из хранилищ
     *
     * @return $this
     * @throws BucketException
     */
    public function getCopy() {
        $result = false;
        foreach ($this->copy_clones as $copy) {

            try {//если не возможно получить файл из этого хранилища - игнорировать ошибку, и идти дальше
                $this->nowBucketId = $copy['bucket_id'];
                switch ($copy['params']['bucketType']) {

                    case 0://ftp
                        $result = $this->getFileByFTP($copy['params']['data']['server_filename'], $copy['params']['data']['params']);
                        break;

                    case 1://Я.диск
                        $result = $this->getFileFromYandexDisk($copy['params']['data']['diskPath'], $copy['params']['data']['params']);
                        break;

                    case 2://DROPBOX
                        $result = $this->getFileFromDropbox($copy['params']);
                        break;

                    case 3://S3
                        $result = $this->getFileFromS3($copy['params']);
                        break;

                    case 4:
                        $result = $this->getFileFromLocal($copy['params']);
                        break;
                    case 5:
                        $result = $this->getFileFromGoogleDrive($copy['params']);
                        break;

                }
            } catch (BucketException $bucketException) {}

            if ($result) {//Если файл получен - то выйти из цикла загрузки
                break;
            }
        }

        if (!$result) {
            throw new BucketException("Не удалось получить файл $this->filename ни из одного хранилища", ['file_copies' => $this->copy_clones]);
        }

        $this->backup_file = $result;

        return $this;
    }


    /**
     * Запустить восстановление из копии
     *
     * @return bool
     * @throws BackupException
     */
    public function restore() {
        if (empty($this->backup_file)) {
            throw new BackupException("Файл бекапа не загружен. Для этого стоит воспользоваться методом getCopy(), перед выполнением этой операции", $this->backup_file);
        }

        switch (BackupTables::getBackupType($this->filename)) {
            case "db":
                return $this->restoreDb();
            case "file":
                return $this->restoreFiles();
            default:
                die("Не возможно установить такой тип бекапа. <a href='/admin/autobackup/copys/'>Назад</a>");
        }
    }


    /**
     * Восстановить бекап файлов
     *
     * @return bool
     * @throws BackupException
     */
    public function restoreFiles() {
        if (empty($this->backup_file)) {
            throw new BackupException("Нет файла для восстановления");
        }

        if (BackupTables::getBackupType($this->filename) != "file") {
            throw new BackupException("Архив для восстановления файлов не содержит файлы", $this->filename);
        }

        $zip = new ZipArchive();
        $open = $zip->open($this->backup_file);

        if ($open !== true) {
            throw new BackupException("Не получается открыть файл $this->backup_file для восстановления. Код ошибки ZipArchive::$open", ["zip" => $zip]);
        }

        $result = $zip->extractTo(ROOT);
        $zip->close();

        unlink($this->backup_file);

        return $result;
    }

    /**
     * Восстановить бд
     *
     * @return bool
     * @throws BackupException
     */
    public function restoreDb() {
        if (empty($this->backup_file)) {
            throw new BackupException("Нет файла для восстановления");
        }


        if (BackupTables::getBackupType($this->filename) != "db") {
            throw new BackupException("Архив для восстановления файлов не содержит бекап бд", $this->filename);
        }
        $time = time();
        @mkdir($this->tmp_dir.$time);

        $zip = new ZipArchive();


        $open = $zip->open($this->backup_file);
        $result = $zip->extractTo($this->tmp_dir.$time);
        $res = $zip->close();

        $filename = str_replace(".zip", ".sql", $this->filename);
        $filepath = $this->tmp_dir.$time."/".$filename;

        $this->installMysqlDump($this->tmp_dir.$time."/".$filename);

        unlink($this->tmp_dir.$time."/".$filename);

        return true;
    }


    /**
     * Запустить установку дампа в бд
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
        $command = 'mysql --user='.$user.' --password="'.addslashes($password).'" --host='.$host.' '.$dbname.' < '.$dump_path;
        exec($command);
    }


    /**
     * Запустить восстановление
     *
     * @param $task_id
     * @param $date
     * @param $restore_type
     *
     * @return string - айди выполнения задания
     */
    public static function runRestore($task_id, $date, $restore_type) {
        $uid = BackupProgress::generateUid(time(), $task_id);

        $params = " --uid=$uid --taskid=$task_id --date=$date --type=$restore_type";
        $cmd = BackupCronHandler::getPhp()." ".BackupCronHandler::RESTORETASKDIR.$params;

        SmartBackup::runAsync($cmd);

        return $uid;
    }

}