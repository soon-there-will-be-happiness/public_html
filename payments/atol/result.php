<?php defined('BILLINGMASTER') or die;



// Чтение и декодирование данных callback
$callback_data = json_decode(file_get_contents('php://input'), true);
Log::add(0,'Curl error', ["error" => $callback_data],'return.log');

if (isset($callback_data['orderId']) && isset($callback_data['status'])) {
    $order_id = intval($callback_data['orderId']);
    $status = $callback_data['status'];
    // Получение данных заказа
    $order = Order::getOrderDataByID($order_id,100);
    if ($status =="success") {
        Order::renderOrder($order);
        echo "OK $order_id";
    } elseif ($status == 'fail') {
        echo "Payment failed";
    } else {
        echo "Unknown status";
    }
} else {
    echo "Invalid callback data";


}
?>
