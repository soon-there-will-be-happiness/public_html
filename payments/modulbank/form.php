<?php defined('BILLINGMASTER') or die;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"'.$metriks : '';

require_once (dirname(__FILE__) . '/../../lib/payments/modulbank/modulbankhandler.php');

// настройки Modulbank
$payment_name = 'modulbank';
$modulbank = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($modulbank['params']));

$handler = new ModulbankHandler($params, $this->settings, $order, $total);
$form_data = $handler->getFormData();
?>

<?php if(!isset($form_data['error'])):?>
    <form enctype="application/x-www-form-urlencoded" accept-charset='UTF-8' method="POST" action="<?=$form_data['url'];?>"<?=$form_parameters;?>>
        <?php foreach ($form_data['fields'] as $name => $value):?>
            <input type="hidden" name="<?=htmlspecialchars($name);?>" value="<?=htmlspecialchars($value);?>">
        <?php endforeach;?>
        <button type="submit" class="payment_btn"><?=System::Lang('TO_PAY');?></button>
    </form>
<?else:?>
    <script>console.error("<?php echo $form_data['error'];?>");</script>
<?endif?>