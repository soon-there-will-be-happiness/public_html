<?php

class BackupTables {



    # ЗАДАНИЯ

    /**
     * Получить задания
     * @param string $id - не обязательно
     * @return array
     */
    public static function getBackupTasks($id = "") {
        $db = Db::getConnection();
        if (!empty($id)) {
            $id = " WHERE `id` = '$id'";
        }

        $result = $db->query("SELECT * FROM `".PREFICS."backup_tasks` $id ORDER BY id DESC");

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получить задание по айди и статусу
     * @param $id
     * @param string $status
     * @return mixed
     */
    public static function getBackupTasksById($id, $status = "") {
        $db = Db::getConnection();
        if (!empty($status)) {
            $status = "`status` = '$status' AND ";
        }

        $result = $db->query("SELECT * FROM `".PREFICS."backup_tasks` WHERE $status `id`='$id'");

        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Получить актуальные задания
     * @param int $timeUnix
     *
     * @return array|false
     */
    public static function getActualTasks(int $timeUnix) {
        $db = Db::getConnection();

        $result = $db->query("SELECT * FROM `".PREFICS."backup_tasks` WHERE `next_action` <= '$timeUnix' AND `status` = '1'");
        $tasks = $result->fetchAll(PDO::FETCH_ASSOC);

        if (!$tasks) {
            return false;
        }

        foreach ($tasks as $id => $task) {
            $tasks[$id]['period'] = json_decode($task['period'], true);
        }

        return $tasks;
    }

    /**
     * Получить задание по айди
     * @param $id
     *
     * @return mixed
     */
    public static function getTaskById($id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM `".PREFICS."backup_tasks` WHERE `id`='$id'");

        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Создать задание
     * @param $name
     * @param $desc
     * @param $period
     * @param $next_action
     * @param $enableDb
     * @param $enableFiles
     * @param $folder_include
     * @param $folder_exclude
     * @param $files_exclude
     * @param $send_notif
     * @param $divide_to_parts
     * @param $clients_enable
     * @param $amount_copies
     * @param $selected_storages
     *
     * @return bool
     */
    public static function addBackupTask($name, $desc, $period, $next_action, $enableDb, $enableFiles, $folder_include, $folder_exclude, $files_exclude, $send_notif, $divide_to_parts, $clients_enable, $amount_copies, $selected_storages) {
        $db = DB::getConnection();

        $result = $db->prepare("INSERT INTO `".PREFICS."backup_tasks` 
            (`name`, `desc`, `period`, `next_action`, `bd_enable`, `files_enable`, `folders_include`, `folders_exclude`, `files_exclude`, `send_notif`, `divide_to_parts`, `clients_enable`, `amount_copies`, `selected_storages`) VALUES 
            (:name, :desc, :period, :next_action, :bd_enable, :files_enable, :folders_include, :folders_exclude, :files_exclude, :send_notif, :divide_to_parts, :clients_enable, :amount_copies, :selected_storages)
        ");

        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':desc', $desc, PDO::PARAM_STR);
        $result->bindParam(':period', $period, PDO::PARAM_STR);
        $result->bindParam(':next_action', $next_action, PDO::PARAM_INT);
        $result->bindParam(':bd_enable', $enableDb, PDO::PARAM_INT);
        $result->bindParam(':files_enable', $enableFiles, PDO::PARAM_INT);
        $result->bindParam(':folders_include', $folder_include, PDO::PARAM_STR);
        $result->bindParam(':folders_exclude', $folder_exclude, PDO::PARAM_STR);
        $result->bindParam(':files_exclude', $files_exclude, PDO::PARAM_STR);
        $result->bindParam(':send_notif', $send_notif, PDO::PARAM_STR);
        $result->bindParam(':divide_to_parts', $divide_to_parts, PDO::PARAM_INT);
        $result->bindParam(':clients_enable', $clients_enable, PDO::PARAM_INT);
        $result->bindParam(':amount_copies', $amount_copies, PDO::PARAM_INT);
        $result->bindParam(':selected_storages', $selected_storages, PDO::PARAM_STR);

        return $result->execute();
    }

    /**
     * Изменить задание
     * @param $id
     * @param $name
     * @param $desc
     * @param $period
     * @param $next_action
     * @param $enableDb
     * @param $enableFiles
     * @param $folder_include
     * @param $folder_exclude
     * @param $files_exclude
     * @param $send_notif
     * @param $divide_to_parts
     * @param $clients_enable
     * @param $amount_copies
     * @param int $status
     * @param string $selected_storages
     *
     * @return bool
     */
    public static function editBackupTask($id, $name, $desc, $period, $next_action, $enableDb, $enableFiles, $folder_include, $folder_exclude, $files_exclude, $send_notif, $divide_to_parts, $clients_enable, $amount_copies, $status = 1, $selected_storages = "[]") {
        $db = DB::getConnection();

        $result = $db->prepare("UPDATE `".PREFICS."backup_tasks` 
        SET `name` = :name, `desc` = :desc, `period` = :period, `next_action` = :next_action, `bd_enable` = :bd_enable, `files_enable` = :files_enable, 
        `folders_include` = :folders_include, `folders_exclude` = :folders_exclude, `files_exclude` = :files_exclude, `send_notif` = :send_notif, `divide_to_parts` = :divide_to_parts, 
        `clients_enable` = :clients_enable, `amount_copies` = :amount_copies, `status` = :status, `selected_storages` = :selected_storages
        WHERE `id` = '$id'");

        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':desc', $desc, PDO::PARAM_STR);
        $result->bindParam(':period', $period, PDO::PARAM_STR);
        $result->bindParam(':next_action', $next_action, PDO::PARAM_INT);
        $result->bindParam(':bd_enable', $enableDb, PDO::PARAM_INT);
        $result->bindParam(':files_enable', $enableFiles, PDO::PARAM_INT);
        $result->bindParam(':folders_include', $folder_include, PDO::PARAM_STR);
        $result->bindParam(':folders_exclude', $folder_exclude, PDO::PARAM_STR);
        $result->bindParam(':files_exclude', $files_exclude, PDO::PARAM_STR);
        $result->bindParam(':send_notif', $send_notif, PDO::PARAM_STR);
        $result->bindParam(':divide_to_parts', $divide_to_parts, PDO::PARAM_INT);
        $result->bindParam(':clients_enable', $clients_enable, PDO::PARAM_INT);
        $result->bindParam(':amount_copies', $amount_copies, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':selected_storages', $selected_storages, PDO::PARAM_STR);

        return $result->execute();
    }

    /**
     * Удалить задание
     * @param $id
     *
     * @return bool
     */
    public static function removeTask($id) {
        $result = Db::getConnection()->prepare("DELETE FROM `".PREFICS."backup_tasks` WHERE `id` = :id");
        return $result->execute([":id" => $id]);
    }

    /**
     * Удалить копии задания
     * @param $id
     *
     * @return bool
     */
    public static function removeTaskCopies($id) {

        $copies = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `task_id` = '$id'")->fetchAll(PDO::FETCH_ASSOC);
        if ($copies) {
            foreach ($copies as $item) {
                $item['params'] = json_decode($item['params'], true);
                BackupRemover::removeFileInStorages($item['filename'], $item['params']['bucketType'], $item['params'], $item['id'], $item['bucket_id']);
            }
        }

        $result = Db::getConnection()->prepare("DELETE FROM `".PREFICS."backup_copys` WHERE `task_id` = :id");
        return $result->execute([":id" => $id]);
    }

    /**
     * Обновить время сработки задания
     * @param $id
     * @param $time
     * @param $nextAction
     *
     * @return bool
     */
    public static function updateTask($id, $time, $nextAction) {
        $db = Db::getConnection();

        $result = $db->prepare("UPDATE `".PREFICS."backup_tasks` SET `last_run` = :time, `next_action` = :next_action WHERE `id` = $id");

        $result->bindParam(':time', $time, PDO::PARAM_INT);
        $result->bindParam(':next_action', $nextAction, PDO::PARAM_INT);

        return $result->execute();
    }


    //////////////////////////////////////////////
    /// ХРАНИЛИЩА
    //////////////////////////////////////////////

    /**
     * Получить хранилища
     *
     * @param int|null $status
     *
     * @return array
     */
    public static function getBuckets(int $status = null) {
        if ($status) {
            $status = " WHERE `status` = $status";
        }
        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_buckets` $status");
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получить хранилища с помощью id
     *
     * @param array $ids
     * @param int $status
     *
     * @return array
     */
    public static function getBucketsByIds($ids = [], int $status = 1) {
        if ($ids == [0]) {
            return [SmartBackup::getDefaultStorage()];
        }
        if ($status) {
            $status = " AND `status` = $status";
        }
        $ids = implode(", ", $ids);
        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_buckets` WHERE `id` IN ($ids) $status");
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Создать хранилище
     * @param $title
     * @param $type
     * @param $params
     *
     * @return bool
     */
    public static function addBucket($title, $type, $params) {
        $result = Db::getConnection()->prepare("INSERT INTO `".PREFICS."backup_buckets` (`title`, `type`, `params`) VALUES (:title, :type, :params)");

        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);

        return $result->execute();
    }

    /**
     * Изменить хранилище
     * @param $id
     * @param $title
     * @param $type
     * @param $params
     * @param int $status
     *
     * @return bool
     */
    public static function editBucket($id, $title, $type, $params, $status = 1) {
        $result = Db::getConnection()->prepare("UPDATE `".PREFICS."backup_buckets` SET title = :title, type = :type, params = :params, `status` = :status WHERE id = :id");
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }

    /**
     * Получить хранилище
     * @param $id
     *
     * @return mixed
     */
    public static function getBucket($id) {
        $result = Db::getConnection()->query("SElECT * FROM `".PREFICS."backup_buckets` WHERE `id` = $id");

        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Удалить хранилище
     * @param $id
     *
     * @return bool
     */
    public static function removeStorage($id) {
        $result = Db::getConnection()->prepare("DELETE FROM `".PREFICS."backup_buckets` WHERE `id` = :id");
        return $result->execute([":id" => $id]);
    }

    /**
     * Удалить копии связанные с хранилищем
     * @param $id
     *
     * @return bool
     */
    public static function removeStorageCopies($id) {

        $copies = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `bucket_id` = '$id'")->fetchAll(PDO::FETCH_ASSOC);
        if ($copies) {
            foreach ($copies as $item) {
                $item['params'] = json_decode($item['params'], true);
                BackupRemover::removeFileInStorages($item['filename'], $item['params']['bucketType'], $item['params'], $item['id'], $item['bucket_id']);
            }
        }

        $result = Db::getConnection()->prepare("DELETE FROM `".PREFICS."backup_copys` WHERE `bucket_id` = :id");
        return $result->execute([":id" => $id]);
    }


    /**
     * Получить выбранные хранилища задания
     * @param array $task
     *
     * @return array
     */
    public static function getSelectedStoragesForTask($task) {
        if (is_string($task['selected_storages'])) {
            $task['selected_storages'] = json_decode($task['selected_storages']);
        }

        if (empty($task['selected_storages'])) {
            return [];
        }

        $storages = self::getBucketsByIds($task['selected_storages']);

        $storageTypes = [];
        foreach ($storages as $storage) {
            $storageTypes[] = $storage['type'];
        }

        return array_unique($storageTypes);
    }



    //////////////////////////////////////////////
    /// КОПИИ
    //////////////////////////////////////////////

    /**
     * Добавить копию
     * @param $date
     * @param $filename
     * @param $filesize
     * @param $task_id
     * @param $bucket_id
     * @param $params
     * @param int $is_part
     * @param int $is_smart
     * @param null $smart_type
     *
     * @return bool
     */
    public static function addBackupCopy($date, $filename, $filesize, $task_id, $bucket_id, $params, $is_part = 0, $is_smart = 0, $smart_type = null) {
        $result = Db::getConnection()
            ->prepare("INSERT INTO `".PREFICS."backup_copys` (`date`, `filename`, `filesize`, `task_id`, `bucket_id`, `params`, `is_part`, `is_smart`, `smart_type`) 
            VALUES (:date, :filename, :filesize, :task_id, :bucket_id, :params, :is_part, :is_smart, :smart_type)");

        $result->bindParam(':date', $date, PDO::PARAM_INT);
        $result->bindParam(':filename', $filename, PDO::PARAM_STR);
        $result->bindParam(':filesize', $filesize, PDO::PARAM_INT);
        $result->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $result->bindParam(':bucket_id', $bucket_id, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':is_part', $is_part, PDO::PARAM_INT);
        $result->bindParam(':is_smart', $is_smart, PDO::PARAM_INT);
        $result->bindParam(':smart_type', $smart_type, PDO::PARAM_INT || PDO::PARAM_NULL);

        $result = $result->execute();
        self::saveCopyToFile($date, $filename, $filesize, $task_id, $bucket_id, $params, $is_part, $is_smart, $smart_type);

        return $result;
    }

    public static function saveCopyToFile($date, $filename, $filesize, $task_id, $bucket_id, $params, $is_part = 0, $is_smart = 0, $smart_type = null) {
        $copy = [
            "date" => $date,
            "filename" => $filename,
            "filesize" => $filesize,
            "task_id" => $task_id,
            "bucket_id" => $bucket_id,
            "params" => $params,
            "is_part" => $is_part,
            "is_smart" => $is_smart,
            "smart_type" => $smart_type,
        ];

        $copy = json_encode($copy);
        $path = ROOT."/extensions/autobackup/config/copies";

        $result = file_put_contents($path, $copy."\n", FILE_APPEND);
        if (!$result) {
            Log::add(3, "Не удалось записать информацию о копии в файл", ["copy" => $copy, "result" => $result, "error" => error_get_last()]);
        }

        return (bool) $result;
    }

    /**
     * Получить копии с условием
     * @param string $where
     *
     * @return array|false
     */
    public static function getBackupCopies($where = "") {
        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` $where ORDER BY `date` DESC");
        $copys = $result->fetchAll(PDO::FETCH_ASSOC);
        if (!$copys) {
            return false;
        }
        foreach ($copys as $key => $copy) {
            $copys[$key]['params'] = json_decode($copy['params'], true);
        }

        return $copys;
    }

    /**
     * Получить копию
     * @param $id
     *
     * @return mixed
     */
    public static function getBackupCopy($id) {
        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `id` = '$id'");
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Получить копии по имени файла
     * @param $filename
     *
     * @return array|false
     */
    public static function getBackupCopiesByFilename($filename) {
        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `filename` = '$filename'");
        $copys = $result->fetchAll(PDO::FETCH_ASSOC);
        if (!$copys) {
            return false;
        }
        foreach ($copys as $key => $copy) {
            $copys[$key]['params'] = json_decode($copy['params'], true);
        }
        return $copys;
    }


    /**
     * Получить все части бэкапа
     * @param $fullFilename
     * @param $taskId
     * @param $date
     *
     * @return array
     */
    public static function getAllBackupParts($fullFilename, $taskId, $date) {
        $file = self::removePartNumInFilename($fullFilename);
        $file = str_replace(".zip", "", $file);

        $sqlFile = $file."-part";
        $findedParts = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `filename` LIKE '%$file%' AND `task_id` = '$taskId' AND `date` = '$date'")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($findedParts as $key => $copy) {
            $findedParts[$key]['params'] = json_decode($copy['params'], true);
        }

        //отсортировать массив на: № Части -> Копии части в хранилищах
        $sorted = [];
        foreach ($findedParts as $copy) {
            $key = self::getPartNumber($copy['filename']);
            $sorted[$key][] = $copy;
        }

        return $sorted;
    }


    /**
     * Получить массив заданий и копий
     * @param $tasks
     * @param false $minDate
     * @param int $maxDate
     *
     * @return array
     * @throws BackupException
     */
    public static function getTaskCopies($tasks, $minDate = false, $maxDate = 0, $needSmart = true) {
        if ($needSmart) {
            $tasksIds = [0];
        }

        foreach ($tasks as $task) {
            $tasksIds[] = $task['id'];
        }

        $taskCopies = [];
        foreach ($tasksIds as $tasksId) {
            $where = "";
            if ($minDate) {
                $where = " AND `date` >= '$minDate'";
            }
            if ($maxDate) {
                $where .= " AND `date` <= '$maxDate'";
            }

            $taskCopies[$tasksId] = Db::getConnection()
                ->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `task_id` = $tasksId $where")
                ->fetchAll(PDO::FETCH_ASSOC);
        }

        $resultCopies = [];
        foreach ($taskCopies as $task_id => $copies) {
            $resultCopies[$task_id] = [];
            foreach ($copies as $copy) {
                $date = $copy['date'];
                $copy['params'] = json_decode($copy['params'], true);
                $type = self::getBackupType($copy['filename']);
                $bucketType = $copy['params']['bucketType'];

                $resultCopies[$task_id][$date][$type][$bucketType][] = $copy;
            }
        }

        foreach ($resultCopies as $task_id => $dateCopies) {
            arsort($dateCopies);
            $resultCopies[$task_id] = $dateCopies;
        }

        return $resultCopies;
    }

    /**
     * Фильтр по копиям перед выводом в цикле
     * @param $copys
     *
     * @return array
     */
    public static function processCopys($copys) {
        $resultCopies = [];
        foreach ($copys as $task_id => $dateCopy) {
            if (empty($dateCopy)) {
                $resultCopies[] = [
                    "task_id" => $task_id,
                    "copys" => [],
                    "bucketTypes" => [],
                    "fullSize" => 0,
                    "task" => self::getTaskById($task_id),
                    "date" => 0,
                    "types" => [],
                    "is_smart" => false,
                    "smart_type" => 0,
                ];
                continue;
            }

            foreach ($dateCopy as $date => $typeCopies) {
                $types = array_keys($typeCopies);
                $bucketTypes = [];
                $fullSize = 0;
                $isSmart = false;
                $smartType = 0;
                $copiesList = [];


                foreach ($typeCopies as $type => $bucketTypeCopies) {
                    $bucketTypes = array_keys($bucketTypeCopies);

                    foreach ($bucketTypeCopies as $copies) {
                        foreach ($copies as $copy) {
                            $fullSize += $copy['filesize'];
                            if ($copy['is_smart']) {
                                $isSmart = true;
                                $smartType = $copy['smart_type'];
                            }
                        }
                        $copiesList = array_merge($copiesList, $copies);
                    }
                }

                $resultCopies[] = [
                    "task_id" => $task_id,
                    "copys" => $copiesList,
                    "date" => $date,
                    "bucketTypes" => $bucketTypes,
                    "types" => $types,
                    "fullSize" => $fullSize,
                    "task" => self::getTaskById($task_id),
                    "is_smart" => $isSmart,
                    "smart_type" => $smartType,
                ];


            }
        }

        $finalCopies = [];
        $duplicates = [];
        foreach ($resultCopies as $copy) {
            $duplicateKey = $copy['task_id']."-".$copy['date'];

            if (in_array($duplicateKey, $duplicates)) {
                continue;
            }
            if ($copy['task_id'] == 0 && $copy['is_smart'] == false && empty($copy['copys'])) {
                continue;
            }

            $finalCopies[] = $copy;
            $duplicates[] = $duplicateKey;
        }

        return $finalCopies;
    }

    /**
     * Найти связанные копии
     * @param $task_id
     * @param $date
     * @param string $type
     *
     * @return array|false
     */
    public static function findCopy($task_id, $date, $type = "") {

        if (!in_array($type, [0, "db", "file", ""])) {
            return false;
        }
        if ($type == "0") {
            $type = "";
        }
        if ($type == "file") {
            $type = " AND `filename` LIKE 'file%'";
        }
        if ($type == "db") {
            $type = " AND `filename` LIKE 'db%'";
        }
        if ($type == "clients") {
            $type = " AND `filename` LIKE 'clients%'";
        }

        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` WHERE `task_id` = '$task_id' AND `date` = '$date' $type");

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }



    # ДОПОЛНИТЕЛЬНЫЕ

    /**
     * Очистить таблицу backup_progress
     * @return bool
     */
    public static function truncateProgressTable() {
        $result = Db::getConnection()->prepare("TRUNCATE `".PREFICS."backup_progress`");
        return $result->execute();
    }

    /**
     * Получить день недели из номера
     * @param int (0-6) $day
     * @param bool $lang - true: русский / false: англ
     *
     * @return string
     */
    public static function convertDayWeek($day, $lang = true) {
        switch ($day) {
            case 0:
                return $lang ? "Воскресение" : "Sunday";
            case 1:
                return $lang ? "Понедельник" : "Monday";
            case 2:
                return $lang ? "Вторник" : "Tuesday";
            case 3:
                return $lang ? "Среда" : "Wednesday";
            case 4:
                return $lang ? "Четверг" : "Thursday";
            case 5:
                return $lang ? "Пятница" : "Friday";
            case 6:
                return $lang ? "Суббота" : "Saturday";
        }
        return "???";
    }

    /**
     * Получить номер части бекапа
     * @param $filename
     *
     * @return false|int
     */
    public static function getPartNumber($filename) {
        preg_match("^-part[0-9]{1,}^", $filename, $matches);

        if (empty($matches)) {
            return false;
        }
        $result = str_replace("-part", "", $matches[0]);

        return intval($result);
    }

    /**
     * Удалить номер части из имени файла
     * @param $filename
     *
     * @return array|string|string[]|null
     */
    public static function removePartNumInFilename($filename) {
        $result = preg_replace("^-part[0-9]{1,}^", "", $filename);
        return $result;
    }

    /**
     * Определить тип бекапа
     * @param $filename
     *
     * @return string
     * @throws BackupException
     */
    public static function getBackupType($filename) {
        if (strpos($filename, "db") === 0) {
            return "db";
        }
        if (strpos($filename, "file") === 0) {
            return "file";
        }
        if (strpos($filename, "clients") === 0) {
            return "clients";
        }

        throw new BackupException("Не возможно определить тип бекапа", ["filename" => $filename]);
    }

    /**
     * Получить все папки(без файлов) из директорий в $allowed_dirs
     * @param $allowed_dirs
     * @return array
     */
    public static function getAllBackupFolders($allowed_dirs) {
        $folders = [];
        foreach ($allowed_dirs as $allowedDir) {
            $dirs = System::get_dir_files(ROOT.$allowedDir, false, true, true);
            array_push($dirs, ROOT.$allowedDir);
            $folders = array_merge($folders, $dirs);
        }

        usort($folders, function ($a,$b) {
            if (strlen($a) == strlen($b)) {}
            return (strlen($a) < strlen( $b)) ? -1 : 1;
        });

        return $folders;
    }

    /**
     * Получить все папки(без файлов) из директорий в $allowed_dirs
     * @param $allowed_dirs
     * @return array
     */
    public static function getAllBackupFiles($allowed_dirs) {
        $files = [];
        foreach ($allowed_dirs as $allowedDir) {
            $dirs = System::get_dir_files(ROOT.$allowedDir, true, false, false);
            $files = array_merge($files, $dirs);
        }
        return $files;
    }

    /**
     * Получить дату следующей сработки задания
     *
     * @param int $now
     * @param $taskData
     *
     * @return false|int
     */
    public static function getNextActionTime(int $now, $taskData) {
        if ($taskData["period"]["type"] == 3) {
            $plusDays = strtotime('tomorrow', $now);
        } else {
            $plusDays = strtotime('next '.self::convertDayWeek($taskData["period"]["day"], false), $now);
        }

        $time = explode(":", $taskData["period"]["time"]);
        $nextTime = strtotime("+ ".$time[0]." hours ".$time[1]." minutes", $plusDays);

        return $nextTime;
    }

    public static function clearTmp() {
        $tempPath = ROOT."/tmp/";
        @self::deleteTmpFiles($tempPath);
        mkdir($tempPath);
        file_put_contents($tempPath.".gitignore", "*");
        BackupTables::truncateProgressTable();
    }

    private static function deleteTmpFiles($path) {
        if (is_file($path)) return unlink($path);
        if (is_dir($path)) {
            foreach(scandir($path) as $p) if (($p!='.') && ($p!='..'))
                self::deleteTmpFiles($path.DIRECTORY_SEPARATOR.$p);
            return rmdir($path);
        }
        return false;
    }

}