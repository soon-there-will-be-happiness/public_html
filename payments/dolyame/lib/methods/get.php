<?php defined('BILLINGMASTER') or die; defined('PAYMENT_DIR') or die;


$d_order = $api->orderInfo($order_id);

if(!$order)
	ANSWER(false, "Заказ не найден в системе.", ['log' => $d_order]);

if($order['payment_id'] != $dolyame['payment_id'])
	ANSWER(false, "Заказ оплачен другим способом или не оплачен вовсе.", ['log' => $d_order]);

if(!$d_order)
	ANSWER(false, "Заказ не найден в системе Долями", ['log' => $d_order]);

ANSWER(false, "Заказ успешно найден:", ['log' => $d_order]);
