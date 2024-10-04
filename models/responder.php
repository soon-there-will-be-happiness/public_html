<?php defined('BILLINGMASTER') or die;

class Responder {
    
    public static function addWhere($where, $add, $and = true) 
    {
        if ($where){
            
          if ($and) $where .= " AND $add";
          else $where .= " OR $add";
          
        } else $where = $add;
        return $where;
    }
    
    
    // ПОЛУЧИТЬ ПРИЧИНЫ ОТПИСОК
    public static function getReasons($email)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."email_reasons WHERE email = '$email' ORDER BY id ASC");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }
    
    
    // ИЗМЕНИТЬ ГАЛОЧКУ ПОДПИСКИ У ЮЗЕРА
    public static function DeleteIsSubs($email, $is_subs)
    {
        $user = User::getUserDataByEmail($email);
        if($user){
            $db = Db::getConnection();  
            $sql = 'UPDATE '.PREFICS.'users SET is_subs = :is_subs WHERE user_id = '.$user['user_id'];
            $result = $db->prepare($sql);
            $result->bindParam(':is_subs', $is_subs, PDO::PARAM_INT);
            return $result->execute();
        }
    }
    
    
    
    // ПОЛУЧИТЬ СПИСОК ПОДПИСОК
    public static function getUniqSubscribers($page = 1, $show_items = null)
    {
        $db = Db::getConnection();
        
        $offset = $show_items ? ($page - 1) * $show_items : 0;
        
        $result = $db->query("SELECT * FROM ".PREFICS."email_subs_map ORDER BY id DESC LIMIT ".$show_items." OFFSET $offset");
        
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }


    // ПОЛУЧИТЬ СПИСОК e-mail response
    public static function getUniqEmail($page = 1, $show_items = null, $delivery_id)
    {
        $db = Db::getConnection();

        $offset = $show_items ? ($page - 1) * $show_items : 0;

        $result = $db->query("SELECT * FROM ".PREFICS."email_task WHERE delivery_id = $delivery_id ORDER BY task_id DESC LIMIT ".$show_items." OFFSET $offset");

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ПОДПИСЧИКОВ ПО ФИЛЬТРУ
     * @param $filter
     * @param $page
     * @param $show_items
     * @return array|bool
     */
    public static function getUniqSubscribersByFilter($filter, $page, $show_items)
    {
        $clauses = [];
        if ($filter['delivery']) {
            $clauses[] = "delivery_id = {$filter['delivery']}";
        }
        if ($filter['email']) {
            $clauses[] = "email LIKE '%{$filter['email']}%'";
        }

        $db = Db::getConnection();
        $where = $clauses ? 'WHERE '.implode(' AND ', $clauses) : '';
        $offset = $show_items ? ($page - 1) * $show_items : 0;
        $result = $db->query("SELECT * FROM ".PREFICS."email_subs_map $where ORDER BY id DESC LIMIT $show_items OFFSET $offset");

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ЗАПИСЬ ПОДПИСЧИКА В КАРТУ
     * @param $delivery_id
     * @param $email
     * @param $name
     * @param $phone
     * @param $time
     * @param $subs_key
     * @param $confirmed
     * @param $cancelled
     * @param $spam
     * @param $param
     * @param $responder_setting
     * @param $setting
     * @return bool|false|int|PDOStatement
     */
    public static function addSubsToMap($delivery_id, $email, $name, $phone, $time, $subs_key, $confirmed, $cancelled, $spam,
                                        $param, $responder_setting, $setting)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$delivery_id) {
            return false;
        }

        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(id) FROM ".PREFICS."email_subs_map WHERE delivery_id = $delivery_id AND email = '$email'");
        $data = $result->fetch();

        if ($data[0] > 0) {
            return false;
        }

        $sql = 'INSERT INTO '.PREFICS.'email_subs_map (delivery_id, email, subs_name, subs_time, subs_key, confirmed, cancelled, spam, phone)
                VALUES (:delivery_id, :email, :subs_name, :subs_time, :subs_key, :confirmed, :cancelled, :spam , :phone)';

        $result = $db->prepare($sql);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->bindParam(':subs_name', $name, PDO::PARAM_STR);
        $result->bindParam(':subs_key', $subs_key, PDO::PARAM_STR);
        $result->bindParam(':delivery_id', $delivery_id, PDO::PARAM_INT);
        $result->bindParam(':subs_time', $time, PDO::PARAM_INT);
        $result->bindParam(':confirmed', $confirmed, PDO::PARAM_INT);
        $result->bindParam(':cancelled', $cancelled, PDO::PARAM_INT);
        $result->bindParam(':spam', $spam, PDO::PARAM_INT);
        $result->bindParam(':phone', $phone, PDO::PARAM_STR);

        $result = $result->execute();
        if (!$result) {
            return false;
        }

        $user = User::getUserDataByEmail($email);
        if ($user) {
            $sql = "UPDATE ".PREFICS."users SET is_subs = 1 WHERE email = '$email'";
            $result = $db->query($sql);
        }

        if ($confirmed == 0) { // если не подтверждён, отправить подтверждение
            $send = Email::sendConfirmSubs($delivery_id, $email, $name, $subs_key);
        } else {
            $delivery = self::getDeliveryData($delivery_id);
            if ($delivery) {
                self::eventsAfterConfirm($delivery, $user, $name, $email,
                    $phone, $time, $param, $responder_setting, $setting, 1
                );
            }
        }

        return $result;
    }


    /**
     * @param $delivery
     * @param $user
     * @param $name
     * @param $email
     * @param $phone
     * @param $time
     * @param $param
     * @param $responder_setting
     * @param $setting
     * @param int $is_subs
     */
    public static function eventsAfterConfirm($delivery, $user, $name, $email, $phone, $time, $param, $responder_setting,
                                              $setting, $is_subs = 0) {
        $letter_list = Responder::getAutoLetterList($delivery['delivery_id']); // Получить письма автосерии
        if ($letter_list) {
            foreach ($letter_list as $letter) {
                $send = time() + ($letter['send_time'] * 3600);
                $result = Responder::AddTask($delivery['delivery_id'], $letter['letter_id'], $email, $send, 0);
            }
        }
        
        if (!$user) { // создать юзера-подписчика
            $user = User::AddNewClient($name, $email, $phone, null, null, null,
                'user', 0, $time, 'subscription', $param, 1, null,
                null, $responder_setting['params']['send_pass'], $setting['register_letter'], $is_subs
            );
        }

        if ($user && $delivery) {
            self::eventsAfterSubscription($delivery, $user['user_id']);
        }
    }


    /**
     * @param $delivery
     * @param $user_id
     */
    public static function eventsAfterSubscription($delivery, $user_id) {
        if ($delivery['add_user_groups']) {
            $date = time();
            $user_groups = json_decode($delivery['add_user_groups'], true);
            if ($user_groups) {
                foreach ($user_groups as $user_group) {
                    User::WriteUserGroup($user_id, $user_group, $date);
                }
            }
        }
    }


    // ПОЛУЧИТЬ ЗАПИСЬ В КАРТЕ
    public static function getSubsMapRow($email, $delivery)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."email_subs_map WHERE email = '$email' AND delivery_id = $delivery AND confirmed = 0 LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(isset($data) && !empty($data)) return $data;
        else return false;
    }
    
    
    
    // ПРОВЕРИТЬ НАЛИЧИЕ РАССРОЧКИ ПО Email
    public static function checkInstallmentFromEmail($email)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(id) FROM ".PREFICS."installment_map WHERE email = '$email' AND status IN (0, 1)");
        $count = $result->fetch();
        if($count[0] > 0) return $count[0];
        else return false;
    }
    
    
    // ОБНОВИТЬ ЗАПИСЬ В КАРТЕ
    public static function updateSubsRow($id, $confirmed, $cancelled = 0, $spam = 0 )
    {
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'email_subs_map SET confirmed = :confirmed, cancelled = :cancelled, spam = :spam WHERE id = '.$id;
        $result = $db->prepare($sql);
        $result->bindParam(':confirmed', $confirmed, PDO::PARAM_INT);
        $result->bindParam(':cancelled', $cancelled, PDO::PARAM_INT);
        $result->bindParam(':spam', $spam, PDO::PARAM_INT);
        return $result->execute();
    }


    /**
     * УДАЛИТЬ ЗАПИСЬ В КАРТЕ
     * @param $email
     * @param $delivery
     * @return bool
     */
    public static function DeleteSubsRow($email, $delivery) {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_subs_map WHERE email = :email';
        if ($delivery) {
            $sql .= ' AND delivery_id = :delivery_id';
            $sql .= '; DELETE FROM '.PREFICS.'email_task WHERE delivery_id = :delivery_id AND email = :email';
        }
        
        $result = $db->prepare($sql);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        if($delivery)$result->bindParam(':delivery_id', $delivery, PDO::PARAM_INT);

        return $result->execute();
    }
    
    
    // УДАЛИТЬ ЗАПИСЬ В КАРТЕ
    public static function DeleteSubsRowByID($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_subs_map WHERE id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        return $result->execute();
    }


    /**
     * ЗАПИСАТЬ ПРИЧИНУ ОТПИСКИ
     * @param $email
     * @param $reason
     * @param $type
     * @param int $delivery_id
     * @return bool
     */
    public static function WriteUnsubReason($email, $reason, $type, $delivery_id = 0)
    {
        $db = Db::getConnection();
        $time = time();
        $sql = 'INSERT INTO '.PREFICS.'email_reasons (email, reason, time, type, delivery_id) 
                VALUES (:email, :reason, :time, :type, :delivery_id)';

        $result = $db->prepare($sql);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->bindParam(':reason', $reason, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_STR);
        $result->bindParam(':time', $time, PDO::PARAM_INT);
        $result->bindParam(':delivery_id', $delivery_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ПОЛУЧЕНИЕ ЗАДАНИЙ НА ОТПРАВКУ
     * @return bool
     */
    public static function getTasksForAction()
    {
        $params = unserialize(Responder::getResponderSetting());
        $limit = $params['params']['count'];
        $time = $status = time();
        $time_60minutabout = $time - 7200;
        
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."email_task SET status = :status WHERE send_time < $time AND status = 0 LIMIT $limit ";
        $result = $db->prepare($sql);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $upd = $result->execute();
        
        if ($upd) {

            $sql = "SELECT et.*, u.user_name FROM ".PREFICS."email_task as et
                            LEFT JOIN ".PREFICS."users as u ON et.email = u.email
                            WHERE et.status = $status AND et.send_time < $time LIMIT $limit";
            $result = $db->query($sql);
            $data = [];
            while($row = $result->fetch()){
                $data[] = $row;
            }
            
            $sql = 'UPDATE '.PREFICS."email_task SET status = 0 WHERE status < $time_60minutabout AND status != 0;";
            $result = $db->query($sql);
            $result->execute();
    
            return !empty($data) ? $data : false;
        }
    }

    /**
     * ПОЛУЧЕНИЕ КОЛИЧЕСТВА ЗАДАНИЙ НА ОТПРАВКУ ПО delivery_id
     * @param $delivery_id
     * @return int|bool
     */
    public static function getCountTasksForAction($delivery_id)
    {
        $db = Db::getConnection();
        $sql = "SELECT count(*) AS total  FROM ".PREFICS."email_task WHERE delivery_id = $delivery_id ";
        $result = $db->query($sql);
        $data = $result->fetch();
        
        return !empty($data) ? $data['total'] : false;
    }
	
	
	
	// ПОИСК ЗАВИСШИХ ЗАДАНИЙ
    public static function searchStragglerTask($time)
    {
        $db = Db::getConnection();
        $period = $time - 7200;
        $sql = 'UPDATE '.PREFICS."email_task SET status = 0 WHERE status > $period;";
        $result = $db->query($sql);
        return $result->execute();
    }
        
    
	
	// ПЕРЕОТПРАВКА НЕУДАВШИХСЯ
    public static function ResendTask($task_fail)
    {
        $db = Db::getConnection();  
        $status = 0;
        $sql = 'UPDATE '.PREFICS.'email_task SET status = :status WHERE id IN ('.$task_fail.')';
        $result = $db->prepare($sql);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        return $result->execute();
    }
        
     
    // УДАЛИТЬ ЗАДАЧИ 
    public static function DeleteTaskStr($task_str)
    {
        $task_str = trim($task_str, ",");
        $del = $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS."email_task WHERE task_id IN ($task_str)";
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        return $result->execute();
    }
	
    
    
    // УДАЛИТЬ ЗАДАЧУ 
    public static function DeleteTask($id)
    {
        // Удаляем задачу и
        $del = $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_task WHERE task_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // УДАЛИТЬ ЗАДАЧУ по емейл и id рассылки
    public static function DeleteTaskByEmail($email, $delivery_id)
    {
        // Удаляем задачу и
        $del = $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_task WHERE delivery_id = :id AND email = :email';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $delivery_id, PDO::PARAM_INT);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        return $result->execute();
    }
    
    
    // ЗАПИСЬ В ЛОГ 
    public static function WrtiteLog($letter_id, $delivery_id, $time, $email, $action, $descript)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'email_task_log (letter_id, delivery_id, email, date_time, action, descript ) 
                VALUES (:letter_id, :delivery_id, :email, :date_time, :action, :descript)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':letter_id', $letter_id, PDO::PARAM_INT);
        $result->bindParam(':delivery_id', $delivery_id, PDO::PARAM_INT);
        $result->bindParam(':date_time', $time, PDO::PARAM_INT);
        
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->bindParam(':action', $action, PDO::PARAM_STR);
        $result->bindParam(':descript', $descript, PDO::PARAM_STR);
        return $result->execute();
    }


    /**
     * ДОБАВИТЬ ЗАДАЧУ НА ОТПРАВКУ ПИСЬМА
     * @param $delivery_id
     * @param $letter_id
     * @param $recipient
     * @param $send
     * @param $status
     * @param null $letter
     * @return bool
     */
    public static function AddTask($delivery_id, $letter_id, $recipient, $send, $status, $letter = null)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'email_task (delivery_id, letter_id, email, send_time, status, letter) 
                VALUES (:delivery_id, :letter_id, :email, :send_time, :status, :letter)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':delivery_id', $delivery_id, PDO::PARAM_INT);
        $result->bindParam(':letter_id', $letter_id, PDO::PARAM_INT);
        $result->bindParam(':email', $recipient, PDO::PARAM_STR);
        $result->bindParam(':send_time', $send, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':letter', $letter, PDO::PARAM_STR);

        return $result->execute();
    }
    
    
    // ПОЛУЧИТЬ ПИСЬМО МАССОВОЙ РАССЫЛКИ
    public static function getDeliveryLetter($delivery_id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."email_letter WHERE delivery_id = $delivery_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(isset($data)) return $data;
    }
    
    
    
    // ПОЛУЧИТЬ ПИСЬМО АВТОСЕРИИ
    public static function getLetterData($letter_id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."email_letter WHERE letter_id = $letter_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(isset($data)) return $data;
        else return false;
    }
    
    
    
    // СОЗДАТЬ ПИСЬМО АВТО СЕРИИ
    public static function addAutoLetter($delivery_id, $send, $target, $subject, $letter, $status)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'email_letter (delivery_id, send_time, subject, body, target, status, create_date ) 
                VALUES (:delivery_id, :send_time, :subject, :body, :target, :status, :create_date )';
        $date = time();
        $result = $db->prepare($sql);
        $result->bindParam(':delivery_id', $delivery_id, PDO::PARAM_INT);
        $result->bindParam(':send_time', $send, PDO::PARAM_INT);
        
        $result->bindParam(':target', $target, PDO::PARAM_STR);
        $result->bindParam(':subject', $subject, PDO::PARAM_STR);
        $result->bindParam(':body', $letter, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':create_date', $date, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // ИЗМЕНИТЬ ПИСЬМО АВТОСЕРИИ
    public static function editAutoLetter($letter_id, $send, $target, $subject, $letter, $status)
    {
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'email_letter SET send_time = :send_time, subject = :subject, body = :body, target = :target, 
                                                status = :status WHERE letter_id = '.$letter_id;
        $result = $db->prepare($sql);
        $result->bindParam(':send_time', $send, PDO::PARAM_INT);
        $result->bindParam(':target', $target, PDO::PARAM_STR);
        $result->bindParam(':subject', $subject, PDO::PARAM_STR);
        $result->bindParam(':body', $letter, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    
    // ПОДСЧИТАТЬ КОЛ_ВО ПИСЕМ В АВТОСЕРИИ
    public static function countAutoLetters($delivery_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(letter_id) FROM ".PREFICS."email_letter WHERE delivery_id = $delivery_id");
        $count = $result->fetch();
        return $count[0];
    }
    
    
    
    // УДАЛИТЬ ПИСЬМО АВТОСЕРИИ
    public static function delAutoletter($letter_id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_letter WHERE letter_id = :id;
        DELETE FROM '.PREFICS.'email_task WHERE letter_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $letter_id, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    
    /**
     *    РАССЫЛКИ   И АВТОСЕРИИ
     */


    /**
     * ПОЛУЧИТЬ СПИСОК РАССЫЛОК ПОЛЬЗОВАТЕЛЯ (на что он подписан)
     * @param $email
     * @return array|bool
     */
    public static function getUserDelivery($email)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."email_subs_map WHERE email = '$email' ORDER BY id ASC");

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }
    
    
    // ПОЛУЧИТЬ СПИСОК ПИСЕМ АВТОСЕРИИ
    public static function getAutoLetterList($id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."email_letter WHERE delivery_id = $id ORDER BY send_time ASC");
        $i = 0;
        while($row = $result->fetch()){
            $data[$i]['letter_id'] = $row['letter_id'];
            $data[$i]['delivery_id'] = $row['delivery_id'];
            $data[$i]['subject'] = $row['subject'];
            $data[$i]['body'] = $row['body'];
            $data[$i]['create_date'] = $row['create_date'];
            $data[$i]['status'] = $row['status'];
            $data[$i]['send_time'] = $row['send_time'];
            $data[$i]['target'] = $row['target'];
            $i++;
        }
        if(isset($data)) return $data;
        else return false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ РАССЫЛКИ
     * @param $id
     * @return bool|mixed
     */
    public static function getDeliveryData($id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."email_delivery WHERE delivery_id = $id");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * СОЗДАТЬ МАССОВУЮ РАССЫЛКУ / АВТОСЕРИЮ
     * @param $name
     * @param $type
     * @param string $sender_name
     * @param $desc
     * @param $send
     * @param $time
     * @param $subject
     * @param $letter
     * @param $confirmation
     * @param $sent_list
     * @param $ex_list
     * @param $count_letters
     * @param $target
     * @param $confirm_body
     * @param $confirm_subject
     * @param $after_confirm_text
     * @param int $count_bad
     * @param null $bad_list_id
     * @param null $add_user_groups
     * @return bool
     */
    public static function addDelivery(
        $name, $type, $sender_name, $desc, $send, $time, $subject, $letter, $confirmation, $sent_list, 
        $ex_list, $count_letters, $target, $confirm_body, $confirm_subject, $after_confirm_text, 
        $count_bad = 0, $bad_list_id = null, $add_user_groups = null, $redirect_url = null
    ){


        // Создать рассылку и получить её ID
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'email_delivery (name, delivery_desc, type, send_time, create_date, sent, ex_sent, count_letters,
                    confirmation, confirm_subject, confirm_body, after_confirm_text, count_bad, bad_list_id, add_user_groups, redirect_url )
                VALUES (:name, :delivery_desc, :type, :send_time, :create_date, :sent, :ex_sent, :count_letters, :confirmation,
                    :confirm_subject, :confirm_body, :after_confirm_text, :count_bad, :bad_list_id, :add_user_groups, :redirect_url)';

        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':delivery_desc', $desc, PDO::PARAM_STR);
        $result->bindParam(':confirmation', $confirmation, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_INT);
        $result->bindParam(':send_time', $send, PDO::PARAM_STR);
        $result->bindParam(':create_date', $time, PDO::PARAM_STR);
        $result->bindParam(':sent', $sent_list, PDO::PARAM_STR);
        $result->bindParam(':ex_sent', $ex_list, PDO::PARAM_STR);
        $result->bindParam(':confirm_subject', $confirm_subject, PDO::PARAM_STR);
        $result->bindParam(':confirm_body', $confirm_body, PDO::PARAM_STR);
        $result->bindParam(':count_letters', $count_letters, PDO::PARAM_INT);
        $result->bindParam(':after_confirm_text', $after_confirm_texts, PDO::PARAM_STR);
        $result->bindParam(':count_bad', $count_bad, PDO::PARAM_INT);
        $result->bindParam(':bad_list_id', $bad_list_id, PDO::PARAM_STR);
        $result->bindParam(':add_user_groups', $add_user_groups, PDO::PARAM_STR);
        $result->bindParam(':redirect_url', $redirect_url, PDO::PARAM_STR);
        if (!$result->execute()) {
            return false;
        }

        // Получить ID массовой рассылки
        if ($type == 1) {
            $result = $db->query("SELECT delivery_id FROM ".PREFICS."email_delivery WHERE create_date = $time AND send_time = $send");
            $data = $result->fetch(PDO::FETCH_ASSOC);

            if (!empty($data)) {
                $delivery_id = $data['delivery_id'];
                $status = 1;
                // Создать письмо и получить его ID
                $sql = 'INSERT INTO '.PREFICS.'email_letter 
                    (delivery_id, send_time, sender_name, subject, body, status, create_date, target)
                VALUES 
                    (:delivery_id, :send_time,:sender_name, :subject, :body, :status, :create_date, :target)';

                $result = $db->prepare($sql);
                $result->bindParam(':delivery_id', $delivery_id, PDO::PARAM_INT);
                $result->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
                $result->bindParam(':send_time', $send, PDO::PARAM_INT);
                $result->bindParam(':subject', $subject, PDO::PARAM_STR);
                $result->bindParam(':body', $letter, PDO::PARAM_STR);
                $result->bindParam(':target', $target, PDO::PARAM_STR);
                $result->bindParam(':status', $status, PDO::PARAM_INT);
                $result->bindParam(':create_date', $time, PDO::PARAM_INT);
                $result->execute();

                // Получить ID письма
                $result = $db->query("SELECT * FROM ".PREFICS."email_letter WHERE create_date = $time AND send_time = $send");
                $data = $result->fetch(PDO::FETCH_ASSOC);

                if (!empty($data)) {
                    return [
                        'letter_id' => $data['letter_id'],
                        'delivery_id' => $delivery_id
                    ];
                }
            }

            return false;
        }

        return true;
    }


    /**
     * ИЗМЕНИТЬ МАССОВУЮ ИЛИ АВТОСЕРИЮ
     * @param $id
     * @param $letter_id
     * @param $name
     * @param $type
     * @param $desc
     * @param $time
     * @param $subject
     * @param $letter
     * @param $confirmation
     * @param $target
     * @param $confirm_body
     * @param $confirm_subject
     * @param $after_confirm_text
     * @param $add_user_groups
     * @return bool
     */
    public static function editDelivery(
        $id, $letter_id, $name, $sender_name, $type, $desc, $time, $subject, $letter, $confirmation,
        $target, $confirm_body, $confirm_subject, $after_confirm_text, $add_user_groups, $redirect_url
    ){
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."email_delivery SET name = :name, delivery_desc = :delivery_desc, confirmation = :confirmation, 
                confirm_subject = :confirm_subject, confirm_body = :confirm_body, after_confirm_text = :after_confirm_text,
                add_user_groups = :add_user_groups, redirect_url = :redirect_url WHERE delivery_id = $id";
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':delivery_desc', $desc, PDO::PARAM_STR);
        $result->bindParam(':confirmation', $confirmation, PDO::PARAM_STR);
        $result->bindParam(':confirm_subject', $confirm_subject, PDO::PARAM_STR);
        $result->bindParam(':confirm_body', $confirm_body, PDO::PARAM_STR);
        $result->bindParam(':after_confirm_text', $after_confirm_text, PDO::PARAM_STR);
        $result->bindParam(':add_user_groups', $add_user_groups, PDO::PARAM_STR);
        $result->bindParam(':redirect_url', $redirect_url, PDO::PARAM_STR);

        if ($type == 1) {
            $result->execute();
            // Изменить письмо
            $sql = 'UPDATE '.PREFICS."email_letter SET  sender_name = :sender_name, subject = :subject, body = :body, target = :target WHERE letter_id = $letter_id";
            $result = $db->prepare($sql);
            $result->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
            $result->bindParam(':subject', $subject, PDO::PARAM_STR);
            $result->bindParam(':body', $letter, PDO::PARAM_STR);
            $result->bindParam(':target', $target, PDO::PARAM_STR);
        }

        return $result->execute();
    }
    
    
    // ПОДСЧИТАТЬ КОЛ-во РАССЫЛОК
    public static function countDeliveriesByType($type)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(delivery_id) FROM ".PREFICS."email_delivery WHERE type = $type ");
        $count = $result->fetch();
        if($count[0] > 0) return $count[0];
        else return false;
    }
    
    
    // ПОЛУЧИТЬ СПИСОК РАССЫЛОК
    public static function getDeliveryList($type, $page = 1, $show_items = null)
    {
        $offset = ($page - 1) * $show_items;
        $db = Db::getConnection();
        if($show_items == null) $result = $db->query("SELECT * FROM ".PREFICS."email_delivery WHERE type = $type ORDER BY delivery_id DESC");
        else $result = $db->query("SELECT * FROM ".PREFICS."email_delivery WHERE type = $type ORDER BY delivery_id DESC LIMIT ".$show_items." OFFSET $offset");
        $i = 0;
        while($row = $result->fetch()){
            $data[$i]['delivery_id'] = $row['delivery_id'];
            $data[$i]['name'] = $row['name'];
            $data[$i]['type'] = $row['type'];
            $data[$i]['send_time'] = $row['send_time'];
            $data[$i]['create_date'] = $row['create_date'];
            $data[$i]['count_letters'] = $row['count_letters'];
            $data[$i]['confirmation'] = $row['confirmation'];
            $data[$i]['count_bad'] = $row['count_bad'];
            $data[$i]['sent']=$row['sent'];
            $i++;
        }
        if(isset($data)) return $data;
        else return false;
    }
    // ПОЛУЧИТЬ СПИСОК РАССЫЛОК по ID
    public static function getDeliveryListForID($type, $page = 1, $show_items = null,$id)
    {
        $offset = ($page - 1) * $show_items;
        $db = Db::getConnection();
        if($show_items == null) $result = $db->query("SELECT * FROM ".PREFICS."email_delivery WHERE type = $type AND delivery_id = $id ORDER BY delivery_id DESC");
        else $result = $db->query("SELECT * FROM ".PREFICS."email_delivery WHERE type = $type AND delivery_id = $id ORDER BY delivery_id DESC LIMIT ".$show_items." OFFSET $offset");
        $i = 0;
        while($row = $result->fetch()){
            $data[$i]['delivery_id'] = $row['delivery_id'];
            $data[$i]['name'] = $row['name'];
            $data[$i]['type'] = $row['type'];
            $data[$i]['send_time'] = $row['send_time'];
            $data[$i]['create_date'] = $row['create_date'];
            $data[$i]['count_letters'] = $row['count_letters'];
            $data[$i]['confirmation'] = $row['confirmation'];
            $data[$i]['count_bad'] = $row['count_bad'];
            $data[$i]['sent'] = $row['sent'];
            $data[$i]['redirect_url'] =  $row['redirect_url'];
            $i++;
        }
        if(isset($data)) return $data;
        else return false;
    }

    // ПОЛУЧИТЬ СПИСОК ПЛОХИХ ЕМАЙЛОВ В РАССЫЛКЕ
    public static function getBadList($id)
    {
 
        $db = Db::getConnection();
        $result = $db->query("SELECT bad_list_id FROM ".PREFICS."email_delivery WHERE delivery_id = $id");
        
        while($row = $result->fetch()){
            $bad_list_id = $row['bad_list_id'];
        }
        if(isset($bad_list_id)) return $bad_list_id;
        else return false;
    }

    
    // ПОДСЧЁТ ОТПРАВЛЕННЫХ ПИСЕМ МАССОВОЙ РАССЫЛКИ
    public static function countSendLetters($delivery_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(id) FROM ".PREFICS."email_task_log WHERE delivery_id = $delivery_id AND action = 'send' ");
        $count = $result->fetch();
        return $count[0];
    }
    
    
    
    // УДАЛИТЬ РАССЫЛКУ /АВТОСЕРИЮ
    public static function delDelivery($id, $type)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_delivery WHERE delivery_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        
        if(!empty($type)){
            $result->execute();
            
            // Удалить письмо рассылки и задания в очереди
            $sql = 'DELETE FROM '.PREFICS.'email_letter WHERE delivery_id = :id ; DELETE FROM '.PREFICS.'email_task WHERE delivery_id = :id;
            DELETE FROM '.PREFICS.'email_task WHERE delivery_id = :id';
            $result = $db->prepare($sql);
            $result->bindParam(':id', $id, PDO::PARAM_INT);
            return $result->execute();
           
            
            
        } else {
            return $result->execute();   
        }
        
    }
    public static function delEmailFromResponder($id){
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_task WHERE task_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        return $result->execute();

    }
    
    
    
    /**
     *   СБОР СПИСКОВ ДЛЯ ОТПРАВКИ МАССОВОГО ПИСЬМА
     */
     
    // СПИСОК ИЗ ТИПОВ ПОЛЬЗОВАТЕЛЕЙ - ВОЗВРАЩАЕТ МАССИВ
    public static function getListByUserTypes($user_types)
    {
        $sql = '';
        foreach($user_types as $type){
            if($type != 'all'){
                    
                    $sql .= "AND $type = 1 ";
                }
        }
        
        if(in_array("all", $user_types)) $sql = '';
        
        
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."users WHERE status = 1 AND is_subs = 1 $sql ORDER BY user_id DESC");
        $i = 0;
        while($row = $result->fetch()){
            $data[$i] = $row['email'];
            $i++;
        }
        if(isset($data) && !empty($data)) return $data;
        else return array();
        
    }
    
    
    // СПИСОК ПО ГРУППАМ ЮЗЕРОВ - ВОЗВРАЩАЕТ МАССИВ
    public static function getListByUserGroups($user_groups)
    {
        $sql = implode(", ", $user_groups);
        $db = Db::getConnection();
        $result = $db->query("SELECT email FROM ".PREFICS."users WHERE is_subs = 1 AND user_id IN ( 
                                    SELECT user_id FROM ".PREFICS."user_groups_map WHERE group_id IN ($sql)
                                    )");
        
        $i = 0;
        while($row = $result->fetch()){
            $data[$i] = $row['email'];
            $i++;
        }
        if(isset($data)) return $data;
        else return array();
    }
    
    
    
    // СПИСОК ПО ПОДПИСКЕ - массив
    public static function getListByUserSubs($user_subs)
    {
        $sql = implode(", ", $user_subs);
        $db = Db::getConnection();
        $result = $db->query("SELECT email FROM ".PREFICS."users WHERE is_subs = 1 AND user_id IN ( 
                                    SELECT user_id FROM ".PREFICS."member_maps WHERE subs_id IN ($sql)
                                    )");
        
        $i = 0;
        while($row = $result->fetch()){
            $data[$i] = $row['email'];
            $i++;
        }
        if(isset($data)) return $data;
        else return array();
    }
    
    
    
    
    
    // СПИСОК ПО ЕМЕЙЛ РАССЫЛКАМ
    public static function getListByResponder($responders)
    {
        $sql = implode(", ", $responders);
        $db = Db::getConnection();
        $result = $db->query("SELECT email FROM ".PREFICS."email_subs_map WHERE delivery_id IN ( $sql ) AND confirmed > 1 AND cancelled = 0 AND spam = 0");
        
        $i = 0;
        while($row = $result->fetch()){
            $data[$i] = $row['email'];
            $i++;
        }
        if(isset($data)) return $data;
        else return array();
    }
    
    
    
    
    
    
    
    // СПИСОК ПО СЕГМЕНТАМ - массив
    public static function getListByUserSegments($user_segments, $chance)
    {
        $sql = implode(", ", $user_segments);
        if($chance == 0) $dop = '';
        else $dop = " count > $chance AND ";
        $db = Db::getConnection();
        $result = $db->query("SELECT email FROM ".PREFICS."users WHERE is_subs = 1 AND user_id IN ( 
                                    SELECT user_id FROM ".PREFICS."segments_user_map WHERE $dop sid IN ($sql)
                                    )");
        
        $i = 0;
        while($row = $result->fetch()){
            $data[$i] = $row['email'];
            $i++;
        }
        if(isset($data)) return $data;
        else return array();
    }
    
    
    
    // ПОЛУЧИТЬ ОБЩИЕ НАСТРОЙКИ РАССЫЛКИ
    public static function getResponderSetting()
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT params FROM ".PREFICS."extensions WHERE name = 'responder' ");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(isset($data)) return $data['params'];
    }
    
    
    // ПОЛУЧИТЬ СТАТУС МОДУЛЯ РАССЫЛКИ
    public static function getResponderStatus()
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT enable FROM ".PREFICS."extensions WHERE name = 'responder' ");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(isset($data)) return $data['enable'];
        else return false;
    }
    
    
    // УДАЛИТЬ ЗАДАНИЯ НА ОТПРАВКУ ПО ЕМЕЙЛ
    public static function deleteTaskFromUserEmail($email)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'email_task WHERE email = :email';
        $result = $db->prepare($sql);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        
        return $result->execute();
    }
    
    
    public static function getTotalSubscribers($filter = null)
    {
        $clauses = [];
        if ($filter && $filter['delivery']) {
            $clauses[] = "delivery_id = {$filter['delivery']}";
        }
        if ($filter && $filter['email']) {
            $clauses[] = "email LIKE '%{$filter['email']}%'";
        }

        $db = Db::getConnection();
        $where = $clauses ? 'WHERE '.implode(' AND ', $clauses) : '';
        $result = $db->query("SELECT COUNT(id) FROM ".PREFICS."email_subs_map $where");
        $count = $result->fetch();

        return $count[0];
    }
    
    
    
    // ИЗМЕНИТЬ НАСТРОЙКИ ВСЕЙ РАССЫЛКИ
    public static function SaveResponderSetting($params, $status)
    {
        $db = Db::getConnection();  
        $sql = "UPDATE ".PREFICS."extensions SET params = :params, enable = :enable WHERE name = 'responder'";
        $result = $db->prepare($sql);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':enable', $status, PDO::PARAM_INT);
        return $result->execute();
    }
    
    public static function deleteEmailFromEmailTask($email) {
        $db = Db::getConnection();

        $sql = "DELETE FROM ".PREFICS."email_task WHERE email = :email";

        $result = $db->prepare($sql);
        $result->bindParam(':email', $email, PDO::PARAM_STR);

        return $result->execute();
    }


}