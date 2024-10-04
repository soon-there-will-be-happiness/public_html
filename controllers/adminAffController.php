<?php defined('BILLINGMASTER') or die; 


class adminAffController extends AdminBase {
    
    // ВЫПЛАТЫ ПАРТНЁРСКИХ - СПИСОК КОМУ НУЖНО ВЫПЛАТИТЬ
    public function actionIndex()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_aff'])) {
            System::redirectUrl("/admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $params = unserialize(System::getExtensionSetting('partnership'));
		
		if (isset($_POST['add_transaction']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_aff'])){
                System::redirectUrl("/admin");
            }

            $user_id = intval($_POST['pid']);
            $order_id = intval($_POST['order_id']);
            $order = $order_id ? Order::getOrder($order_id) : null;
            $client_email = $order ? $order['client_email'] : null;
            $summ = intval($_POST['summ']);
            $date = strtotime($_POST['date']);
            $product_id = 1;
            
            $add = Aff::PartnerTransaction($user_id, $order_id, $product_id, $summ, 0, 1, $client_email);
            if ($add) {
                System::redirectUrl("/admin/aff?success");
            }
        }
        
        if (isset($_POST['pay'])) {
            if (!isset($acl['change_aff'])) {
                System::redirectUrl("/admin");
            }

            $id = intval($_POST['partner']);
            $pay = intval($_POST['summ']);
            $order_id = 0;
            $type = 0;
            $summ = 0;
			$product_id = 0;
            $order = $order_id ? Order::getOrder($order_id) : null;
            $client_email = $order ? $order['client_email'] : null;

            $add = Aff::PartnerTransaction($id, $order_id, $product_id, $summ, $pay, $type, $client_email);
            
            // Отправить письмо о выплате
            if ($add) {
                Aff::SendPartnerNotifOfPay($id, $pay);
                System::redirectUrl("/admin/aff?success");
            }
            
        }
        $partners = Aff::getPartnersToPay();
        $title='Партнерка - выплаты';
        require_once (ROOT . '/template/admin/views/aff/index.php');
        return true;
    }
    
    
    // СПИСОК ВЫПЛАТ ПАРТНЁРАМ
    public function actionPaystat()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_aff'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $params = unserialize(System::getExtensionSetting('partnership'));
        
        $partners = Aff::getPartnersToPay(1);
        $title='Партнерка - список выплат';
        require_once (ROOT . '/template/admin/views/aff/pay_stat.php');
        return true;
    }

    
    // СТАТИСТИКА ВЫПЛАТ АВТОРАМ 
    public function actionAuthorpaystat()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_aff'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $params = unserialize(System::getExtensionSetting('partnership'));
        
        $authors = Aff::getAuthorsToPay(1);
        $title='Партнерка статистика выплат';
        require_once (ROOT . '/template/admin/views/aff/authors_stat.php');
        return true;
        
    }
    
    // ВЫПЛАТЫ АВТОРСКИХ - СПИСОК КОМУ НУЖНО ВЫПЛАТИТЬ
    public function actionAuthors()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_aff'])) header("Location: /admin");
        if(!isset($acl['change_aff'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $params = unserialize(System::getExtensionSetting('partnership'));  
        
        if(isset($_POST['pay'])){
            
            if(!isset($acl['change_aff'])){
                header("Location: /admin");
                exit();
            }
            
            $id = intval($_POST['partner']);
            $pay = intval($_POST['summ']);
            $order_id = 0;
            $type = 0;
            $summ = 0;
            
            $add = Aff::AuthorTransaction($id, $order_id, 0 ,$summ, $pay, $type);
            
            // Отправить письмо о выплате
            if($add){
                Aff::SendPartnerNotifOfPay($id, $pay);
                header("Location: ".$setting['script_url']."/admin/authors?success");
            }
            
        }
        $authors = Aff::getAuthorsToPay();
        $title='Партнерка - выплаты авторских';
        require_once (ROOT . '/template/admin/views/aff/authors.php');
        return true;
    }
    
    
    
    // СТАТИСТИКА НАЧИСЛЕНИЙ ПАРТНЁРАМ
    public function actionUserstat($id)
    {
        $id = intval($id);
        $acl = self::checkAdmin();
        if (!isset($acl['show_aff'])) {
            System::redirectUrl("/admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $params = unserialize(System::getExtensionSetting('partnership'));

		$items = Aff::getHistoryTransactionNew($id, 'aff'); // Все вместе
        $pays = Aff::getHistoryTransaction($id, 0, 'aff'); // Выплаты
        $user = User::getUserById($id);

        if (isset($_POST['stat_id']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_aff'])){
                System::redirectUrl("/admin");
            }

            $stat_id = intval($_POST['stat_id']);
            $summ = intval($_POST['summ']);

            $edit = Aff::reloadComiss($stat_id, $summ, 'aff');
            if ($edit) {
                System::redirectUrl("/admin/aff/userstat/$id");
            }
        }
        $title='Партнерка статистика начислений';
        require_once (ROOT . '/template/admin/views/aff/userstat.php');
        return true;
    }
    
    
    
    // СТАТИСТИКА НАЧИСТЕЛИЙ АВТОРАМ
    public function actionAuthorstat($id)
    {
        $id = intval($id);
        $acl = self::checkAdmin();
        if(!isset($acl['show_aff'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $params = unserialize(System::getExtensionSetting('partnership'));
		
        // Все вместе
		$items = Aff::getHistoryTransactionNew($id, 'author');
        
        // начисления
        //$items = Aff::getHistoryTransaction($id, 1, 'author');
        
        // Выплаты
        $pays = Aff::getHistoryTransaction($id, 0, 'author');
        
        $user = User::getUserById($id);
        
        if(isset($_POST['stat_id']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            if(!isset($acl['change_aff'])){
                header("Location: /admin");
                exit();
            }
            $stat_id = intval($_POST['stat_id']);
            $summ = intval($_POST['summ']);
            
            $edit = Aff::reloadComiss($stat_id, $summ, 'author');
            if($edit)header("Location: /admin/authors/userstat/$id");
            
        }
        $title='Партнерка - статистика начислений авторам';
        require_once (ROOT . '/template/admin/views/aff/authorstat.php');
        return true;
    }
    
    
    
    public static function actionTop()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_aff'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $users = Aff::getTopPartners();
        $title='Партнерка';
        require_once (ROOT . '/template/admin/views/aff/usertop.php');
        return true;
    }
    
    
    // НАСТРОЙКИ ПАРТНЁРКИ
    public function actionPartnership()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_aff'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['saveaff']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            if(!isset($acl['change_aff'])){
                header("Location: /admin");
                exit();
            }
            $params = serialize($_POST['aff']);
            $status = intval($_POST['status']);
            
            $save = Aff::SaveAffSetting($params, $status);
        }
        
        $params = unserialize(System::getExtensionSetting('partnership'));
        $enable = System::getExtensionStatus('partnership');
        $title='Партнерка - настройки';
        require_once (ROOT . '/template/admin/views/settings/aff.php');
        return true;
    }
    
    
}