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
// Данные для подключения
$order_items = Order::getOrderItems($order['order_id']);
$out_summ = $total . '00';
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

// Данные чека
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




/*


// Данные чека
$data = [
    'timestamp' => "14.11.2024 23:14:00",
    'external_id' => '892924433234522515',

    'receipt'=>[
        'client'=>[
            'email'=>'karsakovkirilo@gmail.com',
            'phone'=>'+7999999999'
        ],
        'company'=>[
            'email'=>'thecareer36@gmail.com',
            'sno'=>'usn_income',
            'inn'=>'3664246543',
            'payment_address'=>'394018, г Воронеж, ул Платонова, д 25, помещ 512',
        ],
        'items'=>[
            'name'=>'Test',
            'price'=>100,
            'quantity'=>1,
            'measure'=>0,
            'sum'=>100,
            'payment_method'=>'full_prepaymen',
            'payment_object'=>'1',
            'type'=>1,
            'vat'=>['type'=>'none']
        ],
        ],
       
        'payments'=>["type"=>1,'sum'=>100],
        'total'=>100,
    
];
$token=AutoToken::CheckToken($login,$pass);

echo $token;
// Отправка запроса
$headers = [
    "Content-Type: application/json, charset=utf-8",
    "Token: $token",
];

$ch = curl_init("URL");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);//
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$payment_data = json_decode($response, true);
// Проверка на массив
if (is_array($payment_data['error'])) {
    $payment_url = json_encode($payment_data['error']); // Преобразуем массив в строку
} else {
    $payment_url = $payment_data['error'] ?? ''; // Если это не массив, оставляем как есть
}

echo $http_code;
echo $payment_url;

echo $token;*/








?>


