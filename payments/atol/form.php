<?php defined('BILLINGMASTER') or die;

$params = unserialize(base64_decode($payment['params']));
$shop_id = $params['ya_shop_id'];
$scid = $params['ya_scid'];

// ATOL Pay integration parameters
$api_url = "https://new-api-mobile.atolpay.ru/v1/ecom/payments";
$token = "<YOUR_API_TOKEN>"; // Use your API token

// Data for the payment request
$data = array(
    "amount" => intval($total * 100), // amount in kopecks (e.g. 10000 = 100 rubles)
    "orderId" => $order['order_id'],
    "sessionType" => "oneStep", // Single-stage payment
    "additionalProps" => array(
        "returnUrl" => "https://your-website.com/payment-success",
        "notificationUrl" => "https://your-website.com/payment-notification",
    ),
    "receipt" => array(
        "buyer" => array("email" => $order['client_email']),
        "positions" => array(
            array(
                "name" => "Order Payment",
                "price" => intval($total * 100),
                "quantity" => 1,
                "paymentMethod" => 1,
                "paymentSubject" => 1,
                "tax" => 0,
            ),
        ),
        "providerId" => 0,
        "sno" => 0
    ),
    "paymentMethods" => array(
        array(
            "paymentType" => "card",
            "bankId" => 100 // Specify bank here, e.g. 100 for Alfa-Bank
        )
    )
);

// Make the API request
$options = array(
    'http' => array(
        'header' => "Authorization: Bearer $token\r\nContent-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
    )
);
$context = stream_context_create($options);
$response = file_get_contents($api_url, false, $context);
$result = json_decode($response, true);

// Redirect to the payment URL
if (isset($result['paymentUrl'])) {
    header("Location: " . $result['paymentUrl']);
    exit;
} else {
    echo "Error: Payment could not be initiated. " . $result['errorMessage'];
}
?>

