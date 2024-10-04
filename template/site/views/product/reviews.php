<?php defined('BILLINGMASTER') or die;

if(!empty($this->view['h1'])):?>
    <h1 class="rev-h1"><?=$this->view['h1'];?></h1>
<?endif;?>

<p class="flex-right">
    <a class="button btn-add-rev" href="/reviews/add"><?=System::Lang('WRITE_REVIEW');?></a>
</p>

<div class="reviews_list">
    <?if($list_reviews):
        foreach($list_reviews as $review):?>
            <div class="review_item">
                <div class="review_item-inner">
                    <div class="review_img">
                        <?if(!empty($review['attach'])):?>
                            <img src="/images/reviews/<?=$review['attach'];?>" alt="<?=$review['name'];?>">
                        <?endif;?>

                        <ul class="rev-soc">
                            <?if(!empty($review['site_url'])):?>
                                <li><a href="<?=$review['site_url'];?>" target="_blank" rel="nofollow"><i class="icon-site"></i></a></li>
                            <?endif;?>

                            <?if(!empty($review['vk_url'])):?>
                                <li><a href="<?=$review['vk_url'];?>" target="_blank" rel="nofollow"><i class="icon-vk-i"></i></a></li>
                            <?endif;?>

                            <?if(!empty($review['fb_url'])):?>
                                <li><a href="<?=$review['fb_url'];?>" target="_blank" rel="nofollow"><i class="icon-facebook"></i></a></li>
                            <?endif;?>
                        </ul>
                    </div>

                    <div class="review_desc">
                        <?if(!empty($review['product_id'])):
                            $pr_arr = Product::getProductName($review['product_id']);
                            if($pr_arr['product_name'] != '- - '):?>
                                <p><strong class="review_product_name"><?=$pr_arr['product_name'];?></strong></p>
                            <?endif;
                        endif;?>

                        <p><strong class="review_user_name"><?=$review['name'];?></strong>
                            <?php $reviews_tune = unserialize(base64_decode($this->settings['reviews_tune']));
                            if(!isset($reviews_tune['show_date']) || $reviews_tune['show_date']):?>
                                , <span class="small review_create_date"><?=$review['create_date']?></span>
                            <?endif;?>
                        </p>

                        <div class="review_desc__desc"><?=$review['text'];?></div>
                    </div>
                </div>
            </div>
        <?php endforeach;
    else:?>
        <p><?=System::Lang('NO_REVIEW');?></p>
    <?endif;?>
</div>

<?if(isset($is_pagination) && $is_pagination == true) echo $pagination->get();?>
