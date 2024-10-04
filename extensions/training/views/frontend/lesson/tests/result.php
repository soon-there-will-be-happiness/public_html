<?defined('BILLINGMASTER') or die;
$count_passing_test = TrainingTest::getCountPassingTest($this->test['test_id'], $this->user_id);
$count_more_test_try = $this->test['test_try'] - $count_passing_test;?>

<h3 id="test_go" class="test-go"><?=System::Lang('TEST');?></h3>
<?if($this->homework['test'] == 0 && $this->test_expired):?>
    <p><?=System::Lang('TIME_OVER');?></p>
    <?if($count_more_test_try > 0):?>
        <form class="start-test-form" action="/training/lesson/test/start" method="POST">
            <input type="hidden" name="lesson_id" value="<?=$this->lesson['lesson_id'];?>">
            <input type="hidden" name="test_id" value="<?=$this->test['test_id'];?>">
            <input type="submit" name="go_test" class="btn-green btn-green--big" value="Попробовать еще раз">
        </form>
    <?endif;
else:
    $passing_time = $test_result['date'] - $this->homework['test_start'];
    $mk_passing_time = mktime( 0, 0, $passing_time);

    $passing_time_d = intval($passing_time / 86400);
    $passing_time_h = (int)date('G', $mk_passing_time);
    $passing_time_m = (int)date('i', $mk_passing_time);
    $passing_time_s = (int)date('s', $mk_passing_time);
    $show_passing_time = ($passing_time_d ? "$passing_time_d д." : '') . ($passing_time_h ? " $passing_time_h ч." : '') . ($passing_time_m ? " $passing_time_m мин." : '') . " $passing_time_s сек.";?>

    <div id="test_status" class="test-status <?=$this->homework['test'] == 1 ? 'test-final' : 'test-not-done'.($count_more_test_try < 1 ? ' red' : '');?>">
        <i class="<?=$this->homework['test'] == 1 ? 'icon-check' : 'icon-stop';?>"></i><?=TrainingTest::getStatusText($this->homework['test']);?>
    </div>

    <div class="modal-test-result">
        <p><strong><?=System::Lang('POINT_RESULTS');?> <?=$test_result['sum_points'];?></strong></p>
        <p><?=System::Lang('CORRECT_ANSWER');?> <?="{$test_result['sum_valid']} из {$this->show_questions_count}";?></p>
        <p><?=System::Lang('TIME');?> <?=$show_passing_time;?></p>
        <p><?=System::Lang('ATTEMPT');?> <?="$count_passing_test из {$this->test['test_try']}";?></p>
    </div>

    <?if($this->homework['test'] == 1 && $this->test['help_hint_success'] || ($this->homework['test'] == 2 && $this->test['help_hint_fail'])):?>
        <div class="decryption-spoiler test-result-decoding">
            <div class="decryption-spoiler-title">
                <span><?=System::Lang('SHOW_TRANSCRIPT');?></span><i class="icon-down"></i>
            </div>

            <div class="decryption-spoiler-content" style="display: none;">
                <table class="table-test-answers">
                    <thead>
                        <tr>
                            <td><?=System::Lang('QUESTION');?></td>
                            <?if($this->test['help_hint_fail'] != 2):?>
                                <td width="200"><?=System::Lang('CORRECT_ANSWERED');?></td>
                            <?endif;?>
                            <td width="200">Ваш ответ</td>
                            <td class="text-right"><?=System::Lang('POINTS');?></td>
                        </tr>
                    </thead>

                    <tbody>
                        <?$detail_test_result = TrainingTest::getDetailedTestResult($this->lesson_id, $this->user_id, true);
                        if ($detail_test_result):
                            $prev_question_id = null;
                            foreach($detail_test_result as $result):
                                if ($this->test['help_hint_fail'] == 2 && $result['is_valid']) {
                                    continue;
                                }?>
                                <tr>
                                    <td>
                                        <?if(!$prev_question_id || $prev_question_id != $result['quest_id']):?>
                                            <span class="result-item-question"><?=$result['question'];?></span>
                                            <?if($result['help']):?>
                                                <span class="result-item-icon" data-uk-tooltip="" title="<?=$result['help'];?>">
                                                    <i class="icon-answer"></i>
                                                </span>
                                            <?endif;
                                        endif;

                                        if($result['image']):?>
                                            <div class="result-item-cover">
                                                <img src="<?=$result['image']?>" alt="" />
                                            </div>
                                        <?endif;?>
                                    </td>

                                    <?if($this->test['help_hint_fail'] != 2):?>
                                        <td>
                                            <div class="result-item">
                                                <span class="result-point result-green"></span>

                                                <?if($result['question_type'] == TrainingTest::QUESTION_TYPE_ARRANGE): // по порядку
                                                    $result['result'] = json_decode($result['result'], true);
                                                    $options = TrainingTest::getOptionsByQuest($result['quest_id']);
                                                    if($options):?>
                                                        <div class="result-sub-item-list">
                                                            <?foreach($options as $option):
                                                                if ($result['result'] && !in_array($option['option_id'], $result['result'])) {
                                                                    continue;
                                                                }?>
                                                                <div class="result-sub-item">
                                                                    <?if($option['cover']):?>
                                                                        <span class="result-sub-item-cover">
                                                                            <img src="<?=$option['cover']?>" alt="" />
                                                                        </span>
                                                                    <?endif;?>
                                                                    <span class="result-sub-item-title"><?=$option['title'];?></span>
                                                                </div>
                                                            <?endforeach;?>
                                                        </div>
                                                    <?endif;
                                                else:
                                                    if($result['cover_quest']):?>
                                                        <div class="result-item-cover__question">
                                                            <img src="<?=$result['cover_quest']?>" alt="" />
                                                        </div>
                                                    <?else:?>
                                                        <div class="result-item-inner"><?=str_replace(',', ',<br>', $result['title']);?></div>
                                                    <?endif;
                                                endif;?>
                                            </div>
                                        </td>
                                    <?endif;?>

                                    <td>
                                        <div class="result-item">
                                            <span class="result-point result-<?=$result['is_valid'] ? 'green' : 'red';?>"></span>

                                            <?if($result['question_type'] == TrainingTest::QUESTION_TYPE_ARRANGE): // по порядку?>
                                                <div class="result-sub-item-list">
                                                    <?$result['result'] = !is_array($result['result']) ? json_decode($result['result'], true) : $result['result'];
                                                    if($result['result']):
                                                        foreach($result['result'] as $option_id):
                                                            $option = TrainingTest::getOption((int)$option_id);?>
                                                            <div class="result-sub-item">
                                                                <?if($option && $option['cover']):?>
                                                                    <span class="result-sub-item-cover">
                                                                        <img src="<?=$option['cover']?>" alt="" />
                                                                    </span>
                                                                <?endif;?>
                                                                <span class="result-sub-item-title"><?=$option['title'];?></span>
                                                            </div>
                                                        <?endforeach;
                                                    endif;?>
                                                </div>
                                            <?else:
                                                if($result['cover_answer']):?>
                                                    <span class="result-item-cover__answer">
                                                        <img src="<?=$result['cover_answer']?>" alt="" />
                                                    </span>
                                                <?else:?>
                                                    <div class="result-item-wrap">
                                                        <div class="result-item-inner"><?=str_replace(',', ',<br>', $result['result']);?></div>
                                                    </div>
                                                <?endif;
                                            endif;?>
                                        </div>
                                    </td>

                                    <td class="text-right"><?=$result['user_points'];?></td>
                                </tr>
                                <?$prev_question_id = $result['quest_id'];
                            endforeach;
                        endif;?>
                    </tbody>
                </table>
            </div>
        </div>
    <?endif;

    if($this->homework['test'] == 2 && $count_more_test_try < 1 && !$this->test['re_test']):?>
        <p class="text-center test-try-info">
            <strong><?=System::Lang('ATTEMPTS_ENDED');?></strong><br>
            <strong><?=System::Lang('WAIT_RESPONSE');?></strong>
        </p>
    <?endif;

    if(($this->homework['test'] == 2 && $count_passing_test < $this->test['test_try']) || $this->test['re_test']):?>
        <form class="start-test-form" action="/training/lesson/test/start" method="POST">
            <input type="hidden" name="lesson_id" value="<?=$this->lesson['lesson_id'];?>">
            <input type="hidden" name="test_id" value="<?=$this->test['test_id'];?>">
            <input type="submit" name="go_test" class="btn-green btn-green--big" value="Попробовать еще раз">
        </form>
    <?endif;
endif;

if($this->homework['test'] == 1 && TrainingLesson::isLessonComplete($this->lesson['lesson_id'], $this->user_id)):?>
    <script>
        $('.lesson-inner .next_less_next').removeClass('hidden');
    </script>
<?endif;?>
