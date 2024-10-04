<?defined('BILLINGMASTER') or die;?>

<h1><?=$params['params']['title'];?></h1>

<div class="gallery_categories">
    <?if($cat_list):
        foreach($cat_list as $cat):
            if($cat['parent_id'] == 0):?>
                <div class="gal_cat">
                    <h2><a href="/gallery/<?=$cat['alias'];?>"><?=$cat['cat_name']?></a></h2>
                    <?if(!empty($cat['cat_cover'])):?>
                        <a href="/gallery/<?=$cat['alias'];?>">
                            <img src="/images/gallery/cats/<?=$cat['cat_cover'];?>" alt="<?=$cat['cat_name'];?>">
                        </a>
                    <?endif;?>
                </div>
            <?endif;
        endforeach;
    endif;?>
</div>

<style>
    .gallery_categories {display:flex; justify-content:space-around; flex-wrap: wrap}
    .gal_cat {width:30%}
</style>
