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



//$records = PointDB::getRecordsWithStatus();


// Проход по каждому платежу, полученному из API
if (isset($response['result']['payments']) && !empty($response['result']['payments'])) {
    $payments = $response['result']['payments'];
    foreach ($payments as $payment) {

        $response = $api->getPayment($payment);
        $paymentDate = $response['result']['payment']['document_date'];
        $paymentAmount = $response['result']['payment']['amount'];
        if(Payments::getPaymentTochkaByPaymentId( $payment )==null){
            Payments::addPaymentTochka($payment,   $paymentAmount,  $paymentDate,json_decode($payment));
        }
    }
}

$com_per = 2.7; // Комиссия
$luft = 1; // Погрешность
$payments_tochkas = Payments::getAllPaymentsTochka('unmatched'); // Список платежей из Точки

foreach ($payments_tochkas as $payments_tochka) { // 7765
    $matched_payments = Payments::getJoinedOrdersAndPayments(); // Список продуктов 2990 2990/ 1000 2990 1000
    $total_commission = 0; // Общая комиссия для текущего платежа 258,26

    foreach ($matched_payments as $matched_payment) {
        // Порог для сопоставления с учетом комиссии и погрешности
        $threshold = $matched_payment["amount"] * (1 - ($com_per + $luft) / 100); // 2879,37 // 963
        Log::add(1, 'matched_payment', [
            'matched_payment' => $matched_payment,
            'if' => $payments_tochka["amount"] >= $threshold,
            'tocka'=>  $payments_tochka["amount"],
            'threshold' => $threshold,
        ], 'cyclops_match');
        // Проверяем, можно ли сопоставить текущий платеж
        if ($payments_tochka["amount"] >= $threshold) { // 1043,26 >= 2879,37 // 963
            // Обновляем сопоставление платежа с продуктом
            Payments::updatePaymentId($matched_payment['matched_payment_id'], $payments_tochka['id']);


            // Уменьшаем сумму текущего платежа с учетом комиссии
            $payments_tochka["amount"] -= $matched_payment["amount"] * (1 - ($com_per + $luft) / 100); // 1043,26

            // Добавляем в общую комиссию
            $total_commission += $matched_payment["amount"] * ($com_per + $luft) / 100; // 258,26

        }
    }

    // Платеж полностью сопоставлен
    Payments::updatePaymentStatus($payments_tochka['id'], 'matched');
    // Проверяем результат цикла
    if ($payments_tochka["amount"] >= $total_commission) {
        // Логируем оставшийся остаток
        Log::add(1, 'unmatched pay', [
            'payment_id' => $payments_tochka['id'],
            'remaining_amount' => $payments_tochka["amount"],
            'total_commission' => $total_commission,
        ], 'cyclops_match');
    }
}

