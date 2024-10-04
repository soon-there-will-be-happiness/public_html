<?php define('BILLINGMASTER', 1);

if (!empty($_POST)) {

    // Настройки
    require_once(dirname(__FILE__) . '/../../components/db.php');
    require_once(dirname(__FILE__) . '/../../config/config.php');
    require_once(dirname(__FILE__) . '/../../lib/payments/modulbank/lib/fpayments.php');

    $root = dirname(__FILE__) . '/../../';
    define('ROOT', $root);
    define("PREFICS", $prefics);

    require_once(ROOT . '/components/autoload.php');

    //Настройки paybox
    $payment_name = 'paybox';
    $paybox = Order::getPaymentSetting($payment_name);
    $params = unserialize(base64_decode($paybox['params']));
    $secret_key = trim($params['paybox_merchant_secret']);
    $merchant_id = trim($params['pg_merchant_id']);

    //Проверка
    $order_id = intval($_POST['pg_order_id']);
    $order = !empty($order_id) ? Order::getOrderDataByID($order_id, 0) : null;
    if (!$order) {
        exit('order not found');
    }
    
    $data = print_r($_POST, true);

    $sig = paybox::generateSign($_POST, $secret_key);

    if ($sig != $_POST['pg_sig']) {
        exit(json_encode([
            "pg_status" => "error",
        ]));
    }

    if ($_POST['pg_result'] == 1) {
        $render = Order::renderOrder($order, $paybox['payment_id']);
    } else {
        exit(json_encode([
            "pg_status" => "rejected",
        ]));
    }

    $log = Order::writePayLog($order['order_date'], null, 'All', $data, $paybox['payment_id']);

    exit(json_encode([
        "pg_status" => "ok",
    ]));

}