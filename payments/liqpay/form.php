<?php defined('BILLINGMASTER') or die;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"'.$metriks : '';

// настройки LiqPay
$payment_name = 'liqpay';
$liqpay = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($liqpay['params']));
$public_key = trim($params['public_key']);
$private_key = trim($params['private_key']);
$currency = $params['currency'];
$inv_desc = 'Order '.$order['order_date'];

$data = [
    'version' => '3',
    'public_key' => $public_key,
    'action' => 'pay',
    'amount' => $total,
    'currency' => $currency,
    'description' => $inv_desc,
    'order_id' => $order['order_id'],
    'language'    => $this->settings['lang'],
    'result_url' => $this->settings['script_url'] . '/payments/liqpay/success.php',
    'server_url' => $this->settings['script_url'] . '/payments/liqpay/result.php',
];

if (isset($params['fiscalization_checks']) && $params['fiscalization_checks']) {
    $receipts = [];
    foreach ($order_items as $item) {
        $receipts[] = [
            'good' => [
                'code' => $params['products_code'],
                'name' => $item['product_name'],
                'price' => $item['price'] * 100,
            ],
            'quantity' => 1000,
            'is_return' => false,
        ];
    }
    $data['goods'] = $receipts;
}

$data = base64_encode(json_encode($data));

$signature = base64_encode(sha1($private_key.$data.$private_key, 1));
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"' . $metriks : '';
?>

<form enctype="application/x-www-form-urlencoded" accept-charset='UTF-8' method="POST" action="https://www.liqpay.ua/api/3/checkout"<?=$form_parameters;?>>
    <input type="hidden" name="data" value="<?=$data?>" />
    <input type="hidden" name="signature" value="<?=$signature?>" />
    <button type="submit" class="payment_btn"><?=System::Lang('TO_PAY');?></button>
</form>
