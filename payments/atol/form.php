<?php defined('BILLINGMASTER') or die;
$setting = System::getSetting();
$inv_id = $order['order_id'];


$params = unserialize(base64_decode($payment['params']));
$record = AtolDB::findRecordByOrderId( $inv_id);
$token = $params['token'];
$api_url = $params['url'];
$login= $params['login'];
$pass=$params['password'];
$inv_id='0'.$inv_id;
if (!$record) {

$token = $params['token'];
$api_url = $params['url'];
$login= $params['login'];
$pass=$params['password'];

$order_items = Order::getOrderItems($order['order_id']);
$out_summ =$order['summ'];
$items = array();
foreach($order_items as $item){
    $items[] = [
        'name' => $item['product_name'],
        'quantity' => 1000,
        'price' => intval($item['price'] . '00'),
        'sum' => intval($item['price'] . '00'),
        'measure' => 0,
        'tax' => 5,
        'paymentMethod' => 0,
        'paymentSubject' => 10
    ];
}

$data = [
    'amount' => intval($out_summ),
    'orderId' => $inv_id,
    'sessionType' => 'oneStep',
    'additionalProps' => [
        'returnUrl' =>$setting['script_url'] . '/payments/atol/success?id='.$order['order_id'],
        'notificationUrl' => $setting['script_url'] . '/payments/atol/result',
    ],
    'receipt' => [
        'buyer' => [
            'email' => $order['client_email'],
        ],
        'positions' => $items,
        'providerId' => 100,
        'type' => 'sell',
        'sno' => 1
    ]
];

// Отправка запроса
$headers = [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
    "charset=utf-8"
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$error_data = json_decode($response, true);
$error_id = $error_data['error']['error_id'] ?? 'Неизвестная ошибка';

if(intval($error_id)==11){
        $token=AutoToken::CheckToken($token,$login,$pass);
        if( $token!=false){
                $headers = [
                    "Authorization: Bearer $token",
                    "Content-Type: application/json",
                    "charset=utf-8"
                ];
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
           
        }

    }

if ($http_code == 200) {
    $payment_data = json_decode($response, true);

    $payment_url = $payment_data['data']['paymentUrl'] ?? '';

    // Проверка наличия URL и создание формы
    if (!empty($payment_url)) {
        AtolDB::insertRecord( htmlspecialchars($payment_url),$inv_id,false);
    } else {
        echo 'Ошибка: URL для оплаты не найден в ответе.';
    }
} else {
    $error_data = json_decode($response, true);
    $error_id = $error_data['error']['error_id'] ?? 'Неизвестная ошибка';
    
    $error_message = $error_data['error']['text'] ?? 'Неизвестная ошибка';
//text":

    echo 'Ошибка: ' . $http_code . ' - ' . htmlspecialchars($error_message);
}
$record = AtolDB::findRecordByOrderId($inv_id);
}
if($record){
    echo '<form action="' . $record['url'] . '" method="POST">';
    echo '<input type="submit" class="payment_btn" value="' . System::Lang('TO_PAY') . '"/>';
    echo '</form>';
}
else{
    echo 'Ошибка: ';
}
?>


