<?defined('BILLINGMASTER') or die;

if($post_list):
    foreach($post_list as $post):
        $rubric = Blog::getRubricAlias($post['rubric_id']);?>

        <div class="blog_item">
            <?// Blog::get_post_img($post_id, 'blog_img', 'blank') - получить картинку поста и вывести html
                // передаём ID поста, имя класса blog_img, для обёртки div и способ открытия ссылки в новом окне или нет.
            if(!empty($post['post_img'])):?>
                <div class="blog_img">
                    <a href="/blog/<?=$rubric?>/<?=$post['alias'];?>">
                        <img src="/images/post/cover/<?=$post['post_img'];?>" alt="<?=$post['img_alt'];?>">
                    </a>
                </div>
            <?endif;?>

            <div class="intro">
                <?// get_post_title() - вывести заголовок записи ?>
                <h2 class="blog_item__title">
                    <a href="/blog/<?=$rubric?>/<?=$post['alias'];?>"><?=$post['name'];?></a>
                </h2>

                <?// get_post_info() - вывести инфу к записи ?>
                <div class="post_info">
                    <?if($this->params['show_create_date'] == 1):?>
                        <span class="small"><?=date("d.m.Y", $post['create_date']);?>
                            <?if($this->params['show_cat'] == 1) echo ' | ';?></span>
                    <?endif;

                    if(isset($this->params['show_start_date']) && $this->params['show_start_date'] == 1):?>
                        <span class="small"><?=date("d.m.Y", $post['start_date']).($this->params['show_cat'] ? ' | ' : '');?></span>
                    <?endif;

                    if($this->params['show_cat'] == 1 && $post['rubric_id'] != 0):?>
                        <span class="small"> <?$rubr = Blog::getRubricDataByID($post['rubric_id']);?><?=System::Lang('CATEGORY');?> <a href="/blog/<?=$rubr['alias'];?>"><?=$rubr['name'];?></a></span>
                    <?endif;

                    if($post['author_id'] != null):?>
                        <span class="small"><?=System::Lang('AUTHOR');?> <?$author_data = User::getUserNameByID($post['author_id']); echo $author_data['user_name'];?></span>
                    <?endif;?>
                </div>

                <div class="post_short-desc"><?=$post['intro'];?></div>
            </div>

            <div class="read_more__wrap">
                <?// ниже идёт класс "... btn-blue-thin", что говорит о синем цвете кнопки, сейчас нам это уже не актуально, т.к. цвет будем задавать в другом месте
                // поэтому можно удалить этот класс и отсавить read_more ?>
                <a class="read_more btn-blue-thin" href="/blog/<?=$rubric?>/<?=$post['alias'];?>"><?=System::Lang('MORE');?></a>
            </div>
        </div>
    <?endforeach;
else:
    echo 'Пока здесь нет записей';
endif;

if ($is_pagination == true) {
    echo $pagination->get();
}?>