<?php defined('BILLINGMASTER') or die;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('CUSTOM_PAY');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'custom_pay', 'submit');" : null;
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : null;
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? $metriks : '';
?>

<a class="payment_btn payment_btn-link" href="#ModalCustomPay" data-uk-modal><?=System::Lang('INSTRUCTIONS');?></a>

<div id="ModalCustomPay" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-dialog-3">
        <div class="userbox modal-userbox-3">
            <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
            <form enctype="application/x-www-form-urlencoded" action="" method="POST"<?=$form_parameters;?>>
                <h3 class="modal-head-2"><?=System::Lang('RESULT_PAYMENT');?> <?=$total;?> <?=$this->settings['currency'];?>
                <?if($currency_list):
                    foreach($currency_list as $currency):?>
                        <span class="product-price"> | <?=$total * $currency['tax'];?> <?=$currency['simbol'];?></span>
                    <?endforeach;
                endif;?>
                </h3>
                <?php $params = unserialize(base64_decode($payment['params'])); echo $params['instruct'];?>
                <hr>
                <p><?=System::Lang('TO_BE_PAID');?> <strong><?=$total;?> <?=$this->settings['currency'];?></strong>
                <?if($currency_list):
                    foreach($currency_list as $currency):?>
                        <span class="product-price"> | <?=$total * $currency['tax'];?> <?=$currency['simbol'];?></span>
                    <?endforeach;
                endif;?></p>
                <div>
                    <h5 class="one-filter__title"><?=System::Lang('SELECT_YOU_PAID');?></h5>
                    <div class="select-wrap">
                        <select name="gateway" required="required">
                            <option value="">- <?=System::Lang('CHOOSE');?> -</option>
                            <?php $gateway_list = explode(",", $params['gateway']);
                                foreach($gateway_list as $gateway):?>
                            <option value="<?php echo $gateway;?>"><?php echo $gateway;?></option>
                            <?php endforeach;?>
                        </select>
                   </div>
                </div>
                <p><?=System::Lang('PAYMENT_DETAILS');?></p>
                <input type="text" name="card_number">
                <input type="hidden" name="payment" value="<?=$payment['payment_id'];?>">
                <input type="hidden" name="summ" value="<?=$total;?>">
                <p><input type="submit" name="custom_pay" class="order_button btn-green-small mt-5" value="<?=System::Lang('PAIDED');?>"></p>
            </form>
        </div>
    </div>
</div>