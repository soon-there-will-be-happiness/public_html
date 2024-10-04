<?php

class BackupCronHandler {

    const DEFAULT_PHP_PATH = "php";
    const CRONDIR = ROOT."/extensions/autobackup/task/autobackup_cron.php";
    const RESTORETASKDIR = ROOT."/extensions/autobackup/task/restore_copy.php";

    static $autobackupExtName = "autobackup";

    static $issetTaskIdArgument = false;
    static $issetUidArgument = false;

    static $isSmart = false;
    static $smartType = false;
    static $taskid = false;

    /** @var bool Уникальный айди запуска. Используется для формирования прогресса по выполнению, для задач где нужно отображать прогресс */
    static $uid = false;



    /** Инициализировать крон */
    public static function init($argv, $max_exec_time = 7200) {
        $args = getopt("", [
            "smart", "type:", "taskid:", "uid:"
        ]);

        if (isset($args["smart"])) {
            self::$isSmart = true;
        }
        if (isset($args['type'])) {
            self::$smartType = $args['type'];
        }
        if (isset($args['taskid'])) {
            self::$taskid = $args['taskid'];
        }
        if (isset($args['uid'])) {
            BackupProgress::$writeProgress = true;
            self::$uid = $args['uid'];
        }

        self::enableLongWaitForQueries($max_exec_time);
    }

    /**
     * Разрешить долгое ожидание для бд
     * @param $max_exec_time
     */
    public static function enableLongWaitForQueries($max_exec_time) {
        Db::getConnection()->prepare("SET SESSION interactive_timeout = $max_exec_time")->execute();
        Db::getConnection()->prepare("SET SESSION wait_timeout = $max_exec_time")->execute();
    }

    /** Отправить сообщение по емайлам из настроек */
    public static function sendMessage($extSettings, $message, $header = "Отчёт по резервному копированию") {
        if (@$extSettings['sendEmail'] == 0) {
            return false;
        }

        $emails = BackupMail::getEmailsToSend($extSettings['emails_to_report'] ?? []);
        $bMail = new BackupMail($emails);

        $bMail->setMessage($header, $message);
        $bMail->sendMessage();
    }


    /**
     * Получить настройки расширения автобекапов
     * @return mixed
     */
    public static function getExtSettings() {
        return json_decode(System::getExtensionSetting(self::$autobackupExtName), true);
    }

    /**
     * Получить размер части копии
     * @param $extSettings
     *
     * @return int|mixed
     */
    public static function getPartSize($extSettings) {
        return $extSettings['part_size'] ?? 100000000;
    }

    /**
     * Получить копии для крона
     *
     * @param false $taskid
     * @param false $isSmart
     *
     * @return array|false
     */
    public static function getTasks($taskid = false, $isSmart = false) {

        if ($isSmart) {
            return [ SmartBackup::getSmartTask(self::$smartType) ];
        }

        if ($taskid) {
            $task = BackupTables::getBackupTasksById($taskid, 1);
            if (!$task) {
                die("Задание не найдено");
            }

            $task['period'] = json_decode($task['period'], true);
            $tasks[] = $task;
        } else {
            $tasks = BackupTables::getActualTasks(time());
        }

        return $tasks;
    }


    /** Записать сообщение о запуске/ошибке в кронлог */
    public static function writeStartupOrErrorInfoToCronlog($autobackupExtName, $STARTTIME, $mess = null) {
        $result = Db::getConnection()->prepare("INSERT INTO " . PREFICS . "cron_logs(`jobs_cron`, `last_run`, `text_error`) VALUES(:name_jobs, :last_run, :error)  ON DUPLICATE KEY UPDATE `last_run` = :last_run, `text_error` = :error");

        $result->bindParam(':name_jobs', $autobackupExtName, PDO::PARAM_STR);
        $result->bindParam(':last_run', $STARTTIME, PDO::PARAM_INT);
        $mess = null;
        $result->bindParam(':error', $mess, PDO::PARAM_STR || PDO::PARAM_NULL);

        return $result->execute();
    }


    public static function getPhp() {
        $settings = self::getExtSettings();

        if (empty($settings['php_path'])) {
            return self::DEFAULT_PHP_PATH;
        }

        return $settings['php_path'];
    }
}