<?defined('BILLINGMASTER') or die;

$metriks = null;
if(!empty($this->settings['yacounter'])) $ya_goal = "yaCounter".$this->settings['yacounter'].".reachGoal('ADD_TO_BUY');";
else $ya_goal = null;
if($this->settings['ga_target'] == 1) $ga_goal = "ga ('send', 'event', 'add_to_buy', 'click');";
else $ga_goal = null;
if(!empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1) $metriks = ' onclick="'.$ya_goal.$ga_goal.' return true;"';

$complect_params = unserialize(base64_decode($product['complect_params']));
$complect_params = explode("|", $complect_params);
$style = ' '.$complect_params[2];

$standart_price = Price::getFinalPrice($product['product_id']);?>


<div class="catalog-item__price-box mb-10">
    <div class="text-center">
        <span><?=System::Lang('COAST');?></span>

        <?if($standart_price['real_price'] < $standart_price['price']):?>
            <span class="old_price"><?=$standart_price['price'];?> <?=$this->settings['currency'];?></span>&nbsp;
            <span class="red_price"><?=$standart_price['real_price'];?> <?=$this->settings['currency'];?>
                <?if($currency_list){
                    foreach($currency_list as $currency){?>
                        <span> | <?=round($standart_price['real_price'] * $currency['tax']);?> <?=$currency['simbol'];?></span>
                    <?}
                };?>
            </span>
        <?else:?>
            <strong><?=$standart_price['real_price'];?> <?=$this->settings['currency'];?></strong>
            <?if($currency_list){
                foreach($currency_list as $currency){?>
                    <span> | <?=round($standart_price['real_price'] * $currency['tax']);?> <?=$currency['simbol'];?></span>
                <?}
            };
        endif;?>
    </div>

    <div class="text-center">
        <?if($product['product_amt'] != 0):
            if($this->settings['use_cart'] == 1):?>
                <button data-id="<?=$product['product_id'];?>" class="add_to_cart order_link"<?=$metriks;?>><nobr><?=System::Lang('IN_CART');?></nobr></button>
            <?else:?>
                <a class="order_link" href="/buy/<?=$product['product_id']; ?>"<?=$metriks;?>><?=$product['button_text'];?></a>
            <?endif;
        endif;?>

        <div class="text-center">
            <small class="product-count">
                <?if($product['show_amt'] == 1):?>
                    <?=prodCount2($product['product_amt']);?>
                <?endif;?>
            </small>
        </div>
    </div>
</div>


<?function prodCount2($count){
    if($count == 0) $result = 'Товар закончился';
    elseif($count == -1) $result = '';
    else $result = "Осталось: $count";
    return $result;
}?>
