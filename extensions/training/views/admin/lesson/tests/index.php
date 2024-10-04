<?php defined('BILLINGMASTER') or die;?>

<!-- Добавить/изменить вопрос теста -->
<div id="modal_test_question" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-add-elem">
        <div class="userbox modal-userbox-3">
            <?require_once (__DIR__.'/save_question.php');?>
        </div>
    </div>
</div>

<!-- Добавить ответ -->
<div id="modal_add_answer" class="uk-modal" data-show_modal="modal_edit_element">
    <div class="uk-modal-dialog uk-modal-add-elem">
        <div class="userbox modal-userbox-3"></div>
    </div>
</div>