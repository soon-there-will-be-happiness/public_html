<?php defined('BILLINGMASTER') or die; defined('PAYMENT_DIR') or die;

if(@ $_POST['show'] == 'form'){ // отправляем форму
	require_once PAYMENT_DIR . '/refund_modal.php';
	exit();
}

if(!$order)
	ANSWER(false, "Заказ не найден в системе.");

if($order['payment_id'] != $dolyame['payment_id'])
	ANSWER(false, "Заказ оплачен другим способом или не оплачен вовсе.");


$d_order = $api->orderInfo($order_id);

if(!$d_order)
	ANSWER(false, "Заказ не найден в системе Долями");

if($d_order['status'] != 'committed')
	ANSWER(false, "Данный заказ нельзя отправить на возврат.", ['log' => $d_order]);


if(isset($_POST['param']) && (int) trim($_POST['param']) == $order_id){

	require_once PAYMENT_DIR . '/lib/sm_fns.php';

	$items = orderItems($order_id, $order, $params);

	$refund_res = $api->orderRefund($order_id, $items['amount'], $items['items']);

	if(isset($refund_res['amount'], $refund_res['refund_id'])){
	    $currency = System::getMainSetting()['currency'];
		ANSWER(false, "Возврат оформлен! Клиенту будет возвращена стоимость товаров.", [
			'alert' => true,
			'action' => 'back',
			'log' => $d_order
		]);

	}else
		ANSWER(true, 
			isset($refund_res['message']) 
				? $refund_res['message'] 
				: "Данный заказ нельзя отправить на возврат.", 
			[
				'alert' => true,
				'action' => 'back',
				'log' => $d_order
			]
		);
}

ANSWER(true, "", [
	'amount' => $d_order['amount'],
	'email' => $order['client_email'],
	'order_id' => $order['order_id'],
	'order_edit_url' => "<a href=\"/admin/orders/edit/{$order['order_id']}\">перейти к заказу</a>",
	'log' => $d_order
]);
