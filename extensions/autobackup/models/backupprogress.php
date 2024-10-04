<?php

class BackupProgress {

    const TABLE = PREFICS."backup_progress";
    const ACTION_TYPE = "backuping";
    const ACTIONS = [
        "init" => "Задача выполняется",

        "make_files_archive" => "Копируем файлы",
        "upload_files_archive" => "Загружаем бекап файлов в хранилища",

        "make_db_archive" => "Копируем базу данных",
        "upload_db_archive" => "Загружаем бекап базы данных в хранилища",

        "make_clients_archive" => "Копируем базу клиентов",
        "upload_clients_archive" => "Загружаем базу клиентов в хранилища",

        "finished" => "Задача завершена!",
    ];

    static $writeProgress = false;
    protected static $obj;

    protected $task_id;
    protected $start_time;
    protected $uid;

    protected $executed_actions = [];


    public static function progress($task = null, $start_time = null, $uid = null) {
        if (!empty(self::$obj)) {
            return self::$obj;
        }

        if ($task == null || $start_time == null) {
            throw new BackupException("Ошибка инициализации класса backupprogress", ["task" => $task, "start_time" => $start_time, "uid" => $uid]);
        }

        return self::$obj = new self($task, $start_time, $uid);
    }

    public static function unsetObj() {
        return self::$obj = null;
    }

    /**
     * @param $task
     * @param $start_time
     * @param null $uid
     *
     * @throws BackupException
     */
    protected function __construct($task, $start_time, $uid = null) {
        $this->task_id = $task['id'];
        $this->start_time = $start_time;
        $this->uid = $uid ?? self::generateUid($start_time, $this->task_id);
        $this->setActionAsExecutable("init");
    }

    /**
     * Записать действие
     * @param $action
     * @param int $is_error
     * @param string $message
     *
     * @return bool
     * @throws BackupException
     */
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

    public function finishTask($errored = 0, $message = "") {
        $result = $this->setActionAsExecutable("finished", $errored, $message);
        self::unsetObj();
        return $result;
    }

    /**
     * Сохранить действие в бд
     *
     * @param $uid
     * @param $task_id
     * @param $start_time
     * @param $executing_action
     * @param $is_error
     * @param $executing_time
     * @param $message
     *
     * @return bool
     */
    public static function addExecutableActionInDb($uid, $task_id, $start_time, $executing_action, $is_error, $executing_time, $message) {
        if (!self::$writeProgress) {
            return true;
        }

        $result = Db::getConnection()->prepare(
        "INSERT INTO `".self::TABLE."` (uid, task_id, start_time, executing_action, is_error, executing_time, message, action_type) 
                      VALUES (:uid, :task_id, :start_time, :executing_action, :is_error, :executing_time, :message, :action_type)"
        );

        $result->bindParam(':uid', $uid);
        $result->bindParam(':task_id', $task_id);
        $result->bindParam(':start_time', $start_time);
        $result->bindParam(':executing_action', $executing_action);
        $result->bindParam(':is_error', $is_error);
        $result->bindParam(':executing_time', $executing_time);
        $result->bindParam(':message', $message);

        $ACTIONTYPE = self::ACTION_TYPE;
        $result->bindParam(':action_type', $ACTIONTYPE);

        return $result->execute();
    }


    /**
     * Создать уникальный айди для прогресса
     * @param $time
     * @param $task_id
     *
     * @return string
     */
    public static function generateUid($time, $task_id) {
        $microtime = microtime(true);
        return sha1("$microtime|$time|task$task_id");
    }

}