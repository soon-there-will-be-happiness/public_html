<?php defined('BILLINGMASTER') or die; 


class affController extends baseController {
    
    
    // РЕДИРЕКТ НА ВНЕШНИЙ ЛЕНДИНГ 
    public function actionExtland($product_id, $partner_id) {
        $cookie = $this->settings['cookie'];
        $aff_set = unserialize(System::getExtensionSetting('partnership'));
        $aff_life = intval($aff_set['params']['aff_life']);
        
        $product = Product::getProductData($product_id);
        
        if ($product) {
            $product_name = $product['product_name'];
            
            $verify = Aff::AffHits($partner_id);
            if ($verify) {
                $url = $product['external_url'];
    
                if (!empty($url)) {
                    setcookie("aff_$cookie", $partner_id, time()+3600*24 * $aff_life, '/');
                    System::redirectUrl($url);
                } else {
                    $subject = 'Пустая ссылка на лендинг';
                    $text = "<p>School-Master обнаружил пустую ссылку на внешний лендинг у продукта $product_name</p><p>Проверьте настройки продукта.</p>";
                    Email::SendMessageToBlank($this->settings['admin_email'], 'BM', $subject, $text);
                }
            }
        } else {
            require_once ("{$this->template_path}/404.php");
        }
    }

    /**
     * СТРАНИЦА ПАРТНЁРА В ЛК
     */
    public function actionAff()
    {


        $extension = System::CheckExtensension('partnership', 1);
        if (!$extension) {
            require_once ("{$this->template_path}/404.php");
        }

        // Проверка авторизации
        $userId = User::checkLogged();

        // Данные юзера
        $user = User::getUserById($userId);
        if ($user['is_partner'] != 1) {
            System::redirectUrl('/lk');
        }

		if (isset($_POST['save_postback'])) {
            $postback = json_encode($_POST['postback']);
            $fb_pixel = isset($_POST['fb_pixel']) ? json_encode($_POST['fb_pixel']) : null;
            $add = Aff::writePostback($userId, $postback, $fb_pixel);
            if ($add) {
                System::redirectUrl('/lk/aff?success');
            }
        }

        if (isset($_POST['addlinktg'])) {
            $user_id =intval($_POST['user_id']);
            $product_id =intval($_POST['product_id']);
            $telegram = !empty($_POST['telegram']) ? htmlentities($_POST['telegram']) : null;
            if (!is_null($telegram) && !is_null($product_id) && !is_null($user_id)){
                TelegramProduct::addOrUpdate($user_id, $product_id , $telegram );
            }
        }
       



        if (isset($_POST['save_req'])) {
            if (isset($_POST['req'])) {
                foreach($_POST['req'] as $key => $value) {
                    if ($key != 'rs') {
                        $req[$key] = htmlentities($value);
                    } else {
                        $req[$key]['rs'] = htmlentities($value['rs']);
                        $req[$key]['off_name'] = htmlentities($value['off_name']);
                        $req[$key]['bik'] = htmlentities($value['bik']);
                        $req[$key]['inn'] = htmlentities($value['inn']);
                        $req[$key]['rs2'] = htmlentities($value['rs2']);
                        $req[$key]['name'] = htmlentities($value['name']);
                        $req[$key]['fio'] = htmlentities($value['fio']);
                    }
                }
                
                $req = serialize($req);
            } else {
                $req = null;
            }
            
            $addreq = Aff::UpdateReq($userId, $req);
            if ($addreq) {
                System::redirectUrl('/lk/aff?success');
            }
        }
        
        if (isset($_POST['addlink']) && !empty($_POST['url'])) {
            $url = filter_var($_POST['url'], FILTER_SANITIZE_STRING);
            $desc = htmlentities($_POST['desc']);
            
            $addlink = Aff::AddPartnerShortLink($userId, $url, $desc);
            if ($addlink) {
                System::redirectUrl('/lk/aff?success');
            }
        }
        
        if (isset($_POST['deletelink'])) {
            $link_id = intval($_POST['link_id']);
            $del = Aff::deleteShortLink($link_id);
            if ($del) {
                System::redirectUrl('/lk/aff?success');
            }
        }
        
        $links = Aff::getPartnerLinks();
        $short_links = Aff::getShortLinkByPartner($userId);
        
        // настройки партнёрки
        $params = unserialize(System::getExtensionSetting('partnership'));
        
        $months = [
            "Декабрь", "Январь", "Февраль", "Март",
            "Апрель", "Май", "Июнь",
            "Июль", "Август", "Сентябрь",
            "Октябрь", "Ноябрь", "Декабрь"
        ];

        // Реквизиты партнёра
        $req = Aff::getPartnerReq($userId);
		$postbacks = json_decode($req['postbacks'], true);
        $fb_pixel = json_decode($req['fb_pixel'], true);
        $paid = isset($_GET['all']) ? null :1;
        $total = Aff::getUserTransactData($userId, 'aff'); // Всего заработано

        if (isset($params['params']['return_period']) && $params['params']['return_period'] > 0) {
            $date = time() - ($params['params']['return_period'] * 86400);
            $total2 = Aff::getUserTransactData($userId, 'aff', $date);
        }

        $hits = Aff::contHitsToPartner($userId);
        $last_pay = Aff::getParnerLastPay($userId, 'aff');
        $orders = Aff::getHistoryTransaction($userId, 1, 'aff', $paid);
        $total_orders = Aff::CountOrdersToPartner($userId, 1, 1);
        $clients = Aff::getUserFromPartner($userId);
        $count_month_has_date = Aff::CountMonthHasDate($userId);
        $main_table = Aff::getDateForMainTable($userId);

        $this->setSEOParams('Партнёрская программа');
        $this->setViewParams('lk', 'aff/aff_index.php',
            false, null, 'aff-page'
        );

        require_once ("{$this->template_path}/main.php");
    }
    public function actionParent() {
        $user_id = User::checkLogged();
    
        $user = User::getUserById($user_id);
    
        if (isset($_POST['addchild'])) {
            $child_email = !empty($_POST['child_email']) ? htmlentities($_POST['child_email']) : null;
            $order_id = intval($_POST['order_id']);
            if ($child_email != null && $order_id != null) {
                $order = Order::getOrder($order_id);
                $user_child = User::getUserDataByEmail($child_email);
                if($user_child==false){
                    $setting = System::getSetting();
                    $send_pass = $setting['enable_cabinet'];
                    $enter_method = 'free';
                    $is_client=true;
                    $order_info = $order['order_info'] != null ? unserialize(base64_decode($order['order_info'])) : null;
                    $surname = isset($order_info['surname']) ? $order_info['surname'] : null;
                    $nick_telegram = isset($order_info['nick_telegram']) ? $order_info['nick_telegram'] : null;
                    $vk_id = isset($order_info['vk_id']) ? $order_info['vk_id'] : null;
                    $ok_id = isset($order_info['ok_id']) ? $order_info['ok_id'] : 0;
                    $patronymic = isset($order_info['patronymic']) ? $order_info['patronymic'] : null;
                    $user_child = User::AddNewClient($child_email, $child_email, $order['client_phone'],
                    $order['client_city'], $order['client_address'], $order['client_index'], 'user', $is_client,
                    time(), $enter_method, $order['visit_param'], 1, null, null, $send_pass,
                    $setting['register_letter'], 0, null, $order['partner_id'], $surname, $patronymic,
                    $nick_telegram, "", $order, $vk_id, null, $ok_id
                );
                sleep(2);
                }
                $order_items = Order::getOrderItems($order['order_id']);
                if ($user_child != false) {
                    foreach ($order_items as $order_item) {
                        $product = Product::getProductDataForSendOrder($order_item['product_id']);
                        if ($product['manager_letter'] != null) {
                            $manager_letter = unserialize(base64_decode($product['manager_letter']));
                            if (isset($manager_letter['email_manager']) && !empty($manager_letter['email_manager'])) {
                                $subj_manager = isset($manager_letter['subj_manager']) ? $manager_letter['subj_manager'] : null;
                                $letter_manager = isset($manager_letter['letter_manager']) ? $manager_letter['letter_manager'] : null;
                                $send_custom = Email::sendCustomLetterForManager(
                                    $manager_letter['email_manager'],
                                    $subj_manager,
                                    $letter_manager,
                                    $order
                                );
                            }
                        }
    
                        if ($product['del_group_id']) {
                            User::deleteUserGroupsFromList($user_child['user_id'], $product['del_group_id']);
                        }
    
                        // Добавление групп для пользователя при рассрочке и БЕЗ
                        if ($product['group_id'] != 0 && ($order['installment_map_id'] == 0 || $product['installment_addgroups'] == 0)) {
                            $add_groups = explode(",", $product['group_id']);
                            foreach ($add_groups as $group) {
                                User::WriteUserGroup($user_child['user_id'], $group);
                            }
                        }
    
                        $training_enabled = System::CheckExtensension('training', 1);
                        if ($training_enabled) {
                            $user_groups = $user_child['user_id'] ? User::getGroupByUser($user_child['user_id']) : false;
                            $user_planes = $user_child['user_id'] ? Member::getPlanesByUser($user_child['user_id'], 1) : false;
                            if ($user_groups || $user_planes) {
                                $filter = [
                                    'user_groups' => $user_groups,
                                    'user_planes' => $user_planes
                                ];
                                $training_list = $user_child['user_id'] ? Training::getTrainingList(null, null, $filter, null) : null;
                                if ($training_list) {
                                    foreach ($training_list as $training) {
                                        if ($training['curators_auto_assign'] == 1) {
                                            Order::AssignUserToCurator($user_child['user_id'], $training);
                                        }
                                    }
                                }
                            }
                        }
    
                        $subscription_id = null;
                        $membership_enabled = System::CheckExtensension('membership', 1);
                        if ($membership_enabled && $user_child && !empty($product['subscription_id']) && ($order['installment_map_id'] == 0)) {
                            Member::renderMember($product['subscription_id'], $user_child['user_id'], 1, $subscription_id, $order['subs_id']);
                        }
                    }
                    ToChild::close($order_id, $child_email);
                }
            } else {
                ErrorPage::returnError('Пользователя с таким email нет в системе');
            }
        }
    
        $this->setSEOParams('Партнёрская программа');
        $this->setViewParams('aff', 'family/child_parther_tab.php', false, null, 'aff-req-page');
    
        require_once ("{$this->template_path}/main.php");
    }

    public function actionAuthor()
    {
        $extension = System::CheckExtensension('partnership', 1);
        if (!$extension) {
            require_once ("{$this->template_path}/404.php");
        }

        $userId = User::checkLogged();
        $user = User::getUserById($userId);
        if ($user['is_author'] != 1) {
            System::redirectUrl("/lk");
        }
        
        if (isset($_POST['save_req'])) {
            $req = [];

            if (isset($_POST['req'])) {
                foreach($_POST['req'] as $key => $value) {
                    if ($key != 'rs') {
                        $req[$key] = htmlentities($value);
                    }  else {
                        $req[$key]['rs'] = htmlentities($value['rs']);
                        $req[$key]['off_name'] = htmlentities($value['off_name']);
                        $req[$key]['bik'] = htmlentities($value['bik']);
                        $req[$key]['inn'] = htmlentities($value['inn']);
                        $req[$key]['rs2'] = htmlentities($value['rs2']);
                        $req[$key]['name'] = htmlentities($value['name']);
                        $req[$key]['fio'] = htmlentities($value['fio']);
                    }
                }
            }

            $req = $req ? serialize($req) : null;
            $addreq = Aff::UpdateReq($userId, $req);
            if ($addreq) {
                System::redirectUrl("/lk/author?success");
            }

        }

        $params = unserialize(System::getExtensionSetting('partnership')); // настройки партнёрки
        $req = Aff::getPartnerReq($userId); // Реквизиты партнёра
        $transacts = Aff::getAuthorTransaction($userId);
        $total = Aff::getUserTransactData($userId, 'author');

        $this->setSEOParams('Кабинет автора');
        $this->setViewParams('lk', 'aff/author_index.php', false,
            null, 'author-page'
        );

        require_once ("{$this->template_path}/main.php");
    }
    
    
    
    
    // СТРАНИЦА ОПИСАНИЯ ПАРТНЁРКИ
    public function actionAffdesc()
    {

        $extension = System::CheckExtensension('partnership', 1);
        if (!$extension) {
            require_once ("{$this->template_path}/404.php");
        }
        
        $params = unserialize(System::getExtensionSetting('partnership'));
        
        // ВЫЯСНЯЕМ ПОКАЗЫВАТЬ ЛИ ССЫЛКИ НА РЕГИСТРАЦИЮ И ЛК
        if (User::isAuth()) {
            // Обновляем данные пользователя
            $user_id = $_SESSION['user'];
            $show_cabinet = false;
            // Данные юзера
            $user = User::getUserById($user_id);
            if ($user['is_partner'] == 1) {
                $show_aff = false;
            } else $show_aff = true;
        } else {
            $show_aff = true;
            $show_cabinet = true;
        }

        $title = isset($params['params']['seotitle']) ? $params['params']['seotitle'] : 'Партнёрская программа';
        $meta_desc = isset($params['params']['metadesc']) ? $params['params']['metadesc'] : null;
        $meta_keys = isset($params['params']['metakeys']) ? $params['params']['metakeys'] : null;

        $this->setSEOParams($title, $meta_desc, $meta_keys);
        $this->setViewParams('aff', 'aff/index.php', false,
            null, 'aff-desc-page'
        );

        require_once ("{$this->template_path}/main.php");
    }
    
    
    
    // РЕГИСТРАЦИЯ В ПАРТНЁРКЕ
    public function actionAffreg()
    {
        $cookie = $this->settings['cookie'];
        $extension = System::CheckExtensension('partnership', 1);
        if (!$extension) {
            require_once ("{$this->template_path}/404.php");
        }
        
        // Проверяем авторизацию юзера
        if (User::isAuth()) {
            // Обновляем данные пользователя
            $user_id = $_SESSION['user'];
            $verify = isset($_COOKIE["aff_$cookie"]) ? Aff::PartnerVerify(intval($_COOKIE["aff_$cookie"])) : false; // Проверка партнёра на существование и самозаказ
            $partner_id = $verify ? intval($_COOKIE["aff_$cookie"]) : 0;
           
            Aff::AddUserToPartner($user_id, $partner_id);
    
            $partner_group = Aff::getPartnerGroup();
            if ($partner_group) {
                User::WriteUserGroup($user_id, $partner_group);
            }
            
            // Перенаправляем в личный кабинет.
            System::redirectUrl("/lk/aff?success_reg");
        }

        $date = time();
        
        if (isset($_POST['affreg']) && !empty($_POST['email']) && !empty($_POST['pass'])) {
            $name = htmlentities($_POST['name']);
            if (strpbrk($name, "'()-$%&!")) {
                exit('Do not use special characters!'); 
            }
            $timeout = intval($_POST['tm']); // время заполнения формы
            $now = time();
            
            if (($now - $timeout) < 4) {
                exit('Ошибка 344 - afftime');
            }
            
            $email = htmlentities($_POST['email']);
            $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
            $about = htmlentities($_POST['about']);
            $reg_key = md5($date);
            $param = isset($_COOKIE["$cookie"]) ? htmlentities($_COOKIE["$cookie"]) : htmlentities($_SESSION["$cookie"]);
            
            // Партнёрка
            $verify = isset($_COOKIE["aff_$cookie"]) ? Aff::PartnerVerify(intval($_COOKIE["aff_$cookie"])) : false; // Проверка партнёра на существование и самозаказ
            $partner_id = $verify && $verify['email'] != $email ? intval($_COOKIE["aff_$cookie"]) : 0;
    
            $partner_group = Aff::getPartnerGroup();
            $add = Aff::AddNewPartner($name, $email, $pass, $about, $param, $date, $reg_key, $partner_group, $partner_id);
            
            if ($add) {
                // Отправить партнёру письмо с подтверждением
                $send = Email::SendPernerLetter($name, $email, $reg_key);
                $message = '<h4>Регистрация прошла успешно<br />Вам на почту отправлено письмо для подтверждения.<br />На всякий случай проверьте папку спам.</h4>';
            } else {
                $message = '<h4>Такой e-mail уже зарегистрирован, войдите на сайт используя ваш e-mail и пароль</h4>';
            }
        }

        $this->setSEOParams('Регистрация в партнёрской программе');
        $this->setViewParams('aff', 'aff/reg.php', false,
            null, 'aff-req-page'
        );

        require_once ("{$this->template_path}/main.php");
    }
    
    
    // ПОДТВЕРЖДЕНИЕ ЕМЕЙЛА ПРИ РЕГИСТРАЦИИ ПАРТНЁРА
    public function actionConfirm()
    {
        $extension = System::CheckExtensension('partnership', 1);
        if (!$extension) {
            require_once ("{$this->template_path}/404.php");
        }
        
        if (isset($_GET['key'])) {
            $key = htmlentities($_GET['key']);
            
            // Найти юзера с этим ключом и изменить статус на 1.
            $user = User::getUserDataToRegkey($key);
            if (!$user) {
                exit('Ошибка, наверное, вы уже подтвердили ваш e-mail');
            }

            User::updateUserStatus($user['user_id'], 1);
            Email::SendNotifAboutPartnerToAdmin($user['user_name'], $user['email']); // написать админу что зарегался новый партнёр

            $this->setSEOParams('Ваш e-mail подтверждён');
            $this->setViewParams('aff', 'aff/confirm.php', false);

            require_once ("{$this->template_path}/main.php");
        } else {
            System::redirectUrl($this->settings['script_url']);
        }
    }
    
    
    
    // РЕДИРЕКТЫ ПАРТНЁРОВ
    public function actionRedirect($id)
    {
        $id = intval($id);
        $url = Aff::getAffRedirect($id);
        
        $aff_set = unserialize(System::getExtensionSetting('partnership'));
        $aff_life = intval($aff_set['params']['aff_life']);
        
        if ($url) {
            $cookie = $this->settings['cookie'];
            
            $hit = Aff::AffHits($url['partner_id']);
            if ($hit) {
                setcookie("aff_$cookie", $url['partner_id'], time()+3600*24 * $aff_life, '/');
                System::redirectUrl($url['url']);
            }
        } else {
            require_once ("{$this->template_path}/404.php");
        }
    }
}