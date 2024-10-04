<?php define('BILLINGMASTER', 1);

// Настройки
$reportEmail = 'report@kasyanov.info';
$root = dirname(__FILE__, 2);
define('ROOT', $root);
require_once(ROOT.'/components/db.php');
require_once(ROOT.'/config/config.php');
require_once(ROOT.'/components/autoload.php');
define("PREFICS", $prefics);

/* Приходит вебхук с: 
- ID заказа
- емейл клиента
- ID тренинга
*/
Log::add(0, "Данные запроса aff", ["request" => $_REQUEST]);
$settings = System::getSetting();
$secret = trim($_REQUEST['secret']) ?? die('нет ключа');

ob_start();
print_r($_REQUEST);
$buffer = ob_get_contents();
ob_end_clean();

Email::SendMessageToBlank('report@kasyanov.info', 'fff', 'fff', $buffer);


if ($secret != trim($settings['secret_key'])) {
    returnResponse('не верный ключ');
}

$order_id = intval($_REQUEST['order_id']) ?? die('нет заказа');

$client_email = $_REQUEST['client_email'] ?? die('нет email');
$training_id = intval($_REQUEST['training']) ?? 0;

// Получаем данные заказа
$order = Order::getOrder($order_id);
if (!$order) {
    returnResponse('Заказа не существует');
}

// Получаем данные клиента по его емейлу
$client = User::getUserDataByEmail($client_email);
if (!$client) {
    returnResponse('Клиента не существует');
}
$client_id = $client['user_id'];

if($training_id > 0){
    $training = Training::getTraining($training_id);
    if (!$training) {
        returnResponse('Тренинга не существует');
    }   
}

$partner_id = $order['partner_id'];
if (empty($partner_id)) {
        returnResponse("Нет айди партнера в заказе");
}

// Получить данные партнёра
$partner = User::getUserById($partner_id);
if (!$partner) {
    returnResponse('Партнера не существует');
}
if ($partner['is_partner'] != 1) {
    returnResponse('Партнер не является партнером');
}


// Отправляем письмо партнёру о продлении
function sendMessageToPartnerFromPomogatorProlong($partner, $subject, $text, $settings) {
    $send = Email::sender($partner['email'], $subject, $text, $settings, $settings['from_name'], $settings['from']);
}


// ЕСЛИ ЭТО ПРОДЛЕНИЕ
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'prolong'){
    
    // Лог для проверки
    $text = "
    <p>ID заказа: $order_id</p>
    <p>ID партнера: $partner_id</p>
    <p>ID клиента: $client_id</p>
    <p>ID тренинга: $training_id</p>";
    Email::sender($reportEmail, "Отчет о продлении aff_curator", $text, $settings, $settings['sender_name'], $settings['sender_email']);
    
    // Отправляем письмо партнёру о продлении
    $subject = 'Клиент продлил подписку';
    $text = 'Отлично! Клиент продлил подписку: <br />Имя: '.$client['user_name'].'<br />Тел: '.$client['phone'].'<br />Email:'.$client['email'];
    $send = sendMessageToPartnerFromPomogatorProlong($partner, $subject, $text, $settings);
    
    
} else {
    
// ЕСЛИ ЭТО ПОКУПКА
$db = Db::getConnection();

// Если не куратор, сделать куратором
if($partner['is_curator'] != 1){
    $is_curator = 1;
    $user_id = $partner['user_id'];
    $sql = 'UPDATE '.PREFICS."users SET is_curator = :is_curator WHERE user_id = :user_id";
    
    $result = $db->prepare($sql);
    $result->bindParam(':is_curator', $is_curator, PDO::PARAM_INT);
    $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $result->execute();   
}

// Если не куратор в тренинге
$isPartnerCurator = boolval($db->query("SELECT * FROM `".PREFICS."training_curators_in_training` 
                                        WHERE `curator_id` = '$partner_id' AND `training_id` = '$training_id'")->fetch());
// Сделать куратором в тренинге
if (!$isPartnerCurator) {
    $sql = $db->prepare("INSERT INTO `".PREFICS."training_curators_in_training` (`curator_id`, `training_id`, `section_id`, `is_master`, `assing_to_users`) 
                        VALUES ('$partner_id', '$training_id', '0', '0', '0')");
    $result = $sql->execute();
}

// Назначить куратором для юзера
$addCuratorToUser = $db->prepare("INSERT INTO `".PREFICS."training_curator_to_user` (`curator_id`, `user_id`, `training_id`, `section_id`) 
                                    VALUES ('$partner_id', '$client_id', '$training_id', '0')");
$addCuratorToUser = $addCuratorToUser->execute();

// Функция
function returnResponse($text) {
    return die($text);
}

// Отправляем письмо партнёру о покупке
function sendMessageToPartnerFromPomogator($partner, $subject, $text, $settings) {
    $send = Email::sender($partner['email'], $subject, $text, $settings, $settings['from_name'], $settings['from']);
}


$subject = 'Новый клиент';
$product_name = Product::getProductName($order['product_id']);
$text = 'Поздравляем! У вас новый клиент: <br />Имя: '.$client['user_name'].' '.$client['surname'].'<br />Тел: '.$client['phone'].'<br />Email:'.$client['email'].'<br />
Продукт: '.$product_name['product_name'].'<br />
Свяжитесь с клиентом для уведомления о том, что оплата прошла';

sendMessageToPartnerFromPomogator($partner, $subject, $text,$settings);

// Лог для проверки
$text = "
<p>ID заказа: $order_id</p>
<p>ID партнера: $partner_id</p>
<p>ID клиента: $client_id</p>
<p>ID тренинга: $training_id</p>
";
Email::sender($reportEmail, "Отчет о работе скрипта aff_curator", $text, $settings, $settings['sender_name'], $settings['sender_email']);

}