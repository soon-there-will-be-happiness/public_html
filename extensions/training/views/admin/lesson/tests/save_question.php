<?defined('BILLINGMASTER') or die;
$test_question = isset($test_question) ? $test_question : null;
$test_question_id = $test_question ? $test_question['quest_id'] : null;
$options = $test_question_id ? TrainingTest::getOptionsByQuest($test_question_id) : null;
$id_prefix = !$test_question_id ? 'add_' : 'edit_'?>

<form action="/admin/training/test/question/save/<?="$training_id/$lesson_id";?>"<?if(!$test_question_id) echo ' data-ajax_upd_block="#tests-settings .el-edit-list .sortable_box"';?> id="<?=$id_prefix?>test_question_form" method="POST" enctype="multipart/form-data">
    <div class="modal-admin_top">
        <h3 class="modal-traning-title"><?=$test_question_id ? 'Редактировать вопрос' : 'Добавить вопрос';?></h3>
        <ul class="modal-nav_button">
            <li><input type="submit" name="save_quest" value="Сохранить" class="button save button-green"></li>
            <li class="modal-nav_button__last">
                <a class="button uk-modal-close uk-close modal-nav_button__close" href="#close"><i class="icon-close"></i></a>
            </li>
        </ul>
    </div>

    <div class="admin_form">
        <div class="admin_message hidden">Успешно!</div>

        <div class="row-line">
            <div class="col-1-1 mb-0">
                <h4>Вопрос</h4>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Вопрос</label>
                    <input type="text" name="quest[name]" value="<?=$test_question ? $test_question['question'] : '';?>" required="required">
                </div>
            </div>


            <div class="col-1-2">
                <div class="width-100"><label>Изображение</label>
                    <input id="<?=$id_prefix?>test_question_img-<?=$test_question_id;?>" type="text" name="quest[cover]" value="<?=$test_question ? $test_question['image'] : '';?>">
                    <a href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=<?=$id_prefix?>test_question_img-<?=$test_question_id;?>&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" class="btn iframe-btn" type="button">Выбрать изображение</a>
                </div>

                <?if($test_question && $test_question['image']):?>
                    <div class="width-100">
                        <img src="<?=$test_question['image']?>" alt="" width="140">
                    </div>
                <?endif;?>
            </div>

            <div class="col-1-1">
                <div class="width-100 test-question-type<?if($test_question) echo ' disabled';?>"><label>Тип вопроса:</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio question-type-variant<?if(!$test_question || $test_question['question_type'] == TrainingTest::QUESTION_TYPE_VARIANT) echo ' active';?>"><input name="quest[question_type]" type="radio" value="<?=TrainingTest::QUESTION_TYPE_VARIANT;?>"<?if(!$test_question || $test_question['question_type'] == TrainingTest::QUESTION_TYPE_VARIANT) echo ' checked="checked"';?><?if($test_question) echo ' disabled="disabled"';?> data-show_on="<?=$id_prefix?>test_question_settings"><span>Выбор варианта</span></label>
                        <label class="custom-radio question-type-own_answer<?if($test_question && $test_question['question_type'] == TrainingTest::QUESTION_TYPE_OWN_ANSWER) echo ' active';?>"><input name="quest[question_type]" type="radio" value="<?=TrainingTest::QUESTION_TYPE_OWN_ANSWER;?>"<?if($test_question && $test_question['question_type'] == TrainingTest::QUESTION_TYPE_OWN_ANSWER) echo ' checked="checked"';?><?if($test_question) echo ' disabled="disabled"';?> data-show_on="<?=$id_prefix?>test_quest_right_answer_settings" data-show_off="<?=$id_prefix?>test_quest_answer_settings"><span>Свой ответ</span></label>
                        <label class="custom-radio question-type-arrange<?if($test_question && $test_question['question_type'] == TrainingTest::QUESTION_TYPE_ARRANGE) echo ' active';?>"><input name="quest[question_type]" type="radio" value="<?=TrainingTest::QUESTION_TYPE_ARRANGE;?>"<?if($test_question && $test_question['question_type'] == TrainingTest::QUESTION_TYPE_ARRANGE) echo ' checked="checked"';?><?if($test_question) echo ' disabled="disabled"';?>><span>По порядку</span></label>
                    </span>
                </div>
            </div>

            <div class="col-1-1">
                <div class="width-100"><label>Пояснение для расшифровки</label>
                    <textarea name="quest[help]" placeholder="Выводится после окончания теста"><?=$test_question ? $test_question['help'] : '';?></textarea>
                </div>
            </div>
        </div>

        <div class="row-line hidden mt-20" id="<?=$id_prefix?>test_question_settings">
            <div class="col-1-2">
                <div class="width-100"><label>Правильный ответ</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="quest[true_answer]" type="radio" value="1"<?if(!$test_question || $test_question['true_answer'] == 1) echo ' checked="checked"';?>><span>один</span></label>
                        <label class="custom-radio"><input name="quest[true_answer]" type="radio" value="2"<?if($test_question && $test_question['true_answer'] == 2) echo ' checked="checked"';?>><span class="not-red">несколько</span></label>
                    </span>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Обязательно выбрать все правильные</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="quest[require_all_true]" type="radio" value="1"<?if(!$test_question || $test_question['require_all_true'] == 1) echo ' checked="checked"';?>><span>да</span></label>
                        <label class="custom-radio"><input name="quest[require_all_true]" type="radio" value="0"<?if($test_question && $test_question['require_all_true'] == 0) echo ' checked="checked"';?>><span>нет</span></label>
                    </span>
                </div>
            </div>
        </div>

        <?if($test_question):?>
            <div class="row-line mt-20" id="<?=$id_prefix?>test_quest_answer_settings">
                <div class="col-1-1 mb-0">
                    <h4>Ответ</h4>
                </div>

                <div class="col-1-1">
                    <div class="width-100">
                        <a href="javascript:void(0)" class="button button-green test-edit" data-url="/admin/trainingajax/testanswerform?quest_id=<?="$test_question_id&lesson_id=$lesson_id&training_id=$training_id";?>" data-modal_id="modal_add_answer" data-questin_id="<?=$test_question_id;?>"><i class="el-icon"></i>Добавить ответ</a>
                    </div>

                    <?if($test_question && $test_question['question_type'] != TrainingTest::QUESTION_TYPE_OWN_ANSWER && $options):
                        if ($test_question['question_type'] == TrainingTest::QUESTION_TYPE_VARIANT):
                            require_once(__DIR__ . '/list_answers.php');
                        else:
                            require_once(__DIR__ . '/list_sort_answers.php');?>

                            <div class="width-100 mt-20"><label>Кол-во баллов за правильную расстановку</label>
                                <div class="min-label-wrap" style="width: 100px;">
                                    <label class="mb-0">
                                        <span class="min-label">б.</span>
                                    </label>
                                    <input type="text" value="<?=$test_question['points'];?>" name="quest[points]">
                                </div>
                            </div>

                            <div class="test-own-answer-instruction">Расставьте ответы в правильном порядке для проверки.</div>
                        <?endif;
                    endif;?>
                </div>
            </div>
        <?endif;?>

        <div class="row-line hidden mt-20" id="<?=$id_prefix?>test_quest_right_answer_settings">
            <div class="col-1-1">
                <div class="width-100"><label>Режим проверки:</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="quest[check_mode]" type="radio" value="1"<?if(!$test_question || $test_question['check_mode'] == 1) echo ' checked="checked"';?>><span>Жестко (учитывать регистр)</span></label>
                        <label class="custom-radio"><input name="quest[check_mode]" type="radio" value="2"<?if($test_question && $test_question['check_mode'] == 2) echo ' checked="checked"';?>><span class="not-red">Мягко (не учитывать регистр и лишние пробелы)</span></label>
                    </span>
                </div>
            </div>

            <div class="col-1-1 mb-0">
                <h4>Ответ</h4>
            </div>

            <div class="col-2-3">
                <div class="width-100"><label>Правильный ответ</label>
                    <input type="text" name="quest[right_answer]" value="<?=$options ? $options[0]['value'] : '';?>">
                </div>
            </div>

            <div class="col-1-4">
                <div class="width-100 min-label-wrap">
                    <label>&nbsp;
                        <span class="min-label">б.</span>
                    </label>
                    <input type="text" value="<?=$options ? $options[0]['points'] : '';?>" name="quest[right_answer_points]">
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="question_id" value="<?=$test_question_id;?>">
    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
</form>

<script src="/extensions/training/web/admin/js/test_question.js?v=<?=CURR_VER;?>"></script>