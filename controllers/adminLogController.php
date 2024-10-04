<?php defined('BILLINGMASTER') or die;

class adminLogController extends AdminBase {

    public function actionIndex() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }
        $setting = System::getSetting();


        $filters = [
            "in_arhive" => isset($_GET['in_arhive']) && $_GET['in_arhive'] != 0 ? 1 : 0,
            "type" => isset($_GET['type']) && $_GET['type'] == 0 ? $_GET['type'] : false,
            "level" => isset($_GET['level']) && $_GET['level'] != "null" ? $_GET['level'] : false,
        ];
        $page = $_GET['page'] ?? 1;

        $logs = LogTable::getLastLogs($filters, $page, $setting['show_items']);

        $types = LogTable::getLogTypes();
        $levels = [0, 1, 2, 3, 4, 5, 6, 7];

        $totalPages = $logs['pages']['total'];
        $logs = $logs['logs'];


        $pagination = new Pagination($totalPages, $page, $setting['show_items']);
        $title = 'Логи - список';
        require_once (ROOT . '/template/admin/views/logs/logs_list.php');
    }

    public function actionShowLog($id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }

        $log = LogTable::getLog($id);
        $log['context'] = json_decode($log['context'], true);

        $title = 'Лог - просмотр';
        require_once (ROOT . '/template/admin/views/logs/log_show.php');
    }

    public function actionChangeArhive($id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }
        $value = $_GET['to'];

        $res = LogTable::changeLogArchive($id, $value);

        System::redirectUrl("/admin/logs/$id", $res);
    }

    public function actionDeleteLog($id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }

        $res = LogTable::deleteLog($id);

        System::redirectUrl("/admin/logs/", $res);
    }


}
