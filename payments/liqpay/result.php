<?php define('BILLINGMASTER', 1);

if (!empty($_POST['data']) && !empty($_POST['signature'])) {

    // Настройки системы
    require_once(dirname(__FILE__) . '/../../components/db.php');
    require_once(dirname(__FILE__) . '/../../config/config.php');

    $root = dirname(__FILE__) . '/../../';
    define('ROOT', $root);
    define("PREFICS", $prefics);

    require_once (ROOT . '/components/autoload.php');


    // настройки LiqPay
    $payment_name = 'liqpay';
    $liqpay = Order::getPaymentSetting($payment_name);
    $params = unserialize(base64_decode($liqpay['params']));
    $public_key = trim($params['public_key']);
    $private_key = trim($params['private_key']);

    $json = $_POST['data'];
    $received_data = json_decode(base64_decode($json), true);
    $order_id = intval($received_data['order_id']);

    $order = !empty($order_id) ? Order::getOrderDataByID($order_id, 100) : null;
    if(!$order) exit('order not found');

    $amount = Order::getOrderTotalSum($order_id);
    if (!empty($order['ship_method_id'])) {
        $ship_method = System::getShipMethod($order['ship_method_id']);
        $amount += $ship_method['tax'];
    }

    if ($received_data['amount'] != $amount) {
        exit('Wrong payment amount');
    } elseif ($received_data['currency'] != $params['currency']) {
        exit('Wrong payment currency');
    }

    $generated_signature = base64_encode(sha1($private_key.$json.$private_key, 1));

//подключение лога оплаты
    $subscription_id = isset($_POST['SubscriptionId']) ? htmlentities($_POST['SubscriptionId']) : null;//new log pay
    $query = "";//new log pay
    foreach($_REQUEST as $key => $value) {//new log pay
        $query .= "&".$key."=".$value;//new log pay
    }

    if ($received_data['status'] == 'success' && $_POST['signature'] == $generated_signature && $received_data['public_key'] == $public_key) {
        // Рендерим заказ
        $render = Order::renderOrder($order, $liqpay['payment_id']);
        $log = Order::writePayLog($order['order_date'], $subscription_id, 'Pay', $query, $liqpay['payment_id']);//new log pay
        //$send = Email::SendMessageToBlank('report@kasyanov.info', 'Oleg', 'Ответ Liqpay на result', $liqpay['payment_id']);
    }
}
