<?defined('BILLINGMASTER') or die;?>

<h1><?=$cat['cat_name'];?></h1>

<?if($subcat_list):?>
    <div class="gallery_categories">
        <?foreach($subcat_list as $subcat):?>
            <div class="gal_cat">
                <h2><a href="/gallery/<?=$alias;?>/<?=$subcat['alias'];?>"><?=$subcat['cat_name']?></a></h2>
                <?if(!empty($cat['cat_cover'])):?>
                    <a href="/gallery/<?=$alias;?>/<?=$subcat['alias'];?>">
                        <img src="/images/gallery/cats/<?=$subcat['cat_cover'];?>" alt="<?=$subcat['cat_name'];?>">
                    </a>
                <?endif;?>
            </div>
        <?endforeach;?>
    </div>
<?endif;

if($img_list):?>
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
                     style="display:none">
                <?if(!empty($img['link'])) echo '</a>';
            endforeach;
        endif;?>
    </div>
<?endif;?>