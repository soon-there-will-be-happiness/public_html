<?php

class adminCyclopsController extends AdminBase {
    public function actionPayments() {
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

        $logs = Cyclops::getPayments();//::listPayments($filters, $page, $setting['show_items']);

        $totalPages = $logs['pages']['total'];
        $logs = $logs['logs'];


        $pagination = new Pagination($totalPages, $page, $setting['show_items']);
        $title = 'Логи Cyclops payments - список';
        require_once (ROOT . '/template/admin/views/cyclops/logs_payments_list.php');
    }
}