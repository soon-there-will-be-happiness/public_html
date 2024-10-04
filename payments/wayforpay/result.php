<?php define('BILLINGMASTER', 1);

require_once __DIR__ . '/wayforpay.php';

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');

$payment_name = 'wayforpay';
$robox = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($robox['params']));

// TODO проверка результата оплаты будет тут
$data = json_decode(file_get_contents("php://input"), true);

$w4p = new WayForPay();
$key = $params['secretkey'];;
$w4p->setSecretKey($key);

$paymentInfo = $w4p->isPaymentValid($data);

$order_id='';

if ($paymentInfo === true) {
    
    list($order_id,) = explode(WayForPay::ORDER_SEPARATOR, $data['orderReference']);

    $message = '';

    $order = Order::getOrderDataByID($order_id, 100);

    //подключение лога оплаты
    $subscription_id = isset($_POST['SubscriptionId']) ? htmlentities($_POST['SubscriptionId']) : null;//new log pay
    $query = "";//new log pay
    foreach($_REQUEST as $key => $value) {//new log pay
        $query .= "&".$key."=".$value;//new log pay
    }

    if ($order){
        $render = Order::renderOrder($order, $robox['payment_id']);
        $log = Order::writePayLog($order['order_date'], $subscription_id, 'Pay', $query, $robox['payment_id']);//new log pay
        echo $w4p->getAnswerToGateWay($data);
    } else {
        echo $paymentInfo;
    }
} else {
    echo $paymentInfo;
}
exit();
?>