<?php use Migrations\MigrationsTable;

defined('BILLINGMASTER') or die;

class adminSettingController extends AdminBase {
    
    
    public function actionSettings()
    {
        System::checkPermission('show_main_tunes');

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $setting_main = System::getSettingMainpage();
        $setting_main2 = System::getSettingMainpageBySecondId();
        $params = json_decode($setting['params'], true);
        $custom_url_redirect = isset($params['custom_url_redirect']) ? $params['custom_url_redirect'] : '';
        $smsс = unserialize(base64_decode($setting['smsc']));
        $ticket = unserialize(base64_decode($setting['org_data']));
        $remind_sms1 = unserialize(base64_decode($setting['remind_sms1']));
        $remind_sms2 = unserialize(base64_decode($setting['remind_sms2']));

        $folder = ROOT . '/template/' . $setting['template'] . '/js/'; // папка с плеером
        $path_orig_player = $folder . 'player_bm_orig.js'; // Полный путь с именем файла
        $path_cur_player = $folder . 'player_bm.js'; // Полный путь с именем файла
        if (file_exists($path_orig_player) && file_exists($path_cur_player)) {
            $diffplayer = md5_file($path_orig_player) != md5_file($path_cur_player);
        }

        if (isset($_GET['resetplayer']) && $_GET['token'] == $_SESSION['admin_token']) {
            $copy = copy($path_orig_player, $path_cur_player);
            if($copy) {
                header("Location: ".$setting['script_url']."/admin/settings?success");
            }
        }

        if (isset($_POST['save_main'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            System::checkPermission('change_main_tunes');

            cache::clearCurrentDriver();

            $site_name = $_POST['site_name'];
            $admin_email = trim($_POST['admin_email']);
            $support_email = trim($_POST['support_email']);
            $lang = $_POST['lang'];
            $currency = $_POST['currency'];
            $template = $_POST['template'];
            $template_set = $_POST['template_set'];
            $show_items = $_POST['show_items'];
            $script_url = trim($_POST['script_url']);
            $security_key = trim($_POST['security_key']);
            $cookie = $_POST['cookie'];
            $secret_key = trim($_POST['secret_key']);
			$private_key = trim($_POST['private_key']);
            $debug_mode = $_POST['debug_mode'];
            $max_upload = $_POST['max_upload'] < 32768 ? intval($_POST['max_upload'])  : 32767;
            $login_redirect = intval($_POST['login_redirect']);
            
            $params = json_encode(array_merge($params, $_POST['params']));
            
            $use_cart = intval($_POST['use_cart']);
            $enable_catalog = intval($_POST['enable_catalog']);
            $enable_reviews = intval($_POST['enable_reviews']);
            $enable_landing = intval($_POST['enable_landing']);
            $enable_sale = intval($_POST['enable_sale']);
            $enable_cabinet = intval($_POST['enable_cabinet']);
            $enable_registration = intval($_POST['enable_registration']);
            $multiple_authorizations = intval($_POST['multiple_authorizations']);
            $user_sessions = json_encode($_POST['user_sessions']);
            $enable_feedback = intval($_POST['enable_feedback']);
            $write_feedback = intval($_POST['write_feedback']);
            $split_test_enable = intval($_POST['split_test_enable']);
            $request_phone = $_POST['request_phone'];
            $show_order_note = intval($_POST['show_order_note']);
            $email_protection = intval($_POST['email_protection']);
            $strict_report = intval($_POST['strict_report']);
            $simple_free_dwl = $_POST['simple_free_dwl'];   
            $dwl_in_lk = $_POST['dwl_in_lk'];
            $nds_enable = intval($_POST['nds_enable']);
            $nds_value = intval($_POST['nds_value']);
            
            $order_life_time = $_POST['order_life_time'] < 128 ? $_POST['order_life_time'] : 127;
            $dwl_time = $_POST['dwl_time'] < 32768 ? intval($_POST['dwl_time'])  : 32767;
            $dwl_count = $_POST['dwl_count'] < 128 ? intval($_POST['dwl_count'])  : 127;
            
            $yacounter = htmlentities($_POST['yacounter']);
            $ga_target = intval($_POST['ga_target']);
            
            $use_smtp = $_POST['use_smtp'];
            $smtp_host = $_POST['smtp_host'];
            $smtp_port = $_POST['smtp_port'];
            $smtp_user = $_POST['smtp_user'];
            $smtp_pass = trim($_POST['smtp_pass']);
            $smtp_ssl = $_POST['smtp_ssl'];
            $sender_name = $_POST['sender_name'];
            $sender_email = trim($_POST['sender_email']);
            $smtp_domain = $_POST['smtp_domain'];
            $smtp_selector = $_POST['smtp_selector'];
            $smtp_private_key = trim($_POST['smtp_private_key']);
            $return_path = $_POST['return_path'];
            
            $show_surname = intval($_POST['show_surname']);
            $only_name2name = isset($_POST['only_name2name']) ? intval($_POST['only_name2name']) : 0;
            $show_patronymic = intval($_POST['show_patronymic']);
            $show_telegram_nick = intval($_POST['show_telegram_nick']);
            $show_instagram_nick = intval($_POST['show_instagram_nick']);
            $show_vk_page = intval($_POST['show_vk_page']);

            $sms_service = intval($_POST['sms_service']);
            $smsc = base64_encode(serialize($_POST['smsc']));
            $mobizon = json_encode($_POST['mobizon']);

            $countries_list = !empty($_POST['countries_list']) ? json_encode($_POST['countries_list']) : '';
            $session_time = intval($_POST['session_time']) < 128 ? intval($_POST['session_time']) : 127;
            $editor = (int)$_POST['editor'];
            $logs_life_time = (int)$_POST['logs_life_time'];

            //Уведомления о выписке счета
            $notify_admin_about_account_statement = intval($_POST['notify_admin_about_account_statement']);
            $emails_for_account_statement_notifications = $_POST['emails_for_account_statement_notifications'];
            $email_to_notify = [];
            if (isset($email_to_notify)) {
                $emails_to_notify = System::parseEmails($emails_for_account_statement_notifications);
                $emails_to_notify = json_encode($emails_to_notify);
            }

            if (isset($_FILES["cover"]["tmp_name"]) && $_FILES["cover"]["size"] != 0) {
                $tmp_name = $_FILES["cover"]["tmp_name"]; // Временное имя картинки на сервере
                $img = $_FILES["cover"]["name"]; // Имя картинки при загрузке 
                
                $folder = ROOT . '/images/'; // папка для сохранения
                $path = $folder . $img; // Полный путь с именем файла
                if (is_uploaded_file($tmp_name)) {
                    if (file_exists($path)) {
                        $pathinfoimage = pathinfo($path);
                        $newname = $pathinfoimage['filename'].'-copy.'.$pathinfoimage['extension'];
                        $img = $newname;
                        $path = $folder . $newname;
                    }
                    move_uploaded_file($tmp_name, $path);
                }
            } else {
                $img = $_POST['current_img'];
            }

            if (isset($_FILES["favicon"]["tmp_name"]) && $_FILES["favicon"]["size"] != 0) {
                $icon_types = array(
                    'image/vnd.microsoft.icon',
                    'image/x-icon',
                    'image/x-ms-bmp');

                $tmp_name_favicon = $_FILES["favicon"]["tmp_name"]; // Временное имя картинки на сервере
                $path = ROOT . '/favicon.ico';
                    move_uploaded_file($tmp_name_favicon, $path);
            }

            if (isset($_FILES["playerjs"]["tmp_name"]) && $_FILES["playerjs"]["size"] != 0) {
                $playerjs_upload = $_FILES["playerjs"]["tmp_name"];
                move_uploaded_file($playerjs_upload, $path_cur_player);
            }
            //Капча
            $reCaptcha['enable'] = intval($_POST['enable_reCaptcha']);
            $reCaptcha['reCaptchaSecret'] = $_POST['reCaptchaSecret'];
            $reCaptcha['reCaptchaSiteKey'] = $_POST['reCaptchaSiteKey'];
            $reCaptcha['minimalScoreVerifyValue'] = intval($_POST['minimalScoreVerifyValue']) / 100;
            
            $reCaptcha = json_encode($reCaptcha);


            if (isset($_POST['cache'])) {
                //перезапись
                $cacheset = [
                    'enable'=> $_POST['cache']['enable'] ?? 0,
                    'type'=> $_POST['cache']['type'] ?? 'file',
                    'memcached'=> [
                        'port'=> $_POST['cache']['memcached']['port'] ?? 11211,
                    ],
                ];
                file_put_contents(ROOT.'/config/cache.php', "<?php".PHP_EOL.PHP_EOL. 'return ' . var_export($cacheset, true) . ";".PHP_EOL);
            }

            $save = System::saveSettings($site_name, $admin_email, $support_email, $lang, $currency, $template,
                $template_set, $show_items, $script_url, $security_key, $cookie, $secret_key, $debug_mode,
                $max_upload, $use_cart, $enable_catalog, $enable_reviews, $enable_landing, $enable_sale,
                $enable_cabinet, $enable_registration, $multiple_authorizations, $user_sessions, $enable_feedback,
                $write_feedback, $split_test_enable, $request_phone, $show_order_note, $email_protection, $strict_report,
                $simple_free_dwl, $dwl_in_lk, $order_life_time, $dwl_time, $dwl_count, $yacounter, $ga_target,
                $use_smtp, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_ssl, $sender_name, $sender_email,
                $smtp_domain, $smtp_selector, $smtp_private_key, $img, $return_path, $login_redirect, $show_surname,
                $only_name2name, $show_patronymic, $show_telegram_nick, $show_instagram_nick, $smsc, $countries_list, $session_time,
                $private_key, $params, $editor, $logs_life_time, $nds_enable, $nds_value, $notify_admin_about_account_statement, $emails_to_notify,
                $reCaptcha, $sms_service, $mobizon, $show_vk_page
            );

            if ($save) {
                $log = ActionLog::writeLog('settings', 'edit', 'save_main', 1, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::redirectUrl("/admin/settings?success");
            }
        }

        
        if (isset($_POST['save_vid'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            System::checkPermission('change_main_tunes');

			$external_url = htmlentities($_POST['external_url']);
            $main_page_content = $_POST['main_page_content'];
            $main_page_title = $_POST['main_page_title'];
            $main_page_desc = $_POST['main_page_desc'];
            $main_page_keys = $_POST['main_page_keys'];
            $main_page_tmpl = isset($_POST['main_page_tmpl']) ? $_POST['main_page_tmpl'] : 1;
            $main_page_text = $_POST['main_page_text'];
            $in_head = $_POST['in_head'];
            $in_body = $_POST['in_body'];

            $catalog_title = htmlentities($_POST['catalog_title']);
            $catalog_h1 = htmlentities($_POST['catalog_h1']);
            $catalog_desc = htmlentities($_POST['catalog_desc']);
            $catalog_keys = htmlentities($_POST['catalog_keys']);
            $catalog_filter = intval($_POST['catalog_filter']);
            
            $reviews_tune = base64_encode(serialize($_POST['reviews_tune']));
            
            $politika_link = htmlentities($_POST['politika_link']);
            $oferta_link = htmlentities($_POST['oferta_link']);
            $politika_text = $_POST['politika_text'];
            $oferta_text = $_POST['oferta_text'];
            $oferta_text2  = $_POST['oferta_text2'];
            
            $params = json_encode(array_merge($params, $_POST['params']));

            $save = System::SaveVID($main_page_content, $main_page_title, $main_page_desc, $main_page_keys,
                $main_page_tmpl, $main_page_text, $in_head, $in_body, $catalog_title, $catalog_h1, $catalog_desc,
                $catalog_keys, $reviews_tune, $politika_link, $oferta_link, $politika_text, $oferta_text,
                $external_url, $catalog_filter, $params, $oferta_text2 
            );
        $oferta_texts=System::GetWithoutPartner();
        $is_equal=false;
        if($oferta_texts!=null){
            if($oferta_texts[0]!=null){
                if($oferta_texts[0]['text'] == $oferta_text)
                {
                    $is_equal=true;
                }
        }
    }    
        $oferta_texts=System::GetWithPartner(1);
        $is_equal1=false;
        if($oferta_texts!=null){
            if($oferta_texts[0]!=null){
                      if($oferta_texts[0]['text'] == $oferta_text)
                {
                    $is_equal1=true;
                }
        }}
        if(!$is_equal1)
            System::InsertOferta(  $oferta_text2,1);
        if(!$is_equal)
            System::InsertOferta(  $oferta_text);
            if ($save) {
                $log = ActionLog::writeLog('settings', 'edit', 'save_vid', 1, time(), $_SESSION['admin_user'], json_encode($_POST));
                header("Location: ".$setting['script_url']."/admin/settings?cat=vid&success");
            }
        }


        if (isset($_POST['save_letters'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            cache::clearCurrentDriver();
            System::checkPermission('change_main_tunes');
            
            $client_letter_subj = $_POST['client_letter_subj'];
            $client_letter = $_POST['client_letter'];

            $reg_confirm_letter = $_POST['reg_confirm_letter'];
            $register_letter = $_POST['register_letter'];
            $pass_reset_letter = $_POST['pass_reset_letter'];

            $remind_letter1 = base64_encode(serialize($_POST['remind_letter1']));
            $remind_letter2 = base64_encode(serialize($_POST['remind_letter2']));
            $remind_letter3 = base64_encode(serialize($_POST['remind_letter3']));
            
            $remind_sms1 = base64_encode(serialize($_POST['remind_sms1']));
            $remind_sms2 = base64_encode(serialize($_POST['remind_sms2']));
            $reg_sms = json_encode($_POST['reg_sms']);
            $ticket = base64_encode(serialize($_POST['ticket']));
            
            $save = System::saveLetters($client_letter_subj, $client_letter, $reg_confirm_letter, $register_letter,
                $pass_reset_letter, $remind_letter1, $remind_letter2, $remind_letter3, $remind_sms1, $remind_sms2,
                $reg_sms, $ticket);

            if ($save) {
                $log = ActionLog::writeLog('settings', 'edit', 'save_letters', 1, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::redirectUrl('/admin/settings?cat=letters&success');
            }
        }
        
        
        if(isset($_POST['email_test'], $_POST['token']) && !empty($_POST['email_for_test']) && $_POST['token'] == $_SESSION['admin_token']){
            System::checkPermission('change_main_tunes');
            
            // проверить заполнение админсокго емейла + отправителя

            $error = null;
            if(empty($setting['admin_email'])) $error = 'Не забудьте указать email администратора<br />';
            if(empty($setting['support_email'])) $error .= 'Не забудьте указать email техподдержки<br />';
            
            if(empty($setting['sender_email'])) $error .= 'Не забудьте указать email отправителя (вкладка Почта)<br />';
            if(empty($setting['sender_name'])) $error .= 'Не забудьте указать имя отправителя (вкладка Почта)';
            
                
            // делаем отправку
            
            $email = trim(mb_strtolower($_POST['email_for_test']));
            $name = '';
            $subject = '# Тест отправки почты';
            $text = '<div style="width:100%; margin:0; padding:1em 0; background:#373A4C">
            <div style="width:80%; margin:1em auto; padding:1em 5%; background:#fff">
                <h1>Тестирование отправки почты</h1>
                <p>Здравствуйте! </p>
                <p>Если вы получили это письмо, значит почта на вашем сайте работает исправно.<br />
                Отвечать на это сообщение не нужно.</p>
                <p>Желаем успешной и продуктивной работы!<br />С уважением, команда School-Master.</p>
            </div>
            </div>';

            try {
                $send = Email::SendMessageToBlank($email, $name, $subject, $text, null, true);
            }catch (Exception $exception) {
                $error = '<h4>Ошибка отправки тестового сообщения</h4><h5><b>Возможные варианты решения проблемы:</b></h5><p>Проверьте правильность данных (smtp хост, порт, пользователь, пароль, шифрование)</p><h5><b>Текст ошибки:</b></h5><p>'.$exception->getMessage()."</p>";
            }

            $title = 'Email - тест';
            require_once(ROOT . '/template/admin/views/settings/email_test.php');
            return true;
                
            
        }

        if (isset($_GET['generateapi']) AND !isset($_SESSION['Api2Data'])) {
            $tokens = [
                'access_token' => apiTokens::generateToken(64),
                'refresh_token' => apiTokens::generateToken(64),
                'expire' => time() + apiBaseController::$timeToExpire,
            ];
            $_SESSION['Api2Data'] = $tokens;
            apiTokens::createToken($_SESSION['admin_user'], $tokens['access_token'], $tokens['refresh_token'], $tokens['expire']);
        }

        if (isset($_GET['clearmemcached'])) {
            cache::clear('memcached');
        }
        if (isset($_GET['clearfilecache'])) {
            cache::clear('file');
        }
        if (file_exists( ROOT . '/config/cache.php')) {
            $cachesettings = require(ROOT . '/config/cache.php');
        } else {
            $cachesettings['enable'] = 0;
            $cachesettings['type'] = 'file';
        }
        $filecacheStats = cache::getStats('file');
        if(class_exists('Memcached')){
            $memcacheStats = cache::getStats('memcached');
        }

        if (isset($_GET['cat'])) {
            if($_GET['cat'] == 'vid') {
                require_once(ROOT . '/template/admin/views/settings/index_vid.php');
            } elseif($_GET['cat'] == 'letters') {
                require_once(ROOT . '/template/admin/views/settings/index_letters.php');
            }
        } else {
            $title = 'Настройка - главная';
            require_once(ROOT . '/template/admin/views/settings/index.php');
        }
        return true;
    }
    
    
    
    
    
    // ОБСЛУЖИВАНИЕ
    public function actionServices()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $title = 'Обслуживание системы';
        require_once(ROOT . '/template/admin/views/settings/services.php');
        return true;
    }
    
    
    
    // SQL запрос
    public function actionSql()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['get_sql'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $sql = $_POST['sql'];
            $result = System::getSQL($sql);
        }
        
        $title = 'Произвольный SQL запрос';
        require_once(ROOT . '/template/admin/views/settings/sql.php');
        return true;
    }
    
    
    // Доп. Валюты
    public function actionCurrency()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $currency_list = Currency::getCurrencyList();
        $title = 'Настройка - доп.валюты';
        require_once(ROOT . '/template/admin/views/currency/currency.php');
        return true;
    }
    
    
    // Создать Доп валюту
    public function actionAddcurrency()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $currency_list = Currency::getCurrencyList();
        
        if(isset($_POST['add_currency'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $name = htmlentities($_POST['name']);
            $simbol = htmlentities($_POST['simbol']);
            $code = intval($_POST['code']);
            $tax = htmlentities($_POST['tax']);
            $tax = str_replace(",",".", $tax);
            $status = intval($_POST['status']);
            
            $add = Currency::addCurrency($name, $simbol, $code, $tax, $status);
            if($add) header("Location: /admin/settings/currency?success");
        }
        $title = 'Настройка - создание валюты';
        require_once(ROOT . '/template/admin/views/currency/add_currency.php');
        return true;
    }
    
    
    // Редактировать Валюту
    public function actionEditcurrency($id)
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        $currency = Currency::getCurrencyData($id);
        
        if(isset($_POST['edit_currency'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $name = htmlentities($_POST['name']);
            $simbol = htmlentities($_POST['simbol']);
            $code = intval($_POST['code']);
            $tax = htmlentities($_POST['tax']);
            $status = intval($_POST['status']);
            
            $edit = Currency::editCurrency($id, $name, $simbol, $code, $tax, $status);
            if($edit) header("Location: /admin/settings/currency?success");
        }
        $title = 'Настройка - редактирование валюты';
        require_once(ROOT . '/template/admin/views/currency/edit_currency.php');
        return true;
    }
    
    
    // Удалить валюту
    public function actionDelcurrency($id)
    {
        System::checkPermission('show_main_tunes', 'change_main_tunes');

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            
            $del = Currency::delCurrency($id);
            if($del) header("Location: /admin/settings/currency?success");
        }
    }
    
    
    // СТАТУСЫ ДЛЯ МЕНЕДЖЕРОВ
    public function actionCrmstatus()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $statuses = Order::getCRMStatusList();
        $title = 'Настройка - статус для менеджера';
        require_once(ROOT . '/template/admin/views/settings/crm_status.php');
        return true;
    }
    
    // Создать статус
    public function actionAddcrmstatus()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['addstatus'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $title = htmlentities($_POST['title']);
            $status_desc = htmlentities($_POST['status_desc']);
            
            $add = Order::addCRMStatus($title, $status_desc);
            if($add) header("Location: /admin/settings/crmstatus?success");
        }
        $title = 'Настройка - создание статуса для менеджера';
        require_once(ROOT . '/template/admin/views/settings/add_crm_status.php');
        return true;
    }
    
    
    
    // Изменить статус
    public function actionEditcrmstatus($id)
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        $status = Order::getCRMStatus($id);
        
        if(isset($_POST['editstatus'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $title = htmlentities($_POST['title']);
            $status_desc = htmlentities($_POST['status_desc']);
            
            $edit = Order::editCRMStatus($id, $title, $status_desc);
            if($edit) header("Location: /admin/settings/crmstatus?success");
        }
        $title = 'Настройка - изменение статуса для менеджера';
        require_once(ROOT . '/template/admin/views/settings/edit_crm_status.php');
        return true;
    }
    
    
    // Удалить статус
    public function actionDelcrmstatus($id)
    {
        System::checkPermission('show_main_tunes', 'change_main_tunes');

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            
            $del = Order::delCRMStatus($id);
            if($del) header("Location: ".$setting['script_url']."/admin/settings/crmstatus?success");
        }
    }
    
    
	
	// ВЫВОД КОНФИГА
    public function actionConfig() // admin/config
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $paramPath = ROOT . '/config/config.php';
        $params = include($paramPath);
        
        echo '<p><br />DB name: '.$dbname;
        echo '<br />preffix: '.$prefics;
        echo '<br />user: '.$user;
        echo '<br />pass: '.$password;
		echo '<br /><br /><< <a href="/admin">Dashboard</a></p>';
        echo phpinfo();
    }
	
	
    
    // ВЫВОД ЗАДАНИЙ КРОН
    public static function actionCronjobs()
    {
        System::checkPermission('show_main_tunes');
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $title = 'Настройка - вывод заданий CRON';
        require_once(ROOT . '/template/admin/views/settings/cron.php');
        return true;
    }
    
    
    
    // СПОСОБЫ ДОСТАВКИ
    public function actionDeliveryset()
    {
        System::checkPermission('show_payment_tunes');

        $name = $_SESSION['admin_name'];
        $velivery_methods = Order::getDeliveryMethods(0);
        $title = 'Дашбоард- варианты доставки';
        require_once(ROOT . '/template/admin/views/settings/delivery_var_list.php');
        return true;
    }
    
    
    // ДОБАВИТЬ СПОСОБ ДОСТАВКИ
    public function actionAdddeliverymethod()
    {
        System::checkPermission('show_payment_tunes', 'change_main_tunes');

        $name = $_SESSION['admin_name'];
        if(isset($_POST['addmethod'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){

            $name = htmlentities($_POST['name']);
            $ship_desc = htmlentities($_POST['ship_desc']);
            $status = intval($_POST['status']);
            $tax = intval($_POST['tax']);
            $when_pay = intval($_POST['when_pay']);
            
            $add = System::addDeliveryMethod($name, $ship_desc, $status, $tax, $when_pay);
            if($add) {
                System::setNotif(true, "Способ доставки `{$name}` создан!");
                System::redirectUrl("/admin/deliverysettings");
            }
        }
        $title = 'Дашбоард - варианты доставки - добавить способ';
        require_once(ROOT . '/template/admin/views/settings/delivery_var_add.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ СПОСОБ ДОСТАВКИ
    public function actionEditdeliverymethod($id)
    {
        System::checkPermission('show_payment_tunes', 'change_main_tunes');

        $name = $_SESSION['admin_name'];
        $id = intval($id);
        
        if(isset($_POST['editmethod'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){

            $name = htmlentities($_POST['name']);
            $ship_desc = htmlentities($_POST['ship_desc']);
            $status = intval($_POST['status']);
            $tax = intval($_POST['tax']);
            $when_pay = intval($_POST['when_pay']);
             
            $edit = System::editDeliveryMethod($id, $name, $ship_desc, $status, $tax, $when_pay);
            
            if($edit) {
                System::setNotif(true);
                System::redirectUrl("/admin/deliverysettings/edit/$id");
            }
        }
        
        $ship_method = System::getShipMethod($id);
        $title = 'Дашбоард - доставка - изменить способ';
        require_once(ROOT . '/template/admin/views/settings/delivery_var_edit.php');
        return true;
    }
    
    
    // УДАЛИТЬ СПОСОБ ДОСТАВКИ
    public function actionDeletedeliverymethod($id)
    {
        System::checkPermission('show_payment_tunes', 'change_main_tunes');

        $name = $_SESSION['admin_name'];
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            
            $del = System::deleteShipMethod($id);

            $name = isset($_GET['name'])
                ? "`" . htmlentities($_GET['name']) . "` "
                : '';
            
            System::setNotif($del ? true : false, $del ? "Способ доставки {$name}удален!" : "");
            System::redirectUrl("/admin/deliverysettings");
            
        }
    }
    
    
    // СПИСОК ПРАВ МЕНЕДЖЕРОВ
    public function actionPermissions()
    {
        System::checkPermission('show_perms');

        $name = $_SESSION['admin_name'];
        
        $levels = System::getACLlist();
        $title = 'Менеджеры - список прав';
        require_once(ROOT . '/template/admin/views/settings/acl.php');
        return true;
    }
    
    
    // ДОБАВИТЬ ПРАВА МЕНЕДЖЕРА
    public function actionAddpermissions()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_perms'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['addperm'], $_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_main_tunes']))
                System::redirectUrl("/admin/settings");
            
            $user_id = intval($_POST['user_id']);
            $perm = serialize($_POST['perm']);
            
            $write = System::AddPermiss($user_id, $perm);
            if($write) header("Location: /admin/permissions?success");
            
        }
        
        $levels = System::getACLlist();
        $title = 'Менеджеры - создать права';
        require_once(ROOT . '/template/admin/views/settings/acl_add.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ ПРАВА МЕНЕДЖЕРА
    public function actionEditpermissions($id)
    {
        $acl = $acl = self::checkAdmin();
        if(!isset($acl['show_perms'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['saveperm'])&& isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_main_tunes']))
                System::redirectUrl("/admin/settings");
            
            $perm = serialize($_POST['perm']);
            
            $upd = System::UpdPermiss($id, $perm);
            if($upd) header("Location: /admin/permissions/edit/$id?success");
            
        }
        
        $level = System::getACLbyID($id);
        $title = 'Менеджеры - изменить права';
        require_once(ROOT . '/template/admin/views/settings/acl_edit.php');
        return true;
    }
    
    
    // УДАЛИТЬ ПРАВА МЕНЕДЖЕРА
    public function actionDelpermissions($id)
    {
        $acl = $acl = self::checkAdmin();
        if(!isset($acl['show_perms'])) {
            header("Location: /admin");
            exit();   
        }
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_main_tunes']))
                System::redirectUrl("/admin/settings");
            
            $del = System::delACL($id);
            if($del) header("Location: ".$setting['script_url']."/admin/permissions");
        }
        
    }
    
    
    // НАСТРОЙКА ПЛАТЁЖЕК
    public function actionPayments()
    {        
        System::checkPermission('show_payment_tunes');

        $setting = System::getSetting();
        
        if(isset($_POST['install_payment']) && System::checkToken()){
            
            System::checkPermission('change_main_tunes');

            if(isset($_FILES["payment"]["tmp_name"]) && $_FILES["payment"]["size"] != 0 ){
                
                $tmp_name = $_FILES["payment"]["tmp_name"];
                $name = $_FILES["payment"]["name"];
                $dir = time();
                $tmp_path = ROOT . "/tmp/$dir";
                $template = $setting['template'];
                
                $zip = new ZipArchive(); //Создаём объект для работы с ZIP-архивами
                
                //Открываем архив archive.zip и делаем проверку успешности открытия
                if ($zip->open($tmp_name) === true) {
                    
                    $zip->extractTo($tmp_path); //Извлекаем файлы в указанную директорию
                    $zip->close(); //Завершаем работу с архивом
                    $message = '<div class="admin_message">Расширение успешно установлено</div>';
                } else {
                    $message = '<div class="admin_warning">Ошибка при установке</div>';
                }
                
                // Подключить файл params.php
                if(include (ROOT . "/tmp/$dir/install.php")){
                    
                    // Сделать запись в БД
                    $install = System::installPayment($name, $title, $enable, $params, $desc);
                    if($install){
                            // Создать папки
                        if($folders != false){
                            foreach($folders as $folder){
                                $path = ROOT.'/'.$folder[0];
                                mkdir($path);
                            }
                        }
                        // Переместить файлы согласно инструкции в install.php
                        foreach($files as $file){
                            $old = $file[0];
                            $new = $file[1];
                            rename($tmp_path.$old, $new);
                        }
                        
                    } else {
                        $message = '<div class="admin_warning">Расширение уже установлено</div>';
                    }
                    
                    
                } else {
                    $message = '<div class="admin_warning">Не найден файл установки</div>';
                }
                
            }
        }
        
        $payments = Order::getPaymentsForAdmin();
        
        $title = 'Дашбоард - список платежных модулей';
        require_once(ROOT . '/template/admin/views/settings/payments.php');
        return true;
    }
    
    
    
    public function actionEditpayments($id)
    {
        System::checkPermission('show_payment_tunes');

        $id = intval($id);
        $setting = System::getSetting();
        
        if(isset($_POST['savepayments']) && System::checkToken()){
            System::checkPermission('change_main_tunes');
            
            $title = $_POST['title'];
            $public_title = $_POST['public_title'];
            $sort = $_POST['sort'];
            $status = $_POST['status'];
            $payment_desc = $_POST['payment_desc'];

            // ЗАГРУЗКА СЕРТИФИКАТА (type='file name='cert') 
            if (isset($_FILES['cert'], $_POST['file_dir'])
                && $load_path = System::uploadPostFile('cert', $_POST['file_dir'], false)
            ) {
                $_POST['params']['cert_path'] = array_pop(explode('/', $load_path));
            }

            // ЗАГРУЗКА КЛЮЧА (type='file' name='key') 
            if (isset($_FILES['key'], $_POST['file_dir'])
                && $load_path = System::uploadPostFile('key', $_POST['file_dir'], false)
            ) {
                $_POST['params']['key_path'] = array_pop(explode('/', $load_path));
            }

            $params = base64_encode(serialize($_POST['params']));
            $edit = Order::EditPayments($id, $title, $public_title, $sort, $status, $payment_desc, $params);

            if($edit){
                System::setNotif('save');
                System::redirectUrl("/admin/paysettings/{$id}");
            }
        }
        
        $payment = Order::getPaymentDataForAdmin($id);

        if(!$payment){
            System::setNotif('error', "Платежный модуль (ID {$id}) не найден.");
            System::redirectUrl("/admin/paysettings");
        }
        if(!file_exists(ROOT . '/payments/'. $payment['name'] . '/params.php')){
            System::setNotif('error', "Отсутствуют файлы платёжного модуля.");
            System::redirectUrl("/admin/paysettings");
        }

        $title = 'Дашбоард - редактирование платёжного модуля';
        require_once(ROOT . '/template/admin/views/settings/edit_payment.php');
        return true;
    }
    
    
    
    
    public function actionDeletepayments($id)
    {
        System::setNotif(false, "Удаление модуля недоступно.");
        System::redirectUrl("/admin/paysettings/{$id}");

        System::checkPermission('show_payment_tunes', 'change_main_tunes');
        
        if(System::checkToken($_GET['token'])){

            $del = System::deletePayment($id);

            if($del){
                $name = isset($_GET['name']) ? $_GET['name'] . ' ' : '';

                System::setNotif('delete', "Платежный модуль {$name}успешно удален.");
                System::redirectUrl("/admin/paysettings");
            }else{
                System::setNotif('error', "Не удалось удалить модуль!");
                System::redirectUrl("/admin/paysettings/{$id}");
            }
        }
    }


    // ОБНОВИТЬ CMS
    public function actionCMSUpdate()
    {
        // TODO SM-1705 Добавить проверку на использование не стандартного шаблона, если не стандарт например КАЙНО то не обновлять/предупреждать
        if (isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $acl = self::checkAdmin();
            if (!isset($acl['show_ext_tunes']) || !isset($acl['change_main_tunes'])) {
                header("Location: /admin");
                exit();
            }

            $res = System::curl('https://lk.school-master.ru/cmsupdate.php', array('key' => 'flS16H5PgjcI'), 60);
            if ($res['info']['http_code'] != 200 || !strlen($res['content'])) {
                System::addError('Ошибка при загрузке обновления');
                exit;
            }

            $tmp_path = ROOT . '/tmp/' . time();
            if (file_exists($tmp_path)) {
                System::removeDirectory($tmp_path);
            }

            mkdir($tmp_path);
            $tmp_zip =  $tmp_path . '/updatecms.zip';
            file_put_contents($tmp_zip, $res['content']);

            $result = System::installExtensions($tmp_zip, 'updatecms.zip', 'update');

            System::hasSuccess() ? System::showSuccess() : System::showError();
            exit;
        }
    }
    
    // ПОЛУЧИТЬ СПИСОК РАСШИРЕНИЙ
    public function actionExtensions()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_ext_tunes'])) {
            header("Location: /admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        cache::clearCurrentDriver();
        if (isset($_POST['install_ext']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if(!isset($acl['change_main_tunes'])) {
                header("Location: /admin");
                exit();
            }

            // Переместить архив в папку tmp
            // Распаковать архив
            if(isset($_FILES["extens"]["tmp_name"]) && $_FILES["extens"]["size"] != 0 ) {
                $tmp_name = $_FILES["extens"]["tmp_name"];
                $name = $_FILES["extens"]["name"];
                $result = System::installExtensions($tmp_name, $name);
            }
        }
        
        $type = 'system';
        if(isset($_GET['type'])){
            if($_GET['type'] == 'template') $type = 'template';
        }
        

        $exts = System::getAllExtensions($type);
        $title = 'Дашбоард - настройки - расширения';
        require_once(ROOT . '/template/admin/views/settings/extensions.php');
        return true;
    }
    
    
    // СПИСОК ВСЕХ РАСШИРЕНИЙ
    public function actionAllextensions()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_ext_tunes'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $system = 'all';
        $exts = System::getAllExtensions('all');
        
        echo '<p>Список установленных расширений</p><p>';
        if($exts){
            foreach($exts as $item){
                echo $item['name'].' - '.$item['title']. ' - '.$item['type'].' ver: '.$item['version'].'<br />';
            }
        }
        echo '</p>';
    }
    
    
    
    // УДАЛЕНИЕ ОБЛОЖЕК 
    public function actionDelimg($id)
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_POST['del_img']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            if(!isset($acl['change_products'])){
                header("Location: /admin");
                exit();
            }
            $file = $_POST['path'];
            $page = $_POST['page'];
            $table = htmlentities($_POST['table']);
            $name = $_POST['name'];
            $where = $_POST['where'];
            
            // Удалить файл физически, пока убрали до внедрения нормального файл-менеджера во всех местах
            // Потому-что при копированни потом когда люди хотят изменить картинку она удалется и из первичного элемента
            // с которого происходит копирование
            // if(file_exists($file)){
            //    unlink($file);   
            //}
            
                // Удалить из БД
                $db = Db::getConnection();  
                $sql = 'UPDATE '.PREFICS.$table.' SET '.$name.' = "" WHERE '.$where.' = '.$id;
                $result = $db->prepare($sql);
                $result->bindParam(':name', $name, PDO::PARAM_STR);
                $result->bindParam(':title', $title, PDO::PARAM_STR);
                $result->bindParam(':alias', $alias, PDO::PARAM_STR);
                if($result->execute()){
                    header("Location: /$page?success");
                } else exit('Error delete image');
                }
            
    }
    
    
    
    // BACKUPS
    public static function actionBackup()
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_backups'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_GET['file'])){
            header("Location: /tmp/".$_GET['file']);
        }
        
        if(isset($_POST['backup']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_main_tunes']))
                System::redirectUrl("/admin/settings");
            
            $backup = System::createBackup();
            if($backup) header("Location: ".$setting['script_url']."/admin/backup?success&file=$backup" );
            else echo 'Error!';
            
        }
        $title = 'Бэкап';
        require_once(ROOT . '/template/admin/views/settings/backup.php');
        return true;
    }
    
    
    
    
    // ПУНКТЫ МЕНЮ
    public function actionMenuitems()
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $setting_main = System::getSettingMainpage();
        $setting_main2 = System::getSettingMainpageBySecondId();
        $menu_items = System::getMenuItems();
        $user_menu = json_decode($setting_main['user_menu'], 1);
        
        if(isset($_POST['user_menu_save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $user_menu = json_encode($_POST['user_menu']);
            $save = System::saveUserMenu($user_menu);
            if($save) header("Location: /admin/menuitems?success");
        }
        $title = 'Меню - пункты';
        require_once(ROOT . '/template/admin/views/settings/menuitems.php');
        return true;
    }
    
    
    // СОЗДАТЬ ПУНКТ МЕНЮ
    public function actionAddmenuitem()
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $menu_items = System::getMenuItems();
        if(isset($_GET['type'])) $type = htmlentities($_GET['type']);
        
        if(isset($_POST['addmenuitem']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_main_tunes']))
                System::redirectUrl("/admin/settings");

            $name = htmlentities($_POST['name']);
            $parent_id = intval($_POST['parent_id']);
            $url = $_POST['url'];
            $sort = intval($_POST['sort']);
            $status = intval($_POST['status']);
            $type = htmlentities($_POST['type']);
            $menu_id = intval($_POST['menu_id']);
            $title = htmlentities($_POST['title']);
            $new_window = intval($_POST['new_window']);
            $sitemap = intval($_POST['sitemap']);
            $changefreq = htmlentities($_POST['changefreq']);
            $visible = intval($_POST['visible']);
            $priority = htmlentities($_POST['priority']);
			
            $add = System::addMenuItem($name, $url, $sort, $status, $type, $menu_id, $title, $new_window, $parent_id, $sitemap, 
            $changefreq, $visible, $priority);
            if($add) header("Location: ".$setting['script_url']."/admin/menuitems?success");
            
        }
        $title = 'Меню - создать новый';
        require_once(ROOT . '/template/admin/views/settings/addmenuitem.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ ПУНКТ МЕНЮ
    public function actionEditmenuitem($id)
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $menu_items = System::getMenuItems();
        $type = isset($_GET['type']) ? htmlentities($_GET['type']) : null;

        if (isset($_POST['savemenuitem']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_main_tunes'])) {
                System::redirectUrl('/admin');
            }

            $name = htmlentities($_POST['name']);
            $parent_id = intval($_POST['parent_id']);
            $url = $_POST['url'];
            $sort = intval($_POST['sort']);
            $status = intval($_POST['status']);
            $menu_id = intval($_POST['menu_id']);
            $title = htmlentities($_POST['title']);
            $new_window = intval($_POST['new_window']);
            $sitemap = intval($_POST['sitemap']);
            $changefreq = htmlentities($_POST['changefreq']);
            $visible = intval($_POST['visible']);
            $priority = htmlentities($_POST['priority']);
            $show_in_order_pages = intval($_POST['show_in_order_pages']);
            $showByGroup = intval($_POST['showByGroup']);
            $showGroups = $_POST['showGroups'] ? json_encode($_POST['showGroups']) : null;

            $edit = System::editMenuItem($id, $name, $url, $sort, $status, $menu_id, $title, $new_window, $parent_id,
                $sitemap, $changefreq, $visible, $priority, $show_in_order_pages, $showByGroup, $showGroups
            );

            if ($edit) {
                if (isset($_POST['training'])) {
                    $tr_save = Training::SaveMPSettings($_POST['training']['params']);
                }

                System::redirectUrl("/admin/menuitems/edit/$id?type=$type&success");
            }
        }

        $item = System::getMenuItem($id);

        $title = 'Меню - изменение пункта';
        require_once(ROOT . '/template/admin/views/settings/editmenuitem.php');
        return true;
    }
    
    
    // УДАЛИТЬ ПУНКТ МЕНЮ
    public function actionDelmenuitem($id)
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            if(!isset($acl['change_main_tunes']))
                System::redirectUrl("/admin/settings");
            
            $del = System::delMenuItem($id);
            if($del) header("Location: ".$setting['script_url']."/admin/menuitems?success");
            else header("Location: ".$setting['script_url']."/admin/menuitems?fail");
        }
    }


    public function actionMigrationIndex() {
        $acl = self::checkAdmin();
        $version = preg_replace('/[^0-9]/', '', CURR_VER);


        if (isset($_POST['runMigrations'])) {//Запустить миграции
            $migrations = new \Migrations\migrationHandler($version, "migrate", "web", false);
            $result = $migrations->runTasks();
            $_SESSION['lastMigrationResult'] = $result;

            System::redirectUrl("/admin/settings/migrations?migrationresult=".boolval($result));
        }


        if (isset($_GET["migrationresult"])) {//Были выполнены миграции
            $migrationResult = $_SESSION['lastMigrationResult'] ?? false;
        } else {
            unset($_SESSION['lastMigrationResult']);
        }

        $handler = new \Migrations\migrationHandler($version, "check", "web", false);
        $dbCheckResult = $handler->runTasks();

        $completedMigrationsCount = MigrationsTable::getCount();

        $title = 'Проверка актуальности базы данных';
        require_once(ROOT . '/template/admin/views/settings/migrations.php');
        return true;
    }


    public function actionSettingsChecker() {

        $result = SettingsChecker::run();

        $title = 'Проверка настроек системы';
        require_once(ROOT . '/template/admin/views/settings/settings_checker.php');
        return true;
    }


    public function actionChangeExtStatus($id) {
        $acl = self::checkAdmin();

        $setting = System::getSetting();
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $status = intval($_REQUEST['status']);

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $res = System::changeExtStatus($id, $status);

            if ($res) {
                header("Location: /admin/extensions");
            }
        }
    }

    public function actionChangePaymentMethodStatus($id) {
        $acl = self::checkAdmin();

        $id = intval($id);
        $status = intval($_REQUEST['status']);
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $res = System::changePaymentStatus($id, $status);

            if ($res) {
                header("Location: /admin/paysettings");
            }
        }
    }


    // ДЛЯ ТЕСТА И СИСТЕМНЫХ ОПЕРАЦИЙ
    public function actionTest()
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();



        //require_once(ROOT . '/template/admin/views/-makeup.php');

    }
}