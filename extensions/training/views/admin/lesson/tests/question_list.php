<?php defined('BILLINGMASTER') or die;?>

<input type="hidden" name="sort_upd_url" value="/admin/trainingajax/updsorttestquestions">

<?foreach($questions as $question):?>
    <div class="el-edit <?=TrainingTest::getQuestionTypeKey($question['question_type']);?>">
        <div class="add-answer-1">
            <input type="hidden" name="sort_items[]" value="<?=$question['quest_id'];?>" data-type="test-question">
            <a href="javascript:void(0)" id="test_question_<?=$question['quest_id'];?>" class="test-edit" data-url="/admin/trainingajax/testquestionform?quest_id=<?="{$question['quest_id']}&lesson_id=$lesson_id&training_id=$training_id&token={$_SESSION['admin_token']}";?>">
                <i class="button-drag el-icon ui-sortable-handle"></i><?=$question['question'];?>
            </a>
        </div>

        <div class="add-answer-3">
            <a onclick="return confirm('Вы уверены?')" href="/admin/training/test/question/del/<?="$training_id/$lesson_id/{$question['quest_id']}?token={$_SESSION['admin_token']}";?>" title="Удалить">
                <span class="icon-remove"></span>
            </a>
        </div>
    </div>
<?endforeach;


