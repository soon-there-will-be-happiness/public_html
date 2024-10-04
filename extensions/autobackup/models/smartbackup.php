<?php


class SmartBackup {

    const TYPE_BEFORE_UPDATE = 0;
    const TYPE_BEFORE_IMPORT = 1;

    /** @var int время, в течении которого умный бекап актуален */
    const SMART_BACKUP_RELEVANCE_DURATION = 600;

    /** @var SmartBackup */
    private static $singleton;

    public static function backup(): SmartBackup {
        if (empty(self::$singleton)) {
            return self::$singleton = new self();
        }
        return self::$singleton;
    }


    private $type = 0;
    private $uid = "";
    private $needRedirect = true;

    private $backupBeforeUpdate = 0;
    private $backupBeforeImport = 0;

    private function __construct() {
        $this->uid = BackupProgress::generateUid(time(), 0);
        $settings = BackupCronHandler::getExtSettings();

        $this->backupBeforeImport = $settings['smart_backup_import'] ?? $this->backupBeforeImport;
        $this->backupBeforeUpdate = $settings['smart_backup_update'] ?? $this->backupBeforeUpdate;
    }

    /**
     * Установить тип бекапа
     *
     * @param $type - константа этого класса - TYPE_*
     *
     * @return $this
     * @throws BackupException
     */
    public function setType($type) {
        $validTypes = [self::TYPE_BEFORE_UPDATE, self::TYPE_BEFORE_IMPORT];

        if (!in_array($type, $validTypes)) {
            throw new BackupException("Не правильно указан тип для умного бекапа", ["type" => $type]);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Получить айди запущенного задания
     * @return string
     */
    public function getUid() {
        return $this->uid;
    }


    /**
     * Запустить бекап, но проверить, если копия была создана давно, или вовсе никогда не создавалась
     *
     * @return $this
     */
    public function runIfNeed() {
        $copyExists = SmartBackup::getSmartCopyLast($this->type);
        if ($copyExists) {
            $this->needRedirect = false;
            return $this;
        }

        return $this->run();
    }


    /**
     * Запустить задание умного бекапа
     *
     * @return SmartBackup
     */
    public function run() {

        if ($this->type == self::TYPE_BEFORE_UPDATE && $this->backupBeforeUpdate == 0) {
            $this->needRedirect = false;
            return $this;
        }
        if ($this->type == self::TYPE_BEFORE_IMPORT && $this->backupBeforeImport == 0) {
            $this->needRedirect = false;
            return $this;
        }

        $uid = $this->execBackup();

        return $this;
    }

    /**
     * Переадресовать юзера на страницу задания, если needRedirect = true
     *
     * @param string $returnUrl - если указать url, то на странице задания будет ссылка для возврата
     *
     * @return bool|void
     */
    public function redirect($returnUrl = "") {
        if (!$this->needRedirect) {
            return true;
        }

        $param = !empty($returnUrl) ? "&back=$returnUrl" : "";
        $url = "/admin/autobackup/progress/0?smart=true$param&type=$this->type&uid=$this->uid";

        sleep(5);//нужен для того, чтобы задание точно было запушено

        return System::redirectUrl($url);
    }

    /**
     * Запуск бекапа
     *
     * @return string
     */
    private function execBackup() {
        $params = " --smart --type=" . $this->type . " --uid=" . $this->uid;
        $cmd = BackupCronHandler::getPhp()." ".BackupCronHandler::CRONDIR.$params;
        self::runAsync($cmd);

        return $this->uid;
    }

    /**
     * Запустить команду асинхронно (в фоне), не дожидаясь окончания ее работы
     *
     * @param $cmd
     */
    public static function runAsync($cmd) {
        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen( "start cmd /c ".$cmd, 'r'));
        } else {
            exec($cmd.' >/dev/null 2>&1 &');//для unix
        }
    }


    public function setDefaults() {
        $this->type = 0;
        $this->uid = BackupProgress::generateUid(time(), 0);
    }

    /**
     * Получить название бекапа из его типа
     *
     * @param $type
     *
     * @return string
     */
    public static function getNameByType($type) {
        switch ($type) {

            case self::TYPE_BEFORE_UPDATE:
                return "Перед обновлением";

            case self::TYPE_BEFORE_IMPORT:
                return "Перед импортом";

            default:
                return "";
        }
    }

    public static function getSmartTask($type) {
        $name = "Умный бэкап. ".SmartBackup::getNameByType($type);

        $storages = BackupCronHandler::getExtSettings()['smart_backup_storages'] ?? [0];

        return [
            "id" => 0,
            "name" => $name,
            "desc" => "",
            "period" => ["type" => 3, "day" => 1, "time" => "00:00"],
            "next_action" => 1,
            "bd_enable" => true,
            "files_enable" => false,
            "clients_enable" => false,
            "divide_to_parts" => false,
            "folders_include" => "[]",
            "folders_exclude" => "[]",
            "files_exclude" => "[]",
            "send_notif" => 1,
            "last_run" => 0,
            "amount_copies" => 3,
            "last_error_data" => null,
            "status" => true,
            "selected_storages" => json_encode($storages),
        ];
    }

    public static function getSmartCopyLast($type) {
        $minimalTime = time() - self::SMART_BACKUP_RELEVANCE_DURATION;
        $result = Db::getConnection()->query("SELECT * FROM `".PREFICS."backup_copys` 
            WHERE `date` > '$minimalTime' AND `task_id` = 0 AND `is_smart` = 1 AND `smart_type` = '$type' ORDER by `id` LIMIT 1;
        ");

        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public static function getDefaultStorage() {
        return [
            'id'     => 0,
            "title"  => "Локальное хранилище",
            "type"   => 4,
            "params" => '{"ftp_ip":"","ftp_port":"21","ftp_login":"","ftp_pass":"","ftp_path":"","yandex_disk":"","dropbox_token":"","dropbox_refresh_token":"","s3_type":"0","s3_endpoint":"","s3_bucket":"","s3_keyid":"","s3_secret":"","local_path":"\/load\/backups\/"}',
            "status" => 1,
        ];
    }
}