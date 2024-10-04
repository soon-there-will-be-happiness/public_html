<?if (!defined('CURR_VER')) {
    require_once "{$_SERVER['DOCUMENT_ROOT']}/index.php";
} else {
    require_once dirname(__FILE__) .'/src/BillPayments.php';
    require_once dirname(__FILE__) .'/src/BillPaymentsException.php';
    require_once (ROOT . '/components/autoload.php');

    $payment_name = 'qiwi';
    $qiwi = Order::getPaymentSetting($payment_name);
    $setting = System::getSetting();
    $params = unserialize(base64_decode($qiwi['params']));

    $billPayments = new \Qiwi\Api\BillPayments($params['business']);
    $billId = isset($_GET['key']) && $_GET['key'] != '' ? base64_decode($_GET['key']) : '';
    $response = $billId ? $billPayments->getBillInfo($billId) : null;

    if (isset($response['status']['value']) && $response['status']['value'] == 'PAID') {
        $html .= "<a href=\"{$response['payUrl']}\">Посмотреть чек</a>";

        if (isset($response['comment']) && $response['comment'] !='') {
            $order_id = (integer) base64_decode($response['comment']) ;
            $order = Order::getOrder($order_id);
            $data_product = Order::getOrder($order_id);
            if ($data_product['summ'] == $response['amount']['value']) {
                $payment_name = 'qiwi';
                $qiwi = Order::getPaymentSetting($payment_name);
                Order::renderOrder($order, $qiwi['payment_id']);
            }

        }
    }
};
