<?php defined('BILLINGMASTER') or die;

class Autopilot extends User {
    private static function getStringFields(){
        return [
            'user_name',
            'login',
            'email',
            'phone',
            'city',
            'address',
            'zipcode',
            'note',
            'pass',
            'role',
            'enter_method',
            'refer',
            'reg_key',
            'surname',
            'nick_telegram',
            'nick_instagram',
            'vk_url',
            'photo_url',
            'sex'
        ];
    }

    public static function getSettings($type = false) {
        $db = Db::getConnection();
        $result = $db->query("
            SELECT id, enable, params, version 
            FROM ".PREFICS."extensions 
            WHERE name LIKE 'autopilot' 
            LIMIT 0,1
        ");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if(empty($data) || !isset($data['params']))
            return [];

        $enable = $data['enable'];


        $service = Connect::getServiceByName('vkontakte');

        if($data['params'] != 'transferred_to_connect' && ($params = json_decode($data['params'], true)) &&
            is_array($params) && isset($params['modules'], $params['vk_auth_params'], $params['vk_app'], $params['vk_club'])
        ){

            if(
                (@ $service['service_params']['v'] != @ $params['vk_app']['v']) ||

                (@ $service['service_params']['app_id'] != @ $params['vk_app']['id']) ||
                (@ $service['service_params']['secret'] != @ $params['vk_app']['secret']) ||
                (@ $service['service_params']['service_key'] != @ $params['vk_app']['service_key']) ||

                (@ $service['service_params']['scope'] != @ $params['vk_auth_params']['scope']) ||
                (@ $service['service_params']['redirect_uri'] != @ $params['vk_auth_params']['redirect_uri']) ||
                (@ $service['service_params']['response_type'] != @ $params['vk_auth_params']['response_type']) ||

                (@ $service['service_params']['group_id'] != @ $params['vk_club']['id']) ||
                (@ $service['service_params']['chat_token'] != @ $params['vk_club']['key']) ||

                (@ $service['params']['msg'] != @ $params['vk_club']['notify']) ||
                (@ $service['params']['auth'] != @ $params['modules']['login'])
            ){
                if($method = Connect::getServiceMethod('vkontakte', 'updSetting')){

                    $service['service_params']['v'] = $params['vk_app']['v'];

                    $service['service_params']['app_id'] = $params['vk_app']['id'];
                    $service['service_params']['secret'] = $params['vk_app']['secret'];
                    $service['service_params']['service_key'] = $params['vk_app']['service_key'];
                    
                    $service['service_params']['scope'] = $params['vk_auth_params']['scope'];
                    $service['service_params']['redirect_uri'] = $params['vk_auth_params']['redirect_uri'];
                    $service['service_params']['response_type'] = $params['vk_auth_params']['response_type'];

                    $service['service_params']['group_id'] = $params['vk_club']['id'];
                    $service['service_params']['chat_token'] = $params['vk_club']['key'];

                    $service['params']['msg'] = $params['vk_club']['notify'];
                    $service['params']['auth'] = $params['modules']['login'];

                    $method($service['service_id'], $service);
                }
            }

            Autopilot::setParams('transferred_to_connect');
        }

        if($type == 'status')
            $data = [
                'status' => $enable
            ];

        elseif($type == 'chat')
            $data = [
                'v' => $service['service_params']['v'],
                'access_token' => $service['service_params']['chat_token'],
                'group_id' => $service['service_params']['group_id']
            ];

        elseif($type == 'app')
            $data = [
                'v' => $service['service_params']['v'],
                'id' => $service['service_params']['app_id'],
                'service_key' => $service['service_params']['service_key'],
                'secret' => $service['service_params']['secret']
            ];

        else
            $data = [
                'vk_app' => [
                    'v' => $service['service_params']['v'],
                    'id' => $service['service_params']['app_id'],
                    'secret' => $service['service_params']['secret'],
                    'service_key' => $service['service_params']['service_key']
                ],
                'vk_auth_params' => [
                    'scope' => $service['service_params']['scope'],
                    'redirect_uri' => $service['service_params']['redirect_uri'],
                    'response_type' => $service['service_params']['response_type']
                ],
                'vk_club' => [
                    'id' => $service['service_params']['group_id'],
                    'key' => $service['service_params']['chat_token'],
                    'notify' => $service['params']['msg']
                ],
                'status' => $enable
            ];

        return $data;
    }

    public static function setParams(string $data) {
        $db = Db::getConnection();  

        $sql = "
            UPDATE ".PREFICS."extensions 
            SET params = :params  
            WHERE name LIKE 'autopilot'
        ";
        $result = $db->prepare($sql);
        $result->bindParam(':params', $data, PDO::PARAM_STR);

        return $result->execute();
    }

    public static function setStatus(bool $status) {
        $db = Db::getConnection(); 

        $status = (int) $status;

        $sql = "
            UPDATE ".PREFICS."extensions 
            SET enable = :status
            WHERE name LIKE 'autopilot'
        ";
        $result = $db->prepare($sql);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        $res = $result->execute();

        if($res)
            System::setNotif('save');

        return $res;
    }

    // ПОЛУЧИТЬ ДАННЫЕ ГРУППЫ по полю name
    public static function getUserGroupData($name)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."user_groups WHERE group_name LIKE '$name' ");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(isset($data)) return $data;
        else return false;
    }

    // ПОЛУЧИТЬ ID ГРУПП ПО ИХ СИСТЕМНЫМ ИМЕНАМ 
    public static function getGroupsByNames($names) {
        $group_keys = array_map(function($key){return ':var_'.$key;}, array_keys($names));

        $db = Db::getConnection();       
        $sql = ' SELECT group_id FROM '.PREFICS.'user_groups WHERE group_name IN ('.implode(',', $group_keys).')';

        $result = $db->prepare($sql); 
        foreach($names as $group_key => $group_name) {
            $result->bindParam(':var_'.$group_key, $group_name);
        } 

        $result->execute();
        $data = $result->fetchAll(PDO::FETCH_COLUMN);

        if(isset($data) && $data) return $data;
        else return array();
    }


public static function getGroupsByNamesTitle($names) {
    // Генерируем параметры для IN
    $group_keys = array_map(function($key) {
        return ':var_' . $key;
    }, array_keys($names));

    // Подключение к базе
    $db = Db::getConnection();

    // Создаем SQL-запрос с именованными параметрами
    $sql = 'SELECT group_id, group_title FROM ' . PREFICS . 'user_groups WHERE group_title IN (' . implode(',', $group_keys) . ')';

    // Подготавливаем запрос
    $result = $db->prepare($sql);

    // Привязываем параметры
    foreach ($names as $group_key => $group_name) {
        $result->bindValue(':var_' . $group_key, $group_name);
    }

    // Выполняем запрос
    $result->execute();

    // Извлекаем данные как ассоциативный массив
    $data = $result->fetchAll(PDO::FETCH_ASSOC);

    // Возвращаем результат
    return !empty($data) ? $data : [];
}

    
    
    public static function prepareVkUrl($vk_url) {
        $vk_url = trim($vk_url);
        $screen_name = '';
        
        if (filter_var($vk_url,FILTER_VALIDATE_INT)) {
            return 'https://vk.com/id'.(int)$vk_url;
        }
        elseif (preg_match('/vk\.com\/(.+)/imu', $vk_url, $match) && isset($match[1]) && $match[1]) {
            $screen_name = $match[1];
        }
        elseif (preg_match('/(@|\*)(.+)/imu', $vk_url, $match) && isset($match[2]) && $match[2]) {
            $screen_name = $match[2];           
        }
        elseif (preg_match('/^([a-z._0-9]+)$/imu', $vk_url, $match) && isset($match[1]) && $match[1]) {
            $screen_name = $match[1];
        }

        if ($screen_name) { 
            if (preg_match('/id(\d+)/imu', $vk_url, $m) && isset($m[1]) && (int)$m[1]) {
                return 'https://vk.com/id'.(int)$m[1];
            }

            $autopilot = self::getSettings('app');           
            if (isset($autopilot['vk_app']['service_key'],$autopilot['vk_app']['v']) && $autopilot['vk_app']['service_key']) {
                $vk_data_res = self::vk_request($autopilot['vk_app']['service_key'], 'users.get', array('user_ids'=>$screen_name,'fields'=>'screen_name'), $autopilot['vk_app']['v']);
                $vk_data = (isset($vk_data_res['response']) && $vk_data_res['response'][0])?$vk_data_res['response'][0]:false;
                if ($vk_data && isset($vk_data['first_name'])) {
                    $vk_url = 'https://vk.com/id'.$vk_data['id'];
                }
                /*else{
                     exit('Автопилот: Не получилось достать данные из ВКонтакте. Скорее всего в настройках сайта неверный <strong>ключ доступа</strong> приложения ВК (сервисный ключ доступа). Исправьте его или удалите (для отключения интеграции) <a href="/admin/autopilot">по этой ссылке</a>.');
                }*/
            } 
        }
        

        return $vk_url;
    }

    // Проверка UTM меток
    public static function searchChannel($data) {
        $channel = 0;
        if(isset($data['utm_source'])) {
            $utm1 = '?utm_source='.htmlentities($data['utm_source']);
            
            if(isset($data['utm_medium'])) $utm2 = '&utm_medium='.htmlentities($data['utm_medium']);
            else $utm2 = '';
            
            if(isset($data['utm_campaign'])) $utm3 = '&utm_campaign='.htmlentities($data['utm_campaign']);
            else $utm3 = '';
            
            if(isset($data['utm_content'])) $utm4 = '&utm_content='.htmlentities($data['utm_content']);
            else $utm4 = '';
            
            if(isset($data['utm_term'])) $utm5 = '&utm_term='.htmlentities($data['utm_term']);
            else $utm5 = '';
            
            $utm = $utm1 . $utm2 . $utm3 . $utm4 . $utm5;            
            return intval(Stat::searchChannel($utm)); // возрврат channel_id
        } 
    }

    // Получение всех ДАННЫХ ЮЗЕРА по ПОЛЮ ПРОФИЛЯ. Например, по VK ID
    public static function getUserByField($value, $field='vk_url', $email = null) {
        $db = Db::getConnection();
        $sql = "SELECT * FROM ".PREFICS."users WHERE $field LIKE :value ";
        if ($email)  $sql .= " OR email LIKE :email "; 
        $sql .= "ORDER BY user_id ASC LIMIT 0,1 ";

        $result = $db->prepare($sql);
        $result->bindParam(':value', $value, PDO::PARAM_STR);
        if ($email) $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->execute();

        $data = $result->fetch(PDO::FETCH_ASSOC);
        if($data && !empty($data)){
            unset($data['pass'], $data['login']);
            return $data;
        } 
        else {
            return false;
        }
    }

    public static function updateUserFields($user, $fields = array()){
        if (!$user || !isset($user['user_id']) || !(int)$user['user_id']) {
            return false;
        }

        $user_id = (int)$user['user_id'];
        if (isset($user_data['user_id'])) unset($fields['user_id']);
        if (isset($user_data['user_name']) && !$user_data['user_name']) {
            unset($fields['user_name']);
        }

        if ($fields) {
            if (isset($fields['from_id'])) {
                unset($fields['from_id']); // do not update partner id 
            }

            $new_data = array();
            foreach ($fields as $name => $value) {
                if ($value!==null) {
                    $new_data[] = "$name = :$name";
                }
                else{
                    unset($fields[$name]);
                }
            }

            $db = Db::getConnection();
            $sql = 'UPDATE '.PREFICS.'users SET '.implode(', ', $new_data).' WHERE user_id = '.$user_id;
            $result = $db->prepare($sql);
            $string_fields = self::getStringFields();
            foreach ($fields as $name => &$value){
                if (in_array($name, $string_fields)) {
                    $result->bindParam(':'.$name, $value, PDO::PARAM_STR);
                }
                else{
                    $result->bindParam(':'.$name, $value, PDO::PARAM_INT);
                } 
            }
            $result->execute();
            return array_replace($user,$fields);               
        }
        
        return $user;
    }

    
    // add new user and check exist by email
    public static function addNewUser($user_data, $login_letter, $send_login = 0) { 
        $result = array('success'=>0, 'user'=> 0);
        $email = null;
        if (isset($user_data['email']) && $user_data['email']) {
            if(!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
                $result['error_message'] = 'Email is not valid: '.$user_data['email'];
                return $result;
            }

            $email = $user_data['email']; 
            $exist_user = User::getUserIDatEmail($email);
            if ($exist_user!==false) {
                $result['error_message'] = 'Imported user is already exist: '.$exist_user;
                return $result;
            }
        }
        else{
            $result['error_message'] = 'Email field is required';
            return $result;
        }

        // id, email, role, pass, status - required fields
        $user_data['role'] = 'user'; 
        if (isset($user_data['user_id'])) unset($user_data['user_id']);
        if (!isset($user_data['user_name']) || $user_data['user_name'] == null) $user_data['user_name'] = 'Subscriber';
        if (!isset($user_data['status'])) {
            $user_data['status'] = 1;
        }

        // create password 
        $chars="abcdefghigklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ1234567890";
        $max=8;
        $size = strlen($chars)-1;
        $password = null; 
        while($max--) $password.= $chars[mt_rand(0,$size)];
        $user_data['pass'] = password_hash($password, PASSWORD_DEFAULT);


        // set reg date and compose reg key
        $date = time();
        $user_data['enter_time'] = $date;
        $user_data['reg_date'] = $date;
        $user_data['reg_key'] = md5($date);


        $fields = array_keys($user_data);
        $fields_db_vars = array_map(function($e){return ':'.$e;}, $fields);        
        $sql = 'INSERT INTO '.PREFICS.'users ('.implode(', ', $fields) .') VALUES ('.implode(', ', $fields_db_vars).')';
        
        $db = Db::getConnection();
        $insert_result = $db->prepare($sql); 
        foreach ($user_data as $name => &$value) {
            if (in_array($name, self::getStringFields())) {
                $insert_result->bindParam(':'.$name, $value, PDO::PARAM_STR);
            }
            else{
                $insert_result->bindParam(':'.$name, $value, PDO::PARAM_INT);
            } 
        }

        $insert_result->execute();
        $user_id = $db->lastInsertId();
        
        if ($user_id) {
            $user = array_merge(['id'=>(int)$user_id,'password' => $password,'name'=>$user_data['user_name']], User::getUserById((int)$user_id));
            unset($user['user_id'], $user['login'], $user['user_name'], $user['pass'], $user['reg_key']);
            $result['user'] = $user;
            $result['success'] = 1;
        }
        
        if($send_login == 1) {
            Email::SendLogin($user_data['user_name'], $email, $password, $login_letter);   
        }

        return $result;  
    }
    
    // // СОЗДАНИЕ / ОБНОВЛЕНИЕ ПОЛЬЗОВАТЕЛЯ + ВОЗВРАТ ЕГО ДАННЫХ 
    // С проверкой существования по заданному полю и обновлением is_client, если потом покупается платный продукт
    // И с отправкой доступа для клиента
    public static function saveUser($search_field, $search_value, $user_data, $groups, $responder, $login_letter, $send_login = 1) {
        $result = array('success'=>0, 'user'=> 0);

        // валидировать емейл
        if(isset($user_data['email']) && $user_data['email'] && !filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
            $result['error_message'] = 'Email is not valid: '.$user_data['email'];
            return $result;
        }

        $email = isset($user_data['email'])?$user_data['email']:null;
        // search existed user by feld or by email
        $user = self::getUserByField($search_value, $search_field, $email);

        if ($user && isset($user['user_id'])) {
            if ($email && strripos($user['email'], '@vk.com')) {
                $user_data['email'] = $email;
            }
            else{
                unset($user_data['email']);
            }
            if (isset($user['channel_id'])) {
                unset($user_data['channel_id']);
            }
            $user = self::updateUserFields($user, $user_data); 
            $user = array_merge(array('id'=>(int)$user['user_id'],'name'=>$user['user_name']), $user);
            unset($user['user_id'], $user['login'], $user['user_name'], $user['pass'], $user['reg_key']);
            $result['user'] = $user;
            $result['success'] = 1;
        }
        else{
            $result = self::addNewUser($user_data, $login_letter, $send_login);
        }   
        
        if($result['success']){

            // Создание пользователя в Connenct
            if(isset($result['user']['id'], $result['user']['vk_url']) && is_numeric($result['user']['id'])
                && ($vk_id = substr($result['user']['vk_url'], strrpos($result['user']['vk_url'], '/id') + 3))
                && is_numeric($vk_id)
            ){
                Connect::addUser($result['user']['id'], 'vkontakte', $vk_id, 'vk.com/id' . $vk_id);
                Connect::setNotifStatus($vk_id, 'vkontakte', true);
            }

            // получить айдишки групп по их системным именам            
            if ($groups) {
               $gids = self::getGroupsByNames($groups);
            }
            else{
                $gids = [];
            }         

            // Добавить юзеру группы
            if($gids){
                foreach($gids as $group_id){
                    self::WriteUserGroup($result['user']['id'], $group_id);
                }
            }                 
            
            // Подписать на автосерию            
            if($responder != 0){ 
                // Получить письма автосерии
                $letter_list = Responder::getAutoLetterList($responder);
                if($letter_list):
                foreach($letter_list as $letter){
                    
                    $send = time() + ($letter['send_time'] * 3600);
                    $status = 0;
                    
                    $task = Responder::AddTask($responder, $letter['letter_id'], $email, $send, $status);                            
                }
                endif;
            }             
        }
        return $result; 
    }

    // ПОЛУЧАЕТ СПИСОК ПОЛЬЗОВАТЕЛЕЙ ДЛЯ АДМИНКИ (по ролям)
    public static function getUserListForAdmin($role, $page = 1, $show_items = 20, $user_group = null, $fields_map = [])
    {
        $offset = ($page - 1) * $show_items;
        $filters = [];

        foreach ($fields_map as $fields_type => $fields) {
            if (is_array($fields)) {
                foreach ($fields as $field_name => $field_value) {
                    if ($field_name=='name' || $field_name=='id') {
                        $field_name = 'user_'.$field_name;
                    }
                    if ($field_name=='login' || $field_name=='pass' || $field_value==='') {
                        continue;
                    }
                    elseif ($fields_type==='text') {
                        $filters[] = "$field_name LIKE '%$field_value%'";  
                    }
                    elseif ($fields_type==='numbers' && (int)$field_value!==0 ) {
                        $filters[] = "$field_name IN (".implode(',', array_map('intval', explode(',', $field_value))).")";  
                    }
                }
            } 
        }
        
        if($user_group != null){
            $filters[] = "user_id IN (SELECT user_id FROM ".PREFICS."user_groups_map WHERE group_id = $user_group )";
        }

        if($role !== 0){ 
            $filters[] = "$role = 1"; 
        }

        $filter = $filters? 'WHERE '.implode(' AND ', $filters):'';
        $query = "SELECT user_id, user_name, email, role, reg_date, status, phone, is_client FROM ".PREFICS."users $filter ORDER BY user_id DESC";
        if ($user_group == null) $query .= " LIMIT $show_items OFFSET $offset";

        // print_r($query); echo "<br>";
        $db = Db::getConnection();
        $result = $db->query($query);
        $i = 0;
        while($row = $result->fetch()){
            $data[$i]['user_id'] = $row['user_id'];
            $data[$i]['user_name'] = $row['user_name'];
            $data[$i]['email'] = $row['email'];
            $data[$i]['role'] = $row['role'];
            $data[$i]['reg_date'] = $row['reg_date'];
            $data[$i]['status'] = $row['status'];
            $data[$i]['phone'] = $row['phone'];
            $data[$i]['is_client'] = $row['is_client'];
            $i++;
        }
        if(isset($data) && !empty($data)) return $data;
        else return false;
    } 

    /**
     * skyCurl
     * @param  string  $link  link for post
     * @param  array   $query query params
     * @param  string  $type  method: POST, GET, POST-JSON, PUT, PATCH, DELETE
     * @return string         request response
     */
    public static function skyCurl($url, $query = [], $type = 'POST', $headers = [], $return = true){
        $type = strtoupper($type);
        $response = false;
        $timeout = 65;

        $curl_opt = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => $return,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_POST => true,
            CURLOPT_FOLLOWLOCATION =>true
        );
        
        if ($type == 'POST' || $type == 'POST-JSON')
           $curl_opt[CURLOPT_POST] = true;
        
        if($type == 'POST')
            $curl_opt[CURLOPT_POSTFIELDS] = http_build_query($query);

        elseif ($type=='GET') {
            $curl_opt[CURLOPT_URL] = $url . empty($query) ? '' : '?' . http_build_query($query);
            $curl_opt[CURLOPT_HTTPGET] = true;
        }

        else {
            $headers = array_merge(array('Content-Type: application/json','Accept: application/json'),$headers);
            $curl_opt[CURLOPT_URL] = $url;
            $curl_opt[CURLOPT_POSTFIELDS] = json_encode($query,JSON_UNESCAPED_UNICODE);

            if ($type!='POST-JSON') 
                $curl_opt[CURLOPT_CUSTOMREQUEST] = $type;
        }

        if ($headers) {
            $curl_opt[CURLOPT_HTTPHEADER] = $headers;                          
        }

        $skyCurl = curl_init();
        curl_setopt_array($skyCurl, $curl_opt);
        $response = curl_exec($skyCurl);

        if (is_string($response) && $response && !mb_check_encoding($response, 'UTF-8') && mb_check_encoding($response, 'windows-1251')) 
            $response = mb_convert_encoding($response, 'UTF-8', 'Windows-1251');

        global $http_code;

        if (curl_errno($skyCurl)) 
            $http_code = curl_error($skyCurl);
        
        else
            $http_code = curl_getinfo($skyCurl, CURLINFO_HTTP_CODE);
        
        curl_close($skyCurl); 
        
        
        return $response;           
    }

    // $result = vk_request($vk_key,'users.get',array('user_ids'=>26187274),'5.103');
    /**
     * [vk_request description]
     * @param  string $key    [description]
     * @param  string $method [description]
     * @param  array  $params [description]
     * @param  string $v      [description]
     * @return array        
     */
    public static function vk_request($token = '', $method = '', $params = [], $v = '5.103') {
        $res = [];

        if ($token) {
            $params = array_merge(
                ['access_token' => $key, 'v' => $v],
                $params
            );

            $request_result = self::skyCurl('https://api.vk.com/method/' . $method, $params);

            $res = json_decode($request_result, true);
        }

        else
            $res = [
                'success' => false,
                'message' => 'Reqired params not found'
            ];
        
        return $res;  
    }


    // VK ID ПО ЕМЕЙЛ
    public static function getVKbyEmail($email) {
        $db = Db::getConnection();

        $sql = "
            SELECT user_id, vk_url 
            FROM ".PREFICS."users 
            WHERE email = :email
        ";
        $result = $db->prepare($sql);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->execute();

        $data = $result->fetch(PDO::FETCH_ASSOC);

        if(isset($data) && !empty($data)) 
            return (int) preg_replace('/[^0-9]/', '', $data['vk_url']);

        return false;
    }


    public static function sendMessToVK($settings='', $vk_user_id=0, $text='', $params=array()) {
        $res = [];

        if ($vk_user_id && $text
            && !empty($settings['access_token'])
            && !empty($settings['group_id'])
            && !empty($settings['v']) 
        ) {
            $params = array_replace(
                [
                    'access_token' => $settings['access_token'], 
                    'group_id'     => $settings['group_id'],
                    'v'            => $settings['v'], 

                    'peer_id'      => (int) $vk_user_id,
                    'message'      => $text,

                    'random_id' => time() . rand(111,999),
                    'dont_parse_links' => 1
                ],
                $params
            );

            $res = self::vk_request($settings['access_token'], 'messages.send', $params);
        } 

        else
            $res = [
                'success' => false,
                'message' => 'Reqired params not found',
                'function' => 'sendMessToVK'
            ];
        
        return $res;  
    }

    public static function sendMessToVKbyEmail($email='', $html='', $params=array()) {   
        $settings = self::getSettings('chat');  
        $vk_user_id = 0;
        $vk_user_id = self::getVKbyEmail($email);            

        if ($vk_user_id) {
            $reg = array(
                '/<hr class=".*?\bend\b.*?" \/>[\s\S]+/imu',
                '/<p>&nbsp;<\/p>/imu',
                '/<\/p>|<\/h[1-6]>/imu',
                '/<br\b.*?>/imu'
            ); // delete non usefull symbols and html code   
            $html = preg_replace($reg, array('',"\n","</p>\n","\n"), $html);

            // find attachments 
            preg_match_all('/<img.+?alt=("([a-z]+[0-9]+_[0-9]+,*)+?").*?\/>/imu', $html, $m);

            if ($m && isset($m[1][0])) {
                $attachments = [];

                foreach ($m[1] as $matched) {
                    $matched = trim($matched, ' "');
                    $attach_array = array_map('trim', explode(',', $matched));    
                    $attachments = array_merge($attachments, $attach_array); 
                }

                if ($attachments) 
                    $params['attachment'] = implode(',', array_slice($attachments, 0, 10));
            }

            $text = trim(html_entity_decode(strip_tags($html)));
            $res = self::sendMessToVK($settings, $vk_user_id, $text, $params);
        }

        else
            $res = [
                'success' => false,
                'message' => 'Reqired params not found',
                'function' => 'sendMessToVKbyEmail'
            ];
        
      return $res;  
    }

}
