<?php defined('BILLINGMASTER') or die;

class trainingController extends trainingBaseController {

    /**
     * ГЛАВНАЯ СТРАНИЦА ТРЕНИНГОВ
     */
    public function actionIndex()
    {
        $filter  = [
            'access' => isset($_GET['acc']) && $_GET['acc'] != 'all' ? $_GET['acc'] : false,
            'author' => isset($_GET['aut']) && is_array($_GET['aut'])  ? $_GET['aut'] : false,
            'category' => isset($_GET['cat']) && is_array($_GET['cat'])  ? $_GET['cat'] : false,
        ];

        $cat_list = $training_list = $count_trainings_without_filter = false;

        if ($this->tr_settings['show_list'] == 'all') {
            $cat_list = TrainingCategory::getCatList(false, TrainingCategory::STATUS_CATEGORY_ON);
            $count_trainings_without_filter = Training::getCountTrainings([0], 1);
            $training_list = $count_trainings_without_filter ? Training::getTrainingList(0, null, $filter) : false;
        } elseif($this->tr_settings['show_list'] == 'without_categories') {
            $count_trainings_without_filter = Training::getCountTrainings(null, 1);
            $training_list = Training::getTrainingList(null, null, $filter);
        } elseif ($this->tr_settings['show_list'] == 'content_separate_category' && $this->tr_settings['categories_to_content']) {
            $cat_list = TrainingCategory::getSubCategoriesByParentsIds($this->tr_settings['categories_to_content']);
            $count_trainings_without_filter = Training::getCountTrainings($this->tr_settings['categories_to_content'], 1);
            $training_list = $count_trainings_without_filter ? Training::getTrainingList($this->tr_settings['categories_to_content'], null, $filter) : false;
        }

        $canonical = $this->settings['script_url'].'/training';
        $user_id = intval(User::isAuth());
        $user_groups = $user_id ? User::getGroupByUser($user_id) : false;
        $user_planes = $user_id ? Member::getPlanesByUser($user_id, 1, true) : false;

        $this->setPageSettings($this->tr_settings, 'training_index', 'training/list.php',
            '', 'training-list-container'
        );
        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * ПРОСМОТР ТРЕНИНГА
     * @param $tr_alias
     * @throws Exception
     */
    public function actionTraining($tr_alias)
    {
        $tr_alias = htmlentities($tr_alias);
        $training = Training::getTrainingByAlias($tr_alias);

        if (!$training || !$training['status']) {
            require_once (ROOT . "/template/{$this->settings['template']}/404.php");
        }
        
        require_once (ROOT ."/lib/mobile_detect/Mobile_Detect.php");
        $detect = new Mobile_Detect;

        $user_id = intval(User::isAuth());
        $user_groups = $user_id ? User::getGroupByUser($user_id) : false;
        $user_planes = $user_id ? Member::getPlanesByUser($user_id, 1, true) : false;
        $user_is_curator = Training::isCuratorInTrainingInSection($user_id, $training['training_id']);

        $big_button = json_decode($training['big_button'], true);
        $small_button = json_decode($training['small_button'], true);
        $access = Training::getAccessData($user_groups, $user_planes, $training);

        if (!Training::checkUserAccess($access) && $big_button['type'] != 6 && $small_button['type'] != 6) {
            $this->setPageSettings($training, 'training', 'layouts/no_access.php', 'no-access-page inner-training-page');
            require_once ("{$this->template_path}/main3.php");
            exit;
        }
        
        if ($user_id && !isset($_SESSION['training_save_uv']['training'][$training['training_id']])) {
            TrainingUserVisits::saveVisit($user_id, $training['training_id']);
            $_SESSION['training_save_uv']['training'][$training['training_id']] = true;
        }


        $sub_category = $category = false;
        if ($training['cat_id'] != 0) {
            $category = TrainingCategory::getCategory($training['cat_id']);
            if ($category && $category['parent_cat'] != 0) {
                $sub_category = $category;
                $category = TrainingCategory::getCategory($sub_category['parent_cat']);
            }
        }
        
        $canonical = $this->settings['script_url'].'/training/view/'.$tr_alias;
        $sertificate = json_decode($training['sertificate'], true);
        $have_certificate = Training::getUrlHashCertificate2User($user_id, $training['training_id']);
        $lesson_list = TrainingLesson::getLessons($training['training_id'], 0); // получаем уроки
        if ($training['entry_direction'] == 1) {
            return System::redirectUrl('/training/view/'.$training['alias']."/lesson/".$lesson_list[0]['alias']);
        }
        $section_list = TrainingSection::getSections($training['training_id']); // получить разделы из тренинга
        $block_list = TrainingBlock::getBlocks($training['training_id'], 0); // получаем обычные блоки без разделов

        $breadcrumbs = Training::getBreadcrumbs($this->tr_settings, $category, $sub_category, $training);
        $this->setPageSettings($training, 'training', 'training/index.php',
            'training-page', 'training-container'
        );

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * СТРАНИЦА МОИ ТРЕНИНГИ
     */
    public function actionMyTraining()
    {
        $user_id = intval(User::isAuth());
        if(!$user_id) {
            System::redirectUrl('/lk');
        }
        $user_groups = $user_id ? User::getGroupByUser($user_id) : false;
        $user_planes = $user_id ? Member::getPlanesByUser($user_id, 1, true) : false;
        $training_list = $user_id && ($user_groups || $user_planes) ? Training::getTrainingsToUser($user_groups, $user_planes, $user_id) : null;

        $this->setPageSettings([
                'title' => System::Lang('MY_COURSES'),
                'meta_desc' => '',
                'meta_keys' => '',
                'h1' => System::Lang('MY_COURSES')
            ],
            'my_trainings', 'users/my_trainings.php', '', 'training-list-container'
        );

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * СТРАНИЦА КУРАТОРА
     */
    public function actionCurator() {
        $curator_id = intval(User::checkLogged());
        $user = User::getUserById($curator_id);

        if (!$user['is_curator']) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }
        
        if (isset($_POST['accept'])) { // ВЫНЕСЕНИЕ ПОЛОЖИТЕЛЬНОГО РЕШЕНИЯ ДЛЯ ДЗ
            $lesson_id = isset($_POST['lesson_id']) ? $_POST['lesson_id'] : null;
            $homework_id = isset($_POST['homework_id']) ? $_POST['homework_id'] : null;
            $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
            $user_info = User::getUserById($user_id);

            $status = Traininglesson::HOMEWORK_ACCEPTED;
            $assign_to_user = isset($_POST['assign_user']) ? $_POST['assign_user'] : null;
            $result = TrainingLesson::updLessonCompleteStatus($lesson_id, $user_id, $status);
            $result_hw = TrainingLesson::updHomeworkData($homework_id, $user_id, 1, null, $curator_id);
            $training_id = Training::getTrainingIdByLessonId((int)$lesson_id);
            $training = Training::getTraining($training_id);
            $lesson = TrainingLesson::getLesson($lesson_id);
            $task = TrainingLesson::getTask2Lesson($lesson_id);

            if ($result && $assign_to_user == "1") {
                $section_list = Training::getAllSectionsByCuratorAndByTraining($curator_id, $training_id);
                foreach ($section_list as $section){
                    User::WriteCuratorsToUser($user_id, $curator_id, Training::getTrainingIdByLessonId((int)$lesson_id), (int)$section['section_id']);
                }

                User::WriteCuratorsToUser($user_id, $curator_id, Training::getTrainingIdByLessonId((int)$lesson_id), TrainingSection::getSectionByLessonId((int)$lesson_id));
            }

            if ($task['stop_lesson'] == 1 && $task['access_time'] > 1) {   
                $opendatanextlesson = TrainingLesson::getLessonStopOpenDate($training_id, $lesson_id, $user_id);
                if (isset($opendatanextlesson['start_date']) && $training['send_email_to_user_for_open_lesson'] == 1) {
                    TrainingLesson::addLetterStopOpeningLesson($opendatanextlesson['start_date'], $training, $lesson, $user_info);
                }
            }

            $type_message = null;
            if (isset($training['send_email_to_user']) && $training['send_email_to_user'] == 1) {
                Email::SendEmailFromCuratorToUser($user_info, $lesson_id, null, $type_message, $status, $user);
            }


            System::redirectUrl("/lk/curator", $result);
        }
        
        $trainings_to_curator = Training::getAllTrainingsToCurator($curator_id);
        if (!isset($_SESSION['training']['answers_filter']['training_id'])) {
            if ($trainings_to_curator) {
                $_SESSION['training']['answers_filter']['training_id'] = count($trainings_to_curator) == 1 ? $trainings_to_curator[0]['training_id']: false;
            } else {
                $_SESSION['training']['answers_filter']['training_id'] = null;
            }
        }

        if (isset($_POST['reset'])) {
            unset($_SESSION['training']['answers_filter']);
            System::redirectUrl("/lk/curator");
        }

        if (isset($_POST['filter'])) { // TODO тут надо поменять все статусы и переподвязать их из таблицы homework
            $filter_user_data = isset($_POST['user_name']) ? explode(' ', htmlentities(trim($_POST['user_name']))) : null;
            $_SESSION['training']['answers_filter'] = [
                'training_id' => isset($_POST['training_id']) ? (int)$_POST['training_id'] : null,
                'answer_type' => htmlentities($_POST['answer_type']),
                'comments_status' => isset($_POST['comments_status']) ? $_POST['comments_status'] : null,
                'lesson_complete_status' => isset($_POST['lesson_complete_status']) ? $_POST['lesson_complete_status'] : 'all',
                'lesson_id' => isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : null,
                'user_email' => htmlentities($_POST['user_email']),
                'user_name' => $filter_user_data && $filter_user_data[0] ? $filter_user_data[0] : null,
                'user_surname' => isset($filter_user_data[1]) ? $filter_user_data[1] : null,
                'curator_users' => isset($_POST['curator_users']) ? htmlentities($_POST['curator_users']) : null,
                'curator_id' => isset($_POST['curator_users']) && isset($_POST['curator_id']) && $_POST['curator_users'] == 'choose_curator'  ? (int)$_POST['curator_id'] : null,
                'start_date' => isset($_POST['start_date']) && $_POST['start_date'] ? strtotime($_POST['start_date']) : null,
                'finish_date' => isset($_POST['finish_date']) && $_POST['finish_date'] ? strtotime($_POST['finish_date']) : null,
            ];

            System::redirectUrl("/lk/curator");
        }
        if ($user['role']!="admin" && isset($_POST['filter'])) {
           $filter_user_data = isset($_POST['user_name']) ? explode(' ', htmlentities(trim($_POST['user_name']))) : null;
           $_SESSION['training']['answers_filter'] = [
               'training_id' => isset($_POST['training_id']) ? (int)$_POST['training_id'] : null,
               'answer_type' => isset($_POST['answer_type']) ? htmlentities($_POST['answer_type']) :'only_answers',
               'comments_status' => isset($_POST['comments_status']) ? $_POST['comments_status'] : 'unread',
               'lesson_complete_status' => isset($_POST['lesson_complete_status']) ? $_POST['lesson_complete_status'] : 'unchecked',
               'lesson_id' => isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : null,
               'user_email' => htmlentities($_POST['user_email']),
               'user_name' => $filter_user_data && $filter_user_data[0] ? $filter_user_data[0] : null,
               'user_surname' => isset($filter_user_data[1]) ? $filter_user_data[1] : null,
               'curator_users' => "my_users",
               'curator_id' => isset($_POST['curator_users']) && isset($_POST['curator_id']) && $_POST['curator_users'] == 'choose_curator'  ? (int)$_POST['curator_id'] : null,
               'start_date' => isset($_POST['start_date']) && $_POST['start_date'] ? strtotime($_POST['start_date']) : null,
               'finish_date' => isset($_POST['finish_date']) && $_POST['finish_date'] ? strtotime($_POST['finish_date']) : null,
           ];
        } 
        
        $filter = isset($_SESSION['training']['answers_filter']) ? $_SESSION['training']['answers_filter'] : null;
        $lesson_list = $filter && $filter['training_id'] ? TrainingLesson::getLessons($filter['training_id']) : null;

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $training_ids2curator = $trainings_to_curator ? array_column($trainings_to_curator, 'training_id') : null;
        $total = TrainingLesson::getTotalAnswers($filter, $curator_id, $training_ids2curator);
        $answer_list = TrainingLesson::getAnswerList($filter, $page, $this->settings['show_items'], $curator_id, $training_ids2curator);
        $pagination = new Pagination($total, $page, $this->settings['show_items']);

        $this->setPageSettings([
                'title' => 'Кабинет куратора курсов',
                'meta_desc' => '',
                'meta_keys' => '',
                'h1' => ''
            ], 'lk', 'users/curator.php', 'invert-page', 'cabinet-lk'
        );

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * УДАЛИТЬ СООБЩЕНИЕ В ДИАЛОГЕ
     * @param $id
     */
    public function actionDelmessage($id)
    {
        $curator_id = intval(User::checkLogged());
        $user = User::getUserById($curator_id);

        if (!$user['is_curator']) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }
        $attachments = TrainingLesson::getComment($id);
        $del = TrainingLesson::delMessage($id);
        if ($del) {
            // удаляем вложения если были в комментарии
            if (isset($attachments['attach'])){
                foreach(json_decode($attachments['attach'], true) as $attach) {
                    if ($attach['path']) {
                        $path = ROOT . "/" . urldecode($attach['path']);
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }

            }

            System::redirectUrl($_SERVER['HTTP_REFERER'], $del);
        }
    }


    /**
     * УДАЛИТЬ ДОМАШНЮЮ РАБОТУ
     * @param $id
     */
    public function actionDelHomework($id)
    {
        $curator_id = intval(User::checkLogged());
        $user = User::getUserById($curator_id);

        if (!$user['is_curator'] && $this->tr_settings['allow_del_homework']) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $del = TrainingLesson::delHomework($id);
        if ($del) {
            System::redirectUrl('/lk/curator/', $del);
        }
    }


    /**
     * СТРАНИЦА ОТВЕТА НА ДЗ В КУРАТОРСКОЙ
     * @param $homework_id
     * @param $user_id
     * @param $lesson_id
     * @throws Exception
     */
    public function actionAnswer($homework_id, $user_id, $lesson_id) {
        $curator_id = intval(User::checkLogged());
        $curator = User::getUserById($curator_id);
        $answer_user_id = $user_id;

        if (!$curator['is_curator']) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $user_info = User::getUserById($user_id);
        $user_groups = User::getGroupByUser($user_id);
        $user_planes = Member::getPlanesByUser($user_id);

        // Назначеный юзеру куртор 
        $assign_curator = Training::getCuratorToUserByLessonId($lesson_id, $user_id);
        
        $training_id = Training::getTrainingIdByLessonId((int)$lesson_id);
        $training = Training::getTraining($training_id);
        $lesson = TrainingLesson::getLesson($lesson_id);
        $task = TrainingLesson::getTask2Lesson($lesson_id);

        // Здесь принятие задания с автоответом 
        if (isset($_GET['auto']) && $_GET['auto'] == 1) {
            $status = Traininglesson::HOMEWORK_ACCEPTED;
            $auto_reply = base64_encode(htmlentities($task['auto_answer']));
            $write = TrainingLesson::writeComment($homework_id, $curator_id, 0, $auto_reply, 2, null);
            $result = TrainingLesson::updLessonCompleteStatus($lesson_id, $user_id, $status);
            $result_hw = TrainingLesson::updHomeworkData($homework_id, $user_id, 1, null, $curator_id);

            if ($task['stop_lesson'] == 1 && $task['access_time'] > 1) {
                $opendatanextlesson = TrainingLesson::getLessonStopOpenDate($training_id, $lesson_id, $user_id);
                if (isset($opendatanextlesson['start_date']) && $training['send_email_to_user_for_open_lesson'] == 1) {
                    TrainingLesson::addLetterStopOpeningLesson($opendatanextlesson['start_date'], $training, $lesson, $user_info);
                }
            }

            $type_message = null;
            if ($training['send_email_to_user'] == 1) {
                Email::SendEmailFromCuratorToUser($user_info, $lesson_id, null, $type_message, $status, $curator);
            }

            System::redirectUrl("/lk/curator", $result);
        }

        // TODO пока убрали вложения в ответах куратора, если критично то нужно добавить в таблицу поле для этого   
        //$attach = TrainingLesson::uploadAttach2Answer($_FILES['lesson_attach'], $lesson_id, Training::USER_TYPE_CURATOR);
            

        // тут принятие без коментария или прям из списка ответов!!!
        if (isset($_POST['accept'])) { // ВЫНЕСЕНИЕ ПОЛОЖИТЕЛЬНОГО РЕШЕНИЯ ДЛЯ ДЗ
            $status = Traininglesson::HOMEWORK_ACCEPTED;
            $assign_to_user = isset($_POST['assign']) ? $_POST['assign'] : null;
            $result = TrainingLesson::updLessonCompleteStatus($lesson_id, $user_id, $status);
            $result_hw = TrainingLesson::updHomeworkData($homework_id, $user_id, 1, null, $curator_id);
            if ($result && $assign_to_user == "1") {
                $section_list = Training::getAllSectionsByCuratorAndByTraining($curator_id, $training_id);
                if ($section_list) {
                    foreach ($section_list as $section){
                        User::WriteCuratorsToUser($user_id, $curator_id, $training_id, (int)$section['section_id']);
                    }
                }
                User::WriteCuratorsToUser($user_id, $curator_id, $training_id, TrainingSection::getSectionByLessonId((int)$lesson_id));
            }
            
            if ($task['stop_lesson'] == 1 && $task['access_time'] > 1) {
                $opendatanextlesson = TrainingLesson::getLessonStopOpenDate($training_id, $lesson_id, $user_id);
                if (isset($opendatanextlesson['start_date']) && $training['send_email_to_user_for_open_lesson'] == 1) {
                    TrainingLesson::addLetterStopOpeningLesson($opendatanextlesson['start_date'], $training, $lesson, $user_info);
                }
            }

            $type_message = null;
            if ($training['send_email_to_user'] == 1) {
                Email::SendEmailFromCuratorToUser($user_info, $lesson_id, null, $type_message, $status, $curator);
            }

            System::redirectUrl("/lk/curator", $result);
        }

        if (isset($_GET['testtry'])) { // тут даем попытку на еще одно прохождение теста
            $last_answer = TrainingLesson::getAnswer($homework_id);
            $result = TrainingTest::deleteResultsAnswersUsers($lesson_id, $user_id, $last_answer);
            $type_message = 'Вам предоставлена еще одна попытка на прохождения теста.';
            if ($training['send_email_to_user'] == 1) {
                Email::SendEmailFromCuratorToUser($user_info, $lesson_id, null, $type_message, null, $curator);
            }

            System::redirectUrl("/lk/curator", $result);
        }

        $last_answer = TrainingLesson::getAnswer($homework_id);
        $test_result = TrainingTest::getTestResult($lesson_id, $user_id);
        
        if (!$last_answer && !$test_result) {
            require_once (ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $answer_list = TrainingLesson::getAnswers2Lesson($lesson['lesson_id'], $user_id, $homework_id);
        $lesson_complete_status = TrainingLesson::getLessonCompleteStatus($lesson_id, $user_id);
 
        if (isset($_POST['post_message'])) { // ОТВЕТ КУРАТОРА
            $reply = base64_encode(htmlentities($_POST['reply']));
            $attach = null;
            if (isset($_FILES['lesson_attach']) && !empty($_FILES['lesson_attach'])) {
                $attach = TrainingLesson::uploadAttach2Answer($_FILES['lesson_attach'], $lesson_id, Training::USER_TYPE_CURATOR);
            }
            $write = !empty($reply) ? TrainingLesson::writeComment($homework_id, $curator_id, 0, $reply, 2, $attach) : true;

            if ($write) {
                if ($_POST['status_send_complete']) {
                    $status = $_POST['status_send_complete'] == 1 ? Traininglesson::HOMEWORK_ACCEPTED : $_POST['status_send_complete'];
                    $status_hw = $_POST['status_send_complete'] == "1" ? Traininglesson::HOME_WORK_ACCEPTED : Traininglesson::HOME_WORK_DECLINE;
                    $assign_to_user = isset($_POST['assign_user']) ? $_POST['assign_user'] : null;
                    $res = TrainingLesson::updLessonCompleteStatus($lesson_id, $user_id, $status);
                    $result_hw = TrainingLesson::updHomeworkData($homework_id, $user_id, $status_hw, null, $curator_id);

                    if ($res && $assign_to_user == "on") {
                        $section_list = Training::getAllSectionsByCuratorAndByTraining($curator_id, $training_id);
                        if ($section_list) {
                            foreach ($section_list as $section){
                                User::WriteCuratorsToUser($user_id, $curator_id, $training_id, (int)$section['section_id']);
                            }
                        }
                        User::WriteCuratorsToUser($user_id, $curator_id, $training_id, TrainingSection::getSectionByLessonId((int)$lesson_id));
                    }

                    if ($task['stop_lesson'] == 1 && $task['access_time'] > 1 && $status_hw == Traininglesson::HOME_WORK_ACCEPTED) {
                        $opendatanextlesson = TrainingLesson::getLessonStopOpenDate($training_id, $lesson_id, $user_id);
                        if (isset($opendatanextlesson['start_date']) && $training['send_email_to_user_for_open_lesson'] == 1) {
                            TrainingLesson::addLetterStopOpeningLesson($opendatanextlesson['start_date'], $training, $lesson, $user_info);
                        }
                    }

                }

                if (isset($_POST['send_email_to_user']) && $training['send_email_to_user'] == 1){
                    $type_message = $reply ?: '';
                    $status = $_POST['status_send_complete'] ?: '';
                    Email::SendEmailFromCuratorToUser($user_info, $lesson_id, null, $type_message, $status, $curator);
                }
            }

            if ($_POST['status_send_complete']) {
                System::redirectUrl('/lk/curator/', $write);
            } else {
                System::redirectUrl("/lk/curator/answers/$homework_id/$user_id/$lesson_id", $write, '#curator_answer');
            }
        }

        if ($answer_list) {
            // Здесь ставим временный статус, что бы другие кураторы не могли видеть ответы.
            if ($answer_list[0]['status'] == TrainingLesson::HOME_WORK_SEND) {
                TrainingLesson::updateStatusAnswer($homework_id, TrainingLesson::HOME_WORK_IN_VERIFICATION, $curator_id);
            }
        }

        // Тут обновляем статусы комментариев на прочитаные, если они есть.
        TrainingLesson::updateStatusAllCommentsByHomework($homework_id);
        $trainings_to_curator = Training::getAllTrainingsToCurator($curator_id);
        $training_id = Training::getTrainingIdByLessonId($lesson_id);
        $lesson_list = TrainingLesson::getLessons($training_id);

        $this->setPageSettings([
            'title' => 'Кабинет куратора курсов',
            'meta_desc' => '',
            'meta_keys' => '',
            'h1' => ''
        ], 'lk', 'users/answer.php', 'invert-page', 'cabinet-lk'
        );

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * СТРАНИЦА РЕДАКТИРОВАНИЯ ОТВЕТА НА ДЗ
     */
    public function actionEditAnswer() {
        $user_id = intval(User::checkLogged());
        if (!$user_id) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        if (isset($_POST['answer']) && isset($_POST['answer_id'])) {
            $answer_id = (int)$_POST['answer_id'];
            $answer = TrainingLesson::getAnswer($answer_id);
            $lesson = TrainingLesson::getLesson($answer['lesson_id']);
            $training = Training::getTraining($lesson['training_id']);
            $lesson_complete_status = TrainingLesson::getLessonCompleteStatus($answer['lesson_id'], $user_id);

            if ($answer && TrainingLesson::isAllowEditAnswer($training, $lesson_complete_status, $answer)) {
                $user_groups = User::getGroupByUser($user_id);
                $user_planes = Member::getPlanesByUser($user_id, 1, true);
                $section = $lesson['section_id'] ? TrainingSection::getSection($lesson['section_id']) : null;

                $access = Training::getAccessData($user_groups, $user_planes, $training, $section, $lesson);
                if (!Training::checkUserAccess($access)) {
                    $this->setPageSettings($training, 'training', 'layouts/no_access.php', 'no-access-page inner-training-page');
                    require_once ("{$this->template_path}/main3.php");
                    exit;
                }

                if (isset($_POST['del_attach']) && $_POST['del_attach'] && $answer['attach']) {
                    $attachments = json_decode($answer['attach'], true);
                    $del_attachments = explode(';', $_POST['del_attach']);

                    foreach ($attachments as $key => $attach) {
                        if (in_array($attach['name'], $del_attachments)) {
                           unset($attachments[$key]);
                        }
                    }
                    $answer['attach'] = json_encode($attachments);
                }

                if (isset($_FILES['lesson_attach']) && !empty($_FILES['lesson_attach'])) {
                    $attach = TrainingLesson::uploadAttach2Answer($_FILES['lesson_attach'], $lesson['lesson_id'], Training::USER_TYPE_USER, $answer['attach']);
                } else {
                    $attach = $answer['attach'];
                }

                $work_link = isset($_POST['work_link']) ? $_POST['work_link'] : null;

                $answer_msg = base64_encode(htmlentities($_POST['answer']));
                TrainingLesson::updAnswer($answer_id, $answer_msg, $attach, $answer['lesson_id'], $user_id, $work_link);
            }

            System::redirectUrl("/training/view/{$training['alias']}/lesson/{$lesson['alias']}", null, '#tr_answer_list');
        }
    }


    /**
     * СТРАНИЦА РЕДАКТИРОВАНИЯ КОММЕНТАРИЯ К ДЗ
     * @throws Exception
     */
    public function actionEditComment() {
        $user_id = intval(User::checkLogged());
        if (!$user_id) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        if (isset($_POST['comment']) && isset($_POST['comment_id']) && isset($_POST['lesson_id'])) {
            $comment_id = (int)$_POST['comment_id'];
            $comment = TrainingLesson::getComment($comment_id);
            $lesson_id = (int)$_POST['lesson_id'];
            $lesson = TrainingLesson::getLesson($lesson_id);
            $training = Training::getTraining($lesson['training_id']);

            if (!$comment || $comment['status']) {
                System::redirectUrl("/training/view/{$training['alias']}/lesson/{$lesson['alias']}");
            }

            $user_groups = User::getGroupByUser($user_id);
            $user_planes = Member::getPlanesByUser($user_id, 1, true);
            $section = $lesson['section_id'] ? TrainingSection::getSection($lesson['section_id']) : null;

            $access = Training::getAccessData($user_groups, $user_planes, $training, $section, $lesson);
            if (!Training::checkUserAccess($access)) {
                $this->setPageSettings($training, 'training', 'layouts/no_access.php', 'no-access-page inner-training-page');
                require_once ("{$this->template_path}/main3.php");
                exit;
            }

            if (isset($_FILES['lesson_attach']) && !empty($_FILES['lesson_attach'])) {
                $attach = TrainingLesson::uploadAttach2Answer($_FILES['lesson_attach'], $lesson['lesson_id'], Training::USER_TYPE_USER, $comment['attach']);
            } else {
                $attach = $_POST['current_attach'];
            }

            $comment = base64_encode(htmlentities($_POST['comment']));
            TrainingLesson::updComment($comment_id, $comment, $attach);

            System::redirectUrl("/training/view/{$training['alias']}/lesson/{$lesson['alias']}", null, "#tr_comment_{$comment_id}");
        }
    }


    /**
     * СТРАНИЦА РЕДАКТИРОВАНИЯ ОТВЕТА КУРАТОРА
     */
    public function actionEditCuratorComment() {
        $curator_id = intval(User::checkLogged());
        $curator = $curator_id ? User::getUserById($curator_id) : null;

        if (!$curator || !$curator['is_curator']) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        if (isset($_POST['comment']) && isset($_POST['comment_id']) && isset($_POST['lesson_id'])) {
            $comment_id = (int)$_POST['comment_id'];
            $comment = TrainingLesson::getComment($comment_id);
            $lesson_id = (int)$_POST['lesson_id'];
            $lesson = TrainingLesson::getLesson($lesson_id);
            $training = Training::getTraining($lesson['training_id']);


            if (isset($_FILES['lesson_attach']) && !empty($_FILES['lesson_attach'])) {
                $attach = TrainingLesson::uploadAttach2Answer($_FILES['lesson_attach'], $lesson_id, Training::USER_TYPE_CURATOR);
            } else {
                $attach = $_POST['current_attach'];
            }

            $comment = base64_encode(htmlentities($_POST['comment']));
            $res = TrainingLesson::updComment($comment_id, $comment, $attach);

            System::redirectUrl("/lk/curator/answers/{$_POST['homework_id']}/{$_POST['user_id']}/{$lesson['lesson_id']}",
                $res, '#curator_answer'
            );
        }
    }


    /**
     * СТРАНИЦА ВЫБОРА ТАРИФА (Продуктов)
     * @param $training_id
     * @param $hw_access // Уровень доступа к ДЗ (2 - проверка Куратором, 1 - автопроверка, 0 - самостоятельная)
     */
    public function actionOptions($training_id, $hw_access = false)
    {
        $training_id = intval($training_id);
        $training = Training::getTraining($training_id);

        if (!$training) {
            require_once (ROOT . "/template/{$this->settings['template']}/404.php");
        }
        
        $user_id = intval(User::isAuth());

        if ($hw_access !== false) {
            if ($hw_access == 2) {
                $list_product = json_decode($training['by_button_curator_hw'], true)['rate'];
            } elseif ($hw_access == 1) {
                $list_product = json_decode($training['by_button_autocheck_hw'], true)['rate'];
            } elseif (intval($hw_access) === 0) {                
                $list_product = json_decode($training['by_button_self_hw'], true)['rate'];
            }
        } else {
            $big_button = json_decode($training['big_button'], true);
            $small_button = json_decode($training['small_button'], true);
            $small_button_rate = isset($small_button['rate']) ? $small_button['rate'] : null;
            $list_product = isset($big_button['rate']) ? $big_button['rate'] : $small_button_rate;
        }
        
        $h1 = $training['name'];
        $lesson = $section = null;
        $sub_category = $category = false;

        if ($training['cat_id'] != 0) {
            $category = TrainingCategory::getCategory($training['cat_id']);
            if ($category && $category['parent_cat'] != 0) {
                $sub_category = $category;
                $category = TrainingCategory::getCategory($sub_category['parent_cat']);
            }
        }

        $this->setSEOParams('Выберите вариант');
        $this->setViewParams('', 'layouts/rates.php', null, null, 'training-options-page inner-training-page');

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * ПОКАЗАТЬ СЕРТИФИКАТ ПОЛЬЗОВАТЕЛЯ ПО ССЫЛКЕ 
     * @param $hash_url
     */
    public function actionShowCertificate($hash_url)
    {
        if ($hash_url){
            Training::ShowCertificateByUrl($hash_url);
        }
    }
}