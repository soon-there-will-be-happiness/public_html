<?php defined('BILLINGMASTER') or die;


class Conditions {

    const ACTION_ADD_GROUP = 1;
    const ACTION_DEL_GROUP = 2;
    const ACTION_SUBSCRIBE_MAILING = 3;
    const ACTION_UNSUBSCRIBE_MAILING = 4;
    const ACTION_SEND_LETTER = 5;
    const ACTION_SEND_SMS = 6;
    const ACTION_SEND_WEBHOOK = 7;
    const ACTION_DELETE_USER = 8;
    const ACTION_ADD_TO_MEMBERSHIP = 9;
    const ACTION_DEL_TO_MEMBERSHIP = 10;
    const ACTION_USER_TO_PARTNER = 11;

    const ACTION_REPEAT_ONE_STATUS = 0;
    const ACTION_REPEAT_REGULARLY_STATUS = 1;
    const ACTION_REPEAT_AGAIN_STATUS = 0;

    const EXECUTE_DATE_TYPE_SCHEDULER = 1; // по планировщику
    const EXECUTE_DATE_TYPE_WEEK_DAY = 2; // день недели
    const EXECUTE_DATE_TYPE_SELECT_DATE = 3; // конкретная дата

    const COUNT_ITEMS_FOR_ITERATION = 100;

    const RESULT_STATUS_NOT_DONE = 0;
    const RESULT_STATUS_DONE = 1;

    
    /**
     * ОБРАБОТЧИК УСЛОВИЙ
     * @param $condition
     * @param $use_cron
     * @return bool
     */
    public static function renderCond($condition, $use_cron) {
        if ($condition['actions'] && $condition['segment_id']) {
            $filter_model = SegmentFilter::getFilterModel($condition['filter_model']);
            $segment_data = SegmentFilter::getSegmentData($filter_model, $condition['segment_id']);
            $clauses = $segment_data ? $filter_model::getConditions($segment_data, $filter_model) : null;
            $offset = 1;
            $time = time();

            if ($clauses) {
                if ($use_cron) {
                    if (self::isProcessed($condition['id'])) {
                        return false;
                    }

                    self::addQueue($condition['id']);
                }

                while ($data = self::getData2Condition($condition, $clauses, $offset++)) {
                    foreach ($data as $item) {
                        $order_id = $condition['filter_model'] == SegmentFilter::FILTER_TYPE_ORDERS ? $item['order_id'] : null;
                        $user_id = $condition['filter_model'] == SegmentFilter::FILTER_TYPE_USERS ? $item['user_id'] : null;

                        foreach ($condition['actions'] as $action) {
                            if (!$condition['cond_repeat'] // если нет повторения
                                && (!$use_cron || $condition['params']['execute_date_type'] != self::EXECUTE_DATE_TYPE_SELECT_DATE) // если выполнить сразу или не стоит конкретное время
                                && self::isActionRun($condition, $action, $item, $time)) {
                                continue;
                            }

                            self::addActionsQueue($condition['id'], $action['action_id'], $user_id, $order_id);
                        }
                    }
                }

                if ($use_cron) {
                    $next_action = -1;
                    if ($condition['params']['execute_date_type'] == self::EXECUTE_DATE_TYPE_SCHEDULER) {
                        $next_action = $condition['params']['period'] ? $time + $condition['params']['period'] * 60 : 0;
                    } elseif($condition['params']['execute_date_type'] == self::EXECUTE_DATE_TYPE_WEEK_DAY) {
                        if ($condition['next_action'] < $time) {
                            $next_action = Conditions::getNextActionDate(0, $condition['params']);
                        }
                    }

                    self::updNextAction($condition['id'], $next_action);
                }

                while ($queue_actions = self::getActionsQueue($condition['id'])) {
                    foreach ($queue_actions as $queue_action) {
                        $user = $order = null;
                        $action = self::getConditionAction($queue_action['action_id']);
                        if (!$action) {
                            continue;
                        }

                        if ($queue_action['order_id']) {
                            $order = Order::getOrder($queue_action['order_id']);
                            $order_info = $order['order_info'] != null ? unserialize(base64_decode($order['order_info'])) : null;
                            $surname = isset($order_info['surname']) ? $order_info['surname'] : null;
                            $user = [
                                'user_id' => 0,
                                'email' => trim($order['client_email']),
                                'user_name' => $order['client_name'],
                                'surname' => $surname,
                                'phone' => $order['client_phone'],
                                'city' => $order['client_city'],
                                'address' => $order['client_address'],
                                'zipcode' => $order['client_index'],
                            ];

                            if ($_user = User::getUserDataByEmail($order['client_email'])) {
                                $user['user_id'] = $_user['user_id'];
                                $user = array_merge($_user, $user);
                            }
                        } else {
                            $user = User::getUserById($queue_action['user_id']);
                        }

                        $result = self::actionCond($action, $user, $order);
                        $order_id = $order ? $order['order_id'] : null;
                        $act_params = self::getActionParams($action);

                        self::addActionResult($condition['id'], $action['action_id'], $action['action'], $user['user_id'],
                            $user['email'], $order_id, $result, $time, $act_params
                        );

                        self::delActionsQueue($queue_action['task_id']);
                    }
                }

                if ($use_cron) {
                    self::delQueue($condition['id']);
                }
            }
        }
    }


    /**
     * @param $condition_id
     * @return bool
     */
    public static function addQueue($condition_id) {
        $db = Db::getConnection();
        $create_date = time();
        $result = $db->prepare("INSERT INTO ".PREFICS."cond_queue(condition_id, status, create_date) VALUES(:condition_id, 0, :create_date)");
        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);
        $result->bindParam(':create_date', $create_date, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $condition_id
     * @param $action_id
     * @param $user_id
     * @param $order_id
     * @return bool
     */
    public static function addActionsQueue($condition_id, $action_id, $user_id, $order_id) {
        $db = Db::getConnection();
        $result = $db->prepare("INSERT INTO ".PREFICS."cond_action_tasks(condition_id, action_id, user_id, order_id)
                                        VALUES(:condition_id, :action_id, :user_id, :order_id)"
        );

        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);
        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $condition_id
     * @return array|bool
     */
    public static function getActionsQueue($condition_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS."cond_action_tasks WHERE condition_id = :condition_id LIMIT ".self::COUNT_ITEMS_FOR_ITERATION);
        $result->bindParam(':condition_id', $condition_id, PDO::FETCH_ASSOC);
        $result->execute();

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $task_id
     * @return bool
     */
    public static function delActionsQueue($task_id) {
        $db = Db::getConnection();
        $result = $db->prepare("DELETE FROM ".PREFICS."cond_action_tasks WHERE task_id = :task_id");
        $result->bindParam(':task_id', $task_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $condition_id
     * @return bool
     */
    public static function delQueue($condition_id) {
        $db = Db::getConnection();
        $result = $db->prepare("DELETE FROM ".PREFICS."cond_queue WHERE condition_id = :condition_id");
        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @return false|PDOStatement
     */
    public static function delQueues() {
        $db = Db::getConnection();
        $result = $db->query("DELETE FROM ".PREFICS."cond_queue");

        return $result;
    }


    /**
     * @param $condition_id
     * @param $date
     * @return bool
     */
    public static function updNextAction($condition_id, $date) {
        $db = Db::getConnection();
        $result = $db->prepare("UPDATE ".PREFICS."conditions SET next_action  = :next_action WHERE id = :condition_id");
        $result->bindParam(':next_action', $date, PDO::PARAM_INT);
        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $condition_id
     * @return mixed
     */
    public static function isProcessed($condition_id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(*) FROM ".PREFICS."cond_queue WHERE condition_id = $condition_id");
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @return array|bool
     */
    public static function getCondQueues() {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."cond_queue");

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $condition
     * @param $clauses
     * @param $offset
     * @return array|bool
     */
    public static function getData2Condition($condition, $clauses, $offset) {
        if ($condition['filter_model'] == SegmentFilter::FILTER_TYPE_ORDERS) {
            $data = Order::getOrdersWithConditions($clauses, $offset, self::COUNT_ITEMS_FOR_ITERATION, true);
        } else {
            $data = User::getUsersWithConditions($clauses, $offset, self::COUNT_ITEMS_FOR_ITERATION, true);
        }

        return $data;
    }


    /**
     * @param $condition
     * @param $action
     * @param $item
     * @param $date
     * @return bool
     */
    public static function isActionRun($condition, $action, $item, $date) {
        $db = Db::getConnection();
        $where = "WHERE action_id = {$action['action_id']} AND status = 1 AND ";
        $where .= $condition['filter_model'] == SegmentFilter::FILTER_TYPE_USERS ? "user_id = {$item['user_id']}" : "order_id = '{$item['order_id']}'";
        $result = $db->query("SELECT COUNT(*) FROM ".PREFICS."cond_actions_completed $where");
        $data = $result->fetch();

        return $data[0] == 0 ? false : true;
    }


    /**
     * @param $action_id
     * @param int $status
     * @return mixed
     */
    public static function getCountResults($action_id, $status = 1) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT COUNT(*) FROM ".PREFICS."cond_actions_completed
                                         WHERE action_id = :action_id AND status = :status");
        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @param $condition_id
     * @param $action_id
     * @param $action
     * @param $user_id
     * @param $user_email
     * @param $order_id
     * @param $act_status
     * @param $date
     * @param null $act_params
     * @return bool
     */
    public static function addActionResult($condition_id, $action_id, $action, $user_id, $user_email, $order_id, $act_status, $date, $act_params = null) {
        $db = Db::getConnection();
        $result = $db->prepare("INSERT INTO ".PREFICS."cond_actions_completed(condition_id, action_id, action,
                                            user_id, user_email, order_id, act_status, date, act_params)
                                        VALUES(:condition_id, :action_id, :action, :user_id, :user_email, :order_id,
                                            :act_status, :date, :act_params)"
        );

        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);
        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);
        $result->bindParam(':action', $action, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':user_email', $user_email, PDO::PARAM_STR);
        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $result->bindParam(':act_status', $act_status, PDO::PARAM_INT);
        $result->bindParam(':date', $date, PDO::PARAM_INT);
        $result->bindParam(':act_params', $act_params, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @param $action_id
     * @param $status
     * @return bool
     */
    public static function updActionResultStatus($action_id, $status) {
        $db = Db::getConnection();
        $result = $db->prepare("UPDATE ".PREFICS."cond_actions_completed SET status = :status
                                        WHERE action_id = :action_id AND status <> :status"
        );

        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $filter
     * @param $page
     * @param $show_items
     * @return array|bool
     */
    public static function getResults($filter, $page, $show_items) {
        $clauses = [];
        if ($filter && $filter['is_filter']) {
            if ($filter['email']) {
                $clauses[] = "user_email LIKE '%{$filter['email']}%'";
            }
            if ($filter['condition']) {
                $clauses[] = "condition_id = '{$filter['condition']}'";
            }
            if ($filter['event_type']) {
                $clauses[] = "action = '{$filter['event_type']}'";
            }
            if ($filter['status'] !== null) {
                $clauses[] = "status = {$filter['status']}";
            }
            if ($filter['start']) {
                $clauses[] = "date >= {$filter['start']}";
            }
            if ($filter['finish']) {
                $clauses[] = "date < {$filter['finish']}";
            }
        }

        $where = !empty($clauses) ? ('WHERE ' . implode(" AND ", $clauses)) : '';
        $db = Db::getConnection();
        $offset = ($page - 1) * $show_items;
        $result = $db->query("SELECT * FROM ".PREFICS."cond_actions_completed $where
                                       ORDER BY id DESC LIMIT $show_items OFFSET $offset"
        );

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $id
     * @return bool|mixed
     */
    public static function getResult($id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS."cond_actions_completed WHERE id = :id");
        $result->bindParam(':id', $id, PDO::FETCH_ASSOC);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data['act_params'] = isset($data['act_params']) && $data['act_params'] ? json_decode($data['act_params'], true) : null;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param null $filter
     * @return mixed
     */
    public static function getTotalResults($filter = null) {
        $clauses = [];
        if ($filter && $filter['is_filter']) {
            if ($filter['email']) {
                $clauses[] = "user_email LIKE '%{$filter['email']}%'";
            }
            if ($filter['condition']) {
                $clauses[] = "condition_id = '{$filter['condition']}'";
            }
            if ($filter['event_type']) {
                $clauses[] = "action = '{$filter['event_type']}'";
            }
            if ($filter['status'] !== null) {
                $clauses[] = "status = {$filter['status']}";
            }
            if ($filter['start']) {
                $clauses[] = "date >= {$filter['start']}";
            }
            if ($filter['finish']) {
                $clauses[] = "date < {$filter['finish']}";
            }
        }

        $where = !empty($clauses) ? ('WHERE ' . implode(" AND ", $clauses)) : '';
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(*) FROM ".PREFICS."cond_actions_completed $where");
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * ДОБАВИТЬ ГРУППЫ
     * @param $user
     * @param $add_groups
     * @return bool
     */
    private static function addGroups($user, $add_groups) {
        $result = true;
        foreach($add_groups as $group){
            if (!User::WriteUserGroup($user['user_id'], $group)) {
                $result = false;
            }
        }

        return $result;
    }


    /**
     * УДАЛИТЬ ГРУППЫ
     * @param $user
     * @param $del_groups
     * @return bool
     */
    private static function delGroups($user, $del_groups) {
        return User::deleteUserGroupsFromList($user['user_id'], $del_groups);
    }



    /**
     * ДОБАВИТЬ В ПОДПИСКУ МЕМБЕРШИП
     * @param $user
     * @param $del_groups
     * @return bool
     */
    private static function addToMembership($user, $add_to_memberships) {
        $result = [];
        foreach ($add_to_memberships as $membership_id) {
            $result[$membership_id] = Member::renderMember(intval($membership_id), intval($user['user_id']));
        }
        return json_encode($result);
    }



    /**
     * УДАЛИТЬ ПОДПИСУ МЕМБЕРШИП
     * @param $user
     * @param $del_groups
     * @return bool
     */
    private static function delToMembership($user, $del_to_memberships) {
        return; //такое действие требует доработки... #todo
    }


    /**
     * ОТПРАВИТЬ ПИСЬМО
     * @param $user
     * @param $order
     * @param $subject
     * @param $letter
     * @param $params
     * @return bool
     */
    private static function sendLetter($user, $order, $subject, $letter, $params) {
        $setting = System::getSetting();
        if ($order) {
            $letter = self::replaceSendText2Order($order, $user, $letter, $setting);
        } else {
            $letter = self::replaceSendText2User($user, $letter, $setting);
        }

        $prelink = User::generateAutoLoginLink($user);//Ссылка автологин без редиректа
        $replace = [
            '[AUTH_LINK]' => $prelink,
        ];
        $letter = strtr($letter, $replace);

        $letter = User::replaceAuthLinkInText($letter, $prelink);//Ссылка автологин с редиректом
        $emails = isset($params['letter_recipient_type']) && $params['letter_recipient_type'] == 2 ? $params['letter_email'] : $user['email'];
        $send = true;

        if ($emails) {
            foreach (explode(',', $emails) as $email) {
                $email = trim($email);
                if (!Email::SendMessageToBlank($email, $user['user_name'], $subject, $letter)) {
                    $send = false;
                }
            }
        }

        return $send;
    }


    /**
     * ОТПРАВИТЬ SMS
     * @param $user
     * @param $order
     * @param $message
     * @return bool
     */
    private static function sendSMS($user, $order, $message) {
        $setting = System::getSetting();
        if ($order) {
            $message = self::replaceSendText2Order($order, $user, $message, $setting, ', ');
        } else {
            $message = self::replaceSendText2User($user, $message, $setting, ', ');
        }

        return SMSC::sendSMS($user['phone'], $message);
    }


    /**
     * @param $order
     * @param $user
     * @param $text
     * @param $setting
     * @param string $transfer_symbol
     * @return string
     */
    public static function replaceSendText2Order($order, $user, $text, $setting, $transfer_symbol = '<br>') {
        $replace = [
            '[NAME]' => $order['client_name'],
            '[SURNAME]' => $user['surname'],
            '[FULL_NAME]' => "{$order['client_name']} {$user['surname']}",
            '[ORDER]' => $order['order_date'],
            '[DATE]' => date('"d.m.Y H:i:s', $order['order_date']),
            '[SUMM]' => $order['summ'],
            '[SUPPORT]' => $setting['support_email'],
            '[EMAIL]' => $order['client_email']
        ];


        if (preg_match('#\[LINK]|[PRODUCT_NAME]#', $text)) {
            $order_items = Order::getOrderItems($order['order_id']);
            if ($order_items) {
                $product_names = $product_links = '';

                foreach ($order_items as $key => $order_item) {
                    $product = Product::getProductById($order_item['product_id']);
                    if (!$product) {
                        continue;
                    }
                    $product_names .= ($key > 0 ? $transfer_symbol : '').$product['product_name'];
                    $link = "{$setting['script_url']}/download/{$order['order_date']}?key=".md5($order['client_email']);
                    $product_links .= ($key > 0 ? $transfer_symbol : '').$link;
                }
                $replace['[PRODUCT_NAME]'] = $order['client_name'];
                $replace['[LINK]'] = $product_links;
            }
        }

        if (preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $text)) {
            $text = CustomFields::replaceContent($text, $order['client_email']);
        }

        return strtr($text, $replace);
    }


    /**
     * @param $user
     * @param $text
     * @param $setting
     * @param string $transfer_symbol
     * @return string
     */
    public static function replaceSendText2User($user, $text, $setting, $transfer_symbol = '<br>') {
        $replace = [
            '[NAME]' => $user['user_name'],
            '[CLIENT_NAME]' => $user['user_name'],
            '[SURNAME]' => $user['surname'],
            '[FULL_NAME]' => "{$user['user_name']} {$user['surname']}",
            '[SUPPORT]' => $setting['support_email'],
        ];

        if (preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $text)) {
            $text = CustomFields::replaceContent($text, $user['email']);
        }

        return strtr($text, $replace);
    }


    /**
     * @param $params
     * @param $user
     * @param $order
     * @return bool
     */
    public static function sendWebHook($params, $user, $order) {
        $user_id = $user['user_id'] ? $user['user_id'] : null;
        $email = !$user_id ? $user['email'] : null;
        $send_data = [];

        if (!$params['url']) {
            return false;
        }

        if (isset($params['custom_fields'])) {
            $user_custom_fields = CustomFields::getUserFields($user_id, $email);

            foreach ($params['custom_fields'] as $custom_field_id => $custom_field_name) {
                if (!$custom_field_name) {
                    continue;
                }

                $field = CustomFields::getDataFieldByColumnName("custom_field_$custom_field_id");
                if ($field) {
                    $custom_fields_values = CustomFields::getFieldValue($field, $user_custom_fields);
                    $send_data[$custom_field_name] = is_array($custom_fields_values) ? implode(', ', $custom_fields_values) : $custom_fields_values;
                }
            }
        }

        $send_data_default = [
            'name' => $user['user_name'],
            'surname' => $user['surname'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'city' => $user['city'],
            'addres' => $user['address'],
            'index' => $user['zipcode'],
            'vk_url' =>  isset($user['vk_url']) ? $user['vk_url'] : null,
            'insta' => isset($user['nick_instagram']) ? $user['nick_instagram'] : null,
            'telegram' => isset($user['nick_telegram']) ? $user['nick_telegram'] : null,
            'user_id' => $user['user_id'] ? $user['user_id'] : null,
        ];

        if ($order) {
            $order_info = $order['order_info'] ? unserialize(base64_decode($order['order_info'])) : null;
            $order_items = Order::getOrderItems($order['order_id']);
            $products_names = array_column($order_items, 'product_name');
            $send_data_default = array_merge($send_data_default, [
                'order_id' => $order['order_id'],
                'order_date' => $order['order_date'],
                'order_status' => $order['status'],
                'summ' => Order::getOrderTotalSum($order['order_id']),
                'order_products' => $products_names ? implode(', ', $products_names) : null,
                'userId_YM' => $order_info && isset($order_info['userId_YM']) ? $order_info['userId_YM'] : null,
                'userId_GA' => $order_info && isset($order_info['userId_GA']) ? $order_info['userId_GA'] : null,
                'roistat_visitor' => $order_info && isset($order_info['roistat_visitor']) ? $order_info['roistat_visitor'] : null,
            ]);
        }

        if (isset($params['is_send_utm']) && $order) {
            $utm = $order['utm'] ? System::getUtmData($order['utm']) : null;
            if ($utm) {
                foreach ($utm as $key => $val) {
                    $send_data[$key] = $val;
                }
            }
        }

        foreach ($params as $name => $new_name) {
            if ($new_name && isset($send_data_default[$name])) {
                $send_data[$new_name] = $send_data_default[$name];
            }
        }

        if (!empty($send_data)) {
            $webhook_url = $params['url'];

            if ($params['send_type'] == 2) { /// Это GET
                $query_str = '';
                foreach ($send_data as $key => $val) {
                    $query_str .= ($query_str ? '&' : '') . "$key=".urlencode($val);
                }

                $webhook_url .= (strpos($webhook_url, '?') ? '&' : '?').$query_str;
                System::curlAsync($webhook_url);
            } else { // тут POST запроc
                System::curlAsync($webhook_url, $send_data);
            }
        }

        return true;
    }


    /**
     * ДОБАВИТЬ ПОДПИСКУ
     * @param $user
     * @param $time
     * @param $delivery_id
     * @param $confirmed
     * @param $responder_settings
     * @param $settings
     * @return bool|false|int|PDOStatement
     */
    private static function addSubscribe($user, $time, $delivery_id, $confirmed, $responder_settings, $settings) {
        $subs_key = md5($user['email'] . $time);
        $param = $time.';0;;/condition';

        $act = Responder::addSubsToMap($delivery_id, $user['email'], $user['user_name'], $user['phone'], $time, $subs_key,
            $confirmed, 0, 0, $param, $responder_settings, $settings
        );
        
        return $act ? true : false;
    }


    /**
     * УДАЛИТЬ ПОДПИСКИ
     * @param $subscriptions
     * @param $email
     * @return bool
     */
    private static function delSubscribes($subscriptions, $email) {
        $acts = [];

        foreach($subscriptions as $delivery_id){
            $acts[] = Responder::DeleteSubsRow($email, $delivery_id); // удалить из карты подписок
            $acts[] = Responder::DeleteTaskByEmail($email, $delivery_id); // удалить письма рассылки
        }
    
        return empty($acts) || in_array(false, $acts) ? false : true;
    }
    
    
    /** 
     *  УДАЛЕНИЕ ЮЗЕРОВ
     * @param $user_id
     * @return bool
     */
    public static function delUser($user_id)
    {
        $act = User::deleteUser($user_id);
        return $act;
    }

    /**
     * Сделать юзера партнером
     *
     * @param $user_id
     * @param $partner_id
     *
     * @return bool
     */
    public static function userToPartner($user_id, $partner_id){
        return Aff::AddUserToPartner($user_id, $partner_id);
    }


    /**
     * @param $action
     * @return false|string
     */
    public static function getActionParams($action) {
        $params = [];

        switch ($action['action']) {
            case self::ACTION_ADD_GROUP:
                $params = isset($action['params']['add_groups']) ? $action['params']['add_groups'] : null;
                break;
            case self::ACTION_DEL_GROUP:
                $params = isset($action['params']['del_groups']) ? $action['params']['del_groups'] : null;
                break;
            case self::ACTION_SUBSCRIBE_MAILING:
                $params = isset($action['params']['subscribe_delivery']) ? $action['params']['subscribe_delivery'] : null;
                break;
            case self::ACTION_UNSUBSCRIBE_MAILING:
                $params = isset($action['params']['unsubscribe_delivery']) ? $action['params']['unsubscribe_delivery'] : null;
                break;
            case self::ACTION_ADD_TO_MEMBERSHIP:
                $params = isset($action['params']['add_to_membership']) ? $action['params']['add_to_membership'] : null;
                break;
            case self::ACTION_DEL_TO_MEMBERSHIP:
                $params = isset($action['params']['del_to_membership']) ? $action['params']['del_to_membership'] : null;
                break;
        }

        return $params ? json_encode(['actions' => $params], true) : '';
    }


    /**
     * ИСПОЛНЕНИЕ УСЛОВИЙ
     * @param $action
     * @param $user
     * @param null $order
     * @return bool|false|int|PDOStatement
     */
    public static function actionCond($action, $user, $order = null) {
        $time = time();
        $settings = System::getSetting();
        $result = false;

        switch ($action['action']) {
            case self::ACTION_ADD_GROUP: // Добавление групп
                if (isset($action['params']['add_groups']) && $user['user_id']) {
                    $result = self::addGroups($user, $action['params']['add_groups']);
                }
                break;
            case self::ACTION_DEL_GROUP: // Удаление групп
                if (isset($action['params']['del_groups']) && $user['user_id']) {
                    $result = self::delGroups($user, $action['params']['del_groups']);
                }
            case self::ACTION_ADD_TO_MEMBERSHIP: // добавление в подписку мембершипа
                if (isset($action['params']['add_to_membership']) && $user['user_id']) {
                    $result = self::addToMembership($user, $action['params']['add_to_membership']);
                }
            case self::ACTION_DEL_TO_MEMBERSHIP: // удаление из подписки мембершипа
                if (isset($action['params']['del_to_membership']) && $user['user_id']) {
                    $result = self::delGroups($user, $action['params']['del_to_membership']);
                }
                break;
            case self::ACTION_SUBSCRIBE_MAILING: // Подписка на рассылку
                if (isset($action['params']['subscribe_delivery']) && $action['params']['subscribe_delivery'] && $user['email']) {
                    foreach ($action['params']['subscribe_delivery'] as $del_id) {
                        $delivery = Responder::getDeliveryData($del_id);
                        if ($delivery) {
                            $confirmed = $delivery['confirmation'] > 0 ? 0 : $time;
                            $responder_setting = unserialize(Responder::getResponderSetting());
                            $result = self::addSubscribe($user, $time, $del_id,
                                $confirmed, $responder_setting, $settings
                            );
                        }
                    }
                }
                break;
            case self::ACTION_UNSUBSCRIBE_MAILING: // Отписка от рассылки
                if (isset($action['params']['unsubscribe_delivery']) && $action['params']['unsubscribe_delivery'] && $user['email']) {
                    $result = self::delSubscribes($action['params']['unsubscribe_delivery'], $user['email']);
                }
                break;
            case self::ACTION_SEND_LETTER: // Отправка письма
                if ($action['params']['subject'] && $action['params']['letter'] && $user['email']) {
                    $result = self::sendLetter($user, $order, $action['params']['subject'], $action['params']['letter'], $action['params']);
                }
                break;
            case self::ACTION_SEND_SMS: // Отправка sms
                if ($action['params']['message'] && $user['phone']) {
                    $result = self::sendSMS($user, $order, $action['params']['message']);
                }
                break;
            case self::ACTION_SEND_WEBHOOK:
                if ($action['params']['webhook'] && ($user['user_id'] || $user['email'])) {
                    $result = self::sendWebHook($action['params']['webhook'], $user, $order);
                }
                break;
            case self::ACTION_DELETE_USER:
                if ($user['user_id']) {
                    $result = self::delUser($user['user_id']);
                }
                break;
            case self::ACTION_USER_TO_PARTNER:
                if ($user['user_id']) {
                    $result = self::userToPartner($user['user_id'], $user['user_id']);
                }
                break;
        }
        
        return $result;
    }


    /**
     * ПОИСК УСЛОВИЙ
     * @param $time
     * @return array|bool
     */
    public static function searchConditions($time) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."conditions 
                                       WHERE status = 1 AND use_cron = 1 AND next_action <= $time
                                       AND next_action > 0 ORDER BY id DESC"
        );
        
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['actions'] = self::getConditionActions($row['id']);
            $row['params'] = $row['params'] ? json_decode($row['params'], true) : null;
            $data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }


    /**
     * ОБНОВИТЬ УСЛОВИЕ ПОСЛЕ ОБРАБОТКИ
     * @param $cond_id
     * @param $period
     * @param $action
     * @return bool
     */
    public static function updateCond($cond_id, $period, $action)
    {
        $db = Db::getConnection();
        
        $sql = 'UPDATE '.PREFICS.'conditions SET next_action = :next_action WHERE id = '.$cond_id;
        $result = $db->prepare($sql);
        
        $next_action = $action + $period * 60;
        $result->bindParam(':next_action', $next_action, PDO::PARAM_INT);
        
        return $result->execute();
    }


    /**
     * СПИСОК УСЛОВИЙ
     * @return array|bool
     */
    public static function getConditionsList()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."conditions ORDER BY id DESC");
    
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['params'] = json_decode($row['params'], true);
            $data[] = $row;
        }
    
        return !empty($data) ? $data : false;
    }


    /**
     * @param $segment_id
     * @param $filter_model
     * @return mixed
     */
    public static function getCountConditionsBySegmentId($segment_id, $filter_model) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT COUNT(*) FROM ".PREFICS."conditions 
                                         WHERE segment_id = :segment_id AND filter_model = :filter_model"
        );
        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);
        $result->bindParam(':filter_model', $filter_model, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * ДАННЫЕ УСЛОВИЯ ПО ID
     * @param $id
     * @return bool|mixed
     */
    public static function getConditionData($id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."conditions WHERE id = $id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (!empty($data)) {
            $data['actions'] = self::getConditionActions($id);
            $data['params'] = json_decode($data['params'], true);
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $condition_id
     * @return array|bool
     */
    public static function getConditionActions($condition_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS."cond_actions WHERE condition_id = :condition_id ORDER BY action_id DESC");
        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);
        $result->execute();

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['params'] = $row['params'] ? json_decode($row['params'], true) : null;
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $action_id
     * @return bool|mixed
     */
    public static function getConditionAction($action_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS."cond_actions WHERE action_id = :action_id");
        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);
        $result->execute();

        $data = $result->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $data['params'] = $data['params'] ? json_decode($data['params'], true) : null;
        }

        return !empty($data) ? $data : false;
    }


    // ДАННЫЕ УСЛОВИЯ ПО CREATE_DATE
    public static function getCondByCreateDate($create_date)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."conditions WHERE create_date = $create_date LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        return !empty($data) ? $data : false;
    }


    /**
     * @param $condition_id
     * @param $actions
     * @return bool
     */
    public static function addConditionActions($condition_id, $actions) {
        $result = true;

        foreach ($actions as $action) {
            if (!self::addConditionAction($condition_id, $action)) {
                $result = false;
            }
        }

        return $result;
    }


    /**
     * @param $time
     * @param $params
     * @return false|float|int
     */
    public static function getNextActionDate($time, $params) {
        if ($params['execute_date_type'] == self::EXECUTE_DATE_TYPE_SCHEDULER) {
            $date = $time != 0 ? $time : $params['period'] + $time;
        } elseif($params['execute_date_type'] == self::EXECUTE_DATE_TYPE_WEEK_DAY) {
            $execute_time = $params['execute_time'] ? $params['execute_time'] : '00:00';
            $diff = $params['execute_week_day'] - date('w') + ($params['execute_week_day'] >= date('w') ? 0 : 7);
            $date = strtotime("+$diff day", strtotime(date("d-m-Y $execute_time:00")));
            $date = $date > $time && $time != 0 ? $date : $date + 604800;
        } else {
            $date = $params['execute_specific_date'] ? strtotime($params['execute_specific_date']) : 0;
        }

        return $date;
    }


    /**
     * @param $condition_id
     * @param $action
     * @return bool|string
     */
    public static function addConditionAction($condition_id, $action) {
        $params = json_encode($action['params'], JSON_UNESCAPED_UNICODE);
        $db = Db::getConnection();

        $result = $db->prepare('INSERT INTO '.PREFICS.'cond_actions (condition_id, action, params) 
                                        VALUES (:condition_id, :action, :params)');
        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);
        $result->bindParam(':action', $action['action'], PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);

        return $result->execute() ? $db->lastInsertId() : false;
    }


    /**
     * @param $action_id
     * @param $action
     * @return bool
     */
    public static function editConditionAction($action_id, $action) {
        $params = json_encode($action['params'], JSON_UNESCAPED_UNICODE);
        $db = Db::getConnection();

        $result = $db->prepare('UPDATE '.PREFICS.'cond_actions SET action = :action, params = :params
                                        WHERE action_id = :action_id');
        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);
        $result->bindParam(':action', $action['action'], PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ДОБАВИТЬ НОВОЕ УСЛОВИЕ
     * @param $name
     * @param $status
     * @param $repeat
     * @param $desc
     * @param $use_cron
     * @param $params
     * @param $filter_model
     * @param $segment_id
     * @param $actions
     * @param $create_date
     * @param $next_action
     * @return bool|string
     */
    public static function addCondition($name, $status, $repeat, $desc, $use_cron, $params, $filter_model, $segment_id,
                                        $actions, $create_date, $next_action) {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'conditions (name, status, cond_repeat, cond_desc, use_cron, params, filter_model,
                    segment_id, create_date, next_action)
                VALUES (:name, :status, :repeat, :cond_desc, :use_cron, :params, :filter_model, :segment_id,
                    :create_date, :next_action)';

        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':repeat', $repeat, PDO::PARAM_INT);
        $result->bindParam(':cond_desc', $desc, PDO::PARAM_STR);
        $result->bindParam(':use_cron', $use_cron, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':filter_model', $filter_model, PDO::PARAM_INT);
        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);
        $result->bindParam(':create_date', $create_date, PDO::PARAM_INT);
        $result->bindParam(':next_action', $next_action, PDO::PARAM_INT);

        $condition_id = $result->execute() ? $db->lastInsertId() : false;
        if ($condition_id && !empty($actions)) {
            self::addConditionActions($condition_id, $actions);
        }

        return $condition_id;
    }


    /**
     * ИЗМЕНИТЬ УСЛОВИЕ
     * @param $id
     * @param $name
     * @param $status
     * @param $repeat
     * @param $desc
     * @param $use_cron
     * @param $params
     * @param $filter_model
     * @param $segment_id
     * @param $next_action
     * @return bool
     */
    public static function editCondition($id, $name, $status, $repeat, $desc, $use_cron, $params, $filter_model,
                                         $segment_id, $next_action) {
        
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."conditions SET name = :name, status = :status, cond_repeat = :repeat, cond_desc = :cond_desc,
                use_cron = :use_cron, params = :params, filter_model = :filter_model, segment_id = :segment_id,
                next_action = :next_action WHERE id = $id";
        
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':repeat', $repeat, PDO::PARAM_INT);
        $result->bindParam(':cond_desc', $desc, PDO::PARAM_STR);
        $result->bindParam(':use_cron', $use_cron, PDO::PARAM_INT);
        $result->bindParam(':filter_model', $filter_model, PDO::PARAM_INT);
        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':next_action', $next_action, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $condition_id
     * @return bool
     */
    public static function delConditionActions($condition_id) {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS."cond_actions WHERE condition_id = :condition_id";

        $result = $db->prepare($sql);
        $result->bindParam(':condition_id', $condition_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $action_id
     * @return bool
     */
    public static function delConditionAction($action_id) {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS."cond_actions WHERE action_id = :action_id";

        $result = $db->prepare($sql);
        $result->bindParam(':action_id', $action_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ УСЛОВИЕ
     * @param $id
     * @return bool
     */
    public static function delCondition($id) {
        $db = Db::getConnection();
        
        $sql = 'DELETE FROM '.PREFICS.'conditions WHERE id = :id;';

        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($result->execute()) {
            return self::delConditionActions($id);
        }

        return false;
    }


    /**
     * @param null $action
     * @return array|mixed
     */
    public static function getActions($action = null) {
        $actions = [
            self::ACTION_ADD_GROUP => 'Добавить группу',
            self::ACTION_DEL_GROUP => 'Удалить группу',
            self::ACTION_ADD_TO_MEMBERSHIP => 'Добавить в подписку',
            self::ACTION_DEL_TO_MEMBERSHIP => 'Удалить из подписки',
            self::ACTION_SUBSCRIBE_MAILING => 'Подписать на рассылку',
            self::ACTION_UNSUBSCRIBE_MAILING => 'Отписать от рассылок',
            self::ACTION_SEND_LETTER => 'Отправить письмо',
            self::ACTION_SEND_SMS => 'Отправить SMS',
            self::ACTION_SEND_WEBHOOK => 'Отправить webhook',
            self::ACTION_DELETE_USER => 'Удалить пользователей',
            self::ACTION_USER_TO_PARTNER => 'Сделать пользователя партнером',
        ];

        return $action ? $actions[$action] : $actions;
    }


    /**
     * @param null $action
     * @return array|mixed
     */
    public static function getEvents($action = null) {
        $events = [
            self::ACTION_ADD_GROUP => 'Добавлена группа',
            self::ACTION_DEL_GROUP => 'Удалена группа',
            self::ACTION_ADD_TO_MEMBERSHIP => 'Добавить в подписку',
            self::ACTION_DEL_TO_MEMBERSHIP => 'Удалить из подписки',
            self::ACTION_SUBSCRIBE_MAILING => 'Подписан на рассылку',
            self::ACTION_UNSUBSCRIBE_MAILING => 'Отписан от рассылок',
            self::ACTION_SEND_LETTER => 'Отправлено письмо',
            self::ACTION_SEND_SMS => 'Отправлено SMS',
            self::ACTION_SEND_WEBHOOK => 'Вебхук',
            self::ACTION_DELETE_USER => 'Удален пользователь',
            self::ACTION_USER_TO_PARTNER => 'Пользователь стал партнером',
        ];

        return $action ? $events[$action] : $events;
    }


    /**
     * @param $action
     * @param $value
     * @return mixed|string
     */
    public static function getEventUrl($action, $value) {
        $events = [
            self::ACTION_ADD_GROUP => "/admin/usergroups/edit/$value",
            self::ACTION_DEL_GROUP => "/admin/usergroups/edit/$value",
            self::ACTION_ADD_TO_MEMBERSHIP => "/admin/membersubs/edit/$value",
            self::ACTION_DEL_TO_MEMBERSHIP => "/admin/membersubs/edit/$value",
            self::ACTION_SUBSCRIBE_MAILING => "/admin/responder/edit/$value",
            self::ACTION_UNSUBSCRIBE_MAILING => "/admin/responder/edit/$value",
        ];

        return isset($events[$action]) ? $events[$action] : '';
    }


    /**
     * @param null $status
     * @param null $act_status
     * @return string
     */
    public static function getEventStatuses($status = null, $act_status = null) {
        $statuses = [
            self::RESULT_STATUS_NOT_DONE => 'Не выполнено',
            self::RESULT_STATUS_DONE => 'Выполнено',
        ];

        if ($status !== null) {
            return $status && $act_status ? $statuses[self::RESULT_STATUS_DONE] : $statuses[self::RESULT_STATUS_NOT_DONE];
        }

        return $statuses[$status] ?? "";
    }


    /**
     * @param $model_type
     * @return array
     */
    public static function getFields2Messages($model_type) {
        if ($model_type == SegmentFilter::FILTER_TYPE_ORDERS) {
            $vars = [
                '[NAME]' => 'Имя пользователя',
                '[SURNAME]' => 'фамилия клиента',
                '[FULL_NAME]' => 'имя и фамилия клиента',
                '[CUSTOM_FIELD_N]' => 'кастомное поле пользователя где N номер поля',
                '[ORDER]' => 'номер заказа',
                '[DATE]' => 'дата заказа',
                '[PRODUCT_NAME]' => 'название продукта',
                '[SUMM]' => 'сумма заказа',
                '[SUPPORT]' => 'емейл службы поддержки',
                '[LINK]' => 'ссылка на скачивание',
            ];
        } else {
            $vars = [
                '[NAME]' => 'Имя пользователя',
                '[SURNAME]' => 'фамилия клиента',
                '[FULL_NAME]' => 'имя и фамилия клиента',
                '[CUSTOM_FIELD_N]' => 'кастомное поле пользователя где N номер поля',
                '[SUPPORT]' => 'емейл службы поддержки',
                '[AUTH_LINK]' => "Ссылка с автоматическим входом",
                "[AUTH_LINK='/хвостссылки']" => "Ссылка с автоматическим входом и редиректом на какую-либо страницу. Пример: [AUTH_LINK='/blog'] - после авторизации переход в блог",
            ];
        }

        return $vars;
    }


    public static function getIntervalInfo($condition) {
        $info = '';
        if (!$condition['use_cron']) {
            $info = 'Сразу';
        } else {
            switch ($condition['params']['execute_date_type']) {
                case self::EXECUTE_DATE_TYPE_SCHEDULER:
                    if ($condition['params']['period']) {
                        $info = System::addTermination3($condition['params']['period'], 'Кажд[TRMNT]')." {$condition['params']['period']} ".
                            System::addTermination4($condition['params']['period'], 'минут[TRMNT]');
                    } else {
                        $info = 'Один раз при выполнении крона';
                    }
                    break;
                case self::EXECUTE_DATE_TYPE_WEEK_DAY:
                    $info = System::addTerminationByWeekDay($condition['params']['execute_week_day'], 'Кажд[TRMNT]').
                        ' '.self::getWeekDayText($condition['params']['execute_week_day']);
                    break;
                case self::EXECUTE_DATE_TYPE_SELECT_DATE:
                    if ($condition['params']['execute_specific_date']) {
                        $info = $condition['params']['execute_specific_date'];
                    } else {
                        $info = 'Никогда';
                    }
                    break;
            }
        }

        return $info;
    }


    /**
     * @param $day
     * @return string
     */
    public static function getWeekDayText($day) {
        $text = '';
        switch ($day) {
            case 1:
                $text = 'понедельник';
                break;
            case 2:
                $text = 'вторник';
                break;
            case 3:
                $text = 'среду';
                break;
            case 4:
                $text = 'четверг';
                break;
            case 5:
                $text = 'пятницу';
                break;
            case 6:
                $text = 'субботу';
                break;
            case 0:
            case 7:
                $text = 'воскресение';
                break;
        }

        return $text;
    }
}