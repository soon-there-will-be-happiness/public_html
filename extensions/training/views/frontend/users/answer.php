<?defined('BILLINGMASTER') or die;?>

<div class="layout" id="lk">
    <h1><?=System::Lang('CURATOR_CAB_CLIENT_ANSWER');?></h1>

    <ul class="breadcrumbs">
        <li><a href="/"><?=System::Lang('MAIN');?></a></li>
        <li><a href="/lk"><?=System::Lang('PROFILE');?></a></li>
        <li><a href="/lk/curator"><?=System::Lang('CURATOR_OFFICE');?></a></li>
        <li><?=System::Lang('QUESTIONS_LIST');?></li>
    </ul>


    <div class="content-wrap rev-content-wrap" id="training_<?=$training['training_id']?>">
        <div class="maincol<?if($sidebar) echo '_min';?> content-with-sidebar">
            <?if(isset($_GET['success'])):?>
                <div class="success_message"><?=System::Lang('USER_SUCCESS_MESS');?></div>
            <?endif;?>

            <div class="task-info">
                <h5 class="task-info-title"><i class="icon-book"></i><?=System::Lang('HOME_TASK');?></h5>
                <div class="task-info-text"><?=$task['text'];?></div>
            </div>

            <div class="dialog dialog-2 <?if($lesson_complete_status == TrainingLesson::HOMEWORK_SUBMITTED):?>assign-accept<?endif?>">
                <?if($lesson_complete_status == TrainingLesson::HOMEWORK_SUBMITTED && $top_form = true):?>
                    <form action="" method="POST" class="answer-status">
                        <input type="hidden" id="assign" name="assign" value="0">
                        <input type="submit" value="Принять" class="btn-link" name="accept">
                    </form>
                <?endif;

                if($answer_list):
                    foreach($answer_list as $answer):
                        $user_answer = User::getUserNameByID($answer['user_id'])?>
                        <div class="dialog_item dialog_item-2<?if(@ $top_form) echo ' dialog_top_form'?>">
                            <div class="dialog_item__left">
                                <img src="<?=User::getAvatarUrl($user_answer, $this->settings);?>" alt="" />
                            </div>

                            <div class="dialog_item__right">
                                <div class="list-questions__top">
                                    <h4 class="list-questions__name"><?=trim("{$answer['user_name']} {$user_answer['surname']}");?></h4>

                                    <div class="list-questions__time">
                                        <span><?=System::Lang('PASSED');?> <?=date("d.m.Y H:i:s", $answer['date_user_send']);?></span><span># <?=$answer['homework_id'];?></span>
                                        <?if($answer['status'] == TrainingLesson::HOME_WORK_ACCEPTED):
                                            $data_accept = TrainingLesson::getLessonCompleteData($answer['lesson_id'], $answer['user_id']);?>
                                            <?if(isset($data_accept)):?>
                                                <span><?=System::Lang('CHECKED_OUT');?> <?=date("d.m.Y H:i:s", $data_accept['date']);?></span>
                                            <?endif;
                                        endif;?>
                                    </div>
                                </div>

                                <ul class="list-questions__crumbs ">
                                    <li><?=System::Lang('TRENNING');?> «<?=$answer['training_name'];?>»</li>
                                    <li><?=System::Lang('LESSON');?>: <span class="lesson_name">«<a target="_blank" href="/training/view/<?="{$answer['training_alias']}/lesson/{$answer['lesson_alias']}";?>"><?=$answer['lesson_name'];?></a>»</span></li>
                                </ul>

                                <div class="list-questions__text">
                                    <?=html_entity_decode(base64_decode($answer['answer']));?>
                                </div>

                                <?if(!empty($answer['work_link'])):?>
                                    <div class="list-questions__file">
                                        <a class="answer_attach_link" target="_blank" href="<?=$answer['work_link'];?>">
                                            <span class="answer_attach_name"><?=$answer['work_link'];?></span>
                                        </a>
                                    </div>
                                <?endif?>

                                <?if(!empty($answer['attach'])):
                                    $attachFiles = json_decode($answer['attach'], true);
                                    $result = Training::sortFilesToDocumentsAndPhotos($attachFiles, 12);
                                    $imageFiles = $result['images'];
                                    $otherFiles = $result['otherFiles'];?>

                                    <div class="curator-imagesWrapper">
                                        <?if($imageFiles):
                                            foreach($imageFiles as $imageFile):?>
                                                <div class="curator-imageWrapper">
                                                    <div class="imageContainer">
                                                        <img src="/load/hometask/?name=<?=urldecode($imageFile['real_name']); ?>&history_id=<?=$answer['history_id']?>" onclick="showimage(this.src)">
                                                    </div>

                                                    <a style="text-decoration: none;margin-top:4px;" href="/load/hometask/?name=<?=urldecode($imageFile['real_name']);?>&history_id=<?=$answer['history_id']?>"
                                                       target="_blank" download><i class="icon-attach-1"></i><?= $imageFile['name'];?>
                                                    </a>
                                                </div>
                                            <?endforeach;
                                        endif;?>
                                    </div>

                                    <div class="list-questions__file">
                                        <?if($otherFiles):
                                            foreach($otherFiles as $attach):?>
                                                <div style="margin-bottom: 4px">
                                                    <a href="/load/hometask/?name=<?=urldecode($attach['name']);?>&history_id=<?=$answer['history_id']?>"
                                                       target="_blank" download><i class="icon-attach-1"></i><?=$attach['name'];?>
                                                    </a>
                                                </div>
                                            <?endforeach;
                                        endif;
                                        unset($imageFiles, $otherFiles);?>
                                    </div>
                                <?endif;

                                if($test_result['test']):?>
                                    <div class="test-result-show">
                                        <div class="test_result">
                                            <?if($test_result['test'] == 2):?>
                                                <span><?=System::Lang('TEST');?>: <span class="test-error"><?=TrainingTest::getStatusText($test_result['test']);?></span></span>
                                            <?elseif ($test_result['test'] == 1):?>
                                                <span><?=System::Lang('TEST');?>: <span class="test-success"><?=TrainingTest::getStatusText($test_result['test']);?></span></span>
                                            <?else:?>
                                                <span><?=System::Lang('TEST');?>: <span class="test-error"><?=TrainingTest::getStatusText($test_result['test']);?></span></span>
                                            <?endif;?>
                                            <a href="#ModalAccessTestAnswer" data-lesson_id="<?=$lesson_id?>" data-user_id="<?=$answer_user_id?>"><?=System::Lang('LOOKED_ANSWERS');?></a>
                                        </div>
                                        <!-- Здесь кнопку на попытку даем только если тест не сдан (2-ой статус) -->
                                        <?if($test_result['test'] == 2):?>
                                            <a href="<?=$this->settings['script_url']?>/lk/curator/answers/<?="{$homework_id}/{$answer_user_id}/{$lesson_id}?testtry";?>" class="test-result-show__button"><i class="icon-repeat"></i><?=System::Lang('GAVE_ONE_MORE_TRY');?></a>
                                        <?endif;?>
                                    </div>
                                <?endif;?>

                                <div class="list-questions__bottom">
                                    <div class="list-questions-type"><i class="icon-dom-rab"></i><?=System::Lang('HOME_WORK');?></div>

                                    <?if($assign_curator['curator_id'] == $answer['curator_id']):?>
                                        <?if($assign_curator['user_name']):?>
                                            <div class="curator-name"><?=System::Lang('CURATOR');?> <?=$assign_curator['user_name'];?></div>
                                        <?endif;
                                    elseif($answer['curator_id']):?>
                                        <div class="curator-name"><?=System::Lang('CHECKED_CURATOR');?> <?=User::getUserNameByID($answer['curator_id'])['user_name'];?></div>
                                    <?endif;?>
                                </div>
                            </div>

                            <?if($this->tr_settings['allow_del_homework']):?>
                                <div class="dialog_del_mess">
                                    <a onclick="return confirm('Вы уверены?')" href="/lk/curator/del-homework/<?=$answer['homework_id'];?>">
                                        <span class="icon-remove"></span>
                                    </a>
                                </div>
                            <?endif?>
                        </div>

                        <?$sub_answers = TrainingLesson::getCommentsByHomeworkID($answer['homework_id']);
                        if($sub_answers):
                            foreach($sub_answers as $sub_answer):
                                $user_sub_answer = User::getUserNameByID($sub_answer['user_id'])?>
                                <div class="dialog_item dialog_item-2 dialog_item__answer">
                                    <div class="dialog_item__left">
                                        <img src="<?=User::getAvatarUrl($user_sub_answer, $this->settings);?>" alt="" />
                                    </div>

                                    <div class="dialog_item__right">
                                        <div class="dialog_user_name">
                                            <span class="dialog-answer_user_name"><?=trim("{$sub_answer['user_name']} {$user_sub_answer['surname']}");?>,</span>
                                            <span class="small"># <?=$sub_answer['comment_id'];?>,</span>
                                            <span class="small"><?=date("d.m.Y H:i:s", $sub_answer['create_date']);?></span>
                                        </div>

                                        <div class="user_message">
                                            <?if($sub_answer['status'] != 3):?>
                                                <?=html_entity_decode(base64_decode($sub_answer['comment_text']));?>
                                            <?else:?>
                                                <p><?=System::Lang('MESSEGE_DELITED');?></p>
                                            <?endif;?>
                                        </div>

                                        <?if(!empty($sub_answer['attach']) && $sub_answer['attach'] != ""):
                                            $attachFiles = json_decode($sub_answer['attach'], true);
                                            $result = Training::sortFilesToDocumentsAndPhotos($attachFiles, 12);
                                            $imageFiles = $result['images'];
                                            $otherFiles = $result['otherFiles'];?>

                                            <div class="curator-imagesWrapper">
                                                <?if($imageFiles):
                                                    foreach($imageFiles as $imageFile):?>
                                                        <div class="curator-imageWrapper">
                                                            <div class="imageContainer">
                                                                <img src="/load/hometask/?name=<?=urldecode($imageFile['real_name']);?>&comment_id=<?=$sub_answer['comment_id']?>" onclick="showimage(this.src)">
                                                            </div>

                                                            <a style="text-decoration: none; margin-top: 4px;" href="/load/hometask/?name=<?=urldecode($imageFile['real_name']); ?>&comment_id=<?=$sub_answer['comment_id']?>"
                                                               target="_blank" download><i class="icon-attach-1"></i><?=$imageFile['name'];?>
                                                            </a>
                                                        </div>
                                                    <?endforeach;
                                                endif;?>
                                            </div>

                                            <div class="list-questions__file">
                                                <?if($otherFiles):
                                                    foreach($otherFiles as $attach):?>
                                                        <div style="margin-bottom: 4px">
                                                            <a href="/load/hometask/?name=<?= urldecode($attach['name']);?>&comment_id=<?=$sub_answer['comment_id']?>"
                                                               target="_blank" download><i class="icon-attach-1"></i><?= $attach['name'];?>
                                                            </a>
                                                        </div>
                                                    <?endforeach;
                                                endif;
                                                unset($imageFiles, $otherFiles);?>
                                            </div>
                                        <?endif;

                                        if($sub_answer['user_id'] == $user_id && $sub_answer['status'] != 3):?>
                                            <div class="user_message_edit text-right">
                                                <a class="btn-edit" href="javascript:void(0)" <?if($this->tr_settings['scroll2comments']) echo ' data-scroll_to="#curator_edit_answer"';?>
                                                    data-edit_curator_comment="<?=$sub_answer['comment_id'];?>" data-user_id="<?=$answer_user_id;?>" data-lesson_id="<?=$answer['lesson_id'];?>">
                                                    <i class="icon-edit"></i><?= System::Lang('EDIT');?>
                                                </a>
                                            </div>
                                        <?endif;?>
                                    </div>

                                    <?if($sub_answer['status'] != 3):?>
                                        <div class="dialog_del_mess">
                                            <a onclick="return confirm('Вы уверены?')" href="/lk/curator/delmessage/<?=$sub_answer['comment_id'];?>">
                                                <span class="icon-remove"></span>
                                            </a>
                                        </div>
                                    <?endif?>
                                </div>
                            <?endforeach;
                        endif;
                    endforeach;?>

                    <ul class="list-questions__status">
                        <?if($lesson_complete_status == TrainingLesson::HOMEWORK_SUBMITTED):?>
                            <li class="status-inoy"><i class="icon-dom-rab-asterisk"></i><span style="display: none"><?=System::Lang('ON_CHECK');?></span></li>
                        <?elseif($lesson_complete_status == TrainingLesson::HOMEWORK_ACCEPTED):?>
                            <li class="status-prinyato"><i class="icon-check"></i><span style="display: none"><?=System::Lang('ACCEPTED');?></span></li>
                        <?elseif($lesson_complete_status == TrainingLesson::HOMEWORK_DECLINE):?>
                            <li class="status-ne-sdan"><span><?=System::Lang('REJECTED');?></span></li>
                        <?endif;?>
                    </ul>
                <?endif;?>
            </div>

            <div class="curator_edit_answer" id="curator_edit_answer">
                <form class="answer-client" enctype="multipart/form-data" action="" method="POST">
                    <?if($task['hint']):?>
                        <div class="answer-client-top">
                            <div class="answer-client-top__left"><i class="icon-info"></i></div>
                            <div class="answer-client-top__right"><?=$task['hint'];?></div>
                        </div>
                    <?endif;?>

                    <div class="answer-client-middle" id="curator_answer">
                        <div class="answer-client-middle__left"><?=System::Lang('ANSWER');?></div>
                        <div class="answer-client-middle__right">
                            <textarea name="reply" class="editor"></textarea>
                        </div>
                    </div>

                    <div class="answer-client-bottom">
                        <div class="answer-client-row">
                            <div class="answer-client-col">
                                <div class="attach">
                                    <input type="file" data-browse="<?=System::Lang('UPLOAD_FILE');?>" multiple name="lesson_attach[]">
                                </div>
                            </div>

                            <div class="answer-client-col">
                                <?if($assign_curator['user_name']):?>
                                    <span class="answer-client-curator"><?=System::Lang('CURATOR');?> <?=$assign_curator['user_name'];?></span>
                                <?else:?>
                                    <div class="answer-client-checkbox">
                                        <label class="custom-checkbox assign-user">
                                            <input type="checkbox" name="assign_user" onclick="changeAssign(this);">
                                            <span><?=System::Lang('CONNECT_TO_ME');?></span>
                                        </label>
                                    </div>
                                <?endif?>

                                <div class="answer-client-checkbox">
                                    <label class="custom-checkbox assign-user">
                                        <input type="checkbox" name="send_email_to_user" checked>
                                        <span><?=System::Lang('SEND_A_MASSEGE');?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="answer-client-col">
                                <div class="answer-client-select select-wrap">
                                    <select name="status_send_complete" id="">
                                        <option value="0"><?=System::Lang('ACCEPTED_STATUS');?></option>
                                        <option value="1"><?=System::Lang('ACCEPTED');?></option>
                                        <option value="2"><?=System::Lang('REJECTED');?></option>
                                    </select>
                                </div>

                                <div class="answer-client-submit">
                                    <input type="hidden" name="answer_id" value="">
                                    <button type="submit" class="button btn-green btn-green--big save" name="post_message"><?=System::Lang('SEND');?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div id="block_edit_comment" class="block_edit_comment uk-animation-scale-up"></div>
            </div>
        </div>

        <aside class="sidebar">
            <div class="filter">
                <div class="client-info">
                    <div class="client-top">
                        <div class="client-photo">
                            <img src="<?=User::getAvatarUrl($user_info, $this->settings);?>" alt="" />
                        </div>

                        <div class="client-data">
                            <div class="client-name"><?=$user_info['user_name']?></div>
                            <div class="client-email"><a href="mailto:<?=$user_info['email']?>"><?=$user_info['email']?></a></div>
                        </div>
                    </div>

                    <?if($user_groups):?>
                        <div class="client-info-data">
                            <h5><?=System::Lang('ACCEPTED_GROUPS');?></h5>
                            <?foreach ($user_groups as $key => $group):?>
                                <div><?=User::getUserGroupData($group)['group_title']?>
                                    <? $group = User::getGroupByUserAndGroup($user_info['user_id'], $group);
                                    $date = $group ? date("d.m.Y", $group['date']) : '';?>
                                    <?if ($date) echo ': выдана '.$date;?>
                                </div>
                            <?endforeach;?>
                        </div>
                    <?endif;

                    if($user_planes):?>
                        <div class="client-info-data">
                            <h5><?=System::Lang('EMAIL_SUBSCRIBERS');?></h5>
                            <?foreach ($user_planes as $key => $plane):?>
                                <div><?=Member::getPlaneByID((int)$plane['subs_id'])['name']?>, <?=System::Lang('THE_ENDING');?> <?=date('d.m.Y', $plane['end'])?> <?='('.strval(round(($plane['end']-$plane['create_date'])/60/60/24)).' дн.)';?></div>
                            <?endforeach;?>
                        </div>
                    <?endif;?>
                </div>


                <form action="/lk/curator#answers" method="POST">
                    <h4 class="filter-title"><?=System::Lang('FILTER');?></h4>

                    <div class="one-filter">
                        <div class="select-wrap">
                            <select name="answer_type">
                                <?/*<option value="all"<?if(!isset($filter['answer_type']) || !$filter['answer_type']) echo ' selected="selected"';?>><?=System::Lang('ANSWERS_AND_COMMENTS');?></option>*/?>
                                <option value="only_answers"<?if(isset($filter['answer_type']) && $filter['answer_type'] == "only_answers") echo ' selected="selected"';?> data-show_off="filter_comments"><?=System::Lang('ANSWERS_ONLY');?></option>
                                <option value="only_comments"<?if(isset($filter['answer_type']) && $filter['answer_type'] == "only_comments") echo ' selected="selected"';?> data-show_off="filter_lesson_status"><?=System::Lang('COMMENTS_ONLY');?></option>
                            </select>
                        </div>
                    </div>

                    <div id="filter_lesson_status" class="one-filter">
                        <div class="select-wrap">
                            <select name="lesson_complete_status">
                                <option value="unchecked"<?if(!isset($filter['lesson_complete_status']) || $filter['lesson_complete_status'] == "unchecked") echo ' selected="selected"';?>><?=System::Lang('NEWS_ANSWERS');?></option>
                                <option value="checked"<?if(isset($filter['lesson_complete_status']) && $filter['lesson_complete_status'] == "checked") echo ' selected="selected"';?>><?=System::Lang('VERIFIED');?></option>
                                <option value="all"<?if(isset($filter['lesson_complete_status']) && $filter['lesson_complete_status'] == "all") echo ' selected="selected"';?>><?=System::Lang('ALL_ANSWERS');?></option>
                            </select>
                        </div>
                    </div>

                    <div id="filter_comments" class="one-filter">
                        <div class="select-wrap">
                            <select name="comments_status">
                                <option value="unread"<?if(!isset($filter['comments_status']) || $filter['comments_status'] == "unread") echo ' selected="selected"';?>><?=System::Lang('UNREAD');?></option>
                                <option value="read"<?if(isset($filter) && $filter['comments_status'] == "read") echo ' selected="selected"';?>><?=System::Lang('READED');?></option>
                                <option value="all"<?if(isset($filter) && $filter['comments_status'] == "all") echo ' selected="selected"';?>><?=System::Lang('ALL_THE_COMMENTS');?></option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-button">
                        <input class="btn-yellow filter-button-submit" type="submit" value="Выбрать" name="filter">
                        <?if(isset($filter)):?>
                            <input class="link-blue" type="submit" value="Сбросить" name="reset">
                        <?endif;?>
                    </div>

                    <input type="hidden" id="user_email" name="user_email" value="<?=$user_info['email'];?>">
                    <input type="hidden" id="myusers_id" name="myusers_id" value="all">
                </form>
            </div>

            <?require_once ("{$this->layouts_path}/sidebar2.php");?>
        </aside>
    </div>
</div>

<div id="ModalAccessTestAnswer" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-dialog-2">
        <div class="userbox"></div>
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

<script>
    function changeAssign(mycheckbox) {
        var assign = document.getElementById("assign");
        if (mycheckbox.checked) {
            assign.value = "1";
        } else {
            assign.value = "0";
        }
    }
</script>

