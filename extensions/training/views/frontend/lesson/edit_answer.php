<?php defined('BILLINGMASTER') or die;?>

<form enctype="multipart/form-data" class="form-complete" action="/training/answer/edit" method="POST" id="edit_answer_form">
    <input type="hidden" name="answer_id" value="<?=$answer['homework_id'];?>">
    <input type="hidden" name="current_attach" value="<?=$answer['attach'];?>">
    <input type="hidden" name="token" value="<?=isset($_SESSION['user_token']) ? $_SESSION['user_token'] : '';?>">

    <div class="block-border-top">
        <div class="add-home-work">
            <h4 class="add-home-work-title"><?=System::Lang('EDIT_ANSWER');?></h4>
                <?php if($task && $task['show_work_link']):?>
                    <div class="add-home-work-line">
                        <div class="add-home-work-left"><?=System::Lang('LINK');?></div>
                        <div class="add-home-work-right">
                            <?php if(isset($answer['work_link'])):?>
                                <input name="work_link" type="text" placeholder="Вставьте ссылку" value="<?=$answer['work_link']?>">
                            <?php else:?>
                                <input name="work_link" type="text" placeholder="Вставьте ссылку">
                            <?php endif;?>
                        </div>
                    </div>
                <?php endif;?>
            <div class="add-home-work-line">
                <div class="add-home-work-left"><?=System::Lang('TEXT');?></div>

                <div class="add-home-work-right">
                    <textarea class="editor" name="answer" id="training-answer-edit" required="required"><?=base64_decode($answer['answer']);?></textarea>

                    <?$answer['attach'] = $answer['attach'] ? json_decode($answer['attach'], true) : null;
                    if($answer['attach'] && is_array($answer['attach'])):?>
                        <div style="display: flex; flex-wrap: wrap;">
                            <?foreach ($answer['attach'] as $attach):?>
                                <div class="attach mt-5">
                                    <div class="list-questions__file">
                                        <a href="/load/hometask/?name=<?=urldecode($attach['name']); ?>&history_id=<?=$answer['history_id']?>"
                                           target="_blank" download>
                                            <i class="icon-attach-1" style="font-size: 20px;"></i>
                                            <span class="answer_attach_name"><?= $attach['name']; ?><span class="icon-remove" data-attach_name="<?=$attach['name'];?>"></span></span>
                                        </a>
                                    </div>
                                </div>
                            <?endforeach;?>
                        </div>

                        <input type="hidden" name="del_attach">
                    <?endif;

                    if($task['show_upload_file']):?>
                        <div class="attach home-work-attach">
                            <input type="file" id="commentfileInput_edit" data-browse="<?=System::Lang('UPLOAD_FILE');?>" multiple name="lesson_attach[]">
                        </div>
                    <?php endif;?>
                </div>
            </div>

            <div class="add-home-work-submit z-1 add-home-work--simple">
                <button type="submit" name="edit_answer" class="button btn-orange btn-green btn-green--big"><?=System::Lang('SAVE');?></button>
            </div>
        </div>
    </div>
</form>
<script>
    //Скрипт
    let commentTextInput = document.getElementById('training-answer-edit');
    let commentfileInput = document.getElementById('commentfileInput_edit');

    if (commentfileInput) {
        commentfileInput.addEventListener('change', function () {
            if (this.value) {
                commentTextInput.removeAttribute('required');
            } else {
                commentTextInput.setAttribute('required', 'required');
            }
        });
    }

</script>
<script type="text/javascript">
  $(function(){
    <?php if($settings['editor'] == 1):?>
      editor_transfiguration($("textarea.editor"));
    <?php else:?>
      editor_transfiguration('training-answer-edit');
    <?php endif;?>
  });
</script>