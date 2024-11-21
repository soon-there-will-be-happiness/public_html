<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = Order::getOrder($order_id);
$point=PointDB::findRecordByOrderId($order_id );
if($point['status']!=true)
{
    $api_url = $params['url'];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://enter.tochka.com/uapi/acquiring/v1.0/payments/'.$point['operationId'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$token.''
        ),
    ));
    $response = curl_exec($curl);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $payment_data = json_decode($response, true);

    curl_close($curl);
    if ($http_code == 200&&$payment_data ['Data']['Operation']['status']=='APPROVED')
    {
        $order = Order::getOrderDataByID($order_id,100);
        Order::renderOrder($order);
        $order_items = Order::getOrderItems($order['order_id']);
        PointDB::updateStatusToTrue($order_id);
    }
}
System::redirectUrl($setting['script_url'] . '/payments/point/success');
?>
