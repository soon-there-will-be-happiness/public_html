<?php defined('BILLINGMASTER') or die;?>

<div class="course_category">
    <div class="row course_category__row" data-uk-grid-match=" { target:'.category_cover' } ">
        <?php foreach($cat_list as $cat):?>
            <div class="col-1-3 course_category_item">
                <?if(!empty($cat['cover'])):?>
                    <a class="category_cover" href="/training/category/<?=$cat['alias'];?>">
                        <img src="/images/training/category/<?=$cat['cover'];?>" alt="<?=$cat['img_alt'];?>">
                    </a>
                <?endif;?>

                <div class="category_desc">
                    <h3 class="category_desc__title">
                        <a href="/training/category/<?=$cat['alias'];?>"><?=$cat['name'];?></a>
                    </h3>

                    <?$course_count = Training::countAllTrainingsInCategory($cat['cat_id']);?>
                    <div class="category_desc_info"><?=html_entity_decode($cat['cat_desc']);?></div>
                    <?if($course_count > 0):?>
                        <div class="course_count"><?=System::Lang('FOR_COURSES')." $course_count";?></div>
                    <?endif;?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
