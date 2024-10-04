<?php define('BILLINGMASTER', 1);

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

//Загрузка классов
require_once (ROOT . '/components/autoload.php');
require_once "load.php";

if (empty($_REQUEST)) {
    jresponse("Нет данных запроса!");
}



if (!isset($_REQUEST["mdOrder"]) || !isset($_REQUEST["orderNumber"]) || !isset($_REQUEST["checksum"]) || !isset($_REQUEST["operation"])) {
    jresponse("Нет нужных данных запроса!");
}

$smOrder = $_REQUEST["orderNumber"];//номер заказа SM
$sberOrder = $_REQUEST["mdOrder"];//номер заказа сбера

$sberParams = Order::getPaymentSetting("sberbank");
$sberParams = unserialize(base64_decode($sberParams['params']));
$openKey = $sberParams['key'] ?? die("нет токена");//Нужно брать из настроек

$sign = SberBank::generateSign($_REQUEST, $openKey);

if ($sign !== strtoupper($_REQUEST['checksum'])) {
    jresponse("Заказ не подтвержден! Подпись не совпадает", 200);
}

$order = Order::getOrder($smOrder);

if (!$order) {
    jresponse("Заказа не существует", 400);
}

if ($_REQUEST['operation'] == "deposited" && $_REQUEST['status'] == 1) {
    $order = Order::renderOrder($order);
} else {
    jresponse("Заказ не подтвержден!", 200, $_REQUEST);
}

jresponse("Заказ подтвержден!", 200);
