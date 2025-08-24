<?php define('BILLINGMASTER', 1); 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
// 1 раз в час - норм.

// Настройки системы
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');
require_once (ROOT . '/vendor/autoload.php');
System::enableLongWaitForQueries();

$db = Db::getConnection();

$setting = System::getSetting();
$now = time();
$name_jobs = "member_cron";
//echo 'email: ';

// Получить список планов подписки и перебрать их

$plane_list = Member::getPlanes(1);

if($plane_list){
	foreach($plane_list as $plane) {
        $product = Product::getProductById($plane['renewal_product']);
		if ($plane['renewal_type'] != 3) {
			if(!$product) continue;
			
			if ($plane['renewal_type'] == 1) {
				$link = $setting['script_url'].'/buy/'.$product['product_id'];
			} elseif ($plane['renewal_type'] == 2) {
				$link = $setting['script_url'].'/catalog/'.$product['product_alias'];
			}
		} else {
			$link = $plane['renewal_link'];
		}

		for ($x=1; $x<=3; $x++) {
            $letter_status_key = "letter_{$x}_status";
			$letter_time_key = "letter_{$x}_time";
			$letter_text_key = "letter_{$x}";
			$letter_subj_key = "letter_{$x}_subj";

            $sms_status_key = "sms{$x}_status";
            $sms_text_key = "sms{$x}_text";

            if (isset($plane[$letter_time_key]) && $plane[$letter_time_key] != 0) {
				$kick_time = $now + $plane[$letter_time_key] * 3600;
				$search_list = Member::SearchExpiresForSendMess($plane['id'], $kick_time, $x - 1);

                if ($search_list) {
                    foreach($search_list as $item) {
                        // Получить данные юзера
                        $user = User::getUserById($item['user_id']);
                        //echo 'email: '.$user['email'].'\nlink:'.$linkToEmail.'\n';
                        $partner_id=User::getUserDataByEmail($user['email']);
                        //var_dump($partner_id);
                        if($product['product_id'] == 28) {
                            $linkToEmail = "{$link}?partner={$partner_id['from_id']}&user={$user['user_name']}&email={$user['email']}&phone={$user['phone']}#pay";
                        } else {
                            $linkToEmail="{$link}?subs_id={$item['id']}";
                        }
                        if(!$user){
                            $text = "Для подписки мембершип с ID ".$item['id']. 'не найден пользователь, проверьте.';
                            AdminNotice::addNotice($text);
                            Email::SendMessageToBlank($setting['admin_email'], 'SM', 'Не найден пользователь SM', $text);
                            continue;
                        }
                        if ($plane[$letter_status_key]) { // Отправить письмо клиенту
                            $send = Email::SendExpirationMessageByClient($user['email'], $user['user_name'],
                                $plane[$letter_subj_key], $plane[$letter_text_key], $linkToEmail
                            );
                            /*$text = 'У клиента '.$user['user_name'].' '.$user['email']." через $plane[$letter_time_key] часов заканчивается подписка. Напомните ему, чтобы он не пропустил";
    

                            Email::SendMessageToBlank($partner_id['email'], 'Окончание подписки у вашего ученика', 'Окончание подписки у вашего ученика. Ссылка продления: '. $linkToEmail, $text);
                            Log::add(1,'Data', [
                                "email" => $user['email'],
                                "kick_time" => $kick_time,
                                "letter_time_key"=>$letter_time_key,
                                "item"=>$item,
                                ]
                                ,'test_membership.log');*/

                        }

                        if ($plane[$sms_status_key] && $user['phone']) {
                            SMS::sendNotice2ExpireSubs($user['user_name'], $linkToEmail,
                                $user['phone'], $plane[$sms_text_key]
                            );
                        }

						// Записать notif в карту
						$upd = Member::updateNotifFromMap($item['id'], $x);

					}
				}
			}
			
		}
	}
}

//Member::SendExpirationMessage();

$recurrents = Member::getRecurrentsMaps();
if($recurrents){
    
    foreach($recurrents as $recurrent){
        $rec_item = explode('=', $recurrent['subscription_id']);
        
        if($rec_item[0] == 'Robokassa'){
            
            ////Email::SendMessageToBlank('report@kasyanov.info', 'fff', 'fff', $buffer.'<br />ID = '.$rec_item[1]);
            
            
            Member::PayRobokassa($rec_item[1], $recurrent['subs_id'], $recurrent['id'], $recurrent['user_id'], $now);
            
        }
    }

}

$planes = Member::SearchExpirePlane();

if ($planes) {
    Member::deleteExpirePlanes($planes);
    Member::addUsersToGroupsByExpPlns($planes);
    Member::addPlanesToUser($planes);
}

//Пишем в таблицу логов крона
//TODO в дальнейшем надо сделать модели и класс если этот функционал будет расширятся
$db = Db::getConnection();  
$sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run) VALUES(:name_jobs, :last_run)  ON DUPLICATE KEY UPDATE last_run = :last_run";
$result = $db->prepare($sql);
$result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
$result->bindParam(':last_run', $now, PDO::PARAM_INT);
$result->execute();

//TODO В дальнейшем, все записи и отправки почты в 
//кронзадачах обернуть в try catch и в случае не удачи 

//писать ошибки в лог в таблицу cron_logs позже добавим там колонку error