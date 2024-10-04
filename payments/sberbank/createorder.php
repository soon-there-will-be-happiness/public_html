<?php define('BILLINGMASTER', 1);

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);
ini_set('display_errors', 1);

//Загрузка классов
require_once (ROOT . '/components/autoload.php');
require_once "load.php";

//header('Content-Type: application/json; charset=utf-8');

if (!isset($_REQUEST['orderid'])) {
    jresponse("ID заказа отсутствует");
}

$orderId = intval($_REQUEST['orderid']);
$order = Order::getOrder($orderId);

if (!$order) {
    jresponse("Заказа не существует");
}

$total = Order::getOrderTotalSum($orderId);

$sberParams = Order::getPaymentSetting("sberbank");
$sberParams = unserialize(base64_decode($sberParams['params'])); //login, password

if ($sberParams['login'] == "" || $sberParams['password'] == "") {
    jresponse("Не указаны данные от платежного сервиса!");
}

$sber = new SberBank($sberParams['login'], $sberParams['password']);

$response = $sber->createOrder($order, $total)->addOrderBundle($order)->send();

if (!$response) {
    return jresponse("Не известная ошибка", 500, $response);
}

if (isset($response['errorCode']) && $response['errorCode'] != 0) { // если произошла ошибка
    return jresponse($response['errorMessage']);
}

//Сохранить созданную ссылку на оплату
$orderInfo = unserialize(base64_decode($order['order_info']));
$orderInfo['sberbankFormUrl'] = $response['formUrl'];
$orderInfo['sberbankOrderId'] = $response['orderId'];
$orderInfo = base64_encode(serialize($orderInfo));

if (method_exists(Order::class, "updateOrderInfo")) {
    Order::updateOrderInfo($orderId, $orderInfo);
} else {
    $db = Db::getConnection();
    $sql = 'UPDATE '.PREFICS.'orders SET order_info = :order_info WHERE order_id = '.$orderId;

    $result = $db->prepare($sql);
    $result->bindParam(':order_info', $newData, PDO::PARAM_STR);

    $result->execute();
}

return jresponse("Заказ создан!", 201, $response);
