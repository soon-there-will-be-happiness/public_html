<?php defined('BILLINGMASTER') or die;

class adminConnectController {

    // Страница настроек connect
    public static function actionSettings(){
        System::checkPermission('show_connect');

        if(isset($_POST['save'], $_POST['params']) && !empty($_POST['params'])){

            if(!System::issetPermission('change_connect') && !System::checkToken()) {
                System::setNotif('perm_error', 'Обвновления не сохранены.');
            }

            $res = Connect::setParams($_POST['params']);

            System::setNotif($res);

            if ($res && isset($_POST['set_settings_default']) && $_POST['set_settings_default'] == 1 && Connect::setDefaultSettingForAll())
                System::setNotif(true, 'Настройки применены ко всем пользователям.');

        }

        $params = Connect::getParams();
        $services = Connect::getAllServices();

        $title='Расширения - Connect';
        require_once (__DIR__ . '/../views/admin/settings.php');

        return true;
    }

    // Получить форму telegram / вк
    public static function actionAjaxForm(string $get, $bot = false){
        if(System::issetPermission('show_connect') && $iss_perms = true);

        $setting = System::getSetting();

        if(isset($_POST['title'], $_POST['name'], $_POST['service_id'])
            && ($service = Connect::getServiceByID($_POST['service_id']))
            && ($file = __DIR__ . "/../services/{$get}/settings_modal.php")
        );

        $id = $_POST['service_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $title = $_POST['title'] ?? '';

        if($bot){
            require_once __DIR__ . '/../views/admin/modals/bot.php';

            return true;
        }

        require_once __DIR__ . "/../views/admin/modals/main.php";
        return true;
    }

    // Сохранить настройки tg/vk
    public static function actionAjaxSubmit(int $id, string $name){

        if(!System::issetPermission('change_connect') || !System::checkToken())
            return System::setNotif('perm_error', "У вас недостаточно прав для редактирования.");

        $data = $_POST;

        if (@ $data['service']['id'] == $id && @ $data['service']['name'] == $name){
            
            if(empty($data['params']))
                $data['params'] = [];

            if($method = Connect::getServiceMethod($name, 'updSetting'))
                $method($id, $data);
        } else {
            ErrorPage::return404();
        }

        return true;
    }

    // Сохранить настройки юзера в админке
    public static function actionUserSettingAjaxForm(int $sm_user_id){
        require __DIR__ . '/../views/site/lk/main.php';

        if (isset($_GET['submit']) && !empty($_POST) && System::issetPermission('change_users') && System::checkToken()) {
            $params = empty($_POST['params']) ? [] : $_POST['params'];

            if(Connect::updUserParams($sm_user_id, $params)){
                $services = Connect::getAllServices(1);
                $connect_user = Connect::getUserBySMID($sm_user_id);
                return row_line($services, $connect_user, false);
            }
        }

        $connect_user = [];
        $services = [];

        if(System::issetPermission('show_users')){
            $connect_user = Connect::getUserBySMID($sm_user_id);
            $services = Connect::getAllServices(1);
        }

        require __DIR__ . '/../views/admin/modals/user_edit.php';
        return true;
    }

}