<?php define('BILLINGMASTER', 1);

$request = json_decode(file_get_contents("php://input"));

if (!empty($request)) {

    // Настройки системы
    require_once(dirname(__FILE__) . '/../../components/db.php');
    require_once(dirname(__FILE__) . '/../../config/config.php');

    $root = dirname(__FILE__) . '/../../';
    define('ROOT', $root);
    define("PREFICS", $prefics);

    require_once (ROOT . '/components/autoload.php');


    // настройки Тинькофф
    $payment_name = 'tinkoff';
    $tinkoff = Order::getPaymentSetting($payment_name);
    $params = unserialize(base64_decode($tinkoff['params']));

    $order_id = intval($request->OrderId);
    if (!$order_id) {
        exit('id is null');
    }

    $order = Order::getOrderDataByID($order_id, 100);
    if (!$order) {
        exit('order not found');
    }

    $notificationModel = new TinkoffNotification($params['terminal_key'], $params['secret_key']);

    try {
        $notificationModel->checkNotification($request);

        $tax = 0;
        $ship_method = !empty($order['ship_method_id']) ? System::getShipMethod($order['ship_method_id']) : null;
        if ($ship_method && $ship_method['tax'] != 0) {
            $tax = $ship_method['tax'] * 100;
        }

        $summ = Order::getOrderTotalSum($order_id) * 100 + $tax;
        $paid_summ = $request->Amount;

        //подключение лога оплаты
        $subscription_id = isset($_POST['SubscriptionId']) ? htmlentities($_POST['SubscriptionId']) : null;//new log pay
        $query = "";//new log pay
        foreach($_REQUEST as $key => $value) {//new log pay
            $query .= "&".$key."=".$value;//new log pay
        }

        if ($summ == $paid_summ && $notificationModel->isOrderPaid()) {
            // Рендерим заказ
            $render = Order::renderOrder($order, $tinkoff['payment_id']);
            $log = Order::writePayLog($order['order_date'], $subscription_id, 'Pay', $query, $tinkoff['payment_id']);//new log pay
            //$send = Email::SendMessageToBlank('report@kasyanov.info', 'Oleg', 'Ответ Тинькофф на result', $tinkoff['payment_id']);
        }
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}