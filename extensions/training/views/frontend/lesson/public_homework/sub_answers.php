<?defined('BILLINGMASTER') or die;

foreach($sub_answers as $sub_answer):
    $user_sub_answer = User::getUserNameByID($sub_answer['user_id']);?>
    <div class="answer_item answer" id="tr_comment_<?=$sub_answer['comment_id'];?>">
        <div class="answer_item__inner">
            <div class="answer_item__left">
                <img src="<?=User::getAvatarUrl($user_sub_answer, $settings);?>" alt="" />
            </div>

            <div class="answer_item__right">
                <div class="user_name"<?if($public_homework['curator_id'] == $sub_answer['user_id']) echo ' data-user_type="curator"';?>>
                    <p class="user_name_text"><?=$sub_answer['user_name'];?></p>
                    <span class="small"><span style="display: none;"># <?=$sub_answer['comment_id'];?></span><?=date("d.m.Y H:i:s", $sub_answer['create_date']);?></span>
                </div>


                <div class="user_message">
                    <?if($sub_answer['status'] != 3):?>
                        <?=html_entity_decode(base64_decode($sub_answer['comment_text']));?>
                    <?else:?>
                        <p><?=System::Lang('MESSEGE_DELITED');?></p>
                    <?endif;?>
                </div>

                <?if(!empty($sub_answer['attach']) && $sub_answer['status'] != 3):
                    $result = Training::sortFilesToDocumentsAndPhotos(json_decode($sub_answer['attach'], true), 18);
                    $image_files = $result['images'];
                    $other_files = $result['otherFiles'];?>

                    <div class="attach mt-5">
                        <div class="list-questions__file">
                            <?if($result['images']):?>
                                <div class="list-modal-images-wrap">
                                    <?foreach($result['images'] as $attach):?>
                                        <div class="modal-image-wrap width-33">
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
                <?endif;?>
            </div>
        </div>
    </div>
<?endforeach;?>
