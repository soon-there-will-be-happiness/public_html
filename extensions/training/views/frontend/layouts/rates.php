<?defined('BILLINGMASTER') or die;

$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('ADD_TO_BUY');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'add_to_buy', 'click');" : null;
$metriks = !empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1 ? ' onclick="'.$ya_goal.$ga_goal.' return true;"' : null;?>


<?if($training['full_cover_param']):
    $full_cover_param = json_decode($training['full_cover_param'], true);
endif;?>

<style>
    .hero-wrap {
        height: <?=$full_cover_param['heroheigh'];?>px;
        background-position: <?=$full_cover_param['position']?>;
        background-size: cover;
    }
    .hero-wrap:before {
        opacity:<?=$full_cover_param['overlay']?>;
        background:<?=$full_cover_param['overlaycolor']?>;
    }

    @media screen and (max-width: 640px),
    only screen and (max-device-width:640px) {
        .hero-wrap {height: <?=$full_cover_param['heromobileheigh'];?>px}
    }

    .lesson_cover{width: <?=$this->tr_settings['width_less_img'];?>px}
    <?if(isset($this->tr_settings['show_blocks']) && $this->tr_settings['show_blocks'] == 0):?>
        .module-number {display:none}
    <?endif;?>
</style>

<?if($training['full_cover']):?>
    <div id="hero" class="hero-wrap" style="background-image: url(/images/training/<?=$training['full_cover']?>)">
        <?if(!empty($h1)) echo '<h1>'.$h1.'</h1>';?>
        <ul class="breadcrumbs hero-breadcrumbs">
            <?$breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section, $lesson);
            foreach ($breadcrumbs as $link => $name):?>
                <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>
        </ul>
    </div>
<?endif;?>

<div class="layout" id="courses">
    <?if(!$training['full_cover']):?>
        <ul class="breadcrumbs">
            <?$breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section, $lesson);
            foreach ($breadcrumbs as $link => $name):?>
                <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>
        </ul>
    <?endif;?>

    <div class="content-wrap" id="training_<?=$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar">
            <div class="prod-course-top">
                <?if(empty($training['full_cover'])):?>
                    <?if(!empty($h1)) echo '<h1>'.$h1.'</h1>';?>
                <?endif;?>
                <h4><?=System::Lang('ACCESS_TARIFFS');?></h4>
            </div>

            <?if($list_product):
                foreach($list_product as $prod_id):
                    $product = Product::getProductById($prod_id);?>

                    <div class="catalog_item">
                        <?if ($product['product_cover']):?>
                            <div class="catalog_item_img">
                                <?if($product['external_landing'] == 1 && !empty($product['external_url'])):?>
                                    <a href="<?=$product['external_url'];?>">
                                        <img src="<?=$this->settings['script_url'];?>/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
                                    </a>
                                <?else:?>
                                    <a href="<?=$this->settings['script_url'];?>/catalog/<?=$product['product_alias'];?>">
                                        <img src="<?=$this->settings['script_url'];?>/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
                                    </a>
                                <?endif;?>
                            </div>
                        <?endif;?>

                        <div class="catalog_item__right">
                            <div class="catalog_desc intro">
                                <h4 class="catalog_item__title"><?=$product['product_name'];?></h4>

                                <?if($product['product_desc'] != null):?>
                                    <div class="product_desc"><?=nl2br($product['product_desc']);?></div>
                                <?endif;?>
                            </div>

                            <?if($product['show_price_box'] == 1):?>
                                <?if($product['hidden_price'] == 0):?>
                                    <div class="catalog-item__price-box">
                                        <div>
                                            <span class="font-bold"><?=System::Lang('COAST');?></span>
                                            <?$price = Price::getPriceinCatalog($product['product_id']);?>
                                            <?if($price['real_price'] < $price['price']):?>
                                                <span class="old_price"><?=$price['price'];?> <?=$this->settings['currency'];?></span>&nbsp;
                                                <span class="red_price"><?=$price['real_price'];?> <?=$this->settings['currency'];?></span>
                                            <?else:?>
                                                <strong><?=$price['real_price'];?> <?=$this->settings['currency'];?></strong>
                                            <?endif;?>
                                        </div>
                                    </div>
                                <?endif;?>

                                <div class="catalog-item__button-box">
                                    <?if($this->settings['use_cart'] == 1):
                                        if($product['hidden_price'] == 0):?>
                                            <div>
                                                <button data-id="<?=$product['product_id'];?>" class="btn-green add_to_cart"<?=$metriks;?>><?=System::Lang('IN_CART');?></button>
                                            </div>
                                        <?endif;
                                    elseif($product['hidden_price'] == 0):?>
                                        <div>
                                            <a class="btn-green" href="<?=$this->settings['script_url'];?>/buy/<?=$product['product_id'];?>" target="_blank"<?=$metriks;?>><?=$product['button_text'];?></a>
                                        </div>
                                    <?endif;

                                    if($this->settings['enable_landing'] == 1):
                                        if($product['external_landing'] == 1 && !empty($product['external_url'])):?>
                                            <a href="<?=$product['external_url'];?>"><?=System::Lang('MORE');?></a>
                                        <?else:?>
                                            <a href="<?=$this->settings['script_url'];?>/catalog/<?=$product['product_alias'];?>"><?=System::Lang('MORE');?></a>
                                        <?endif;
                                    elseif($this->settings['enable_landing'] == 0 && $product['external_landing'] == 1 && !empty($product['external_url'])):?>
                                        <a href="<?=$product['external_url'];?>"><?=System::Lang('MORE');?></a>
                                    <?endif;?>
                                </div>
                            <?else:?>
                                <div class="catalog-item__price-box">
                                    <?if($product['hidden_price'] == 0):?>
                                        <div>
                                            <span class="font-bold"><?=System::Lang('COAST');?></span>
                                            <?$price = Price::getPriceinCatalog($product['product_id']);
                                            if($price['real_price'] < $price['price']):?>
                                                <span class="old_price"><?=$price['price'];?> <?=$this->settings['currency'];?></span>&nbsp;
                                                <span class="red_price"><?=$price['real_price'];?> <?=$this->settings['currency'];?></span>
                                            <?else:?>
                                                <strong><?=$price['real_price'];?> <?=$this->settings['currency'];?></strong>
                                            <?endif;?>
                                        </div>
                                    <?endif;?>
                                </div>

                                <div class="catalog-item__button-box">
                                    <div>
                                        <a class="btn-green" href="<?=$this->settings['script_url'];?>/buy/<?=$product['product_id'];?>" target="_blank"<?=$metriks;?>><?=$product['button_text'];?></a>
                                    </div>

                                    <?if($this->settings['enable_landing'] == 1):
                                        if($product['external_landing'] == 1 && !empty($product['external_url'])):?>
                                            <a href="<?=$product['external_url'];?>"><?=System::Lang('MORE');?></a>
                                        <?else:?>
                                            <a href="<?=$this->settings['script_url'];?>/catalog/<?=$product['product_alias'];?>"><?=System::Lang('MORE');?></a>
                                        <?endif;
                                    elseif($this->settings['enable_landing'] == 0 && $product['external_landing'] == 1 && !empty($product['external_url'])):?>
                                        <a href="<?=$product['external_url'];?>"><?=System::Lang('MORE');?></a>
                                    <?endif;?>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                <?endforeach;
            endif;?>
        </div>

        <aside class="sidebar">
            <?if($training['cover'] && $training['cover_settings'] == 1 && $training['show_widget_progress']):?>
                <section class="widget _instruction traning-widget">
                    <div class="sidebar-image">
                        <img src="/images/training/<?=$training['cover']?>">
                    </div>
                    
                    <h4 class="traninig-name"><?=$training['name']?></h4>

                    <?if($user_id):?>
                        <h3 style="display: block; margin-top: 48px;"><?=System::Lang('YOUR_PROGRESS');?></h3>
                        <p class="progress-text"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                        <?require_once (__DIR__ . '/../layouts/progressbar.php'); ?>
                    <?else:?>
                        <p><?=System::Lang('PROGRESS_OF_THE_TRAINING_WILL_BE_DISPLAYED_HERE');?></p>
                    <?endif;?>
                </section>
            <?else:
                if($training['cover'] /*&& !$training['full_cover']*/ &&  $training['cover_settings'] == 1):?>
                    <section class="widget _instruction traning-widget">
                        <div class="sidebar-image">
                            <img src="/images/training/<?=$training['cover']?>">
                        </div>

                        <h4 class="traninig-name"><?=$training['name']?></h4>
                    </section>
                <?endif;?>

                <?if($user_id && $training['show_widget_progress']):?>
                    <section class="widget _instruction traning-widget">
                        <h3><?=System::Lang('YOUR_PROGRESS');?></h3>
                        <p class="progress-text"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                        <?require_once (__DIR__ . '/../layouts/progressbar.php'); ?>
                    </section>
                <?elseif($training['show_widget_progress']):?>
                    <section class="widget traning-widget">
                        <p><?=System::Lang('PROGRESS_OF_THE_TRAINING_WILL_BE_DISPLAYED_HERE');?></p>
                    </section>
                <?endif;
            endif;?>

            <?if($sidebar):
                $widget_arr = $sidebar;
                require ("$this->widgets_path/widget_wrapper.php");
            endif;?>
        </aside>
    </div>
</div>