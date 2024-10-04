<?php defined('BILLINGMASTER') or die;

$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter{$this->settings['yacounter']}.reachGoal('ADD_TO_BUY');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'add_to_buy', 'click');" : null;
$metriks = !empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1 ? "onclick=\"{$ya_goal}{$ga_goal} return true;\"" : null;?>

<?if(!empty($this->view['h1'])):?>
    <h1 class="rev-h1"><?=$this->view['h1'];?></h1>
<?endif;?>

<?if(!empty($category_data['cat_desc'])):?>
    <p><?=$category_data['cat_desc'];?></p>
<?endif;

if (!isset($_GET['cat']) && $this->settings['catalog_filter']) {
    require (__DIR__.'/catalog_filter.php');
}?>

<div class="product-list">
    <?if($list_product):
        foreach($list_product as $product):?>
            <?php if($product['product_access'] == 2){ // если доступен только авторизованным с группой/подпиской
                $haveAccess = Product::checkProductAvailableToUser($product, function () { return false;});
                if (!$haveAccess) {
                    continue;
                }   
            }?>
            <?php include (ROOT."/template/site/views/product/product_card.php"); ?>
        <?php endforeach;
    endif;?>
</div>

<link rel="stylesheet" href="/template/<?=$this->settings['template'];?>/css/filters.css?v=<?=CURR_VER;?>" type="text/css" />
<script type="text/javascript" src="/template/<?=$this->settings['template'];?>/js/catalog_filter.js?v=<?=CURR_VER;?>"></script>