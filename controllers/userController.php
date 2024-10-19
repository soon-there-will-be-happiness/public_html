<?php defined('BILLINGMASTER') or die;


class userController extends baseController {
    
    
    public function actionLogin()
    {
        if ($this->settings['enable_cabinet'] == 0 && $this->settings['enable_aff'] == 0) {
            ErrorPage::return404();
        }

        $email = '';
        $userId = User::isAuth();

        if ($userId) {
            System::redirectUrl("/");
        }
        
        if (isset($_POST['enter']) && !empty($_POST['email']) && !empty($_POST['pass'])) {
            $email = htmlentities(trim($_POST['email']));
            $pass = trim($_POST['pass']);
            $errors = false;
            $user = User::checkUserData($email, $pass);

            if ($user == false) {
                $errors[] = 'Неверный логин или пароль';
            } else {
                $auth = User::Auth($user['user_id'], $user['user_name']);
                
                if ($auth) {
                    $is_remember = isset($_POST['remember_me']) ? true : false;
                    Remember::saveData($user, $is_remember);
                }

                $emnam = isset($_COOKIE['emnam']) ? $_COOKIE['emnam'] : "==";
                $emnam = explode("=", $emnam);

                if($emnam[0] != $email) // актуализация
                    $emnam[0] = $email;

                setcookie('emnam', implode("=", $emnam), time() + 7776000, '/'); // сохр на 3 мес

                //Проверка на первый вход и редирект
                if (!isset($user['last_visit'])) {
                    $settings = System::getSetting(true);
                    $params = json_decode($settings['params'], true);
                    if (isset($params['first_login_redirect']) && $params['first_login_redirect'] != '') {
                        System::redirectUrl($params['first_login_redirect']);
                    } else {
                        $url = User::redirectFromEnter($this->settings['login_redirect']);
                        System::redirectUrl($url);
                    }
                } else {
                    $url = User::redirectFromEnter($this->settings['login_redirect']);
                    System::redirectUrl($url);
                }
            }
        }

        $this->setSEOParams('Вход на сайт');

        $all_widgets = Widgets::getWidgets($this->view['is_page'], $userId);
        $sidebar = Widgets::RenderWidget($all_widgets, 'sidebar');
        $this->view['main_content_class'] = !$sidebar ? 'content-lk' : '';
        $content_class = $sidebar ? 'content-wrap' : '';

        $this->setViewParams('auth', 'users/login.php', false, null, 'authorization-page',
            $content_class, true, $this->settings['in_head'], $this->settings['in_body']
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    public function actionForgot ()
    {
        if ($this->settings['enable_cabinet'] == 0 && $this->settings['enable_aff'] == 0) {
            ErrorPage::return404();
        }
        
        if (!User::isAuth()) {
            $this->setSEOParams('Вспомнить пароль');
            $now = time();
            
            if (isset($_POST['forgot'])) {
                $email = trim(strtolower(htmlentities($_POST['email'])));
                
                // Найти юзера с данным емейлом
                $data = User::getUserDataByEmail($email);
                
                // если дата в recovery меньше чем 1 час, то пишем что вы уже меняли пароль           
                if ($data) {
                    
                    if(!empty($data['recovery'])) {
                        
                        $recovery = json_decode($data['recovery'], 1);
                        
                        if($now - $recovery['response'] < 600){
                            ErrorPage::returnError('<h2 style="text-align:center; padding:2em 0; color:#555">Вы уже восстанавливали пароль менее 10 минут назад, проверьте почту, в т.ч. папку СПАМ.<br /><a href="/">Вернуться назад</a></h2>', '');
                        }
                    }
                    
                    $recovery = array();
                    $recovery['response'] = $now;
                    $recovery['update'] = 0;
                    
                    $recovery = json_encode($recovery);
                    
                    User::updateRecovery($data['user_id'], $recovery);
                    
                    // Отправить письмо со ссылкой для смены пароля
                    // ссылка/lostpass?email=[email]&key=[USER_ID]+[Recovery]+[SECRET_KEY]
                    $key = md5($data['user_id']."{$now}{$this->settings['secret_key']}");
					
					if (isset($_SESSION['lostpass'])) unset($_SESSION['lostpass']);
                    
                    $send = Email::LostYourPass($data['email'], System::Lang('LETTER_LOSTPASS'), $key);
                    if ($send) {
                        header("Location: /forgot?mess=ok");
                    }
                } else {
                    $mess = 'Пользователя с таким e-mail не существует';
                }
            }

            $this->setViewParams('lk', 'users/forgot.php', false,
                null, 'invert-page forgot-page'
            );

            $this->view['noindex'] = true;
            require_once ("{$this->template_path}/main.php");
        } else {
            System::redirectUrl('/lk');
        }
        return true;
    }
    
    
    
    public function actionChangepass() {
        if ($this->settings['enable_cabinet'] == 0 && $this->settings['enable_aff'] == 0) {
            ErrorPage::return404();
        }
        
        if (isset($_GET['email']) && isset($_GET['key'])) {
            $email = trim(strtolower(htmlentities($_GET['email'])));
            $now = time();
            $data = User::getUserDataByEmail($email);
            $recovery = [];
            
            if ($data) {
                if(!empty($data['recovery'])) {
                    $recovery = json_decode($data['recovery'], 1);
                    
                    if ($recovery['update'] != 0){
                        ErrorPage::returnError('<h2 style="text-align:center; padding:2em 0; color:#555">Вы уже восстановили пароль, проверьте почту, в т.ч. папку СПАМ.<br /><a href="/">Вернуться назад</a></h2>', '');
                    }
                    
                    $recovery['update'] = $now;
                    $time = $recovery['response'];
                    $new_recovery = json_encode($recovery);
                } else {
                    ErrorPage::returnError('Error 444');
                }
                
                User::updateRecovery($data['user_id'], $new_recovery);
                
                $key = md5("{$data['user_id']}{$time}{$this->settings['secret_key']}");
                if ($key != $_GET['key']) {
                    ErrorPage::returnError('O-pps. Error 303');
                }
                
                // Создаём пароль клиенту
                $chars="abcdefghigklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ1234567890";
                $max = 8;
                $size = strlen($chars)-1;
                $password = null; 
        		while ($max--) {
                    $password.=$chars[mt_rand(0,$size)];
                }

                // Пишем в базу новый пароль
                $change = User::ChangePass($data['user_id'], $password);
                $letter = System::Lang('LETTER_CHANGE_PASS');
                
                // Отсылаем юзеру $password
                Email::ChangePassOk($data['email'], $password, $letter);
                
                if ($change) {
                    $this->view['noindex'] = true;
                    $this->setViewParams('lk', 'users/forgot_ok.php', false,
                        null, 'invert-page'
                    );
                    $this->setSEOParams('Вспомнить пароль');
                    require_once ("{$this->template_path}/main.php");
                    return true;
                }
            } else {
                ErrorPage::returnError('Ошибка: неверные параметры.');
            }
        } else {
            header("Location: /");
        }
        return true;
    }


    /**
     *
     */
    public function actionLogout() {
        User::userLogOut();
        if (isset($_COOKIE['sm_remember_me'])) {
            setcookie("sm_remember_me", '', time() - 86400 * 30, '/', Helper::getDomain()); // 30 дней
        }
        System::redirectUrl('/');
    }


    /**
     * REGISTRATION
     */
    public function actionRegistration() {

        if ($this->settings['enable_registration'] == 0) {
            ErrorPage::return404();
        }

        if (User::isAuth()) {
            System::redirectUrl('/');
        }

		$timer = $now = time();
        $cookie = $this->settings['cookie'];
        $custom_fields = CustomFields::getFields(CustomFields::PARSE_TYPE_REGISTRATION);

        if (isset($_POST['save']) && isset($_COOKIE["$cookie"]) && isset($_POST['time']) && isset($_POST['sign']) && isset($_POST['email'])) {
			if (!empty($_POST['fio'])) {
                ErrorPage::returnError('Регистрация отключена');
            }

            //Если включена капча, то проверка
            $reCaptcha = json_decode($this->settings['reCaptcha'], true);
            if ($reCaptcha['enable'] == 1) {
                $reCaptcha = new recaptcha($_POST['g-recaptcha-response'], $reCaptcha['reCaptchaSecret'], $reCaptcha['minimalScoreVerifyValue']);
                if (!$reCaptcha->checkCaptcha()) {
                    ErrorPage::returnError('you are robot!');
                }
            }

			$timer = intval($_POST['time']);
			$check = $now - $timer;
			
			if ($check < 2) {
                ErrorPage::returnError('Регистрация отключена');
            }

			$sign = md5(intval($_POST['time']).'+'.$this->settings['secret_key']);
			if ($sign != $_POST['sign']) {
                ErrorPage::returnError('Error 899');
            }
			
			$email = trim(strtolower(htmlentities($_POST['email'])));
            $name = trim($_POST['name']);
			if (strpos($name, ':/')) {
                ErrorPage::returnError('Error 898 SQL syntax is wrong');
            }

            $surname = isset($_POST['surname']) ? trim(htmlentities($_POST['surname'])) : '';
            $patronymic = isset($_POST['patronymic']) ? trim(htmlentities($_POST['patronymic'])) : '';
            $phone = htmlentities($_POST['phone']);
            $pass = htmlentities($_POST['pass']);
            $confirm_pass = htmlentities($_POST['confirm_pass']);
            $order_id=  htmlentities($_POST['order_id']);
            if ($email && $name && $phone && $pass && $confirm_pass) {
                if ($pass == $confirm_pass) {
                    if (strlen($pass) >= 6) {
                        $user_exists = User::searchUser($email);
                        if ($user_exists) {
                            User::addError('Пользователь с данным e-mail уже существует');
                        } else {
                            $hash = password_hash($pass, PASSWORD_DEFAULT);
                            $reg_date = time();
                            $user_param = "$reg_date;0;;";
                            $is_child=ToChild::searchByOrderId($order_id);

                            User::addError(    $order_id);

                            if($order_id){
                                $is_child=ToChild::searchByOrderId($order_id);
                                if($is_child!=false){
                                    $order=Order::getOrder($order_id);
                                    $user = User::AddNewClient($name, $email, $phone,$order['client_city'], $order['client_address'], $order['client_index'], 'user',true,$reg_date, 'custom', $order['visit_param'],0, $hash,$pass,
                                    false,$this->settings['register_letter'], 0, null, $order['partner_id'], $surname, $patronymic,
                                    null, null, null, null, true);
                                    ToChild::close($order_id,$email);
                                    if ($user['channel_id'] != 0 && $user['channel_id'] != $order['channel_id']) {
                                        Order::updateChannel_id($order['order_id'], $user['channel_id']);
                                    }
                                    $items = Order::getOrderItems($order['order_id']);
                                    foreach($items as $item) {
                                        $product = Product::getProductDataForSendOrder($item['product_id']);
                                        if ($product['manager_letter'] != null) {
                                            $manager_letter = unserialize(base64_decode($product['manager_letter']));
                                            if (isset($manager_letter['email_manager']) && !empty($manager_letter['email_manager'])) {
                                                $subj_manager = isset($manager_letter['subj_manager']) ? $manager_letter['subj_manager'] : null;
                                                $letter_manager = isset($manager_letter['letter_manager']) ? $manager_letter['letter_manager'] : null;
                                                $send_custom = Email::sendCustomLetterForManager($manager_letter['email_manager'],
                                                    $subj_manager, $letter_manager, $order
                                                );
                                            }
                                        }
                                        if ($product['del_group_id']) {
                                            User::deleteUserGroupsFromList($user['user_id'], $product['del_group_id']);
                                        }
                                        // Добавление групп для пользователя при рассрчоке и БЕЗ
                                        if ($product['group_id'] != 0 && ($order['installment_map_id'] == 0 || $product['installment_addgroups'] == 0)) {
                                            $add_groups = explode(",", $product['group_id']);
                                            foreach ($add_groups as $group) {
                                                User::WriteUserGroup($user['user_id'], $group);
                                            }
                                        }

                                        $training = System::CheckExtensension('training', 1);
                                        if ($training) {
                                            $user_groups = $user['user_id'] ? User::getGroupByUser($user['user_id']) : false;
                                            $user_planes = $user['user_id'] ? Member::getPlanesByUser($user['user_id'], 1) : false;
                                            if ($user_groups || $user_planes) {
                                                $filter = [
                                                    'user_groups' => $user_groups,
                                                    'user_planes' => $user_planes
                                                ];
                                                $training_list = $user['user_id'] ? Training::getTrainingList(null, null, $filter, null) : null;
                                                if ($training_list) {
                                                    foreach($training_list as $training) {
                                                        if ($training['curators_auto_assign']==1) {
                                                            Order::AssignUserToCurator($user['user_id'], $training);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        $subscription_id=null;
                                        $membership = System::CheckExtensension('membership', 1);
                                        if ($membership && $user && !empty($product['subscription_id']) && ($order['installment_map_id'] == 0 )) {
                                            Member::renderMember($product['subscription_id'], $user['user_id'], 1, $subscription_id, $order['subs_id']);
                                        }

                                    }
                                    if ($user) {
                                        if (isset($_SESSION['confirm_phone'])) {
                                            User::confirmPhone($user['user_id'], $phone);
                                        }
        
                                        Email::SendRegConfirm($name, $email, $user['reg_key'], $pass, $this->settings['reg_confirm_letter']);
                                        $_SESSION['reg_status'] = 1;
        
                                        if ($custom_fields) {
                                            $custom_fields_data = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];
                                            CustomFields::saveUserFields($user['user_id'], null, $custom_fields_data);
                                        }
        
                                        System::redirectUrl('/lk/registration');
                                    }


                                }else{
                                    User::addError('Error1');
                                }
                            }else{

                                User::addError('Error2');
                         /*   $user = User::AddNewClient($name, $email, $phone, null, null, null,
                                'user',  null, $reg_date, 'custom', $user_param, 0, $hash,
                                $pass, false, $this->settings['register_letter'], 0, null, null,
                                $surname, $patronymic, null, null, null, null, true
                            );*/
                        }

                       
                        }
                    } else {
                        User::addError('Пароль должен содержать не меньше 6 символов');
                    }
                } else {
                    User::addError('Пароли не совпадают, попробуйте ввести еще раз');
                }
            }
        }

        $this->setSEOParams('Регистрация');
        $this->setViewParams('lk', 'users/registration.php', false,
            null, 'registration-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }



    /**
     * ПОДТВЕРЖДЕНИЕ РЕГИСТРАЦИИ
     * @param $req_key
     */
    public function actionRegistrationConfirm($req_key) {
        $user = User::getUserDataToRegkey($req_key);
        if ($user) {
            $res = User::updateUserStatus($user['user_id'], 1);
            if ($res) {
                $_SESSION['reg_status'] = 2;
                header("Location: /lk/registration");
            }
        } else {
            header("Location: /");
        }
    }


    /**
     * ПРОВЕРКА НАЛИЧИЯ СЕССИИ
     */
    public function actionCheckSession() {
        $resp = ['status' => true];
        if (!$this->settings['multiple_authorizations'] && UserSession::userLogOut()) {
            $resp['status'] = false;
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode($resp);
    }

    /**
     * Автовход по ссылке
     * параметры запроса:
     * GET: email
     * GET: token
     * GET: redirectlink (не обязательно)
     * @return bool
     */
    public function actionAutoLogin() {// ссылка вида:  /autologin?email=email&token=token&redirect=/trainings
        $email = $_GET['email'] ?? die('нет email');
        $token = $_GET['token'] ?? die('нет токена');
        $redirect = $_GET['redirect'] ?? "/";

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ErrorPage::returnError('Неверный формат email', null, 401);
        }

        $user = User::getUserDataByEmail($email);

        if (!$user) {
            ErrorPage::returnError("Пользователя не существует", null, 401);
        }

        if (!isset($user['auto_login'])) {
            ErrorPage::returnError("Нет данных для входа по ссылке", null, 401);
        }

        $autoLoginData = json_decode($user['auto_login'], true);
        if (!is_array($autoLoginData)) {
            ErrorPage::returnError("Нету данных для входа по ссылке", null, 401);
        }

        if ($autoLoginData['token'] != $token) {
            ErrorPage::returnError("Неверный токен", null, 401);
        }

        $auth = User::Auth($user['user_id'], $user['user_name']);

        if (isset($auth)) {
            Remember::saveData($user, true);
        }

        //обновить данные
        User::updateUserToken($user['user_id'], json_encode([
            'token' => $autoLoginData['token'],
            'last_use' => time(),
            'create_date' => $autoLoginData['create_date'],
        ]));

        System::redirectUrl($redirect);

        return true;
    }
}