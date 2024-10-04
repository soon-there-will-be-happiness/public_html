<?php defined('BILLINGMASTER') or die; defined('PAYMENT_DIR') or die;


$d_order = $api->orderInfo($order_id);

if(!$order)
	ANSWER(false, "Заказ не найден в системе.", ['log' => $d_order]);

if(!$d_order)
	ANSWER(false, "Заказ не найден в системе Долями", ['log' => $d_order]);

if($d_order['status'] == 'wait_for_commit'){
	require_once PAYMENT_DIR . '/lib/main.php';
    require_once PAYMENT_DIR . '/lib/sm_fns.php';

    $api = new Dolyame_functions($params);

    $order_items = orderItems($order_id, $order, $params);

    $items = $order_items['items'];
    $amount = $order_items['amount'];

    $res = $api->orderCommit($order_id, $amount, $items);

    ANSWER(true, "Заказ подтвержден", ['log' => $res]);
}

ANSWER(false, "Статус заказа не позволяет его подтвердить.", ['log' => $d_order]);
