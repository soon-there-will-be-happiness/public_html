<?defined('BILLINGMASTER') or die;?>

<div class="layout" id="training">
    <h1><?=System::Lang('CURATOR_OFFICE');?></h1>

    <ul class="breadcrumbs">
        <li><a href="/"><?=System::Lang('MAIN');?></a></li>
        <li><a href="/lk"><?=System::Lang('PROFILE');?></a></li>
        <li><?=System::Lang('CURATOR_OFFICE');?></li>
    </ul>

    <div class="content-wrap rev-content-wrap" id="training_<?=@$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar">
            <div id="answers">
                <?if(isset($_GET['success'])):?>
                    <div class="success_message"><?=System::Lang('USER_SUCCESS_MESS');?></div>
                <?endif;

                if($answer_list):?>
                    <?foreach($answer_list as $answer):
                        $answer_url = $this->settings['script_url'] . "/lk/curator/answers/{$answer['homework_id']}/{$answer['user_id']}/{$answer['lesson_id']}"; ?>
                        <div class="list-questions">
                            <div class="list-questions__left">
                                <?$user_answer = User::getUserNameByID($answer['user_id']);?>
                                <img src="<?=User::getAvatarUrl($user_answer, $this->settings);?>" alt="" />
                            </div>

                            <div class="list-questions__right">
                                <div class="list-questions__top">
                                    <h4 class="list-questions__name">
                                        <?if((isset($answer['answer']) && $answer['answer']) || $answer['comment_text']):?>
                                            <a href="/lk/curator/answers/<?="{$answer['homework_id']}/{$answer['user_id']}/{$answer['lesson_id']}";?>"><?=$answer['user_name'];?><?=$answer['surname'] ? '&nbsp;'.$answer['surname'] : '';?></a>
                                        <?else:
                                            echo trim("{$answer['user_name']} {$answer['surname']}");?>
                                        <?endif;?>
                                    </h4>

                                    <div class="list-questions__time">
                                        <?if($answer['type_items'] == 'answer' && isset($answer['create_date'])):?>
                                            <span><?=System::Lang('PASSED');?> <?=date("d.m.Y H:i:s", $answer['create_date']);?></span><span>#<?=$answer['homework_id'];?></span>
                                        <?elseif($answer['type_items'] != 'answer'):?>
                                            <span><?=System::Lang('COMMENT_DATE');?> <?=date("d.m.Y H:i:s", $answer['create_date']);?></span><span>#<?=$answer['homework_id'];?></span>
                                        <?endif;

                                        if ($answer['status'] == TrainingLesson::HOME_WORK_ACCEPTED && $answer['type_items'] == 'answer'):
                                            $data_accept = TrainingLesson::getLessonCompleteData($answer['lesson_id'], $answer['user_id']);?>
                                            <?if(isset($data_accept)):?>
                                                <span><?=System::Lang('CHECKED_OUT');?> <?=date("d.m.Y H:i:s", $data_accept['date']);?></span>
                                            <?endif;
                                        endif;?>
                                    </div>
                                </div>

                                <ul class="list-questions__crumbs">
                                    <li><?=System::Lang('TRENNING');?> «<?=$answer['training_name'];?>»</li>
                                    <li><?=System::Lang('LESSON');?>: <span class="lesson_name">«<?=$answer['lesson_name'];?>»</span></li>
                                </ul>

                                <ul class="list-questions__status">
                                    <?if ($answer['type_items'] == 'answer'):?>
                                        <?if ($answer['status'] == TrainingLesson::HOME_WORK_ACCEPTED && $answer['type_items'] == 'answer'):?>
                                            <li class="status-prinyato"><i class="icon-check"></i></li>
                                        <?elseif ($answer['status'] == TrainingLesson::HOME_WORK_DECLINE):?>
                                            <li class="status-ne-sdan">
                                                <span><?=System::Lang('TEST_NOT_PASSED');?></span>
                                            </li>
                                        <?else:?>
                                            <li class="status-inoy">
                                                <span style="display: none"><?=System::Lang('ANOTHER_STATUS');?></span><span class="icon-dom-rab-asterisk"></span>
                                            </li>
                                        <?endif;?>
                                    <?endif;?>
                                </ul>

                                <div class="list-questions__text">
                                    <?if ($answer['type_items'] == 'answer'):?>
                                        <?=mb_substr(trim(strip_tags(html_entity_decode(base64_decode($answer['answer'])))), 0, 100);?>
                                    <?else:?>
                                        <?=mb_substr(trim(strip_tags(html_entity_decode(base64_decode($answer['comment_text'])))), 0, 100);?>
                                    <?endif;?>
                                </div>

                                <?if(!empty($answer['attach'])):?>
                                    <?$attachFiles = json_decode($answer['attach'], true);
                                    $result = Training::sortFilesToDocumentsAndPhotos($attachFiles);
                                    $imageFiles = $result['images'];
                                    $otherFiles = $result['otherFiles'];?>

                                    <div class="curator-imagesWrapper">
                                        <?foreach ($imageFiles as $imageFile) { //изображения ?>
                                            <div class="curator-imageWrapper">
                                                <div class="imageContainer">
                                                    <img src="/load/hometask/?name=<?=urldecode($imageFile['real_name']);?>&history_id=<?=$answer['history_id']?>" onclick="showimage(this.src)">
                                                </div>
                                                <a style="text-decoration: none; margin-top: 4px;" href="/load/hometask/?name=<?=urldecode($imageFile['real_name']);?>&history_id=<?=$answer['history_id']?>" target="_blank" download><i class="icon-attach-1"></i><?=$imageFile['name'];?></a>
                                            </div>
                                        <?}?>
                                    </div>

                                    <div class="list-questions__file">
                                        <?foreach($otherFiles as $attach) { //файлы ?>
                                            <div style="margin-bottom: 4px">
                                                <a href="/load/hometask/?name=<?=urldecode($attach['name']);?>&history_id=<?=$answer['history_id']?>" target="_blank" download><i class="icon-attach-1"></i><?=$attach['name'];?></a>
                                            </div>
                                        <?}?>
                                    </div>
                                <?endif;

                                if (isset($answer['task_type']) && $answer['task_type'] > 1):?>
                                    <div class="test-result-show"> <?=System::Lang('TEST');?>:
                                        <?if ($answer['test'] == '0'):?>
                                            <span style="color: #FFCA10;"><?=System::Lang('IN_PROCESS');?></span>
                                        <?elseif ($answer['test'] == '1'):?>
                                            <span>
                                                <span style="color: #5DCE59;"><?=System::Lang('DONE');?></span>
                                                <a href="#ModalAccessTestAnswer" data-lesson_id="<?=$answer['lesson_id']?>" data-user_id="<?=$answer['user_id']?>"><?=System::Lang('LOOKED_ANSWERS');?></a>
                                            </span>
                                        <?elseif ($answer['test'] == '2'):?>
                                            <span>
                                                <span style="color: #E04265;"><?=System::Lang('NOT_PASSED');?></span> <a href="#ModalAccessTestAnswer" data-lesson_id="<?=$answer['lesson_id']?>" data-user_id="<?=$answer['user_id']?>"><?=System::Lang('LOOKED_ANSWERS');?></a>
                                            </span>

                                            <!-- Здесь кнопку на попытку даем только если тест не сдан (2-ой статус) -->
                                            <?if($answer['test'] == '2'):?>
                                                <a href="<?=$answer_url. "?testtry";?>" class="test-result-show__button">
                                                    <i class="icon-repeat"></i><?=System::Lang('GAVE_ONE_MORE_TRY');?>
                                                </a>
                                            <?endif;?>

                                            <!-- Сейчас у нас есть просто принять ответ либо отклонить
                                            <a href="#" class="test-result-show__button"><i class="icon-skip"></i>Пропустить</a> -->
                                        <?else:?>
                                            <span style="color: #E04265;"><?=System::Lang('NOT_START');?></span>
                                        <?endif;?>
                                    </div>
                                <?endif;?>

                                <div class="list-questions__file"></div>

                                <div class="list-questions__bottom">
                                    <?if($answer['type_items'] == 'answer'):?>
                                        <div class="list-questions-type"><i class="icon-dom-rab"></i><?=System::Lang('HOME_WORK');?></div>
                                    <?else:?>
                                        <div class="list-questions-type"><i class="icon-dom-rab-komment"></i><?=System::Lang('COMMENT');?></div>
                                    <?endif;?>

                                    <div>
                                        <form id="accept" action="" method="POST" class="form-accept">
                                            <?if($answer['type_items'] == 'answer'):
                                                if($answer['teacher']):
                                                    if($answer['teacher'] == $answer['curator_id'] || $answer['curator_id'] == 0):?>
                                                        <div><?=System::Lang('CURATOR');?> <?=$answer['curator_name']?></div>
                                                    <?elseif($answer['curator_id']):?>
                                                        <div class="curator-name"><?=System::Lang('CHECKED_CURATOR');?> <?=User::getUserNameByID($answer['curator_id'])['user_name'];?></div>
                                                    <?endif;
                                                else:?>
                                                    <div>
                                                        <?if($answer['answer'] && $answer['status'] != TrainingLesson::HOME_WORK_ACCEPTED):?>
                                                            <label class="custom-checkbox assign-user">
                                                                <input type="checkbox" name="assign_user" value="1">
                                                                <span><?=System::Lang('CONNECT_TO_ME');?></span>
                                                            </label>
                                                        <?endif;?>
                                                    </div>
                                                <?endif;

                                                if($answer['status'] == TrainingLesson::HOME_WORK_ACCEPTED):?>
                                                    <?if($answer['answer'] || $answer['attach'] || $answer['work_link']):?>
                                                        <a class="btn-green" href="<?=$answer_url;?>"><?=System::Lang('LOOK');?></a>
                                                    <?endif;
                                                else:?>
                                                    <ul class="accept-dz">
                                                        <li class="nav_gorizontal__parent-wrap">
                                                            <input type="hidden" value="accept" name="accept">
                                                            <input type="hidden" value="<?=$answer['homework_id']?>" name="homework_id">
                                                            <input type="hidden" value="<?=$answer['user_id']?>" name="user_id">
                                                            <input type="hidden" value="<?=$answer['lesson_id']?>" name="lesson_id">
                                                            <input type="submit" name="answer_send" class="btn-green dz-submit-btn" value="Принять">

                                                            <?if($answer['answer'] || $answer['attach']):
                                                                if($answer['auto_answer']):?>
                                                                    <a class="btn-green dz-submit-btn" href="<?=$answer_url ."?auto=1";?>">
                                                                        <?=System::Lang('ACCEPT_AND_ANSWER');?>
                                                                    </a>
                                                                <?endif;?>

                                                                <a class="btn-green dz-submit-btn" href="<?=$answer_url;?>">
                                                                    <?=System::Lang('GIVE_ANSWER');?>
                                                                </a>
                                                            <?endif;?>
                                                        </li>
                                                    </ul>
                                                <?endif;
                                            else:?>
                                                <a class="btn-green dz-submit-btn" href="<?=$answer_url;?>">
                                                    <?=System::Lang('GIVE_ANSWER');?>
                                                </a>
                                            <?endif;?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?endforeach;
                endif;?>
            </div>
            <?if(isset($pagination) && $pagination->amount>1) echo $pagination->get();?>
        </div>

        <aside class="sidebar">
            <div class="filter" id="filter">
                <form action="/lk/curator#answers" method="POST">
                    <h4 class="filter-title"><?=System::Lang('RESULTS_FILTER');?> <?=$total;?></h4>

                    <div class="one-filter">
                        <div class="select-wrap">
                            <select name="training_id">
                                <option value=""<?if(isset($filter) && $filter['training_id'] === null) echo ' selected="selected"';?>><?=System::Lang('ALL_TRENNINGS');?></option>
                                <?if($trainings_to_curator):
                                    foreach($trainings_to_curator as $training):?>
                                        <option value="<?=$training['training_id'];?>"<?if(isset($filter) && $filter['training_id'] == $training['training_id']) echo ' selected="selected"';?>><?=$training['name'];?></option>
                                    <?endforeach;
                                endif;?>
                            </select>
                        </div>
                    </div>

                    <?if(isset($filter['training_id']) && $filter['training_id'] != 0):?>
                        <div class="one-filter">
                            <div class="select-wrap">
                                <select name="lesson_id">
                                    <option value="0"><?=System::Lang('ALL_THE_LESSONS');?></option>
                                    <?if(isset($lesson_list)):
                                        foreach($lesson_list as $lesson):?>
                                            <option value="<?=$lesson['lesson_id'];?>"<?if(isset($filter['lesson_id']) && $filter['lesson_id'] == $lesson['lesson_id']) echo ' selected="selected"';?>><?=$lesson['name'];?></option>
                                        <?endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                    <?endif;?>

                    <div class="one-filter">
                        <div class="select-wrap">
                            <select name="curator_users">
                                <option value="my_users"<?if(isset($filter['curator_users']) && $filter['curator_users'] == 'my_users') echo ' selected="selected"';?>><?=System::Lang('MY_STUDENTS');?></option>
                                <?if($user['role'] == 'admin' && $curators = User::getCurators()):?>                                
                                    <option value="all"<?if(!isset($filter['curator_users']) || $filter['curator_users'] == 'all') echo ' selected="selected"';?>><?=System::Lang('ALL_STUDENTS');?></option>
                                    <option value="choose_curator" data-show_on="filter_curator_id"<?if(isset($filter['curator_users']) && $filter['curator_users'] == 'choose_curator') echo ' selected="selected"';?>><?=System::Lang('CHOOSE_THE_CURATOR');?></option>
                                <?endif;?>
                            </select>
                        </div>
                    </div>

                    <?if($user['role'] == 'admin' && $curators):?>
                        <div class="one-filter hidden" id="filter_curator_id">
                            <div class="select-wrap">
                                <select name="curator_id">
                                    <?foreach ($curators as $curator):?>
                                        <option value="<?=$curator['user_id']?>" <?if(isset($filter['curator_id']) && $filter['curator_id'] == $curator['user_id']) echo ' selected="selected"';?>><?=trim("{$curator['surname']} {$curator['user_name']}")?></option>
                                    <?endforeach;?>
                                </select>
                            </div>
                        </div>
                    <?endif;?>

                    <div class="one-filter">
                        <div class="select-wrap">
                            <select name="answer_type">
                                <?/*<option value="all"<?if(!isset($filter['answer_type']) || !$filter['answer_type']) echo ' selected="selected"';?>><?=System::Lang('ANSWERS_AND_COMMENTS');?></option>*/?>
                                <option value="only_answers"<?if(isset($filter['answer_type']) && $filter['answer_type'] == "only_answers") echo ' selected="selected"';?> data-show_off="show_comments"><?=System::Lang('ANSWERS_ONLY');?></option>
                                <option value="only_comments"<?if(isset($filter['answer_type']) && $filter['answer_type'] == "only_comments") echo ' selected="selected"';?> data-show_off="show_lesson_status"><?=System::Lang('COMMENTS_ONLY');?></option>
                            </select>
                        </div>
                    </div>

                    <div id="show_lesson_status" class="one-filter">
                        <div class="select-wrap">
                            <select name="lesson_complete_status">
                                <option value="unchecked"<?if(!isset($filter['lesson_complete_status']) || $filter['lesson_complete_status'] == "unchecked") echo ' selected="selected"';?>><?=System::Lang('NEWS_ANSWERS');?></option>
                                <option value="checked"<?if(isset($filter['lesson_complete_status']) && $filter['lesson_complete_status'] == "checked") echo ' selected="selected"';?>><?=System::Lang('VERIFIED');?></option>
                                <option value="all"<?if(isset($filter['lesson_complete_status']) && $filter['lesson_complete_status'] == "all") echo ' selected="selected"';?>><?=System::Lang('ALL_ANSWERS');?></option>
                            </select>
                        </div>
                    </div>

                    <div id="show_comments" class="one-filter">
                        <div class="select-wrap">
                            <select name="comments_status">
                                <option value="unread"<?if(!isset($filter['comments_status']) || $filter['comments_status'] == "unread") echo ' selected="selected"';?>><?=System::Lang('UNREAD');?></option>
                                <option value="read"<?if(isset($filter['comments_status']) && $filter['comments_status'] == "read") echo ' selected="selected"';?>><?=System::Lang('READED');?></option>
                                <option value="all"<?if(isset($filter['comments_status']) && $filter['comments_status'] == "all") echo ' selected="selected"';?>><?=System::Lang('ALL_THE_COMMENTS');?></option>
                            </select>
                        </div>
                    </div>

                    <div class="one-filter">
                        <div class="datetimepicker-wrap">
                            <input type="text" name="start_date" class="datetimepicker" autocomplete="off" value="<?=isset($filter['start_date']) ? date('d.m.Y H:i', $filter['start_date']) : '';?>" placeholder="От">
                        </div>
                    </div>

                    <div class="one-filter">
                        <div class="datetimepicker-wrap">
                            <input type="text" name="finish_date" class="datetimepicker" autocomplete="off" value="<?=isset($filter['finish_date']) ? date('d.m.Y H:i', $filter['finish_date']) : '';?>" placeholder="До">
                        </div>
                    </div>

                    <div class="one-filter">
                        <input type="text" name="user_name" value="<?=isset($filter['user_name']) ? trim("{$filter['user_name']} {$filter['user_surname']}") : '';?>" placeholder="Имя">
                    </div>

                    <div class="one-filter">
                        <input type="text" name="user_email" value="<?=isset($filter['user_email']) ? $filter['user_email'] : '';?>" placeholder="E-mail">
                    </div>

                    <div class="filter-button">
                        <input class="btn-yellow filter-button-submit" type="submit" value="Выбрать" name="filter">
                        <?if(isset($filter)):?>
                            <input class="link-blue" type="submit" value="Сбросить" name="reset">
                        <?endif;?>
                    </div>
                </form>
            </div>

            <?require_once (ROOT . '/template/site/layouts/sidebar2.php');?>
        </aside>
    </div>
</div>

<div id="ModalAccessTestAnswer" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-dialog-2">
        <div class="userbox"></div>
    </div>
</div>
<div id="fullscreenimagewrapper" onclick="closeimage()">
    <div id="wrapperimage-full">
        <img id="fullscreenimage">
    </div>
    <img id="btnclosefullscreenimage" src="/template/new_simple/images/close.png" style="width: 30px;" onclick="closeimage()" title="Закрыть">
</div>
<!-- Отображение изображений по клику во вложениях -->
<script>
    function showimage(src) {
        document.getElementById('fullscreenimage').src = src;
        document.getElementById('fullscreenimagewrapper').style.display = 'flex';
    }
    function closeimage() {
        document.getElementById('fullscreenimagewrapper').style.display = 'none';
    }
</script>