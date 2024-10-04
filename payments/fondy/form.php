<?php defined('BILLINGMASTER') or die;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"'.$metriks : '';

$order_desc = 'Order '.$order['order_date'];
$order_amount = $total * 100;
// TODO чуть позже нужно переделать 
foreach ($order_items as $item) {
    $order_desc.= ', '.$item['product_name'];
}

?>

<form enctype="application/x-www-form-urlencoded" action="/payments/fondy/pay.php" method="POST"<?=$form_parameters;?>>
    <input type="hidden" name="order_id" value="<?=$order['order_id'];?>">
    <input type="hidden" name="order_desc" value="<?=$order_desc;?>">
    <input type="hidden" name="order_amount" value="<?=$order_amount;?>">
    <input type="hidden" name="client_email" value="<?=$order['client_email'];?>">
    <input type="submit" class="payment_btn" value="<?=System::Lang('TO_PAY');?>">
</form>