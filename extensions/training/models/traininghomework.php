<?php defined('BILLINGMASTER') or die;


class TrainingHomeWork {

    use ResultMessage;

    const ALLOWED_TYPES = [];

    private $training;
    private $lesson;
    private $task;
    private $homework;
    private $homework_id;
    private $user_id;
    /** @var callable|null  */
    private $onErrorCallback = null;

    /**
     * TrainingHomeWork constructor.
     * @param               $training
     * @param               $lesson
     * @param               $task
     * @param               $user_id
     * @param callable|null $callbackOnAttachError(str $error_message)
     */
    public function __construct($training, $lesson, $task, $user_id, callable $callbackOnAttachError = null) {
        $this->training = $training;
        $this->lesson = $lesson;
        $this->user_id = $user_id;
        $this->task = $task;
        $this->homework = $task ? TrainingLesson::getHomeWork($user_id, $lesson['lesson_id']) : false;
        $this->homework_id = $this->homework ? $this->homework['homework_id'] : null;

        $this->onErrorCallback = $callbackOnAttachError;
    }


    /**
     * @return false|string|null
     */
    public function getAttach() {
        $attach = null;
        if (isset($_FILES['lesson_attach']) && $_FILES['lesson_attach']['size'][0] > 0) {
            $file_lesson_attach = $_FILES['lesson_attach'];
            $isValid = $this->validateAttach($file_lesson_attach);
            if ($isValid !== true) {
                return call_user_func($this->onErrorCallback, $isValid['message']);
            }

            $attach = TrainingLesson::uploadAttach2Answer($file_lesson_attach, $this->lesson['lesson_id'], Training::USER_TYPE_USER);
        }

        return $attach;
    }


    /**
     * @return string
     */
    public function getAnswer() {
        if (isset($_POST['answer'])) {
            return self::getSafeAnswer($_POST['answer']);
        }

        return null;
    }


    /**
     * @param $answer
     * @return string|string[]|null
     */
    public static function getSafeAnswer($answer) {
        $answer = preg_replace('/<(script)[^>]*>.*?<\/\\1>/s','', html_entity_decode($answer));
        $answer = $answer ? strip_tags($answer, '<div><strong><em><br><br></br><p><ul><ol><li><a><span><b><del><img>') : null;

        if ($answer) {
            $dom = new DOMDocument;
            $dom->loadHTML(mb_convert_encoding($answer, 'HTML-ENTITIES', 'UTF-8'));
            $xpath = new DOMXPath($dom);
            $tags = $dom->getElementsByTagName('*');
            if ($tags) {
                foreach ($tags as $tag) {
                    $tag->removeAttribute('onerror');
                    $tag->removeAttribute('onclick');
                }
            }

            $answer = $dom->saveHTML();
        }

        $answer = $answer && trim(strip_tags($answer, '<img>')) ? base64_encode(htmlentities($answer)) : null;

        return $answer;
    }


    /**
     * @param $lesson_complete_status
     * @param $task_check_type
     * @param $levelAccessTypeHomeWork
     * @param $answer_list
     * @param $homework_is_public
     * @return bool
     */
    public function answerSave($lesson_complete_status, $task_check_type, $levelAccessTypeHomeWork, $answer_list, $homework_is_public) {
        $attach = $this->getAttach();
        $answer = $this->getAnswer();

        $work_link = isset($_POST['work_link']) && $_POST['work_link'] ? trim($_POST['work_link']) : null;
        if (isset($_POST['answer']) && !$answer && (!$work_link && !$attach)) {
            return false;
        }

        $answer = $answer ?: base64_encode('Работа сделана, пожалуйста, проверьте. Спасибо!');

        if ($task_check_type == 0) { // Если самостоятельная проверка то в базу не пишем, а просто обновляем статус в юзер_мап ниже
            $answer = base64_encode('Самостоятельная проверка');
            $status = in_array($this->task["access_type"], [1,3]) && $this->homework['test'] == 2 ? 4 : 1;
        } else { // тут если у урока стоит тип Автопроверка(1), то юзер_мап пишется статус 4 иначе во всех остальных случаях 1
            $status = $task_check_type == 1 && in_array($this->task["access_type"], [1,3]) && $this->homework['test'] == 2 ? 4
                : ($task_check_type == TrainingLesson::HOME_WORK_ACCEPTED ? TrainingLesson::HOME_WORK_ACCEPTED : TrainingLesson::HOME_WORK_SEND);
        }

        $write = TrainingLesson::writeAnswer($this->task['task_id'], $this->lesson['lesson_id'], $this->user_id,
            0, $status, $homework_is_public, $answer, $attach, $work_link
        );

        if ($write) { // TODO вот тут нужно будет добавить проверки по типам доступа к ДЗ у юзера
            if ($task_check_type == 0) {
                $lesson_complete_status = in_array($this->task["access_type"], [1,3]) && $this->homework['test'] == 2 ? 2 : 3;
            } else { // тут если у Урока стоит тип Автопроверка(1), то юзер_мап пишется статус 4 иначе во всех остальных случаях 1
                $lesson_complete_status = $task_check_type == 1 && in_array($this->task["access_type"], [1,3]) && $this->homework['test'] == 2 ? 2
                    : ($task_check_type == 1 ? TrainingLesson::HOMEWORK_AUTOCHECK : TrainingLesson::HOMEWORK_SUBMITTED);
            }

            TrainingLesson::updLessonCompleteStatus($this->lesson['lesson_id'], $this->user_id, $lesson_complete_status);

            if ($this->task['task_type'] && $this->training['send_email_to_curator'] == 1 && intval($task_check_type) == 2) {
                Email::SendAnswerFromUserToCurator($this->user_id, $answer_list[0]['homework_id'], $answer, $this->lesson, $this->training);
            }
        }
    }


    /**
     * @param $homework_id
     * @return bool
     */
    public function commentSave($homework_id) {
        $attach = $this->getAttach();
        $answer = $this->getAnswer();

        if ($answer) {
            $id = TrainingLesson::writeComment($homework_id, $this->user_id, 0, $answer, 0, $attach);

            if ($this->training['send_email_to_curator'] == 1) {
                Email::SendAnswerFromUserToCurator($this->user_id, $homework_id, $answer, $this->lesson, $this->training);
            }

            return $id;
        }

        return false;
    }


    /**
     * ПОЛУЧИТЬ ДЗ
     * @param $homework_id
     * @return bool|mixed
     */
    public static function getHomework($homework_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT *  FROM " . PREFICS . "training_home_work WHERE homework_id = :homework_id");
        $result->bindParam(':homework_id', $homework_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ КОЛИЧЕСТВО ЛАЙКОВ ДЛЯ ДЗ
     * @param $homework_id
     * @param null $user_id
     * @return mixed
     */
    public static function getCountLikes2Homework($homework_id, $user_id = null) {
        $db = Db::getConnection();
        $where = 'WHERE homework_id = :homework_id'.($user_id ? ' AND user_id = :user_id' : '');
        $result = $db->prepare('SELECT COUNT(*) FROM '.PREFICS."training_home_work_liked $where");
        $result->bindParam(':homework_id', $homework_id, PDO::PARAM_INT);
        if ($user_id) {
            $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * ДОБАВИТЬ ЛАЙК ДЛЯ ДЗ
     * @param $homework_id
     * @param $user_id
     * @return bool
     */
    public static function addLike2Homework($homework_id, $user_id)
    {
        $db = Db::getConnection();
        $result = $db->prepare('INSERT INTO '.PREFICS.'training_home_work_liked (homework_id, user_id) 
                                         VALUES (:homework_id, :user_id)'
        );
        $result->bindParam(':homework_id', $homework_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ЛАЙК ДЛЯ ДЗ
     * @param $homework_id
     * @param $user_id
     * @return bool|mixed
     */
    public static function delLike2Homework($homework_id, $user_id)
    {
        $db = Db::getConnection();
        $result = $db->prepare("DELETE FROM ".PREFICS."training_home_work_liked 
                                         WHERE homework_id = :homework_id AND user_id = :user_id"
        );
        $result->bindParam(':homework_id', $homework_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ЗАГРУЗКА ВЛОЖЕНИЯ К ОТВЕТУ
     * @param $files
     * @param $lesson_id
     * @param $user_type
     * @return false|string
     */
    public static function uploadAttach($files, $lesson_id, $user_type)
    {
        $settings = System::getSetting();
        $attachments = [];
        $attach_folders = [
            Training::USER_TYPE_ADMIN => 'admin',
            Training::USER_TYPE_CURATOR => 'curator',
            Training::USER_TYPE_USER => 'user',
        ];

        foreach($files["name"] as $key => $attach_name) {
            if (!$attach_name || $files["size"][$key] == 0) {
                return '';
            }

            $attach_name = System::getSecureString($attach_name, true);

            if ($user_type == Training::USER_TYPE_USER) {
                if (!System::isAllowedUploadAttach($attach_name, $files['type'][$key], $files["size"][$key], $settings)) {
                    continue;
                }
            }

            $tmp_name = $files["tmp_name"][$key];
            $relative_path = "load/hometask/lessons/$lesson_id/{$attach_folders[$user_type]}";

            $dir_path = ROOT;
            foreach (explode('/', $relative_path) as $dir) {
                $dir_path .= "/$dir";
                if (!file_exists($dir_path)) {
                    mkdir($dir_path);
                }
            }

            $unique_name = md5(microtime(true).mt_rand(100,999).$attach_name);
            $file_info = pathinfo($attach_name);
            $relative_path = "/$relative_path/$unique_name.{$file_info['extension']}";

            if (is_uploaded_file($tmp_name) && move_uploaded_file($tmp_name, ROOT.$relative_path)) {
                $attachments[] = [
                    'path' => urlencode($relative_path),
                    'name' => $attach_name,
                ];
            }
        }

        return !empty($attachments) ? json_encode($attachments) : '';
    }

    private function validateAttach($file_lesson_attach) {

        if ($file_lesson_attach["error"][0] != UPLOAD_ERR_OK) { //TODO: наверное нужно залогировать
            return ["status" => false, "message" => "Файл загружен с ошибкой"];
        }

        $allowedTypes = System::getAllowedMimes();
        if (!in_array($file_lesson_attach["type"][0], $allowedTypes)) {
            return ["status" => false, "message" => "Неподдерживаемый тип файла. Файл данного MIME-типа запрещен для загрузки"];
        }

        $allowedTypes = System::getAllowedFileExtensions();
        $file_extension = System::getFileExtension($file_lesson_attach["name"][0]);
        if (!in_array($file_extension, $allowedTypes)) {
            return ["status" => false, "message" => "Неподдерживаемый тип файла. Файл с расширением '$file_extension' запрещен для загрузки"];
        }

        return true;
    }
}