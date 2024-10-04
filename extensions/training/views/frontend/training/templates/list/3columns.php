<?defined('BILLINGMASTER') or die;
$now = time();

if ((isset($this->tr_settings['filter']) || isset($widget_params['params']['filter'])) && $this->view['is_page'] != 'my_trainings') {
    require_once(__DIR__ . '/../../../filter/filter.php');
    $training_filter_enabled = true;
}

$error = false;
if (isset($_GET['category'])) {
    $category = TrainingCategory::getCategoryByAlias($_GET['category']);
    $error = is_array($category) ? false : true;
}

if(@!$error):?>
    <div class="row training_list row-3-column" data-uk-grid-match=" { target:'.course_cover' } ">
        <?if($training_list):
            $tr_index = 0;
            foreach($training_list as $key => $training):
                if ((!$training['show_in_main']) && ($current_url !== "lk/mytrainings")) {
                    continue;
                }

                if (isset($_GET['category']) && isset($category) && $training['cat_id'] != $category['cat_id']) {
                    continue;
                }


                $access = Training::getAccessData($user_groups, $user_planes, $training);
                $buttons = Training::renderByButtons($access, $training);?>

                <div class="col-1-3 course_item col-1-3__training-2">
                    <div class="course_item__top <?if(empty($training['cover'])):?>course_item__top--mt-25<?endif;?>">
                        <h3 class="course_item__title"><?=$training['name'];?></h3>
                        <?if($training['show_price']):?>
                            <span class="course_item__price"><?=!empty($training['price']) ? "{$training['price']}" : System::Lang('FREE');?></span>
                        <?endif;

                        if(!empty($training['authors'])):
                            $authors = explode(',', $training['authors']);?>
                            <p class="training_author"><?=count($authors) < 2 ? System::Lang('AUTHOR') : System::Lang('AUTHORS').':';?>
                                <?foreach($authors as $_key => $author) {
                                    $user_name = User::getUserNameByID($author);
                                    if($user_name)
                                        echo ($_key > 0 ? ', ' : '') . $user_name['user_name'] . ' ' . $user_name['surname'];
                                }?>
                            </p>
                        <?endif;?>
                    </div>

                    <?if($training['show_desc'] && $training['short_desc']):?>
                        <div class="course_desc"><?=html_entity_decode($training['short_desc']);?></div>
                    <?endif;

                    if(!empty($training['cover'])):?>
                        <div class="course_cover">
                            <img src="/images/training/<?=$training['cover'];?>" alt="<?=$training['img_alt'];?>"<?if($training['padding']) echo ' style="padding: '.$training['padding'].';"';?>>
                        </div>
                    <?endif;

                    if($user_id && $training['show_progress2list']): // получение пройденых уроков юзера
                        require (__DIR__ . '/../../../layouts/progressbar2list.php');?>
                    <?endif;?>

                    <div class="course_bottom">
                        <div class="course_data_wrap">
                            <ul class="course_data">
                                <?if(isset($training['show_passage_time']) && $training['show_passage_time'] && $training['duration_type'] == 2):?>
                                    <li class="tr-info">
                                        <span class="icon-tr_time text-center"></span>
                                        <span class="text-center"><?=$training['duration']?></span
                                    </li>
                                <?elseif (isset($training['show_passage_time']) && $training['show_passage_time']  && $training['duration_type'] == 1):?>
                                    <li class="tr-info">
                                        <span class="icon-tr_time text-center"></span>
                                        <span class="text-center"><?=Training::countDurationByTraining($training);?></span>
                                    </li>
                                <?endif;

                                if($training['show_count_lessons']):?>
                                    <li class="tr-info">
                                        <span class="icon-tr_count_lessons text-center"></span>
                                        <span class="text-center"><?=System::Lang('LESSONS_FOR').' '.TrainingLesson::getCountLessons2Training($training, $training['count_lessons_type']);?></span>
                                    </li>
                                <?endif;

                                if($training['show_complexity'] == 1):?>
                                    <li class="tr-info">
                                        <span class="icon-tr_level text-center"></span>
                                        <span class="text-center">
                                            <?if ($training['complexity'] == 1): ?>
                                                <?=System::Lang('LIGHT_LEVEL');?>
                                            <?elseif ($training['complexity'] == 2): ?>
                                                <?=System::Lang('AVERIGE_LEVEL');?>
                                            <?elseif ($training['complexity'] == 3): ?>
                                                <?=System::Lang('COMPLEX_LEVEL');?>
                                            <?endif;?>
                                        </span>
                                    </li>
                                <?endif;

                                if(isset($training['sertificate'])):
                                    $show_sert = json_decode($training['sertificate'], true);
                                    if (isset($show_sert['show_sert']) && $show_sert['show_sert'] == 1):?>
                                        <li class="tr-info">
                                            <span class="icon-tr_certificate text-center"></span>
                                            <span class="text-center"><?=System::Lang('CERTIFICAT_AVAILABLE');?></span>
                                        </li>
                                    <?endif;
                                endif;

                                if($training['show_start_date'] == 1):
                                    if (!isset($training['params']['start_date_write']) || !$training['params']['start_date_write']) {
                                        $start_date = $now < $training['start_date'] ? date("d.m.Y", $training['start_date']) : System::Lang('ALWAYS');
                                    } else {
                                        $start_date = $training['params']['start_date_text'];
                                    }?>
                                    <li class="tr-info">
                                        <span class="icon-tr_start text-center"></span>
                                        <span class="text-center"><?=$start_date;?></span>
                                    </li>
                                <?endif;?>
                            </ul>
                        </div>

                        <div class="course_links">
                            <div class="course_readmore">
                                <?if($buttons['big_button']):?>
                                    <div class="z-1">
                                        <?if($training['show_start_date'] && isset($buttons['over_button_text'])):?>
                                            <p class="small"><?=$buttons['over_button_text'];?></p>
                                        <?endif;

                                        if(mb_stripos($buttons['big_button']['url'], "?viewmodal")):?>
                                            <a data-uk-lightbox="" data-lightbox-type="iframe" class="<?=Training::getCssClasses($this->settings, $buttons['big_button']['class-type']);?>" href="<?=System::replaceNameEmail($buttons['big_button']['url'], $user_id);?>">
                                                <?=$buttons['big_button']['text'];?>
                                            </a>
                                        <?else:?>
                                            <a<?=isset($buttons['big_button']['target_blank']) ? ' target="_blank"' : '';?> class="<?=Training::getCssClasses($this->settings, $buttons['big_button']['class-type']);?>" href="<?=System::replaceNameEmail($buttons['big_button']['url'], $user_id);?>">
                                                <?=$buttons['big_button']['text'];?>
                                            </a>
                                        <?endif;?>
                                    </div>
                                <?endif;

                                if(isset($buttons['small_button']) && $buttons['small_button']):?>
                                    <div class="z-1">
                                        <?if(mb_stripos($buttons['small_button']['url'], "?viewmodal")):?>
                                            <a data-uk-lightbox="" data-lightbox-type="iframe" class="<?=Training::getCssClasses($this->settings, $buttons['small_button']['class-type']);?>" href="<?=System::replaceNameEmail($buttons['small_button']['url'], $user_id);?>">
                                                <?=$buttons['small_button']['text'];?>
                                            </a>
                                        <?else:?>
                                            <a<?=isset($buttons['small_button']['target_blank']) ? ' target="_blank"' : '';?> class="<?=Training::getCssClasses($this->settings, $buttons['small_button']['class-type']);?>" href="<?=System::replaceNameEmail($buttons['small_button']['url'], $user_id);?>">
                                                <?=$buttons['small_button']['text'];?>
                                            </a>
                                        <?endif;?>
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                </div>

                <?if(++$tr_index % 3 == 0 && count($training_list) != $tr_index):?>
                    <div class="course_line"></div>
                <?endif;
            endforeach;
        endif;?>
    </div>
<?else:?>
    <h2>Ничего не найдено</h2>
<?endif;?>