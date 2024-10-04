<?defined('BILLINGMASTER') or die;?>

<h3 id="test_go" class="test-go"><?=System::Lang('TEST');?></h3>

<div id="test_status" class="test-status test-process">
    <i class="icon-stopwatch"></i><?=System::Lang('IN_PROCESS');?>
</div>

<form class="test-form" action="/training/lesson/test/complete" method="POST" data-question_type="<?=$question['question_type'];?>">
    <input type="hidden" name="lesson_id" value="<?=$this->lesson_id;?>">
    <input type="hidden" name="question_id" value="<?=$question['quest_id'];?>">

    <?require (__DIR__.'/progressbar.php');?>

    <p class="question-name"><?="{$question['question']}"?></p>

    <?if($question['image'] != null):?>
        <img class="test-one-image" src="<?=$question['image'];?>" alt="">
    <?endif;?>

    <div class="test-answer-row-wrap test-question-type__<?=$question['question_type'];?>">
        <?$input_type = TrainingTest::getOptionInputType($question);
        if ($question['question_type'] == TrainingTest::QUESTION_TYPE_ARRANGE && isset($_SESSION['test_questions']['answers'][$question['quest_id']])) {
            $options_by_ids = TrainingTest::getOptionsByQuest($question['quest_id'], false, true);
            $options = [];

            foreach ($_SESSION['test_questions']['answers'][$question['quest_id']] as $key => $option_id) {
                $options[] = $options_by_ids[$option_id];
            }
        } else {
            $is_rand = $question['question_type'] == TrainingTest::QUESTION_TYPE_ARRANGE ? true : false;
            $options = TrainingTest::getOptionsByQuest($question['quest_id'], false, false, $is_rand);
        }

        if($options):
            foreach($options as $key => $option):?>
                <div class="test-answer-row<?=$option['cover'] != null ? ' with-image' : '';?>">
                    <?if($question['question_type'] == TrainingTest::QUESTION_TYPE_VARIANT):
                        if($option['cover'] != null):?>
                            <img src="<?=$option['cover'];?>" class="test-list-image" alt="">
                        <?endif;?>

                        <label class="custom-<?=$input_type;?>">
                            <?$is_checked = isset($_SESSION['test_questions']['answers'][$question['quest_id']]) && in_array($option['option_id'], $_SESSION['test_questions']['answers'][$question['quest_id']]) ? true : false;
                            if ($input_type == 'checkbox'):?>
                                <input type="<?=$input_type;?>" data-id="<?=$option['option_id'];?>" name="option[<?=$question['quest_id'];?>][]" value="<?=$option['value'];?>"<?if($is_checked) echo ' checked="checked"';?>>
                            <?else:?>
                                <input type="<?=$input_type;?>" data-id="<?=$option['option_id'];?>" name="option[<?=$question['quest_id'];?>]" value="<?=$option['value'];?>"<?if($is_checked) echo ' checked="checked"'; if($key == 0) echo ' required="required"';?>>
                            <?endif;?>

                            <span><b><?=$option['title'];?></b></span>
                        </label>
                    <?elseif($question['question_type'] == TrainingTest::QUESTION_TYPE_OWN_ANSWER): // свой ответ?>
                        <input type="text" data-id="<?=$option['option_id'];?>" name="option[<?=$question['quest_id'];?>]" value="<?=isset($_SESSION['test_questions']['answers'][$question['quest_id']]) ? $_SESSION['test_questions']['answers'][$question['quest_id']] : '';?>" required="required" placeholder="Введите ваш ответ">
                    <?else: // по порядку
                        if($option['cover'] != null):?>
                            <img src="<?=$option['cover'];?>" class="test-list-image" alt="">
                        <?endif;?>
                        <span class="test-answer-title"><?=$option['title'];?></span>
                        <input type="hidden" name="option[<?=$question['quest_id'];?>]" value="<?=$option['sort'];?>" data-id="<?=$option['option_id'];?>">
                    <?endif;?>
                </div>
            <?endforeach;
        endif;?>
    </div>

    <div>
        <div class="test-btn-row">
            <?if ($number_question > 1):?>
                <button class="btn-green btn-test-prev" type="button" id="prevBtn"><i class="icon-prev"></i><?=System::Lang('PREVIOUS');?></button>
            <?endif;

            if ($number_question < $this->show_questions_count):?>
                <button class="btn-green btn-test-next" type="button" id="nextBtn"><?=System::Lang('FURTHER');?><i class="icon-next"></i></button>
            <?elseif($number_question == $this->show_questions_count):?>
                <div class="test-submit">
                    <input type="submit" name="test_complete" id="test_complete" class="button btn-blue-small" value="Завершить тест">
                </div>
            <?endif;?>
        </div>
    </div>
</form>
