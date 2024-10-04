<?php defined('BILLINGMASTER') or die;

class adminUsersController extends AdminBase {
    
    
    // Список юзеров
    public function actionIndex()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_users'])) 
            System::redirectUrl("/admin");

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $is_pagination = !isset($_GET['role']) && !isset($_POST['load_csv']) ? true : false;
        $role = isset($_GET['role']) ? htmlentities($_GET['role']) : 0;

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = !isset($_POST['load_csv']) ? 3000 : 300000;

        $conditions = isset($_GET['filter']) ? UserFilter::getConditions($_GET) : null;
        $total_users = $conditions ? User::countUsersWithConditions($conditions) : User::countUsers();

        if ($is_pagination) {
            $pagination = new Pagination($total_users, $page, $setting['show_items']);
            $limit = $setting['show_items'];
        }

        if ($conditions) {
            $users = User::getUsersWithConditions($conditions, $page, $setting['show_items'], $is_pagination);
        } else {
            $users = User::getUserListForAdmin($role, $page, $limit);
        }

        if (isset($_POST['load_csv']) && $users) {
            $time = time();
            $csv = User::getCsv($users, ';');
            $write = file_put_contents(ROOT."/tmp/users_{$time}.csv", $csv);
            if ($write) {
                System::redirectUrl("/tmp/users_{$time}.csv");
            }
        }

        $title = 'Пользователи - список';
        require_once (ROOT . '/template/admin/views/users/index.php');
        return true;
    }
    
    
    
    
    // ИМПОРТ ЮЗЕРОВ
    public function actionImport()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_users'])) 
            System::redirectUrl('/admin');

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $time = time();

        if ((!isset($_POST['import']) || $_POST['is_new']) && isset($_SESSION['import_data'])) {
            unset($_SESSION['import_data']);
        }

        if (System::CheckExtensension('autobackup', 1)) {//Todo: умный бекап
            SmartBackup::backup()->setType(SmartBackup::Type_BeforeImport)->runIfNeed()->redirect("/admin/users/import/");
        }

        if (isset($_POST['import']) && isset($_FILES['file'])&& $_FILES['file']['size'] != 0) {

            if (!isset($_POST['token']) || $_POST['token'] != $_SESSION['admin_token'] || !isset($acl['export_users'])) {
                exit(json_encode(['redirect' => '/admin']));
            }

            if (!isset($_SESSION['import_data'])) {
                $_SESSION['import_data'] = [];
                exit(json_encode(['show_progress_bar' => true]));
            }

            if (ini_get("max_execution_time") < 180) {
                ini_set("max_execution_time", 180); //увеличить время скрипта
            }

            $field_1 = htmlentities($_POST['first_field']);
            $field_2 = htmlentities($_POST['second_field']);
            $field_3 = htmlentities($_POST['third_field']);
            $field_4 = htmlentities($_POST['fourth_field']);
            $field_5 = htmlentities($_POST['five_field']);
            $field_6 = htmlentities($_POST['six_field']);
            $field_7 = htmlentities($_POST['seven_field']);
            
            $separator = htmlentities($_POST['separator']);
            $send_letter = intval($_POST['send_letter']);
            $letter = $_POST['letter'];
            $empty_name = htmlentities($_POST['empty_name']);
            $responder = intval($_POST['responder']);
            $validate = intval($_POST['validate']);
            $is_client = isset($_POST['is_client']) ? intval($_POST['is_client']) : 0;
            $is_partner = isset($_POST['is_partner']) ? intval($_POST['is_partner']) : 0;
            $is_subs = isset($_POST['is_subs']) ? intval($_POST['is_subs']) : 0;
            $groups = isset($_POST['groups']) ? $_POST['groups'] : false;
            
            $email = $phone = $user_name = $surname = $city = null;
            $lines = array();

            $file_path = isset($_SESSION['import_data']['file_path']) ? $_SESSION['import_data']['file_path'] : null;
            if (!$file_path && isset($_FILES['file'])) {
                $tmp_name = $_FILES["file"]["tmp_name"]; // Временное имя файла на сервере
                $pathinfo = pathinfo($_FILES["file"]["name"]);
                $file_name = time() . ".{$pathinfo['filename']}.{$pathinfo['extension']}"; // Имя файла для импорта
                $file_path = ROOT . "/tmp/$file_name"; // Путь для сохранения

                if (is_uploaded_file($tmp_name)) {
                    $content = file_get_contents($tmp_name);
                    if (!mb_check_encoding($content, 'UTF-8')) {
                        if (mb_check_encoding($content, 'cp1251')) {
                            $content = iconv('cp1251', 'UTF-8', $content);
                        } else {
                            unset($_SESSION['import_data']);
                            System::setNotif(false, "Ни один пользователь не добавлен");
                            exit(json_encode(['redirect' => '/admin/users/import?fail']));
                        }
                    }

                    file_put_contents($file_path, $content);
                }
            }

            $wrong_email = isset($_SESSION['import_data']['wrong']) ? $_SESSION['import_data']['wrong'] : 0;
            $success = isset($_SESSION['import_data']['success']) ? $_SESSION['import_data']['success'] : 0;
            $emails = isset($_SESSION['import_emails']['emails']) ? $_SESSION['import_emails'] : array();
            $dupl_emails = isset($_SESSION['import_data']['dupl']) ? $_SESSION['import_data']['dupl'] : 0;

            $file = file($file_path);
            $filesize = sizeof($file);
            $max_users = $filesize >= 500 ? 50 : 25;
            $start = isset($_SESSION['import_data']['finish']) ? $_SESSION['import_data']['finish'] : 0;
            $finish = ($start + $max_users) < $filesize ? $start + $max_users : $filesize;
            $progress = 0;
            $wrongplane = 0;
            $expireerrors = 0;
            $successsub = 0;
            $expireType = 1;

            for ($str = $start; $str < $finish; $str++) {

                $line = $file[$str];
                if (empty($line)) {
                    break;
                }
                
                if ($field_2 == 'none') {
                    $lines[0] = trim($line);
                } else {
                    $lines = explode($separator, $line);
                }

                //если это первая строка базы с названием полей -> дальше (надо доработать #todo)
                if($lines[0] == 'id' && $lines[1] == 'name' && $lines[2] == 'surname' && $lines[3] == 'email' && $lines[4] == 'phone')
                    continue;

                for ($i = 0; $i < 7; $i++) {
                    if (isset($lines[$i])) {
                        $field_name = 'field_'.($i+1);

                        switch($$field_name){
                            case 'email':
                                $email = trim($lines[$i]);
                                break;
                            case 'name':
                                $user_name = trim(trim($lines[$i]), '"');
                                break;
                            case 'phone':
                                $phone = trim($lines[$i]);
                                break;
                            case 'surname':
                                $surname = trim($lines[$i]);
                                break;
                            case 'city':
                                $city = trim($lines[$i]);
                                break;
                            case 'planeid':
                                $planeid = trim($lines[$i]);
                                break;
                            case 'subexpire':
                                $subexpire = trim($lines[$i]);
                                break;
                        }
                    }
                }

                if (empty($email)) {
                    $wrong_email++;
                } else {
                    $subs_key = md5($email . $time);
                    $user_param = "$time;0;0;";

                    if (!in_array($email, $emails)) {
                        $add = User::importUsers($user_name, $email, $phone, $send_letter, $subs_key, $user_param, $setting, $empty_name, $letter, $responder, $time, $groups, $validate, $is_client, $surname, $is_subs, $city);
                        $user = $add;
                    } else {
                        $user = User::getUserDataByEmail($email);
                        $dupl_emails++;
                    }
                    $emails[] = $email;

                    if (isset($add) && $add) {
                        $success++;
                    }
                    if (isset($user)) {
                        if ($is_partner) {
                            $act = Aff::AddUserToPartner($user['user_id'], 0);
                        }
                    } else {
                        $wrong_email++;
                        continue;
                    }

                    if (isset($planeid) && $user) {
                        $plane = Member::getPlaneByID(intval($planeid));
                        if (!$plane) {
                            $wrongplane++;
                            continue;
                        }

                        //Дата окончания
                        if (empty($subexpire)) {
                            $lifetime = $plane['lifetime'] == 0 ? 1 : $plane['lifetime'];
                            switch ($plane['period_type']) {
                                case 'Day':
                                    $subexpire = time() + 60 * 60 * 24 * $lifetime;
                                    break;
                                case 'Week':
                                    $subexpire = time() + 60 * 60 * 24 * 7 * $lifetime;
                                    break;
                                default: //Month and others
                                    $subexpire = time() + 60 * 60 * 24 * 30 * $lifetime;
                                    break;
                            }
                        } else {
                            if ($expireType == 0) {
                                if ($subexpire < time()) {
                                    $subexpire = time() + 60 * 60 * 24 * 30;
                                }
                            }
                            if ($expireType == 1) {
                                if (strlen($subexpire) >= 8) {
                                    $expireerrors++;
                                    continue;
                                }
                                $subexpire = time() + 60 * 60 * 24 * $subexpire;
                            }
                        }

                        if (isset($subexpire)) {
                            $sub = Member::addUserSubscribe($planeid, $user['user_id'], '1', $time, $subexpire, null);
                            if ($sub) {
                                $successsub++;
                            }
                            unset($subexpire);
                        }

                    }
                }

                $progress = intval(($wrong_email + $success) / $filesize * 100);
            }

            $import_data = [
                'finish' => $finish,
                'success' => $success,
                'wrong' => $wrong_email,
                'dupl' => $dupl_emails,
                'total' => $filesize,
                'is_finish' => false,
                'progress' => $progress,
                'file_path' => $file_path,
                'redirect' => '',
                'wrongplane' => $wrongplane ?? 0,
                'expireerrors' => $expireerrors ?? 0,
                'successsub'=> $successsub ?? 0,
                'show_progress_bar' => false,
            ];

            if ($finish == $filesize) {
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                $import_data['is_finish'] = true;
                unset($_SESSION['import_data']);
            } else {
                $_SESSION['import_data'] = $import_data;
            }

            exit(json_encode($import_data));
        }
        $title = 'Пользователи - импорт';
        require_once (ROOT . '/template/admin/views/users/import.php');
        return true;
    }
    
    
    
    // ЭКСПОРТ ЮЗЕРОВ
    public function actionExport()
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['export']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['export_users'])) {
                System::redirectUrl('/admin/users?fail');
            }

            if ($_POST['type'] == 'all') { // экспорт всех пользователей
                $users = User::getAllUsers();
            } elseif($_POST['type'] == 'custom' && !empty($_POST['user_groups'])) { // экспорт по группам
                $user_groups = implode(",", $_POST['user_groups']);
                $users = User::getAllUsers($user_groups);
            } elseif($_POST['type'] == 'without') {
                $users = User::getAllUsers(false);
            } else {
                $users = null;
            }
            
            $sep = htmlentities(trim($_POST['separator']));
            $time = time();

            if ($users) {
                $csv = User::getCsv($users, $sep);
                $write = file_put_contents(ROOT.'/tmp/users_'.$time.'.csv', $csv);
                if ($write) {
                    $log = ActionLog::writeLog('users', 'export', 'users', 0, $time, $_SESSION['admin_user'], json_encode($_POST));
                    header("Location: ".$setting['script_url'].'/tmp/users_'.$time.'.csv');
                } else {
                    echo 'Ошибка выборки пользователей';
                }
            }
        }
        $title = 'Пользователи - экспорт';
        require_once (ROOT . '/template/admin/views/users/export.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ ЮЗЕРА
    public function actionEdit($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_users'])) {
            header("Location: /admin");
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
		$user_cerificates = false;
		
		if (isset($_POST['make_partner'])) {
            $act = Aff::AddUserToPartner($id, 0);
		} //else $act = Aff::AuthorAction($id, 0);

        $custom_fields = CustomFields::getFields();

        if (isset($_POST['user_enter']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            unset($_SESSION['user']);
            unset($_SESSION['name']);
            
            $_SESSION['user'] = $_POST['user_id'];
            $_SESSION['name'] = $_POST['user_name'];
            //$auth = User::Auth($_POST['user_id'], $_POST['user_name']);
            header("Location: /lk");
        }
        
        
        // Отправка письма юзеру
        if (isset($_POST['send']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $subject = htmlentities($_POST['subject']);
            $email = htmlentities($_POST['email']);
            $name = null;
            $letter = $_POST['letter'];
            $sender_name = htmlentities($_POST['sender_name']);

            $addit_data = !isset($_POST['addit_data']) || empty($_POST['addit_data']) ? [] : $_POST['addit_data'];
            
            $send = Email::SendMessageToBlank($email, $name, $subject, $letter, $sender_name, false, false, $addit_data);
            if($send) {
                header("Location: /admin/users/edit/$id?success");
            }
        }


        // Спец режим партнёрки
        if (isset($_POST['add_spec_aff']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $spec_aff_params = $_POST['specaff_params'];
            $upd = User::AddProductSpecAff($id, $spec_aff_params);
            if ($upd) {
                header("Location: /admin/users/edit/$id?success");
            }
        }
        
        
        // Изменение продукта в спец.режиме
        if (isset($_POST['spec_id']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $item_id = intval($_POST['spec_id']);
            
            if (isset($_POST['save_spec'])) {
                $spec_aff_params = $_POST['specaff_params'];
                
                $upd = User::updateSpecUser($item_id, $spec_aff_params);
                if ($upd) {
                    header("Location: /admin/users/edit/$id?success");
                }
            }
            
            if (isset($_POST['del_spec'])) {
                $del = User::deleteSpecAff($item_id);
                if($del) {
                    header("Location: /admin/users/edit/$id?success");
                }
            }
        }
        
        if(isset($_POST['changecurator']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $new_curator = intval($_POST['newcurator']);
            $curator = intval($_POST['curator_id']);
            $section = intval($_POST['section_id']);
            $training = intval($_POST['training_id']);
            $user_id = intval($_POST['user_id']);
            $ChangeOK = Training::setNewCuratorToUser($user_id, $training, $section, $curator, $new_curator);
            if($ChangeOK){
                header("Location: /admin/users/edit/$id?success");
                exit();
            }
        }

        if(isset($_POST['deletecurator']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $new_curator = intval($_POST['newcurator']);
            $curator = intval($_POST['curator_id']);
            $section = intval($_POST['section_id']);
            $training = intval($_POST['training_id']);
            $user_id = intval($_POST['user_id']);
            $ChangeOK = Training::setNewCuratorToUser($user_id, $training, $section, $curator, $new_curator, true);
            if($ChangeOK){
                header("Location: /admin/users/edit/$id?success");
                exit();
            }
        }
        
        
        if(isset($_POST['reload_map_item']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $start = strtotime($_POST['start']);
            $end_date = strtotime($_POST['end_date']);
            $map_id = intval($_POST['reload_map_item']);
            $status = intval($_POST['status']);
            
            $upd = Flows::updateUserMap($map_id, $start, $end_date, $status);
            if($upd) header("Location: /admin/users/edit/$id?success");
        }
        
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {

            $add_GET = '';

            if(!isset($acl['change_users'])){
                header("Location: /admin/users?fail");
                exit();
            }

            $name = htmlentities($_POST['name']);
            $surname = isset($_POST['surname']) ? htmlentities($_POST['surname']) : null;
            $patronymic = isset($_POST['patronymic']) ? htmlentities($_POST['patronymic']) : null;
            $old_email = isset($_POST['old_email']) ? htmlentities($_POST['old_email']) : null;
            $email = htmlentities($_POST['email']);
            $phone = htmlentities($_POST['phone']);
            $city = htmlentities($_POST['city']);
            $zipcode = htmlentities($_POST['zipcode']);
            $address = htmlentities($_POST['address']);
			$login = htmlentities($_POST['login']);
			$role = htmlentities($_POST['role']);
            $level = isset($_POST['level']) ? intval($_POST['level']) : 0;

            if($old_email != null or $old_email = User::getUserNameByID($id)['email']){

                if ($old_email != $email && User::searchUser($email)){
                    $email = $old_email;
                    $add_GET .= '&dublemail';
                } else {
                    if ($old_email != $email) {
                        //Обновить данные, где был старый email на новый
                        $emailUpdateNote = User::searchAndReplaceUserEmailInOrdersInstallments($old_email, $email);
                    }
                }
            }

            
            $sex = htmlentities($_POST['sex']);
            $nick_telegram = htmlentities($_POST['nick_telegram']);
            $nick_instagram = htmlentities($_POST['nick_instagram']);

            if (System::CheckExtensension('autopilot', 1)) { // расширение autopilot
                $vk_url = Autopilot::prepareVkUrl($_POST['vk_url']);
                $vk_url = htmlentities($vk_url);
            } else {
                $vk_url = htmlentities($_POST['vk_url']);
            }

            $is_partner = isset($_POST['is_partner']) ? $_POST['is_partner'] : 0;
            
			$partnership = System::CheckExtensension('partnership', 1);
			if ($partnership) {
                if (isset($_POST['custom_comiss'])) {
                    $upd = Aff::updateCustomComiss($id, intval($_POST['custom_comiss']));
                }
                
                if (isset($_POST['is_author'])) {
                    $act = Aff::AuthorAction($id, 1);
                } else {
                    $act = Aff::AuthorAction($id, 0);
                }
            }
			
 
            $act = isset($_POST['is_curator']) ? Course::AddIsCurator($id, 1) : Course::AddIsCurator($id, 0);
			
            $is_subs = isset($_POST['is_subsc']) ? $_POST['is_subsc'] : 0;
            $groups = isset($_POST['groups']['ids']) ? $_POST['groups']['ids'] : false;
            $groups_dates = isset($_POST['groups']['dates']) ? $_POST['groups']['dates'] : false;
            $curators = isset($_POST['curators']) ? $_POST['curators'] : false;

            $note = $emailUpdateNote ?? htmlentities($_POST['note']);
            $status = htmlentities($_POST['status']);
            
            if($_SESSION['admin_user'] == $id) $status = 1;
            
            $pass = !empty($_POST['pass']) ? $_POST['pass'] : '';
            $spec_aff = isset($_POST['spec_aff']) ? intval($_POST['spec_aff']) : 0;


            $edit = User::editUser($id, $name, $email, $phone, $city, $zipcode, $address, $note, $status,
                $pass, $groups, $groups_dates, $is_partner, $is_subs, $role, $login, $surname, $patronymic,
                $sex, $nick_telegram, $nick_instagram, $level, $vk_url, $spec_aff, $curators
            );
            
            if ($edit) {
                if ($custom_fields) {
                    $custom_fields_data = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];
                    CustomFields::saveUserFields($id, null, $custom_fields_data, null);
                }
                $log = ActionLog::writeLog('users', 'edit', 'user', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                header("Location: /admin/users/edit/$id?success" . $add_GET);
            }
        }
        
        if (isset($_POST['blacklist']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            if(!isset($acl['change_users'])){
                header("Location: /admin");
                exit();
            }
            
            $email = htmlentities($_POST['email']);
            $act = $_POST['act'] == 'add' ? User::addBlackList($email, 1) : $act = User::addBlackList($email, 0); // добавить/удалить из BL;
            
            if ($act) {
                header("Location: ".$setting['script_url']."/admin/users/edit/$id?success");
            }
        }
        
        $blog = System::CheckExtensension('blog', 1);
        if ($blog) {
            $segment_list = Blog::getUserSegments($id);
        }
        
        $responder = System::CheckExtensension('responder', 1);
        $flows = System::CheckExtensension('learning_flows', 1);
        $en_courses = System::CheckExtensension('courses', 1);
        $user = User::getUserDataForAdmin($id);
        if (!$user) {
            ErrorPage::return404();
        }

        if ($user['is_partner'] == 1) {
            $partner = Aff::getPartnerReq($user['user_id']);
        }
        
        $orders = Order::getUserOrders($user['email']);
        $uniq_courses = Course::getUniqCourseInUserMap($id); // список ID тех курсов, которые просмотрены юзером
        $user_groups = User::getGroupByUser($id);
        $user_planes = Member::getAllPlanesByUser($id);
        $all_summ = 0;
        $en_training = System::CheckExtensension('training', 1);
        $uniq_trainings = null;
        if($en_training){
            $user_curators = Training::getAllCuratorsToUser($id);
            $uniq_trainings = Training::getTrainingFromUserMap($id);
            $user_cerificates = Training::getCertificates2User($id);
        }    
        
        //$log_letters = Email::getLog($page = 1, $show_items = null, $pagination = false, $user['email'], $start = false, $finish = false, $subject = false, $filter = false);
        
		$log_letters = Email::getLogByUser($user['email']);
		
		$aff_params = User::getProductsForSpecAff($id);

        $title = 'Пользователи - изменение';
        require_once (ROOT . '/template/admin/views/users/edit.php');
        return true;
    }
    
    
    
    
    // СОЗДАТЬ ЮЗЕРА
    public function actionCreate()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['change_users'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['create']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_users'])){
                header("Location: /admin");
                exit();
            }
    
            $email = htmlentities(trim(strtolower(mb_substr($_POST['email'], 0, 50))));
            
            if ($add = User::searchUser($email)) {
                $error_msg = 'Пользователь с таким E-mail уже существует!';

            } else {
                $name = htmlentities(mb_substr($_POST['name'], 0, 255));
                $surname = isset($_POST['surname']) ? htmlentities($_POST['surname']) : null;
                $phone = htmlentities(mb_substr($_POST['phone'],0,25));
                $city = htmlentities(mb_substr($_POST['city'],0,50));
                $login = htmlentities($_POST['login']);
    
                $index = htmlspecialchars(mb_substr($_POST['zipcode'],0, 8));
                $address = htmlentities(mb_substr($_POST['address'],0,255));
                $status = intval($_POST['status']);
                $enter_method = htmlentities($_POST['method']);
    
                $role = htmlentities($_POST['role']);
                $is_client = intval($_POST['is_client']);
                $date = time();
                $param = $date.';admin;0;admin';
                $send_login = intval($_POST['send_login']);
    
                if (isset($_POST['groups'])) {
                    $groups = $_POST['groups'];
                }
    
                if (empty($_POST['pass'])){
                    // Создаём пароль клиенту
                    $pass_data = System::createPass(8);
                    $password = $pass_data['pass'];
                    $hash = $pass_data['hash'];
                } else {
                    $password = htmlentities($_POST['pass']);
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $add = User::AddNewClient($name, $email, $phone, $city, $address, $index, $role, $is_client,
                    $date, $enter_method, $param, $status, $hash, $password, $send_login, $setting['register_letter'],
                    0, $login, null, $surname
                );
                
                // Добавление групп для пользователя
                if(isset($_POST['groups'])){
                    $groups = $_POST['groups'];
                    foreach($groups as $group){
                        User::WriteUserGroup($add['user_id'], $group);
                    }
                }
    
                if($add) {
                    $log = ActionLog::writeLog('users', 'add', 'user', 0, time(), $_SESSION['admin_user'], json_encode($_POST));
                    header("Location: ".$setting['script_url']."/admin/users/edit/{$add['user_id']}?success");
                }
            }
        }
        $title = 'Пользователи - добавление';
        require_once (ROOT . '/template/admin/views/users/create.php');
        return true;
    }
    
    
    public function actionDelete($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['del_users'])) {
            header("Location: /admin/users");
            exit();
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            
            $user = User::getUserById($id);
            if(!$user) exit('User not found');
            
            try {
                $delmembertrue = Member::delMemberByIDUser($id);
            } catch (Exception $e) {
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
            }
            
            $del_task = Responder::deleteTaskFromUserEmail($user['email']);
            $del_subs = Responder::DeleteSubsRow($user['email'], false);
            $del = User::deleteUser($id);
            if($del&&$delmembertrue){
                $log = ActionLog::writeLog('users', 'delete', 'user', $id, time(), $_SESSION['admin_user'], 0);
                header("Location: ".$setting['script_url']."/admin/users?success");   
            }
        }
    }
    
    
    
    /*   ГРУППЫ   */
    
    
    public function actionGroup()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_users'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $groups = User::getUserGroups();
        $title = 'Пользователи - группы';
        require_once (ROOT . '/template/admin/views/users/groups.php');
        return true;
    }
    
    
    
    public function actionAddgroup()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_users'])) {
            header("Location: /admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();


        if (isset($_POST['save']) && !empty($_POST['title']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if(!isset($acl['change_users'])){
                header("Location: /admin/users");
                exit;
            }

            $title = $_POST['title'];
            $name = System::Translit($title);
            $uniquebool = User::checkUniqueGroupName($name);
            if (!$uniquebool) {
                $name .= '_'.(User::getLastCreatedGroupID()+1);
            }

            $desc = $_POST['desc'];
            $del_tg_chats = isset($_POST['del_tg_chats']) ? $_POST['del_tg_chats'] : null;

            $add = User::AddNewUserGroup($name, $title, $desc, $del_tg_chats);
            if ($add) {
                header("Location: /admin/usergroups?success");
                exit;
            }
        }
        $title = 'Пользователи - добавить  группу';
        require_once (ROOT . '/template/admin/views/users/addgroup.php');
        return true;
    }
    
    
    
    public function actionEditgroup($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_users'])) {
            header("Location: /admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if(!isset($acl['change_users'])){
                header("Location: /admin/users");
                exit;
            }

            $name = $_POST['name'];
            $uniquebool = User::checkUniqueGroupName($name, $id);
            if (!$uniquebool) {
                $name .= '_'.(User::getLastCreatedGroupID()+1);
            }
            $title = $_POST['title'];
            $desc = $_POST['desc'];
            $del_tg_chats = isset($_POST['del_tg_chats']) ? $_POST['del_tg_chats'] : null;
            
            $edit = User::EditUserGroup($id, $name, $title, $desc, $del_tg_chats);
            if($edit) {
                $log = ActionLog::writeLog('users', 'edit', 'group', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                header("Location: /admin/usergroups/edit/$id?success");
                exit;
            }
        }
        
        $group = User::getUserGroupData($id);
        $title = 'Пользователи - редактировать группу';
        require_once (ROOT . '/template/admin/views/users/editgroup.php');
        return true;
    }
    
    
    
    public function actionDelgroup($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['del_users'])) {
            header("Location: /admin/users");
            exit();
        }
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            $del = User::deleteGroup($id);
            
            if($del){
                $log = ActionLog::writeLog('users', 'delete', 'group', $id, time(), $_SESSION['admin_user'], 0);
                header("Location: ".$setting['script_url']."/admin/usergroups?success");   
            }
        }
    }

    public function actionDelGroupWithUsers($group_id) {
        $acl = self::checkAdmin();
        if(!isset($acl['del_users'])) {
            header("Location: /admin/users");
            exit();
        }
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $group_id = intval($group_id);

        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            $deletedUsers[] = User::deleteUsersInGroup($group_id);
            $deletedGroup[] = User::DeleteGroup($group_id);

            $log = array_merge($deletedGroup, $deletedUsers);

            if ($log) {
                $loginfo = ActionLog::writeLog('users', 'delete', 'group', $group_id, time(), $_SESSION['admin_user'], json_encode($log));
                return header("Location: ".$setting['script_url']."/admin/usergroups?success");
            }
        }
    }
    
    public function actionDelCompleteLesson($lesson_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['del_users'])) {
            header("Location: /admin/users");
            exit();
        }
    
        $lesson_id = intval($lesson_id);
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token'] && $lesson_id && $user_id) {
            if (isset($_GET['newtr'])) {
                $result = Training::delCompleteLessonFull($user_id, $lesson_id);
            } else {
                $result = Course::delCompleteLesson($user_id, $lesson_id);
            }
    
            if ($result) {
                header("Location: /admin/users/edit/$user_id?success");
            }
        }
    }
    
    public function actionResetPass() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_users'])) {
            header("Location: /admin");
        }
    
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $user_name = isset($_GET['user_name']) && $_GET['user_name'] ? $_GET['user_name'] : 'уважаемый пользователь';
        $user_email = isset($_GET['user_email']) ? $_GET['user_email'] : null;
        $token = isset($_GET['token']) ? $_GET['token'] : null;
        
        if ($user_id && $user_name && $user_email && $token == $_SESSION['admin_token']) {
            if ($user_id == 15 && $_SESSION['admin_user'] != 15) {
                header("Location: /admin/users/");
            }
            
            $setting = System::getSetting();
            if (!$setting['pass_reset_letter']) {
                header("Location: /admin/users/");
            }
            
            $pass = System::generateStr(8);
            $res = User::ChangePass($user_id, $pass);
            if ($res) {
                Email::SendLogin($user_name, $user_email, $pass, $setting['pass_reset_letter']);
                header("Location: /admin/users/edit/{$user_id}?success");
            }
        }
    }


    /**
     * КАСТОМНЫЕ ПОЛЯ
     */
    public function actionCustomFields() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_users'])) {
            System::redirectUrl('/admin');
        }

        $setting = System::getSetting();
        $name = $_SESSION['admin_name'];

        if (isset($_POST['add_field']) || isset($_POST['save_field'])) {
            $res = $params = null;
            $field_name = htmlentities($_POST['field_name']);
            $field_data_type = (int)$_POST['field_data_type'];
            $field_type = (int)$_POST['field_type'];

            if (in_array($field_type, [CustomFields::FIELD_TYPE_CHECKBOX, CustomFields::FIELD_TYPE_MULTI_SELECT])) {
                $field_data_type = CustomFields::FIELD_DATA_TYPE_TEXT;
            }

            if (!in_array($field_type, [CustomFields::FIELD_TYPE_TEXT, CustomFields::FIELD_TYPE_TEXTAREA])) {
                $field_list = [];
                $field_headers_list = explode(',', htmlentities($_POST['list_headers']));
                if ($field_headers_list) {
                    foreach ($field_headers_list as $key => $title) {
                        $field_list[$key + 1] = $title;
                    }
                }
                $params = json_encode($field_list);
            }

            if ($field_data_type == CustomFields::FIELD_DATA_TYPE_INT) {
                $field_default_value = (int)$_POST['field_default_value'];
            } else {
                $field_default_value = $_POST['field_default_value'] && in_array($field_type, [CustomFields::FIELD_TYPE_CHECKBOX, CustomFields::FIELD_TYPE_MULTI_SELECT]) ?
                    json_encode(explode(',', $_POST['field_default_value'])) : htmlentities($_POST['field_default_value']);
            }


            $is_show_in_profile = (int)$_POST['is_show_in_profile'];
            $is_show2registration = (int)$_POST['is_show2registration'];
            $is_show2order = (int)$_POST['is_show2order'];
            $is_editable = (int)$_POST['is_editable'];
            $field_sort = (int)$_POST['field_sort'];
            $is_parse_in_api = (int)$_POST['is_parse_in_api'];
            $status = (int)$_POST['status'];

            if (isset($_POST['add_field'])) {
                if (CustomFields::getCountFields(null, null) < 50) {
                    $res = CustomFields::addField($field_name, $field_data_type, $field_type, $field_default_value,
                        $is_show_in_profile, $is_show2registration, $is_editable, $field_sort, $is_parse_in_api, $params,
                        $status, $is_show2order
                    );
                } else {
                    CustomFields::addError('Полей не может быть больше 50');
                }
            } else {
                $field_id = (int)$_POST['field_id'];
                $field_data = CustomFields::getDataField($field_id);
                if (($field_data['field_data_type'] == $field_data_type && $field_data['field_type'] == $field_type) || CustomFields::isAllowUpdField($field_data['column_name'])) {
                    $res = CustomFields::updField($field_data, $field_name, $field_data_type, $field_type,
                        $field_default_value, $is_show_in_profile, $is_show2registration, $is_editable, $is_parse_in_api, $field_sort,
                        $params, $status, $is_show2order
                    );

                    CustomFields::updFieldData($field_id, $field_name, $field_type, $is_show_in_profile, $is_editable,
                        $is_parse_in_api, $field_sort, $params, $status
                    );
                } else {
                    CustomFields::addError('Ошибка сохранения, поле содержит данные');
                }
            }
            System::redirectUrl('/admin/users/custom-fields', $res);
        }

        if (isset($_POST['field_id'])) {
            $custom_field = CustomFields::getDataField((int)$_POST['field_id']);
            if ($custom_field && in_array($custom_field['field_type'], [CustomFields::FIELD_TYPE_CHECKBOX, CustomFields::FIELD_TYPE_MULTI_SELECT])) {
                $data = json_decode($custom_field['default_value'], true);
                $custom_field['default_value'] = is_array($data) ? implode(',', $data) : $custom_field['default_value'];
            }
            $title = 'Кастомные поля';
            require_once (ROOT . '/template/admin/views/users/edit_custom_fields.php');
        } else {
            require_once (ROOT . '/template/admin/views/users/custom_fields.php');
        }
        return true;
    }


    /**
     * УДАЛЕНИЕ КАСТОМНОГО ПОЛЯ
     * @param $field_id
     */
    public function actionDelCustomField($field_id) {
        $acl = self::checkAdmin();
        if(!isset($acl['del_users'])) {
            System::redirectUrl('/admin/users');
        }

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = CustomFields::delField($field_id);
            System::redirectUrl('/admin/users/custom-fields', $del);
        }
    }


    /**
     * @param $id
     */
    public function actionDelSession($id) {
        $setting = System::getSetting();
        $session = UserSession::getSession($id);
        if (!$session) {
            require_once (ROOT . "/template/{$setting['template']}/404.php");
        }

        $res = UserSession::deleteUserData($id);
        System::redirectUrl("/admin/users/edit/{$session['user_id']}");
    }


    /**
     * @param $id
     */
    public function actionBlockSession($id) {
        $setting = System::getSetting();
        $session = UserSession::getSession($id);
        if (!$session) {
            require_once (ROOT . "/template/{$setting['template']}/404.php");
        }

        $res = UserSession::updStatus($id, 2);
        System::redirectUrl("/admin/users/edit/{$session['user_id']}", $res);
    }


    /**
     * @param $id
     */
    public function actionUnblockSession($id) {
        $setting = System::getSetting();
        $session = UserSession::getSession($id);
        if (!$session) {
            require_once (ROOT . "/template/{$setting['template']}/404.php");
        }

        $res = UserSession::updStatus($id, 1);
        System::redirectUrl("/admin/users/edit/{$session['user_id']}", $res);
    }

      /**
     * @param $id
     */
    public function actionDeletePartner($id, $partnerid) {
        if (isset($_POST['del_partner'])) {
            $res = User::deletePartnerFromUser($id);
            ActionLog::writeLog('users', 'delete', 'partner', $id, time(), $_SESSION['admin_user'], json_encode('Удалил партнера:'.$partnerid));
            header("Content-type: application/json; charset=utf-8");
            echo json_encode(['status' => $res]);
        }
    }

    public function actionGenerateTokensForUsers() {
        $users = User::getAllUsers();

        foreach ($users as $user) {
            User::updateUserToken($user['user_id'], json_encode([
                'token' => System::generateStr(64),
                'last_use' => null,
                'create_date' => time(),
            ]));
        }

        System::redirectUrl('/admin/users', true);
    }


    public function actionUserFastFilter() {

        $client = $_REQUEST['client'] ?? null;
        $return404 = false;

        function returnResult($users) {
            if (!$users[0]) {
                return return404();
            }
            $setting = System::getSetting();
            foreach($users as $user):
                include (ROOT.'/template/admin/views/users/user_card.php');
            endforeach;
            die();
        }

        function return404($mess = "Ошибка. Ничего не найдено!") {
            http_response_code(404);
            header("Content-Type: application/json");
            die(json_encode([
                'status' => false,
                'message' => $mess
            ]));
        }

        if (!isset($client)) {
            return return404();
        }

        if (intval($client) != 0) {
            $users[] = User::getUserById(intval($client));
            returnResult($users);
        }

        $clauses = "u.user_name LIKE '%$client%' OR u.email LIKE '%$client%' OR u.surname LIKE '%$client%' OR u.patronymic LIKE '%$client%'";

        $users = User::getUsersWithConditions($clauses);

        returnResult($users);
    }
}