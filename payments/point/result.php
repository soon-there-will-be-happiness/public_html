<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));


// Чтение и декодирование данных callback
$callback_data = json_decode(file_get_contents('php://input'), true);
Log::add(0,'Curl error', ["error" => $callback_data],'return.log');

if (isset($callback_data['orderId']) && isset($callback_data['status'])) {
    $order_id = intval($callback_data['orderId']);
    $status = $callback_data['status'];
    $order = Order::getOrderDataByID($order_id,100);
    if ($status =="success") {
        Order::renderOrder($order);
        $order = Order::getOrderDataByID($order_id,100);
        $order_items = Order::getOrderItems($order['order_id']);
        $out_summ =$order['summ'];
        $items = array();
        $token = $params['token'];
        $api_url = $params['url'];

        Cyclops::Run($order);
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
