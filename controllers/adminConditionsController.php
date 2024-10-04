<?php defined('BILLINGMASTER') or die; 


class adminConditionsController extends AdminBase {
    
    
    
    // СПИСОК УСЛОВИЙ
    public function actionIndex()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_conditions'])) {
            header("Location: /admin");
        }
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $conditions_list = Conditions::getConditionsList();
        $title='Условия - список';
        require_once (ROOT . '/template/admin/views/conditions/index.php');
        return true;
    }


    /**
     * ДОБАВИТЬ УСЛОВИЕ
     */
    public function actionAdd()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_conditions']))
            System::redirectUrl('/admin');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $cond_actions = isset($_SESSION['conditions']['actions']) ? $_SESSION['conditions']['actions'] : null;

        if (isset($_POST['add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_conditions'])) 
                System::redirectUrl('/admin');

            $create_date = time();
            $name = htmlentities($_POST['name']);
            $status = intval($_POST['status']);
            $desc = htmlentities($_POST['desc']);
            $use_cron = intval($_POST['use_cron']);
            $filter_model = htmlentities($_POST['filter_model']);
            $segment = intval($_POST['segment']);
            $repeat = intval($_POST['repeat']);
            $params = $_POST['params'];
            $time = time();
            $next_action = Conditions::getNextActionDate($time, $params);

            $condition_id = Conditions::addCondition($name, $status, $repeat, $desc, $use_cron, json_encode($params),
                $filter_model, $segment, $cond_actions, $create_date, $next_action
            );

            if ($use_cron == 0 && $condition_id && $cond_actions) { //выполнить сразу
                $condition = Conditions::getConditionData($condition_id);
                if ($condition) {
                    $act = Conditions::renderCond($condition, $use_cron);
                }
            }
            
            if ($condition_id) {
                unset($_SESSION['conditions']['actions']);
                System::setNotif(true);
                System::redirectUrl("/admin/conditions");
            }
        }

        $title='Условие - добавление';
        require_once (ROOT . '/template/admin/views/conditions/add.php');
        return true;
    }


    /**
     * ИЗМЕНИТЬ УСЛОВИЕ
     * @param $id
     */
    public function actionEdit($id) {
        $acl = self::checkAdmin();
        if (!isset($acl['change_conditions'])) 
            System::redirectUrl('/admin');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $condition = Conditions::getConditionData($id);

        if (!$condition) {
            require_once (ROOT . "/template/{$setting['template']}/404.php");
        }

        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $name = htmlentities($_POST['name']);
            $status = intval($_POST['status']);
            $desc = htmlentities($_POST['desc']);
            $use_cron = intval($_POST['use_cron']);
            $filter_model = intval($_POST['filter_model']);
            $segment = intval($_POST['segment']);
            $params = $_POST['params'];
            $repeat = intval($_POST['repeat']);
            $time = time();
            $next_action = -1;
            if ($params['execute_date_type'] != Conditions::EXECUTE_DATE_TYPE_SELECT_DATE || $condition['next_action'] != -1) { // что бы заново не выполнились условия
                $next_action = Conditions::getNextActionDate($time, $params);
            }

            $edit = Conditions::editCondition($id, $name, $status, $repeat, $desc, $use_cron,
                json_encode($params), $filter_model, $segment, $next_action
            );

            if ($use_cron == 0 && $status == 1) { //выполнить сразу
                $act = Conditions::renderCond($condition, $use_cron);
            }
            
            if ($edit) {
                System::setNotif(true);
                System::redirectUrl("/admin/conditions/edit/$id?filter=фильтр&segment=$segment&filter_model=$filter_model");
            }
        }

        $cond_actions = $condition['actions'];
        $title='Условие - изменение';
        require_once (ROOT . '/template/admin/views/conditions/edit.php');
        return true;
    }


    /**
     * УДАЛИТЬ УСЛОВИЕ
     * @param $id
     */
    public function actionDel($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_conditions'])) 
            header("Location: /admin");  

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Conditions::delCondition($id);
            
            System::setNotif($del ? true : false, !$del ?: "Успешно удалено.");
            System::redirectUrl("/admin/conditions");
        }
        return true;
    }


    /**
     *
     */
    public static function actionAddAction() {
        $acl = self::checkAdmin();
        if (!isset($acl['change_conditions'])) {
            exit;
        }

        if ($_POST['condition_id']) {
            $condition = Conditions::getConditionData((int)$_POST['condition_id']);
            $cond_actions = Conditions::getConditionActions((int)$_POST['condition_id']);
        } else {
            $cond_actions = isset($_SESSION['conditions']['actions']) ? $_SESSION['conditions']['actions'] : null;
        }

        $action =  [
            'action' => (int)$_POST['action'],
            'params' => $_POST['params'],
        ];

        if ($_POST['condition_id']) {
            $action['action_id'] = Conditions::addConditionAction((int)$_POST['condition_id'], $action);
            $cond_actions[] = $action;
        } else {
            $cond_actions[] = $action;
            $_SESSION['conditions']['actions'] = $cond_actions;
        }

        $filter_model = (int)$_POST['filter_model'];
        require_once (ROOT . '/template/admin/views/conditions/action_list.php');
        return true;
    }


    /**
     * @param $action_id
     */
    public static function actionEditAction($action_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['change_conditions'])) {
            exit;
        }

        $action_key = isset($_POST['action_key']) ? (int)$_POST['action_key'] : 0;

        if (isset($_POST['save_action'])) {
            $action =  [
                'action' => (int)$_POST['action'],
                'params' => $_POST['params'],
            ];

            $res = true;
            if ($action_id) {
                $res = Conditions::editConditionAction($action_id, $action);

                if ($_POST['repeat_again']) {
                    Conditions::updActionResultStatus($action_id, 0);
                }
            } else {
                $_SESSION['conditions']['actions'][$action_key] = $action;
            }

            exit(json_encode(['status' => $res]));
        }

        if (isset($_POST['get_action_results'])) {
            $get_action_results = Conditions::getCountResults($action_id);
            header("Content-type: application/json; charset=utf-8");
            exit(json_encode(['result' => $get_action_results]));
        }

        $cond_action = $action_id ? Conditions::getConditionAction($action_id) : $_SESSION['conditions']['actions'][$action_key];
        $filter_model = isset($_POST['filter_model']) ? $_POST['filter_model'] : null;

        if (!$filter_model && $action_id) {
            $condition = Conditions::getConditionData($cond_action['condition_id']);
            $filter_model = $condition['filter_model'];
        }

        require_once (ROOT . '/template/admin/views/conditions/edit_action.php');
        return true;
    }


    /**
     *
     */
    public static function actionDelAction() {
        $acl = self::checkAdmin();
        if (!isset($acl['del_conditions'])) {
            exit;
        }

        if ($_POST['action_id']) {
            $res = Conditions::delConditionAction((int)$_POST['action_id']);
            echo json_encode(['status' => $res]);
        } elseif (isset($_POST['action_key']) && isset($_SESSION['conditions']['actions'][$_POST['action_key']])) {
            unset($_SESSION['conditions']['actions'][$_POST['action_key']]);
            echo json_encode(['status' => true]);
        }
    }


    public function actionLog() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_conditions'])) {
            System::redirectUrl('/admin');
        }

        $filter = null;
        if (isset($_GET['filter'])) {
            $filter = [
                'email' => isset($_GET['email']) && $_GET['email'] ? htmlentities($_GET['email']) : null,
                'condition' => isset($_GET['condition']) && $_GET['condition'] ? intval($_GET['condition']) : null,
                'event_type' => isset($_GET['event_type']) && $_GET['event_type'] ? intval($_GET['event_type']) : null,
                'status' => isset($_GET['status']) && $_GET['status'] !== '' ? intval($_GET['status']) : null,
                'start' => isset($_GET['start']) && $_GET['start'] ? strtotime($_GET['start']) : null,
                'finish' => isset($_GET['finish']) && $_GET['finish'] ? strtotime($_GET['finish']) : null,
            ];

            $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;
        }

        $setting = System::getSetting();
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total = Conditions::getTotalResults($filter);
        $pagination = new Pagination($total, $page, $setting['show_items']);
        $log_list = Conditions::getResults($filter, $page, $setting['show_items']);

        require_once (ROOT . '/template/admin/views/conditions/log.php');
        return true;
    }


    public function actionEvent($id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_conditions'])) {
            System::redirectUrl('/admin');
        }

        $event = Conditions::getResult($id);

        if (!$event) {
            $setting = System::getSetting();
            require_once (ROOT . "/template/{$setting['template']}/404.php");
        }

        require_once (ROOT . '/template/admin/views/conditions/event.php');
        return true;
    }


    public function actionSettings() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_conditions'])) {
            System::redirectUrl('/admin');
        }

        $cond_queues = Conditions::getCondQueues();

        require_once (ROOT . '/template/admin/views/conditions/settings.php');
    }


    public function actionDelCondQueues() {
        $result = Conditions::delQueues();

        System::redirectUrl('/admin/conditions/settings/', $result);
    }
}