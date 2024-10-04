<?php defined('BILLINGMASTER') or die;


class connectController{

    /**
     * Принять вебхук от сервиса
     * @param $name - имя сервиса
     *
     * @return bool
     */
    public function actionWebhook($name){
        $updates = file_get_contents("php://input");
        $bot = 'main';
        require_once __DIR__ . '/../services/bot_loader.php';

        return true;
    }


    /**
     * Обработка формы. Привязать/отвязать сервис
     *
     * @param string $name
     * @param string $action
     */
    public function actionProcess(string $name, string $action = 'auth'){
        if(($action == 'attach' || $action == 'unlink') && !empty($_GET['hash'])){
            $this->processAttachOrUnset($name, $action);
        }

        elseif ($action == 'auth' && ($method = Connect::getServiceMethod($name, 'authProcess'))){
            $this->processAuth($method);
        }
        else {
            ErrorPage::return404();
        }

        return true;
    }

    private function processAttachOrUnset($name, $action) {
        $base = base64_decode($_GET['hash']);
        $base = explode("-", $base);

        $timeout = 10;
        $message = System::Lang('ERROR') . ' ';

        if (!User::isAuth()) {
            $message .= 'Not authorized';
        }

        if (!is_numeric($base[0]) || $base[0] < time()) {
            $message .= 'Timeout';
        }

        if ($base[1] == $name && $base[2] == md5(' ' . $_SESSION['connect_token'])){
            $service_data = Connect::getServiceByName($name);

            if($action == 'unlink'){
                $unlinkResult = $this->processUnlink($name, $base, $service_data);
                $message .= $unlinkResult['message'];
                $html = $unlinkResult['html'];
                $timeout = $unlinkResult['timeout'];
            }

            if($action == 'attach'){
                $message = $this->processAttach($name, $base, $service_data, $message);
            }
        }

        require __DIR__ . '/../views/site/login/load_page.php';
    }

    private function processUnlink($name, $base, $service_data) {
        $confirm_code = md5($base[1] . 'confirm' . $base[2]);
        if (isset($_GET['confirm']) && $_GET['confirm'] == $confirm_code) {

            $res = Connect::unlinkServiceByHash($name, $base[2]);

            if ($res) {
                $timeout = 5;
                $message = System::Lang('USER_SUCCESS_MESS') . '!';
            }
        } elseif (isset($_GET['cansel'])) {
            $timeout = 1;
            $message = 'Ok! Go back...';
        } else {
            $timeout = 60;
            $message = System::Lang('UNLINK') . " {$service_data['title']}?";
            $html = "<div class='links'>
                <a href='?hash={$_GET['hash']}&cansel'>" . System::Lang('CANCEL') . "</a>
                <a class='red' href='?hash={$_GET['hash']}&confirm={$confirm_code}'>" . System::Lang('CONFIRM') . "</a>
            </div>";
        }

        return ["message" => $message, "html" => $html, "timeout" => $timeout];
    }

    private function processAttach($name, $base, $service_data, $message) {
        if ($service_data && isset($service_data['params']['auth_link'])) {

            $link = str_replace(['{hash}', 'cct1'], [$base[2], 'cct2'], $service_data['params']['auth_link']);

            System::setCookie('connect_attach', $name, $base[0] + 300, "/");
            System::setCookie('auth_hash', $_GET['hash'], $base[0] + 300, "/");
            //$res = setcookie('auth_hash', $_GET['hash'], $base[0]);

            System::redirectUrl($link);
        } else {
            return $message . 'Service';
        }

        return "";
    }

    private function processAuth($method) {
        if (empty($_COOKIE['auth_hash']) && empty($_GET['code'])) {
            $timeout = 15;
            $message = 'Error: timeout';
            require __DIR__ . '/../views/site/login/load_page.php';
        }

        elseif (isset($_GET['cansel'])) {
            $timeout = 1;
            $message = 'Ok! Go back...';
            require __DIR__ . '/../views/site/login/load_page.php';
        }

        else
            $method();
    }


    /**
     * Проверка авторизации
     * TODO: использовать long polling(ибо не хорошо каждую секунду отсылать запросы)
     * @param string $name
     */
    public function actionAuthLoad(string $name) {
        if (empty($_POST))
            ErrorPage::return404();

        if (!isset($_POST['hash'], $_POST['time'], $_POST['connect_token']) || $_POST['connect_token'] != @ $_SESSION['connect_token'])
            System::jsonAnswer(false, 'error');

        if (User::isAuth()) // уже авторизован
            System::jsonAnswer(true);

        $hash = $_POST['hash'];

        $res = Connect::GoAuth($hash);

        if ($res != 'success'){
            System::jsonAnswer(false, $res, ['hash' => base64_decode($hash)]);
            exit;
        }


        # проверка - была ли регистрация через сервис или нет
        $isRegister = false;
        $cookie = $_COOKIE['connect'];
        if ($cookie) {
            $json = base64_decode($_COOKIE['connect']);
        }

        if ($cookie && $json) {
            $userData = json_decode($json, true);
            if ($userData) {
                $userId = $userData['user_id'];
            }
            if ($userId) {
                $user = User::getUserById($userId);
                if ($user) {
                    if (strpos($user['email'], "@".$name.".com") !== false) {
                        $isRegister = true;
                    }
                }
            }
        }

        # ссылка для перенаправления
        if ($isRegister) {
            $_SESSION["registered_by_connect"] = true;
            $url = "/lk?registered_by_connect=1";
        } else {
            $url = User::redirectFromEnter(System::getSetting()['login_redirect']);
        }

        System::jsonAnswer(true, '', [
            'redirect' => $url
        ]);
    }

    /**
     * Получить ссылку на авторизацию
     *
     * @param $name
     *
     * Возвращает JSON со ссылкой для авторизации
     */
    public static function actionAuthLink($name){
        $data = $_POST;
        if (empty($data))
            ErrorPage::return404();

        if (!isset($data['time'], $data['serviceID'], $data['connect_token']) || $data['connect_token'] != @ $_SESSION['connect_token'])
            System::jsonAnswer(false);

        $service = Connect::getServiceByName($name);

        if (
            !$service
            || $service['service_id'] != $data['serviceID']
            || !($service['status'] == 1 && isset($service['types']['auth'], $service['params']['auth_link']) && @ $service['params']['auth'] == 1)
        )
            System::jsonAnswer(false);


        $life_time = time() + 90;

        $auth_link = $service['params']['auth_link'];
        $hash = base64_encode($life_time . '-' . md5($data['time'] . ':' . $data['connect_token']));
        $link = str_replace('{hash}', $hash, $auth_link);

        if (!empty($data['email'])){
            $rem_data = Connect::getRemUserData($name, $data['email']);
        }
        
        setcookie('auth_hash', $hash, $life_time, '/connect/authProcess');
        setcookie('ConnectAuthHash', $hash, $life_time, '/');

        if (!empty($data['email']))
            setcookie('auth_email', $data['email'], $life_time, '/connect/authProcess');

        System::jsonAnswer(true, '', 
            [
                'hash' => $hash,
                'link' => $link
            ]
        );
    }


    /**
     * Получить данные для лк
     *
     * @param null $name
     * @param false $lk
     */
    public static function actionAjax($name = null, $lk = false){
        $req = $_GET + $_POST;
        if (!$name || !isset($_SESSION['connect_token']) || empty($req['method']))
            ErrorPage::return404();

        $user_id = User::checkLogged();

        if (!$user_id)
            System::jsonAnswer(false, 'not authorized');

        if (!$db_key = Connect::getServiceKey($name))
            System::jsonAnswer(false, 'service not found');

        if (@ Connect::getServiceByName($name)['status'] != 1)
            System::jsonAnswer(false, 'disabled');

        $connect_user = Connect::getUserBySMID($user_id);

        if ((empty($_POST['method']) || $_POST['method'] == "check_attach") &&
            !empty($connect_user[$db_key]) && is_numeric($connect_user[$db_key]))
            System::jsonAnswer(true, 'connected', [
                'service_id' => $connect_user[$db_key]
            ]);

        if ($_POST['method'] == "check_attach")
            System::jsonAnswer(true, 'not attached', [
                'conn_lk' => @ Connect::getServiceByName($name)['params']['lk_conn'] == 1
            ]);

        if (!in_array($_POST['method'], ["attach", "unlink"]))
            System::jsonAnswer(false, 'method not found');

        $hash = md5(' ' . $_SESSION['connect_token']);

        if (!Connect::updUserAuthHash($user_id, $hash))
            System::jsonAnswer(false, 'SQL error');

        $life_time = time() + 90;
        $hash = base64_encode($life_time . '-' . $name . '-' . $hash);

        $link = System::getSetting()['script_url'] . "/connect/{$_POST['method']}/{$name}?hash={$hash}";
        System::jsonAnswer(true, '', 
            [
                'link' => $link
            ]
        );
    }

    /**
     * Показ/Сохранение формы в лк
     * @param $method
     */
    public function actionAjaxLk($method) {
        if (!$method || empty($_SESSION['connect_token']) || empty($_POST)) {
            ErrorPage::return404();
        }

        $user_id = User::checkLogged();
        if (!$user_id) {
            ErrorPage::return404();
        }

        $services = Connect::getAllServices(1);
        $connect_user = Connect::getUserBySMID($user_id);

        switch ($method) {
            case 'submit':
                $params = empty($_POST['params']) ? [] : $_POST['params'];
                require __DIR__ . '/../views/site/lk/main.php';
                if (Connect::updUserParams($user_id, $params)) {
                    return row_line($services, $connect_user);
                }
                break;

            case 'setting':
                $show = 1;
                require __DIR__ . '/../views/site/lk/main.php';
                break;

            default:
                ErrorPage::return404();
        }
    }


    /**
     * Проверить авторизацию
     */
    public function actionIsAuth(){
        if (User::isAuth()) // уже авторизован
            System::jsonAnswer(false);

        if (!isset($_COOKIE['ConnectAuthHash']))
            System::jsonAnswer(false);

        $res = Connect::GoAuth($_COOKIE['ConnectAuthHash']);

        unset($_COOKIE['ConnectAuthHash']);
        setcookie('ConnectAuthHash', '', 888888888, '/');

        if ($res != 'success'){
            System::jsonAnswer(false, $res);
            exit;
        }

        # ссылка для перенаправления
        $url = User::redirectFromEnter(System::getSetting()['login_redirect']); 

        System::jsonAnswer(true, '', [
                'redirect' => $url
        ]);
    }

}