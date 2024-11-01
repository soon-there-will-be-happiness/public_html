<?php
define('BILLINGMASTER', 1);
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
if (isset($response['result']['payments']) && !empty($response['result']['payments'])) {
    // Проходим по каждому платежу
    foreach ($response['result']['payments'] as $paymentId) {
        // Вызов метода addPayment для каждого платежа
        Cyclops::addPayment($paymentId);
    }
}
