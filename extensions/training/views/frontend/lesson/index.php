<?defined('BILLINGMASTER') or die;?>

<script src="/template/<?=$this->settings['template'];?>/js/player_bm.js" type="text/javascript"></script>
<?require_once (ROOT . '/extensions/training/web/frontend/style/lesson.php');?>

<!-- // здесь большая картинка с оверлеем если есть  -->
<?if($training['cover_settings'] == 2):?>
    <div id="hero" class="hero-wrap" style="background-image: url(/images/training/<?=$training['full_cover']?>);background-color: #373A4C; <?= empty($training['full_cover']) ? "opacity: 75%;" : "" ?>">
        <div class="h1"><?=$training['name'];?></div>

        <!--<ul class="breadcrumbs hero-breadcrumbs">
            <breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section, $lesson);
            foreach ($breadcrumbs as $link => $name):?>
                <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <endforeach;?>
        </ul>-->
    </div>
<?endif;?>

<div class="layout" id="courses">
    <?if(!$training['full_cover'] && $training['cover_settings'] != 2):?>
        <ul class="breadcrumbs">
            <?$breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training, $section, $lesson);
            foreach ($breadcrumbs as $link => $name):?>
                <li><?=$link ? "<a href=\"$link\">$name</a>" : $name;?></li>
            <?endforeach;?>
        </ul>
    <?endif;?>

    <?// Ссылка на след. урок для автотренингов
    $access_homework = TrainingLesson::checkUserAccessHomeWork($user_groups, $user_planes, $training, $task);
    $lesson_is_stop = TrainingLesson::isLessonStopStatus($lesson['lesson_id']);
    $lesson_complete = TrainingLesson::isLessonComplete($lesson['lesson_id'], $user_id);
    $prev = $lesson['sort'] > 1 ? TrainingLesson::getLessonBySort($lesson['training_id'], $lesson['sort']-1, 1, 1) : null;
    $prev_link = $prev ? "/training/view/{$training['alias']}/lesson/{$prev['alias']}" : false;
    $next = TrainingLesson::getLessonBySort($lesson['training_id'], $lesson['sort'] + 1, 1, 2);
    $next_status = Training::getAccessData($user_groups, $user_planes, $training, $section, $next);
    $next_link = $next ? "/training/view/{$training['alias']}/lesson/{$next['alias']}" : false;
    if($lesson_is_stop):
        $show_next_link = $lesson_complete && $next_status['status'] !== 7 && $next_status['status'] !== 4 ? true : false;
    else:
        $show_next_link = $next_status['status'] !== 7 && $next_status['status'] !== 4 && $next_status['status'] !== 5 ? true : false;
    endif;?>

    <div class="content-wrap" id="training_<?=$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar<?if($training['lessons_tmpl'] == 1) echo ' lesson-sidebar-outside';?>">
            <?if(isset($_GET['success'])):?>
                <div class="success_message"><?=System::Lang('USER_SUCCESS_MESS');?></div>
            <?endif;?>

            <div class="lesson-inner">
                <div class="lesson-inner-top ">
                    <h1 class="lesson-inner-h1 h2"><?=$lesson['name'];?></h1>

                    <div class="next_less_top-wrap<?if(!$prev_link) echo ' not-prev_link';?>">
                        <?if($prev_link):?>
                            <a class="next_less_top next_less_prev" href="<?=$prev_link;?>"><span><?=System::Lang('PREVIOUS_LESSON');?></span></a>
                        <?endif;

                        if($next_link && $show_next_link):?>
                            <a class="next_less_top next_less_next" href="<?=$next_link;?>"><span><?=System::Lang('NEXT_LESSON');?></span></a>
                        <?endif;

                        if($lesson['show_comments'] && $task['task_type'] != 0 && in_array($lesson_homework_status, [TrainingLesson::LESSON_STARTED, TrainingLesson::HOMEWORK_DECLINE]) && $access_homework):?>
                            <span class="z-1">
                                <a class="scroll-link btn-orange" href="#comments"><?=System::Lang('MAKE_HOME_TASK');?></a>
                            </span>
                        <?endif;?>
                    </div>
                </div>

                <?/*ЭЛЕМЕНТЫ УРОКА*/
                require_once(__DIR__ . '/elements/index.php');

                /*ДОМАШНЕЕ ЗАДАНИЕ*/
                require_once(__DIR__ . '/home_task.php');?>
            </div>

            <?if($lesson['show_comments'] && $this->tr_settings['commentcode']):?>
                <div class="block-border-top">
                    <div class="comments" id="comments">
                        <?=$this->tr_settings['commentcode'];?>
                    </div>
                </div>
            <?endif;?>

            <div class="next_less_top-wrap next_less_top-wrap--bottom<?if(!$prev_link) echo ' not-prev_link';?>">
                <?if($prev_link):?>
                    <a class="next_less_top next_less_prev" href="<?=$prev_link;?>"><span><?=System::Lang('PREVIOUS_LESSON');?></span></a>
                <?endif;

                if($next_link && $show_next_link):?>
                    <a class="next_less_top next_less_next" href="<?=$next_link;?>"><span><?=System::Lang('NEXT_LESSON');?></span></a>
                <?endif;?>
            </div>
        </div>

        <!-- Здесь просто по PHP условию выводим блок с сайдбаром или нет -->
        <?if($training['lessons_tmpl'] == 1):?> <!-- Это макет узкий, а значит блок весь выводится -->
            <aside class="sidebar">
                <!-- класс widget-sticky сделал на всякий случай, если надо, чтобы прогресс бар был плавающим. Если не нужен, можно убрать. -->
                <?if($training['cover'] && $training['cover_settings'] == 1 && $training['show_widget_progress']):?>
                    <section class="widget _instruction traning-widget">
                        <div class="sidebar-image">
                            <img src="/images/training/<?=$training['cover']?>">
                        </div>

                        <h4 class="traninig-name"><?=$training['name']?></h4>

                        <?if($user_id):?>
                            <p class="progress-text" style="margin-top: -10px !important;"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                            <?require_once (__DIR__ . '/../layouts/progressbar.php'); ?>
                        <?else:?>
                            <p><?=System::Lang('PROGRESS_OF_THE_TRAINING_WILL_BE_DISPLAYED_HERE');?></p>
                        <?endif;?>
                    </section>
                <?else:
                    if($training['cover'] /*&& !$training['full_cover']*/ &&  $training['cover_settings'] == 1):?>
                        <section class="widget _instruction traning-widget">
                            <div class="sidebar-image">
                                <img src="/images/training/<?=$training['cover']?>">
                            </div>

                            <h4 class="traninig-name"><?=$training['name']?></h4>
                        </section>
                    <?endif;?>

                    <?if($user_id && $training['show_widget_progress']):?>
                        <section class="widget _instruction traning-widget">
                            <h3><?=System::Lang('YOUR_PROGRESS');?></h3>
                            <p class="progress-text"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                            <?require_once (__DIR__ . '/../layouts/progressbar.php'); ?>
                        </section>
                    <?elseif($training['show_widget_progress']):?>
                        <section class="widget traning-widget">
                            <p><?=System::Lang('PROGRESS_OF_THE_TRAINING_WILL_BE_DISPLAYED_HERE');?></p>
                        </section>
                    <?endif;
                endif;?>

                <?if($have_certificate): ?>
                    <section class="widget traning-widget">
                        <?if(!empty($sertificate['header'])):?>
                            <h3 class="elephant-title"><i class="icon-sertifikat"></i><?=$sertificate['header'];?></h3>
                        <?endif;?>

                        <a target="_blank" href="<?=$this->settings['script_url'];?>/training/showcertificate/<?=$have_certificate['url'];?>">
                            <img src="<?=$this->settings['script_url'];?>/training/showcertificate/<?=$have_certificate['url'];?>" alt="">
                        </a>

                        <p class="text-center">
                            <a href="<?=$this->settings['script_url'];?>/training/showcertificate/<?=$have_certificate['url'];?>" class="btn-green" download>Скачать</a>
                        </p>
                    </section>
                <?endif;

                if($user_is_curator):?>
                    <section class="widget elephant-widget">
                        <h3 class="elephant-title"><i class="icon-elephant"></i><?=System::Lang('LOGIN_AS_CURATOR');?></h3>
                        <p><?=System::Lang('ALL_LESSONS_AVAILABLE');?></p>
                    </section>
                <?endif;

                if($sidebar):
                    $widget_arr = $sidebar;
                    require ("$this->widgets_path/widget_wrapper.php");
                endif;?>
            </aside>
        <?endif;?>
    </div>
</div>

<?$load_kscript = false;
$player_ks = '';

if (is_array($elements)) {
    foreach ($elements as $element):
        if($element['type'] == TrainingLesson::ELEMENT_TYPE_MEDIA):
            if($element['params']['element_type'] == 2): //video?>
                <script src="/template/<?=$this->settings['template'];?>/js/player_bm.js" type="text/javascript"></script>
                <script>
                  var player = new Playerjs({id:"player_<?=$element['id'];?>", file:window.atob("<?=base64_encode(trim($element['params']['url']));?>"), design:1, <?if($watermark != false):?>wid:"<?=$watermark;?>",<?endif;?> <?if(isset($_GET['testmode'])):?>wid_test:1, <?endif;?> poster:"<?=$element['params']['cover'];?>"});
                </script>
            <?elseif($element['params']['element_type'] == 3): //audio?>
                <script src="/template/<?=$this->settings['template'];?>/js/audio_play.js" type="text/javascript"></script>
                <script>
                  var player = new Playerjs({id:"a_player_<?=$element['id'];?>", file:window.atob("<?=base64_encode(trim($element['params']['url']));?>"), design:1});
                </script>
            <?elseif($element['params']['element_type'] == 6): //kinescope video
                $load_kscript = true;
                $watermark_text = (isset($element['params']['show_watermark']) && $element['params']['show_watermark'] == 1) ? $watermark : ' ';
                $scale_watermark = isset($element['params']['show_watermark_scale']) && is_numeric($element['params']['show_watermark_scale']) ? $element['params']['show_watermark_scale'] : 0;
                $time_visible = isset($element['params']['show_watermark_visible']) && is_numeric($element['params']['show_watermark_visible']) ? $element['params']['show_watermark_visible']*1000 : 0;
                $time_hidden = isset($element['params']['show_watermark_hidden']) && is_numeric($element['params']['show_watermark_hidden']) ? $element['params']['show_watermark_hidden']*1000 : 0;
                $player_ks .= "
                    playerFactory
                    .create('k_player_".$element['id']."', {
                        url: '".$element['params']['url']."',
                        size: { width: '100%', height: 434 },
                        ui: { watermark: { text: '".$watermark_text."', mode: 'random', scale: ".$scale_watermark.", displayTimeout: {visible:". $time_visible.", hidden: ".$time_hidden."}}},
                    })
                    .then(function (player) {
                        player
                        .once(player.Events.Ready, function (event) {
                            event.target.setVolume(0.5);
                        })
                });";
            endif;
        endif;
    endforeach;
}

if($load_kscript):?>
    <script>
      var tag = document.createElement('script');
      tag.src = 'https://player.kinescope.io/latest/iframe.player.js';
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
      function onKinescopeIframeAPIReady(playerFactory) {
          <?=$player_ks;?>
      }
    </script>
<?endif;?>

<script src="/template/<?=$this->settings['template'];?>/js/player_bm.js" type="text/javascript"></script>
