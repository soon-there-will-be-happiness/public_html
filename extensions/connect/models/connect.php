<?php defined('BILLINGMASTER') or die;


class Connect {

    public static $transfer_path = __DIR__ . '/connectTransfer.php';

    /** @var array существующие сервисы */
    private static $receivedServices = [];

    /** @var array существующие юзеры */
    private static $receivedUsers = [];



    /**
     * Выполнить авторизацию из хэша.
     * @param $hash - хеш авторизации
     *
     * @return string. Значения: timeout, hash not found, user not found, success
     */
    public static function GoAuth($hash){
        $base = base64_decode($hash);
        $base = explode("-", $base);

        if($base[0] < time()) // время вышло
            return 'timeout';

        $conn_user = Connect::searchAuthHash($hash);

        if(!$conn_user) // hash не найден в БД
            return 'hash not found';

        $user = User::getUserById($conn_user['user_id']);

        if(!$user || @ $user['status'] != 1) /* пользователь не найден или откл */
            return 'user not found';

        # авторизация
        User::Auth($user['user_id'], $user['user_name']);

        # обновим cookie 'emnam' >---
        $emnam = isset($_COOKIE['emnam']) ? explode('=', $_COOKIE['emnam']) : ['', '', ''];

        if(!$emnam || empty($emnam[0]) || $emnam[0] != $user['email'])
            $emnam[0] = $user['email'];
        
        setcookie('emnam', implode('=', $emnam), time() + 2592000 /*30 d*/, '/');
        # ---<

        # убираем хеш авторизации
        Connect::updUserAuthHash($user['user_id'], null);

        return 'success';
    }

    /**
     * ПОЛУЧИТЬ ДАННЫЕ ВСЕХ СЕРВИСОВ (кроме настроек для подключения)
     *
     * @param string $status статус сервиса (по умолчанию: все)
     * @param string $filter Фильтр поиска (WHERE <filter>)
     *
     * @return array
     */
    public static function getAllServices($status = '', string $filter = ''){

        $where = empty($status) || !is_numeric($status) ? '' : " WHERE `status` = {$status} ";
        $where .= ' ' . $filter;

        $db = Db::getConnection();
        $result = $db->query("
            SELECT `service_id`, `name`, `title`, `status`, `params`, `service_types`
            FROM ".PREFICS."connects 
            {$where}
            ORDER BY service_id ASC
        ");

        $data = [];

        while($row = $result->fetch()) {
            $id = $row['service_id'];

            $data[$id]['service_id'] = $id;
            $data[$id]['name']   = $row['name'];
            $data[$id]['title']  = $row['title'];
            $data[$id]['status'] = $row['status'];
            $data[$id]['types'] = array_flip(explode("|", $row['service_types']));

            $data[$id]['params'] = json_decode(base64_decode($row['params']), true);
        }

        if(empty($data))
            return [];

        self::$receivedServices = self::$receivedServices + $data;

        return $data;
    }

     /**
     * ВЫВОД КНОПОК АВТОРИЗАЦИИ
     * @param  string $explr уникальный код для блока
     * @return void
     */
    public static function showAuthButtons(string $explr = ''){
        $services = Connect::getAllServices();
        $setting = System::getSetting();

        require __DIR__ . '/../views/site/login/main.php';
    }

    /**
     * ПРОВЕРИТЬ НАЛИЧИЕ ПОДКЛ И ВЫВЕСТИ СООБЩЕНИЕ (если не подкл)
     *
     * @param string $name имя сервиса в connect
     * @param int $sm_user_id id юзера в базе users
     * @param bool $only_there_are_groups
     *
     * @return bool
     */
    public static function showConnectNotice(string $name, int $sm_user_id, bool $only_there_are_groups = false): bool{
        if (!$key = self::getServiceKey($name))
            return false;

        $c_user = Connect::getUserBySMID($sm_user_id);

        if ($c_user && isset($c_user[$key]) && is_numeric($c_user[$key]) && $c_user[$key] > 0)
            return false;

        $service = Connect::getServiceByName($name);

        if (!$service)
            return false;

        if ($only_there_are_groups && empty(self::userGroupsTGChatIds($sm_user_id)))
            return false;

        require __DIR__ . '/../views/site/lk/notice.php';

        return true;
    }

    /**
     * ПОЛЧИТЬ СПИСОК ID ТГ ЧАТОВ ГРУПП ПОЛЬЗОВТЕЛЯ
     * 
     * @param  int    $sm_user_id id юзера в базе users
     * @return array
     */
    public static function userGroupsTGChatIds(int $sm_user_id): array{
        $groups = User::getGroupByUser($sm_user_id);

        if(!$groups || empty($groups))
            return [];

        $res = [];

        foreach ($groups as $group_id) {
            $group = User::getUserGroupData($group_id);

            if(isset($group['del_tg_chats']) && !empty($group['del_tg_chats'])) {
                $res += explode(',', str_replace(' ', '', $group['del_tg_chats']));
            }
        }

        return $res;
    }

    /**
     * ОТПРАВИТЬ СООБЩЕНИЕ В СЕРВИС ПО SM_user_id
     *                              
     * @param  string $name        имя сервиса в connect
     * @param  int    $sm_user_id  id юзера в базе users
     * @param  array  $data        тело сообщения ['text' => text]
     * @return bool
     */
    public static function sendMessageServiceBySMID(string $name, int $sm_user_id, array $data): bool {
        if(!$key = self::getServiceKey($name))
            return false;

        $service_user_id = self::getUserBySMID($sm_user_id, $key);

        if(!$service_user_id)
            return false;

        $res = false;

        if(($method = self::getServiceMethod($name, 'sendMessage')))
            $res = $method($service_user_id, $data);

        return $res;
    }

    /**
     * ОТПРАВИТЬ СООБЩЕНИЕ В СЕРВИС ПО Email
     *
     * @param string $name имя сервиса в connect
     * @param string $email
     * @param array $data тело сообщения ['text' => text]
     *
     * @return bool
     */
    public static function sendMessageServiceByEmail(string $name, string $email, array $data): bool {
        if(!$key = self::getServiceKey($name))
            return false;

        $service_user_id = self::getUserByEmail($email, $key);

        if(!$service_user_id)
            return false;

        if(($method = self::getServiceMethod($name, 'sendMessage')))
            return $method($service_user_id, $data);

        return false;
    }

    /**
     * Отправить сообщения исходя из email юзера
     *
     * @param string $email
     * @param string $text
     * @param array $addit_data
     *
     * @return array
     */
    public static function sendMessagesByEmail(string $email, string $text, array $addit_data = [], string $file_path = 'https://dev.xn--80ajojzgb4f.xn--p1ai/images/photo_2023-02-10_11-05-17.jpg'){
        $filter = [];
        $file_path = 'https://dev.xn--80ajojzgb4f.xn--p1ai/images/photo_2023-02-10_11-05-17.jpg';
        $res = ['users' => [], 'count' => 0];

        if (!empty($addit_data) && isset($addit_data['caller'])) {
            if ($addit_data['caller'] == 'SendMessageToBlank' && !empty($addit_data['addit_data'])) {
                $d_addit_data = $addit_data['addit_data'];

                if (!isset($d_addit_data['caller'], $d_addit_data['mail']) || $d_addit_data['caller'] != 'user_edit') {
                    return $res;
                }

                foreach ($d_addit_data['mail'] as $name => $type) {
                    $filter['service_name'][] = $name;
                }
            } else {
                $when_msg = self::getParams('when_msg');

                if (!in_array($addit_data['caller'], $when_msg)) {
                    return $res;
                }
            }
        }

        $services = self::getAllServices(1);
        $user = self::getUserByEmail($email);

        if(empty($user))
            return $res;

        $text = preg_replace("#<a\s*[^>]*href=\"(.*)\".*>(?:\s*\\1)</a>#i", "\\1", $text);
        $text = preg_replace("#<a\s*[^>]*href=\"(.*)\".*>(.*)</a>#i", "\\2 (\\1)", $text);

        $text = strip_tags($text, "<br><b><i></i>");
        $text = str_replace(['<br>', '<br/>', '<br />', '</br>'], "\n", $text);
        $text = htmlspecialchars_decode($text);
        $text = html_entity_decode($text);
        if (!empty($file_path)) {
            $data = [
                'media' =>[
                    'file_url'=>$file_path,
                    'type'=>self::detectFileType($file_path)
                ],
            ];
        } else {
            $data = [
                'text' => $text,
            ];

        }
        var_dump($data);
        foreach ($services as $id => $service) {
            if (!empty($filter) && isset($filter['service_name']) && !empty($filter['service_name']) && !in_array($service['name'], $filter['service_name']))
                continue;

            if (@$user['params'][$service['name']]['noti'] != true || !($key = self::getServiceKey($service['name'])) || !isset($user[$key]))
                continue;

            if ($method = self::getServiceMethod($service['name'], 'sendMessage')) {
                if ($method($user[$key], $data)) {
                        $res['users'][$email] ?? $res['users'][$email] = [];
                    $res['users'][$email][$key] = $user[$key];
                }
            }

        }

        $res['count'] = count($res['users']);
        return $res;
    }


    private static function detectFileType($file_path) {
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $photo_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $video_types = ['mp4', 'mov', 'avi', 'mkv', 'flv'];
        $audio_types = ['mp3', 'wav', 'ogg'];
        $document_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

        if (in_array($ext, $photo_types)) return 'photo';
        if (in_array($ext, $video_types)) return 'video';
        if (in_array($ext, $audio_types)) return 'audio';
        if (in_array($ext, $document_types)) return 'document';

        return 'document'; // По умолчанию отправляем как документ
    }


/* ====================================================================================
                            ДЕЙСТВИЯ С СЕРВИСАМИ
======================================================================================= */

    /**
     * ПОЛУЧИТЬ ДАННЫЕ СЕРВИСА ПО ID
     * 
     * @param  int    $id id сервиса в connect
     * @return bool
     */
    public static function getServiceByID(int $id) {
        if(isset(self::$receivedServices[$id])) {
            return self::$receivedServices[$id];
        }

        $res = self::searchService('service_id', $id);

        if($res && !empty($res['service_id'])) {
            self::$receivedServices[$res['service_id']] = $res;
        }

        return $res;
    }

    /**
     * ПОЛУЧИТЬ ДАННЫЕ СЕРВИСА ПО ИМЕНИ
     * 
     * @param  string  $name имя сервиса в connect
     * @return bool
     */
    public static function getServiceByName(string $name){
        if (!preg_match("#^[a-z0-9-_]+$#", $name))
            return false;
        
        $res = self::searchService('name', $name);

        if ($res && !empty($res['service_id'])) {
            self::$receivedServices[$res['service_id']] = $res;
        }

        return $res;
    }


    /**
     * ОБВНОВИТЬ ДАННЫЕ СЕРВИСА ПО ID
     * 
     * @param  int    $id             id сервиса в Connect
     * @param  array  $service_params Настроки сервиса (данные для подключения)
     * @param  array  $params         Параметры работы сервиса
     * @return bool
     */
    public static function updServiceSettingByID(int $id, array $service_params, array $params){
        return self::updServiceSetting(['service_id' => $id], $service_params, $params);
    }

    /**
     * ОБВНОВИТЬ СТАТУС СЕРВИСА ПО ID
     *     
     * @param  int    $id     id сервиса в Connect
     * @param  int    $status Новый статус 0/1/2
     * @return bool
     */
    public static function updServiceStatus(int $id, int $status){
        if($status < 0 || $status > 2) {
            $status = 0;
        }

        return Db::_update('connects', ['status' => $status], ['service_id' => $id]);
    } 


/*                          ДЕЙСТВИЯ С ПОЛЬЗОВАТЕЛЯМИ
====================================================================================
 */
    /**
     * УЗНАТЬ СТАТУС msg/auth в СЕРВИСЕ
     *
     * @param int $sm_user_id
     * @param string $name имя сервиса в connect
     * @param bool $only_user
     *
     * @return null|bool
     */
    public static function getUserNotifStatusBySMID(int $sm_user_id, string $name, bool $only_user = false){
        $user = self::getUserBySMID($sm_user_id);

        return empty($user) ? false : self::checkUserStatus($user, $name, 'msg', $only_user);
    }

    /**
     * Получить информацию о авторизации через сервис у юзера
     *
     * @param int $sm_user_id
     * @param string $name
     * @param bool $only_user
     *
     * @return bool
     */
    public static function getUserAuthStatusBySMID(int $sm_user_id, string $name, bool $only_user = false){
        $user = self::getUserBySMID($sm_user_id);

        return !empty($user) && self::checkUserStatus($user, $name, 'auth', $only_user);
    }

    /**
     * Проверить, имеет ли юзер доступ к сервису
     *
     * @param $user
     * @param string $name
     * @param string $key
     * @param bool $only_user
     *
     * @return bool
     */
    private static function checkUserStatus($user, string $name, string $key, bool $only_user = false) {
        if (!isset($user['params'], $user['params'][$name], $user['params'][$name][$key]) || !$user['params'][$name][$key]) {
            return false;
        }

        if ($only_user) {
            return true;
        }

        $service = self::getServiceByName($name);

        if (isset($service['types'][$key], $service['params'][$key])) {
            return (bool) $service['params'][$key];
        }

        return false;
    }

    /**
     * УСТАНОВИТЬ СТАТУС ПОДПИСКИ НА УВЕДОМЛЕНИЯ СЕРВИСА
     * 
     * @param int    $service_user_id id юзера внутри сервиса
     * @param string $name            имя сервиса в connect
     * @param bool   $status          статус
     * 
     * @return bool
     */
    public static function setNotifStatus(int $service_user_id, string $name, bool $status): bool{
        $user = self::getUserByServiceID($name, $service_user_id);

        if(empty($user)) {
            return false;
        }

        $sm_user_id = $user['user_id'];
        $user_params = $user['params'];
        
        $user_params[$name] ?? $user_params[$name] = [];
        $user_params[$name]['msg'] = $status;

        return (bool) self::updUserParams($sm_user_id, $user_params);
    }

    /**
     * ДОБАВИТЬ/ОБНОВИТЬ ЮЗЕРА В СЕРВИСЕ ПО SM_user_id
     *
     * @param int $smUserId id юзера в базе users
     * @param string $name имя сервиса в connect
     * @param int $serviceUserId id юзера внутри сервиса
     * @param string $serviceUsername
     * @param string $authHash
     *
     * @return bool
     */
    public static function addUser(int $smUserId, string $name, int $serviceUserId, string $serviceUsername = '', string $authHash = ''): bool {
        if ($smUserId <= 0) {
            return false;
        }

        $serviceKey = self::getServiceKey($name);
        if (!$serviceKey) {
            return false;
        }

        $userByService = self::getUserByServiceID($name, $serviceUserId);
        if ($userByService && $userByService['auth_hash'] != $authHash) {
            self::unlinkServiceByServiceID($name, $serviceUserId, $userByService);
        }

        $settingsDefault = self::getParams('settings_default');
        $params = !empty($settingsDefault) && $settingsDefault ? serialize($settingsDefault) : null;

        $userData = [
            'user_id' => $smUserId,
            'params' => $params,
            $serviceKey => $serviceUserId
        ];

        $queryData = [$serviceKey => $serviceUserId];
        $result = Db::_insert_or_upd('connect_users', $userData, $queryData);

        if ($result && !empty($serviceUsername)) {
            self::updUsernameForUsersDB($smUserId, $name, $serviceUsername);
        }

        if ($result && ($method = self::getServiceMethod($name, 'updUserServiceID'))) {
            $result = $method($smUserId, $serviceUserId, $serviceUsername, false);
        }

        return (bool) $result;
    }

    public static function setDefaultSettingForAll(){
        $settings_default = self::getParams('settings_default');
        $params = !empty($settings_default) && $settings_default ? serialize($settings_default) : null;

        return Db::_update('connect_users', ['params' => $params,], [1 => 1]);
    }

    /**
     * ДОБАВИТЬ/ОБНОВИТЬ ЮЗЕРА В СЕРВИСЕ ПО AUTH_HASH
     *
     * @param string $auth_hash
     * @param string $name имя сервиса в connect
     * @param int $service_user_id id юзера внутри сервиса
     * @param string $service_username
     *
     * @return mixed
     */
    public static function addUserByHash(string $auth_hash, string $name, int $service_user_id, string $service_username = '') {
        $key = self::getServiceKey($name);
        if (!$key) {
            return false;
        }

        $res = true;
        $user = self::searchAuthHash($auth_hash);

        if ($user || !empty($user)) {
            $res = self::addUser($user['user_id'], $name, $service_user_id, $service_username, $auth_hash);
        }

        if ($res) {
            $res = self::getUserBySMID($user['user_id']);
        }

        return $res;
    }

    /**
     * Добавить(зарегистрировать) нового пользователя через сервис
     */
    public static function addNewUser($user, $service_user_id, $hash, $name) {
        $email = $service_user_id."@".$name.".com";
        return User::addUser($user['first_name'] ?? $user['user_name'], $email,
            null, $hash, time(), "user", null, null, null, 1
        );
    }

    /**
     * НАЙТИ ПОЛЬЗОВАТЕЛЯ В "Connect" по id сервиса
     *                 
     * @param  string $name            имя сервиса в conenct
     * @param  int    $service_user_id id юзера внутри сервиса
     * @return array                  
     */
    public static function getUserByServiceID(string $name, int $service_user_id, bool $rem = false) {
        $key = self::getServiceKey($name);
        if(!$key) {
            return [];
        }

        return self::searchUser($key, $service_user_id, $rem);
    }

    /**
     * НАЙТИ ПОЛЬЗОВАТЕЛЯ В "Connect" по SM_user_id
     * 
     * @param  int    $sm_user_id id юзера в базе users
     * @param  string $key        вывод по результату
     * @return mixed             
     */
    public static function getUserBySMID(int $sm_user_id, string $key = '') {
        $res = self::searchUser('user_id', $sm_user_id);

        if (empty($key)) {
            return $res;
        }

        if (isset($res[$key])) {
            return $res[$key];
        }

        return null;
    }

    /**
     * НАЙТИ ПОЛЬЗОВАТЕЛЯ В "Connect" по email
     *
     * @param string $email
     * @param string $key
     *
     * @return mixed|null
     */
    public static function getUserByEmail(string $email, string $key = ''){
        if(!isset(self::$receivedUsers[$email])){
            $db = Db::getConnection();
            $result = $db->prepare("SELECT c.* FROM " . PREFICS . "connect_users as c LEFT JOIN " . PREFICS . "users as u ON c.user_id = u.user_id 
                WHERE u.email = :email
            ");
            $result->bindParam(':email', $email);
            $result->execute();

            $data = $result->fetch(PDO::FETCH_ASSOC);

            self::$receivedUsers[$email] = $data;
        }

        $data = self::$receivedUsers[$email];

        if (empty($data)) {
            return null;
        }

        if (empty($key)) {
            return $data;
        }

        return @ $data[$key];
    }
    
    /**
     * ПОИСК ЮЗЕРА ПО AUTH_HASH
     * 
     * @param  string $auth_hash хеш
     * @return mixed
     */
    public static function searchAuthHash(string $auth_hash) {
        $data = Db::_select_one('connect_users', ['auth_hash' => $auth_hash], [], true);

        if (empty($data) || !$data) {
            return null;
        }

        return $data;
    }

    /**
     *  ОБНОВИТЬ ХЕШ АВТОРИЗАЦИИ ЮЗЕРА
     *  
     * @param  int    $sm_user_id id юзера в users
     * @param  string $hash    хеш
     * @return bool
     */
    public static function updUserAuthHash(int $sm_user_id, $hash) {
        $settings_default = self::getParams('settings_default');
        $params = !empty($settings_default) && $settings_default ? serialize($settings_default) : null;

        return Db::_insert_or_upd('connect_users',
            ['user_id' => $sm_user_id, 'params' => $params, 'auth_hash' => $hash],
            ['auth_hash' => $hash]
        );
    }

    /**
     * Обновить params у юзера
     *
     * @param int $sm_user_id
     * @param array $params
     *
     * @return bool
     */
    public static function updUserParams(int $sm_user_id, array $params) {
        return Db::_update('connect_users',
            ['params' => $params],
            ['user_id' => $sm_user_id]
        );
    }

    /**
     * ОТВЯЗАТЬ СЕРВИС ПО ID
     *
     * @param string $name имя сервиса в connect
     * @param int $service_user_id
     * @param null $user
     *
     * @return bool
     */
    public static function unlinkServiceByServiceID(string $name, int $service_user_id, $user = null) {
        if(!$key = self::getServiceKey($name)) {
            return false;
        }

        $user = self::getUserByServiceID($name, $service_user_id);

        return self::unlinkService($name, [$key => $service_user_id], $user);    
    }

    /**
     * ОТВЯЗАТЬ СЕРВИС ПО HASH
     *
     * @param string $name
     * @param string $hash
     * @param null $user
     *
     * @return bool
     */
    public static function unlinkServiceByHash(string $name, string $hash, $user = null) {
        $user = self::searchAuthHash($hash);

        return self::unlinkService($name, ['auth_hash' => $hash], $user);
    }

    /**
     * ОТВЯЗАТЬ СЕРВИС
     *
     * @param string $name
     * @param array $where
     * @param array $user
     *
     * @return bool
     */
    private static function unlinkService(string $name, array $where, array $user) {
        if(!$key = self::getServiceKey($name))
            return false;

        $res = true;

        $res = Db::_update('connect_users', [$key => null, 'auth_hash' => null], $where);

        if (!$res || empty($user)) {
            return false;
        }

        $service_user_id = $user[$key];

        $method = self::getServiceMethod($name, 'sendMessage');
        if ($method) {
            $method($service_user_id, ['text' => 'Данный аккаунт был отвязан от школы.']);
        }

        if ($name == "telegram") {
            $res = Db::_update('telegram_users', ['sm_user_id' => null, 'hash' => null], ['user_id' => $service_user_id]);
            $method = self::getServiceMethod($name, 'kickUsersChats');
            if (!$method) {
                return $res;
            }

            $user_chats = [];
            $all_chats = Db::_select_all('telegram_chats', ['chat_id', 'data'], 'chat_id', true);

            foreach ($all_chats as $chat_id => $chat) {
                if (isset($chat['data']['users'], $chat['data']['users'][$service_user_id])) {
                    $user_chats[] = $chat_id;
                }
            }

            $del_groups = User::getGroupByUser($service_user_id);
            if (!$del_groups) {
                return $res;
            }

            foreach ($del_groups as $group_id) {
                $group = User::getUserGroupData($group_id);

                if ($group && $group['del_tg_chats']) {
                    foreach (explode(',', $group['del_tg_chats']) as $del_chat) {
                        $user_chats[] = trim($del_chat);
                    }
                }
            }

            $method($service_user_id, $user_chats);
        }

        return $res;
    }

    /**
     * УСТАНОВКА ХЕША АВТОРИЗАЦИИ (ДЛЯ СЕРВИСОВ)
     *
     * @param  string $name            имя сервиса в connect
     * @param  string $hash            хеш
     * @param  int    $service_user_id id юзера в сервисе
     *
     * @return string Успех установки (success/Error/timeout/User not found)
     */
    public static function authUser(string $name, string $hash, int $service_user_id, array $new_user_data = []): string {
        $user = self::getUserByServiceID($name, $service_user_id);



        if (!isset($user['user_id']) && empty($new_user_data)) {
            return 'User not found & cannot register';
        }

        $isRegister = false;
        if (!isset($user['user_id']) && !empty($new_user_data)) {
            $isRegister = true;
            $new_sm_user_id = self::addNewUser($new_user_data, $service_user_id, $hash, $name);
            $res = self::addUser($new_sm_user_id, $name, $service_user_id, $new_user_data['user_name'], $hash);
            $user = self::getUserByServiceID($name, $service_user_id);
        }

        if (!isset($user['user_id'])) {
            return 'User not found';
        }

        if (!$isRegister) {
            if (empty($user['params']) || !isset($user['params'][$name]['auth']) || $user['params'][$name]['auth'] != 1) {
                return 'authorization off';
            }
        }

        $base = base64_decode($hash);
        $base = explode("-", $base);

        if ($base[0] < time()) {
            return 'timeout';
        }

        $result = self::updUserAuthHash($user['user_id'], $hash);
        if (!$result) {
            return 'Error';
        }
        if ($isRegister) {
            return 'success-register';
        }

        return 'success';
    }



    /**
     *  СОХРАНИТЬ ДАННЫЕ ЮЗЕРА ИЗ CONNECT в Cookie
     *
     * @param int $sm_user_id id юзера в users
     *
     * @return void
     */
    public static function saveUserData(int $sm_user_id = 0) {
        $data = self::getUserBySMID($sm_user_id);

        if (empty($data)) {
            return;
        }

        unset($data['data'], $data['auth_hash']);

        System::setCookie('connect', $data, 2592000 /*30 d*/, '/');
    }

    /**
     * Проверка на пред авторизацию через Connect
     *
     * @param string $name
     * @param string $email
     *
     * @return array|false
     */
    public static function getRemUserData(string $name, string $email) {
        if (!$key = self::getServiceKey($name)) {
            return false;
        }

        $cookie_connect = System::getCookie('connect');

        if (!isset($cookie_connect[$key], $cookie_connect['user_id'])) {
            return false;
        }

        $user_id = $cookie_connect['user_id'];
        $data = Db::_select_one('users', ['user_id' => $user_id, 'email' => $email]);

        if (empty($data)) {
            return false;
        }

        return ['name' => $name, 'service_user_id' => $cookie_connect[$key], 'user_id' => $user_id, 'email' => $email];
    }

    /**
     *  ОБВНОВИТЬ USERNAME ПОЛЬЗОВАТЕЛЯ В СЕРВИСЕ
     * 
     * @param  int    $sm_user_id id юзера в базе users
     * @param  string $name       имя сервиса в connect
     * @param  string $value      значение
     * @return bool
     */
    public static function updUsernameForUsersDB(int $sm_user_id, string $name, $value) {
        $key = self::getServiceKey($name, 'db_ukey');
        if (!$key) {
            return null;
        }

        return Db::_update('users', [$key => $value], ['user_id' => $sm_user_id]);
    }


/*                          СЛУЖЕБНЫЕ ФУНКЦИИ
====================================================================================
 */

    /**
     *  УСТАНОВИТЬ СТАТУС ЭТОГО РАСШИРЕНИЯ
     *
     * @param bool $status стастус расширения
     * @return bool
     */
    public static function setStatus(bool $status) {
        $status = (int) $status;

        return Db::_update('extensions', ['enable' => $status], ['name' => 'connect']);
    }

    /**
     *  УСТАНОВИТЬ ПАРМЕТРЫ ЭТОГО РАСШИРЕНИЯ
     *
     * @param array $params стастус расширения
     * @return bool
     */
    public static function setParams(array $params) {
        return @ Db::_update('extensions', ['params' => $params], ['name' => 'connect']);
    }

    /**
     *
     * @param string $key
     *
     * @return array|bool|null
     */
    public static function getParams(string $key = '') {
        $res = Db::_select_one('extensions', ['name' => 'connect'], ['params'], true);

        if (isset($res['params'])) {
            $res = $res['params'];
        }

        $res['settings_default'] ?? $res['settings_default'] = [];

        $res['when_msg'] ?? $res['when_msg'] = [];

        if (!empty($key)) {
            return $res[$key] ?? [];
        }

        return $res;
    }

    /**
     * Получить имя столбца сервиса
     * @param $name
     * @param string $key
     *
     * @return null
     */
    public static function getServiceKey($name, $key = 'db_key'){
        $service = self::getServiceClass($name);

        if (!$service || !property_exists($service, $key)) {
            return null;
        }

        return $service::$$key;
    }

    /**
     * Вызов функции в классе сервиса
     *
     * @param string $name
     * @param string $method
     * @param bool $notis
     *
     * @return false|string
     */
    public static function getServiceMethod(string $name, string $method, bool $notis = true){
        $service = self::getServiceClass($name);

        if (method_exists($service, $method)) {
            return "{$service}::{$method}";
        }

        !$notis or System::setNotif('system_error', "Ошибка системы. Not found class or method {$service}::{$method}()");

        return false;
    }

    /**
     * Вызов класса сервиса и его подключение
     *
     * @param string $name
     *
     * @return false|string
     */
    public static function getServiceClass(string $name){
        $dir = __DIR__ . "/../services/{$name}";

        defined('SERVICE_DIR') or define('SERVICE_DIR', $dir);
        
        $files = [
           __DIR__ . '/connectService.php',
           $dir . '/model.php'
        ];

        $name = ucfirst($name);

        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
            } else {
                print('file not found ' . $file);
            }
        }

        $service = "\Connect\\$name\Model";
        
        if (class_exists($service)) {
            return $service;
        }

        return false;
    }

    /**
     * Поиск юзера в сервисе
     *
     * @param string $key
     * @param $value
     * @param bool $rem
     *
     * @return array|bool|mixed
     */
    private static function searchUser(string $key, $value, bool $rem = false){
        if (!$rem && isset(self::$receivedUsers[$key . $value])) {
            return self::$receivedUsers[$key . $value];
        }

        $data = Db::_select_one('connect_users', [$key => $value], [], true);

        if (empty($data) || !$data) {
            return [];
        }

        if (empty($data['params']) || !is_array($data['params'])) {
            $data['params'] = [];
        }

        if (empty($data['data']) || !is_array($data['data'])) {
            $data['data'] = [];
        }

        self::$receivedUsers[$key . $value] = $data;

        foreach ($data as $col => $cont) {

            if (is_array($cont) || !in_array($col, ['params', 'data', $key])) {
                continue;
            }

            self::$receivedUsers[$col . $cont] =& self::$receivedUsers[$key . $value];
        }

        return $data;
    }

    /**
     * Поиск сервиса
     *
     * @param string $key
     * @param $value
     * @param false $rem
     *
     * @return bool|mixed|null
     */
    private static function searchService(string $key, $value, $rem = false){
        if (!$rem && isset(self::$receivedServices[$key . $value])) {
            return self::$receivedServices[$key . $value];
        }

        $data = Db::_select_one('connects', [$key => $value], [], true);

        if (empty($data)) {
            return null;
        }

        $data['types'] = array_flip(explode("|", $data['service_types']));
        unset($data['service_types']);

        self::$receivedServices[$key . $value] = $data;

        return $data;
    }

    /**
     * Обновить настройки сервиса
     *
     * @param array $where
     * @param array $service_params
     * @param array $params
     *
     * @return bool
     */
    private static function updServiceSetting(array $where, array $service_params, array $params) {
        $service_params = base64_encode(json_encode($service_params));
        $params = base64_encode(json_encode($params));

        return Db::_update('connects', ['service_params' => $service_params, 'params' => $params], $where);
    }

}
