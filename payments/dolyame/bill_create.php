<?php define('BILLINGMASTER', 1);

if(empty($_POST)){
	header('Location /');
	exit();
}

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');

require __DIR__ . '/lib/main.php';


$payment_name = 'dolyame';
$dolyame = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($dolyame['params']));
$setting = System::getSetting();

$api = new Dolyame_functions($params);

$api->setScriptURL($setting['script_url'] . "/payments/{$payment_name}");


$order_id = (int) $_POST['order_id'];
$order_link = $setting['script_url'] . "/pay/{$_POST['order_date']}";

$order_items = Order::getOrderItems($order_id);
$user_info = [];

if(!empty($_POST['client_email']))
    $user_info['email'] = $_POST['client_email'];

$order = [];
if (!empty($_POST['ship_method_id'])) {
    $order = [
        'ship_method_id' => $_POST['ship_method_id']
    ];
}

require_once __DIR__ . '/lib/sm_fns.php';

$order_items = orderItems($order_id, $order, $params);

$items = $order_items['items'];
$amount = $order_items['amount'];

$link = $api->CreateLink($order_id, $amount, $items, $user_info);

if(!$link || !is_string($link)){

    setcookie('payment_error', 
        json_encode(
            isset($_COOKIE['payment_error']) 
            ? json_decode($_COOKIE['payment_error'], true)[] = [$dolyame['payment_id']]
            : [$dolyame['payment_id']] ),
        time() + 300, '/'
    );

    $link = $order_link . "?payment_error#*";
    
    AdminNotice::addNotice('Ошибка работы платежной системы "Долями"', '/admin/paysettings/' . $dolyame['payment_id']);
}

header('Location: ' . $link);
exit();

?>