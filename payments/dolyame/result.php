<?php define('BILLINGMASTER', 1);

$webhook = file_get_contents('php://input');

if(empty($webhook)){
    header('Location /');
    exit();
}

require __DIR__ . '/lib/webhook.php';

$wh_api = new Dolyame_webhook($webhook);

if(!$wh_api->isVerify()){ // (?) запрос не от Долями / недостаточно данных
    header('HTTP/1.1 403 no access');
    exit('No access');
}

$status = $wh_api->getStatus();

if(!in_array($status, ["completed", "wait_for_commit"]))
    exit('ok');

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');


$payment_name = 'dolyame';
$dolyame = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($dolyame['params']));

$order_id = (int) $wh_api->getOrderID();

$order = empty($order_id) ?: Order::getOrder($order_id);

if (!$order) 
    exit('order not found');


$summ = Order::getOrderTotalSum($order_id);

$ship_method = empty($order['ship_method_id']) ? null : System::getShipMethod($order['ship_method_id']);

if ($ship_method && isset($ship_method['tax']))
    $summ += $ship_method['tax'];

$summ = number_format($summ, 2, '.', '');

if ($summ != $wh_api->getOrderAmount()) 
    exit('Wrong payment summ');

$res = 'end.';

if($status == 'wait_for_commit'){

    require_once __DIR__ . '/lib/main.php';
    require_once __DIR__ . '/lib/sm_fns.php';

    $api = new Dolyame_functions($params);

    $order_items = orderItems($order_id, $order, $params);

    $items = $order_items['items'];
    $amount = $order_items['amount'];

    $res = $api->orderCommit($order_id, $amount, $items);
}

elseif($status == 'completed'){

    $res = Order::renderOrder($order, $dolyame['payment_id']);
}

echo is_string($res) ? $res : '\boolean: ' . ($res ? 'true' : 'false');

exit();