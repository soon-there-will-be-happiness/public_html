<?php 
namespace Connect\Telegram;
defined('BILLINGMASTER') or die;

class Model implements \Connect\Service{

    /** @var string вид ссылки авторизации */
    public static $auth_link = "https://t.me/{username}?start=cct1{hash}";

    /** @var string идентификатор столбца в БД connect_users */
    public static $db_key = 'tg_id';

    /** @var string идентификатор столбца логина в БД users */
    public static $db_ukey = 'nick_telegram';


    /**
     * @param int $id
     * @param array $data
     * @param bool $notif
     * @return bool
     */
    public static function updSetting(int $id, array $data, bool $notif = true){
        $sp = $data['service_params'];
        $error = [];

    	$info = self::requestTGApi($sp['token'], 'getMe');

    	//TODO: connect_refactoring: выбросить исключение вместо ошибки
        if($info['ok'] && isset($info['result']['username'])){
            $data['service_params']['username'] = $info['result']['username'];

            $data['params']['db_key'] = self::$db_key;
            $data['params']['auth_link'] = str_replace(
                '{username}', 
                $info['result']['username'], 
                self::$auth_link
            );
            
	    	$webhook = self::requestTGApi($sp['token'], 'getWebhookInfo');

            $url = \System::getSetting()['script_url'] . "/connect/webhook/telegram";

	    	if(@ $webhook['url'] != $url){
	    		$setwebhook = self::requestTGApi($sp['token'], 'setWebhook?url=' . $url);

	    		if(!$setwebhook['ok']){
	    			$error[] = "Не удалось подключить WebHook и активировать сервис. Error: #{$setwebhook['error_code']} {$setwebhook['description']}";
	    			$data['enable'] = 0;
	    		}
	    	}

            if($data['enable'] == 1){
                \Connect::updServiceStatus($id, 1);
            	self::setExtStatus(true);
                $data['params']['use_webhook'] = 1;
            }

            else{
                \Connect::updServiceStatus($id, 0);
            	self::setExtStatus(false);
            }
        }
        
        else{
        	$error[] = 'Токен Telegram недействителен!';

        	if($data['enable'] == 1)
	    		$error[] = "Невозможно активировать сервис.";

            \Connect::updServiceStatus($id, 2);
        	self::setExtStatus(false);

            $data['service_params']['username'] = null;
        }

        $res = \Connect::updServiceSettingByID($id, $data['service_params'], $data['params']);

        if($notif && $res)
		    \System::setNotif(true, 'Настройки Telegram сохранены!');

        if($notif && !empty($error))
            \System::setNotif(false, implode(" ", $error), 120);

        return $res;
    }

    /**
     * @param int $sm_user_id
     * @param int $tg_id
     * @param string $service_username
     * @param bool $cu_req
     * @return bool
     */
    public static function updUserServiceID(int $sm_user_id, int $tg_id, string $service_username = '', bool $cu_req = false){

        if($cu_req){
            $db = \Db::getConnection();
            $sql = "
                UPDATE ".PREFICS."connect_users 
                SET 
                    `" . self::$db_key . "` = :id
                WHERE user_id = {$sm_user_id}
            ";
            $result = $db->prepare($sql);
            $result->bindParam(':id', $tg_id, PDO::PARAM_INT);

            return $result->execute();
        }
        return true;
    }

    /**
     *
     */
    public static function authProcess(){
        $timeout = 100;
        $message = \System::Lang('ERROR') . ' ';

        if(isset($_GET['lhash'], $_GET['name'], $_GET['user_id'], $_GET['email'], $_GET['service_user_id'], $_GET['auth_link'], $_COOKIE['auth_hash']) && is_numeric($_GET['user_id'])){
            $user_id = (int) $_GET['user_id'];

            if(md5(@ $_GET['user_id'] . ':' . @ $_GET['name'] . ':' . explode("-", base64_decode($_COOKIE['auth_hash']))[0]) == $_GET['lhash']
                && ($connect_user = \Connect::getUserBySMID($user_id))
                && ($user = \User::getUserById($user_id))
                && @ $user['status'] == 1
            ){
                $code = mt_rand(1000, 9999);
                $hash = [
                    substr($_COOKIE['auth_hash'], 0, 16),
                    substr($_COOKIE['auth_hash'], 8)
                ];

                $auth_enable = \Connect::getUserAuthStatusBySMID($user_id, 'telegram');

                $msg = $auth_enable
                    ? ['text' => "Подтвердите вход в школу (#{$code})",
                        'keyboard' => ["inline",
                        [
                            [
                                ["Войти", "%authProcess#{$hash[0]}"],
                                ["Нет", "%authProcess"]
                            ]
                        ]
                    ]]
                    : ['text' => "Попытка входа в Личный кабинет.\n\n Включите авторизацию через Telegram в личном кабинете школы."];

                $send = $auth_enable ? self::sendMessage($_GET['service_user_id'], $msg) : false;

                if($auth_enable && $send)
                    \Connect::updUserAuthHash($user_id, $hash[1]);
        
                elseif($auth_enable)
                    \System::redirectUrl(base64_decode($_GET['auth_link']));

                else
                    $timeout = 3;
                
                $message = "Подвердите вход (#{$code})";
                $html = "
                <p>В чат Telegram бота отправлено сообщение для подтверждения.</p>
                <div class='links'>
                    <a href='?cansel'>" . \System::Lang('CANCEL') . "</a>
                </div>
                <a class='red' style='font-size: 12px; margin-top: 5px;' href='" . base64_decode($_GET['auth_link']) . "'>Войти по ссылке</a>
                ";
            }

            else
                $message .= "confirm error";
        }
        else
            $message .= "little data";

        require __DIR__ . '/../../views/site/login/load_page.php';
    }

    /**
     * @param int $user_id
     * @param array $data
     * @return bool
     */
    public static function sendMessage(int $user_id, array $data){
        $pre_msg = false;
        $text = false;
        $media= false;
        if(isset($data['text']) && !empty($data['text']) && is_string($data['text']))
            $text = $data['text'];

        elseif(isset($data['media']) && !empty($data['media']))
            $media = $data['media'];

        elseif(isset($data['pre_msg']) && !empty($data['pre_msg']) && is_string($data['pre_msg']))
            $pre_msg = $data['pre_msg'];

        else
            return false;

        $api = self::getBotApiObject($user_id);

        if($api === false)
            return false;

        $replace = isset($data['replace'], $data['replace'][0], $data['replace'][1]) ? $data['replace'] : false;

        if($pre_msg && !$text)
            return (bool) $api->sendPreMessage($pre_msg, $replace);
        

        elseif($text){
            $keyboard = isset($data['keyboard']) ? $data['keyboard'] : false;

            return (bool) $api->sendMessage($text, $keyboard);
        }

        elseif($media){
            $keyboard = isset($data['keyboard']) ? $data['keyboard'] : false;

            return (bool) $api->sendMedia($media, $text, $keyboard);
        }
        
        return false;
    }

    /**
     * @param $user_ids
     * @param $chat_ids
     * @param bool $then_unban
     * @return array|array[]|false
     */
    public static function kickUsersChats($user_ids, $chat_ids, bool $then_unban = false){
        $api = self::getBotApiObject();

        if($api === false)
            return false;

        if(!is_array($user_ids) && is_numeric($user_ids))
            $user_ids = [$user_ids];

        if(!is_array($chat_ids) && is_numeric($chat_ids))
            $chat_ids = [$chat_ids];

        $res = [
            'success' => [],
            'error' => []
        ];

        foreach ($user_ids as $user_id) {
            foreach ($chat_ids as $chat_id) {
                if($api->ban($user_id, $chat_id)){
                    $res['success'][] = $user_id . ' ' . $chat_id;

                    if($then_unban)
                        $api->unban($user_id, $chat_id);
                }

                else
                    $res['error'][] = $user_id . ' ' . $chat_id;
            }
        }
        return $res;
    }

    /**
     * @param bool $status
     * @return bool
     */
    private static function setExtStatus(bool $status = false){
    	$status = (int) $status;

        $db = \Db::getConnection();

        $sql = "
	        UPDATE ".PREFICS."extensions 
	        SET enable = {$status}
	        WHERE name = 'telegram'
        ";
        
        $result = $db->prepare($sql);
        return $result->execute();
    }

    /**
     * @param int $user_id
     * @return \Connect\Telegram\api\Methods|false
     */
    private static function getBotApiObject($user_id = 0){
        $methods_path = __DIR__ . '/api/methods.php';
        if(!file_exists($methods_path))
            return false;

        defined('CONNECT_TG_BOT') or define('CONNECT_TG_BOT', 1); 
        require_once $methods_path;

        $load_data = [
            'from' => ['id' => $user_id],
            'chat' => ['id' => $user_id]
        ];
        $bot = isset($data['bot']) ? $data['bot'] : 'main';
        $bot = new api\Methods($load_data, false, $bot);
        
        if(!$bot())
            return false;

        return $bot;
    }

    /**
     * @param string $token
     * @param string $method
     * @return mixed
     */
    public static function requestTGApi(string $token, string $method){

        $curl = new \Curl('https://api.telegram.org/bot' . $token . '/' . $method);
        $res = $curl->GET();

		return json_decode($res, true);
    }

}