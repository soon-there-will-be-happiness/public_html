<?php defined('BILLINGMASTER') or die;


class adminBackupApiController extends AdminBase {

    public function __construct() {
        require_once ROOT."/extensions/autobackup/config/autobackup_class_loader.php";
    }

    const MAX_WAITING_TIME_LONG_POLLING = 3;

    # Подсчет размера бекапа

    public function actionGetBackupSize() {
        if (empty($_REQUEST) || !isset($_REQUEST['json'])) {
            self::response(["message" => "Ошибка в запросе"]);
        }

        $data = json_decode($_REQUEST['json'], true);
        if (empty($data)) {
            self::response(["message" => "Ошибка в запросе"]);
        }
        $enabled = $data['enabled'] ?? null;
        $excludes = $data['exclude'] ?? null;

        if (!$enabled || !$excludes) {
            self::response(["message" => "Ошибка в запросе"]);
        }

        $excludes = array_merge($excludes['folders'], $excludes['files']);
        $excludes = array_filter($excludes,function ($a) {
            return !empty($a);
        });
        $dirSize = $enabled['files'] ? self::getFilesBackupSizeWithExcludes($excludes) : 0;
        $dbSize = $enabled['db'] ? self::getDbSize() : 0;
        $clientsSize = $enabled['clients'] ? self::getClientsSize() : 0;

        return self::response([
            "files" => $dirSize * 0.45,
            "db" => $dbSize* 0.5,
            "clients" => $clientsSize,
        ], 201);
    }

    public static function getFilesBackupSizeWithExcludes(array $excludes = []){
        $hash = sha1("filesbackup_".implode(";", $excludes));

        $cache = null;//Cache::get($hash);

        if ($cache) {
            return $cache;
        }

        $dirsInRoot = BackupData::getFoldersInRoot();
        $dirSize = 0;
        foreach ($dirsInRoot as $dir) {
            $dirFiles = System::get_dir_files(ROOT . $dir);
            foreach ($dirFiles as $key => $file) {//Убрать root из файла
                $file = str_replace(ROOT, "", $file);

                foreach ($excludes as $item) {//Не проверяем файлы, если они соответствуют исключениям
                    if(strpos($file, $item) === 0) {
                        continue 2;
                    }
                }
                $dirSize += filesize(ROOT.$file);
            }
        }

        Cache::set($hash, $dirSize, 300);

        return $dirSize;
    }

    public static function getDbSize() {
        $cache = Cache::get("backup_dbsize");

        if ($cache) {
            return $cache;
        }

        $db = Db::getConnection()->query("SHOW TABLE STATUS");
        $dbSize = 0;
        $result = $db->fetchAll();
        foreach ($result as $Row){
            $dbSize += $Row["Data_length"] + $Row["Index_length"];
        }
        Cache::set("backup_dbsize", $dbSize, 300);

        return $dbSize;
    }

    public static function getClientsSize() {
        $tableName = PREFICS."users";
        $db = Db::getConnection()->query("SHOW TABLE STATUS WHERE `Name` = '$tableName'");
        $result = $db->fetchAll();
        $tableSize = $result[0]["Data_length"] + $result[0]["Index_length"];
        return $tableSize;
    }

    # Прогресс выполнения

    public function actionGetLastBackupTaskProgressData($task_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }
        if (!isset($_GET['lastid'])) {
            self::response(["message" => "не указан параметр lastid"], 422);
        }
        $uid = null;
        if (isset($_GET['uid'])) {
            $uid = $_GET['uid'];
        }

        $last_id = intval($_GET['lastid']);

        $task = BackupTables::getTaskById($task_id);
        if (!$task) {
            self::response(["message" => "Задание не найдено"], 404);
        }
        $maxtime = time() + self::MAX_WAITING_TIME_LONG_POLLING;
        while (true) {
            if (time() > $maxtime) {
                self::response("", 204);
            }

            $tasks_progress = BackupShowProgress::getProgressToTaskByIdLast($task_id, $last_id, $uid);
            if ($tasks_progress) {
                $sorted = [];

                $allProgress = BackupShowProgress::getProgressToTaskById($task_id);
                $percent = BackupShowProgress::calculateTaskProgress($task, $allProgress);

                foreach ($tasks_progress as $progress) {
                    if ($progress['executing_action'] != "finished") {
                        $progress['executing_action'] = BackupProgress::ACTIONS[$progress['executing_action']];
                    } elseif ($progress['is_error'] == 1) {
                        $progress['executing_action'] = "Задача не завершена";
                    } else {
                        $progress['executing_action'] = BackupProgress::ACTIONS[$progress['executing_action']];
                    }

                    $sorted[] = $progress;
                }

                $tasks_progress = $sorted;
                self::response(["data" => $tasks_progress, "progress" => $percent], 201);
            }
            sleep(1);
        }
    }

    # Восстановление из бекапа

    public function actionStartBackupRestore() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $err = $this->validateRestoreRequest();
        if ($err !== true) {
            return self::response(["message" => $err], 422);
        }

        $request = json_decode($_REQUEST['request'], true);


        if ($request['task_id'] == 0) {
            $task = SmartBackup::getSmartTask(0);
        } else {
            $task = BackupTables::getTaskById($request['task_id']);
        }

        if (!$task) {
            return self::response(["message" => "Такого задания не существует"], 404);
        }

        $copys = BackupTables::findCopy($request['task_id'], $request['backup_date'], $request['restore_type']);
        if (!$copys) {
            return self::response(["message" => "Копии не найдены"], 404);
        }

        $uid = BackupRestorer::runRestore($request['task_id'], $request['backup_date'], $request['restore_type']);

        sleep(3);//Нужно чтобы задача точно была запущена
        self::response([
            "message" => "Восстановление запущено",
            "uid" => $uid
        ], 201);
    }

    public function validateRestoreRequest() {
        if (!isset($_REQUEST['request'])) {
            return "Не указан параметр request";
        }

        $request = json_decode($_REQUEST['request'], true);

        if (!isset($request['from_bucket'])) {
            return "Не указан параметр from_bucket";
        }

        if (!isset($request['task_id'])) {
            return "Не указан параметр task_id";
        }

        if (!isset($request['backup_date'])) {
            return "Не указан параметр backup_date";
        }

        if (!isset($request['restore_type'])) {
            return "Не указан параметр restore_type";
        }

        return true;
    }

    public function actionRestoreProgress($uid) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $data = json_decode($_REQUEST['data'] ?? "[]", true);
        if (empty($data)) {
            ErrorPage::return404();
        }

        $task = BackupTables::getTaskById($data['task_id']);
        $copys = BackupTables::findCopy($data['task_id'], $data['backup_date'], $data['restore_type']);

        $progressActions = RestoreProgress::getProgressByUid($uid);

        $progress = RestoreProgress::calculate($progressActions, $data['restore_type'], $copys);

        return require_once (ROOT . '/extensions/autobackup/views/admin/restore_progress.php');
    }

    public function actionGetLastBackupTaskProgress($uid) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $last_id = $_GET['last_id'] ?? 0;

        $data = json_decode($_REQUEST['data'] ?? "[]", true);
        if (empty($data)) {
            self::response(["message" => "data не существует"], 404);
        }

        $task = BackupTables::getTaskById($data['task_id']);
        if (empty($data)) {
            self::response(["message" => "Задания не существует"], 404);
        }

        $copys = BackupTables::findCopy($data['task_id'], $data['backup_date'], $data['restore_type']);
        if (empty($copys)) {
            self::response(["message" => "Копий не существует"], 404);
        }

        $maxTime = time() + self::MAX_WAITING_TIME_LONG_POLLING;

        while (true) {

            if (time() > $maxTime) {
                return self::response("", 204);
            }

            $progressActions = RestoreProgress::getProgressByUid($uid);
            if (empty($copys)) {
                self::response(["message" => "Прогресса по заданию не существует. Возможно, оно не было запущенно"], 404);
            }

            $progressInPercent = RestoreProgress::calculate($progressActions, $data['restore_type'], $copys);

            $newActions = [];
            foreach ($progressActions as $key => $action) {
                if ($last_id < $action['id']) {
                    if ($action['executing_action'] != "finished") {
                        $action['executing_action'] = BackupProgress::ACTIONS[$action['executing_action']];
                    } elseif ($action['is_error'] == 1) {
                        $action['executing_action'] = "Задача не завершена";
                    } else {
                        $action['executing_action'] = BackupProgress::ACTIONS[$action['executing_action']];
                    }

                    $newActions[] = $action;
                }
            }
            if (empty($newActions)) {
                sleep(1);
                continue;
            }

            return self::response([
                "progress" => $progressInPercent,
                "data"     => $newActions,
            ], 201);
        }
    }


    # Умный бекап

    public function actionRunSmartBackup() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }
        if (isset($_SERVER['HTTP_REFERER'])) {
            $back = $_SERVER['HTTP_REFERER'];
        } else {
            $back = "/admin/";
        }

        SmartBackup::backup()->setType(SmartBackup::TYPE_BEFORE_UPDATE)->runIfNeed()->redirect($back);

        System::redirectUrl($back, true);
    }



    /**
     * Отдать json||str ответ
     *
     * @param string | array | object $response
     * @param int $httpCode
     */
    public static function response($response, $httpCode = 200) {
        if (is_array($response) || is_object($response)) {
            header("Content-Type: application/json");
            $response = json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        http_response_code($httpCode);
        echo $response;
        exit();
    }
}