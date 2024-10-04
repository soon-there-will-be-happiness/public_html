<?php defined('BILLINGMASTER') or die; 
require_once ("{$this->layouts_path}/head.php");
$metriks = null;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter{$this->settings['yacounter']}.reachGoal('ADD_UPSELL');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'add_upsell', 'submit');" : null;
if(!empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1) {
    $metriks = ' onsubmit="'.$ya_goal.$ga_goal.' return true;"';
}?>

<div id="order_form">
    <div class="container-cart">
        <ul class="container-crumbs">
            <li class="crumbs-no-active"><?=System::Lang('FIRST_YOUR_DATAS');?></li>
            <li class="two-active"><?=System::Lang('SECOND_CART');?></li>
            <li><?=System::Lang('THIRD_PAYMENT_VARIANT');?></li>
        </ul>

        <h2><?=System::Lang('SPECIAL_OFFER');?></h2>
        <?=$intro;?>

        <div class="offer main">
            <div class="order_item">
                <div class="order_item-left">
                    <img class="upsell_cover" src="<?=$this->settings['script_url'];?>/images/product/<?=$upsell['product_cover'];?>" alt="<?=$upsell['product_name'];?>">
                </div>

                <div class="order_item-desc">
                    <h4><?=$upsell['product_name'];?></h4>
                </div>

                <div class="order_item-price_box-right">
                    <div>
                        <?if($old_price):?>
                            <span class="old_price"><?=$old_price;?></span><?=$this->settings['currency'];?><br>
                        <?endif;?>
                        <span class="real_price"><?=$price;?></span><?=$this->settings['currency'];?>
                    </div>
                </div>
            </div>

            <div class="upsell_box-bottom">
                <form action="" id="top_yes" method="POST"<?=$metriks;?>>
                    <input type="submit" name="upsell" value="Добавить к заказу" class="upsell_button btn-green">
                    <input type="hidden" name="result" value="1">
                    <input type="hidden" name="step" value="<?=$step;?>">
                </form>

                <form action="" id="top_no" method="POST">
                    <input type="submit" name="upsell" value="Спасибо, не надо" class="upsell_cancel link-red">
                    <input type="hidden" name="result" value="0">
                    <input type="hidden" name="step" value="<?=$step;?>">
                </form>
            </div>
        </div>

        <?if(!empty($text)):
            echo $text;?>
        <?endif;?>
    </div>
</div>
</html>