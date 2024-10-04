<?defined('BILLINGMASTER') or die;

if(!empty($rubric['short_desc'])):?>
    <div class="blog_category_desc"><?=$rubric['short_desc'];?></div>
<?endif;

if($post_list):
    foreach($post_list as $post):
        $rubric = Blog::getRubricAlias($post['rubric_id']);?>

        <div class="blog_item">
            <?if(!empty($post['post_img'])):?>
                <div class="blog_img">
                    <a href="/blog/<?=$rubric?>/<?=$post['alias'];?>">
                        <img src="/images/post/cover/<?=$post['post_img'];?>" alt="<?=$post['img_alt'];?>"></a>
                </div>
            <?php endif;?>

            <div class="intro">
                <h2 class="blog_item__title">
                    <a href="/blog/<?=$rubric?>/<?=$post['alias'];?>"><?=$post['name'];?></a>
                </h2>

                <div class="post_info">
                    <?if($params['params']['show_create_date'] == 1):?>
                        <span class="small"><?=date("d.m.Y", $post['create_date']);?>
                            <?if($params['params']['show_cat'] == 1) echo ' | ';?></span>
                    <?php endif;

                    if(isset($params['params']['show_start_date']) && $params['params']['show_start_date'] == 1):?>
                        <span class="small"><?=date("d.m.Y", $post['start_date']).($params['params']['show_cat'] ? ' | ' : '');?></span>
                    <?php endif;

                    if($params['params']['show_cat'] == 1):?>
                        <span class="small"> <?php $rubr = Blog::getRubricDataByID($post['rubric_id']);?><?=System::Lang('CATEGORY');?> <a href="/blog/<?=$rubr['alias'];?>"><?=$rubr['name'];?></a></span>
                    <?php endif;?>
                </div>
                <?=$post['intro'];?>
            </div>

            <div class="read_more__wrap">
                <a class="read_more btn-blue-thin" href="/blog/<?=$rubric?>/<?=$post['alias'];?>"><?=System::Lang('MORE');?></a>
            </div>
        </div>
    <?php endforeach;
else:
    echo 'Пока здесь нет записей';
endif;

if($is_pagination == true) echo $pagination->get();