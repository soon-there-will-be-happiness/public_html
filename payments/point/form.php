<?php defined('BILLINGMASTER') or die;
$setting = System::getSetting();
$inv_id = $order['order_id'];

$params = unserialize(base64_decode($payment['params']));
$record = PointDB::findRecordByOrderId($inv_id);
$token = $params['token'];
$api_url = $params['url'];

$inv_id = '0' . $inv_id;
if (!$record) {
    $token = $params['token'];
    $api_url = $params['url'];
    $merchantId = $params['merchantId'];
    $customerCode = $params['customerCode'];

    $out_summ = $order['summ'];
    $order_items = Order::getOrderItems($order['order_id']);
    
    // Формируем данные для запроса
    $requestData = [
        'Data' => [
            'customerCode' => $customerCode,
            'amount' => number_format(floatval($out_summ), 2, '.', ''), // Форматируем как денежную сумму
            'purpose' => "Оплата за курс",
            'redirectUrl' => $setting['script_url'] . '/payments/point/result?id=' . $order['order_id'],
            'failRedirectUrl' => $setting['script_url'] . '/payments/point/result',
            'merchantId' => $merchantId,
            'Options' => [
                'trancheCount' => 13,
                'period' => "Day",
                'daysInPeriod' => 28
            ]
        ]
    ];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url . '/subscriptions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($requestData, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $token, // Исправлено: подставляем реальный токен
            'Content-Type: application/json',
            'Accept: application/json'
        ),
    ));
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code == 200) {
        $payment_data = json_decode($response, true);
        
        // Извлекаем все необходимые данные из ответа
        $payment_url = $payment_data['Data']['paymentLink'] ?? '';
        $operationId = $payment_data['Data']['operationId'] ?? '';
        $status = $payment_data['Data']['status'] ?? '';
        $purpose = $payment_data['Data']['purpose'] ?? '';
        $amount = $payment_data['Data']['amount'] ?? '';
        $consumerId = $payment_data['Data']['consumerId'] ?? '';
        $recurring = $payment_data['Data']['recurring'] ?? false;
        $options = $payment_data['Data']['Options'] ?? [];
        $paymentLinkId = $payment_data['Data']['paymentLinkId'] ?? '';
        
        // Также извлекаем данные из Links и Meta если нужно
        $selfLink = $payment_data['Links']['self'] ?? '';
        $totalPages = $payment_data['Meta']['totalPages'] ?? 0;
        
        if (!empty($payment_url)) {
            PointDB::insertRecord( htmlspecialchars($payment_url),$inv_id,false,$payment_data['Data']['operationId'] );
        } else {
            echo 'Ошибка: URL для оплаты не найден в ответе.';
            LogEmail::PaymentError("URL для оплаты не найден. Ответ: " . $response, "point/result.php", "sell");
        }
    } else {
        $error_message = "HTTP код: " . $http_code . "\nОтвет: " . $response;
        LogEmail::PaymentError($error_message, "point/result.php", "sell");
        echo $error_message;
    }
}

$record = PointDB::findRecordByOrderId($inv_id);
if ($record) {
    echo '<a type="submit" class="payment_btn" value="' . System::Lang('TO_PAY') . '" href="' . $record['url'] . '">' . System::Lang('TO_PAY') . '</a>';
} else {
    echo 'Ошибка: не удалось создать запись для оплаты';
}
?>