<?php
define('BILLINGMASTER', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once (dirname(__FILE__) . '/cyclopsApi.php');
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');
require_once (ROOT . '/vendor/autoload.php');
System::enableLongWaitForQueries();
use Dotenv\Dotenv;

// Assuming the .env file is in the root directory
$dotenv = Dotenv::createImmutable(__DIR__."/../");
$dotenv->load();
$api = CyclopsApi::getInstance();
$response = $api->listPayments(1,50,['identify' => false]);
Log::add(1,'cron execute', ["response" => $response],'cyclops_cron');

$records = PointDB::getRecordsWithStatus();

// Проход по каждому платежу, полученному из API
if (isset($response['result']['payment']) && !empty($response['result']['payment'])) {
    $payment = $response['result']['payment'];

    // Преобразуем дату из API в объект DateTime
    $paymentDate = DateTime::createFromFormat('Y-m-d', $payment['document_date']);
    $paymentAmount = $payment['amount'];
    $paymentId = $payment['id'];
    if(Payments::getPaymentTochkaByPaymentId( $paymentId )==null){
        Payments::addPaymentTochka($payment_id,   $paymentAmount,  $paymentDate,json_decode($payment));
    }
    foreach ($response['result']['payments'] as $payment) { 
        $api->refundPayment($payment);
    }
    // Проходим по всем записям из базы данных
    foreach ($records as $record) {
        // Преобразуем дату заказа из базы данных в объект DateTime
        $orderDate = DateTime::createFromFormat('Y-m-d', $record['order_date']);
        $orderAmount = $record['summ'];  // Сумма из базы данных
        $orderId = $record['order_id'];  // Order ID из базы данных
        $pointId = $record['point_id'];  // Point ID из базы данных

        // Проверка: дата платежа позже даты заказа и сумма совпадает
        if ($paymentDate > $orderDate && abs($paymentAmount - $orderAmount) < 0.0001) {
            // Если совпадение по времени и сумме, добавляем платеж
           /* Cyclops::addPayment([
                'point_id' => $pointId,
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'amount' => $paymentAmount,
                'status' => $payment['status'],
                'url' => $record['url'],  // Добавляем URL из базы данных
                'operationId' => $record['operationId'],  // Добавляем operationId из базы данных
            ]);*/

            // Логируем успешное сопоставление
            Log::add(1, 'payment match', [
                'point_id' => $pointId,
                'order_id' => $orderId,
                'payment_id' => $paymentId
            ], 'cyclops_match');

        }
    }
}

$matched_payments=Payments::getJoinedOrdersAndPayments();
$payments_tochkas=Payments::getAllPaymentsTochka(status: 'unmatched');
foreach ($payments_tochkas as $payments_tochka) {
    $i=0;
    $amount=floatval($payments_tochka["amount"]);
    foreach($matched_payments as $matched_payment){
        if( $amount<=0){
            break;
        }else{
            $amount-=floatval($matched_payment["amount"]);
        }
    }
    if($i>0){
        Payments::updatePaymentStatus($payments_tochka['id'],'matched');
        for($j=0;$j<$i;$j++){
            Payments::updatePaymentId($matched_payments[$j]['id'],$payments_tochka['id']);
        }
    }
    else{
        Log::add(1, 'unmatched pay', [
            'payments_tochka' => $payments_tochka['id'],
            ], 'cyclops_match');
    }
}

