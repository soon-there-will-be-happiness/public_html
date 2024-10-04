<?php defined('BILLINGMASTER') or die;

$params = unserialize(base64_decode($payment['params']));
$merchant_id = $params['pg_merchant_id'];
$secret = $params['paybox_merchant_secret'];

$disableForm = false;
if ($merchant_id && $secret) {
    $paybox = new paybox($order['order_id'], $merchant_id, $total, 'Оплата', 'randomstr', $secret, $order['client_email'] ?? "", $order['client_phone'] ?? "");
    $inputs = $paybox->generateHiddenInputs();
} else {
    $inputs = "";
    $disableForm = true;
}
?>
<form enctype="application/x-www-form-urlencoded" id="paybox_form" action="https://api.paybox.money/payment.php" method="POST"<?php /*=$form_parameters*/?>>
    <?php echo $inputs; ?>
    <?php if (!$disableForm) { ?>
        <input type="submit" class="payment_btn" value="<?=System::Lang('TO_PAY');?>">
    <?php } ?>
</form>
