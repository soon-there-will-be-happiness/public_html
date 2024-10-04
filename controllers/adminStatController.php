<?php defined('BILLINGMASTER') or die; 


class adminStatController extends AdminBase {

    /**
     * ЛОГ ДЕЙСТВИЙ В АДМИНКЕ
     */
    public function actionActionlog()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_stat'])) {
            System::redirectUrl("/admin");
        }
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        $filter = [
            'extension' => isset($_GET['extension']) ? htmlentities($_GET['extension']) : null,
            'start_date' =>  isset($_GET['start_date']) && $_GET['start_date'] ? strtotime($_GET['start_date']) : null,
            'finish_date' => isset($_GET['finish_date']) && $_GET['finish_date'] ? strtotime($_GET['finish_date']) : null,
            'element_id' => isset($_GET['element_id']) && $_GET['element_id'] ? (int)$_GET['element_id'] : null,
        ];
        $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;

        $total = ActionLog::getActionLogTotal($filter);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $action_log = ActionLog::getActionLog($filter, $page, $setting['show_items']);

        $pagination = new Pagination($total, $page, $setting['show_items']);
        $title = 'Лог';
        require_once (ROOT . '/template/admin/views/stat/actionlog.php');
        return true;
    }


    /**
     * ПРОСМОТР ЗАПИСИ В ЛОГЕ ДЕЙСТВИЙ
     * @param $id
     */
    public function actionActionlogview($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_stat'])) {
            System::redirectUrl("/admin");
        }
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $log = ActionLog::getActionLogView($id);
        $title = 'Лог - просмотр';
        require_once (ROOT . '/template/admin/views/stat/actionlog_view.php');
        return true;
    }


    /**
     * РАСШИРЕННАЯ СТАТИСТИКА
     */
    public static function actionExtstat ()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_stat'])) {
            System::redirectUrl("/admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $paid = isset($_GET['paid']) && $_GET['paid'] == 1 ? true : false;
        $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");
        
        $now = time();
        $months = [
            'Декабрь', 'Январь', 'Февраль',
            'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь',
            'Октябрь', 'Ноябрь', 'Декабрь'
        ];

        $current_month = date("n");
        $current_day = date("j");
        $count_days_in_year = date("z");
        
        // даты для самого первого периода
        $start_1 = strtotime("01.01.$year 00:00");
        $finish_1 = $current_month != 1 ? strtotime("01.01.$year 00:00") : $now;
        $title = 'Расширенная статистика';
        if (isset($_POST['get_stat'])) {
            require_once (ROOT . "/template/admin/views/stat/extstat/{$_POST['get_stat']}".'.php');
        } else {
            require_once (ROOT . '/template/admin/views/stat/extstat/index.php');
        }
        return true;
    }
    
	
	
	
    public static function actionIndex ()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_stat'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['filter'])){
            
            $start = strtotime($_POST['start']);
            $finish = strtotime($_POST['finish']);
            
        } else {
            $start = $finish = null;
        }

        // Определяем время
        // Текущий час
        $curr_hour = date("H");
        
        $cur_minute = date("i") *60; // Текущие минуты в секундах
        
        $hour = $curr_hour * 60 *60; // Часы в секундах
        
        $today_time_left = $hour + $cur_minute; // Прошло секунд сегодня с 00:00
        
        // Прошло секунд со вчера
        $yesteday_time_left = (($curr_hour + 24)*60 *60) + $cur_minute;
        
        // Дата начала дня в timestamp 
        $day = time() - $today_time_left;
        
        // Дата начал вчерашнего дня в сек.
        $yesterday = time() - $yesteday_time_left;
        
        $day7 = time() - (3600 *24 * 7);
        $day30 = time() - (3600 * 24 * 30);

        $title = 'Статистика';
        require_once (ROOT . '/template/admin/views/stat/index.php');
        return true;
    }
    
    
	
	
	// ЛОГ ПЛАТЕЖЕЙ
    public function actionPaylog()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_stat'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['filter'])){
            
            $start = strtotime($_POST['start']);
            $finish = strtotime($_POST['finish']);
			$subscriptionID = $_POST['subscriptionID'];
			$email = $_POST['email'];
            $log_List = Stat::getPayLog($start, $finish, $subscriptionID, $email);
            
        } elseif(isset($_GET['order_date']) && $_GET['order_date'] > 0){
            
            $log_List = Stat::getPayLogGET(intval($_GET['order_date']));
            
        } else $log_List = Stat::getPayLog();
        
        $title = 'Лог - платежи';
        require_once (ROOT . '/template/admin/views/stat/paystat.php');
        return true;
    }
    
    
    // ПРОСМОТР ПЛАТЕЖА
    public function actionPaylogview($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_stat'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $id = intval($id);
        
        $log = Stat::getPayLogItem($id);
        echo 'ID: '.$log['id'].'<br />Дата: '.date("d.m.Y H:i:s", $log['transaction_date']).'<br />Тип:'.$log['specify'].'<br />Заказ: '.$log['order_date'].'<br />SubscriptionID: '.$log['subs_id'].
        '<br />Платёжная система: '.$log['payment_id'].'<br />'.$log['query'];
    }
    
	
	
    
    // СТАТИСТИКА ПО ПРОДУКТАМ
    public function actionProductstat()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_stat'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $order = 'summ';
        $start = null;
        $finish = null;

        if (isset($_GET['reset'])) {
            if (isset($_SESSION['filter'])) {
                unset($_SESSION['filter']);
                header("Location: /admin/stat/product");
            }
        }

        if(isset($_POST['filter'])){
            $start = strtotime($_POST['start']);
            $finish = strtotime($_POST['finish']);
            $_SESSION['filter']['start'] = $start; 
            $_SESSION['filter']['finish'] = $finish;
        }
        
        if(isset($_GET['order'])){
            $order = $_GET['order'];
            if(isset($_SESSION['filter'])){
                $start = $_SESSION['filter']['start'];
                $finish = $_SESSION['filter']['finish'];
            }
        }
        
        $products = Stat::getProductStat($order,$start,$finish);
        $title = 'Статистика по продуктам';
        require_once (ROOT . '/template/admin/views/stat/stat_product.php');
        return true;
    }
    
    
    
    // СТАТИСТИКА ПО КАНАЛАМ
    public function actionChannelstat()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_stat'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $channel_list = Stat::getChannelList();
        
        $group_list = Stat::getGroupList();
        $title = 'Статистика по каналам';
        require_once (ROOT . '/template/admin/views/stat/stat_channels.php');
        return true;
    }
    
    
    
    // КАНАЛЫ ТРАФИКА
    public static function actionChannels()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $channel_list = Stat::getChannelList();
        $title = 'Каналы трафика';
        require_once (ROOT . '/template/admin/views/stat/channels.php');
        return true;
    }
    
    
    
    // ДОБАВИТЬ КАНАЛ
    public function actionAddchannels()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            if(!isset($acl['change_channel'])){
                header("Location: /admin");
                exit();
            }
            $add = Stat::addChannel(htmlentities($_POST['name']), intval($_POST['group']), htmlentities($_POST['channel_desc']), 
            htmlentities($_POST['source']), htmlentities($_POST['medium']), htmlentities($_POST['campaign']), htmlentities($_POST['content']),
            htmlentities($_POST['term']), intval($_POST['summ']));
            
            if($add) header("Location: ".$setting['script_url']."/admin/channels?success");
            
        }
        $title = 'Каналы трафика - добавление';
        require_once (ROOT . '/template/admin/views/stat/add_channel.php');
        return true;
    }
    
    
    
    // ИЗМЕНИТЬ КАНАЛ
    public static function actionEditchannel($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_channel'])){
                header("Location: /admin");
                exit();
            }
            $edit = Stat::editChannel($id, htmlentities($_POST['name']), intval($_POST['group']), htmlentities($_POST['channel_desc']), 
            htmlentities($_POST['source']), htmlentities($_POST['medium']), htmlentities($_POST['campaign']), htmlentities($_POST['content']),
            htmlentities($_POST['term']), intval($_POST['summ']));
            
            if($edit) header("Location: ".$setting['script_url']."/admin/channels/edit/$id?success");
            
        }
        
        $channel = Stat::getChannelData($id);
        $title = 'Каналы трафика - изменение';
        require_once (ROOT . '/template/admin/views/stat/edit_channel.php');
        return true;
    }
    
    
    // УДАЛИТЬ КАНАЛ
    public static function actionDelchannel($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['del_channel'])) {
            header("Location: /admin/stat");
            exit();   
        }
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            $del = Stat::deleteChannel($id);
            
            if($del) header("Location: ".$setting['script_url']."/admin/channels");
        }
    }
    
    /**
     *   ГРУППЫ КАНАЛОВ
     */
    
    
    // ГРУППЫ КАНАЛОВ
    public static function actionGroupchannels()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $title = 'Каналы трафика - группы';
        require_once (ROOT . '/template/admin/views/stat/groups.php');
        return true;
    }
    
    
    // ДОБАВИТЬ ГРУППУ КАНАЛОВ
    public function actionAddgroup()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_channel'])){
                header("Location: /admin");
                exit();
            }
            $add = Stat::addGroup(htmlentities($_POST['name']));
            if($add) header("Location: ".$setting['script_url']."/admin/channels/group?success");
            
        }
        $title = 'Каналы трафика - добавление группы';
        require_once (ROOT . '/template/admin/views/stat/add_group.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ ГРУППУ
    public function actionEditgroup($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_channel'])){
                header("Location: /admin");
                exit();
            }
            $edit = Stat::editGroup($id, htmlentities($_POST['name']));
            if($edit) header("Location: ".$setting['script_url']."/admin/channels/group?success");
            
        }
        
        $group = Stat::getGroupData($id);
        $title = 'Каналы трафика - изменение группы';
        require_once (ROOT . '/template/admin/views/stat/edit_group.php');
        return true;
    }
    
    
    // УДАЛИТЬ ГРУППУ
    public function actionDelgroup($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['del_channel'])) {
            header("Location: /admin/stat");
            exit();   
        }
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            $del = Stat::deleteGroup($id);
            
            if($del) header("Location: ".$setting['script_url']."/admin/channels/group");
        }
    }
    
    
    
    // ЖУРНАЛ ОТПРАВКИ ПИСЕМ В СИСТЕМЕ
    public function actionEmailog()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total = Email::countLogs();
        
        $is_pagination = true;
        $pagination = new Pagination($total, $page, 100);
        
        if(isset($_GET['reset'])){
            if(isset($_SESSION['log_filter'])) {
                unset($_SESSION['log_filter']);
                header("Location: /admin/emailog");
            }
        }
        
        if(isset($_POST['filter']) || isset($_SESSION['log_filter'])){
            $is_pagination = false;
            
            
            if(!empty($_POST['email']) || isset($_SESSION['log_filter']['email']) && !empty($_SESSION['log_filter']['email'])) {
                
                !empty($_POST['email']) ? $email = htmlentities($_POST['email']) : $email = htmlentities($_SESSION['log_filter']['email']);
                $_SESSION['log_filter']['email'] = $email; 
            } else $email = false;
            
            
            if(!empty($_POST['type']) || isset($_SESSION['log_filter']['type']) && !empty($_SESSION['log_filter']['type'])) {
                
                !empty($_POST['type']) ? $type = htmlentities($_POST['type']) : $type = htmlentities($_SESSION['log_filter']['type']);
                $_SESSION['log_filter']['type'] = $type; 
            } else $type = false;
            
            
            if(!empty($_POST['start']) || isset($_SESSION['log_filter']['start'])) {
                
                isset($_POST['start']) ? $start = strtotime($_POST['start']) : $start = strtotime($_SESSION['log_filter']['start']);
                if(isset($_POST['start'])) $_SESSION['log_filter']['start'] = $_POST['start'];   
            } else $start = null;
            
            if(!empty($_POST['finish']) || isset($_SESSION['log_filter']['finish'])) {
                
                isset($_POST['finish']) ? $finish = strtotime($_POST['finish']) : $finish = strtotime($_SESSION['log_filter']['finish']);
                if(isset($_POST['finish']))$_SESSION['log_filter']['finish'] = $_POST['finish'];   
            } else $finish = null;
            
            if(!empty($_POST['type'])) $type = htmlentities($_POST['type']);
            else $type = false;
            
            $log_list = Email::getLog($page, 100, false, $email, $start, $finish, $type);
            
            if($log_list) $count = count($log_list);
            else $count = 0;
            
        } else $log_list = Email::getLog($page, 100, $is_pagination);
        $title = 'Журнал отправки писем';
        require_once (ROOT . '/template/admin/views/stat/email_log.php');
        return true;
    }
    
    // ПОСМОТРЕТЬ ЗАПИСЬ ЛОГА
    public function actionEmailogview($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_channel'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $log = Email::getLogData($id);
        $title = 'Лог - просмотр записи';
        require_once (ROOT . '/template/admin/views/stat/email_log_view.php');
        return true;
    }


    /**
     * ЛОГИ SMS
     */
    public function actionSmslog() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_channel'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        if(isset($_GET['reset']) && isset($_SESSION['sms_filter'])){
            unset($_SESSION['sms_filter']);
            System::redirectUrl('/admin/smslog');
        }

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total = SMS::countLogs();
        $is_pagination = true;
        $pagination = new Pagination($total, $page, 100);
        $phone = $start = $finish = null;

        if (isset($_POST['filter']) || isset($_SESSION['sms_filter'])) {
            $is_pagination = false;
            $phone = isset($_POST['phone']) ? htmlentities($_POST['phone']) : $_SESSION['sms_filter']['phone'];
            $start = isset($_POST['start']) ? strtotime($_POST['start']) : strtotime($_SESSION['sms_filter']['start']);
            $finish = isset($_POST['finish']) ? strtotime($_POST['finish']) : strtotime($_SESSION['sms_filter']['finish']);

            if (isset($_POST['filter'])) {
                $_SESSION['sms_filter'] = [
                    'phone' => $phone,
                    'start' => $_POST['start'],
                    'finish' => $_POST['finish']
                ];
            }

            $sms_list = SMS::getLog($phone, $start, $finish);
            $count = $sms_list ? count($sms_list) : 0;
        } else {
            $sms_list = SMS::getLog();
        }
        $title = 'Лог sms';
        require_once (ROOT . '/template/admin/views/stat/sms_log.php');
        return true;
    }
}