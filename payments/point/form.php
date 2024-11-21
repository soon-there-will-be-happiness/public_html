<?php defined('BILLINGMASTER') or die;
$setting = System::getSetting();
$inv_id = $order['order_id'];


$params = unserialize(base64_decode($payment['params']));
$record = PointDB::findRecordByOrderId( $inv_id);
$token = $params['token'];
$api_url = $params['url'];
$login= $params['login'];
$pass=$params['password'];
$inv_id='0'.$inv_id;
if (!$record) {
    $token = $params['token'];
    $api_url = $params['url'];

    $customerCode = $params['customerCode'];

    $out_summ =$order['summ'];
    $order_items = Order::getOrderItems($order['order_id']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL =>  $api_url.'/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "Data": {
            "customerCode": '.$customerCode.',
            "amount": '.floatval($out_summ).',
            "purpose": "Оплата за курс",
            "paymentMode": ["sbp","card"],
            "redirectUrl": "'.$setting['script_url'] . '/payments/point/result?id='.$order['order_id'].'"
            }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',"Authorization: Bearer $token",
        ),
    ));
    $response = curl_exec($curl);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($curl);
    if ($http_code == 200) {
        $payment_data = json_decode($response, true);
        $payment_url = $payment_data['Data']['paymentLink'] ?? '';
        if (!empty($payment_url)) {
            PointDB::insertRecord( htmlspecialchars($payment_url),$inv_id,false,$payment_data['Data']['operationId'] );
        } else {
            echo 'Ошибка: URL для оплаты не найден в ответе.';
        }
    }
    else{
        LogEmail:: PaymentError( json_encode( $response['Errors']['message']), "point/result.php","sell");
    }
}
$record = PointDB::findRecordByOrderId($inv_id);
if($record){
    echo '<form action="' . $record['url'] . '" method="POST">';
    echo '<input type="submit" class="payment_btn" value="' . System::Lang('TO_PAY') . '"/>';
    echo '</form>';
}
else{
    echo 'Ошибка: ';
}
?>


