<?php define('BILLINGMASTER', 1);

// Настройки
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');


$setting = System::getSetting();
$cookie = $setting['cookie'];
$status = false;

$payment_name = 'yakassapi';
$yakassa = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($yakassa['params']));

$shop_id = $params['ya_shop_id'];
$api_key = $params['api_key'];
$currency = $params['currency'];

// Разделение финпотоков 
if(isset($_GET['org_id']) && !empty($_GET['org_id'])){
    $org_id = intval($_GET['org_id']);
    
    $organization = Organization::getOrgData($org_id);
    if($organization['yookassa']['enable'] == 1){
        
        $shop_id = $organization['yookassa']['shop_id'];
        $api_key = $organization['yookassa']['api_key'];
        $currency = $organization['yookassa']['currency'];
    }
}

require (ROOT .'/lib/yakassa_sdk/autoload.php');
use YandexCheckout\Client;//Импортируем класс

$client = new Client();//Создаём экземпляр объекта
$client->setAuth($shop_id, $api_key);

$json = file_get_contents('php://input');
$payment = json_decode($json, true);

$paymentId = $payment ? $payment['object']['id'] : false;


if ($paymentId) {
  try {
    $payment = $client->getPaymentInfo($paymentId);//$paymentId);
	//Если на метод передать некорректный Id платежа, то обработка скрипта остановится, чтобы этого не произошло 
	//обязательно обрабатываем ситуацию с исключением по try - catch
  } catch (Exception $e) {}
}

if (isset($payment->status) and (($payment->status == "succeeded") )) {

    // Получаем данные заказа
    $order = Order::getOrderToAdmin(intval($payment->metadata->order_id));
    if(!$order) exit('order not found');
    
    $summ = Order::getOrderTotalSum($payment->metadata->order_id);
    
    // Проверяем данные заказа с пришедшими
    $amount = $payment->amount->value;
    if($amount == $summ.'.00' && $order['status'] != 1) {   
        
        // Рендерим заказ
  		$render = Order::renderOrder($order, $yakassa['payment_id']);
       
    }
}


if(isset($order)){
    ob_start();
    echo '<pre>';
    print_r($payment);
    echo '</pre>';
    $buffer = ob_get_contents();
    $log = Order::writePayLog($order['order_date'], null, 'All', $buffer, $yakassa['payment_id']);
    ob_end_clean();
}

$html = file_get_contents("{$setting['script_url']}/payments/yakassapi/success.php");
echo $html;