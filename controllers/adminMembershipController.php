<?php defined('BILLINGMASTER') or die; 


class adminMembershipController extends AdminBase {
    
    
    // Страница планов подписки
    public function actionIndex()
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();
        
        $params = unserialize(Member::getMembershipSetting());
        
        // Список планов подписки
        $planes = Member::getPlanes();
        $title='Мембершип - план подписки';
        require_once (ROOT . '/template/admin/views/membership/index.php');
        return true;
    }


    /**
     * Лог продлений подписок
     */
    public function actionLog()
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();

        $filter = [
            'subs_map_id' => isset($_GET['subs_map_id']) && $_GET['subs_map_id'] ? htmlentities($_GET['subs_map_id']) : null,
            'plane_id' => isset($_GET['plane_id']) && $_GET['plane_id'] ? intval($_GET['plane_id']) : null,
            'user_id' => isset($_GET['user_id']) && $_GET['user_id'] ? intval($_GET['user_id']) : null,
            'start_date' =>  isset($_GET['start_date']) && $_GET['start_date'] ? strtotime($_GET['start_date']) : null,
            'finish_date' => isset($_GET['finish_date']) && $_GET['finish_date'] ? strtotime($_GET['finish_date']) : null,
        ];
        $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;

        $total_items = Member::getTotalMemberLog($filter);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pagination = new Pagination($total_items, $page, $setting['show_items']);

        $params = unserialize(Member::getMembershipSetting());
        $logs = Member::getMemberLog($filter, $page, $setting['show_items']);
        $title='Мембершип - лог продления подписки';
        require_once (ROOT . '/template/admin/views/membership/log.php');
        return true;
    }


    /**
     * СОЗДАТЬ НОВЫЙ ПЛАН ПОДПИСКИ
     */
    public function actionAddsubs()
    {
        System::checkPermission('show_member');

        $params = unserialize(Member::getMembershipSetting());
		$setting = System::getSetting();
        
        if (isset($_POST['add']) && System::checkToken()) {
            System::checkPermission('change_member');

            $add = Member::AddNewPlane($_POST);
            
            if ($add) {
                $log = ActionLog::writeLog('membership', 'add', 'plane', 0, time(),
                    $_SESSION['admin_user'], json_encode($_POST)
                );
                System::setNotif(true, "План подписки `{$_POST['name']}` создан!");
                System::redirectUrl('/admin/membersubs');
            }
            
        }
        $title='Мембершип -  создание плана подписки';
        require_once (ROOT . '/template/admin/views/membership/add_plane.php');
        return true;
    }


    /**
     * ИЗМЕНИТЬ ПЛАН ПОДПИСКИ
     * @param $id
     */
    public function actionEditsubs($id)
    {
        System::checkPermission('show_member');
    
        $id = intval($id);

        $params = unserialize(Member::getMembershipSetting());
		$setting = System::getSetting();
        
        if (isset($_POST['save']) && System::checkToken()) {
            System::checkPermission('change_member');

            $edit = Member::editPlane($id, $_POST);
            if ($edit) {
                $log = ActionLog::writeLog('membership', 'edit', 'plane', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::setNotif(true, "Изменения сохранены!");
                System::redirectUrl("/admin/membersubs/edit/$id");   
            }
        }
        
        // Данные плана подписки
        $plane = Member::getPlaneByID($id);
        $selected = unserialize(base64_decode($plane['select_payments']));
        $related_plane_arr = $plane['related_planes'] ? explode(",", $plane['related_planes']) : null;
        $title='Мембершип - изменить план подписки';
        require_once (ROOT . '/template/admin/views/membership/edit_plane.php');
        return true;
    }
    
    
    
    // Страница уровней доступа
    public function actionLevels()
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();
        $params = unserialize(Member::getMembershipSetting());
        
        $levels = Member::getLevelsList();
        $title='Мембершип - список уровней доступа';
        require_once (ROOT . '/template/admin/views/membership/levels.php');
        return true;
    }
    
    
    
    // СОЗДАТЬ НОВЫЙ УРОВЕНЬ
    public function actionAddlevel()
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();
        $params = unserialize(Member::getMembershipSetting());
        
        if(isset($_POST['add']) && System::checkToken()){
            System::checkPermission('change_member');

            $add = Member::AddNewLevel(htmlentities($_POST['name']), htmlentities($_POST['desc']));
            if($add){
                System::setNotif(true);
                System::redirectUrl("/admin/memberlevels");
            }
            
        }
        $title='Мембершип - создание нового уровня';
        require_once (ROOT . '/template/admin/views/membership/add_level.php');
        return true;
    }
    

    
    
    // УДАИТЬ ПЛАН ПОДПИСКИ
    public function actionDelsubs($id)
    {
        System::checkPermission('del_member');

		$setting = System::getSetting();
        $id = intval($id);
        if(System::checkToken($_GET['token'])){
            $del = Member::DeletePlane($id);
            if($del)
                $log = ActionLog::writeLog('membership', 'delete', 'plane', $id, time(), $_SESSION['admin_user'], 0);
            $name = isset($_GET['name']) 
                ? "`" . $_GET['name'] . "` " 
                : '';

            System::setNotif($del ? true : false, $del 
                ? "План подписки {$name}удален!" 
                : "Невозможно удалить план подписки!"
            );
            System::redirectUrl("/admin/membersubs");
        }
    }


    /**
     * Страница купленных подписок
     */
    public function actionUsers()
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();
        $params = unserialize(Member::getMembershipSetting());

        if (isset($_GET['reset'])) {
            unset($_SESSION['filter_memberusers']);
        }

        $filter = !isset($_POST['filter']) && isset($_SESSION['filter_memberusers']) 
            ? $_SESSION['filter_memberusers'] 
            : [
                'plane' => isset($_POST['filter']) && $_POST['plane'] ? htmlentities($_POST['plane']) : null,
                'email' => isset($_POST['filter']) && $_POST['email'] ? htmlentities($_POST['email']) : null,
                'name' => isset($_POST['filter']) && $_POST['name'] ? htmlentities($_POST['name']) : null,
                'surname' => isset($_POST['filter']) && $_POST['surname'] ? htmlentities($_POST['surname']) : null,
                'status' => isset($_POST['filter']) && $_POST['status'] != '' ? intval($_POST['status']) : null,
                'pay_status' => isset($_POST['filter']) && $_POST['pay_status'] != '' ? intval($_POST['pay_status']) : null,
                'start' => isset($_POST['filter']) && $_POST['start'] ? htmlentities($_POST['start']) : null,
                'start_from' => isset($_POST['filter']) && $_POST['start_from'] ? strtotime($_POST['start_from']) : null,
                'start_to' => isset($_POST['filter']) && $_POST['start_to'] ? strtotime($_POST['start_to']) : null,
                'finish' => isset($_POST['filter']) && $_POST['finish'] ? htmlentities($_POST['finish']) : null,
                'finish_from' => isset($_POST['filter']) && $_POST['finish_from'] ? strtotime($_POST['finish_from']) : null,
                'finish_to' => isset($_POST['filter']) && $_POST['finish_to'] ? strtotime($_POST['finish_to']) : null,
                'canceled' => isset($_POST['filter']) && $_POST['canceled'] ? htmlentities($_POST['canceled']) : null,
                'canceled_from' => isset($_POST['filter']) && $_POST['canceled_from'] ? strtotime($_POST['canceled_from']) : null,
                'canceled_to' => isset($_POST['filter']) && $_POST['canceled_to'] ? strtotime($_POST['canceled_to']) : null,
            ];

        if (isset($_POST['load_csv'], $_SESSION['filter_memberusers'])) {
            $filter['is_filter'] = true;

        } else {
            $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;
            if ($filter['is_filter']) {
                $_SESSION['filter_memberusers'] = $filter;
            }
        }
        

        $time = time();
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total_items = Member::getTotalPlanesWithFilter($filter);

        $is_pagination = !isset($_POST['load_csv']) ? true : false;
        $pagination = new Pagination($total_items, $page, $setting['show_items']);

        $members = Member::getMemberListWithFilter($filter, $page, $setting['show_items'], $is_pagination);

        if (isset($_POST['load_csv']) && $members) {
            $fields = [
                'id', 'subs_id', 'subscription_id',
                'user_id', 'user_name', 'email', 'login',
                'status', 'create_date', 'begin', 'end'
            ];
            $count_fields = count($fields);
            $csv = implode(';', $fields) . PHP_EOL;

            foreach ($members as $key => $member) {
                foreach ($fields as $_key => $field) {
                    $value = $member[$field] && in_array($field, ['create_date', 'begin', 'end']) ? date("d.m.Y H:i:s", $member[$field]) : $member[$field];
                    $csv .= $value . ($_key < $count_fields - 1 ? ';' : '');
                }
                $csv .= PHP_EOL;
            }

            $write = file_put_contents(ROOT . "/tmp/memberusers_$time.csv", $csv);
            if ($write) {
                System::redirectUrl("/tmp/memberusers_$time.csv");
            }
        }
        $title='Мембершип - список участников';
        require_once (ROOT . '/template/admin/views/membership/members.php');
        return true;
    }


    /**
     * ИЗМЕНИТЬ ПОДПИСКУ ЮЗЕРА
     * @param $id
     */
    public function actionEdituser($id)
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();
        $params = unserialize(Member::getMembershipSetting());

        $member = Member::getMemberRow($id);

        if (!$member) {
            require_once (ROOT . '/template/'.$setting['template'].'/404.php');
        }

        if (isset($_GET['reset-counter-notifications'])) {
            $res = Member::updateNotifFromMap($id, 0);
            System::redirectUrl("/admin/memberusers/edit/$id", $res);
        }

        $planes = Member::getPlanes();
        $time = time();

        if (isset($_POST['edit']) && System::checkToken()) {
            $subscription_id = htmlentities($_POST['subscription_id']);
            $end = strtotime($_POST['end']);
            $plane_id = isset($_POST['plane_id']) ? intval($_POST['plane_id']) : 0;
            
            $status = intval($_POST['status']);
			$lc_id = isset($_POST['lc_id']) ? intval($_POST['lc_id']) : 0;
            $recurrent_cancelled = isset($_POST['recurrent_cancelled']) ? intval($_POST['recurrent_cancelled']) : null;

            $edit = Member::editUserSubscript($id, $subscription_id, $end, $plane_id, $status, $recurrent_cancelled, $lc_id);
            if ($edit) {
                $log = ActionLog::writeLog('membership', 'edit', 'member', $id, $time, $_SESSION['admin_user'], json_encode($_POST));
                System::setNotif(true);
                System::redirectUrl("/admin/memberusers/edit/$id");
            }
        }
        $title='Мембершип - изменение подписки пользователя';
        require_once (ROOT . '/template/admin/views/membership/edit_member.php');
        return true;
    }
    
    
    // ДОБАВИТЬ УЧАСТНИКА
    public static function actionAddmember()
    {
        System::checkPermission('show_member');

		$setting = System::getSetting();
        $params = unserialize(Member::getMembershipSetting());
        
        
        if(isset($_POST['add']) && System::checkToken()){
            System::checkPermission('change_member');
            
            $add = Member::renderMember(intval($_POST['plane']), intval($_POST['user_id']));

            if($add)
                $log = ActionLog::writeLog('membership', 'add', 'member', 0, time(), $_SESSION['admin_user'], json_encode($_POST));

            System::setNotif($add ? true : false, $add 
                ? "Участник добавлен!" 
                : "Невозможно добавить участника!"
            );
            System::redirectUrl("/admin/memberusers");
            
        }
        $title='Мембершип - добавить новую подписку';
        require_once (ROOT . '/template/admin/views/membership/add_member.php');
        return true;
    }
    
    
    // УДАЛИТЬ УЧАСТНИКА
    public function actionDelmember($id)
    {
        System::checkPermission('del_member');

		$setting = System::getSetting();
        $id = intval($id);
        if(System::checkToken($_GET['token'])){
            
            if(isset($_GET['action'])){
                
                if($_GET['action'] == 'delete') $act = Member::delMember($id);
                if($_GET['action'] == 'pause') $act = Member::pauseMember($id, 0);
                if($_GET['action'] == 'play') $act = Member::pauseMember($id, 1);
                
                
                if($act)
                    $log = ActionLog::writeLog('membership', 'delete', 'member', $id, time(), $_SESSION['admin_user'], 0);

                System::setNotif($act ? true : false, $act 
                    ? "Участник удален!" 
                    : "Невозможно удалить участника!"
                );
                System::redirectUrl("/admin/memberusers");  
            }
        }
    }
    
    
    
    // НАСТРОЙКИ 
    public function actionSettings()
    {
        System::checkPermission('show_member');

        $setting = System::getSetting();
        
        if(isset($_POST['savemember']) && System::checkToken()){
            System::checkPermission('change_member');

            $params = serialize($_POST['member']);
            $status = intval($_POST['status']);
            
            $save = Member::SaveBlogSetting($params, $status);
            System::setNotif($save ? true : false);
        }
        
        $params = unserialize(Member::getMembershipSetting());
        $enable = Member::getMemberShipStatus();
        $title='Мембершип - настройки';
        require_once (ROOT . '/template/admin/views/membership/setting.php');
        return true;
    }
    
    // ЭКСПОРТ УЧАСТНИКОВ
    public function actionExport() {

        if (isset($_POST['export']) && System::checkToken()) {
            System::checkPermission('show_member');

            $setting = System::getSetting();
            $params = unserialize(Member::getMembershipSetting());

            $members = Member::getMemberList();
            if ($members) {
                $str = 'id,user_name,email,phone,subscription_id,subs_id,create_date,end'.PHP_EOL;
                foreach ($members as $member) {
                    $row = array(
                        'id' => $member['id'],
                        'user_name' => $member['user_name'],
                        'email' => $member['email'],
                        'phone' => $member['phone'],
                        'subscription_id' => $member['subscription_id'],
                        'subs_id' => $member['subs_id'],
                        'create_date' => date("d.m.Y H:i", $member['create_date']),
                        'end' => date("d.m.Y H:i", $member['end']),
                    );

                    $str.= implode(',', array_values($row)).';'.PHP_EOL;
                }

                $csv = '/tmp/users_' . time() . '.csv';
                $write = file_put_contents(ROOT.$csv, $str);
                if ($write) {
                    header("Location: $csv");
                } else {
                    echo 'Ошибка выборки участников';
                }
            }
        }
        $title='Мембершип - экспорт участников';
        require_once (ROOT . '/template/admin/views/membership/export.php');
        return true;
    }

    public function actionImportPlanes() {
        //TODO: сделать форму
        if (isset($_POST['import'])) {

            $filePath = $_FILES['file']['tmp_name'];
            $separator = $_POST['separator'] ?? ';';
            $sendEmail = $_POST['sendEmail'];
            $expireType = $_POST['expireType'];//0 - абсолютный; 1 - относительный(в днях)
            $is_partner = isset($_POST['is_partner']) ? intval($_POST['is_partner']) : 0;
            $is_client = 1;
            $is_subs = 0;

            $fields = [
                1 => $_POST['first_field'] ?? 'name',
                2 => $_POST['second_field'] ?? 'email',
                3 => $_POST['third_field'] ?? 'subsId',
                4 => $_POST['fourth_field'] ?? 'expire',
            ];

            if (!is_file($filePath)) {
                $error_msg[] = "Файл поврежден";
            }
            if ($_FILES['file']['type'] != ('text/csv' || 'text/plain')) {
                $error_msg[] = "Неправильный формат файла";
            }

            if (!isset($error_msg)) {
                $fileData = file($filePath);//открываем файл
                //получаем поля в файле
                $tableFields = array_shift($fileData);//Удаляем первую строку
/*
                $tableFields = explode($separator, $tableFields);

                foreach ($tableFields as $key => $field) {
                    $tableFields[$key] = trim($field);
                }*/

                $tableFields = $fields;
                //$tableFields - массив ключей

                //Создаем из файла массив, более удобный для работы
                $data = [];
                foreach ($fileData as $key => $fileField) {
                    $tempArr = explode(';', $fileField);
                    $i = 0;
                    foreach ($tableFields as $field) {
                        $data[$key][$field] = trim($tempArr[$i]);
                        $i++;
                    }
                }

                //Проверка данных
                $errors = [];//ошибки
                $success = [];
                $time = time();

                $countAll = count($data);

                foreach ($data as $key => $line) {
                    //проверка данных
                    //На существование плана
                    /*var_dump($line);*/
                    if (empty($line['email'])) {
                        $errors[] = "email пустой";
                        continue;
                    }

                    if (empty($line['subsId'])) {
                        $errors[] = "id подписки пустой";
                        continue;
                    }

                    $plane = Member::getPlaneByID(intval($line['subsId']));
                    if (!$plane) {
                        $errors[] = "План " . $line['subsId'] . " не существует";
                        continue;
                    }

                    //Юзера с емайл
                    $issetUser = User::searchUser($line['email']);
                    if ($issetUser) {
                        $line['userExists'] = true;
                        $errors[] = "Пользователь с почтой ".$line['email']." уже существует. Ему добавлена подписка";
                    }

                    //Дата окончания
                    if (empty($line['expire'])) {
                        $lifetime = $plane['lifetime'] == 0 ? 1 : $plane['lifetime'];
                        switch ($plane['period_type']) {
                            case 'Day':
                                $line['expire'] = time() + 60 * 60 * 24 * $lifetime;
                                break;
                            case 'Week':
                                $line['expire'] = time() + 60 * 60 * 24 * 7 * $lifetime;
                                break;
                            default: //Month and others
                                $line['expire'] = time() + 60 * 60 * 24 * 30 * $lifetime;
                                break;
                        }
                    } else {
                        if ($expireType == 0) {
                            if ($line['expire'] < time()) {
                                $line['expire'] = time() + 60*60*24*30;
                            }
                        }
                        if ($expireType == 1) {
                            if (strlen($line['expire']) >= 8) {
                                $errors[] = "Не верный тип даты окончания подписки у пользователя ".$line['email'];
                                continue;
                            }
                            $line['expire'] = time() + 60*60*24*$line['expire'];
                        }
                    }
                    $success[] = $line;
                }

                $serverErrors = [];//массив с ошибочками(не удачная вставка бд и тд)
                $successCount = 0;


                $createdUsers = [];

                foreach ($success as $import) {
                    //импорт юзера
                    $name = $import['name'] ?? explode("@", $import['email'])[0];
                    $name = utf8_encode($name);
                    $password = System::generateStr(12);
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    if ((isset($import['userExists']) && $import['userExists'] == true) || array_key_exists($import['email'], $createdUsers)) {
                        $user = User::getUserDataByEmail($import['email']);
                    } else {
                        $user = User::AddNewClient($name, $import['email'], '', '', '', '', 'user', $is_client, $time, 'custom', '', '1', $hash, $password, $sendEmail, System::getSetting(true)['register_letter'], $is_subs);
                        $createdUsers[$import['email']] = true;
                    }

                    if (!$user) {
                        $serverErrors[] = ['add_user', 'email' => $import['email']];
                        continue;
                    }

                    //импорт подписки
                    $sub = Member::addUserSubscribe($import['subsId'], $user['user_id'], '1', $time, $import['expire'], null);
                    if (!$sub) {
                        $serverErrors[] = ['add_subs', 'email' => $import['email'], 'plane_id' => $import['plane_id'], 'user_id' => $user];
                        continue;
                    }

                    $successCount++;
                }

            } else {
                System::redirectUrl('/admin/memberusers/import', false);
            }
        }

        $title = 'Мембершип - добавить новую подписку';
        require_once (ROOT . '/template/admin/views/membership/import.php');
        return true;
    }
}