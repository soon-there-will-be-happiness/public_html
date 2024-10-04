<?defined('BILLINGMASTER') or die;?>

<style>
    .lesson_cover{width: <?=$params['params']['width_less_img'];?>px}
    <?if(isset($params['params']['show_blocks']) && $params['params']['show_blocks'] == 0):?>
    .module-number {display:none}
    <?endif;?>
</style>

<div class="one-course-top">
    <h1><?=$course['name'];?></h1>
    <?=$course['course_desc'];?>
</div>

<div class="lessons_list">
    <?if($lesson_list):
        foreach($lesson_list as $lesson):
            // Формируем URL для покупки доступа к уроку
            switch ($lesson['type_access_buy']) {
                case 3: // если ссылка
                    $link_access = $lesson['link_access'];
                    break;

                case 2: // если лендинг продукта
                    $product = Product::getProductData($lesson['product_access']);
                    $link_access = '/catalog/'.$product['product_alias'];
                    break;

                case 1: // если страница заказа продукта
                    $link_access = '/buy/'.$lesson['product_access'];
                    break;

                case 0: // если нет данных, то берём их из настроек курса
                    if ($course['type_access_buy'] == 3) {
                        $link_access = $course['link_access'];
                    } elseif($course['type_access_buy'] == 2) {
                        $product = Product::getProductData($course['product_access']);
                        $link_access = '/catalog/'.$product['product_alias'];
                    } elseif($course['type_access_buy'] == 1) {
                        $link_access = '/buy/'.$course['product_access'];
                    } else {
                        $link_access = '';
                    }
                    break;
            }
            // НАЗВАНИЯ БЛОКОВ

            if($lesson['block_id'] && ($block === null || $block != $lesson['block_id'])):
                $block_name = Course::getBlockLessonName($lesson['block_id']);

                if($block_name):
                    if($block !== null && $block != $lesson['block_id']):?>
                        </div></div>
                    <?endif;?>

                    <div class='cut old_cut'>
                        <div class='block-heading__click'>
                            <div class='module-number'><?=System::Lang('MODULE');?></div>
                            <h4 id="block_<?=$lesson['block_id'];?>" class='block-heading'><?=$block_name;?></h4>
                        </div>

                        <div style="" class="mini_cut old_mini_cut">
                    <?$prev_block = $lesson['block_id'];
                endif;
                $block = $lesson['block_id'];
            elseif($block && $lesson['block_id'] == 0):
                $block = null;?>
                </div></div>
            <?endif;?>

            <div class="lesson_item old_lesson_item">
                <?$access = Course::checkAcсessLesson($course, $lesson, $user_groups, $user_planes);
                    $complete_less = 0;
                    if ($map_items) {
                        foreach($map_items as $item){
                            if (in_array($lesson['lesson_id'], $item) && $item['status'] == 1) {
                                $complete_less = 1;
                            }
                        }
                }?>

                <?if(!empty($lesson['cover'])):
                    if (!$access && $lesson['sort'] == 1) {
                        $lesson_url = $link_access;
                    } else {
                        $lesson_url = $course['alias'].'/'.$lesson['alias'];
                    }?>

                    <a href="<?=$lesson_url;?>">
                        <div class="lesson_cover">
                            <img src="/images/lessons/<?=$lesson['cover'];?>" alt="<?=$lesson['img_alt'];?>"/>
                        </div>
                    </a>
                <?endif;?>

                <div class="lesson_desc old_lesson_desc">
                    <?if($access && $complete_less == 1){?>
                        <div class="lesson-title-green-check">
                            <a href="/courses/<?=$course['alias'];?>/<?=$lesson['alias'];?>"><?=$lesson['name'];?></a>
                        </div>
                    <?} elseif($access && $complete_less == 0){ ?>
                        <div class="lesson-title-yellow-circle">
                            <a href="/courses/<?=$course['alias'];?>/<?=$lesson['alias'];?>"><?=$lesson['name'];?></a>
                        </div>
                    <?} else {?>
                        <script>
                            function changeLink(url) {
                              document.getElementById('accessLink').href=url;
                              document.getElementById('accessLink').target="_blank";
                            }
                        </script>

                        <div class="lesson-title-lock">
                            <a href="#ModalAccess" onclick="changeLink('<?=$link_access;?>')" data-uk-modal="{center:true}"><?=$lesson['name'];?></a>
                        </div>
                    <?}
                    echo $lesson['less_desc'];?>
                </div>
            </div>
        <?endforeach;

        if(!empty($block)) echo '</div></div>';
    endif;?>
</div>

<div id="ModalAccess" class="uk-modal">
        <div class="uk-modal-dialog">
            <div class="userbox modal-userbox-2">
                <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
                <div class="box1">
                    <h3 class="modal-head-2"><?=System::Lang('COURSE_NOT_ACCESSED');?></h3>
                    <p><?=System::Lang('ACCESSED_COURSE');?><?if(isset($is_auth) && !$is_auth ):?> <?=System::Lang('SITE_AUTHORIZE');?><?endif;?>.</p>
                    <div class="group-button-modal">
                        <a class="button btn-yellow" id="accessLink" href="#"><?=System::Lang('GET_ACCESS');?></a>
                        <?if(isset($is_auth) && !$is_auth ):?> <a class="btn-blue-border" href="#modal-login" data-uk-modal="{center:true}"> <?=System::Lang('SITE_LOGIN');?></a><?endif;?>
                    </div>
                </div>
            </div>
        </div>
    </div>