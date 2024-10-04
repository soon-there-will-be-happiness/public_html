<?defined('BILLINGMASTER') or die;

$metriks = null;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('ADD_TO_BUY');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'add_to_buy', 'click');" : null;

if(!empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1) {
    $metriks = ' onclick="'.$ya_goal.$ga_goal.' return true;"';
}?>

<div class="product-list mb-30">
    <?=$page['page_text'];

    if(!empty($page['page_code'])):?>
        <div class="sale_custom_code"><?=$page['page_code'];?></div>
    <?endif;
    if($list_product):
        foreach($list_product as $product):?>
            <?php include (ROOT."/template/site/views/product/product_card.php"); ?>
        <?endforeach;
    else:?>
        <p><?=System::Lang('DISCOUNTS_OVER');?></p>
    <?endif;?>
</div>