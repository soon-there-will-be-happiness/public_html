<?php defined( 'BILLINGMASTER') or die;
$payment = Order::getPaymentSetting('point');
$params = unserialize(base64_decode($payment['params']));
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = Order::getOrder($order_id);
//$point=PointDB::findRecordByOrderId($order_id );
$setting = System::getSetting(true);

{
    $order = Order::getOrder($order_id);
    //Order::renderOrder($order);
    $order_items = Order::getOrderItems($order['order_id']);
    //PointDB::updateStatusToTrue($order_id);
    AutoToken::sendCheck( $order);
}
System::redirectUrl($setting['script_url'] . '/payments/point/success?id='.$order_id);