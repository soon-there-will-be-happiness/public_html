<?php define('BILLINGMASTER', 1);

// КРОН ДЛЯ скрипта помогатора куратора-партнёра.
// Ищет подписки, которые заканчиваются.

// Настройки системы
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);
$now = time();

require_once (ROOT . '/components/autoload.php');
require_once (ROOT . '/vendor/autoload.php');

$now = time();
$name_jobs = "aff_cron";

$setting = System::getSetting();

// НАЙТИ ИСТЕКАЮЩИЕ ПОДПИСКИ

$time_left = 72; // подписка заканчивается через 3 дня
$kick_time = $now + $time_left * 3600;

$db = Db::getConnection();
$sql = "SELECT * FROM ".PREFICS."member_maps WHERE end < $kick_time AND end > $now AND status = 1 AND send_notification = 0 ";

$result = $db->query($sql);

$data = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $data[] = $row;
}

if($data){
    
    ob_start();
    print_r($data);
    $buffer = ob_get_contents();
    ob_end_clean();
    
    //Email::SendMessageToBlank('report@kasyanov.info', 'кем стать aff_cron', 'fff', $buffer);
    
    foreach($data as $map){
        
        $user_data = User::getUserById($map['user_id']); // получили ID юзера из карты подписки
        if($user_data){
            
            if($user_data['from_id'] > 0){
                $partner_id = $user_data['from_id']; // получили ID партнёра
                
                $partner_data = User::getUserById($partner_id); // получили данные партнёра
                if($partner_data){
                    
                    $text = 'У клиента '.$user_data['user_name'].' '.$user_data['email']." через $time_left часов заканчивается подписка. Напомните ему, чтобы он не пропустил";
                    
                    // Отправить письмо ob_start();
                    print_r($map);
                    $buffer = ob_get_contents();
                    ob_end_clean();
                    
                    Email::SendMessageToBlank($partner_data['email'], 'Окончание подписки у вашего ученика', 'Окончание подписки у вашего ученика', $text);
                    
                    
                    // Обновить
                    $num = 1;
                    $sql = 'UPDATE '.PREFICS."member_maps SET send_notification = :notif WHERE id = :map_id";
                    
                    $result = $db->prepare($sql);
                    $result->bindParam(':map_id', $map['id'], PDO::PARAM_INT);
                    $result->bindParam(':notif', $num, PDO::PARAM_INT);
                    return $result->execute();
                    
                }
            }
            
        }
        
    }
}

$db = Db::getConnection();  
$sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run) VALUES(:name_jobs, :last_run)  ON DUPLICATE KEY UPDATE last_run = :last_run";
$result = $db->prepare($sql);
$result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
$result->bindParam(':last_run', $now, PDO::PARAM_INT);
$result->execute();



?>