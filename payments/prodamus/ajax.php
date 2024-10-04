<?php

define('BILLINGMASTER', 1);

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);
/*header('Content-Type: application/json; charset=utf-8');*/
require_once (ROOT . '/components/autoload.php');

require_once 'Hmac.php';

// настройки Prodamusa
$payment_name = 'prodamus';
$robox = Order::getPaymentSetting($payment_name);
/*$currencies = Currency::getCurrencyList();*/
$params = unserialize(base64_decode($robox['params']));

$secret_key = $params['prodamus_secret_key'];

$request = $_REQUEST;


if (isset($request) && key_exists('enable_'.$request['currency'], $params) ) {
    //сгенерировать подпись
    if (isset($request['order_date'])) {
        $order = Order::getOrderData($request['order_date'], 0, 1);
    } else {
        die(json_encode(['message'=>'order_date отсутствует']));
    }

    if (isset($order)) {
        $order_items = Order::getOrderItems($order['order_id']);
    } else {
        die(json_encode(['message'=>'нет такого заказа']));
    }
    $payment = order::getPaymentSetting('prodamus');
    $pay_object_delivery = 'commodity';

    switch ($request['currency']) {
        case 'rub':
            break;
        case 'eur':
            break;
        case 'usd':
            break;
        default:
            die(json_encode(['message'=>'Не правильный тип валюты. Нужно usd/rub/eur']));
            break;
    }


    $selected_currency = $request['currency'];
    $order_items_for_payments = Price::changeOrderItemsPriceWithDeposits($order, $order_items);

    @ob_end_clean();
    ob_start();
    require_once 'form.php';
    $formdata = ob_get_contents();
    ob_end_clean();

    header("content-type: json");
    http_response_code(201);
    $json = json_encode(['link'=>$formdata]);
    echo $json;
    die();
}
die(json_encode(['message'=>'ошибка. Отсутствуют данные']));