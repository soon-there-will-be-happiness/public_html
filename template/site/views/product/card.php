<?php defined('BILLINGMASTER') or die;?>
<div class="maincol-inner">
    <div class="product_page">
        <div class="product_img">
            <?if(!empty($product['product_cover'])):?>
                <img src="/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>" class="mb-15">
            <?php endif;

            if($product['show_price_box'] == 1) {
                require_once ("{$this->layouts_path}/price_box_card2.php");
            }

            if (!empty($product['code_price_box'])) {
                echo $product['code_price_box'];
            }?>
        </div>

        <div class="product_info">
            <?if($product["$text_heading"] == 1):?>
                <h1 class="mb-15"><?=$product['product_name'];?></h1>
            <?php endif;?>
            <?=System::renderContent($text_lp);?>
            <?=$product['custom_code'];?>
        </div>
    </div>
</div>

<?if($product['show_reviews'] == 1):
    $reviews = Product::getReviewsByProductID($product['product_id']);
    if($reviews):?>
        <div class="reviews_list">
            <h3><?=System::Lang('REVIEWS');?></h3>

            <?php foreach($reviews as $review):?>
                <div class="review_item">
                    <div class="review_item-inner">
                        <div class="review_img">
                            <?if(!empty($review['attach'])):?>
                                <img src="/images/reviews/<?=$review['attach'];?>" alt="<?=$review['name'];?>">
                            <?php endif;?>
                            <?php if ($cat_data = Product::getCatData($product['cat_id'])):?>
                                <strong>Категория: </strong><?=$cat_data['cat_name'];?>
                            <?php endif;?>
                            <hr/>
                            <ul class="rev-soc">
                                <?if(!empty($review['site_url'])):?>
                                    <li><a href="<?=$review['site_url'];?>" target="_blank" rel="nofollow"><i class="icon-site"></i></a></li>
                                <?php endif;

                                if(!empty($review['vk_url'])):?>
                                    <li><a href="<?=$review['vk_url'];?>" target="_blank" rel="nofollow"><i class="icon-vk-i"></i></a></li>
                                <?php endif;

                                if(!empty($review['fb_url'])):?>
                                    <li><a href="<?=$review['fb_url'];?>" target="_blank" rel="nofollow"><i class="icon-facebook"></i></a></li>
                                <?php endif;?>
                            </ul>
                        </div>

                        <div class="review_desc">
                            <p><strong class="review_user_name"><?=$review['name'];?></strong></p>
                            <div class="review_desc__desc"><?=$review['text'];?></div>

                            <?$reviews_tune = unserialize(base64_decode($setting['reviews_tune']));
                            if(!isset($reviews_tune['show_date']) || $reviews_tune['show_date']):?>
                                <p class="review_create_date"><?=$review['create_date'];?></p>
                            <?endif;?>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    <?php endif;
endif;
