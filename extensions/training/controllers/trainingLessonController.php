<?php defined('BILLINGMASTER') or die;

class trainingLessonController extends trainingBaseController {

    /**
     * СТРАНИЦА УРОКА
     * @param $training_alias
     * @param $lesson_alias
     * @throws Exception
     */
    public function actionLesson($training_alias, $lesson_alias)
    {
        $training_alias = htmlentities($training_alias);
        $lesson_alias = htmlentities($lesson_alias);
        
        $canonical = $this->settings['script_url'].'/training/view/'.$training_alias.'/lesson/'.$lesson_alias;

        $training = Training::getTrainingByAlias($training_alias);
        $lesson = $training ? TrainingLesson::getLessonByAlias($training['training_id'], $lesson_alias) : null;
        if (!$training || !$training['status'] || !$lesson) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        // TODO для размышления, в старых тренингах используется params переменная и там в head подгружается код
        // для комментариев, по этому тут костыль, что бы там не городить огород из условий на тренинги(новые или старые)
        $params['params']['commenthead'] = $this->tr_settings['commenthead'];
        if (!empty($params['params']['commenthead'])) {
            $comments = 1;
        }

        $user_groups = $this->user ? User::getGroupByUser($this->user['user_id']) : false;
        $user_planes = $this->user ? Member::getPlanesByUser($this->user['user_id'], 1, true) : false;
        $section = $lesson['section_id'] ? TrainingSection::getSection($lesson['section_id']) : null;
        $section_id = $section ? $section['section_id'] : 0;
        $user_is_curator = Training::isCuratorInTrainingInSection($this->user_id, $training['training_id'], $section_id);
        $watermark = isset($this->user) ? htmlentities($this->user['email']) : '';
        if (isset($this->user['phone'])) {
            $watermark .= $this->user['phone'];
        }
        $access = Training::getAccessData($user_groups, $user_planes, $training, $section, $lesson);
        
        if (isset($access['status']) && $access['status']!==4) {
            $has_lesson_last_stop = TrainingLesson::isLessonLastStopStatus($lesson['sort'], $training['training_id'], $lesson['section_id']);
            if ($has_lesson_last_stop && (!isset($access['is_admin']) && !isset($access['is_curator']))) {
                $access = TrainingLesson::isAccessLastHomework($has_lesson_last_stop, $training,
                    $lesson, $section, $this->user_id, $user_groups, $user_planes
                );
            }
        }

        if (!Training::checkUserAccess($access)) {
            $this->setPageSettings($lesson, 'lesson', 'layouts/no_access.php',
                'no-access-page lesson-no-access-page inner-training-page'
            );
            require_once ("{$this->template_path}/main3.php");
            exit;
        }

        if ($this->user_id && !isset($_SESSION['training_save_uv']['lesson'][$lesson['lesson_id']])) {
            TrainingUserVisits::saveVisit($this->user_id, $training['training_id'], $section_id, $lesson['lesson_id']);
            $_SESSION['training_save_uv']['lesson'][$lesson['lesson_id']] = true;
        }


        $category = $training['cat_id'] != 0 ? TrainingCategory::getCategory($training['cat_id']) : null;
        $sub_category = false;
        if ($category && $category['parent_cat'] != 0) {
            $sub_category = $category;
            $category = TrainingCategory::getCategory($category['parent_cat']);
        }

        if ($this->user_id && $training['confirm_phone'] && CallPassword::notAccessUser($this->user_id)) {
            $this->setPageSettings($lesson, 'lesson', 'layouts/callpassword_no_access.php',
                'no-access-page lesson-no-access-page inner-training-page'
            );
            require_once ("{$this->template_path}/main3.php");
            exit;
        }

        $time = time();
        $task = TrainingLesson::getTask2Lesson($lesson['lesson_id']);
        $homework = $task ? TrainingLesson::getHomeWork($this->user_id, $lesson['lesson_id']) : false;
        $homework_id = $homework ? $homework['homework_id'] : null;
        $answer_list = $this->user_id ? TrainingLesson::getAnswers2Lesson($lesson['lesson_id'], $this->user_id, $homework_id) : false;

        // если задания у урока нет, и стоит настройка сразу делать урок пройденым
        $status_usermap = $task['task_type'] == 0 && $lesson['auto_access_lesson'] == 1 ? 3 : 0;

        $hit = TrainingLesson::writeHit($lesson['lesson_id'], $training['training_id'],$lesson['hits'] + 1, $this->user_id, $status_usermap);
        $lesson_complete_status = Traininglesson::getLessonCompleteStatus($lesson['lesson_id'], $this->user_id);
        $lesson_homework_status = Traininglesson::getHomeworkStatus($lesson['lesson_id'], $this->user_id);

        $levelAccessTypeHomeWork = $access && isset($access['is_admin']) || isset($access['is_curator']) ? 2 :
            TrainingLesson::getLevelAccessTypeHomeWork($user_groups, $user_planes, $training);

        // тут тип проверки домашнего задания берем из прав доступа юзера
        $task_check_type = $task['check_type'] > $levelAccessTypeHomeWork ? $levelAccessTypeHomeWork : $task['check_type'];
        $public_homework_settings = TrainingPublicHomework::getSettings($lesson['lesson_id']);

        if (isset($_POST['complete'])) { // ответ к уроку
            $homework_is_public = $public_homework_settings['status'] && $public_homework_settings['user_choose'] && isset($_POST['homework_is_public']) ? true : false;

            System::sessionAdd("homework_last_text", $_POST['answer'] ?? "");
            $homework = new TrainingHomeWork($training, $lesson, $task, $this->user_id, function ($error_message) use ($training_alias, $lesson_alias) {
                return System::redirectUrl("/training/view/$training_alias/lesson/$lesson_alias?error=$error_message", null, '#tr_answer_list');
            });

            $homework->answerSave($lesson_complete_status, $task_check_type, $levelAccessTypeHomeWork, $answer_list, $homework_is_public);

            System::redirectUrl("/training/view/$training_alias/lesson/$lesson_alias", null, '#tr_answer_list');
        }

        if (isset($_POST['comment'])) { // комментарий к уроку
            $homework = new TrainingHomeWork($training, $lesson, $task, $this->user_id);
            $id = $homework->commentSave($homework_id);

            System::redirectUrl("/training/view/$training_alias/lesson/$lesson_alias", null, "#tr_comment_{$id}");
        }

        if (isset($_POST['public_homework']['add_comment'])) { // комментарий к публичному ДЗ
            TrainingPublicHomework::addComment($_POST['public_homework'], $lesson['lesson_id'], $this->user_id);
            System::redirectUrl("/training/view/$training_alias/lesson/$lesson_alias", null, "#tr_homework_list");
        }

        $attachments = TrainingLesson::getElements2Lesson($lesson['lesson_id'], TrainingLesson::ELEMENT_TYPE_ATTACH);

        //Сертификат
        $user_id = intval(User::isAuth());
        $sertificate = json_decode($training['sertificate'], true);
        $have_certificate = Training::getUrlHashCertificate2User($user_id, $training['training_id']);


        $this->setPageSettings($lesson, 'lesson', 'lesson/index.php', 'lesson-page inner-training-page');
        require_once ("{$this->template_path}/main3.php");
    }


    public function actionLessonAttach($attach_id) {
        $attach = TrainingLesson::getElement($attach_id);
        if ($attach ) {
            $lesson = TrainingLesson::getLesson($attach['lesson_id']);
            $training = Training::getTraining($lesson['training_id']);

            $user_groups = $this->user_id ? User::getGroupByUser($this->user_id) : false;
            $user_planes = $this->user_id ? Member::getPlanesByUser($this->user_id, 1, true) : false;
            $section = $lesson['section_id'] ? TrainingSection::getSection($lesson['section_id']) : null;

            $access = Training::getAccessData($user_groups, $user_planes, $training, $section, $lesson);
            if (Training::checkUserAccess($access)) {
                $path = ROOT . "/load/training/lessons/{$attach['lesson_id']}/{$attach['params']['attach']}";
                if (file_exists($path)) {
                    System::fileForceDownload($path, $attach['params']['attach']);
                }
            }
        }
    }


    /**
     * СТРАНИЦА ВЫБОРА ТАРИФА в уроках (Продуктов)
     * @param $lesson_id
     */
    public function actionOptions($lesson_id)
    {
        $lesson_id = intval($lesson_id);
        $lesson = TrainingLesson::getLesson($lesson_id);

        if (!$lesson) {
            require_once (ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $h1 = $lesson['name'];
        $training = Training::getTraining($lesson['training_id']);
        $section = TrainingSection::getSection($lesson['section_id']);

        $sub_category = $category = false;
        if ($training['cat_id'] != 0) {
            $category = TrainingCategory::getCategory($training['cat_id']);
            if ($category && $category['parent_cat'] != 0) {
                $sub_category = $category;
                $category = TrainingCategory::getCategory($sub_category['parent_cat']);
            }
        }

        $big_button = json_decode($lesson['by_button'], true);
        $list_product = isset($big_button['rate']) ? $big_button['rate'] : null;

        $this->setSEOParams('Выберите вариант');
        $this->setViewParams('', 'layouts/rates.php', null, null, 'training-options-page inner-training-page');

        require_once ("{$this->template_path}/main3.php");;
    }
}
