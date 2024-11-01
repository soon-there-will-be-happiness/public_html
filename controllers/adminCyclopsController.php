<?php

class adminCyclopsController extends AdminBase {
    public function actionPayments() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }
        $setting = System::getSetting();
        $filters = [
            "amount" => isset($_GET['amount']) && $_GET['amount'] != 0 ? 1 : 0,
            "identify" => isset($_GET['identify']) && $_GET['identify'] == 0 ? $_GET['identify'] : false,
        ];
        $page = $_GET['page'] ?? 1;

        $logs = Cyclops::getPayments($filters, $page, $setting['show_items']);//::listPayments($filters, $page, $setting['show_items']);
        $identifies =[true,false];
        $totalPages = $logs['pages']['total'];
        $logs = $logs['logs'];


        $pagination = new Pagination($totalPages, $page, $setting['show_items']);
        $title = 'Логи Cyclops payments - список';
        require_once (ROOT . '/template/admin/views/cyclops/logs_payments_list.php');
    }
}