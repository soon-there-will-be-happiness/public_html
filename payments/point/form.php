<?php defined('BILLINGMASTER') or die;
$setting = System::getSetting();
$inv_id = $order['order_id'];


$params = unserialize(base64_decode($payment['params']));
$record = PointDB::findRecordByOrderId( $inv_id);
$token = $params['token'];
$api_url = $params['url'];

$inv_id='0'.$inv_id;
if (!$record) {
    $token = $params['token'];
    $api_url = $params['url'];
    $merchantId = $params['merchantId'];
    $customerCode = $params['customerCode'];

    $out_summ =$order['summ'];
    $order_items = Order::getOrderItems($order['order_id']);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://enter.tochka.com/uapi/acquiring/v1.0/subscriptions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
          "Data": {
              "customerCode":"'.$customerCode.'",
              "amount":  '.floatval($out_summ).',
              "purpose": "Оплата за курс",
              "redirectUrl": "'.$setting['script_url'].'/payments/point/result?id='.$order['order_id'].'",
              "failRedirectUrl":  "'.$setting['script_url'].'/payments/point/result",
              "merchantId": '.$merchantId.'",
              "Options": {
                  "trancheCount": 13,
                  "period": "Day",
                  "daysInPeriod": 1
              }
          }
      }',
        CURLOPT_HTTPHEADER => array(
          'Authorization: Bearer '.$token,
          'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code == 200) {
        $payment_data = json_decode($response, true);
        if (isset($payment_data['Data'])) {
            $data = $payment_data['Data'];
            $payment_url = $data['paymentLink'] ?? '';
            $operation_id = $data['operationId'] ?? '';
            
            if (!empty($payment_url)) {
                PointDB::insertRecord(htmlspecialchars($payment_url), $inv_id, false, $operation_id);
            } else {
                echo 'Ошибка: URL для оплаты не найден в ответе.';
            }
        } else {
            echo 'Ошибка: неверный формат ответа, отсутствует ключ "Data".';
        }
    } else {
        $error_message = print_r($response, true);
        LogEmail::PaymentError($error_message, "point/result.php", "sell");
        echo $error_message;
    }
}
$record = PointDB::findRecordByOrderId($inv_id);
if($record){
 
    echo '<a type="submit" class="payment_btn" value="' . System::Lang('TO_PAY') . '" href='."'" . $record['url'] ."'" . '>' . System::Lang('TO_PAY') . '</a>';

}
else{
    echo 'Ошибка: ';
}
?>


