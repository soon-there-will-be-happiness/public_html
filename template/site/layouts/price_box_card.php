<?defined('BILLINGMASTER') or die;

$metriks = null;
if(!empty($this->settings['yacounter'])) $ya_goal = "yaCounter".$this->settings['yacounter'].".reachGoal('ADD_TO_BUY');";
else $ya_goal = null;
if($this->settings['ga_target'] == 1) $ga_goal = "ga ('send', 'event', 'add_to_buy', 'click');";
else $ga_goal = null;
if(!empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1) $metriks = ' onclick="'.$ya_goal.$ga_goal.' return true;"';

$complect_params = unserialize(base64_decode($product['complect_params']));
$complect_params = explode("|", $complect_params);
$style = ' '.$complect_params[2];?>

<div class="product_price_box">
    <div class="product_box">
        <p class="product_box-title"><?=System::Lang('COAST');?></p>
        
        <ul><?=$complect_params[1];?></ul>
        <?$standart_price = Price::getFinalPrice($product['product_id']);?>

        <p class="price_str">
            <?if($standart_price['real_price'] < $standart_price['price']):?>
                <span class="old_price"><?=$standart_price['price']?></span>&nbsp;<span class="red_price"><?=$standart_price['real_price'];?> <?=$this->settings['currency'];?>
                    <?if($currency_list){
                        foreach($currency_list as $currency){?>
                            <span> | <?=round($standart_price['real_price'] * $currency['tax']);?> <?=$currency['simbol'];?></span>
                        <?}
                    };?>
                </span>
            <?else:?>
                <?=$standart_price['real_price'];?> <?=$this->settings['currency'];
                if($currency_list){
                    foreach($currency_list as $currency){?>
                        <span> | <?=round($standart_price['real_price'] * $currency['tax']);?> <?=$currency['simbol'];?></span>
                    <?}
                };
            endif;?>
        </p>

        <?if($product['product_amt'] != 0):
            if($this->settings['use_cart'] == 0):?>
                <p><button data-id="<?=$product['product_id'];?>" class="add_to_cart order_link"<?=$metriks;?>><?=System::Lang('IN_CART');?></button></p>
            <?else:?>
                <p><a class="order_link" href="<?=$this->settings['script_url'];?>/buy/<?=$product['product_id']; ?>"<?=$metriks;?>><?=$product['button_text'];?></a></p>
            <?endif;
        endif;?>

        <?if($product['show_amt'] == 1):?>
            <?=prodCount($product['product_amt']);?>
        <?endif; ?>
    </div>

<?function prodCount($count){
    if($count == 0) $result = 'Товар закончился';
    elseif($count == -1) $result = '';
    else $result = "Всего осталось: $count";
    return $result;
}
?>
</div>