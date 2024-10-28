<?php defined('BILLINGMASTER') or die;



// Чтение и декодирование данных callback
$callback_data = json_decode(file_get_contents('php://input'), true);
Log::add(0,'Curl error', ["error" => $callback_data],'return.log');

if (isset($callback_data['orderId']) && isset($callback_data['status'])) {
    $order_id = intval($callback_data['orderId']);
    $status = $callback_data['status'];
    Log::add(5, 'Curl status',  ["error" => $status],'return.log');
    Log::add(5,'OK', ["order_id" => $order_id],'return.log');


    // Получение данных заказа
    $order = Order::getOrderDataByID($order_id,100);
    Log::add(5,'OK', ["order_id2" => $order_id],'return.log');

    if ($status =="success") {
        Order::renderOrder($order);

        Log::add(5,'OK', ["order_id3" => $order_id],'return.log');

        echo "OK $order_id";
    } elseif ($status == 'fail') {
    
        Log::add(5,'fail', ["Payment failed"=> $order_id],'return.log');


        echo "Payment failed";
    } else {
        echo "Unknown status";
        Log::add(5,'Unknown ', ["Unknown status"=> $callback_data],'return.log');


    }
} else {
    echo "Invalid callback data";
    Log::add(5,'OK', ["Invalid callback data"=> $callback_data],'return.log');

}
?>
