<?php defined('BILLINGMASTER') or die;


class TrainingTest {

    use ResultMessage;


    // TODO Перд релизом, после тщательного тестирования проверить не использованые константы через глобальный поиск
    // и удалить не используемые
    CONST ELEMENT_TYPE_MEDIA = 1;
    CONST ELEMENT_TYPE_PLAYLIST = 2;
    CONST ELEMENT_TYPE_TEXT = 3;
    CONST ELEMENT_TYPE_ATTACH = 4;
    CONST ELEMENT_TYPE_HTML = 5;

    CONST ELEMENT_TEXT_TYPE_TEXT = 1;
    CONST ELEMENT_TEXT_TYPE_ACCORDEON = 2;
    CONST ELEMENT_TEXT_TYPE_POPAP = 3;

    const MSG_TYPE_ANSWER = 1;
    const MSG_TYPE_COMMENT = 2;

    /// Статусы в таблице training_user_map
    const LESSON_STARTED = 0;
    const HOMEWORK_SUBMITTED = 1;
    const HOMEWORK_DECLINE = 2;
    const HOMEWORK_ACCEPTED = 3;
    const HOMEWORK_AUTOCHECK = 4;

    /// Статусы в таблице training_home_work
    const HOME_WORK_ACCEPTED = 1;
    const HOME_WORK_DECLINE = 2;
    const HOME_WORK_IN_VERIFICATION = 3;
    const HOME_WORK_SEND = 4;

    /// TODO нужно добавить(переделать старые) новые константы под новые таблицы
    /// пока совосем старые не удаляем, только добавляем новые!!!
    const ANSWER_IS_NOT_READ = 0;
    const ANSWER_IS_READ = 1;
    const ANSWER_IS_ANSWERED = 2;

    

    const HOMEWORK_ACCESS_FREE = 0; // ТИП ДОСТУПА - СВОБОДНЫЙ
    const HOMEWORK_ACCESS_TO_GROUP = 1; // ТИП ДОСТУПА - ПО ГРУППЕ
    const HOMEWORK_ACCESS_TO_SUBS = 2; // ТИП ДОСТУПА - ПО ПОДПИСКЕ

    const QUESTION_TYPE_VARIANT = 1;
    const QUESTION_TYPE_OWN_ANSWER = 2;
    const QUESTION_TYPE_ARRANGE = 3;



    /**
     * ОБРАБОТАТЬ ДАННЫЕ ТЕСТА ПЕРЕД СОХРАНЕНИЕМ ДЛЯ АДМИНКИ
     * @param $data
     * @return mixed
     */
    public static function beforeSaveData2Admin($data) {
        $fields = [
            'safe' => [],
            'int' => [
                'test_finish', 'test_try', 'test_time', 'show_questions_count', 'help_hint_success',
                're_test', 'is_random_questions', 'help_hint_fail', 'auto_start'
            ],
            'str' => ['test_desc'],
            'date' => [],
            'json' => [],
        ];

        return Training::beforeSaveData2Admin($fields, $data);
    }


    /**
     * ЗАПИСАТЬ ВРЕМЯ НАЧАЛА ПРОХОЖДЕНИЯ ТЕСТА
     * проверяет наличие записи в БД, если есть обновляет, если нет, записывает
     * @param $task_id
     * @param $lesson_id
     * @param $user_id
     * @param int $curator_id
     * @param int $status
     * @param null $create_date
     * @param int $public
     * @param int $mark
     * @param int $points
     * @param null $test
     * @param int $test_start
     * @return bool
     */
    public static function writeStartTest($task_id, $lesson_id, $user_id, $curator_id = 0, $status = 0, $create_date = null,
                                          $public = 0, $mark = 0, $points = 0, $test = null, $test_start = 0)
    {
        $db = Db::getConnection();
        $time = time();
        $query = "SELECT COUNT(homework_id) FROM ".PREFICS."training_home_work WHERE lesson_id = $lesson_id AND user_id = $user_id";
        $result = $db->query($query);
        $count = $result->fetch();

        if ($count[0] == 0) {
            $sql = 'INSERT INTO '.PREFICS.'training_home_work (task_id, lesson_id, user_id, curator_id, status, create_date,
                        public, mark, points, test, test_start) 
                    VALUES (:task_id, :lesson_id, :user_id, :curator_id, :status, :create_date, :public, :mark, :points,
                        :test, :test_start)';
        } else {
            $sql = 'UPDATE '.PREFICS.'training_home_work SET task_id = :task_id, lesson_id = :lesson_id, user_id = :user_id,
                    curator_id = :curator_id, public = :public, mark = :mark, points = :points, test = :test, test_start = :test_start
                    WHERE lesson_id =:lesson_id AND user_id =:user_id';
        }
        
        $result = $db->prepare($sql);
        $result->bindParam(':task_id', $task_id, PDO::PARAM_INT);
        $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':curator_id', $curator_id, PDO::PARAM_INT);
        $result->bindParam(':create_date', $time, PDO::PARAM_INT);
        $result->bindParam(':public', $public, PDO::PARAM_INT);
        $result->bindParam(':mark', $mark, PDO::PARAM_INT);
        $result->bindParam(':points', $points, PDO::PARAM_INT);
        $result->bindParam(':test', $points, PDO::PARAM_STR);
        $result->bindParam(':test_start', $test_start, PDO::PARAM_INT);  

        if ($count[0] == 0) {
            $result->bindParam(':status', $status, PDO::PARAM_INT);
        }

        return $result->execute();                             
            
    }


    /**
     * ЗАПИСАТЬ РЕЗУЛЬТАТ ТЕСТА
     * @param $test_id
     * @param $lesson_id
     * @param $user_id
     * @param $quest_id
     * @param $answers
     * @param $points
     * @param $is_valid
     * @param $date
     * @return bool
     */
    public static function addResultTest($test_id, $lesson_id, $user_id, $quest_id, $answers, $points, $is_valid, $date)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'training_test_results (test_id, lesson_id, user_id, quest_id, result, date, points, is_valid) 
                VALUES (:test_id, :lesson_id, :user_id, :quest_id, :result, :date, :points, :is_valid)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':test_id', $test_id, PDO::PARAM_INT);
  		$result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':quest_id', $quest_id, PDO::PARAM_INT);
        $result->bindParam(':points', $points, PDO::PARAM_INT);
        $result->bindParam(':is_valid', $is_valid, PDO::PARAM_INT);
        $result->bindParam(':date', $date, PDO::PARAM_INT);
        $result->bindParam(':result', $answers, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ РЕЗУЛЬТАТА ПОСЛЕДНЕГО ТЕСТА
     * @param $test_id
     * @param $user_id
     * @return bool|mixed
     */
    public static function getLastTestResults($test_id, $user_id) {
        $db = Db::getConnection();
        $query = "SELECT MAX(date) AS date, COUNT(id) as count_ids, SUM(points) AS sum_points, SUM(is_valid) AS sum_valid
                  FROM ".PREFICS."training_test_results 
                  WHERE test_id = :test_id AND user_id = :user_id GROUP BY date ORDER BY date DESC";

        $result = $db->prepare($query);
        $result->bindParam(':test_id', $test_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return $data['count_ids'] > 0 ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ КОЛИЧЕСТВО РАЗ ПРОХОДЕНИЯ ТЕСТА
     * @param $test_id
     * @param $user_id
     * @return mixed
     */
    public static function getCountPassingTest($test_id, $user_id) {
        $db = Db::getConnection();
        $query = "SELECT COUNT(DISTINCT date) FROM ".PREFICS."training_test_results WHERE test_id = :test_id AND user_id = :user_id";

        $result = $db->prepare($query);
        $result->bindParam(':test_id', $test_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ РЕЗУЛЬТАТА ТЕСТА
     * @param $test_id
     * @param $user_id
     * @param $points_for_finish
     * @return array
     */
    public static function getTestResultData($test_id, $user_id, $points_for_finish)
    {
        $test_results = self::getLastTestResults($test_id, $user_id);
        if ($test_results) {
            $count_results = count($test_results);
            if ($points_for_finish) {
                $is_valid = $test_results['sum_points'] >= $points_for_finish ? true : false;
            } else {
                $is_valid = $test_results['count_ids'] == $test_results['sum_valid'] ? true : false;
            }

            return array_merge(['success' => $is_valid], $test_results);
        } else {
            return false;
        }
    }
    
    
  
    /**
     * ПОЛУЧИТЬ ОБЩИЙ РЕЗУЛЬТАТ ТЕСТА
     * @param $lesson_id
     * @param $user_id
     * @return bool|mixed
     */
    public static function getTestResult($lesson_id, $user_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT DISTINCT test, test_start FROM ".PREFICS."training_home_work WHERE lesson_id = $lesson_id AND user_id = $user_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ДОБАВИТЬ ВОПРОС К ТЕСТУ
     * @param $test_id
     * @param $question
     * @param $question_type
     * @param $help
     * @param $true_answer
     * @param $require_all_true
     * @param $sort
     * @param $img
     * @param $check_mode
     * @return string|null
     */

    public static function addQuestion($test_id, $question, $question_type, $help, $true_answer, $require_all_true, $sort, $img, $check_mode = 1)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'training_questions (test_id, question, question_type, help, true_answer,
                    require_all_true, sort, image, check_mode) 
                VALUES (:test_id, :question, :question_type, :help, :true_answer, :require_all_true, :sort, :image, :check_mode)';

        $result = $db->prepare($sql);
        $result->bindParam(':question', $question, PDO::PARAM_STR);
        $result->bindParam(':question_type', $question_type, PDO::PARAM_INT);
        $result->bindParam(':help', $help, PDO::PARAM_STR);
        $result->bindParam(':test_id', $test_id, PDO::PARAM_INT);
        $result->bindParam(':true_answer', $true_answer, PDO::PARAM_INT);
        $result->bindParam(':require_all_true', $require_all_true, PDO::PARAM_INT);
        $result->bindParam(':sort', $sort, PDO::PARAM_INT);
        $result->bindParam(':image', $img, PDO::PARAM_STR);
        $result->bindParam(':check_mode', $check_mode, PDO::PARAM_INT);
        $result = $result->execute();

        return $result ? $db->lastInsertId() : null;
    }


    /**
     * ИЗМЕНИТЬ ВОПРОС ТЕСТА
     * @param $quest_id
     * @param $question
     * @param $help
     * @param $true_answer
     * @param $require_all_true
     * @param $img
     * @param int $check_mode
     * @param null $points
     * @return bool
     */
    public static function editQuestion($quest_id, $question, $help, $true_answer, $require_all_true, $img,
                                        $check_mode = 1, $points = null)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'training_questions SET question = :question, help = :help, true_answer = :true_answer,
                require_all_true = :require_all_true, image = :image, check_mode = :check_mode, points = :points
                WHERE quest_id = :quest_id';
        $result = $db->prepare($sql);
        $result->bindParam(':quest_id', $quest_id, PDO::PARAM_INT);
        $result->bindParam(':question', $question, PDO::PARAM_STR);
        $result->bindParam(':help', $help, PDO::PARAM_STR);
        $result->bindParam(':true_answer', $true_answer, PDO::PARAM_INT);
        $result->bindParam(':require_all_true', $require_all_true, PDO::PARAM_INT);
        $result->bindParam(':image', $img, PDO::PARAM_STR);
        $result->bindParam(':check_mode', $check_mode, PDO::PARAM_INT);
        $result->bindParam(':points', $points, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ПОДРОБНЫЙ РЕЗУЛЬТАТ ТЕСТА
     * @param $lesson_id
     * @param $user_id
     * @param $is_last_result
     * @return bool|mixed
     */
    public static function getDetailedTestResult($lesson_id, $user_id, $is_last_result)
    {
        $db = Db::getConnection();
        $query = "SELECT MAX(t_tr.test_id) AS test_id, MAX(t_tr.result) AS result, MAX(t_tr.is_valid) AS is_valid,
                  MAX(t_tr.points) AS user_points, GROUP_CONCAT(DISTINCT t_to.title SEPARATOR ',') AS title, t_tq.quest_id, t_tq.question, 
                  t_tq.help, t_tq.image, t_tq.question_type, MAX(t_to.cover) AS cover_quest, MAX(t_to_answ.cover) AS cover_answer
                  FROM ".PREFICS."training_test_results AS t_tr 
                  LEFT JOIN ".PREFICS."training_test_options AS t_to ON t_tr.quest_id = t_to.quest_id
                  LEFT JOIN ".PREFICS."training_questions AS t_tq ON t_tr.quest_id = t_tq.quest_id
                  LEFT JOIN ".PREFICS."training_test_options AS t_to_answ ON t_tr.quest_id = t_to_answ .quest_id AND t_tr.result = t_to_answ.value
                  WHERE t_tr.lesson_id = $lesson_id AND t_tr.user_id = $user_id AND t_to.valid = 1".
                  ($is_last_result ? ' AND t_tr.date = (SELECT MAX(date) AS date FROM '.PREFICS."training_test_results 
                  WHERE lesson_id = $lesson_id AND user_id = $user_id)" : '').
                  ' GROUP BY t_tr.quest_id';

        $result = $db->query($query);
        $data = $result->fetchAll(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ВОПРОС ПО ID
     * @param $quest_id
     * @return bool|mixed
     */
    public static function getQuestion($quest_id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."training_questions WHERE quest_id = $quest_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ СОРТИРОВКУ ДЛЯ ДОБАВЛЯЕМОГО ВОПРОСА К ТЕСТУ
     * @param $lesson_id
     * @return int
     */
    public static function getFreeSort2Question($lesson_id) {
        $db = Db::getConnection();
        $query = "SELECT MAX(tq.sort) FROM ".PREFICS."training_questions AS tq
                  LEFT JOIN ".PREFICS."training_test AS tt ON tt.test_id = tq.test_id
                  WHERE tt.lesson_id = :lesson_id";
        $result = $db->prepare($query);
        $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->execute();
        $count = $result->fetch();

        return (int)$count[0] + 1;
    }


    /**
     * ПОЛУЧИТЬ ВОПРОСЫ К ТЕСТУ
     * @param $test_id
     * @return array|bool
     */
    public static function getQuestionsByTestId($test_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."training_questions WHERE test_id = $test_id ORDER BY sort ASC");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ID ВОПРОСОВ ТЕСТА
     * @param $test_id
     * @return array|bool
     */
    public static function getQuestionsIdsByTestId($test_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT GROUP_CONCAT(quest_id ORDER BY sort ASC) FROM ".PREFICS."training_questions WHERE test_id = $test_id");
        $data = $result->fetch();

        return !empty($data) ? explode(',', $data[0]) : false;
    }


    /**
     * ПОЛУЧИТЬ КОЛИЧЕСТВО ВОПРОСОВ ТЕСТА
     * @param $test_id
     * @return array|bool
     */
    public static function getCountQuestions2Test($test_id)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(*) FROM ".PREFICS."training_questions WHERE test_id = $test_id ORDER BY sort ASC");
        $count = $result->fetch();

        return $count[0];
    }


    /**
     * ОБНОВИТЬ СОРТИРОВКУ ДЛЯ ВОПРОСА ТЕСТА
     * @param $id
     * @param $sort
     * @return bool
     */
    public static function updSortTestQuestions($id, $sort) {
        $db = Db::getConnection();
        $result = $db->prepare('UPDATE '.PREFICS.'training_questions SET sort = :sort WHERE quest_id = :quest_id');

        $result->bindParam(':quest_id', $id, PDO::PARAM_INT);
        $result->bindParam(':sort', $sort, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ВОПРОС К ТЕСТУ
     * @param $quest_id
     * @return bool
     */
    public static function delQuestion($quest_id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'training_questions WHERE quest_id = :id ; DELETE FROM '.PREFICS.'training_test_options WHERE quest_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $quest_id, PDO::PARAM_INT);
        return $result->execute();
    }


    /**
     * ДОБАВИТЬ ОПЦИЮ (ВАРИАНТ ОТВЕТА) для вопрсоа
     * @param $quest_id
     * @param $title
     * @param $value
     * @param $valid
     * @param $points
     * @param $sort
     * @param string $img
     * @return bool
     */
    public static function addAnswer($quest_id, $title, $value, $valid, $points, $sort, $img = '')
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'training_test_options (quest_id, title, value, sort, valid, points, cover) 
                VALUES (:quest_id, :title, :value, :sort, :valid, :points, :cover)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':value', $value, PDO::PARAM_STR);
  		$result->bindParam(':quest_id', $quest_id, PDO::PARAM_INT);
        $result->bindParam(':sort', $sort, PDO::PARAM_INT);
        $result->bindParam(':valid', $valid, PDO::PARAM_INT);
        $result->bindParam(':points', $points, PDO::PARAM_INT);
        $result->bindParam(':cover', $img, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ОБНОВИТЬ ОПЦИЮ (ВАРИАНТ ОТВЕТА)
     * @param $option_id
     * @param $title
     * @param $value
     * @param $valid
     * @param $points
     * @param $cover
     * @return bool
     */
    public static function updAnswer($option_id, $title, $value, $valid, $points, $cover)
    {
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS."training_test_options SET title = :title, value = :value,
                valid = :valid, points = :points, cover = :cover WHERE option_id = $option_id";
        $result = $db->prepare($sql);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':value', $value, PDO::PARAM_STR);
        $result->bindParam(':valid', $valid, PDO::PARAM_INT);
        $result->bindParam(':points', $points, PDO::PARAM_INT);
        $result->bindParam(':cover', $cover, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ОБНОВИТЬ ОПЦИИ (ВАРИАНТЫ ОТВЕТА)
     * @param $quest_id
     * @param $title
     * @param $value
     * @param $valid
     * @param $points
     * @return bool
     */
    public static function updAnswersByQuestId($quest_id, $title, $value, $valid, $points)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."training_test_options SET title = :title, value = :value,
                valid = :valid, points = :points WHERE quest_id = :quest_id";
        $result = $db->prepare($sql);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':value', $value, PDO::PARAM_STR);
        $result->bindParam(':valid', $valid, PDO::PARAM_INT);
        $result->bindParam(':points', $points, PDO::PARAM_INT);
        $result->bindParam(':quest_id', $quest_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ОБНОВИТЬ СОРТИРОВКУ У ОТВЕТА
     * @param $option_id
     * @param $sort
     * @return bool
     */
    public static function updSortAnswer($option_id, $sort) {
        $db = Db::getConnection();
        $result = $db->prepare('UPDATE '.PREFICS.'training_test_options SET sort = :sort WHERE option_id = :option_id');

        $result->bindParam(':option_id', $option_id, PDO::PARAM_INT);
        $result->bindParam(':sort', $sort, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ОПЦИЮ (ВАРИАНТ ОТВЕТА)
     * @param $option_id
     * @return bool
     */
    public static function deleteAnswer($option_id){
        
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'training_test_options WHERE option_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $option_id, PDO::PARAM_INT);
        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ОПЦИИ (ВАРИАНТЫ ОТВЕТА) на ВОПРОС
     * @param $quest_id
     * @param bool $valid
     * @param bool $by_option_id
     * @param bool $is_rand
     * @return array|bool
     */
    public static function getOptionsByQuest($quest_id, $valid = false, $by_option_id = false, $is_rand = false)
    {
        $db = Db::getConnection();
        $query = "SELECT * FROM ".PREFICS."training_test_options WHERE quest_id = $quest_id";
        $query .= ($valid ? ' AND valid = 1' : '') . ' ORDER BY '.(!$is_rand ? 'sort ASC' : 'RAND()');
        $result = $db->query($query);

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if ($by_option_id) {
                $data[$row['option_id']] = $row;
            } else {
                $data[] = $row;
            }

        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ОПЦИЮ (ВАРИАНТ ОТВЕТА) на ВОПРОС
     * @param $option_id
     * @return array|bool
     */
    public static function getOption($option_id)
    {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT * FROM '.PREFICS.'training_test_options WHERE option_id = :option_id');
        $result->bindParam(':option_id', $option_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ СОРТИРОВКУ ДЛЯ ДОБАВЛЯЕМОГО ОТВЕТА К ВОПРОСУ ТЕСТА
     * @param $quest_id
     * @return int
     */
    public static function getFreeSort2QuestionOption($quest_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT MAX(sort) FROM ".PREFICS."training_test_options WHERE quest_id = :quest_id");
        $result->bindParam(':quest_id', $quest_id, PDO::PARAM_INT);
        $result->execute();
        $count = $result->fetch();

        return (int)$count[0] + 1;
    }


    /**
     * ПОЛУЧИТЬ ТЕСТ ПО TASK_ID
     * @param $task_id
     * @return bool|mixed
     */
    public static function getTestByTaskID($task_id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."training_test WHERE task_id = $task_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }

     /**
     * ПОЛУЧИТЬ ТЕСТ ПО TEST_ID
     * @param $test_id
     * @return bool|mixed
     */
    public static function getTestByTestID($test_id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."training_test WHERE test_id = $test_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ДОБАВИТЬ ТЕСТ
     * @param $lesson_id
     * @return bool
     */
    public static function addTest($lesson_id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT task_id, task_type FROM ".PREFICS."training_task WHERE lesson_id = $lesson_id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (!empty($data)) {
            $status = $data['task_type'] > 1 ? 1 : 0;
            $result = $db->prepare('INSERT INTO '.PREFICS.'training_test (task_id, lesson_id) VALUES (:task_id, :lesson_id)');

            $result->bindParam(':task_id', $data['task_id'], PDO::PARAM_INT);
            $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);

            return $result->execute();
        }
    }


    /**
     * ОБНОВИТЬ ТЕСТ
     * @param $lesson_id
     * @param $data
     * @return bool
     */
    public static function updTest($lesson_id, $data)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."training_test 
        SET 
            test_desc = :test_desc, 
            finish = :finish, 
            test_try = :test_try, 
            re_test = :re_test, 
            test_time = :test_time, 
            show_questions_count = :show_questions_count, 
            help_hint_success = :help_hint_success, 
            is_random_questions = :is_random_questions, 
            help_hint_fail = :help_hint_fail, 
            auto_start = :auto_start 
        WHERE lesson_id = $lesson_id";

        $result = $db->prepare($sql);
        $result->bindParam(':test_desc', $data['test_desc'], PDO::PARAM_STR);
        $result->bindParam(':finish', $data['test_finish'], PDO::PARAM_INT);
        $result->bindParam(':test_try', $data['test_try'], PDO::PARAM_INT);
        $result->bindParam(':re_test', $data['re_test'], PDO::PARAM_INT);
        $result->bindParam(':test_time', $data['test_time'], PDO::PARAM_INT);
        $result->bindParam(':show_questions_count', $data['show_questions_count'], PDO::PARAM_INT);
        $result->bindParam(':help_hint_success', $data['help_hint_success'], PDO::PARAM_INT);
        $result->bindParam(':is_random_questions', $data['is_random_questions'], PDO::PARAM_INT);
        $result->bindParam(':help_hint_fail', $data['help_hint_fail'], PDO::PARAM_INT);
        $result->bindParam(':auto_start', $data['auto_start'], PDO::PARAM_INT);


        return $result->execute();
    }
    
      /**
     * УДАЛИТЬ РЕЗУЛЬТАТЫ ТЕСТА (Дать еще попытку пользователю)
     * @param $lesson_id
     * @param $user_id
     * @param $isanswer
     * @return bool|mixed
     */
    public static function deleteResultsAnswersUsers($lesson_id, $user_id, $isanswer)
    {
        $db = Db::getConnection();
        if ($isanswer){
            $result = $db->query("UPDATE ".PREFICS."training_home_work SET test_start = 0, test = 0 WHERE lesson_id = $lesson_id AND user_id = $user_id");
        } else {
            $result = $db->query("DELETE FROM ".PREFICS."training_home_work WHERE lesson_id = $lesson_id AND user_id = $user_id");
        }
        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ТЕКСТ СТАТУСА ТЕСТА
     * этот статус в таблице training_home_work в колонке test (пока временно там возможно будет json)
     * @param $test_status 0 - в процессе, 1 - сдан, 2 - не сдан, null(или какое другое) - не начат
     * @return string
     */
    public static function getStatusText($test_status) {
        $element_name = '';

        switch ((int)$test_status) {
            case 0:
                $element_name = System::Lang('IN_PROCESS');
                break;
            case 1:
                $element_name = System::Lang('PASSED');
                break;
            case 2:
                $element_name = System::Lang('TEST_NOT_PASSED');
                break;
            default:
                $element_name = System::Lang('NOT_DONE');
                break;
        }

        return $element_name;
    }
    
    
    /**
     * ПОЛУЧИТЬ СТАТУС ДЛЯ СОХРАНЕНИЯ В ТАБЛИЦУ ПРОХОЖДЕНИЯ УРОКОВ (USER_MAP)
     * @param $task_type
     * @param $access_type
     * @param $test_status
     * @param $homework_id
     * @param is_send_homework
     * @return bool|int
     */
    public static function getStatus2UserMap($task_type, $access_type, $test_status, $homework_id, $is_send_homework) {
        $map_status = false;
        
        if ($task_type == 3 || $access_type == 1 || $is_send_homework && $test_status == 1) {
            $map_status = $test_status == 1 ? 3 : 2;
        }
        
        return $map_status;
    }


    /**
     * @param $question
     * @return string
     */
    public static function getOptionInputType($question) {
        if ($question['question_type'] == self::QUESTION_TYPE_OWN_ANSWER) {
            return 'text';
        }

        $type = '';
        switch ($question['true_answer']) {
            case 1:
                $type = 'radio';
                break;
            case 2:
                $type = 'checkbox';
                break;
        }

        return $type;
    }


    /**
     * @param $type
     * @return string
     */
    public static function getQuestionTypeKey($type) {
        switch ($type) {
            case self::QUESTION_TYPE_VARIANT:
                $type = 'variant';
                break;
            case self::QUESTION_TYPE_OWN_ANSWER:
                $type = 'own_answer';
                break;
            case self::QUESTION_TYPE_ARRANGE:
                $type = 'arrange';
                break;
        }

        return $type;
    }
}