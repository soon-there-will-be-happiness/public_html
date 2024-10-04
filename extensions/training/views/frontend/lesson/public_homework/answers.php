<?defined('BILLINGMASTER') or die;

$answer_list = TrainingLesson::getAnswers2Lesson($lesson['lesson_id'], $public_homework['user_id'], $public_homework['homework_id']);
if($answer_list):?>
    <div class="answer_list public-homework">
        <?foreach($answer_list as $key => $answer):
            $user_answer = User::getUserNameByID($answer['user_id']);
            $lesson_complete_status = Traininglesson::getLessonCompleteStatus($lesson['lesson_id'], $answer['user_id']);
            $lesson_homework_status = Traininglesson::getHomeworkStatus($lesson['lesson_id'], $answer['user_id']);?>

            <div class="answer_item answer_question">
                <div class="answer_item__inner">
                    <div class="answer_item__left">
                        <img src="<?=User::getAvatarUrl($user_answer, $this->settings);?>" alt="" />
                    </div>

                    <div class="answer_item__right">
                        <div class="answer_item__right_top">
                            <div class="user_name">
                                <p><?=$answer['user_name'];?><span class="user_name-id" style="display: none;">(# <?=$answer['homework_id'];?>)</span></p>
                                <span class="small"><?=date("d.m.Y H:i:s",$answer['date_user_send']);?></span>
                            </div>

                            <div class="homework_status">
                                <?require(__DIR__ . '/../../layouts/homework_status.php');?>
                            </div>
                        </div>

                        <div class="user_message">
                            <?=html_entity_decode(base64_decode($answer['answer']));?>
                        </div>

                        <?if(!empty($answer['work_link'])):?>
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
                        <?endif;?>

                        <div class="answer-item__bottom">
                            <div class="answer-item-bottom__left"></div>
                            <div class="answer-item-bottom__right">
                                <div class="reply_btn_wrap">
                                    <a class="reply_btn" href="javascript:void(0)" data-homework_id="<?=$answer['homework_id'];?>" data-scroll_to="#add_user_comment">Ответить</a>
                                </div>

                                <div class="like_btn_wrap<?if(TrainingHomework::getCountLikes2Homework($answer['homework_id'], $user_id)) echo ' like';?>">
                                    <a class="like_btn" href="javascript:void(0)" data-homework_id="<?=$answer['homework_id'];?>" data-user_id="<?=$answer['user_id'];?>"></a>
                                    <span class="like_count"><?=($count_likes = TrainingHomework::getCountLikes2Homework($answer['homework_id'])) ? $count_likes : '';?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?if($count_comments = TrainingLesson::getCountCommentsByHomeworkID($answer['homework_id'])):?>
                <div class="sub_answer_list-list" id="tr_homework_list">
                    <?$curators = $answer['curator_id'] ? $answer['curator_id'] : null;
                    if (!$curators) {
                        $curators = Training::getCuratorsTraining($training['training_id']);
                        if ($curators) {
                            $curators = array_merge($curators['datamaster'], $curators['datacurators']);
                        }
                    }
                    if($curators && TrainingLesson::getCountCommentsByHomeworkID($answer['homework_id'], $curators)):
                        $sub_answers = TrainingLesson::getCommentsByHomeworkID($answer['homework_id'], $curators, 1);
                        require (__DIR__.'/sub_answers.php');?>
                    <?endif;

                    if($count_comments > 1):?>
                        <a class="btn-show-sub-answers" href="javascript:void(0)" data-homework_id="<?=$answer['homework_id'];?>">Показать ответы</a>
                    <?endif;?>
                </div>
            <?endif;?>

            <div class="add-user-comment-wrap"></div>
        <?endforeach;?>
    </div>
<?endif;