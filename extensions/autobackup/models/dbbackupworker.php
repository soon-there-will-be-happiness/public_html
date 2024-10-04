<?php


class DbBackupWorker extends BackupWorker {

    const DB_CONF_PATH = ROOT."/tmp/db.cnf";

    /** @var int - Размер части в байтах */
    static $PartSize = 100000000;

    private $tables = [];
    private $parts = [];
    private $clientsCsv = "";
    private $excludeTables = [
        PREFICS."backup_tasks",
        PREFICS."backup_copys",
        PREFICS."backup_buckets",
        PREFICS."backup_progress",
    ];

    public static $startTime;
    public static $endTime;

    public function __construct($partSize) {
        self::$startTime = microtime(1);
        self::$PartSize = $partSize;
    }

    public function dumpDatabase($divide_to_parts = false){

        $this->getAllTables();
        $this->excludeTables();
        if ($divide_to_parts) {
            $this->sortTablesToParts($this->tables);
        }

        return $this;
    }

    /**
     * Отправить бекап
     *
     * @return false|void
     * @throws BackupException
     * @throws BucketException
     */
    public function sendToBuckets() {
        $result = false;

        if (!empty($this->clientsCsv)) {
            $result = $this->sendClientsToBuckets();
            self::$endTime = microtime(1);
            return $result;
        }


        if (!empty($this->parts)) {
            foreach ($this->parts as $key => $part) {
                $result = $this->dumpTables($part, $key);
            }
            self::$endTime = microtime(1);
            return $result;
        }

        if (!empty($this->tables)) {
            $result = $this->dumpTables($this->tables);
            self::$endTime = microtime(1);
            return $result;
        } else {
            throw new BackupException("Нет таблиц в бд!", ["tables" => $this->tables]);
        }

    }

    /**
     * Создать бекап бд
     * @param array $tables
     * @param string $key
     *
     * @throws BackupException
     * @throws BucketException
     */
    private function dumpTables(array $tables, $key = "") {
        $tablesStr = "";
        foreach ($tables as $table) {
            $tablesStr .= $table['Table']." ";
        }

        $dbData = self::getDbParams();
        $dbname = $dbData['db'];
        $user = $dbData['user'];
        $pass = $dbData['pass'];
        if (!empty($pass)) {
            $pass = "--password='$pass'";
        }

        $filenameOnly = "db_". self::getDomainName() . "task".BackupData::$currentTaskId."_".BackupWorker::getDate();
        $filenamePart = "/tmp/$filenameOnly";
        $filenameFull = ROOT.$filenamePart;
        if ($key) {
            $filenameOnly = "$filenameOnly-$key";
            $filenamePart = $filenamePart."-$key";
            $filenameFull = ROOT. $filenamePart;
        }
        $file = self::makeCnfFileForMysql();
        if ($file) {
            $credentials = "--defaults-extra-file=".$file;
        } else {
            $credentials = "--user='$user' $pass";
        }

        $ff = [];
        exec("mysqldump $credentials $dbname $tablesStr > $filenameFull.sql", $ff);

        $archive = $this->makeArchive($filenameFull.".zip", [["path" => "$filenamePart.sql"]], $filenameOnly.".sql");

        if (!$archive) {
            throw new BackupException("Не удалось создать архив $filenameFull.zip");
        }

        unlink("$filenameFull.sql");

        BackupProgress::progress()->setActionAsExecutable("upload_db_archive");
        try {
            BackupBuckets::send($filenameFull.".zip");
        } catch (BucketException $bucketException) {
            BackupWorker::showInfo($bucketException->getMessage()."\n");
            throw $bucketException;
        }

        unlink($filenameFull.".zip");
    }

    /**
     * Получить csv клиентов
     * @return $this
     */
    public function getClientsCSV() {
        $users = User::getAllUsers();
        $csv = User::getCsv($users, ";");

        $date = BackupWorker::getDate();
        $filename = "clients_$date.csv";
        $path = "/tmp/$filename";

        file_put_contents(ROOT.$path,$csv);
        $this->clientsCsv = $path;

        return $this;
    }

    /**
     * Отправить архив клиентов в хранилища
     * @throws BackupException
     * @throws BucketException
     */
    private function sendClientsToBuckets() {
        $date = BackupWorker::getDate();
        $filename = ROOT."/tmp/clients_". self::getDomainName() . "task".BackupData::$currentTaskId."_"."$date.zip";


        $archive = $this->makeArchive($filename, [["path" => $this->clientsCsv]], "clients.csv");

        if (!$archive) {
            throw new BackupException("Не удалось создать архив $filename");
        }

        unlink(ROOT.$this->clientsCsv);

        BackupProgress::progress()->setActionAsExecutable("upload_clients_archive");
        try {
            BackupBuckets::send($filename);
        } catch (BucketException $bucketException) {
            BackupWorker::showInfo($bucketException->getMessage()."\n");
            throw $bucketException;
        }

        unlink($filename);
    }


    /**
     * Получить массив всех таблиц в бд вместе с размером
     *
     * @return array
     */
    private function getAllTables() {
        $paramPath = ROOT . '/config/config.php';
        $params = include($paramPath);
        /**
         * @var $dbname
         */
        $tables = Db::getConnection()->query("SELECT table_name AS `Table`, round(((data_length + index_length) / 1024 / 1024), 2) `size` FROM information_schema.TABLES WHERE table_schema = '$dbname' ORDER BY `size` DESC")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->tables = $tables;
    }

    private function excludeTables() {
        $filtered = [];
        foreach ($this->tables as $table) {
            if (in_array($table['Table'], $this->excludeTables)) {
                continue;
            }
            $filtered[] = $table;
        }

        return $this->tables = $filtered;
    }

    /**
     * Отсоритровать таблицы по размеру на части бэкапа
     * @param $tables
     *
     * @return array
     */
    private function sortTablesToParts($tables) {
        $parts = [];
        $partKey = 0;
        $partSize = 0;

        foreach ($tables as $key => $table) {
            if ($partSize >= self::$PartSize) {//Если размер части больше установленного
                $partSize = 0;//То переключиться на следующую часть
                $partKey += 1;
            }

            $partSize += $table['size'] * 1000;

            $parts["part".$partKey][] = $table;
        }

        return $this->parts = $parts;
    }

    /**
     * Получить имя утилиты Mysqldump
     * @return string
     */
    private function getMysqldump() {
        return "mysqldump";
    }

    /**
     * Проверить существование утилиты mysqldump
     * @return mixed
     * @throws BackupException
     */
    private function checkExistenceOfMysqldump() {
        exec($this->getMysqldump(), $output, $status);

        if (empty($output)) {
            throw new BackupException("Ошибка создания дампа бд. Команды ".$this->getMysqldump()." не существует", ['output' => $output, "code" => $status]);
        }

        return $output;
    }

    /**
     * Получить параметры входа в бд
     * @return array
     */
    private static function getDbParams() {
        include ROOT.'/config/config.php';
        /**
         * @var $dbname
         * @var $user
         * @var $password
         * @var $host
         */
        return [
            "db" => $dbname,
            "user" => $user,
            "pass" => $password,
            "host" => $host
        ];
    }


    public static function makeCnfFileForMysql() {
        $dbData = self::getDbParams();

        $dbname = $dbData['db'];
        $user = $dbData['user'];
        $pass = $dbData['pass'];
        $host = $dbData['host'];

        $content = "[client]\n";
        $content .= 'user = "'.$user.'"'."\n";
        $content .= 'password = "'.$pass.'"'."\n";
        $content .= 'host = "'.$host.'"';

        $result = file_put_contents(self::DB_CONF_PATH, $content);
        return $result ? self::DB_CONF_PATH : false;
    }
}