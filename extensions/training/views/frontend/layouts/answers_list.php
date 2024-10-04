<?php defined('BILLINGMASTER') or die;
if($answer_list):?>
    <div class="answer_list" id="tr_answer_list">
        <?php foreach($answer_list as $key => $answer):
            $user_answer = User::getUserNameByID($answer['user_id']);?>
            <h4 class="answer-title"><?=System::Lang('ANSWER');?></h4>
            <div class="answer_item answer_question">
                <div class="answer_item__inner">
                    <div class="answer_item__left">
                        <img src="<?=User::getAvatarUrl($user_answer, $this->settings);?>" alt="" />
                    </div>

                    <div class="answer_item__right">
                        <div class="user_name">
                            <p><?=$answer['user_name'];?><span class="user_name-id" style="display: none;">(# <?=$answer['homework_id'];?>)</span></p>
                            <span class="small"><?=date("d.m.Y H:i:s",$answer['date_user_send']);?></span>
                        </div>

                        <div class="user_message">
                            <?=html_entity_decode(base64_decode($answer['answer']));?>
                        </div>

                        <?php if(!empty($answer['work_link'])):?>
                            <div class="attach mt-5">
                                <div class="list-questions__file">
                                    <a href="<?=$answer['work_link'];?>" target="_blank" download>
                                        <span class="answer_attach_name"><?=$answer['work_link'];?></span>
                                    </a>
                                </div>
                            </div>
                        <?endif;

                        if(!empty($answer['attach'])):
                            $result = Training::sortFilesToDocumentsAndPhotos(json_decode($answer['attach'], true));
                            $image_files = $result['images'];
                            $other_files = $result['otherFiles'];?>

                            <div class="attach mt-5">
                                <div class="list-questions__file">
                                    <?if($result['images']):?>
                                        <div class="list-modal-images-wrap">
                                            <?foreach($result['images'] as $attach):?>
                                                <div class="modal-image-wrap">
                                                    <a class="modal-image" data-fancybox="" href="/load/hometask/?name=<?=urldecode($attach['real_name']);?>&history_id=<?=$answer['history_id']?>">
                                                        <img src="/load/hometask/?name=<?=urldecode($attach['real_name']);?>&history_id=<?=$answer['history_id']?>">
                                                    </a>

                                                    <a href="/load/hometask/?name=<?=urldecode($attach['real_name']);?>&history_id=<?=$answer['history_id']?>" target="_blank" download>
                                                        <i class="icon-attach-1"></i><?=$attach['name'];?>
                                                    </a>
                                                </div>
                                            <?endforeach;?>
                                        </div>
                                    <?endif;

                                    if($result['otherFiles']):
                                        foreach($result['otherFiles'] as $attach):?>
                                            <a href="/load/hometask/?name=<?=urldecode($attach['name']);?>&history_id=<?=$answer['history_id']?>" target="_blank" download>
                                                <i class="icon-attach-1"></i>
                                                <span class="answer_attach_name"><?=$attach['name'];?></span>
                                            </a>
                                        <?endforeach;
                                    endif;?>
                                </div>
                            </div>
                        <?endif;

                        if($lesson_homework_status != TrainingLesson::HOME_WORK_ACCEPTED):
                            if($lesson_homework_status != TrainingLesson::HOMEWORK_DECLINE && TrainingLesson::isAllowEditAnswer($training, $lesson_complete_status, $answer)):?>
                                <div class="user_message_edit text-right">
                                    <span class="z-1">
                                        <a class="btn-edit" href="javascript:void(0)" data-edit_answer="<?=$answer['homework_id'];?>"><i class="icon-edit"></i><?=System::Lang('EDIT');?></a>
                                    </span>
                                </div>
                            <?php else:?>
                                <div class="user_message_edit user_message_edit__with-warning">
                                    <?php if($lesson_homework_status == TrainingLesson::HOMEWORK_DECLINE):?>
                                        <div class="user_message_warning"><?=System::Lang('RESPONCE_NOT_ACCEPTED');?></div>
                                    <?php else:?>
                                        <div class="user_message_warning"><?=System::Lang('RESPONCE_ACCEPTED_CHECKING');?></div>
                                    <?php endif;?>
                                </div>
                            <?php endif;
                        endif;?>
                    </div>
                </div>
            </div>

            <?php $sub_answers = TrainingLesson::getCommentsByHomeworkID($answer['homework_id']);
            if($sub_answers):?>
                <div class="sub_answer_list-list">
                    <?foreach($sub_answers as $sub_answer):
                        if ($sub_answer['status'] == 3 && $sub_answer['modified_date'] < time() - 3600) {
                            continue;
                        }
                        $user_sub_answer = User::getUserNameByID($sub_answer['user_id']);?>
                        <div class="answer_item answer" id="tr_comment_<?=$sub_answer['comment_id'];?>">
                            <div class="answer_item__inner">
                                <div class="answer_item__left">
                                    <img src="<?=User::getAvatarUrl($user_sub_answer, $this->settings);?>" alt="" />
                                </div>

                                <div class="answer_item__right">
                                    <div class="user_name">
                                        <p><?=$sub_answer['user_name'];?></p>
                                        <span class="small"><span style="display: none;"># <?=$sub_answer['comment_id'];?></span><?=date("d.m.Y H:i:s", $sub_answer['create_date']);?></span>
                                    </div>


                                    <div class="user_message">
                                        <?php if($sub_answer['status'] != 3):?>
                                            <?=html_entity_decode(base64_decode($sub_answer['comment_text']));?>
                                        <?php else:?>
                                            <p><?=System::Lang('MESSEGE_DELITED');?></p>
                                        <?php endif;?>
                                    </div>

                                    <?php if(!empty($sub_answer['attach']) && $sub_answer['status'] != 3):
                                        $result = Training::sortFilesToDocumentsAndPhotos(json_decode($sub_answer['attach'], true));
                                        $image_files = $result['images'];
                                        $other_files = $result['otherFiles'];?>

                                        <div class="attach mt-5">
                                            <div class="list-questions__file">
                                                <?if($result['images']):?>
                                                    <div class="list-modal-images-wrap">
                                                        <?foreach($result['images'] as $attach):?>
                                                            <div class="modal-image-wrap">
                                                                <a class="modal-image" data-fancybox="" href="/load/hometask/?name=<?=urldecode($attach['real_name']);?>&comment_id=<?=$sub_answer['comment_id']?>">
                                                                    <img src="/load/hometask/?name=<?=urldecode($attach['real_name']);?>&comment_id=<?=$sub_answer['comment_id']?>">
                                                                </a>

                                                                <a href="/load/hometask/?name=<?=urldecode($attach['real_name']);?>&comment_id=<?=$sub_answer['comment_id']?>" target="_blank" download>
                                                                    <i class="icon-attach-1"></i><?=$attach['name'];?>
                                                                </a>
                                                            </div>
                                                        <?endforeach;?>
                                                    </div>
                                                <?endif;

                                                if($result['otherFiles']):
                                                    foreach($result['otherFiles'] as $attach):?>
                                                        <a href="/load/hometask/?name=<?=urldecode($attach['name']);?>&comment_id=<?=$sub_answer['comment_id']?>" target="_blank" download>
                                                            <i class="icon-attach-1"></i>
                                                            <span class="answer_attach_name"><?=$attach['name'];?></span>
                                                        </a>
                                                    <?endforeach;
                                                endif;?>
                                            </div>
                                        </div>
                                    <?php endif;?>

                                    <?php if($sub_answer['status'] == 0):?>
                                        <div class="user_message_edit text-right">
                                            <span class="z-1">
                                                <a class="btn-edit" href="javascript:void(0)"<?php if($this->tr_settings['scroll2comments']) echo ' data-scroll_to="#user_comment"';?> data-edit_comment="<?=$sub_answer['comment_id'];?>" data-lesson_id="<?=$answer['lesson_id'];?>"><i class="icon-edit"></i><?=System::Lang('EDIT');?></a>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            <?endif;
        endforeach;?>
    </div>
<?php endif;?>