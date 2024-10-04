<?php defined('BILLINGMASTER') or die;

class adminFlowController extends AdminBase {
    
    
    // СПИСОК ПОТОКОВ
    public function actionIndex()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_products'])) header("Location:/admin");
        $name = $_SESSION['admin_name'];
        
        $flows = System::CheckExtensension('learning_flows', 1);
        if(!$flows) exit('Flows not installed');
        
        $params = json_decode(System::getExtensionSetting('learning_flows'), true);
        
        $flow_list = Flows::getFlows();
        
        $title = 'Потоки - список';
        require_once (ROOT . '/extensions/flows/views/admin/index.php');
        return true;
        
    }
    
    
    // ДОБАВИТЬ ПОТОК
    public function actionAdd()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_products'])) header("Location:/admin");
        $name = $_SESSION['admin_name'];
        $now = time();
        
        $flows = System::CheckExtensension('learning_flows', 1);
        if(!$flows) exit('Flows not installed');
        
        $params = json_decode(System::getExtensionSetting('learning_flows'), true);
        $plane_list = Member::getPlanes();
        $group_list = User::getUserGroups();
        
        if(isset($_POST['add_flow']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $flow_name = htmlentities($_POST['flow_name']);
            $flow_title = !empty($_POST['flow_title']) ? htmlentities($_POST['flow_title']) : $flow_name;
            $status = intval($_POST['status']);
            $limit = intval($_POST['limit']);
            $start_flow = !empty($_POST['start_flow']) ? strtotime($_POST['start_flow']) : $now;
            $end_flow = !empty($_POST['end_flow']) ? strtotime($_POST['end_flow']) : $now + 604800;
            $show_period = intval($_POST['show_period']);
            $is_default = isset($_POST['is_default']) ? intval($_POST['is_default']) : 0;
            
            $public_start = !empty($_POST['public_start']) ? strtotime($_POST['public_start']) : $now;
            $public_end = !empty($_POST['public_end']) ? strtotime($_POST['public_end']) : $now + 604800;
            
            $add_group_arr = isset($_POST['add_groups']) ? json_encode($_POST['add_groups']) : null;
            $del_group_arr = isset($_POST['del_groups']) ? json_encode($_POST['del_groups']) : null;
            $add_plane_arr = isset($_POST['add_planes']) ? json_encode($_POST['add_planes']) : null;
            $letter_arr = json_encode($_POST['letter']);
            
            $products = isset($_POST['products']) ? $_POST['products'] : null;
            $add = Flows::addFlow($flow_name, $flow_title, $status, $start_flow, $end_flow,
                $show_period, $public_start, $public_end, $add_group_arr, $del_group_arr,
                $add_plane_arr, $letter_arr, $products, $limit, $is_default
            );
            if($add) header("Location: /admin/flows/?success");
        }
        
        $title = 'Создать поток';
        require_once (ROOT . '/extensions/flows/views/admin/add.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ ПОТОК
    public function actionEdit($flow_id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['change_products'])) header("Location:/admin");
        $name = $_SESSION['admin_name'];
        $now = time();
        $flow_id = intval($flow_id);
        
        $flows = System::CheckExtensension('learning_flows', 1);
        if(!$flows) exit('Flows not installed');
        
        $params = json_decode(System::getExtensionSetting('learning_flows'), true);
        
        $flow = Flows::getFlowByID($flow_id);
        $flow_products = Flows::getProductsInFlow($flow_id);
        $list_select = Product::getProductListOnlySelect();
        $plane_list = Member::getPlanes();
        $group_list = User::getUserGroups();
        $letter = json_decode($flow['letter'], true);
        
        if(isset($_POST['save_flow']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $flow_name = htmlentities($_POST['flow_name']);
            $flow_title = !empty($_POST['flow_title']) ? htmlentities($_POST['flow_title']) : $flow_name;
            $status = intval($_POST['status']);
            $limit = intval($_POST['limit']);
            $start_flow = !empty($_POST['start_flow']) ? strtotime($_POST['start_flow']) : $now;
            $end_flow = strtotime($_POST['end_flow']);
            $show_period = intval($_POST['show_period']);
            
            $public_start = !empty($_POST['public_start']) ? strtotime($_POST['public_start']) : $now;
            $public_end = strtotime($_POST['public_end']);
            
            $add_group_arr = isset($_POST['add_groups']) ? json_encode($_POST['add_groups']) : null;
            $del_group_arr = isset($_POST['del_groups']) ? json_encode($_POST['del_groups']) : null;
            $add_plane_arr = isset($_POST['add_planes']) ? json_encode($_POST['add_planes']) : null;
            $letter_arr = json_encode($_POST['letter']);
            $is_default = isset($_POST['is_default']) ? intval($_POST['is_default']) : 0;
            
            $edit = Flows::editFlow($flow_id, $flow_name, $flow_title, $status, $start_flow, $end_flow, $show_period, $public_start, $public_end, $add_group_arr, $del_group_arr, $add_plane_arr, 
            $letter_arr, $limit, $is_default);
            if($edit) header("Location: /admin/flows/edit/$flow_id?success");
        }
        
        
        if(isset($_POST['add_product']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $new_product_id = intval($_POST['product_id']);
            $add = Flows::addProductforFlow($flow_id, $new_product_id);
            
            if($add) header("Location: /admin/flows/edit/$flow_id?success");
        }
        
        
        if(isset($_POST['del_product']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $product_id = intval($_POST['product_id']);
            $del = Flows::deleteProductInFlow($flow_id, $product_id);
            if($del) header("Location: /admin/flows/edit/$flow_id?success");
        }
        
        $title = 'Изменить поток';
        require_once (ROOT . '/extensions/flows/views/admin/edit.php');
        return true;
    }
    
    
    
    
    // НАСТРОЙКИ ПОТОКОВ
    public function actionSettings()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_products'])) header("Location:/admin");
        $name = $_SESSION['admin_name'];
        $ext = 'learning_flows';
        
        $params = json_decode(System::getExtensionSetting($ext), true);
        
        $enable = System::getExtensionStatus($ext);
        $title = 'Настройка потоков';
        
        if (isset($_POST['save_flow']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['show_products'])) {
                System::redirectUrl("/admin");
            }

            $status = intval($_POST['status']);
            $params = json_encode($_POST['params']);
            $save = System::SaveExtensionSetting($ext, $params, $status);
            if($save) header("Location: /admin/flowsetting?success");
        }
        
        require_once (ROOT . '/extensions/flows/views/admin/settings.php');
        return true;
    }
    
    
    
    // УДАЛИТЬ ПОТОК И СВЯЗАННЫЕ ЗАПИСИ
    public function actionDel($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['del_products'])) {
            header("Location:/admin/flows?success");
            exit();
        }
        $name = $_SESSION['admin_name'];
		$setting = System::getSetting();
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            $del = Flows::delFlow($id);
            if($del){
                $log = ActionLog::writeLog('flows', 'delete', 'flow', $id, time(), $_SESSION['admin_user'], null);
                header("Location: ".$setting['script_url']."/admin/flows?success");  
            } 
            else header("Location: ".$setting['script_url']."/admin/flows?fail");
        }
    }
    
}