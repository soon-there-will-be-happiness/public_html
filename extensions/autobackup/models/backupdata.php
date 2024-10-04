<?php


class BackupData {

    private $backup_files;
    private $backup_db;

    private $enableDb;
    private $enableFiles;
    private $enableClients;

    private $divide_to_parts;

    private $folders_include;
    private $folders_exclude;
    private $files_exclude;
    private $partSize = 100000000;//100 мб

    static $currentTaskId = 0;
    static $storages = [];

    /**
     * Создать объект BackupData
     *
     * @param bool $enableDb
     * @param bool $enableFiles
     * @param bool $enableClients
     * @param array $folders_include
     * @param array $folders_exclude
     * @param array $files_exclude
     * @param integer $taskId
     * @param bool $divide_to_parts
     * @param int $partsize
     * @param array $storages
     *
     * @throws BackupException
     */
    public function __construct($enableDb, $enableFiles, $enableClients, $folders_include, $folders_exclude, $files_exclude, $taskId, $divide_to_parts, $partsize = 100000000, $storages = []) {
        $this->enableDb = $enableDb;
        $this->enableFiles = $enableFiles;
        $this->enableClients = $enableClients;
        $this->folders_include = self::getFoldersInRoot();
        $this->folders_exclude = $folders_exclude;
        $this->files_exclude = $files_exclude;
        $this->divide_to_parts = boolval($divide_to_parts);
        self::$currentTaskId = $taskId;
        $this->partSize = $partsize;
        if (empty($storages)) {
            throw new BackupException("В задании $taskId не выбраны хранилища");
        }
        self::$storages = $storages;
    }

    /**
     * Запустить задание
     */
    public function run() {

        $this->runFilesBackup();

        $this->runDbBackup();

        $this->runClientToCsvBackup();
    }


    /**
     * Запустить бекап файлов
     * @return bool
     * @throws BackupException
     */
    public function runFilesBackup()
    {
        if (!$this->enableFiles) {
            return false;
        }

        self::echo("ЗАПУСК БЕКАПА ФАЙЛОВ");

        BackupProgress::progress()->setActionAsExecutable("make_files_archive");

        $fileBackupWorker = new FileBackupWorker(array_merge($this->folders_exclude, $this->files_exclude), $this->folders_include, $this->partSize);
        $fileBackupWorker->runBackup($this->divide_to_parts)->sendToBuckets();

        $fileWorkTime = self::getWorkTime(FileBackupWorker::$endTime, FileBackupWorker::$startTime);
        self::echo("Время бекапа файлов: {$fileWorkTime}s");

        return true;
    }

    /**
     * Запустить бекап бд
     * @return bool
     * @throws BackupException
     */
    public function runDbBackup() {
        if (!$this->enableDb) {
            return false;
        }

        self::echo("ЗАПУСК БЕКАПА БД");

        BackupProgress::progress()->setActionAsExecutable("make_db_archive");

        $dbBackupWorker = new DbBackupWorker($this->partSize);
        $dbBackupWorker->dumpDatabase($this->divide_to_parts)->sendToBuckets();

        $dbWorkTime = self::getWorkTime(DbBackupWorker::$endTime, DbBackupWorker::$startTime);
        self::echo("Время бекапа бд: {$dbWorkTime}s");

        return true;
    }

    /**
     * Запустить бекап клиентов
     * @return bool
     * @throws BackupException
     */
    public function runClientToCsvBackup() {
        if (!$this->enableClients) {
            return false;
        }

        self::echo("ЗАПУСК БЕКАПА КЛИЕНТОВ В CSV");
        BackupProgress::progress()->setActionAsExecutable("make_clients_archive");

        $dbBackupWorker = new DbBackupWorker($this->partSize);
        $dbBackupWorker->getClientsCSV()->sendToBuckets();

        $clientsWorkTime = self::getWorkTime(DbBackupWorker::$endTime, DbBackupWorker::$startTime);
        self::echo("Время бекапа клиентов: {$clientsWorkTime}s");

        return true;
    }


    /**
     * Получить время работы
     * @param $end
     * @param $start
     *
     * @return float
     */
    private static function getWorkTime($end, $start) {
        return round($end - $start, 2);
    }

    /**
     * Создать запись работы
     * @param $mess
     * @param string $n
     */
    private static function echo($mess, $n = "\n") {
        BackupWorker::showInfo($mess, 1, null, "\n");
    }


    /**
     * Получить все папки в директории
     *
     * @param string $dir
     *
     * @return array|false
     */
    public static function getFoldersInRoot($dir = ROOT) {
        $folders = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($folders as $key => $folder) {
            $folders[$key] = str_replace(ROOT, "", $folder);
        }
        $folders[] = "/";

        return $folders;
    }

}