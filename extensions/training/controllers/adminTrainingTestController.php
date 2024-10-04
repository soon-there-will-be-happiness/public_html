<?php defined('BILLINGMASTER') or die;


class adminTrainingTestController  extends AdminBase
{

    protected $setting;
    protected $tr_settings;
    protected $admin_name;
    protected $user_type;
    protected $resp;

    public function __construct()
    {
        $this->setting = System::getSetting();
        $this->tr_settings = Training::getSettings();
        $this->admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : null;
        $this->resp = ['status' => false];
    }


    /**
     * СОХРАНИТЬ ВОПРОС ТЕСТА
     * @param $training_id
     * @param $lesson_id
     */
    public function actionSaveQuest($training_id, $lesson_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_courses'])) {
            exit(json_encode($this->resp));
        }

        if (isset($_POST['save_quest']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) { // изменить вопрос с ответами
            $quest_id = (int)$_POST['question_id'];
            $question_title = htmlentities($_POST['quest']['name']);
            $help = htmlentities($_POST['quest']['help']);
            $true_answer = intval($_POST['quest']['true_answer']);
            $require_all_true = intval($_POST['quest']['require_all_true']);
            $cover = $_POST['quest']['cover'];
            $right_answer = htmlentities(trim($_POST['quest']['right_answer']));
            $right_answer_points = (int)$_POST['quest']['right_answer_points'];
            $check_mode = (int)$_POST['quest']['check_mode'];

            if ($quest_id) { // редактирование вопроса
                $quest_points = (int)$_POST['quest']['points'];

                $save = TrainingTest::editQuestion($quest_id, $question_title, $help, $true_answer,
                    $require_all_true, $cover, $check_mode, $quest_points
                );
                $question = TrainingTest::getQuestion($quest_id);
                $question_type = $question['question_type'];

                if ($save && $question_type == TrainingTest::QUESTION_TYPE_OWN_ANSWER) { // свой ответ
                    TrainingTest::updAnswersByQuestId($quest_id, $right_answer, $right_answer, 1, $right_answer_points);
                }
            } else { // добавление вопроса
                $question_type = (int)$_POST['quest']['question_type'];
                $test = TrainingLesson::getTest2Lesson($lesson_id);
                $sort = TrainingTest::getFreeSort2Question($lesson_id);
                $save = $quest_id = TrainingTest::addQuestion($test['test_id'], $question_title, $question_type,
                    $help, $true_answer, $require_all_true, $sort, $cover, $check_mode
                );

                if ($quest_id && $question_type == TrainingTest::QUESTION_TYPE_OWN_ANSWER) { // свой ответ
                    $save = TrainingTest::addAnswer($quest_id, $right_answer, $right_answer, 1, $right_answer_points, 1);
                }
            }

            if ($save && isset($_POST['answers'])) {
                foreach ($_POST['answers'] as $answer) {
                    $option_id = intval($answer['option_id']);
                    $title = htmlentities($answer['title']);
                    $value = System::Translit($title);
                    $valid = isset($answer['valid']) ? intval($answer['valid']) : false;
                    $points = isset($answer['points']) ? intval($answer['points']) : 0;
                    $cover = $answer['cover'];

                    $upd = TrainingTest::updAnswer($option_id, $title, $value, $valid, $points, $cover);
                    if (!$upd) {
                        $save = false;
                    }
                }
            }

            $anchor = !$_POST['question_id'] ? "#test_question_$quest_id" : null;
            System::redirectUrl("/admin/training/editlesson/$training_id/$lesson_id", $save, $anchor);
        }
    }


    /**
     * УДАЛИТЬ ВОПРОС У ТЕСТА
     * @param $training_id
     * @param $lesson_id
     * @param $quest_id
     */
    public function actionDelQuest($training_id, $lesson_id, $quest_id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_courses'])) {
            System::redirectUrl('/admin');
        }

        $training_id = intval($training_id);
        $lesson_id = intval($lesson_id);

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = TrainingTest::delQuestion($quest_id);
            System::redirectUrl("/admin/training/editlesson/$training_id/$lesson_id", $del);
        }
    }


    /**
     * ДОБАВИТЬ ОТВЕТ К ТЕСТУ
     * @param $training_id
     * @param $lesson_id
     * @param $quest_id
     */
    public function actionAddAnswer($training_id, $lesson_id, $quest_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_courses'])) {
            System::redirectUrl('/admin');
        }

        if (isset($_POST['add_answer']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) { // добавить ответ к вопросу
            $title = htmlentities($_POST['title']);
            $value = System::Translit($title);
            $valid = intval($_POST['valid']);
            $points = intval($_POST['points']);
            $sort = TrainingTest::getFreeSort2QuestionOption($quest_id);
            $cover = $_POST['cover'];
            $this->resp['status'] = TrainingTest::addAnswer($quest_id, $title, $value, $valid, $points, $sort, $cover);

            if (isset($_POST['show_form'])) {
                $this->resp['show_modal_form'] = $_POST['show_form'];
                $this->resp['modal_form_url'] = "/admin/trainingajax/testquestionform?quest_id=$quest_id&lesson_id=$lesson_id&training_id=$training_id&token={$_POST['token']}";
            }
        }

        exit(json_encode($this->resp));
    }


    /**
     * УДАЛИТЬ ОТВЕТ У ТЕСТА
     * @param $training_id
     * @param $lesson_id
     * @param $quest_id
     */
    public function actionDelAnswer($training_id, $lesson_id, $quest_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['del_courses'])) {
            header("Content-type: application/json; charset=utf-8");
            exit(json_encode($this->resp));
        }

        if (isset($_POST['id']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) { //удалить ответ для вопроса
            $option_id = (int)$_POST['id'];
            $option = TrainingTest::getOption($option_id);
            if (!$option) {
                header("Content-type: application/json; charset=utf-8");
                exit(json_encode($this->resp));
            }

            $del = TrainingTest::deleteAnswer($option_id);
            if ($del) {
                $options = TrainingTest::getOptionsByQuest($option['quest_id']);
                require_once (ROOT . '/extensions/training/views/admin/lesson/tests/list_answers.php');
            } else {
                header("Content-type: application/json; charset=utf-8");
                echo json_encode($this->resp);
            }
        }
    }
}