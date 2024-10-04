<?php define('BILLINGMASTER', 1);

define('START', microtime(1));

// Настройки системы
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');
require_once (ROOT . '/vendor/autoload.php');
System::enableLongWaitForQueries();

$setting = System::getSetting();
$sender_name = $setting['sender_name'];
$sender_email = $setting['sender_email'];

$time = time();
$name_jobs = "email_cron";
$error_send = false;

// Инициализировать объект Мейлера
if ($setting['smtp_ssl'] > 0) {
    $auth = $setting['smtp_ssl'] == 1 ? 'ssl' : 'tls';
    
    $transport = (new Swift_SmtpTransport($setting['smtp_host'], $setting['smtp_port'], $auth))
      ->setUsername($setting['smtp_user'])
      ->setPassword($setting['smtp_pass']);  
} else {
    $transport = (new Swift_SmtpTransport($setting['smtp_host'], $setting['smtp_port']))
      ->setUsername($setting['smtp_user'])
      ->setPassword($setting['smtp_pass']);   
}
$mailer = new Swift_Mailer($transport);

if (!empty($setting['smtp_private_key'])) {
    $signer = new Swift_Signers_DKIMSigner($setting['smtp_private_key'], $setting['smtp_domain'], $setting['smtp_selector']);
}

$db = Db::getConnection();
$bulk = false;

// Найти зависшие
$update = Responder::searchStragglerTask($time);

// Получить список заданий на отправку
$tasks = Responder::getTasksForAction();

// получить список массовых рассылок у которых не истекло время отправки
$mass_mail_ids = Email::getActiveMassMailIDs($time - 86400);		  

// $notify_tg = System::CheckExtensension('telegram', 1);
// if ($notify_tg) {
//     $telegram = Telegram::getSettings();
//     $params_tg = unserialize($telegram);
// }


if ($tasks) {
    $task_ok = false;
    $task_fail = false;
    foreach($tasks as $task) { // Перебираем задания
        if ($task['letter']) {
            $letter = json_decode($task['letter'], true);
        } else { // Получить письмо по ID
            $result = $db->query('SELECT * FROM '.PREFICS.'email_letter WHERE letter_id = '.$task['letter_id'].' LIMIT 1');
            $letter = $result->fetch(PDO::FETCH_ASSOC);
        }

        // Изменить имя отправителя
        if(isset($letter['sender_name']) && !empty(trim($letter['sender_name'])))
            $sender_name = $letter['sender_name'];
        
        if (isset($letter) && !empty($letter)) {
            $subject = $letter['subject'];
            
            // Получить данные юзера по емейл
            $email = $task['email'];
            $user = $task['user_name'];
            $promo = $promo_desc = '';
            
            if ($email AND filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Получить промо коды по емейл
                if (strpos($letter['body'], '[PROMO]') !== false || strpos($letter['body'], '[PROMO_DESC]') !== false ) {
                    $promo_data = System::getPromoByEmail($email, $time);

                    if ($promo_data) {
                        $expire = date("d-m-Y H:i", $promo_data['finish']);
                        $promo = 'Ваш промо код: '.$promo_data['promo_code'].'<br /> Действителен до: '.$expire;
                        $promo_desc = 'Ваш промо код: '.$promo_data['promo_code'].'<br /> Действителен до: '.$expire.'<br />'.$promo_data['sale_desc'];
                    }
                }

                $message_id = md5($time . '-' .$email); // id сообщения
                $key = md5($setting['secret_key'].$email); // ключ
                $did = $task['delivery_id'];
                
                if ($mass_mail_ids && in_array($did, $mass_mail_ids)) {
                    $bulk = true;
                }
                
                // Реплейсим текст письма
                $unsub_link = $setting['script_url']. "/responder/unsubscribe/$key?did=$did&email=$email";
                $unsub_click = '<'.$setting['script_url']. "/responder/unsubclick/$key?did=$did&email=$email>";

                //[AUTH_LINK]
                $userdata = User::getUserDataByEmail($email);
                if($userdata) $prelink = User::generateAutoLoginLink($userdata);//Ссылка автологин без редиректа
                else $prelink = '';
                
                $replace = array(
                    '[NAME]' => $user ?? " ",
                    '[CLIENT_NAME]' => $user ?? " ",
					'[EMAIL]' => $email,
                    '[UNSUBSCRIBE]' => $unsub_link,
                    '[PROMO]' => $promo,
                    '[PROMO_DESC]' => $promo_desc,
					'[SUPPORT]' => $setting['support_email'],
                    '[AUTH_LINK]' => $prelink ?? " ",
                );

                if (preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $letter['body'])) {
                    $letter['body'] = CustomFields::replaceContent($letter['body'], $email);
                }

                $text = strtr($letter['body'], $replace);
				$subject = strtr($subject, $replace);

				$text = User::replaceAuthLinkInText($text, $prelink);//Ссылка автологин с редиректом

                $text = html_entity_decode($text);
                $subject = html_entity_decode($subject);
                $sender_name = html_entity_decode($sender_name);

                // Письмо
                $message = new Swift_Message();
                if (!empty($setting['smtp_private_key'])) {
                    $message->attachSigner($signer);
                }

                if ($bulk) {
                    $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                }
                $message->getHeaders()->addTextHeader('List-Unsubscribe', $unsub_click);
        
                $message->setFrom([$sender_email => $sender_name]);
                $message->setBody($text, 'text/html', 'utf-8');
                $message->addPart(strip_tags($text), 'text/plain');
                $message->setSubject($subject);
                $message->setTo($email); 

                // if ($notify_tg && isset($params_tg['params']['notify']) && $params_tg['params']['notify'] == 1) { // расширение telegram
                //     Telegram::sendNotifyMessage($email, $text);
                // }

                try {
                    $send = $mailer->send($message);
                } catch (Exception $e) {
                    // Тут какая-то ошибка при отправке смотреть в файле error_email.txt
                    $error_send = true;
                    $error_mess = $e->getMessage();
                    $sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run, jobs_error, text_error)
                            VALUES(:name_jobs, :last_run, :jobs_error, :text_error)
                            ON DUPLICATE KEY UPDATE last_run = :last_run, text_error = :text_error";
                    $result = $db->prepare($sql);
                    $result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
                    $result->bindParam(':last_run', $time, PDO::PARAM_INT);
                    $result->bindParam(':jobs_error', $error_send, PDO::PARAM_INT);
                    $result->bindParam(':text_error', $error_mess, PDO::PARAM_STR);

                    try {
                        $result->execute();
                    } catch (Exception $e2) {
                        Log::add(4, 'Ошибка email_cron',["error_mysql" => $e2, "error_email" => $e]);
                    }
                }

                if (@ $send) {
                    $task_ok .= $task['task_id'].','; // Собираем ID тасков
                    Responder::WrtiteLog($task['letter_id'], $did, $time, $email, 'send', '');
                    
                    $log = Email::WriteLog($email, $sender_name, $text, $time, $subject, null);
                }
            } else {
                $task_fail .= $task['task_id'].','; // Собираем ID неотправленных тасков

                // тут скорее не ошибка, а то что юзера нет в выборке, пишем ошибку в лог
                Responder::WrtiteLog($task['letter_id'], $task['delivery_id'], $time,
                    $email, 'error',"User with email : $email not found"
                );
                $del = Responder::DeleteTask($task['task_id']);
                continue;
            }
        } else {
            $task_ok .= $task['task_id'].','; // тут собираем задачи у которых нет писем																																																					   
            continue;
        }
    }

    // УДАЛяем задачи и переотправляем неотправленные
    if($task_ok) $del = Responder::DeleteTaskStr($task_ok);
    if($task_fail) $resend = Responder::ResendTask($task_fail);
}


//$finish = round((microtime(true) - START),3);



//Пишем в таблицу логов крона
//TODO в дальнейшем надо сделать модели и класс если этот функционал будет расширятся
if (!$error_send) {
    //$db = Db::getConnection();  
    $sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run) VALUES(:name_jobs, :last_run)  ON DUPLICATE KEY UPDATE last_run = :last_run";
    $result = $db->prepare($sql);
    $result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
    $result->bindParam(':last_run', $time, PDO::PARAM_INT);
    $result->execute();
}
//TODO В дальнейшем, все записи и отправки почты в 
//кронзадачах обернуть в try catch и в случае не удачи 
//писать ошибки в лог в таблицу cron_logs позже добавим там колонку error
