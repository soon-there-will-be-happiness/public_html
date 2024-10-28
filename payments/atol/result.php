<?php defined('BILLINGMASTER') or die;



// Чтение и декодирование данных callback
$callback_data = json_decode(file_get_contents('php://input'), true);
Log::add(0,'Curl error', ["error" => $callback_data],'return.log');
Log::add(5,'Curl callback_data', context: ["callback_data" => $callback_data],type: 'return.log');
// Проверка необходимых параметров
if (isset($callback_data['orderId']) && isset($callback_data['status'])) {
    $order_id = intval($callback_data['orderId']);
    $status = $callback_data['status'];
    Log::add(5,'Curl error', ["error" => $status],'return.log');

    // Получение данных заказа
    $order = Order::getOrderDataByID($order_id,100);

    if ($status == 'success') {
        Order::renderOrder($order_id);
        Order::updateOrderStatus($order_id, 'paid');
        echo "OK$order_id";
    } elseif ($status == 'fail') {
        Order::updateOrderStatus($order_id, 'failed');
        echo "Payment failed";
    } else {
        echo "Unknown status";
    }
} else {
    echo "Invalid callback data";
}
?>
