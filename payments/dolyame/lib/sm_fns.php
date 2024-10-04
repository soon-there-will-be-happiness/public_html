<?php defined('BILLINGMASTER') or die;


function orderItems(int $order_id, array $order, array $params): array{

	$ship_method = empty($order['ship_method_id']) ? null : System::getShipMethod($order['ship_method_id']);

    $items = [];
    $amount = 0;

    $order_items = Order::getOrderItems($order_id);

    // Добавляем доставку в товары.
    if ($ship_method && @ $ship_method['tax'] > 0) {
        $order_items[] = [
            'product_name' => $ship_method['ship_desc'],
            'price' => $ship_method['tax']
        ];
    }

    foreach ($order_items as $item) {
        $price = $item['price'];

        $tmp_items = [
            'name' => html_entity_decode($item['product_name']),
            'price' => number_format(round($price, 2), 2, '.', ''),
            'quantity' => (int) '1.00'
        ];

        if(isset($params['tax_type'], $params['payment_method'])){
            $tmp_items['receipt'] = [
                'tax' => $params['tax_type'],
                'payment_method' => $params['payment_method']
            ];

            if(isset($params['payment_object']) && !empty($params['payment_object']))
                $tmp_items['payment_object'] = $params['payment_object'];
        }

        $items[] = $tmp_items;
        $amount += $price;
    }

    $amount = number_format(round($amount, 2), 2, '.', '');

    return [
    	'amount' => $amount,
    	'items' => $items,
    	'ship_tax' => @ $ship_method['tax']
    ];
}