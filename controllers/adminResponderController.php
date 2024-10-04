<?php defined('BILLINGMASTER') or die; 


class adminResponderController extends AdminBase {
    
    
    // ИМПОРТ ПОДПИСЧИКОВ из файла
    public function actionImport()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
        
        $setting = System::getSetting();
        
        $responder_setting = unserialize(Responder::getResponderSetting());
        
        
        if (isset($_POST['import']) && isset($_FILES['file']) && $_FILES['file']['size'] != 0 
        && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            if (!isset($acl['change_responder'])) {
                 header("Location: /admin");
                 exit();
            }
            $time = time();
            $separator = htmlentities($_POST['separator']);
            if ($_POST['confirm'] == 1) $confirmed = 0;
            else $confirmed = $time;
            $delivery = intval($_POST['delivery']);
            $field_one = $_POST['field_one'];
            $field_two = $_POST['field_two'];
            $field_three = $_POST['field_three'];
            
            $email = null;
            $phone = null;
            $name = null;
            $line_arr = [];            
            
            if (isset($_FILES['file'])) {
                $tmp_name = $_FILES["file"]["tmp_name"]; // Временное имя файла на сервере
                $file = $_FILES["file"]["name"]; // Имя файла при загрузке 
                
                $folder = ROOT . '/tmp/'; // папка для сохранения
                $path = $folder . $file; // Полный путь с именем файла
                if (is_uploaded_file($tmp_name)) {
                    move_uploaded_file($tmp_name, $path);
                }
                
                
                $i = 0;
                $file_handle = fopen($path, "r");
                while (!feof($file_handle)) {
                   $line = fgets($file_handle);
                   if (!empty($line)) {
                        
                        if ($field_two == 'none') $line_arr[0] = $line;
                        else $line_arr = explode($separator, $line);
                        
                        switch($field_one) {
                            case 'email':
                            $email = $line_arr[0];
                            break;
                            
                            case 'name':
                            $name = $line_arr[0];
                            break;
                            
                            case 'phone':
                            $phone = $line_arr[0];
                            break;
                        }
                        
                        if (isset($line_arr[1])):
                        switch($field_two) {
                            case 'email':
                            $email = $line_arr[1];
                            break;
                            
                            case 'name':
                            $name = $line_arr[1];
                            break;
                            
                            case 'phone':
                            $phone = $line_arr[1];
                            break;
                        }
                        endif;
                        
                        if (isset($line_arr[2])):
                        switch($field_three) {
                            case 'email':
                            $email = $line_arr[2];
                            break;
                            
                            case 'name':
                            $name = $line_arr[2];
                            break;
                            
                            case 'phone':
                            $phone = $line_arr[2];
                            break;
                        }
                        endif;
                        
                        $subs_key = md5($email . $time);
                        $user_param = "$time;0;;";
                        $add = Responder::addSubsToMap($delivery, strtolower(trim($email)), trim($name), trim($phone), $time, $subs_key, $confirmed, 0, 0, $user_param, $responder_setting, $setting);
                        if ($add) $i++;
                    }
                   
                }
                fclose($file_handle);
                if ($i != 0) header("Location: ".$setting['script_url']."/admin/subscribers/import?success=$i");
                else header("Location: ".$setting['script_url']."/admin/subscribers/import?fail");
            }
            
        }
        $title = 'Подписчики - импорт';
        require_once (ROOT . '/template/admin/views/responder/import.php');
        return true;
    }
    
    
    
    // СОЗДАНИЕ ПОДПИСЧИКА ВРУЧНУЮ
    public static function actionAddsubscriber()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
        $responder_setting = unserialize(Responder::getResponderSetting());
        
        
        $setting = System::getSetting();
        
        if (isset($_POST['add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                 header("Location: /admin");
                 exit();
            }
            $name = htmlentities($_POST['name']);
            $email = htmlentities($_POST['email']);
            $phone = htmlentities($_POST['phone']);
            $delivery = intval($_POST['delivery']);
            $time = time();
            $subs_key = md5($email . $time);
            $user_param = "$time;0;;";
            if ($_POST['confirm'] == 1) $confirmed = 0;
            else $confirmed = $time;
            
            $add = Responder::addSubsToMap($delivery, $email, $name, $phone, $time, $subs_key, $confirmed, 0, 0, $user_param, $responder_setting, $setting);
            if ($add) {
                header("Location: ".$setting['script_url']."/admin/subscribers?success");
            }
            
        }
        $title = 'Подписчики - создание';
        require_once (ROOT . '/template/admin/views/responder/add_subscriber.php');
        return true;
    }
    
    
    
    
    // СОЗДАНИЕ ФОРМЫ ПОДПИСКИ
    public function actionForm($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
        $params = unserialize(Responder::getResponderSetting());
        
        $setting = System::getSetting();
        
        if (isset($_POST['made']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                 header("Location: /admin");
                 exit();
            }
            $html_style = '<style>
            .bm_subs_form ul {padding:0; margin:0}
            ';
            $html_style2 = '</style>';
            
            if ($_POST['style'] == 'light') $html_style2 = '
            .bm_subs_form.light {width:auto; background:#fff; border:1px solid #ccc}
            </style>';
            
            if ($_POST['style'] == 'black') $html_style2 = '
            .bm_subs_form.black {width:auto; background:#555; border:1px solid orange}
			.bm_subs_form.black h3 {color:#eee}
            </style>';
            
            
            
            $html_title = '<div class="userbox bm_subs_form ' . $_POST['style'].'">'. '<h3>'.$_POST['title'].'</h3>';
            $html_head1 = $_POST['header'];
            if ($_POST['target'] == 1) $target = ' target="_blank"';
            else $target = '';
            $html_head2 = '<form action="'.$setting['script_url'].'/responder/subscribe/'.$id.'" method="POST"'.$target.'>
            <ul>';
            
            $html_email = '<p><script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCI="));</script> placeholder="Ваш E-mail" required="required" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$"></p>';
            
            if ($_POST['name'] == 1) $html_name = '<p><input type="text" name="name" placeholder="Ваше имя" required="required"></p>';
            elseif ($_POST['name'] == 2) $html_name = '<p><input type="text" name="name" placeholder="Ваше имя"></p>';
            else $html_name = '';
            
            if ($_POST['phone'] == 1) $html_phone = '<p><input type="text" name="phone" placeholder="Ваш телефон" required="required"></p>';
            elseif ($_POST['phone'] == 2) $html_phone = '<p><input type="text" name="phone" placeholder="Ваш телефон"></p>';
            else $html_phone = '';
            
            if($_POST['check'] == 1) $checkbox = '<p><input type="checkbox" required="required"> Согласен получать письма</p>';
            else $checkbox = null;

            $html_bootom = '<p><input type="submit" class="btn-yellow text-uppercase font-bold button" value="Подписаться" name="subscribe"></p></ul></form>';
            $html_footer = '<div class="bm_subs_form_bottom">'.$_POST['footer'] . '</div></div>';
            
            $html = $html_style . $html_style2 . $html_title . $html_head1 . $html_head2 . $html_name . $html_email . $html_phone . $checkbox .$html_bootom . $html_footer ;
            
            require_once (ROOT . '/template/admin/views/responder/generator_ok.php');
            return true;
        }
        $title = 'Подписчики - создание формы подписки';
        require_once (ROOT . '/template/admin/views/responder/generator.php');
        return true;
    }
    
    
    // СПИСОК ПОДПИСЧИКОВ
    public function actionSubscribers()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) {
            header("Location: /admin");
        }
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) {
            exit('Responder not installed');
        }

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $params = unserialize(Responder::getResponderSetting());
        $filter = [
            'delivery' =>  isset($_GET['filter']) && isset($_GET['delivery']) ? intval($_GET['delivery']) : null,
            'email' => isset($_GET['filter']) && isset($_GET['email']) ? htmlentities($_GET['email']) : null
        ];
        $total = Responder::getTotalSubscribers($filter);
        $subs_list = Responder::getUniqSubscribersByFilter($filter, $page, $setting['show_items']);
        $pagination = new Pagination($total, $page, $setting['show_items']);
        $title = 'Подписчики - список';

        require_once (ROOT . '/template/admin/views/responder/subscribers.php');
        return true;
    }
    
    
    
    // УДАЛИТЬ ПОДПИСКУ 
    public function actionDelsubs($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        if (!isset($acl['del_responder'])) {
                 header("Location: /admin");
                 exit();
            }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
		$setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Responder::DeleteSubsRowByID($id);
            
            if ($del) header("Location: ".$setting['script_url']."/admin/subscribers?success");
        }
    }
    
    
    
    // СПИСОК РАССЫЛОК (МАССОВЫХ И АВТОСЕРИЙ)
    public function actionIndex($type, $page = 1)
    {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $type = htmlentities($type);
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
		$setting = System::getSetting();
        $total = 0;
        
        
        $params = unserialize(Responder::getResponderSetting());
        
        if ($type == 'mass') {
            $delivery_list = Responder::getDeliveryList(1, $page, $setting['show_items']);
            $total = Responder::countDeliveriesByType(1);
        }
        if ($type == 'auto') {
            $delivery_list = Responder::getDeliveryList(2, $page, $setting['show_items']);
            $total = Responder::countDeliveriesByType(2);
        }
        
        if ($setting['show_items'] < $total) $is_pagination = true;
        else $is_pagination = false;
        $pagination = new Pagination($total, $page, $setting['show_items']);
        $title = 'Массовые рассылки - список автосерий';
        if ($type == 'mass') require_once (ROOT . '/template/admin/views/responder/index.php');
        else require_once (ROOT . '/template/admin/views/responder/index_auto.php');
        return true;
    }

    // СПИСОК ПЛОХИХ EMAIL по рассылке 
    public function actionBadSubscribers($id)
    {
        $acl = self::checkAdmin();
        $bad_list = Responder::getBadList($id);
        $title = 'Подписчики - список неверных email';
        require_once (ROOT . '/template/admin/views/responder/bad_list.php');
        return true;
    }
    
    
    // СОЗДАТЬ МАССОВУЮ РАССЫЛКУ ИЛИ АВТОСЕРИЮ
    public function actionAdd()
    {
        require_once (__DIR__ . '/../vendor/autoload.php');
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['show_responder'])) {
            System::redirectUrl("/admin");
        }

        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) {
            exit('Responder not installed');
        }

        $setting = System::getSetting();
        $params = unserialize(Responder::getResponderSetting());

        if (isset($_POST['add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                System::redirectUrl("/admin");
            }

            $time = time();
            $type = intval($_POST['type']);
            $count_letters = 0;
            $target = '';
            $count_bad = 0;

            // Получатели массового письма
            if ($_POST['type'] == 1) { // если рассылка массовая
                $tip = 'mass';

                $target = htmlentities($_POST['target']);
                // Типы пользователей
                $user_types_arr = [];
                if (isset($_POST['user_types'])) {
                    $user_types = $_POST['user_types'];
                    $user_types_arr = Responder::getListByUserTypes($user_types); // Получить юзеров по типу
                }

                // Группы пользователей
                $user_groups_arr = [];
                if (isset($_POST['user_groups'])) {
                    $user_groups = $_POST['user_groups'];
                    $user_groups_arr = Responder::getListByUserGroups($user_groups); // Получить юзеров по группе
                }

                // По подписке
                $user_subs_arr = [];
                if (isset($_POST['user_subs'])) {
                    $user_subs = $_POST['user_subs'];
                    $user_subs_arr = Responder::getListByUserSubs($user_subs);
                }

                // По емейл рассылке
                $user_delivery_arr = [];
                if (isset($_POST['user_delivery'])) {
                    $user_delivery = $_POST['user_delivery'];
                    $user_delivery_arr = Responder::getListByResponder($user_delivery);
                }

                // По сегменту
                $user_segments_arr = [];
                if (isset($_POST['user_segments'])) {
                    $chance = intval($_POST['chance']); // вероятность
                    $user_segments = $_POST['user_segments'];
                    $user_segments_arr = Responder::getListByUserSegments($user_segments, $chance);
                }

                // ОБЪЕДИНИТЬ и удалить дубликаты
                $new_arr = array_unique(array_merge_recursive(
                                            $user_types_arr,
                                            $user_groups_arr,
                                            $user_subs_arr,
                                            $user_delivery_arr,
                                            $user_segments_arr
                                        )
                );
                unset($user_types_arr, $user_groups_arr, $user_subs_arr, $user_delivery_arr, $user_segments_arr );


                /**
                 *  ИСКЛЮЧЕНИЕ
                 */

                // Типы пользователей
                $ex_user_types_arr = [];
                if (isset($_POST['ex_user_types'])) {
                    $ex_user_types = $_POST['ex_user_types'];
                    $ex_user_types_arr = Responder::getListByUserTypes($ex_user_types); // Исключить юзеров по типу
                }


                // Группы пользователей
                $ex_user_groups_arr = [];
                if (isset($_POST['ex_user_groups'])) {
                    $ex_user_groups = $_POST['ex_user_groups'];
                    $ex_user_groups_arr = Responder::getListByUserGroups($ex_user_groups); // Получить юзеров по группе
                }


                // По подписке
                $ex_user_subs_arr = [];
                if (isset($_POST['ex_user_subs'])) {
                    $ex_user_subs = $_POST['ex_user_subs'];
                    $ex_user_subs_arr = Responder::getListByUserSubs($ex_user_subs);
                }


                // По емейл рассылке
                $ex_user_delivery_arr = [];
                if (isset($_POST['ex_user_delivery'])) {
                    $ex_user_delivery = $_POST['ex_user_delivery'];
                    $ex_user_delivery_arr = Responder::getListByResponder($ex_user_delivery);
                }


                // По сегменту
                $ex_user_segments_arr = [];
                if (isset($_POST['ex_user_segments'])) {
                    $chance = 0;
                    $ex_user_segments = $_POST['ex_user_segments'];
                    $ex_user_segments_arr = Responder::getListByUserSegments($ex_user_segments, $chance);
                }


                // ОБЪЕДИНИТЬ Исключения и удалить дубликаты
                $exclude_arr = array_unique(array_merge_recursive(
                                                $ex_user_types_arr,
                                                $ex_user_groups_arr,
                                                $ex_user_subs_arr,
                                                $ex_user_delivery_arr,
                                                $ex_user_segments_arr
                                            )
                );
                unset($ex_user_types_arr, $ex_user_groups_arr, $ex_user_subs_arr, $ex_user_delivery_arr, $ex_user_segments_arr );

                // СОЗДАТЬ ОКОНЧАТЕЛЬНЫЙ МАСИВ ПОЛУЧАТЕЛЕЙ
                $recipients = $bad_list = [];
                $i = 0;
                foreach ($new_arr as $value) {
                    if (!in_array ($value, $exclude_arr)) {
                        $validator = new Egulias\EmailValidator\EmailValidator();
                        if ($validator->isValid($value, new Egulias\EmailValidator\Validation\RFCValidation())) {
                            $recipients[$i++] = $value;
                        } else {
                            $bad_list[] = User::getUserIDatEmail($value);
                            $count_bad++;
                        }
                    }
                }

                $count_letters = count($recipients);

            } else {
                $tip = 'auto';
            }

            $confirm_body = isset($_POST['confirm_body']) ? $_POST['confirm_body'] : null;
            $confirm_subject = isset($_POST['confirm_subject']) ? htmlentities($_POST['confirm_subject']) : null;
            $after_confirm_text = isset($_POST['after_confirm_text']) ? $_POST['after_confirm_text'] : null;

            $name = htmlentities($_POST['name']);
            $desc = htmlentities($_POST['desc']);
            
            $sender_name = isset($_POST['sender_name']) && !empty(trim($_POST['sender_name']))
                ? htmlentities($_POST['sender_name']) 
                : null;

            $send = !empty($_POST['send']) ? strtotime($_POST['send']) : $time;
            $subject = isset($_POST['subject']) ? $_POST['subject'] : null;
            $letter = isset($_POST['letter']) ? $_POST['letter'] : null;
            $sent_list = isset($_POST['sent']) ? serialize($_POST['sent']) : 0;
            //$sent_list = isset($recipients) ? json_encode($recipients) : 0;
            $ex_list = isset($_POST['ex_sent']) ? serialize($_POST['sent']) : 0;
            $confirmation = isset($_POST['confirmation']) ? intval($_POST['confirmation']) : 0;
            $bad_list_id = !empty($bad_list) ? implode(",", $bad_list) : null;
            $add_user_groups = isset($_POST['add_user_groups']) ? json_encode($_POST['add_user_groups']) : null;
            $redirect_url = isset($_POST['redirect_url']) ? trim(filter_var($_POST['redirect_url']),FILTER_SANITIZE_URL ) : null;

            $add = Responder::addDelivery($name, $type, $sender_name, $desc, $send, $time, $subject, $letter, $confirmation, $sent_list, $ex_list, $count_letters, $target, $confirm_body, $confirm_subject,   $after_confirm_text,$count_bad, $bad_list_id, $add_user_groups, $redirect_url);

            if ($type == 1) {
                // Создать задания на отправку
                if (isset($recipients) && $recipients) {
                    foreach($recipients as $recipient) {
                        $add_task = Responder::AddTask($add['delivery_id'], $add['letter_id'], $recipient, $send, 0);
                    }
                }

                if ($add) {
                    System::redirectUrl("/admin/responder/$tip?success");
                }
            } else {
                if ($add) {
                    System::redirectUrl("/admin/responder/$tip?success");
                }
            }

        }
        $title = 'Массовые рассылки - отправить массовое письмо';
        if (isset($_GET['type']) && $_GET['type'] == 'mass') {
            require_once (ROOT . '/template/admin/views/responder/add_delivery.php');
        }
        /// TODO надо переделать отображение подписчиков в списке #todo
//         elseif(isset($_GET['type']) && $_GET['type'] == 'list_email_to_response'&& isset($_GET['id']) ){
//             $title = 'Массовые рассылки - список email';
//             $id=$_GET['id'];
// //          $list_email_to_responder=Responder::getTasksForAction(); так будет дольше идти запрос в бд
//             $list_email_to_responder=Responder::getDeliveryListForID(1,1,100000,$id);
//             require_once (ROOT . '/template/admin/views/responder/responder_list_email.php');
//         }
        else {
            $title = 'Массовые рассылки - создать автосерию писем';
            require_once (ROOT . '/template/admin/views/responder/add_responder.php');

        }
        return true;
    }

    // ВЫВОД EMAIL ПОЛУЧАТЕЛЕЙ МАССОВОЙ РАССЫЛКИ
    public function actionShowEmail($id)
       {
           require_once (__DIR__ . '/../vendor/autoload.php');
           $acl = self::checkAdmin();
           $name = $_SESSION['admin_name'];
           if (!isset($acl['show_responder'])) {
               System::redirectUrl("/admin");
           }

           $responder = System::CheckExtensension('responder', 1);
           if (!$responder) {
               exit('Responder not installed');
           }

           $setting = System::getSetting();
           $params = unserialize(Responder::getResponderSetting());

        $title = 'Массовые рассылки - список email';
        $id_responder=$id;
        $name_responder=Responder::getDeliveryData($id)['name'];
        $total= Responder::getCountTasksForAction($id);
//        $limit=5;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $is_pagination = true;
        $pagination = new Pagination($total, $page,$setting['show_items']);//TODO $limit->$setting['show_items'] || $setting['show_items']->$limit
        $list_email_to_responder=Responder::getUniqEmail($page, $setting['show_items'], $id);
        require_once (ROOT . '/template/admin/views/responder/responder_list_email.php');
       return true;
    }

// УДАЛЕНИЕ EMAIL ПОЛУЧАТЕЛЯ МАССОВОЙ РАССЫЛКИ
    public function actionDeleteEmail($id)
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['show_responder'])) {
            System::redirectUrl("/admin");
        }
        $name = $_SESSION['admin_name'];
        $title = 'Массовые рассылки - удаление email';
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
        Responder::delEmailFromResponder($id);
    }

       if(isset($_GET['id_responder']))
        $this->actionShowEmail($_GET['id_responder']);

    }
    
    
    // ИЗМЕНИТЬ РАССЫЛКУ
    public function actionEdit($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) {
            System::redirectUrl("/admin");
        }

        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) {
            exit('Responder not installed');
        }

        $setting = System::getSetting();
        $params = unserialize(Responder::getResponderSetting());
        $id = intval($id);
        $delivery = Responder::getDeliveryData($id);

        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                System::redirectUrl("/admin");
            }

            $time = time();
            $type = intval($_POST['type']);
            $tip = $type == 1 ? 'mass' : 'auto';

            $name = htmlentities($_POST['name']);
            $desc = htmlentities($_POST['desc']);
            $target = isset($_POST['target']) ? htmlentities($_POST['target']) : '';

            $sender_name = isset($_POST['sender_name']) && !empty(trim($_POST['sender_name']))
                ? htmlentities($_POST['sender_name']) 
                : null;

            $subject = isset($_POST['subject']) ? $_POST['subject'] : null;
            $letter_id = isset($_POST['letter_id']) ? intval($_POST['letter_id']) : 0;
            $letter = isset($_POST['letter']) ? $_POST['letter'] : null;
            $confirmation = isset($_POST['confirmation']) ? intval($_POST['confirmation']) : 0;
            $confirm_body = isset($_POST['confirm_body']) ? $_POST['confirm_body'] : null;
            $confirm_subject = isset($_POST['confirm_subject']) ? htmlentities($_POST['confirm_subject']) : null;
            $after_confirm_text = isset($_POST['after_confirm_text']) ? $_POST['after_confirm_text'] : null;
            $add_user_groups = isset($_POST['add_user_groups']) ? json_encode($_POST['add_user_groups']) : null;
            $redirect_url = isset($_POST['redirect_url']) ? trim(filter_var($_POST['redirect_url']),FILTER_SANITIZE_URL ) : null;
            $edit = Responder::editDelivery($id, $letter_id, $name, $sender_name, $type, $desc, $time, $subject, $letter,
                                            $confirmation, $target, $confirm_body, $confirm_subject, $after_confirm_text, $add_user_groups, $redirect_url);
            if ($edit) {
                System::redirectUrl("/admin/responder/$tip?success");
            }
        }
        $title = 'Подписчики - изменение рассылки';
        if (isset($_GET['type']) && $_GET['type'] == 'mass') {
            $letter = Responder::getDeliveryLetter($id);
            require_once (ROOT . '/template/admin/views/responder/edit_delivery.php');
        } else {
            require_once (ROOT . '/template/admin/views/responder/edit_responder.php');
        }
        return true;
    }
    
    
    
    
    // УДАЛИТЬ РАССЫЛКУ / АВТОСЕРИЮ
    public function actionDelete($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_responder'])) {
            System::redirectUrl("/admin/subscribers");
        }

        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) {
            exit('Responder not installed');
        }

		$setting = System::getSetting();
        $params = unserialize(Responder::getResponderSetting());
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $type = '';
            if (isset($_GET['type'])) {
                if ($_GET['type'] == 1) {
                    $type = '&type=mass';
                } elseif ($_GET['type'] == 2) {
                    $type = '2';
                }
            }

            $id = intval($id);
            $del = Responder::delDelivery($id, $type);
            if ($del) {
                $redirect_url = $type == 2 ? '/admin/responder/auto/' : '/admin/responder/mass/';
                System::redirectUrl($redirect_url, true);
            } else {
                exit('pipeц');
            }
        }
    }
    
    
    
    /**
     *  ПИСЬМА АВТОСЕРИИ
     */
    
    // СПИСОК ПИСЕМ ОДНОЙ АВТОСЕРИИ
    public static function actionAutoletters($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
		$setting = System::getSetting();
        
        $params = unserialize(Responder::getResponderSetting());
        
        $letter_list = Responder::getAutoLetterList($id);
        $delivery = Responder::getDeliveryData($id);
        $title = 'Рассылка - список писем';
        require_once (ROOT . '/template/admin/views/responder/auto_letters.php');
        return true;
    }
    
    
    
    // СОЗДАТЬ ПИСЬМО АВТОСЕРИИ
    public static function actionAddautoletter($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
		$setting = System::getSetting();
        
        $params = unserialize(Responder::getResponderSetting());
        
        if (isset($_POST['addauto']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                 header("Location: /admin");
                 exit();
            }
            $send = intval($_POST['sending']);
            $target = htmlentities($_POST['target']);
            $subject = $_POST['subject'];
            $letter = $_POST['letter'];
            $status = intval($_POST['status']);
            
            $add = Responder::addAutoLetter($id, $send, $target, $subject, $letter, $status);
            if ($add) header("Location: ".$setting['script_url']."/admin/responder/autoletters/$id");
            
        }
        
        $letter_list = Responder::getAutoLetterList($id);
        $title = 'Рассылка - создать письмо';
        require_once (ROOT . '/template/admin/views/responder/add_auto_letter.php');
        return true;
    }
    
    
    
    // ИЗМЕНИТЬ ПИСЬМО АВТОСЕРИИ
    public function actionEditautoletter($delivery_id, $letter_id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
		$setting = System::getSetting();
        
        $params = unserialize(Responder::getResponderSetting());
        
        if (isset($_POST['editauto']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                 header("Location: /admin");
                 exit();
            }
            $send = intval($_POST['sending']);
            $target = htmlentities($_POST['target']);
            $subject = $_POST['subject'];
            $letter = $_POST['letter'];
            $status = intval($_POST['status']);
            
            $edit = Responder::editAutoLetter($letter_id, $send, $target, $subject, $letter, $status);
            if ($edit) header("Location: ".$setting['script_url']."/admin/responder/autoletters/$delivery_id/edit/$letter_id?success");
        }
        
        $letter = Responder::getLetterData($letter_id);
        $title = 'Рассылка - изменение письма автосерии';
        require_once (ROOT . '/template/admin/views/responder/edit_auto_letter.php');
        return true;
    }
    
    
    
    // УДАЛИТЬ ПИСЬМО АВТОСЕРИИ
    public static function actionDelautoletter($delivery_id, $letter_id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_responder'])) {
            header("Location: /admin/subscribers");
            exit();   
        }
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
		$setting = System::getSetting();
        
        $delivery_id = intval($delivery_id);
        $letter_id = intval($letter_id);
        
        $del = Responder::delAutoletter($letter_id);
        if ($del) header("Location: ".$setting['script_url']."/admin/responder/autoletters/$delivery_id?success");
    }
    
    
    
    // НАСТРОЙКИ РАССЫЛКИ
    public function actionSetting()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
		$setting = System::getSetting();
        
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_responder'])) {
                 header("Location: /admin");
                 exit();
            }
            $params = serialize($_POST['responder']);
            $status = intval($_POST['status']);
            $save = Responder::SaveResponderSetting($params, $status);
            
        }
        
        $params = unserialize(Responder::getResponderSetting());
        $enable = Responder::getResponderStatus();
        $title = 'Рассылка - настройка';
        require_once (ROOT . '/template/admin/views/responder/setting.php');
        return true;
    }
    
    
    public function actionTest()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_responder'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $responder = System::CheckExtensension('responder', 1);
        if (!$responder) exit('Responder not installed');
		$setting = System::getSetting();
        
        //Responder::getTasksForAction();
        
        echo 'Рассылка отправлена';
    }
    
}