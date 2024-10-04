<?php


class FileBackupWorker extends BackupWorker {

    /** @var int - Размер части в байтах */
    static $PartSize = 100000000;

    /** @var string[] - Директории для бекапа */
    private $dirsToBackup = ["/images", "/load"];

    /** @var array Файлы для бекапа */
    private $backupFiles = [];

    /** @var array Массив файлов для бекапа разбитых на части */
    private $backupFilesParts = [];

    /** @var array Какие папки/файлы исключить */
    private $exclude;

    public static $startTime;
    public static $endTime;


    public function __construct(array $exclude, array $include, $PartSize) {
        $this->exclude = array_merge($exclude, ["/tmp"]);
        $this->dirsToBackup = $include;
        self::$PartSize = $PartSize;

        foreach ($this->exclude as $key => $file) {
            $this->exclude[$key] = str_replace(ROOT, "", $file);
        }
        foreach ($this->dirsToBackup as $key => $file) {
            $this->dirsToBackup[$key] = str_replace(ROOT, "", $file);
        }
    }

    /**
     * Запустить бекап файлов
     * @param false $divide_to_parts
     *
     * @return $this
     */
    public function runBackup($divide_to_parts = false) {
        $this->getAllFilesWithFilters();

        if ($divide_to_parts) {
            $this->generatePartFiles();
        }

        return $this;
    }

    /**
     * Отправить бекап в хранилища
     * @return bool|void
     * @throws BackupException
     * @throws BucketException
     */
    public function sendToBuckets() {
        $result = false;
        self::$startTime = microtime(1);

        if (!empty($this->backupFilesParts)) {//отправить по частям

            foreach ($this->backupFilesParts as $key => $backupPart) {
                $result = $this->backupFiles($backupPart, $key);
            }
            self::$endTime = microtime(1);
            return $result;
        }

        if (!empty($this->backupFiles)) { //Отправить все сразу
            $result = $this->backupFiles($this->backupFiles);
            self::$endTime = microtime(1);
            return $result;
        }

        if (empty($this->backupFiles)) {
            self::$endTime = microtime(1);
            return self::showInfo("Нет файлов для бекапа", 2);
        }

        return $result;
    }

    /**
     * Отправить файлы в хранилища
     * @param array $files
     * @param null $key
     *
     * @return bool
     * @throws BackupException
     * @throws BucketException
     */
    private function backupFiles(array $files, $key = null) {
        $date = BackupWorker::getDate();
        $filename = ROOT . "/tmp/files_". self::getDomainName() . "task".BackupData::$currentTaskId."_$date.zip";

        if ($key) {
            $filename = ROOT . "/tmp/files_". self::getDomainName() . "task".BackupData::$currentTaskId."_"."$date-$key.zip";
        }

        $archive = $this->makeArchive($filename, $files);
        if (!$archive) {
            throw new BackupException("Не удалось создать архив $filename");
        }

        BackupProgress::progress()->setActionAsExecutable("upload_files_archive");
        try {
            $result = BackupBuckets::send($filename);
        } catch (BucketException $bucketException) {
            BackupWorker::showInfo($bucketException->getMessage() . "\n");
            throw $bucketException;
        }

        unlink($filename);

        return $result ?? false;
    }




    /**
     * Получить массив файлов без фильтров
     * @return array
     */
    private function getAllFilesForBackupWithoutExclude() {
        $dirs = $this->dirsToBackup;

        $files = [];
        foreach ($dirs as $dir) {
            $dirFiles = System::get_dir_files(ROOT . $dir);

            foreach ($dirFiles as $key => $file) {//Убрать root из файла
                $dirFiles[$key] = str_replace(ROOT, "", $file);
            }

            $files = array_merge($files, $dirFiles);
        }

        self::showInfo("Сформирован список файлов для бекапа");

        return $this->backupFiles = $files;
    }

    /**
     * Получить массив файлов с фильтром
     *
     * @return array
     */
    private function getAllFilesWithFilters() {
        $files = $this->getAllFilesForBackupWithoutExclude();

        foreach ($files as $key => $file) {

            foreach ($this->exclude as $item) {
                if (empty($item)) {
                    continue;
                }

                if(strpos($file, $item) !== false) {
                    unset($files[$key]);
                    break;
                }
            }

        }

        return $this->backupFiles = $files;
    }

    /**
     * Разбить файлы по частям и размеру
     * @return array
     */
    private function generatePartFiles() {
        //Сортировка по размеру файлов

        $parts = [];
        $partKey = 0;
        $partSize = 0;

        foreach ($this->backupFiles as $key => $file) {
            if ($partSize >= self::$PartSize) {//Если размер части больше установленного
                $partSize = 0;//То переключиться на следующую часть
                $partKey += 1;
            }

            $filesize = filesize(ROOT.$file);

            $partSize += $filesize;

            $parts["part".$partKey][] = $file;
        }

        self::showInfo("Успешно создан список частей для бекапа");

        return $this->backupFilesParts = $parts;
    }

}