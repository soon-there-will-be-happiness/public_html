<?php defined('BILLINGMASTER') or die;

$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' '.$metriks : '';

$inv_desc = 'Оплата заказа №'.$order['order_date'];
?>

<form enctype="application/x-www-form-urlencoded" action="<?=$this->settings['script_url']."/payments/dolyame/bill_create.php"?>" method="POST"<?=$form_parameters;?>>
    <input type="hidden" name="order_id" value="<?=$order['order_id'];?>">
    <input type="hidden" name="total" value="<?=$total;?>">
    <input type="hidden" name="order_desc" value="<?=$inv_desc;?>">
    <input type="hidden" name="order_date" value="<?=$order['order_date'];?>">
    <input type="hidden" name="ship_method_id" value="<?=$order['ship_method_id'];?>">

    <input type="hidden" name="client_name" value="<?= @ $order['client_name'];?>">
    <input type="hidden" name="client_email" value="<?= @ $order['client_email'];?>">
    <input type="hidden" name="client_phone" value="<?= @ $order['client_phone'];?>">
    <input type="submit" class="payment_btn" value="<?=System::Lang('TO_PAY');?>">
</form>