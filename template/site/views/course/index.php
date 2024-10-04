<?defined('BILLINGMASTER') or die;
$now = time();

if(!empty($h1)):?>
    <h1><?=$h1;?></h1>
<?endif;

if($cats):?>
    <div class="course_category">
        <h2><?=System::Lang('COURSE_CATEGORY');?></h2>

        <div class="row course_category__row" data-uk-grid-match=" { target:'.category_cover' } ">
            <?foreach($cats as $cat):?>
                <div class="col-1-3 course_category_item">
                    <div class="category_cover">
                        <?if(!empty($cat['cover'])):?>
                            <a href="/courses?category=<?=$cat['alias'];?>">
                                <img src="/images/course/category/<?=$cat['cover'];?>" alt="<?=$cat['img_alt'];?>">
                            </a>
                        <?endif;?>
                    </div>

                    <div class="category_desc">
                        <h3 class="category_desc__title"><a href="/courses?category=<?=$cat['alias'];?>"><?=$cat['name'];?></a></h3>
                        <div class="course_count"><?=System::Lang('FOR_COURSES');?> <?=Course::countCourseinCategory($cat['cat_id'], 1);?></div>
                        <?=$cat['cat_desc'];?>
                    </div>
                </div>
            <?endforeach;?>
        </div>
    </div>
<?endif;?>

<?// Тренинги
if($cat_name):?>
    <h2><?=System::Lang('CATEGORY').$cat_name;?></h2>
<?endif;

if($courses):?>
    <div class="row course_list" data-uk-grid-match=" { target:'.course_cover' } ">
        <?foreach($courses as $course): // ПЕРЕБИРАЕМ ТРЕНИНГИ
            $groups_arr = array();
            $access = false;
            if ($course['show_in_main'] == 0) {
                continue;
            }?>

            <div class="col-1-3 course_item">
                <?if(!empty($course['cover'])):?>
                    <div class="course_cover">
                        <img src="/images/course/<?=$course['cover'];?>" alt="<?=$course['img_alt'];?>"<?if(!empty($course['padding'])):?> style="padding: <?=$course['padding']?>;"<?endif;?>>
                    </div>
                <?endif;?>

                <div class="course_item__middle">
                    <h4 class="course_item__title"><?=$course['name'];?></h4>

                    <?if(!empty($course['author_id'])):?>
                        <p class="course_author"><?=System::Lang('AUTHOR');?> <?$user_name = User::getUserNameByID($course['author_id']); echo $user_name['user_name'];?></p>
                    <?endif;

                    if($course['show_desc'] == 1):?>
                        <div class="course_desc"><?=$course['short_desc'];?></div>
                    <?endif;?>
                </div>

                <div class="course_links">
                    <div class="course_readmore">
                        <?// Проверяем доступ к курсу
                        $course_id = $course['course_id'];
                        $data = Course::checkAccessCourse($course, $user_groups, $user_planes, $this->user_id);?>
                        <a class="<?=$data['class_link'];?>" href="<?=$data['button_link'];?>"><?=$data['action'];?></a>

                        <?if($data['text_link']):?>
                            <div class="current-course-lp">
                                <a class="btn-link" href="<?=$data['text_link'];?>"><?=$data['text_link_anchor'];?></a>
                            </div>
                        <?endif;?>
                    </div>

                    <?$duration = Course::countDurationByCourse($course['course_id']);
                    if($course['show_progress'] == 1 && $this->user_id):
                        $map_items = Course::getCompleteLessonsUser($this->user_id, $course['course_id']);

                        if($map_items):
                            $amount = Course::countLessonByCourse($course['course_id']);
                            $completed = count($map_items);
                            $progress = ($completed / $amount) * 100;?>

                            <!-- <div class="progress_course">
                                <div class="progress_bar">
                                    <div class="completed_line" style="width:<?=ceil($progress);?>%<?if($progress == 100) echo '; background: #4BD96A'?>"> </div>
                                </div>
                            </div>
                            
                            <div class="progress-row">
                                <div class="progress-left"><?=ceil($progress);?><?=System::Lang('PERCENTAGE_PASSED');?></div>
                                <div class="progress-right">
                                    <?=Course::countLessonByCourse($course['course_id']);?> <?=System::Lang('FOR_LESSONS');?>
                                </div> 
                            </div> -->
                        <?endif;
                    endif;?>
                </div>

                <div class="course_data_wrap">
                    <ul class="course_data old_course_data">
                        <?if(!empty($duration)):?>
                            <li><?=System::Lang('TIME');?><br><?=$duration;?> <?=System::Lang('MINUTES');?></li>
                        <?endif;

                        if($course['show_lessons_count'] == 1):?>
                            <li class="text-center"><?=System::Lang('LESSONS_FOR');?><br><?=Course::countLessonByCourse($course['course_id']);?></li>
                        <?endif;

                        if($course['show_hits'] == 1):?>
                            <li><nobr><?=System::Lang('VIEWS');?></nobr><br><?=Course::countHitsByCourse($course['course_id']);?></li>
                        <?endif;

                        if($course['sertificate_id'] != 0):?>
                            <li><?=System::Lang('CERTIFICAT_AVAILABLE');?></li>
                        <?endif;?>

                        <li class="text-center"><?=System::Lang('ACCESS');?><br><?=($course['is_free'] == 1 ? 'Бесплатно' : 'Платно');?></li>

                        <?if($course['show_begin'] == 1):?>
                            <li class="text-center"><?=System::Lang('START');?><br>
                                <?=($now < $course['start_date'] ? date("d.m.Y H:i", $course['start_date']) : 'любое время');?>
                            </li>
                        <?endif;?>
                    </ul>
                </div>
            </div>
        <?endforeach;?>
    </div>
<?endif;?>