<?php define('BILLINGMASTER', 1);

$request = json_decode(file_get_contents("php://input"), true);

// При необходимости включаем логирование.
//file_put_contents(__DIR__ . '/../log_tinkoffinstallments.txt', PHP_EOL . json_encode($request), FILE_APPEND);

if (!empty($request) && isset($request['status']) && isset($request['id']) && in_array($request['status'], ['approved', 'signed'])) {

    // Настройки системы
    require_once(dirname(__FILE__) . '/../../components/db.php');
    require_once(dirname(__FILE__) . '/../../config/config.php');

    $root = dirname(__FILE__) . '/../../';
    define('ROOT', $root);
    define("PREFICS", $prefics);

    require_once (ROOT . '/components/autoload.php');


    // настройки Тинькофф
    $payment_name = 'tinkoffinstallments';
    $tinkoff = Order::getPaymentSetting($payment_name);
    $params = unserialize(base64_decode($tinkoff['params']));

    $order_id = intval($request['id']);
    if (!$order_id) {
        exit('id is null');
    }

    $order = Order::getOrderDataByID($order_id, 100);
    if (!$order) {
        exit('order not found');
    }
	
	$subscription_id = null;
    ob_start();
    print_r($request);
    $buffer = ob_get_contents();
    ob_end_clean();
    $log = Order::writePayLog($order['order_date'], $subscription_id, 'Pay', $buffer, $tinkoff['payment_id']);

    $headers = [
        'Authorization: Basic '.base64_encode("{$params['showcase_id']}:{$params['password']}"),
        'Content-type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://forma.tinkoff.ru/api/partners/v2/orders/$order_id/info");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_NOBODY,false);
    curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    curl_close($ch);

    $result = $result ? json_decode($result, true) : null;
    if ($result) {
        $amount = Order::getOrderTotalSum($order_id);
        if ($result['status'] == 'signed' && $result['status'] != 'demo' && $result['order_amount'] == $amount) {
            $render = Order::renderOrder($order, $tinkoff['payment_id']);
        }
    }
}