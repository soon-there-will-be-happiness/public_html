<?php defined('BILLINGMASTER') or die;

class adminSessionController extends AdminBase {

    public function actionCheck() {
        $logged = self::checkLogged();

        System::jsonAnswer(
            true,
            '',
            [
                'authorization' => (bool) $logged,
                'admin_token' => $logged ? $_SESSION['admin_token'] : '',
                'server_time' => time(),
                'start_time' => is_bool($logged) ? '' : $logged,
                'end_time' => is_bool($logged) ? '' : $logged + ini_get('session.gc_maxlifetime')
            ]
        );
    }

    public function actionLoginForm(){
        if (isset($_POST["login"], $_POST["pass"])) {
            $login = trim($_POST["login"]);
            $pass = trim($_POST["pass"]);
            
            // проверяет введённые данные, если верны, запускаем функцию Auth
            $check = AdminBase::checkUser($login, $pass);
            if ($check) {
                AdminBase::Auth($check["user_id"], $check["user_name"]);
                System::jsonAnswer(true);
            } else {
                System::jsonAnswer(false);

            }
        }
        
        $setting = System::getMainSetting();
        $security_key = $setting["security_key"];

        require_once ROOT . "/template/admin/enter.php";
    }


    public static function checkLogged() {
        if (isset($_SESSION["admin_user"]))
            return $_SESSION['started'] ?? true;
        
        return false;
    }
    
}
