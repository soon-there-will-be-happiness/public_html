<?php defined('BILLINGMASTER') or die;


class Flows {
    
    
    
    // ПОТОКИ ОДНОГО ЮЗЕРА
    public static function getFlowByUserID($user_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."flows_maps WHERE user_id = $user_id");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }
    
    
    
    // ОБНОВИТЬ ПОТОК ЮЗЕРА
    public static function updateUserMap($map_id, $start, $end_date, $status)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."flows_maps SET start = :start, end_date = :end_date, status  = :status WHERE map_id = :map_id";
        
        $result = $db->prepare($sql);
        $result->bindParam(':start', $start, PDO::PARAM_INT);
        $result->bindParam(':end_date', $end_date, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':map_id', $map_id, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    
    // ПРОИЗВЕСТИ НУЖНЫЕ СОБЫТИЯ В НАЧАЛЕ ПОТОКА
    public static function getFlowStartActions($map_item)
    {
        $flow_data = self::getFlowByID($map_item['flow_id']);
        
        if(!empty($flow_data['groups'])){
            // добавить группы
            
            $groups = json_decode($flow_data['groups'], true);
            foreach($groups as $group){
                $write = User::WriteUserGroup($map_item['user_id'], $group);
            }
        }
        
        
        if(!empty($flow_data['planes'])){
            // добавить планы мембершипа
            
            $planes = json_decode($flow_data['planes'], true);
            foreach($planes as $plane){
                $write = Member::renderMember($plane, $map_item['user_id']);
            }
        }
        
        
        if(!empty($flow_data['letter'])){
            // отправить письма
            $letter = json_decode($flow_data['letter'], true);
            if(!empty($letter['text'])){
                
                $setting = System::getSetting();
                $user = User::getUserById($map_item['user_id']);
                $prelink = User::generateAutoLoginLink($user);//Ссылка автологин без редиректа
                
                // реплейсим письмо
                $replace = array(
                    '[CLIENT_NAME]' => $user['user_name'],
                    '[FULL_NAME]' => $user['user_name'].' '.$user['surname'],
                    '[NAME]' => $user['user_name'] ?? " ",
                    '[CLIENT_PHONE]' => $user['phone'],
                    '[SUPPORT]' => $setting['support_email'],
                    '[EMAIL]' => $user['email'],
                    '[AUTH_LINK]' => $prelink,
                );
                
                $text = strtr($letter['text'], $replace);
                $text = User::replaceAuthLinkInText($text, $prelink);//Ссылка автологин с редиректом
                
                $send = Email::SendMessageToBlank($user['email'], $user['user_name'], $letter['subject'], $text);
            }
        }
        
    }
    
    // ПРОИЗВЕСТИ НУЖНЫЕ СОБЫТИЯ ПРИ ЗАВЕРШЕНИИ ПОТОКА
    public static function getFlowFinishActions($map_item)
    {
        $flow_data = self::getFlowByID($map_item['flow_id']);
        
        if(!empty($flow_data['del_groups'])){
            // добавить группы
            
            $del_groups = json_decode($flow_data['del_groups'], true);
            foreach($del_groups as $group){
                $del = User::deleteUserGroup($map_item['user_id'], $group);
            }
        }
        
        
        if(!empty($flow_data['letter'])){
            // отправить письма
            $letter = json_decode($flow_data['letter'], true);
            if(!empty($letter['text_after'])){
                
                $user = User::getUserById($map_item['user_id']);
                $prelink = User::generateAutoLoginLink($user);//Ссылка автологин без редиректа
                
                // реплейсим письмо
                $replace = array(
                    '[CLIENT_NAME]' => $user['user_name'],
                    '[FULL_NAME]' => $user['user_name'].' '.$user['surname'],
                    '[NAME]' => $user['user_name'] ?? " ",
                    '[CLIENT_PHONE]' => $user['phone'],
                    '[SUPPORT]' => $setting['support_email'],
                    '[EMAIL]' => $user['email'],
                    '[AUTH_LINK]' => $prelink,
                );
                
                $text = strtr($letter['text_after'], $replace);
                $text = User::replaceAuthLinkInText($text, $prelink);//Ссылка автологин с редиректом
                
                $send = Email::SendMessageToBlank($user['email'], $user['user_name'], $letter['subject_after'], $text);
            }
        }
        
    }
    
    
    
    
    // ИЗМЕНИТЬ СТАТУС В КАРТЕ ПОТОКА
    public static function updateMapStatus($map_id, $status)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."flows_maps SET status = :status WHERE map_id = :map_id";
        
        $result = $db->prepare($sql);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':map_id', $map_id, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // ПОИСК ЮЗЕРОВ В КАРТЕ ПОТОКОВ
    public static function searchInFlowMap($status, $date)
    {
        $db = Db::getConnection();
        if($status == 0) $where = 'start < ';
        else $where = 'end_date < ';
        $sql = "SELECT * FROM ".PREFICS."flows_maps WHERE status = $status AND $where $date";
        
        $result = $db->query($sql);
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }


    /**
     * ДОБАВИТЬ ЮЗЕРА В КАРТУ ПОТОКА
     * @param $flow_id
     * @param $user_id
     * @param $order_item_id
     * @return bool
     */
    public static function addFlowInMap($flow_id, $user_id, $order_item_id)
    {
        $db = Db::getConnection();
        
        $flow_data = self::getFlowByID($flow_id);
        
        $letter = json_decode($flow_data['letter'], true);
        if(!empty($letter['sell_emails'])) $send = self::sendLetterToManager($letter, $user_id);
        
        $start = $flow_data['start_flow'];
        $end_date = $flow_data['end_flow'];
        $status = 0;
           
        $sql = 'INSERT INTO '.PREFICS.'flows_maps (user_id, flow_id, status, start, end_date, order_item_id) 
            VALUES (:user_id, :flow_id, :status, :start, :end_date, :order_item_id)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':flow_id', $flow_id, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':start', $start, PDO::PARAM_INT);
        $result->bindParam(':end_date', $end_date, PDO::PARAM_INT);
        $result->bindParam(':order_item_id', $order_item_id, PDO::PARAM_INT);
        
        return $result->execute();
    }
    
    
    // ОТПРАВИТЬ ПИСЬМО МЕНЕДЖЕРАМ
    public static function sendLetterToManager($letter, $user_id)
    {
        if(!empty($letter['sell_text'])){
            
            // отправить письма  
            $user = User::getUserById($user_id);
            
            // реплейсим письмо
            $replace = array(
                '[CLIENT_NAME]' => $user['user_name'],
                '[FULL_NAME]' => $user['user_name'].' '.$user['surname'],
                '[NAME]' => $user['user_name'] ?? " ",
                '[CLIENT_PHONE]' => $user['phone'],
                '[EMAIL]' => $user['email']
            );
            
            $text = strtr($letter['sell_text'], $replace);
            
            $emails = explode(",", $letter['sell_emails']);
            
            foreach($emails as $email){
                $email = trim($email);
                $send = Email::SendMessageToBlank($email, 'SM', $letter['sell_subject'], $text);
            }
        }
    }


    /**
     * ПОДСЧИТАТЬ ЮЗЕРОВ В ПОТОКЕ
     * @param $flow_id
     * @param null $order_item_id
     * @return mixed
     */
    public static function countUsersInFlow($flow_id, $order_item_id = null)
    {
        $db = Db::getConnection();
        $where = "flow_id = $flow_id" . ($order_item_id ? " AND order_item_id = $order_item_id" : '');
        $result = $db->query('SELECT COUNT(map_id) FROM '.PREFICS."flows_maps WHERE $where");
        $count = $result->fetch();
        
        return $count[0];
    }
    
    // СПИСОК ПОТОКОВ
    public static function getFlows()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."flows ORDER BY flow_id DESC");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }
    
    
    
    // ПОЛУЧИТЬ ДАННЫЕ ПОТОКА ПО ID
    public static function getFlowByID($id)
    {
        $db = Db::getConnection();
        $sql = " SELECT * FROM ".PREFICS."flows WHERE flow_id = :id LIMIT 1 ";
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        return !empty($data) ? $data : false;
    }
    
    
    // ПОЛУЧИТЬ ПОТОКИ ДЛЯ ПРОДУКТА
    public static function getFlowForProduct($product_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT flow_id FROM ".PREFICS."flows_products WHERE product_id = $product_id ");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row['flow_id'];
        }
        return !empty($data) ? $data : false;
    }
    
    
    
    // ПОЛУЧИТЬ ИМЯ ПОТОКА
    public static function getFlowName($flow_id)
    {
        $db = Db::getConnection();

        $sql = " SELECT flow_name FROM ".PREFICS."flows WHERE flow_id = :flow_id LIMIT 1 ";
        $result = $db->prepare($sql);
        $result->bindParam(':flow_id', $flow_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        return !empty($data) ? $data['flow_name'] : false;
    }
    
    
    // ПОЛУЧИТЬ АКТУАЛЬНЫЕ ПОТОКИ ПО ИХ ID 
    public static function getActualFlowByIDs($flow_ids, $date)
    {
        $db = Db::getConnection();
        $flow_ids = implode(',', $flow_ids);
        $result = $db->query("SELECT * FROM ".PREFICS."flows WHERE status = 1 AND public_start < $date AND public_end > $date AND flow_id IN ($flow_ids)");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }
    
    
    // ПОДСЧИТАТЬ ОПЛАЧЕННЕ ЗАКАЗЫ С КОНКРЕТНЫМ ПОТОКОМ
    public static function countOrdersFromFlowID($flow_id)
    {
        $db = Db::getConnection();
        $result = $db->query('SELECT COUNT(order_item_id) FROM '.PREFICS.'order_items WHERE status = 1 AND flow_id = '.$flow_id);
        $count = $result->fetch();
        return $count[0];
    }
    
    
    
    // ДОБАВИТЬ НОВЫЙ ПОТОК
    public static function addFlow($flow_name, $flow_title, $status, $start_flow, $end_flow, $show_period, $public_start,
                                   $public_end, $add_group_arr, $del_group_arr, $add_plane_arr, $letter_arr, $products, $limit, $is_default)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO `'.PREFICS.'flows` (`flow_name`, `flow_title`, `start_flow`, `end_flow`, `show_period`, `public_start`, `public_end`, `status`, `groups`, `del_groups`, `planes`, `letter`, `limit_users`, `is_default`) 
            VALUES (:flow_name, :flow_title, :start_flow, :end_flow, :show_period, :public_start, :public_end, :status, :groups, :del_groups, :planes, 
            :letter, :limit, :is_default)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':flow_name', $flow_name, PDO::PARAM_STR);
        $result->bindParam(':flow_title', $flow_title, PDO::PARAM_STR);
        $result->bindParam(':start_flow', $start_flow, PDO::PARAM_INT);
        $result->bindParam(':end_flow', $end_flow, PDO::PARAM_INT);
        $result->bindParam(':show_period', $show_period, PDO::PARAM_INT);
        $result->bindParam(':public_start', $public_start, PDO::PARAM_INT);
        $result->bindParam(':public_end', $public_end, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        $result->bindParam(':groups', $add_group_arr, PDO::PARAM_STR);
        $result->bindParam(':del_groups', $del_group_arr, PDO::PARAM_STR);
        $result->bindParam(':planes', $add_plane_arr, PDO::PARAM_STR);
        $result->bindParam(':letter', $letter_arr, PDO::PARAM_STR);
        $result->bindParam(':is_default', $is_default, PDO::PARAM_INT);
        $result = $result->execute();

        if($result && !empty($products)){
            $flow_id = $db->lastInsertId();
            foreach($products as $product_id){
                self::addProductforFlow($flow_id, $product_id);
            }
        }
        return $result;
    }
    
    
    
    // РЕДАКТИРОВАТЬ ПОТОК
    public static function editFlow($id, $flow_name, $flow_title, $status, $start_flow, $end_flow, $show_period, $public_start, $public_end, $add_group_arr, $del_group_arr, $add_plane_arr, 
                                $letter_arr, $limit, $is_default)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."flows SET flow_name = :flow_name, flow_title = :flow_title, start_flow = :start_flow, end_flow = :end_flow, show_period = :show_period,
                                public_start = :public_start, public_end = :public_end, status = :status, groups = :groups, del_groups = :del_groups, planes = :planes, letter = :letter,
                                limit_users = :limit, is_default = :is_default WHERE flow_id = :id";
        
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->bindParam(':flow_name', $flow_name, PDO::PARAM_STR);
        $result->bindParam(':flow_title', $flow_title, PDO::PARAM_STR);
        $result->bindParam(':start_flow', $start_flow, PDO::PARAM_INT);
        $result->bindParam(':end_flow', $end_flow, PDO::PARAM_INT);
        $result->bindParam(':show_period', $show_period, PDO::PARAM_INT);
        $result->bindParam(':public_start', $public_start, PDO::PARAM_INT);
        $result->bindParam(':public_end', $public_end, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':limit', $limit, PDO::PARAM_INT);
        $result->bindParam(':groups', $add_group_arr, PDO::PARAM_STR);
        $result->bindParam(':del_groups', $del_group_arr, PDO::PARAM_STR);
        $result->bindParam(':planes', $add_plane_arr, PDO::PARAM_STR);
        $result->bindParam(':letter', $letter_arr, PDO::PARAM_STR);
        $result->bindParam(':is_default', $is_default, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // ПОЛУЧИТЬ ПРОДУКТЫ ДЛЯ ПОТОКА
    public static function getProductsInFlow($flow_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT product_id FROM ".PREFICS."flows_products WHERE flow_id = $flow_id");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row['product_id'];
        }
        
        return !empty($data) ? $data : false;
    }
    
    
    // ДОБАВИТЬ ПРОДУКТ ДЛЯ ПОТОКА
    public static function addProductforFlow($flow_id, $product_id)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'flows_products (flow_id, product_id) 
            VALUES (:flow_id, :product_id)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':flow_id', $flow_id, PDO::PARAM_INT);
        $result->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        
        return $result->execute();
    }
    
    
    // УДАЛИТЬ ПРОДУКТ ИЗ ПОТОКА
    public static function deleteProductInFlow($flow_id, $product_id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'flows_products WHERE flow_id = :flow_id AND product_id = :product_id';
        $result = $db->prepare($sql);
        $result->bindParam(':flow_id', $flow_id, PDO::PARAM_INT);
        $result->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    
    // УДАЛИТЬ ПОТОК
    public static function delFlow($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'flows WHERE flow_id = :id; DELETE FROM '.PREFICS.'flows_products WHERE flow_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $result->execute();
    }


    /**
     * @param $flow_id
     * @return array
     */
    public static function getStatistics($flow_id) {
        $db = Db::getConnection();
        $sql = "SELECT COUNT(o.order_id) AS count, SUM(oi.price) AS sum FROM ".PREFICS."orders AS o
                LEFT JOIN ".PREFICS."order_items AS oi ON oi.order_id = o.order_id
                WHERE oi.flow_id = $flow_id AND o.status = 1";
        $result = $db->query($sql);
        $paid = $result->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(o.order_id) AS count, SUM(oi.price) AS sum FROM ".PREFICS."orders AS o
                LEFT JOIN ".PREFICS."order_items AS oi ON oi.order_id = o.order_id
                WHERE oi.flow_id = $flow_id";
        $result = $db->query($sql);
        $issue = $result->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(o.order_id) AS count, SUM(oi.price) AS sum FROM ".PREFICS."orders AS o
                LEFT JOIN ".PREFICS."order_items AS oi ON oi.order_id = o.order_id
                WHERE oi.flow_id = $flow_id AND o.status = 2";
        $result = $db->query($sql);
        $expect_confirm = $result->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT COUNT(o.order_id) AS count, SUM(oi.price) AS sum FROM ".PREFICS."orders AS o
                LEFT JOIN ".PREFICS."order_items AS oi ON oi.order_id = o.order_id
                WHERE oi.flow_id = $flow_id AND o.status = 9";
        $result = $db->query($sql);
        $refund = $result->fetch(PDO::FETCH_ASSOC);

        $stat = [
            'paid' => [ //оплаченные счета
                'count' => $paid['count'],
                'sum' => (int)$paid['sum']
            ],
            'issue' => [ //выписанные счета
                'count' => $issue['count'],
                'sum' => (int)$issue['sum']
            ],
            'expect_confirm' => [ //ждут подтверждения
                'count' => $expect_confirm['count'],
                'sum' => (int)$expect_confirm['sum']
            ],
            'refund' => [ //возвраты
                'count' => $refund['count'],
                'sum' => (int)$refund['sum']
            ],
        ];

        return $stat;
    }


    /**
     * УДАЛИТЬ ПОТОК ДЛЯ ЭЛЕМЕНТА ЗАКАЗА
     * @param $flow_id
     * @param $order_item_id
     * @return bool
     */
    public static function delFlow2OrderItemId($flow_id, $order_item_id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS."flows_maps WHERE flow_id = :flow_id AND order_item_id = :order_item_id";

        $result = $db->prepare($sql);
        $result->bindParam(':order_item_id', $order_item_id, PDO::PARAM_INT);
        $result->bindParam(':flow_id', $flow_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ПОТОК ДЛЯ ЗАКАЗА
     * @param $order_id
     * @return bool
     */
    public static function delFlows2Order($order_id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE fm FROM '.PREFICS."flows_maps AS fm 
                INNER JOIN ".PREFICS."order_items AS oi 
                ON oi.flow_id = fm.flow_id AND oi.order_item_id = fm.order_item_id
                WHERE oi.order_id = :order_id";

        $result = $db->prepare($sql);
        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);

        return $result->execute();
    }
}