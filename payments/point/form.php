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
    $items_string= '"Items": [';
    $order_items = Order::getOrderItems($order['order_id']);
    foreach($order_items as $item){
        $items_string.='
          {
                "vatType": "",
                "name": '.$item['product_name'].',
                "amount": '. intval($item['price'] . '00').',
                "quantity": 1,
                "paymentMethod": "0",
                "paymentObject": "10",
                "measure": "0"
            }
        ';
    }
     $items_string.='   ]';


    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL =>  $api_url.'/payments_with_receipt',
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
            "amount": '.intval($out_summ).',
            "purpose": "Оплата за курс",
            "redirectUrl": '.$setting['script_url'] . '/payments/atol/success?id='.$order['order_id'].',
            "failRedirectUrl": '.$setting['script_url'] . '/payments/point/success?id='.$order['order_id'].',
            "paymentMode": ["sbp","card"],
            "merchantId": "",
            "taxSystemCode": "",
            "Client": {
            "name":  '.$order['client_name'].',
            "email":'.$order['client_email'].',
            "phone": '.$order['client_phone'].'
        },
        ' .$items_string.'
            }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',"Authorization: Bearer $token",
        ),
    ));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
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


