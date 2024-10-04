<?php define('BILLINGMASTER', 1);

if (empty($_POST)) {
    $paid_data = json_decode(file_get_contents("php://input"));
    $response = array();
    if (!empty($paid_data)) {
        foreach ($paid_data as $key => $val) {
            $response[$key] = $val;
        }
    }
} else {
    $response = $_POST;
}

if (!empty($response)) {

    // Настройки системы
    require_once(dirname(__FILE__) . '/../../components/db.php');
    require_once(dirname(__FILE__) . '/../../config/config.php');

    $root = dirname(__FILE__) . '/../../';
    define('ROOT', $root);
    define("PREFICS", $prefics);

    require_once (ROOT . '/components/autoload.php');


    // настройки Fondy
    $payment_name = 'fondy';
    $fondy = Order::getPaymentSetting($payment_name);
    $params = unserialize(base64_decode($fondy['params']));
    $secret_key = trim($params['secret_key']);
    $merchant_id = trim($params['merchant_id']);

    $order_id = intval($response['order_id']);
    $order = !empty($order_id) ? Order::getOrderDataByID($order_id, 100) : null;
    if(!$order) exit('order not found');

    $amount = Order::getOrderTotalSum($order_id) * 100;
    if (!empty($order['ship_method_id'])) {
        $ship_method = System::getShipMethod($order['ship_method_id']);
        $amount += $ship_method['tax'] * 100;
    }

    if ($response['merchant_id'] != $merchant_id) {
        exit('wrong merchant ID');
    } elseif ($response['amount'] != $amount) {
        exit('wrong payment amount');
    } elseif ($response['currency'] != $params['currency']) {
        exit('wrong payment currency');
    }
    //подключение лога оплаты
    $subscription_id = isset($_POST['SubscriptionId']) ? htmlentities($_POST['SubscriptionId']) : null;//new log pay
    $query = "";//new log pay
    foreach($_REQUEST as $key => $value) {//new log pay
        $query .= "&".$key."=".$value;//new log pay
    }

    if ($response['order_status'] == 'approved'  && $response['signature'] == getSignature($response, $secret_key)) {
        // Рендерим заказ
        $render = Order::renderOrder($order, $fondy['payment_id']);

        $log = Order::writePayLog($order['order_date'], $subscription_id, 'Pay', $query, $fondy['payment_id']);//new log pay
        //$send = Email::SendMessageToBlank('report@kasyanov.info', 'Oleg', 'Ответ Fondy на result', $fondy['payment_id']);
        exit('OK');
    } else {
        $log = Order::writePayLog($order['order_date'], $subscription_id, 'Bad Sign', $query, $fondy['payment_id']); //new log pay
        exit('declined');
    }
}

function getSignature($params, $secret_key) {
    if (isset($params['response_signature_string'])){
        unset($params['response_signature_string']);
    }
    if (isset($params['signature'])){
        unset($params['signature']);
    }
    $params = array_filter($params,'strlen');
    ksort($params);
    $params = array_values($params);
    $str_params = $secret_key . '|' . implode('|',$params);

    return sha1($str_params);
}