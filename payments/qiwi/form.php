<?php
use qiwi\Api\BillPayments;

defined('BILLINGMASTER') or die;
require_once 'payments/qiwi/src/BillPayments.php';

$ya_goal = !empty($setting['yacounter']) ? "yaCounter".$setting['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $setting['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"'.$metriks : '';

$params = unserialize(base64_decode($payment['params']));
$business = $params['business'];

$inv_id = $order['order_id'];
$inv_desc= "Заказ №{$order['order_date']}";
if (isset($params['send_products']) && $params['send_products']) {
    $order_items = Order::getOrderItems($order['order_id']);
    $pod_list = $order_items ? implode(',', array_column($order_items, 'product_name')) : '';
    $inv_desc .= ";Товар".(count($order_items) > 1 ? 'ы:' : ':')."$pod_list";
}
$amount = $total. '.00';

$params = unserialize(base64_decode($payment['params']));
if(isset($params['business']) && $params['business'] != null){
    define('SECRET_KEY',$params['business'])   ;
    $billPayments = new BillPayments(SECRET_KEY);
}

$publicKey = $params['publicKey'] ;
$billId = $billPayments->generateId();
$params_qiwi = [
    'publicKey' => $publicKey,
    'amount' => $amount,
    'billId' => $billId,
    'comment' => base64_encode($inv_id),
    'successUrl' =>  'http://'. $_SERVER['SERVER_NAME'].'/payments/qiwi/success.php?key='. base64_encode($billId),
];
$link = $billPayments->createPaymentForm($params_qiwi);
?>

<a href = "<?=$link ?>" class = "payment_btn" >Оплатить</a>

