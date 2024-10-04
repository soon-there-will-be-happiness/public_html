<?defined('BILLINGMASTER') or die;?>

<h1><?=$sub_cat['cat_name'];?></h1>

<?if($img_list):?>
    <div id="gallery">
        <?if($img_list):
            foreach($img_list as $img):
                if(!empty($img['link'])):?>
                    <a href="<?=$img['link'];?>">
                <?endif;?>

                <img alt="<?=$img['alt'];?>"
                     src="/images/gallery/thumb/<?=$img['file'];?>"
                     data-image="/images/gallery/<?=$img['file'];?>"
                     data-description="<?=$img['item_desc'];?>"
                     style="display:none"
                >
                <?if(!empty($img['link'])) echo '</a>';
            endforeach;
        endif;?>
    </div>
<?endif;?>
<?=$params['params']['commentcode'];?>