<?php defined('BILLINGMASTER') or die;

class telegramController {

    /**
     * ПОЛУЧЕНИЕ ДАННЫХ ОТ TELEGRAM
     */
    public function actionGetUpdates() {
        $enable = Telegram::getStatus();
        
        if ($enable) {
            $settings = Telegram::getSettings();
            $params = unserialize($settings);
            
            $token = trim($params['params']['token']);
            if (!$token) {
                exit;
            }
            
            $api = new TelegramApi($token);
            $data = $api->getUpdatePost();
            if (!$data) {
                exit;
            }

            if (isset($data['chat_member']['chat']) && isset($data['chat_member']['new_chat_member']) && !empty($data['chat_member']['new_chat_member'])) { // добавляем пользователя в список участников и удаляем его из чатов, если у него не должно быть доступа к ним
                $chat_id = $data['chat_member']['chat']['id'];
                $user_id = (int)$data['chat_member']['new_chat_member']['user']['id'];
                $user_name = isset($data['chat_member']['new_chat_member']['user']['user_name']) ? $data['chat_member']['new_chat_member']['user']['user_name'] : '';
                $first_name = isset($data['chat_member']['new_chat_member']['user']['first_name']) ? $data['chat_member']['new_chat_member']['user']['first_name'] : '';
                $last_name = isset($data['chat_member']['new_chat_member']['user']['last_name']) ? $data['chat_member']['new_chat_member']['user']['last_name'] : '';

                $user = Telegram::getUserByUserId($user_id);
                $all_chats = Telegram::getChats();

                if ($user && $user['sm_user_id']) { // пользователь зарегистрирован в системе
                    $user_chats = Telegram::getChats($user['sm_user_id']);
                    if (!$user_chats || !in_array($chat_id, $user_chats)) { // удаляем пользователя из тг, если у него нет этого чата/канала в его группах/подписках
                        Telegram::delUserFromChats($user['sm_user_id'], null, $chat_id, false, Telegram::EVENT_DEL_USER_FROM_CHAT);
                    }
                } elseif($all_chats && in_array($chat_id, $all_chats) ) { // пользователя нет в списке участников или не зарегистрирован в системе
                    if (!$user) { // пользователь не зарегистрирован в системе
                        Telegram::addUnregisteredUser($user_id, $user_name, $first_name, $last_name);
                    }

                    Telegram::delUserFromChat($api, $user_id, $chat_id, Telegram::EVENT_DEL_USER_FROM_CHAT); // удаляем пользователя из тг, если чат указан в настройках группы/подписки
                }

            } elseif(isset($data['message']['from'])) { // привязываем пользователя
                // Основной код: получаем сообщение, что юзер отправил боту и 
                // заполняем переменные для дальнейшего использования
                $from = $data['message']['from'];
                $chat_id = $data['message']['from']['id'];
                $user_tg_id = $from['id'];


                if ($user_tg_id) { 

                    if(substr($data['message']['text'], 0, 6) == "/start") { 

                        $cmd = explode(" ", $data['message']['text']);
                        
                        if(isset($cmd[1]) && substr($cmd[1], 0, 4) == "cct1"){
                            $service = Connect::getServiceByName('telegram');

                            if($service['status'] != 1 
                                || !isset($service['types']['auth'], $service['params']['auth_link']) 
                                || @ $service['params']['auth'] != 1
                            )
                                return $api->sendMessage($chat_id, "Авторизация через {$service['title']} невозможна.");

                            $res = Connect::authUser('telegram', substr($cmd[1], 4), $user_tg_id);

                            if($res == 'success')
                                return $api->sendMessage($chat_id, "Авторизация через {$service['title']} прошла успешно! Возвращайтесь в школу");

                            else
                                return $api->sendMessage($chat_id, "Для того, чтобы авторизововаться в Школе через {$service['title']}, вам необходимо подключаить его в ЛК.");   
                        }

                        elseif(isset($cmd[1]) && substr($cmd[1], 0, 4) == "cct2"){
                            $service = Connect::getServiceByName('telegram');

                            if($service['status'] != 1 
                                || !isset($service['types']['lk_conn'], $service['params']['auth_link']) 
                                || @ $service['params']['lk_conn'] != 1
                            )
                                return $api->sendMessage($chat_id, "Подключение {$service['title']} к школе невозможна.");
                            $hash = substr($cmd[1], 4);

                            $res = Connect::addUserByHash($hash, 'telegram', $user_tg_id);

                            if($res){
                                Telegram::saveUser($res['user_id'], $user_name, $hash);

                                return $api->sendMessage($chat_id, "Аккаунт школы успешно привязан к {$service['title']}!");
                            }

                            else
                                return $api->sendMessage($chat_id, "Не удалось привязать аккаунт через {$service['title']}.");   
                        }


                    }

                    $user = Telegram::getUserByUserId($user_tg_id);              
                    if ($user && intval($user['sm_user_id']) !== 0) { // тут значит пользователь привязан уже

                        if (!empty($data['message']['text'])) {
                            $text = trim($data['message']['text']);

                            $kb_quest = $api->getKeyboard('keyboard', [
                                [['Да'], ['Нет']]
                            ], ['one_time_keyboard' => true, 'resize_keyboard' => true]);

                            $keyboard = array(array("Подписаться на уведомления"));
                            $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
                            $kb_submit = json_encode($resp);

                            $keyboard = array(array("Отписаться от уведомлений"));
                            $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
                            $kb_unsubmit = json_encode($resp);

                            if ($text == '[Да]' || $text == 'Да' || $text == 'Подписаться на уведомления') {
                                $text_return = "Вы подписаны на уведомления";
                                Telegram::saveChatBotIdToUser($user_tg_id, $chat_id);
                                $api->sendMessage($chat_id, $text_return, $kb_unsubmit);
                            }   
                            elseif ($text == '[Нет]' || $text == 'Нет' || $text == 'Отписаться от уведомлений') {
                                $text_return = "Вы отказались от уведомлений";
                                Telegram::saveChatBotIdToUser($user_tg_id, NULL);
                                $api->sendMessage($chat_id, $text_return, $kb_submit);
                            }
                            elseif ($text == '/start') {
                                $text_return = "Хотите подписаться на уведомления ?";
                                $api->sendMessage($chat_id, $text_return, $kb_quest);
                            }
                        }
                    } else {
                        Telegram::delMemberByUserId($user_tg_id, 0); // удалить участника с нулевым sm_user_id, если есть    
                        $user_id = Telegram::bindUser($api, $data['message']);
                        $tg_chats = Telegram::getChats($user['sm_user_id']);
                        if ($tg_chats) {
                            Telegram::delUserFromBlacklist($user['sm_user_id'], $tg_chats);
                        }
                    }
                }
            }
        }
    }


    /**
     * СОХРАНИТЬ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ
     */
    public function actionSaveData() {
        if (isset($_POST['tg_link'])) {
            $resp = [
                'status' => false,
                'error_msg' => '',
                'hash' => '',
            ];

            $user_id = intval(User::checkLogged());
            if (!$user_id || empty($_POST['tg_link'])) {
                exit(json_encode($resp));
            }

            $settings = Telegram::getSettings();
            $params = unserialize($settings);

            $tg_username = isset($_POST['tg_username']) ? substr(strip_tags($_POST['tg_username']), 0, 64) : '';
            $parts = parse_url($_POST['tg_link']);
            parse_str($parts['query'], $query);
            $hash = $query['start'];
            if ($hash) {
                $resp['status'] = Telegram::saveUser($user_id, $tg_username, $hash);
                $resp['hash'] = $hash;
            }

            echo json_encode($resp);
        } else {
            $sys_settings = System::getSetting();
            require_once (ROOT . "/template/{$sys_settings['template']}/404.php");
        }
    }


    /**
     * ПРОВЕРИТЬ ПРИВЯЗКУ ПОЛЬЗОВАТЕЛЯ
     */
    public function actionCheckBindingUser() {
        if (isset($_POST['tg_username'])) {
            $resp = [
                'status' => false,
                'error_msg' => '',
                'bind' => false,
            ];

            $user_id = intval(User::checkLogged());
            if (!$user_id || empty($_POST['tg_username'])) {
                echo json_encode($resp);
                exit;
            }

            $resp['bind'] = Telegram::checkBindingUser($user_id, $_POST['tg_username']);
            $resp['status'] = true;

            echo json_encode($resp);
        } else {
            $sys_settings = System::getSetting();
            require_once (ROOT . "/template/{$sys_settings['template']}/404.php");
        }
    }

    /**
     * АУТЕНТИФИКАЦИЯ С TG
     */
    public function actionAuth($method = null)
    {   
        $telegram = unserialize(Telegram::getSettings());
        $setting = System::getSetting();

        if (@ $telegram['auth']['enable'] != 1 
            || ($setting['enable_cabinet'] == 0 && $setting['enable_aff'] == 0)
        ) {
            require_once (ROOT . '/template/'.$setting['template'].'/404.php');
            exit;   
        }

        if(User::isAuth() 
            || !isset($_GET['id'], $_GET['auth_date'], $_GET['hash'])
        )
            System::redirectUrl("/");

        $secret_key = hash('sha256', $telegram['params']['token'], true);

        $auth_data = TelegramAuth::getData($_GET, $secret_key);

        if(isset($auth_data['error'])){
            System::redirectUrl("/");    
        }

        $user = TelegramAuth::getUser($auth_data['id']);

        if($user){
            User::Auth($user['user_id'], $user['user_name']);
            $courses = System::CheckExtensension('courses', 1);
            $trainingext = System::CheckExtensension('training', 1);
            if ($setting['login_redirect'] == 1) {
                header ("Location: ".$setting['script_url'].'/lk/');
            } elseif ($setting['login_redirect'] == 2) {
                header ("Location: ".$setting['script_url'].'/lk/orders');
            } elseif ($setting['login_redirect'] == 3 && $courses == true) {
                header ("Location: ".$setting['script_url'].'/lk/mycourses');
            } elseif ($setting['login_redirect'] == 4 && $trainingext == true) {
                header ("Location: ".$setting['script_url'].'/lk/mytrainings');
            } elseif ($setting['login_redirect'] == 5) {
                header ("Location: ".$setting['script_url']);
            } else header ("Location: ".$setting['script_url']);

        }else
            exit("not found"); // #todo
    }

}