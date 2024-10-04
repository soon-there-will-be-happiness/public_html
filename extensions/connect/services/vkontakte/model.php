<?php

namespace Connect\Vkontakte;
defined('BILLINGMASTER') or die;

class Model implements \Connect\Service
{

    # Шаблон ссылки авторизации
    public static $auth_link = "https://oauth.vk.com/authorize?redirect_uri={redirect_url}&v={v}&client_id={app_id}";
    public static $script_url = '/connect/authProcess/vkontakte';

    # идентификатор столбца в БД:
    public static $db_key = 'vk_id';

    # идентификатор столбца логина в БД users:
    public static $db_ukey = 'vk_url';


    /**
     * Обновить настройки сервиса
     * @param int   $id
     * @param array $data
     * @param bool  $notif
     * @return bool
     */
    public static function updSetting(int $id, array $data, bool $notif = true) {
        if (empty($data['service_params']['v'])) {
            $data['service_params']['v'] = '5.130';
        }

        $sp = $data['service_params'];
        $error = [];

        $data['params']['db_key'] = self::$db_key;

        $data['params']['auth_link'] = str_replace(['{redirect_url}', '{app_id}', '{v}'], [\System::getSetting()['script_url'] . self::$script_url, $sp['app_id'], $sp['v']], self::$auth_link);

        self::updateChatToken($sp, $data, $error);
        self::updateVkApp($sp, $data, $error);


        if ($data['params']['msg'] == 1 && !isset($data['service_params'], $data['service_params']['succ_chat'])) {
            $data['params']['msg'] = 0;
        }

        if ($data['params']['auth'] == 1 && !isset($data['service_params'], $data['service_params']['succ_app'])) {
            $data['params']['auth'] = 0;
        }

        if (@ $data['service_params']['succ_app'] || @ $data['service_params']['succ_chat']) {
            if ($data['enable'] == 1) {
                \Connect::updServiceStatus($id, 1);
            }
            else {
                \Connect::updServiceStatus($id, 0);
            }
        }
        else {
            \Connect::updServiceStatus($id, 2);
        }

        $res = \Connect::updServiceSettingByID($id, $data['service_params'], $data['params']);

        if ($notif && $res) {
            \System::setNotif(true, 'Настройки ВКонтакте сохранены!');
        }

        if ($notif && !empty($error)) {
            foreach ($error as $message) {
                \System::setNotif(false, $message, 120);
            }
        }

        return $res;
    }

    /**
     * Обновить токен чата вк
     * @param $serviceParams
     * @param &$data
     * @param &$error
     */
    public static function updateChatToken($serviceParams, &$data, &$error) {
        if (!isset($serviceParams['chat_token'], $serviceParams['group_id'])) {
            return;
        }

        $requestData = ['access_token' => @ $serviceParams['chat_token'], 'v' => @ $serviceParams['v'], 'group_id' => @ $serviceParams['group_id']];

        $info = self::requestVKApi('groups.getCallbackServers', $requestData);

        if (isset($info['error'])) {
            $errMsg = "Vk: chat token error" . $info['error']['error_msg'];
            $error[] = $errMsg;
            return;
        }

        $data['service_params']['succ_chat'] = 1;
    }

    /**
     * Обновить приложение вк
     * @param $serviceParams
     * @param $data
     * @param $error
     */
    public static function updateVkApp($serviceParams, &$data, &$error) {
        if (!isset($serviceParams['app_id'], $serviceParams['secret'], $serviceParams['service_key'])) {
            return;
        }

        $requestData = ['access_token' => @ $serviceParams['service_key'], 'v' => @ $serviceParams['v'],];

        $info = self::requestVKApi('apps.get', $requestData);

        if (isset($info['error'])) {
            $error[] = $errMsg = 'VK App error: ' . @$info['error']['error_msg'];
            return;
        }

        if (@$info['response']['items'][0]['id'] != $serviceParams['app_id']) {
            $error[] = $errMsg = 'VK App error: Ошибка с ID или сервис-ключом';
            return;
        }

        if (!(@$info['response']['items'][0]['type'] == 'site')) {
            $error[] = $errMsg = 'VK App error: Тип приложения должен быть указан как "сайт".';
            return;
        }

        $data['service_params']['succ_app'] = 1;

        if (isset($data['transfer_autopilot'])) {
            require_once ROOT . '/extensions/connect/models/connectTransfer.php';
            $transfer = \Connect\Transfer::AutoPilot(true);

            \System::setNotif(true, 'VK Transfer(AutoPilot): ' . $transfer, 60);
        }
    }


    /**
     * Обновить данные юзера
     * @param int    $sm_user_id
     * @param int    $vk_id
     * @param string $service_username
     * @param bool   $cu_req
     * @return bool
     */
    public static function updUserServiceID(int $sm_user_id, int $vk_id, string $service_username = '', bool $cu_req = false) {
        if (!$cu_req) {
            return true;
        }

        $db = \Db::getConnection();
        $result = $db->prepare("UPDATE " . PREFICS . "connect_users SET `" . self::$db_key . "` = :id WHERE user_id = {$sm_user_id}");
        $result->bindParam(':id', $vk_id, \PDO::PARAM_INT);

        return $result->execute();
    }

    /**
     * Отправить сообщение юзеру
     *
     * @param int   $user_id
     * @param array $data
     * @return bool
     */
    public static function sendMessage(int $user_id, array $data) {
        $pre_msg = false;
        $text = false;

        if (isset($data['text']) && !empty($data['text']) && is_string($data['text'])) {
            $text = $data['text'];
        }
        elseif (isset($data['pre_msg']) && !empty($data['pre_msg']) && is_string($data['pre_msg'])) {
            $pre_msg = $data['pre_msg'];
        }
        else {
            return false;
        }

        $api = self::getBotApiObject($user_id);

        if ($api === false) {
            return false;
        }

        $replace = isset($data['replace'], $data['replace'][0], $data['replace'][1]) ? $data['replace'] : false;

        if ($pre_msg && !$text) {
            return (bool) $api->sendPreMessage($pre_msg, $replace);
        }
        elseif ($text) {
            $keyboard = isset($data['keyboard']) ? $data['keyboard'] : false;

            return (bool) $api->sendMessage($text, $keyboard);
        }

        return false;
    }

    /**
     * Обработать авторизацию
     */
    public static function authProcess() {
        if (isset($_GET['auth_link'])) {
            \System::redirectUrl(base64_decode($_GET['auth_link']));
        }

        if (empty($_GET['code']) || empty($_COOKIE['auth_hash'])) {
            \ErrorPage::return404();
        }

        setcookie('auth_hash', '', time() - 360, '/connect/authProcess');
        $name = 'vkontakte';
        $title = 'VK';

        $service_params = @ \Connect::getServiceByName($name)['service_params'];

        if (empty($service_params) || !@ $service_params['succ_app']) {
            \ErrorPage::return404();
        }

        $params = [
            'client_id' => (int) $service_params['app_id'],
            'client_secret' => $service_params['secret'],
            'redirect_uri' => \System::getSetting()['script_url'] . self::$script_url,
            'code' => $_GET['code']
        ];

        $curl = new \Curl('https://oauth.vk.com/access_token');
        $auth_str = $curl->GET($params);

        $user_data = json_decode($auth_str, true);

        $auth_res = self::processAuth($user_data, $name);

        if ($auth_res == 'success') {
            $timeout = 0.5;
            $message = 'Success! Loading...';
        } else {
            $timeout = 60;
            $message = 'Error ' . $auth_res;
        }

        require __DIR__ . '/../../views/site/login/load_page.php';
    }

    /**
     * Проверить авторизацию
     * @param $user_data
     * @param $name
     * @return string
     */
    private static function processAuth($user_data, $name) {
        $auth_res = "";

        if (isset($user_data['access_token'], $user_data['user_id'])) {
            $vk_user_id = $user_data['user_id'];

            $user_id = \User::isAuth();

            if ($user_id) {
                \Connect::addUser($user_id, $name, $vk_user_id, 'vk.com/id' . $vk_user_id, $_COOKIE['auth_hash']);
                $auth_res = 'success';
            } else {
                $auth_res = \Connect::authUser($name, $_COOKIE['auth_hash'], $vk_user_id, ["first_name" => "user".$vk_user_id, "user_name" => "user".$vk_user_id]);
            }
        }

        if ($auth_res == 'User not found') {
            $user = \Autopilot::getUserByField('%id' . $vk_user_id, 'vk_url', null);

            if (isset($user['user_id'])) {
                $res = \Connect::addUser($user['user_id'], 'vkontakte', $vk_user_id, 'vk.com/id' . $vk_user_id, $_COOKIE['auth_hash']);

                if ($res) {
                    $auth_res = \Connect::authUser($name, $_COOKIE['auth_hash'], $vk_user_id);
                }
            }
        }

        return $auth_res;
    }

    /**
     * Получить объект бота
     * @param int $user_id
     * @return \Connect\Vkontakte\api\Methods|false
     */
    private static function getBotApiObject($user_id = 0) {
        $methods_path = __DIR__ . '/api/methods.php';
        if (!file_exists($methods_path))
            return false;

        defined('CONNECT_VK_BOT') or define('CONNECT_VK_BOT', 1);
        require_once $methods_path;

        $load_data = ['from_id' => $user_id, 'peer_id' => $user_id];
        $bot = isset($data['bot']) ? $data['bot'] : 'main';
        $bot = new api\Methods($load_data, false, $bot);

        if (!$bot())
            return false;

        return $bot;
    }

    /**
     * Запрос в вк
     * @param string $method
     * @param array  $data
     * @return mixed
     */
    public static function requestVKApi(string $method, array $data = []) {
        $url = 'https://api.vk.com/method/' . $method;

        $curl = new \Curl($url);
        return json_decode($curl->POST($data), true);
    }
}