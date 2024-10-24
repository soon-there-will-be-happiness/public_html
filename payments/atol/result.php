<?php define('BILLINGMASTER', 1);

// Load configurations
require_once(dirname(__FILE__) . '/../../models/system.php');
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');
require_once(dirname(__FILE__) . '/../../models/order.php');
require_once(dirname(__FILE__) . '/../../models/email.php');

// Callback handling
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['orderId']) && isset($data['status'])) {
    $order_id = $data['orderId'];
    $status = $data['status'];
    
    // Fetch order details from the database
    $order = Order::getOrderDataByID($order_id, 0);

    if ($status == 1) {
        // Payment was successful
        echo "Payment successful!";
        Order::updateOrderStatus($order_id, 'paid');
    } else {
        // Payment failed
        echo "Payment failed: " . $data['statusMessage'];
        Order::updateOrderStatus($order_id, 'failed');
    }
} else {
    echo "Invalid response from АТОЛ Pay";
}
?>
