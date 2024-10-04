<?php defined('BILLINGMASTER') or die;

class responderController extends baseController {
    
    
    // ПОДПИСКА НА РАССЫЛКУ через html форму
    public function actionSubscribe($id) {
        $id = intval($id);
        
        if (isset($_POST['subscribe']) && !empty($_POST['email'])) {
            $date = time();
            $cookie = $this->settings['cookie'];
            $responder_setting = unserialize(Responder::getResponderSetting());
            $error = false;
            $delivery = Responder::getDeliveryData($id);
			if (!$delivery) {
                ErrorPage::returnError('');
            }
            
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $email = htmlentities($_POST['email']);
                $time = time();
                $subs_key = md5($email . $time);
                $cancelled = $spam = 0;
                $param = $date.';0;;/form';
                
                if (isset($_POST['name'])) {
                    $name = htmlentities($_POST['name']);
                    if (strpbrk($name, "'()-$%&!")) {
                        ErrorPage::returnError('Не используйте спец.символы');
                    }
                } else {
                    $name = $responder_setting['params']['name'];
                }
                
                $phone = isset($_POST['phone']) ? htmlentities($_POST['phone']) : null;
                
                // Добавить в карту рассылок
                $confirmed = $delivery['confirmation'] > 0 ? 0 : $time;
                
                // Создать запись в карте
                $add = Responder::addSubsToMap($id, $email, $name, $phone, $time, $subs_key,
                    $confirmed, $cancelled, $spam, $param, $responder_setting, $this->settings
                );

                if (!$add) {
                    $error = 'Вы уже подписаны на данную рассылку';
                }
            } else {
                $error = 'Не корректный e-mail адрес';
            }

            $is_exist_redirect_url = Responder::getDeliveryListForID(2,1,null,$id); //2 - автосерия
            if ( isset($is_exist_redirect_url[0]['redirect_url']) && $is_exist_redirect_url[0]['redirect_url'] !='') {
                System::redirectUrl($is_exist_redirect_url[0]['redirect_url']);
            }

            $this->setSEOParams('Подтверждение подписки');
            $this->setViewParams('responder', 'responder/confirm.php', false,
                null, 'invert-page'
            );

            require_once ("{$this->template_path}/main2.php");
        } else {
            header("Location: /");
        }
        return true;
    }
    
    
    
    // ПОДТВЕРЖДЕНИЕ ПОДПИСКИ ПОЛЬЗОВАТЕЛЯ
    public function actionConfirm($delivery_id)
    {
        if (isset($_GET['email']) && isset($_GET['key'])) {
            $delivery_id = intval($delivery_id);
            $cookie = $this->settings['cookie'];
            $responder_setting = unserialize(Responder::getResponderSetting());
        
            // получить данные рассылки по id 
            $delivery = Responder::getDeliveryData($delivery_id);
            
            if ($delivery) {
                $email = htmlentities($_GET['email']);
                $key = htmlentities($_GET['key']);
                
                // получить запись в карте подписок и обновить статус confirmed с 0 на дату в unix 
                $row = Responder::getSubsMapRow($email, $delivery['delivery_id']);

                if ($row && $row['subs_key'] == $key) {
                    if ($upd = Responder::updateSubsRow($row['id'], time())) {
                        $user = User::getUserDataByEmail($email);
                        Responder::eventsAfterConfirm($delivery, $user, $row['subs_name'], $row['email'], $row['phone'],
                            time(), htmlentities($_COOKIE["$cookie"]), $responder_setting, $this->settings, 1
                        );

                        $this->setSEOParams('Подтверждение подписки');
                        $this->setViewParams('responder', 'responder/success-confirm.php', false,
                            null, 'invert-page'
                        );

                        require_once ("{$this->template_path}/main2.php");
                    }
                } else {
                    echo '<p>Вы уже подтвердили ваш e-mail или произошла ошибка<p>';
                }
            }
        } else {
            System::redirectUrl($this->settings['script_url']);
        }
        return true;
    }
    
    
    
    // ОТПИСКА ОТ РАССЫЛКИ
    public function actionUnsubscribe($key)
    {
        if (isset($_GET['did']) && isset($_GET['email'])) {
            $did = intval($_GET['did']);
            $email = htmlentities($_GET['email']);
            $key = htmlentities($key);
            
            $row = Responder::getSubsMapRow($email, $did);
            $check = Responder::checkInstallmentFromEmail($email);
            if ($key == md5($this->settings['secret_key'].$email)) {
                if (isset($_POST['gone']) && isset($_POST['type'])) {
                    $type = htmlentities($_POST['type']);
                    $reason = htmlentities($_POST['why']);
                    
                    switch ($type) {
                        case 'single':
                            // Удалить одну подписку
                            $del = Responder::DeleteSubsRow($email, $did);
                            $none = 0;
                            break;
                        case 'all':
                            //Удалить все подпсики
                            $del = Responder::DeleteSubsRow($email, 0);
                            $del_subs = Responder::DeleteIsSubs($email, 0);
                            $del_tasks = Responder::deleteEmailFromEmailTask($email);
                            $did = 0;
                            $none = 0;
                            break;
                        case 'delete':
                            // удалить юзера по email
                            $user = User::getUserDataByEmail($email);
                            $del = $user ? User::deleteUser($user['user_id']) : false;
                            $del_tasks = Responder::deleteEmailFromEmailTask($email);
                            $none = 0;
                            break;
                        case 'none':
                            $none = 1;
                            break;
                    }

                    $this->setSEOParams('Отписка от рассылки');
                    
                    // Записать причину отписки в базу
                    $write = Responder::WriteUnsubReason($email, $reason, $type, $did);

                    if (isset($none)) {
                        if ($none == 1) {
                            $path = 'responder/no-unsubscribe.php';
                        } else {
                            $path = 'responder/ok-unsubscribe.php';
                        }

                        $this->setViewParams('responder', $path, false,
                            null, 'invert-page'
                        );

                        require_once ("{$this->template_path}/main2.php");
                        return true;
                    }
                }

                $this->setViewParams('responder', 'responder/unsubscribe.php', false,
                    null, 'invert-page'
                );
                require_once ("{$this->template_path}/main2.php");
            } else {
                ErrorPage::returnError('Not key valid');
            }
        } else {
            ErrorPage::returnError('not param');
        }
        return true;
    }
    
    
    
    // МНГНОВЕННАЯ ОТПИСКА
    public static function actionUnsubclick($key) {
        if (isset($_GET['did']) && isset($_GET['email'])) {
            $settings = System::getSetting();
            $did = intval($_GET['did']);
            $email = $_GET['email'];
            $key = htmlentities($key);
            $row = Responder::getSubsMapRow($email, $did);

            if ($key == md5($settings['secret_key'].$email)) {
                $del = Responder::DeleteSubsRow($email, $did);
                $del = Responder::deleteEmailFromEmailTask($email);
                exit('<h1 style="text-align:center; padding:1em 0;">Всё хорошо.</h1><h2 style="text-align:center">Вы успешно отписаны от рассылки.</h2>');
            } else {
                ErrorPage::returnError('Not key valid');
            }
        } else {
            ErrorPage::returnError('not param');
        }
    }
}