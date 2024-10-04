<div class="catalog_item">
    <?if($product['product_cover']):?>
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
                        <?$params = json_decode($this->settings['params'], true); ?>
                        <?if(($cat_data = Product::getCatData($product['cat_id'])) && @ $params['show_product_categories'] == 1):?>
                            <strong>Категория: <a href="<?=$this->settings['script_url'];?>/catalog?cat=<?=$cat_data['cat_alias']?>"></strong><?=$cat_data['cat_name'];?></a>
                        <?endif;?>
                        <hr/>

                        <span class="font-bold"><?=System::Lang('COAST');?></span>
                        <?$price = Price::getPriceinCatalog($product['product_id']);
                        if($price['real_price'] < $price['price']):?>
                            <span class="old_price"><?=$price['price'];?> <?=$this->settings['currency'];?></span>&nbsp;
                            <span class="red_price"><?=$price['real_price'];?> <?=$this->settings['currency'];?></span>
                            <?if(@$currency_list){
                                foreach($currency_list as $currency){?>
                                    <span> | <?=$price['real_price'] * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                <?}
                            };?>
                        <?else:?>
                            <strong><?=$price['real_price'];?> <?=$this->settings['currency'];?></strong>
                            <?if(isset($currency_list) && $currency_list){
                                foreach($currency_list as $currency){?>
                                    <span> | <?=$price['real_price'] * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                <?}
                            };?>
                        <?endif;?>
                    </div>
                </div>
            <?endif;?>

            <div class="catalog-item__button-box">
                <?if($this->settings['use_cart'] == 1):
                    if($product['hidden_price'] == 0):?>
                        <div>
                            <button data-id="<?=$product['product_id'];?>" class="btn-blue-border add_to_cart"<?=$metriks;?>><?=System::Lang('IN_CART');?></button>
                        </div>
                    <?endif;
                elseif($product['hidden_price'] == 0):?>
                    <?if(!empty($product['button_text'])):?>
                        <div>
                            <a class="btn-blue-border" href="<?=$this->settings['script_url'];?>/buy/<?=$product['product_id'];?>" target="_blank"<?=$metriks;?>><?=$product['button_text'];?></a>
                        </div>
                    <?endif;
                endif;

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
                            <?if (isset($currency_list) && $currency_list) {
                                foreach ($currency_list as $currency) {?>
                                    <span> | <?=$price['real_price'] * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                <?}
                            };?>
                        <?else:?>
                            <strong><?=$price['real_price'];?> <?=$this->settings['currency'];?></strong><?if(isset($currency_list) && $currency_list){
                                foreach($currency_list as $currency){?>
                                    <span> | <?=$price['real_price'] * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                <?}
                            };?>
                        <?endif;?>
                    </div>
                <?endif;?>
            </div>

            <div class="catalog-item__button-box">
                <?if(!empty($product['button_text'])):?>
                    <div>
                        <a class="btn-blue-border" href="<?=$this->settings['script_url'];?>/buy/<?=$product['product_id'];?>" target="_blank"<?=$metriks;?>><?=$product['button_text'];?></a>
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
        <?endif;?>
    </div>
</div>