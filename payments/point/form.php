<?php defined('BILLINGMASTER') or die;
$setting = System::getSetting();
$inv_id = $order['order_id'];


$params = unserialize(base64_decode($payment['params']));
$record = AtolDB::findRecordByOrderId( $inv_id);
$token = $params['token'];
$api_url = $params['url'];

?>


