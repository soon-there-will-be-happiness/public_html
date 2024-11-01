<?php
require_once (dirname(__FILE__) . 'cyclopsApi.php');
$api = CyclopsApi::getInstance();
$response = $api->listPayments(1,50,['identify' => false]);
if (isset($response['result']['payments']) && !empty($response['result']['payments'])) {
    // Проходим по каждому платежу
    foreach ($response['result']['payments'] as $paymentId) {
        // Вызов метода addPayment для каждого платежа
        Cyclops::addPayment($paymentId);
    }
}
