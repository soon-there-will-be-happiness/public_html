<?php defined('BILLINGMASTER') or die;

$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"'.$metriks : '';

$params = unserialize(base64_decode($payment['params']));
$business = $params['business'];
$currency_code = $params['currency'];

$inv_id = $order['order_id'];
$inv_desc= "Заказ №{$order['order_date']}";
if (isset($params['send_products']) && $params['send_products']) {
    $order_items = Order::getOrderItems($order['order_id']);
    $pod_list = $order_items ? implode(',', array_column($order_items, 'product_name')) : '';
    $inv_desc .= ";Товар".(count($order_items) > 1 ? 'ы:' : ':')."$pod_list";
}
$amount = $total. '.00';
?>

<form enctype="application/x-www-form-urlencoded" action="https://www.paypal.com/cgi-bin/webscr" method="post"<?=$form_parameters;?>>
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="charset" value="utf-8">
    <input type="hidden" name="currency_code" value="<?=$currency_code;?>">
    <input type="hidden" name="business" value="<?=$business;?>">
    <input type="hidden" name="item_name" value="<?=substr($inv_desc, 0, 127);?>">
    <input type="hidden" name="item_number" value="<?=$inv_id;?>">
    <input type="hidden" name="amount" value="<?=$amount;?>">
    <input type="hidden" name="notify_url" value="<?=$this->settings['script_url']."/payments/paypal/result.php";?>">
    <input type="hidden" name="return" value="<?=$this->settings['script_url']."/payments/paypal/success.php";?>">
    <input type="hidden" name="cancel_return" value="<?=$this->settings['script_url']."/payments/paypal/fail.php";?>">
    <input type="submit" class="payment_btn" value="<?=System::Lang('TO_PAY');?>">
</form>