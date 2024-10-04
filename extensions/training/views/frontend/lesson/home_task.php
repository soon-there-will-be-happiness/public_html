<?defined('BILLINGMASTER') or die;
$hisory_id = $homework ? TrainingLesson::answerIsExists($homework['homework_id']) : null;?>

<div class="lesson-sidebar-inside">
    <div class="content-with-sidebar">
        <?if ($user_id):
            if($task['task_type'] > 0 && $levelAccessTypeHomeWork >= 0 && $levelAccessTypeHomeWork !== false):?>
                <div class="block-border-top">
                    <div class="home-work__with-sidebar">
                        <?if($task['task_type'] >= 2):?>
                            <div class="home-work-inner">
                                <div class="block-border-top">
                                    <?require_once(__DIR__ . '/tests/form.php');?>
                                </div>
                            </div>
                        <?endif;?>

                        <div class="home-work-inner">
                            <?if($task['task_type'] < 3):?>
                                <div class="block-border-top homework-top home-work-wrap">
                                    <h4 class="home-work__title"><?=System::Lang('HOME_TASK');?>
                                        <?if($task['check_type'] > $levelAccessTypeHomeWork):?>
                                            <span class="small-caption">(<?=TrainingLesson::getTaskTypeText($levelAccessTypeHomeWork, 1);?>)
                                                <a href="#modalAccessTask" data-uk-modal="{center:true}"><?=System::Lang('CHANGE');?></a>
                                            </span>
                                        <?endif;

                                        require_once(__DIR__ . '/../layouts/homework_status.php');?>
                                    </h4><hr class="separating_line">

                                    <div class="home-work-content"><?=$task['text'];?></div><hr class="separating_line">

                                    <?if($task['task_type'] != 0):
                                        if ($task['check_type'] != 0) {
                                            require_once(__DIR__ . '/../layouts/answers_list.php');
                                        }

                                        if ($task['task_type'] != 3) {
                                            // тут делаем проверку если задание отклонено то форму редактирования
                                            if ($lesson_homework_status == TrainingLesson::HOMEWORK_DECLINE) {
                                                require_once(__DIR__ . '/edit_answer.php');
                                            } else {
                                                require_once(__DIR__ . '/../layouts/answer_form.php');
                                            }
                                        }
                                    endif;

                                    require_once(__DIR__ . '/public_homework/index.php');?>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                </div>
            <?elseif($task['task_type'] != 0):?>
                <div class="not-dz">
                    <span class="h4"><?=System::Lang('HOME_TASK');?></span> <?=System::Lang('NO_HOME_TASK');?>
                    <?if(Training::isShowByButtonsToHw($training)):?>
                        <a href="#modalAccessTask" data-uk-modal="{center:true}"><?=System::Lang('IMPROVE');?></a>
                    <?endif;?>
                </div>
            <?elseif($task['task_type'] == 0 && $lesson['auto_access_lesson'] == 0 && $lesson_complete_status !=3):?>
                <form enctype="multipart/form-data" class="form-complete" action="" method="POST" id="answer_form">
                    <div class="add-home-work-submit z-1">
                        <button type="submit" name="complete" class="button btn-orange btn-green btn-green--big"><?=System::Lang('MARK_PASSED');?></button>
                    </div>
                </form>
            <?endif;
        elseif($task['task_type'] > 0):?>
            <div class="block-border-top homework-top homework-bottom home-work-wrap">
                <h4 class="home-work__subtitle"><?=System::Lang('HOME_TASK');?></h4>
                <?=System::Lang('ACCESS_TO_HOMEWORK');?>
                <div class="home-work__login">
                    <a class="btn-blue-history" href="#modal-login" data-uk-modal="{center:true}"><?=System::Lang('LOGIN');?></a>
                </div>
            </div>
        <?endif;?>
    </div>


    <?if($training['lessons_tmpl'] == 2):?><!-- Если макет урока широкий, то сайдбар выводится тут в низу -->
        <aside class="sidebar">
            <?if ($training['cover'] && $training['cover_settings'] == 1 && $training['show_widget_progress']):?>
                <section class="widget _instruction traning-widget">
                    <div class="sidebar-image">
                        <img src="/images/training/<?=$training['cover']?>">
                    </div>

                    <h4 class="traninig-name"><?=$training['name']?></h4>

                    <?if($user_id):?>
                        <p class="progress-text" style="margin-top: -10px !important;"><?=System::Lang('TRACK_YOUR_TRAINING');?></p>
                        <?require_once (__DIR__ . '/../layouts/progressbar.php');?>
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

            <?if($sidebar):
                $widget_arr = $sidebar;
                require ("$this->widgets_path/widget_wrapper.php");
            endif;?>
        </aside>
    <?endif;?>
</div>

<div id="modalAccessTask" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="userbox modal-userbox-2">
            <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
            <div class="box1">
                <h3 class="modal-head-2"><?=System::Lang('CHANGE_ACCESS_HOME_TASK');?></h3>
                <div class="group-button-modal align-center">
                    <?if ($task['check_type'] == 2):
                        $by_button = json_decode($training['by_button_curator_hw'], true);
                    elseif ($task['check_type'] == 1):
                        $by_button = json_decode($training['by_button_autocheck_hw'], true);
                    elseif (intval($task['check_type']) === 0):
                        $by_button = json_decode($training['by_button_self_hw'], true);
                    endif;
                    $link = Training::getLink2ByButton($by_button['type'], $by_button, $training, $task['check_type']);?>
                    <a class="button btn-yellow" id="accessLink" href="<?=$link?>"><?=$by_button['text']?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_comment_edit" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="userbox modal-userbox-2">
            <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
            <div class="box1">
                <h3 class="modal-head-2"><?=System::Lang('CHANGE_COMMENT');?></h3>
                <div class="group-button-modal align-center">
                    <a class="button btn-yellow" id="accessLink" href="<?=$link?>"><?=$by_button['text']?></a>
                </div>
            </div>
        </div>
    </div>
</div>