<?php defined('BILLINGMASTER') or die;?>
<div class="answer_form_wrap">
    <form enctype="multipart/form-data" class="form-complete" action="" method="POST" id="answer_form">
        <div class="block-border-top">
            <div class="add-home-work">
                <?if(!$lesson_homework_status || $lesson_homework_status == TrainingLesson::HOMEWORK_DECLINE || !$hisory_id):
                    if($task['check_type'] != 0 && $levelAccessTypeHomeWork > 0):?>
                        <h4 class="add-home-work-title"><?=System::Lang('ANSWER');?></h4>

                        <div class="round-block error-alert warning-block <?= empty($_GET['error']) ? "hidden" : "" ?>">
                            <div class="error-alert-text">Ошибка. <span class="homework-error-text"><?= $_GET['error'] ?? "" ?></span></div>
                        </div>

                        <?php if($task && $task['show_work_link']):?>
                            <div class="add-home-work-line">
                                <div class="add-home-work-left"><?=System::Lang('LINK');?></div>

                                <div class="add-home-work-right">
                                    <input name="work_link" type="text" placeholder="<?=System::Lang('ENTER_LINK');?>">
                                </div>
                            </div>
                        <?php endif;?>

                        <div class="add-home-work-line">
                            <div class="add-home-work-left"><?=System::Lang('TEXT');?></div>
                            <div class="add-home-work-right">
                                <textarea placeholder="<?=System::Lang('ENTER_ANSWER');?>" name="answer" class="editor"><?php //System::sessionFlush("homework_last_text"); ?> </textarea>

                                <?if($task['show_upload_file']):?>
                                    <div class="attach home-work-attach">
                                        <input type="file" data-browse="<?=System::Lang('UPLOAD_FILE');?>" multiple name="lesson_attach[]">
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    <?php else:?>
                        <div class="add-home-work-submit z-1">
                             <button type="submit" name="complete" class="button btn-orange btn-green btn-green--big"><?=System::Lang('MARK_PASSED');?></button>
                        </div>
                    <?php endif;
                elseif($task_check_type !=0 && TrainingLesson::isAllowSendComment($training, $lesson_homework_status)):?>
                    <div class="answer" id="user_comment">
                        <h4 class="add-comment-title"><?=System::Lang('COMMENT');?></h4>
                        <div class="add-comment-line">
                            <div class="add-comment-bottom">
                                <textarea class="editor" name="answer" required="" placeholder="<?=System::Lang('LEAVE_A_COMMENT');?>" id="commentTextInput"></textarea>
                                <?php if($task['show_upload_file']):?>
                                    <div class="attach home-work-attach">
                                        <input type="file" data-browse="<?=System::Lang('UPLOAD_FILE');?>" multiple name="lesson_attach[]" id="commentfileInput">
                                    </div>

                                <script>
                                    //Скрипт
                                    let commentTextInput = document.getElementById('commentTextInput');
                                    let commentfileInput = document.getElementById('commentfileInput');

                                    commentfileInput.addEventListener('change', function(){
                                        if( this.value ){
                                            commentTextInput.removeAttribute('required');
                                        } else {
                                            commentTextInput.setAttribute('required', 'required');
                                        }
                                    });

                                </script>

                                <?php endif;?>
                            </div>
                        </div>

                        <div class="add-home-work-submit z-1 add-home-work--simple">
                            <button type="submit" name="comment" class="button btn-orange btn-green btn-green--big"><?=System::Lang('SEND');?></button>
                        </div>
                    </div>
                <?php endif;

                if($task['check_type'] != 0 && ((!$lesson_homework_status && $levelAccessTypeHomeWork > 0) || $lesson_homework_status == TrainingLesson::HOMEWORK_DECLINE || !$hisory_id)):?>
                    <div class="add-home-work-submit z-1 add-home-work--simple">
                        <?if(TrainingPublicHomework::isAllowMakePublic($public_homework_settings)):?>
                            <div class="homework_is_public-wrap"><label class="check_label" style="width: 100%;">
                                <input type="checkbox" name="homework_is_public">
                                <span><?=System::Lang('PUBLIC_HOME_TASK');?></span></label>
                            </div>
                        <?endif;?>

                        <button type="submit" name="complete" class="button btn-orange btn-green btn-green--big"><?=System::Lang('SEND');?></button>
                    </div>
                <?php endif;?>
            </div>
        </div>

        <input type="hidden" name="is_allow_submit_homework" value="<?=$task['task_type'] == 2 && (!$homework || !$homework['test']) ? 0 : 1;?>">
    </form>

    <div id="block_edit_answer" class="block_edit_answer uk-animation-scale-up"></div>
    <div id="block_edit_comment" class="block_edit_comment uk-animation-scale-up"></div>
</div>