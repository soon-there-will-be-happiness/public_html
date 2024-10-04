<?php defined('BILLINGMASTER') or die;?>

<div class="blog-full-wrap">
    <?if($params['params']['show_cover'] == 1 && !empty($post['post_img']) && $post['show_cover']):?>
        <div class="blog_img full">
            <img src="/images/post/cover/<?=$post['post_img'];?>" alt="<?=$post['img_alt'];?>">
        </div>
    <?php endif;?>

    <div class="blog-full">
        <h1 class="post_title"><?=$post['name'];?></h1>

        <div class="post_info">
            <?if($params['params']['show_create_date'] == 1):?>
                <span class="small"><?=date("d.m.Y", $post['create_date']);?>
                    <?if($params['params']['show_cat'] == 1) echo ' | ';?></span>
            <?php endif;

            if (isset($params['params']['show_start_date'])) {
            if($params['params']['show_start_date'] == 1):?>
                <span class="small"><?=date("d.m.Y", $post['start_date']);?>
                    <?if($params['params']['show_cat'] == 1) echo ' | ';?></span>
            <?php endif; }?>

            <?if($params['params']['show_cat'] == 1):?>
                <span class="small"> <?php $rubr = Blog::getRubricDataByID($post['rubric_id']);?><?=System::Lang('CATEGORY');?><a href="/blog/<?=$rubr['alias'];?>"><?=$rubr['name'];?></a></span>
            <?php endif;?>
        </div>

        <div class="post_desc"><?=System::renderContent($post['text']);?></div>
        <?=$post['custom_code'];?>

        <?if($comments):?>
            <div class="comment_wrapper">
                <?=$params['params']['commentcode'];?>
            </div>
        <?endif;?>
    </div>
</div>