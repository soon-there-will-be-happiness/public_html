<?php


class RestoreProgress extends BackupProgress {

    const ACTION_TYPE = "restoring";

    static $tmpDir = ROOT."/tmp/progress/";

    static $writeProgress = true;

    protected static $obj;

    const ACTIONS = [
        "init" => "Задача выполняется",

        "download_files_archive" => "Загружаем бекап файлов",
        "unpacking_files_archive" => "Устанавливаем бекап файлов",

        "download_db_archive" => "Загружаем бекап базы данных",
        "unpacking_db_archive" => "Устанавливаем бекап базы данных",

        "finished" => "-- Задача завершена! --",
    ];

    public static function addExecutableActionInDb($uid, $task_id, $start_time, $executing_action, $is_error, $executing_time, $message) {
        if (!self::$writeProgress) {
            return true;
        }

        $action = json_encode([
            "uid" => $uid,
            "task_id" => $task_id,
            "start_time" => $start_time,
            "executing_action" => "$executing_action",
            "is_error" => $is_error,
            "executing_time" => $executing_time,
            "message" => $message,
        ]);

        $filename = $uid;

        if (!is_dir(self::$tmpDir)) {
            mkdir(self::$tmpDir);
        }

        return (bool) file_put_contents(self::$tmpDir.$filename, $action."\n", FILE_APPEND);
    }

    public function setActionAsExecutable($action, $is_error = 0, $message = "") {
        if (!in_array($action, array_keys(self::ACTIONS))) {
            throw new BackupException("Не существует действия $action", ["right_actions" => self::ACTIONS, "current" => $action]);
        }

        if (in_array($action, $this->executed_actions)) {
            return true;
        }

        array_push($this->executed_actions, $action);

        return self::addExecutableActionInDb($this->uid, $this->task_id, $this->start_time, $action, $is_error, time() ,$message);
    }

    public static function progress($task = null, $start_time = null, $uid = null) {
        if (!empty(self::$obj)) {
            return self::$obj;
        }

        if ($task == null || $start_time == null) {
            throw new BackupException("Ошибка инициализации класса backupprogress", ["task" => $task, "start_time" => $start_time, "uid" => $uid]);
        }

        return self::$obj = new RestoreProgress($task, $start_time, $uid);
    }

    /**
     * Получить ход выполнения
     *
     * @param $uid
     *
     * @return array|false
     */
    public static function getProgressByUid($uid) {
        $filename = self::$tmpDir.$uid;

        if (!is_file($filename)) {
            return false;
        }

        $file = file_get_contents($filename);

        if (empty($file)) {
            return false;
        }

        $fullProgress = explode("\n", $file);

        $resultProgress = [];
        $id = 0;
        foreach ($fullProgress as $progressData) {
            if (empty($progressData)) {
                continue;
            }

            $json = json_decode($progressData, true);

            if (is_null($json)) {
                continue;
            }

            $resultProgress[] = array_merge($json, ["id" => $id]);
            $id++;
        }

        return $resultProgress;
    }


    /**
     * Посчитать прогресс выполнения
     *
     * @param array $progress - выполненные действия
     * @param int | string $type тип бекапа: 0, "db", "files"
     * @param array $copys копии для восстановления
     *
     * @return float|int
     * @throws BackupException
     */
    public static function calculate($progress, $type = 0, $copys = []) {
        $maxCountActions = count(self::ACTIONS);
        $minCountActions = 2;

        $currentTaskTotalActions = self::getTotalActions($minCountActions, $type, $copys);

        if (!$progress) {
            $progress = [];
        }

        $currentTaskActionsCount = count($progress) ?? 0;

        $progressInPercent = round($currentTaskActionsCount / $currentTaskTotalActions, 2) * 100;
        if ($progressInPercent > 100) {
            $progressInPercent = 100;
        }

        return $progressInPercent;
    }

    /**
     * Получить количество действий
     *
     * @param int $minCountActions
     * @param int $type
     * @param array $copys
     *
     * @return int|mixed
     * @throws BackupException
     */
    public static function getTotalActions($minCountActions = 2, $type = 0, $copys = []) {
        $totalActions = $minCountActions;

        if ($type == 0) {//восстанавливать все
            $restoreFiles = true;
            $restoreDb = true;
        }
        if ($type == "db") {
            $restoreDb = true;
        }
        if ($type == "db") {
            $restoreFiles = true;
        }

        $copysHaveDb = false;
        $copysHaveFiles = false;

        foreach ($copys as $copy) {
            $copyType = BackupTables::getBackupType($copy['filename']);
            if ($copyType == "db") {
                $copysHaveDb = true;
            } elseif ($copyType == "file") {
                $copysHaveFiles = true;
            }
        }


        if ($copysHaveFiles) {
            $totalActions += 2;
        }
        if ($copysHaveDb) {
            $totalActions += 2;
        }


        return $totalActions;
    }

}