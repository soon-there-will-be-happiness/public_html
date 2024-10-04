<?php defined('BILLINGMASTER') or die;

class adminAutopilotController{

    /**
     * НАСТРОЙКИ РАСШИРЕНИЯ
     */
    public static function actionSettings() {

        $status = Autopilot::getSettings('status')['status'];

        $service = Connect::getServiceByName('vkontakte');

        System::checkPermission('show_users');
        
        $setting = System::getSetting();

        if (isset($_POST['save'], $_POST['status']) && System::checkToken()) {

            System::checkPermission('change_users');

            $status = $_POST['status'];

            $edit = Autopilot::setStatus($status);
        }

        $title = 'Расширения - настройки Автопилот';
        require_once (__DIR__ . '/../views/admin/settings.php');
    }
}