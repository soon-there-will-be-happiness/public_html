<?php 
namespace Connect\Telegram\bot\src;

defined('CONNECT_TG_BOT') or die;

class Telegram {


    const EVENT_DEL_USER_FROM_CHAT = 1;
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUP = 2; // СОБЫТИЕ ПРИ УДАЛЕНИИ ГРУППЫ
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUPS = 3; // СОБЫТИЕ ПРИ УДАЛЕНИИ ГРУПП (ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЛИ РЕДАКТИРОВАНИИ ЕГО ГРУПП)
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_SUBS = 4; // СОБЫТИЕ ПРИ УДАЛЕНИИ ПОДПИСКИ
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES = 5; // СОБЫТИЕ ПРИ УДАЛЕНИИ ЗАЙЦЕВ ИЗ АДМИНКИ
    const EVENT_DEL_USER_FROM_BLACKLIST = 6; // СОБЫТИЕ ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЗ ЧС TG

    /** @var mainFunctions */
    public static $api = null;


    /**
     * @return array
     */
    public static function getEventsTitles() {
        $events = [
            self::EVENT_DEL_USER_FROM_CHAT => 'Вход пользователя в чат/канал',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUP => 'Удаление группы',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUPS => 'Удаление пользователя или его группы из админки',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_SUBS => 'Удаление подписки',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES => 'Удаление зайцев (из админки)',
            self::EVENT_DEL_USER_FROM_BLACKLIST => 'Удаление пользователя из ЧС',
        ];

        return $events;
    }


    /***
     * Добавить юзера в таблицу чатов
     * @param array $chat
     * @param int $user_id
     */
    public static function addChatUser(array $chat, int $user_id){
        $chat_id = $chat['chat_id'];
        $data = &$chat['data'];

        $data['users'] ?? $data['users'] = [];

        if(!isset($data['users'][$user_id]))
            $data['users'][$user_id] = time();

        \Db::_update('telegram_chats', ['data' => $data], ['chat_id' => $chat_id]);
    }

    /**
     * Получить чат
     * @param array $chat
     * @param bool $rec
     * @param bool $upd
     *
     * @return array
     */
    public static function getChat(array $chat, bool $rec = true, bool $upd = true): array{
        $chat_id = $chat['id'];
        $title = $chat['title'] ?? ' ';

        $data = \Db::_select_one('telegram_chats', ['chat_id' => $chat_id], [], true);

        if(empty($data)){
            if($rec && self::addChat($chat_id, $title, 1, ['type' => @ $chat['type']]))
                return self::getChat($chat, $rec, $upd);

            return [];
        }

        $data['params'] ?? $data['params'] = [];
        
        if($upd 
            && ($data['title'] != $title || @ $data['params']['type'] != @ $chat['type'])
        ){
            isset($data['params']) ?: $data['params'] = [];
            unset($chat['id'], $chat['title']);
            $data['params'] = $chat;
            $data['title'] = $title;
            self::updChat($chat_id, $data);
        }

        return (array) $data;
    }

    /**
     * Добавить чат
     * @param int $chat_id
     * @param string $title
     * @param int $status
     * @param array $params
     *
     * @return bool
     */
    public static function addChat(int $chat_id, string $title = '', int $status = 1, array $params = []): bool{
        $status = (int) $status < 3 && $status >= 0 ? $status : 0;

        return (bool) \Db::_insert_or_upd('telegram_chats', [
                'chat_id' => $chat_id, 
                'title' => $title, 
                'status' => $status, 
                'params' => $params
            ], [
                'title' => $title, 
                'status' => $status, 
                'params' => $params
            ]
        );
    }

    /**
     * Обновить чат
     * @param int $chat_id
     * @param array $data
     *
     * @return bool
     */
    public static function updChat(int $chat_id, array $data): bool{
        return (bool) \Db::_update("telegram_chats", $data,  
            ['integer' => 
                ['chat_id' => $chat_id]
            ],
            ['title', 'status', 'params']
        );
    } 
    
    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ ПО ПОЛЮ USERNAME
     * @param $username
     * @return bool
     */
    public static function getUserByUsername($username) {
        $data = self::searchUser('user_name', $username);

        return !empty($data) ? $data : false;
    }

    /**
     * Найти юзера
     * @param $key
     * @param $value
     *
     * @return bool|null
     */
    private function searchUser($key, $value){
        return \Db::_select_one('telegram_users', [$key, $value]);
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ ПО ПОЛЮ SM_USER_ID
     *
     * @param $sm_user_id
     * @param $req_data
     *
     * @return bool|array
     */
    public static function getUserBySmUserId($sm_user_id, $req_data = []) {
        return self::getUser('sm_user_id', $sm_user_id, $req_data);
    }

    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ ПО ПОЛЮ USER_ID
     *
     * @param int $user_id
     * @param array $req_data
     *
     * @return bool|mixed
     */
    public static function getUserByUserId(int $user_id, $req_data = []) {
        return self::getUser('user_id', $user_id, $req_data);
    }

    /**
     * Получить пользователя
     * @param string $key
     * @param $value
     * @param $req_data
     *
     * @return bool|null
     */
    private static function getUser(string $key, $value, $req_data) {
        $data = \Db::_select_one_join('telegram_users', 'connect_users',
            [
                ['sm_user_id', 'user_id']
            ],
            [
                '1t' => [
                    $key => $value
                ]
            ],
            [
                '1t.*',
                '2t.tg_id',
                '2t.params',
                '2t.auth_hash',
            ],
            true
        );

        if(empty($data))
            return null;

        self::updUserData($data, $req_data);

        return $data;
    }


    /**
     * при необходимости обновить данные таблиц
     * @param $db_data
     * @param array $req_data
     */
    private static function updUserData($db_data, $req_data = []){

        if(empty(@ $db_data['tg_id']) && !empty($db_data['sm_user_id'])){ // если нет в базе Connect -> добавляем
            $username = $req_data['username'] ?? '@' . $db_data['user_name'] ?? $db_data['first_name'] . ' ' . $db_data['last_name'] ?? '';

            \Connect::addUser($db_data['sm_user_id'], 'telegram', $db_data['user_id'], $username, '');

            if($db_data['bot_chat_id'] == $db_data['user_id'])
                \Connect::setNotifStatus($db_data['user_id'], 'telegram', true);
        }

        if(!$req_data || empty($req_data))
            return;

        $upd = [];

        if (isset($req_data['first_name']) && !empty($req_data['first_name']) && $db_data['first_name'] != $req_data['first_name'])
            $upd['first_name'] = $req_data['first_name'];

        if (isset($req_data['last_name']) && !empty($req_data['last_name']) && $db_data['last_name'] != $req_data['last_name'])
            $upd['last_name'] = $req_data['last_name'];

        if (isset($req_data['username']) && !empty($req_data['username']) && $db_data['user_name'] != $req_data['username']){
            $upd['user_name'] = $req_data['username'];

            if(isset($db_data['sm_user_id']) && !empty($db_data['sm_user_id']))
                \Connect::updUsernameForUsersDB($db_data['sm_user_id'], 'telegram', '@' . $req_data['username']);
        }

        if(empty($upd))
            return;

        \Db::_update('telegram_users', $upd, ['user_id' => $db_data['user_id']]);
    }

    /**
     * ПОЛУЧИТЬ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ
     * @param bool $offset
     * @param bool $limit
     * @return array|bool
     */
    public static function getUsers($offset = false, $limit = false) {
        $db = \Db::getConnection();
        $query = "SELECT tg.*, cu.tg_id, cu.params, cu.auth_hash  FROM " . PREFICS . "telegram_users AS tg
            LEFT JOIN " . PREFICS . "connect_users AS cu  ON tg.sm_user_id = cu.user_id WHERE true;";

        $query .= $limit ? " LIMIT $limit" : '';
        $query .= $offset ? " OFFSET $offset" : '';
        $result = $db->query($query);

        $data = [];

        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {

            self::updUserData($row);

            $row['params'] = empty($row['params']) ? [] : @ unserialize($row['params']);

            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ КОЛИЧЕСТВО ВСЕХ ПОЛЬЗОВАТЕЛЕЙ
     * @return mixed
     */
    public static function getTotalUsers() {
        $db = \Db::getConnection();
        $result = $db->query("SELECT COUNT(sm_user_id) FROM " . PREFICS . "telegram_users");
        $count = $result->fetch();

        return $count[0];
    }

    /**
     * СОХРАНИТЬ ПОЛЬЗОВАТЕЛЯ
     * @param $sm_user_id
     * @param $user_id
     * @param $username
     * @param $hash
     * @return bool
     */
    public static function saveUser($sm_user_id, $user_id, $username, $hash) {
        $user = $sm_user_id ? self::getUserBySmUserId2($sm_user_id) : null;
        if ($user && $user['user_id'] != $user_id) {
            return false;
        }

        return \Db::_update('telegram_users',
            [
                'sm_user_id' => $sm_user_id,
                'user_name' => $username,
                'hash' => $hash
            ],
            [
                'user_id' => $user_id
            ]
        );
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ ПО ПОЛЮ SM_USER_ID
     * @param $sm_user_id
     * @return bool|mixed
     */
    public static function getUserBySmUserId2($sm_user_id) {
        $db = \Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS."telegram_users WHERE sm_user_id = :sm_user_id");
        $result->bindParam(':sm_user_id', $sm_user_id, \PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(\PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }

    /**
     * СОХРАНИТЬ ПОЛЬЗОВАТЕЛЯ
     *
     * @param $from_data
     * @param int $sm_user_id
     *
     * @return bool
     */
    public static function addUnregisteredUser($from_data, $sm_user_id = 0) {
        $user_id = $from_data['id'];
        $user_name = @ $from_data['username'];
        $first_name = @ $from_data['first_name'];
        $last_name = @ $from_data['last_name'];
        $get_conn_sm_user_id = @ \Connect::getUserByServiceID('telegram', $user_id)['user_id'];

        if ($sm_user_id == 0 && !empty($get_conn_sm_user_id) && is_numeric($get_conn_sm_user_id))
            $sm_user_id = $get_conn_sm_user_id;

        if($sm_user_id == 0 || !is_numeric($sm_user_id))
            $sm_user_id = null;

        $db = \Db::getConnection();
        $sql = "
            INSERT INTO ".PREFICS."telegram_users 
                (sm_user_id, user_id, user_name, first_name, last_name)
            VALUES
                (:sm_user_id, :user_id, :user_name, :first_name, :last_name)
            ON DUPLICATE KEY 
                UPDATE
                    `sm_user_id` = :sm_user_id,
                    `user_id`    = :user_id,
                    `user_name`  = :user_name,
                    `first_name` = :first_name,
                    `last_name`  = :last_name
        ";
        
        $result = $db->prepare($sql);
        $result->bindParam(':sm_user_id', $sm_user_id, \PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $result->bindParam(':user_name', $user_name, \PDO::PARAM_STR);
        $result->bindParam(':first_name', $first_name, \PDO::PARAM_STR);
        $result->bindParam(':last_name', $last_name, \PDO::PARAM_STR);

        return $result->execute();
    }

   


    /**
     * ПРОВЕРИТЬ ПРИВЯЗАН ЛИ ПОЛЬЗОВАТЕЛЬ К ТЕЛЕГРАММУ
     * @param $sm_user_id
     * @param null $nick
     * @return bool
     */
    public static function checkBindingUser($sm_user_id, $nick = null) {
        $tg_user = self::getUserBySmUserId($sm_user_id, []);

        return is_array($tg_user) && isset($tg_user['user_id']);
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧАТА ПО ГРУППЕ
     * @param $sm_user_id
     * @param $group_id
     * @return bool|mixed
     */
    public static function delUserFromChatsToGroup($sm_user_id, $group_id) {
        $group = \User::getUserGroupData($group_id);
        if ($group && $group['del_tg_chats']) {
            return self::delUserFromChats($sm_user_id, null, $group['del_tg_chats'], false, self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUP, $group_id);
        }

        return false;
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧАТА ДЛЯ ГРУПП (ВЫЗЫАЕТСЯ ПРИ УДАЛЕНИИ ГРУПП В АДМИНКЕ, ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЛИ РЕДАКТИРОВАНИИ ГРУПП ПОЛЬЗОВАТЕЛЯ)
     * @param $sm_user_id
     * @param $del_groups
     * @return bool|mixed
     */
    public static function delUserFromChatsToGroups($sm_user_id, $del_groups = null) {
        if ($del_groups === null) {
            $del_groups = \User::getGroupByUser($sm_user_id);
        }
        if (!$del_groups) {
            return false;
        }

        foreach ($del_groups as $group_id) {
            $group = \User::getUserGroupData($group_id);
            if ($group && $group['del_tg_chats']) {
                self::delUserFromChats($sm_user_id, null, $group['del_tg_chats'], false, self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUPS, $group_id);
            }
        }
        return true;
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧАТА ДЛЯ ПОДПИСКИ, ЕСЛИ НЕТ ДЕЙСТВУЮЩЕЙ ПОДПИСКИ
     * @param $sm_user_id
     * @param $del_chats
     * @param $subs_id
     * @return bool|mixed
     */
    public static function delUserFromChatsToSub($sm_user_id, $del_chats, $subs_id) {
        $tg_user = Telegram::getUserBySmUserId($sm_user_id);
        if (!$tg_user['user_id']) {
            return false;
        }

        $api = self::$api;
        if (!$api) {
            return false;
        }

        $del_chats = explode(',', $del_chats);
        foreach ($del_chats as $del_chat) {
            $count = \Member::countActiveSubsWithTgChat2User($del_chat, $sm_user_id); // получить количество действующих подписок для пользователя с этим чатом
            if ($count == 0) {
                $res = self::delUserFromChat($tg_user['user_id'], $del_chat, self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_SUBS, $subs_id);
            }
        }
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧАТОВ
     * @param $sm_user_id
     * @param $user_id
     * @param $del_chats
     * @param bool $use_check проверять, является ли пользователь членом группы/чаты
     * @param $event_type
     * @param $event_value
     * @return bool
     */
    public static function delUserFromChats($sm_user_id, $user_id, $del_chats, $use_check = false, $event_type = self::EVENT_DEL_USER_FROM_CHAT, $event_value = 0) {
        if (($api = self::$api) && !$api)
            return false;

        $result = false;

        foreach (explode(',', $del_chats) as $del_chat) {
            $del_chat = trim($del_chat);

            if (!$use_check || $api->getChatMember($user_id, $del_chat))
                $result = self::delUserFromChat($user_id, $del_chat, $event_type, $event_value, $sm_user_id);   
        }

        return $result;
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧАТА
     * @param $api
     * @param $user_id
     * @param $del_chat
     * @param $event
     * @param $event_value
     * @return bool
     */
    public static function delUserFromChat($user_id, $del_chat, $event = null, $event_value = 0, $sm_user_id = 0) {
        if(!self::$api)
            return false;

        if (self::$api->ban($user_id, $del_chat)) {
            self::writeLog($event, $event_value, $sm_user_id, $user_id, $del_chat);
            return true;
        }

        return false;
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧС ДЛЯ ГРУППЫ
     * @param $sm_user_id
     * @param $group_id
     * @return bool|void
     */
    public static function delUserFromBlacklistToGroup($sm_user_id, $group_id) {
        $group = \User::getUserGroupData($group_id);
        if ($group && $group['del_tg_chats']) {
            return self::delUserFromBlacklist($sm_user_id, $group['del_tg_chats']);
        }

        return false;
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ ЧС
     * @param $sm_user_id
     * @param $tg_chats
     * @return bool
     */
    public static function delUserFromBlacklist($sm_user_id, $tg_chats) {
        if (!$api = self::$api)
            return false;
        
        $result = false;
        $tg_user = Telegram::getUserBySmUserId($sm_user_id);
        if ($tg_user['user_id']) {
            $tg_chats  = is_array($tg_chats) ? $tg_chats : explode(',', $tg_chats);
            foreach ($tg_chats as $tg_chat) {
                $tg_chat = trim($tg_chat);
                $res = $api->unban($tg_user['user_id'], $tg_chat);
                if ($res) {
                    self::writeLog(self::EVENT_DEL_USER_FROM_BLACKLIST, 0, $sm_user_id, $tg_user['user_id'], $tg_chat);
                    $result = true;
                }
            }
        }

        return $result;
    }


    /**
     * ПОЛУЧИТЬ КОЛИЧЕСТВО ЛОГОВ
     * @param $filter
     * @return mixed
     */
    public static function getTotalLog($filter) {
        $clauses = [];
        if ($filter) {
            if ($filter['email']) {
                $clauses[] = "u.email LIKE '%{$filter['email']}%'";
            }
            if ($filter['username']) {
                $clauses[] = "tu.user_name = '{$filter['username']}'";
            }
            if ($filter['sm_user_id'] !== null) {
                $clauses[] = "tl.sm_user_id = {$filter['sm_user_id']}";
            }
            if ($filter['user_id'] !== null) {
                $clauses[] = "tl.user_id = {$filter['user_id']}";
            }
            if ($filter['chat_id']) {
                $clauses[] = "tl.chat_id = '{$filter['chat_id']}'";
            }
            if ($filter['event_type']) {
                $clauses[] = "tl.event_type = '{$filter['event_type']}'";
            }
            if ($filter['start']) {
                $clauses[] = "tl.date >= {$filter['start']}";
            }
            if ($filter['finish']) {
                $clauses[] = "tl.date < {$filter['finish']}";
            }
        }

        $where = !empty($clauses) ? (' WHERE ' . implode(" AND ", $clauses)) : '';

        $db = \Db::getConnection();
        $query = "SELECT COUNT(tl.log_id) FROM ".PREFICS."telegram_log AS tl
                  INNER JOIN ".PREFICS."telegram_users AS tu ON tu.sm_user_id = tl.sm_user_id
                  INNER JOIN ".PREFICS."users AS u ON u.user_id = tl.sm_user_id $where";
        $result = $db->query($query);
        $count = $result->fetch();

        return $count[0];
    }


    /**
     * ПОЛУЧИТЬ СПИСОК ЛОГОВ
     * @param $filter
     * @param $page
     * @param $show_items
     * @return array|bool
     */
    public static function getLogList($filter, $page, $show_items) {
        $clauses = [];
        if ($filter) {
            if ($filter['email']) {
                $clauses[] = "u.email LIKE '%{$filter['email']}%'";
            }
            if ($filter['username']) {
                $clauses[] = "tu.user_name = '{$filter['username']}'";
            }
            if ($filter['sm_user_id'] !== null) {
                $clauses[] = "tl.sm_user_id = {$filter['sm_user_id']}";
            }
            if ($filter['user_id'] !== null) {
                $clauses[] = "tl.user_id = {$filter['user_id']}";
            }
            if ($filter['chat_id']) {
                $clauses[] = "tl.chat_id = '{$filter['chat_id']}'";
            }
            if ($filter['event_type']) {
                $clauses[] = "tl.event_type = '{$filter['event_type']}'";
            }
            if ($filter['start']) {
                $clauses[] = "tl.date >= {$filter['start']}";
            }
            if ($filter['finish']) {
                $clauses[] = "tl.date < {$filter['finish']}";
            }
        }

        $where = !empty($clauses) ? (' WHERE ' . implode(" AND ", $clauses)) : '';

        $offset = ($page - 1) * $show_items;
        $query = "SELECT tl.* FROM ".PREFICS."telegram_log AS tl
                  INNER JOIN ".PREFICS."telegram_users AS tu ON tu.sm_user_id = tl.sm_user_id
                  INNER JOIN ".PREFICS."users AS u ON u.user_id = tl.sm_user_id $where
                  GROUP BY tl.log_id ORDER BY date DESC LIMIT $show_items OFFSET $offset";

        $db = \Db::getConnection();
        $result = $db->query($query);

        $data = [];
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ СООБЩЕНИЕ СОБЫТИЯ
     * @param $log
     * @return string
     */
    public static function getMessageToEvent($log) {
        switch ($log['event_type']) {
            case self::EVENT_DEL_USER_FROM_CHAT: //
                return "Пользователь удален из чата {$log['chat_id']}";

            case self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUP: // ПРИ УДАЛЕНИИ ГРУППЫ
                return "Пользователь удален из чата {$log['chat_id']} при удалении <a href=\"/admin/usergroups/edit/{$log['event_value']}\" target=\"_blank\">группы с id: {$log['event_value']}</a>";

            case self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUPS: // ПРИ УДАЛЕНИИ ГРУПП (ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЛИ РЕДАКТИРОВАНИИ ЕГО ГРУПП)
                return "Пользователь удален из чата {$log['chat_id']} при удалении <a href=\"/admin/usergroups/edit/{$log['event_value']}\" target=\"_blank\">группы с id: {$log['event_value']}</a> (при удалении пользователя или редактировании групп из админки)";

            case self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_SUBS: // ПРИ УДАЛЕНИИ ПОДПИСКИ
                return "Пользователь удален из чата {$log['chat_id']} при удалении <a href=\"/admin/membersubs/edit/{$log['event_value']}\" target=\"_blank\">подписки с id: {$log['event_value']}</a>";

            case self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES: // ПРИ УДАЛЕНИИ ЗАЙЦЕВ ИЗ АДМИНКИ
                return "Пользователь удален из чата {$log['chat_id']} при удалении зайцев {$log['event_value']}";

            case self::EVENT_DEL_USER_FROM_BLACKLIST: // ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЗ ЧС TG
                return "Пользователь удален из чс для чата {$log['chat_id']}";
        }
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ СПИСКА УЧАСТНИКОВ
     * @param $sm_user_id
     * @return bool
     */
    public static function delMember($sm_user_id) {
        $db = \Db::getConnection();
        $sql = "DELETE FROM ".PREFICS."telegram_users WHERE sm_user_id = :sm_user_id";

        $result = $db->prepare($sql);
        $result->bindParam(':sm_user_id', $sm_user_id, \PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ СПИСКА УЧАСТНИКОВ ПО ПОЛЯМ USER_ID И SM_USER_ID
     * @param $user_id
     * @param $sm_user_id
     * @return bool
     */
    public static function delMemberByUserId($user_id, $sm_user_id) {
        $db = \Db::getConnection();
        $sql = "DELETE FROM ".PREFICS."telegram_users WHERE sm_user_id = :sm_user_id AND user_id = :user_id";
        $result = $db->prepare($sql);
        $result->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $result->bindParam(':sm_user_id', $sm_user_id, \PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ СПИСОК ЧАТОВ ДЛЯ УДАЛЕНИЯ ИЗ НИЗ ПОЛЬЗОВАТАЛЕЙ
     * @param $ids
     * @return bool
     */
    public static function getGroupsChats($ids = []) {
        $db = \Db::getConnection();
        $where = "WHERE del_tg_chats <> '' AND del_tg_chats IS NOT NULL" . ($ids ? ' AND group_id IN ('.implode(',', $ids).')' : '');

        $result = $db->query("SELECT GROUP_CONCAT(DISTINCT del_tg_chats) FROM ".PREFICS."user_groups $where");
        $data = $result->fetch();

        return !empty($data) ? $data[0] : false;
    }


    /**
     * ПОЛУЧИТЬ СПИСОК ПЛАНОВ ПОДПИСОК ДЛЯ УДАЛЕНИЯ ИЗ НИЗ ПОЛЬЗОВАТАЛЕЙ
     * @param array $ids
     * @return bool
     */
    public static function getPlanesChats($ids = []) {
        $db = \Db::getConnection();
        $where = "WHERE del_tg_chats <> '' AND del_tg_chats IS NOT NULL AND status = 1" . ($ids ? ' AND id IN ('.implode(',', $ids).')' : '');
        $result = $db->query("SELECT GROUP_CONCAT(DISTINCT del_tg_chats) FROM ".PREFICS."member_planes $where");
        $data = $result->fetch();

        return !empty($data) ? $data[0] : false;
    }


    /**
     * УДАЛИТЬ ЗАЙЦЕВ ИЗ КАНАЛОВ
     * @param $users
     * @return bool|int
     */
    public static function delStowaways($users) {
        $all_chats = self::getChats();
        $del_users = 0;

        if ($all_chats && $users) {
            foreach ($users as $key => $user) {
                $delete = false;
                $users_chats = $user['sm_user_id'] ? self::getChats($user['sm_user_id']) : [];
                $del_chats = array_diff($all_chats, $users_chats);

                if (!empty($del_chats)) {
                    foreach ($del_chats as $del_chat) {
                        if (self::delUserFromChats($user['sm_user_id'], $user['user_id'], $del_chat, true, self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES, '')) {
                            $delete = true;
                        }
                    }
                    if ($delete) {
                        $del_users++;
                    }
                }
            }
        }

        return $del_users;
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТАЛЕЙ ИЗ ЧС
     * @param $users
     * @return bool|int
     */
    public static function removeFromBlacklist($users) {
        $all_chats = self::getChats();
        $processed_users = 0;

        if ($all_chats && $users) {
            foreach ($users as $key => $user) {
                $users_chats = $user['sm_user_id'] ? self::getChats($user['sm_user_id']) : [];
                $remove_from_blacklist_chats = array_intersect($all_chats, $users_chats);

                if (!empty($remove_from_blacklist_chats) && self::delUserFromBlacklist($user['sm_user_id'], $remove_from_blacklist_chats)) {
                    $processed_users++;
                }
            }
        }

        return $processed_users;
    }


    /**
     * УДАЛИТЬ ЗАЙЦА ИЗ КАНАЛОВ
     * @param $user_id
     * @param $chats
     * @return int
     */
    public static function delStowaway2Chanel($user_id, $chats) {
        $api = self::$api;
        if (!$api) 
            return false;
        
        $is_del = false;
        foreach ($chats as $chat_id) {
            if (self::delUserFromChat($api, $user_id, $chat_id, self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES)) 
                $is_del = true;
        }

        return $is_del;
    }


    public static function getChats($sm_user_id = null) {
        if ($sm_user_id) {
            $user_groups = \User::getGroupByUser($sm_user_id);
            $user_planes = \Member::getPlanesByUser($sm_user_id, 1, true);
            $groups_chats = $user_groups ? self::getGroupsChats($user_groups) : null;
            $planes_chats = $user_planes ? self::getPlanesChats($user_planes) : null;
        } 

        else{
            $groups_chats = self::getGroupsChats();
            $planes_chats = self::getPlanesChats();
        }

        return !$groups_chats && !$planes_chats
            ? []
            : self::getUniqueTGChats($groups_chats, $planes_chats);
    }


    /**
     * ПОЛУЧИТЬ УНИКАЛЬНЫЕ ЧАТЫ TG
     * @param $group_chats
     * @param $planes_chats
     * @return array|mixed|string
     */
    private static function getUniqueTGChats($group_chats, $planes_chats) {
        if (!$group_chats && $planes_chats) 
            $tg_chats = $planes_chats;
        
        elseif ($group_chats && $planes_chats) 
            $tg_chats = "$group_chats,$planes_chats";
        
        else 
            $tg_chats = $group_chats ? $group_chats : $planes_chats;

        $tg_chats = str_replace(' ', '', $tg_chats);
        $tg_chats = array_unique(explode(',', $tg_chats));

        return $tg_chats;
    }


    /**
     * ЗАПИСАТЬ ЛОГ
     * @param $event_type
     * @param $event_value
     * @param $sm_user_id
     * @param $user_id
     * @param $chat_id
     * @return bool
     */
    public static function writeLog($event_type, $event_value, $sm_user_id, $user_id, $chat_id) {
        $db = \Db::getConnection();
        $date = time();
        $sql = "INSERT INTO ".PREFICS."telegram_log (event_type, event_value, sm_user_id, user_id, chat_id, date)
                VALUES(:event_type, :event_value, :sm_user_id, :user_id, :chat_id, '$date')";

        $result = $db->prepare($sql);
        $result->bindParam(':event_type', $event_type, \PDO::PARAM_INT);
        $result->bindParam(':event_value', $event_value, \PDO::PARAM_INT);
        $result->bindParam(':sm_user_id', $sm_user_id, \PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $result->bindParam(':chat_id', $chat_id, \PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * УДАЛЕНИЕ СТАРЫХ ЛОГОВ
     * @param $date
     * @return bool
     */
    public static function delOldLogs($date) {
        $db = \Db::getConnection();
        $result = $db->prepare('DELETE FROM '.PREFICS.'telegram_log WHERE date < :date');
        $result->bindParam(':date', $date, \PDO::PARAM_INT);

        return $result->execute();
    }

}