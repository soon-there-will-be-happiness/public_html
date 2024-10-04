<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/extensions/training/layouts/admin/admin-head.php');?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');
$group_list = User::getUserGroups();
$product_list = Product::getProductListOnlySelect();?>

<div class="main">
    <div class="top-wrap">
        <h1>Настройка урока для <?=$training['name'];?></h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout/" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/training/">Тренинги</a></li>
        <li><a href="/admin/training/structure/<?=$training_id;?>">Структура</a></li>
        <li>Редактировать урок</li>
    </ul>

    <form action="" method="POST" enctype="multipart/form-data" id="edit_lesson_form">
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">

        <?if(isset($_GET['success'])):?>
            <div class="admin_message">Успешно!</div>
        <?endif;?>

        <?if(callpassword::hasError()) CallPassword::showError();?>

        <div class="admin_top admin_top-flex">
            <h3 class="lesson-title">Редактировать урок <?=$lesson['name'];?>
                <a target="_blank" class="link2front-lesson" title="Страница урока" href="/training/view/<?=$training['alias']?>/lesson/<?=$lesson['alias']?>">
                    <i class="icon-exit-top-right"></i>
                </a>
            </h3>
            <ul class="nav_button">
                <li><input type="submit" name="editless" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/training/structure/<?=$training_id;?>">Закрыть</a>
                </li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Урок</li>
                <li>Задание</li>
                <li>Тесты</li>
                <li>Доступ</li>
                <li>Внешний вид</li>
                <li>SEO</li>
            </ul>

            <div class="admin_form">
                <!-- 1 вкладка Урок-->
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Общие настройки</h4>
                        </div>

                        <div class="col-1-2">
                            <p class="width-100"><label>Название</label>
                                <input type="text" value="<?=$lesson['name'];?>" name="name" placeholder="Название урока" required="required">
                            </p>

                            <div class="width-100" id="section_box"><label>Раздел</label>
                                <div class="select-wrap">
                                    <select name="section_id">
                                        <option value="0">Без раздела</option>
                                        <?$sections = TrainingSection::getSections($training_id);
                                        if($sections):
                                            foreach($sections as $section):?>
                                                <option value="<?=$section['section_id']?>"<?if($section['section_id'] == $lesson['section_id']) echo ' selected="selected"';?>>
                                                    <?=$section['name'];?>
                                                </option>
                                            <?endforeach;
                                        endif;?>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100"><label>Блок</label>
                                <div class="select-wrap">
                                    <select name="block_id">
                                        <option value="">Без блока</option>
                                        <?$block_list = TrainingBlock::getBlocks($training_id, null, null);
                                        if($block_list):
                                            foreach($block_list as $block):?>
                                                <option value="<?=$block['block_id']?>"<?if($block['block_id'] == $lesson['block_id']) echo ' selected="selected"';?> data-show_off="section_box">
                                                <?=$block['name'];?>
                                            </option>
                                        <?endforeach;
                                        endif;?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Статус</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1"<?if($lesson['status']) echo ' checked';?>>
                                        <span>Вкл</span>
                                    </label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0"<?if(!$lesson['status']) echo ' checked';?>>
                                        <span>Выкл</span>
                                    </label>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!--  ЭЛЕМЕНТЫ УРОКА NEW -->
                    <?require_once(__DIR__ . '/elements/list.php');?>
                </div>


                <!-- 2 ВКЛАДКА ЗАДАНИЕ -->
                <div>
                    <div class="row-line mb-0">
                        <div class="col-1-1 mb-0">
                            <h4>Настройки задания</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Тип задания</label>
                                <div class="select-wrap">
                                    <select name="task[task_type]">
                                        <option value="0"<?if($task && $task['task_type'] == 0) echo ' selected="selected"';?> data-show_off="custom-homework,tests-settings" data-show_on="auto_access_lesson">Нет задания</option>
                                        <option value="1"<?if($task && $task['task_type'] == 1) echo ' selected="selected"';?> data-show_off="access_type3,access_type1,tests-settings" data-show_on="on_public_homework">Задание</option>
                                        <option value="2"<?if($task && $task['task_type'] == 2) echo ' selected="selected"';?> data-show_off="tests-off-info" data-show_on="on_public_homework">Задание + тест</option>
                                        <option value="3"<?if($task && $task['task_type'] == 3) echo ' selected="selected"';?> data-show_off="access_type2,access_type3,show_homework_text,show_homework_text1,show_homework_text2,tests-off-info">Тест</option>
                                    </select>
                                </div>
                            </div>

                            <div id="on_public_homework" class="width-100 hidden"><label>Домашняя работа видна всем пользователям?</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="public_homework[status]" type="radio" value="1" data-show_on="public_homework_statuses,public_homework_user_choose"<?if($public_homework_settings && $public_homework_settings['status']) echo ' checked="checked"';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio"><input name="public_homework[status]" type="radio" value="0"<?if(!$public_homework_settings || !$public_homework_settings['status']) echo ' checked="checked"';?>>
                                        <span>Нет</span>
                                    </label>
                                </span>

                                <div id="public_homework_statuses" class="width-100 hidden mt-20"><label>Сделать работу публичной со следующими статусами</label>
                                    <select class="multiple-select" name="public_homework[statuses][]" multiple="multiple">
                                        <option value="<?=TrainingLesson::HOME_WORK_SEND;?>"<?if($public_homework_settings['statuses'] && in_array(TrainingLesson::HOME_WORK_SEND, $public_homework_settings['statuses'])) echo ' selected="selected"';?>>Отправлено</option>
                                        <option value="<?=TrainingLesson::HOME_WORK_IN_VERIFICATION;?>"<?if($public_homework_settings['statuses'] && in_array(TrainingLesson::HOME_WORK_IN_VERIFICATION, $public_homework_settings['statuses'])) echo ' selected="selected"';?>>На проверке</option>
                                        <option value="<?=TrainingLesson::HOME_WORK_ACCEPTED;?>"<?if($public_homework_settings['statuses'] && in_array(TrainingLesson::HOME_WORK_ACCEPTED, $public_homework_settings['statuses'])) echo ' selected="selected"';?>>Принято</option>
                                    </select>
                                </div>

                                <div id="public_homework_user_choose" class="width-100 mt-20 hidden"><label>Разрешить пользователю выбирать будет ли домашняя работа публичной</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input  name="public_homework[user_choose]" type="radio" value="1"<?if($public_homework_settings && $public_homework_settings['user_choose']) echo ' checked="checked"';?>>
                                            <span>Да</span>
                                        </label>
                                        <label class="custom-radio"><input name="public_homework[user_choose]" type="radio" value="0"<?if(!$public_homework_settings || !$public_homework_settings['user_choose']) echo ' checked="checked"';?>>
                                            <span>Нет</span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div id="auto_access_lesson" class="width-100 hidden"><label>Как учитывать прохождение урока?</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input  name="auto_access_lesson" type="radio" value="1" <?if($lesson && $lesson['auto_access_lesson'] == 1) echo ' checked="checked"';?>>
                                        <span>Вход в урок</span>
                                    </label>
                                    <label class="custom-radio"><input name="auto_access_lesson" type="radio" value="0" <?if($lesson && $lesson['auto_access_lesson'] == 0) echo ' checked="checked"';?>>
                                        <span>Нажатие на кнопку</span>
                                    </label>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div id="custom-homework" class="mt-0">
                        <div id="show_homework_text1" class="row-line mb-0">
                            <div class="col-1-1 mb-0">
                                <h4>Настройки проверки</h4>
                            </div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Тип проверки</label>
                                    <div class="select-wrap">
                                        <select name="task[check_type]">
                                            <option value="2"<?if($task && $task['check_type'] == 2) echo ' selected="selected"';?>>Проверка куратором</option>
                                            <option value="0"<?if($task && $task['check_type'] == 0) echo ' selected="selected"';?>>Самостоятельная проверка</option>
                                            <option value="1"<?if($task && $task['check_type'] == 1) echo ' selected="selected"';?>>Автопроверка</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="delay-check" class="col-1-2">
                                <p class="width-100 min-label-wrap"><label>Задержка автопроверки<span class="min-label">мин.</span></label>
                                    <input value="<?if($task) echo $task['autocheck_time'];?>" type="text" name="task[autocheck_time]">
                                </p>
                            </div>
                        </div>

                        <div class="row-line mt-0">
                            <div class="col-1-2">
                                <div id="show_homework_text2" class="width-100 status-disabled"><label>Статус проверки</label>
                                    <div class="status-disabled-answer">Выбор будет доступен в следующей версии</div>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input  name="task[check_status_type]" type="radio" value="1" checked disabled<?if($task && $task['check_status_type'] == 1) echo ' checked="checked"';?>>
                                            <span>Зачет</span>
                                        </label>
                                        <label class="custom-radio"><input name="task[check_status_type]" type="radio" value="2" disabled<?if($task && $task['check_status_type'] == 2) echo ' checked="checked"';?>>
                                            <span>Баллы</span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div class="col-1-2"></div>

                            <div class="col-1-2">
                                <div class="width-100">
                                    <label class="custom-chekbox-wrap">
                                        <input type="checkbox" value="1" name="task[stop_lesson]"<?if($task && $task['stop_lesson']) echo ' checked="checked"';?> data-show_on="stop_lesson_show">
                                        <span class="custom-chekbox"></span>Стоп-урок
                                    </label>
                                </div>
                            </div>
                            <!-- TODO Будет реализовано в тренингах 3.0
                            <div class="col-1-2" style="display: none">
                                <div class="width-100"><label>Время на выполнение</label>
                                    <input type="text" name="task[perform_time]">
                                </div>
                            </div>
                            -->
                        </div>

                        <div id="stop_lesson_show" class="row-line mt-10 hidden">
                            <div class="col-1-2">
                                <div class="width-100"><label>Предел действия:</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input  name="task[stop_lesson_vastness]" type="radio" value="1" checked<?if($task && $task['stop_lesson_vastness'] == 1) echo ' checked="checked"';?>>
                                            <span>Весь тренинг</span>
                                        </label>
                                        <label class="custom-radio"><input name="task[stop_lesson_vastness]" type="radio" value="2"<?if($task && $task['stop_lesson_vastness'] == 2) echo ' checked="checked"';?>>
                                            <span>Только раздел</span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="col-1-2"></div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Для прохождения урока необходимо</label>
                                    <div class="select-wrap">
                                        <select name="task[access_type]">
                                            <option id="access_type1" value="1"<?if($task && $task['access_type'] == 1) echo ' selected="selected"';?>>Прохождение теста</option>
                                            <option id="access_type2" value="2"<?if($task && $task['access_type'] == 2) echo ' selected="selected"';?>>Выполнение задания</option>
                                            <option id="access_type3" value="3"<?if($task && $task['access_type'] == 3) echo ' selected="selected"';?>>Тест и задание</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Как быстро открыть доступ?</label>
                                    <div class="select-wrap">
                                        <select name="task[access_time]">
                                            <option value="1"<?if($task && $task['access_time'] == 1) echo ' selected="selected"';?>>Сразу</option>
                                            <option value="2"<?if($task && $task['access_time'] == 2) echo ' selected="selected"';?>>На следующий день</option>
                                            <option value="3"<?if($task && $task['access_time'] == 3) echo ' selected="selected"';?> data-show_on="task_access_time_days">Через X дней</option>
                                            <option value="4"<?if($task && $task['access_time'] == 4) echo ' selected="selected"';?> data-show_on="task_access_time_weekday">Дождаться дня недели</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="px-label-wrap hidden" id="task_access_time_days"><label>Количество дней<span class="px-label">дн.</span></label>
                                    <input type="text" name="task[access_time_days]"  value="<?if($task) echo $task['access_time_days'];?>">
                                </div>

                                <div class="width-100 mt-20 hidden" id="task_access_time_weekday"><label>День недели</label>
                                    <div class="select-wrap">
                                        <select name="task[access_time_weekday]">
                                            <option value="1"<?if($task && $task['access_time_weekday'] == 1) echo ' selected="selected"';?>>Пн.</option>
                                            <option value="2"<?if($task && $task['access_time_weekday'] == 2) echo ' selected="selected"';?>>Вт.</option>
                                            <option value="3"<?if($task && $task['access_time_weekday'] == 3) echo ' selected="selected"';?>>Ср.</option>
                                            <option value="4"<?if($task && $task['access_time_weekday'] == 4) echo ' selected="selected"';?>>Чт.</option>
                                            <option value="5"<?if($task && $task['access_time_weekday'] == 5) echo ' selected="selected"';?>>Пт.</option>
                                            <option value="6"<?if($task && $task['access_time_weekday'] == 6) echo ' selected="selected"';?>>Сб.</option>
                                            <option value="7"<?if($task && $task['access_time_weekday'] == 7) echo ' selected="selected"';?>>Вс.</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="show_homework_text" class="row-line mt-10">
                            <div class="col-1-1">
                                <div class="width-100"><h4>Текст задания</h4>
                                    <textarea class="editor" name="task[text]"><?if($task) echo $task['text'];?></textarea>
                                </div>
                            </div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Поле загрузить файл</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input name="task[show_upload_file]" type="radio" value="1"<?if($task && $task['show_upload_file']) echo ' checked="checked"';?>>
                                            <span>Да</span>
                                        </label>
                                        <label class="custom-radio"><input name="task[show_upload_file]" type="radio" value="0"<?if(!$task['show_upload_file']) echo ' checked="checked"';?>>
                                            <span>Нет</span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Поле ссылка</label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input name="task[show_work_link]" type="radio" value="1"<?if($task && $task['show_work_link']) echo ' checked="checked"';?>>
                                            <span>Да</span>
                                        </label>
                                        <label class="custom-radio"><input name="task[show_work_link]" type="radio" value="0"<?if(!$task['show_work_link']) echo ' checked="checked"';?>>
                                            <span>Нет</span>
                                        </label>
                                    </span>
                                </div>
                            </div>

                            <div class="col-1-1 mt-10">
                                <div class="width-100"><h4>Автоответ</h4>
                                    <textarea class="editor" name="task[auto_answer]"><?if($task) echo $task['auto_answer'];?></textarea>
                                </div>
                            </div>

                            <div class="col-1-1 mt-10">
                                <div class="width-100"><h4>Подсказка куратору</h4>
                                    <textarea class="editor" name="task[hint]"><?if($task) echo $task['hint'];?></textarea>
                                </div>
                            </div>
                        </div>


                        <?/*ToDo убрать после раскомментирования ниже кода*/?>
                        <input type="hidden" value="0" name="task[completed_on_time]">
                        <input type="hidden" value="0" name="task[not_completed_on_time]">

                        <?/*<div class="row-line">
                            <div class="col-1-1 mb-0">
                                <h4>События</h4>
                            </div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Выполнил вовремя</label>
                                    <div class="select-wrap">
                                        <select name="task[completed_on_time]">
                                            <option value="1"<?if($task && $task['completed_on_time'] == 1) echo ' selected="selected"';?>>Ничего</option>
                                            <option value="2"<?if($task && $task['completed_on_time'] == 2) echo ' selected="selected"';?>>Начислить баллы</option>
                                            <option value="3"<?if($task && $task['completed_on_time'] == 3) echo ' selected="selected"';?>>Отправить сообщение</option>
                                            <option value="4"<?if($task && $task['completed_on_time'] == 4) echo ' selected="selected"';?> data-show_on="task_completed_time_add_group">Добавить в группу</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="width-100 hidden" id="task_completed_time_add_group"><label>Добавить группы пользователю</label>
                                    <div class="select-wrap">
                                        <select name="task[completed_time_add_group]">
                                            <?if($group_list):
                                                    $add_groups = $task && $task['completed_time_add_group'] ? json_decode($task['completed_time_add_group'], true) : null;
                                                    foreach($group_list as $group):

                                                        //print_r(json_decode($task['completed_time_add_group'], 1));exit;

                                                        ?>
                                            <option value="<?=$group['group_id'];?>"<?if($add_groups && in_array($group['group_id'], $add_groups)) echo ' selected="selected"';?>><?=$group['group_title'];?></option>
                                            <?endforeach;
                                                endif;?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-1-2">
                                <div class="width-100"><label>Не выполнил вовремя</label>
                                    <div class="select-wrap">
                                        <select name="task[not_completed_on_time]">
                                            <option value="1"<?if($task && $task['not_completed_on_time'] == 1) echo ' selected="selected"';?>>Ничего</option>
                                            <option value="2"<?if($task && $task['not_completed_on_time'] == 2) echo ' selected="selected"';?>>Снять баллы</option>
                                            <option value="3"<?if($task && $task['not_completed_on_time'] == 3) echo ' selected="selected"';?>>Отправить сообщение</option>
                                            <option value="4"<?if($task && $task['not_completed_on_time'] == 4) echo ' selected="selected"';?> data-show_on="task_completed_time_del_group">Удалить из группы</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="width-100 hidden" id="task_completed_time_del_group"><label>Удалить группы пользователя</label>
                                <div class="select-wrap">
                                    <select name="task[completed_time_del_group]">
                                        <?if($group_list):
                                                $del_groups = $task && $task['completed_time_del_group'] ? json_decode($task['completed_time_del_group'], true) : null;
                                                foreach($group_list as $group):?>
                                        <option value="<?=$group['group_id'];?>"<?if($del_groups && in_array($group['group_id'], $del_groups)) echo ' selected="selected"';?>><?=$group['group_title'];?></option>
                                        <?endforeach;
                                            endif;?>
                                    </select>
                                </div>
                            </div>
                        </div>*/?>
                    </div>

                    <?/*<div class="row-line" style="background-color: blue;">
                        <div class="col-1-1 mb-0">
                            <h4>Передача данных</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Воронка Amocrm</label>
                                <div class="select-wrap">
                                    <select name="task[amocrm]">
                                        <option value="">Марафон</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Chatforma</label>
                                <div class="select-wrap">
                                    <select name="task[chatforma]">
                                        <option value="">Марафон</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>*/?>
                </div>


                <!-- 3 ВКЛАДКА ТЕСТЫ-->
                <div>
                    <div class="row-line" id="tests-off-info">
                        <div class="col-1-1">
                            <p>Настройки будут доступны после выбора типа задания с тестами.</p>
                        </div>
                    </div>

                    <div class="row-line mt-0" id="tests-settings">
                        <div class="col-1-1 mb-0">
                            <h4>Настройка теста</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="ball-label-wrap width-100">
                                <label>Считать тест пройденным, если<br> пользователь набирает
                                    <span class="ball-label">баллов</span>
                                </label>
                                <input type="text" value="<?=$test['finish'];?>" name="test_finish" placeholder="кол-во баллов">
                            </div>

                            <div class="width-100">
                                <label>Количество попыток для сдачи</label>
                                <input type="number" min="1" max="127" value="<?=$test['test_try'];?>" name="test_try">
                            </div>

                            <div class="min-label-wrap width-100">
                                <label>Время на прохождение теста
                                    <span class="min-label">мин.</span>
                                </label>
                                <input type="text" value="<?=$test['test_time'];?>" name="test_time">
                            </div>

                            <div class="width-100">
                                <label>Сколько вопросов выводить?</label>
                                <input type="number" max="1024" value="<?=$test['show_questions_count'];?>" name="show_questions_count">
                            </div>

                            <div class="width-100">
                                <label>Использовать рандомный вывод вопросов</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="is_random_questions" type="radio" value="1"<?if($test['is_random_questions'] == 1) echo ' checked="checked"';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="is_random_questions" type="radio" value="0"<?if($test['is_random_questions'] == 0) echo ' checked="checked"';?>>
                                        <span>Нет</span>
                                    </label>
                                </span>
                            </div>

                            <div class="width-100"><label>Открывать тест автоматически</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="auto_start" type="radio" value="1" <?if(@$test['auto_start'] == 1) echo 'checked="checked"';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="auto_start" type="radio" value="0" <?if(@$test['auto_start'] != 1) echo 'checked="checked"';?>>
                                        <span>Нет</span>
                                    </label>
                                </span>
                            </div>

                            <div class="width-100"><label title="Возможность пересдавать тест (независимо от количества попыток для пересдачи и результата теста)">Возможность пересдавать тест</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="re_test" type="radio" value="1"<?if($test['re_test']) echo 'checked="checked"';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="re_test" type="radio" value="0"<?if(!$test['re_test']) echo 'checked="checked"';?>>
                                        <span>Нет</span>
                                    </label>
                                </span>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100">
                                <label>Описание теста</label>
                                <textarea placeholder="Описание будет показано перед началом теста" name="test_desc"><?=$test['test_desc'];?></textarea>
                            </div>

                            <div class="width-100">
                                <label>Расшифровка если сдал</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="help_hint_success" type="radio" value="1"<?if($test['help_hint_success'] == 1) echo ' checked="checked"';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="help_hint_success" type="radio" value="0"<?if($test['help_hint_success'] == 0) echo ' checked="checked"';?>>
                                        <span>Нет</span>
                                    </label>
                                </span>
                            </div>

                            <div class="width-100">
                                <label>Расшифровка если не сдал</label>
                                <span class="custom-radio-wrap not-lot-of">
                                    <label class="custom-radio">
                                        <input name="help_hint_fail" type="radio" value="1"<?if($test['help_hint_fail'] == 1) echo ' checked="checked"';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="help_hint_fail" type="radio" value="0"<?if($test['help_hint_fail'] == 0) echo ' checked="checked"';?>>
                                        <span>Нет</span>
                                    </label>
                                    <br>
                                    <label class="custom-radio">
                                        <input name="help_hint_fail" type="radio" value="2"<?if($test['help_hint_fail'] == 2) echo ' checked="checked"';?>>
                                        <span>Только вопросы с неверными ответами</span>
                                    </label>
                                </span>
                            </div>
                        </div>


                        <div class="col-1-1 mt-10">
                            <h4 class="mb-15">Вопросы теста</h4>

                            <div class="test-question-list-wrap">
                                <a href="#modal_test_question" data-uk-modal="{center:true}"><nobr>Добавить вопрос</nobr></a>

                                <div class="test-question-list mt-20">
                                    <div class="sortable sortable_box">
                                        <?if($questions):
                                            require_once (__DIR__.'/tests/question_list.php');
                                        endif;?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- 4 ВКЛАДКА ДОСТУП -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Настройка доступа</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Тип доступа</label>
                                <div class="select-wrap">
                                    <select id="access_type" name="access_type">
                                        <option value="<?=Training::ACCESS_TO_INHERIT;?>"<?if($lesson['access_type'] == Training::ACCESS_TO_INHERIT) echo ' selected="selected"';?> data-show_off="by_button">Наследовать</option>
                                        <option value="<?=Training::ACCESS_TO_GROUP;?>"<?if($lesson['access_type'] == Training::ACCESS_TO_GROUP) echo ' selected="selected"';?> data-show_on="access_group">По группе</option>
                                        <?$membership = System::CheckExtensension('membership', 1);
                                        if($membership):?>
                                            <option value="<?=Training::ACCESS_TO_SUBS;?>"<?if($lesson['access_type'] == Training::ACCESS_TO_SUBS) echo ' selected="selected"';?> data-show_on="access_planes">По подписке</option>
                                        <?endif;?>
                                        <option value="<?=Training::ACCESS_FREE;?>"<?if($lesson['access_type'] == Training::ACCESS_FREE) echo ' selected="selected"';?>>Свободный доступ</option>
                                    </select>
                                </div>
                            </div>

                            <div id="access_group" class="width-100 hidden"><label>Группа</label>
                                <select class="multiple-select" name="access_groups[]" multiple="multiple">
                                    <?$groups = User::getUserGroups();
                                    $access_groups = json_decode($lesson['access_groups'], true);
                                    foreach($groups as $group):?>
                                        <option value="<?=$group['group_id'];?>"<?if(!empty($access_groups) && in_array($group['group_id'], $access_groups)) echo ' selected="selected"';?>><?=$group['group_title'];?></option>
                                    <?endforeach; ?>
                                </select>
                            </div>

                            <?if($membership):?>
                                <div id="access_planes" class="width-100 mb-20 hidden"><label>Подписка</label>
                                    <select class="multiple-select" name="access_planes[]" multiple="multiple">
                                        <?$planes = Member::getPlanes();
                                        $access_planes = json_decode($lesson['access_planes'], true);
                                        foreach($planes as $plane):?>
                                            <option value="<?=$plane['id'];?>"<?if(!empty($access_planes)){ if(in_array($plane['id'], $access_planes)) echo ' selected="selected"';}?>><?=$plane['name'];?></option>
                                        <?endforeach; ?>
                                    </select>
                                </div>
                            <?endif;?>
                        </div>
                        <div class="col-1-2">
                            <div class="width-100"><label>Показывать урок, если нет доступа.</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="access_hidden" type="radio" value="2"<?if(isset($lesson['access_hidden']) && $lesson['access_hidden'] == 2) echo ' checked';?>><span>Да</span></label>
                                    <label class="custom-radio"><input name="access_hidden" type="radio" value="1"<?if(isset($lesson['access_hidden']) && $lesson['access_hidden'] == 1) echo ' checked';?>><span>Нет</span></label>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row-line mt-10" id="by_button">
                        <div class="col-1-1 mb-0">
                            <h4>При покупке направлять</h4>
                        </div>

                        <div class="col-1-2">
                            <p class="width-100"><label>Текст кнопки</label>
                                <input type="text" name="by_button[text]" value="<?=$by_button['text'];?>">
                            </p>

                            <div class="width-100"><label>Вариант</label>
                                <div class="select-wrap">
                                    <select name="by_button[type]">
                                        <option value="<?=Training::BY_BUTTON_TYPE_NOT_BUTTON;?>"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_NOT_BUTTON) echo ' selected="selected"';?>>Не выбран</option>
                                        <option value="<?=Training::BY_BUTTON_TYPE_PRODUCT_ORDER;?>" data-show_on="by_button_product_order"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_PRODUCT_ORDER) echo ' selected="selected"';?>>Продукт - страница заказа</option>
                                        <option value="<?=Training::BY_BUTTON_TYPE_RATE;?>" data-show_on="by_button_product_rate"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_RATE) echo ' selected="selected"';?>>Выбор тарифа (несколько продуктов)</option>
                                        <option value="<?=Training::BY_BUTTON_TYPE_PRODUCT_LENDING;?>" data-show_on="by_button_product_lending"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_PRODUCT_LENDING) echo ' selected="selected"';?>>Продукт - лендинг</option>
                                        <option value="<?=Training::BY_BUTTON_TYPE_PRODUCT_DESC;?>" data-show_on="by_button_product_desc"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_PRODUCT_DESC) echo ' selected="selected"';?>>Описание продукта</option>
                                        <option value="<?=Training::BY_BUTTON_TYPE_PRODUCT_DESC_MODAL;?>" data-show_on="by_button_product_desc"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_PRODUCT_DESC_MODAL) echo ' selected="selected"';?>>Описание продукта (в модальном окне)</option>
                                        <option value="<?=Training::BY_BUTTON_TYPE_YOUR_URL;?>" data-show_on="by_button_your_url"<?if($by_button['type'] == Training::BY_BUTTON_TYPE_YOUR_URL) echo ' selected="selected"';?>>Своя ссылка</option>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100 hidden" id="by_button_product_order"><label>выберите продукт</label>
                                <div class="select-wrap">
                                    <select name="by_button[product_order]">
                                        <option value="0">Не выбран</option>
                                        <?if ($product_list):
                                            foreach($product_list as $product):?>
                                                <option value="<?=$product['product_id'];?>"<?if(isset($by_button['product_order']) && $product['product_id'] == $by_button['product_order']) echo ' selected="selected"';?>><?=$product['product_name'];?></option>
                                            <?endforeach;
                                        endif;?>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100 hidden" id="by_button_product_lending"><label>выберите продукт</label>
                                <div class="select-wrap">
                                    <select name="by_button[product_lending]">
                                        <option value="0">Не выбран</option>
                                        <?if ($product_list):
                                            foreach($product_list as $product):?>
                                                <option value="<?=$product['product_id'];?>"<?if(isset($by_button['product_lending']) && $product['product_id'] == $by_button['product_lending']) echo ' selected="selected"';?>><?=$product['product_name'];?></option>
                                            <?endforeach;
                                        endif;?>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100 mb-0 hidden" id="by_button_product_desc">
                                <label>Выбор продукта</label>
                                <div class="select-wrap">
                                    <select name="by_button[product_desc]">
                                        <option value="0">Не выбран</option>
                                        <?foreach($product_list as $product):?>
                                            <option value="<?=$product['product_id'];?>"<?if(isset($by_button['product_desc']) && $by_button['product_desc'] == $product['product_id']) echo ' selected="selected"';?>><?=$product['product_name'];?></option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100 hidden" id="by_button_product_rate"><label>Выбор тарифа</label>
                                <select name="by_button[rate][]" multiple="multiple" class="multiple-select">
                                    <option value="0">Не выбран</option>
                                    <?foreach($product_list as $product):?>
                                        <option value="<?=$product['product_id'];?>"<?if(isset($by_button['rate']) && in_array($product['product_id'], $by_button['rate'])) echo ' selected="selected"';?>><?=$product['product_name'];?></option>
                                    <?endforeach;?>
                                </select>
                            </div>

                            <p class="width-100 mb-20 hidden" id="by_button_your_url"><label>укажите ссылку</label>
                                <input type="text" name="by_button[your_url]" value="<?if(isset($by_button['your_url'])) echo  $by_button['your_url']?>" placeholder="http://">
                            </p>
                        </div>
                    </div>

                    <div class="row-line mt-10">
                        <div class="col-1-1 mb-0">
                            <h4>Открытие по расписанию</h4>
                        </div>
                 
                        <div class="col-1-2">
                            <span class="custom-radio-wrap">
                                <label class="custom-radio"><input name="shedule" type="radio" value="1"<?if(isset($lesson['shedule']) && $lesson['shedule'] == 1) echo ' checked';?>><span>Открыть сразу</span></label>
                                <label class="custom-radio"><input name="shedule" type="radio" value="2"<?if(isset($lesson['shedule']) && $lesson['shedule'] == 2) echo ' checked';?> data-show_on="shedule_show"><span>С задержкой</span></label>
                            </span>
                        </div>
                
                        <div class="col-1-1 hidden" id="shedule_show">
                            <div class="width-100"><label>Тип открытия</label>
                                <div class="select-wrap">
                                    <select name="shedule_type">
                                        <option value="1" data-show_on="open_wait_days"<?if($lesson['shedule_type'] && $lesson['shedule_type'] == 1) echo ' selected="selected"';?>>Относительное время</option>
                                        <option value="2" data-show_on="open_date"<?if($lesson['shedule_type'] && $lesson['shedule_type'] == 2) echo ' selected="selected"';?>>Конкретная дата</option>
                                    </select>
                                </div>
                            </div>


                            <div class=" hidden" id="open_date"><label>Дата открытия урока</label>
                                <input class="datetimepicker" autocomplete="off" type="text" size="35" name="shedule_open_date" value="<?=isset($lesson['shedule_open_date']) ? date('d.m.Y H:i', $lesson['shedule_open_date']) : date("d.m.Y ", time());?>">
                            </div>

                            <div class="hidden" id="open_wait_days">
                                <div class="width-100" id=""><label>Относительно чего</label>
                                    <div class="select-wrap">
                                        <select name="shedule_relatively">
                                            <option value="<?=TrainingLesson::START_DATE_TRAINING;?>"<?if($lesson['shedule_relatively'] && $lesson['shedule_relatively'] == TrainingLesson::START_DATE_TRAINING) echo ' selected="selected"';?>>Начало тренинга</option>
                                            <option value="<?=TrainingLesson::START_BUY_DATE_TRAINING;?>"<?if($lesson['shedule_relatively'] && $lesson['shedule_relatively'] == TrainingLesson::START_BUY_DATE_TRAINING) echo ' selected="selected"';?>>Дата покупки (назначения группы/подписки)</option>
                                            <option value="<?=TrainingLesson::ENTER_IN_PREVIOUS_LESSON_DATE;?>"<?if($lesson['shedule_relatively'] && $lesson['shedule_relatively'] == TrainingLesson::ENTER_IN_PREVIOUS_LESSON_DATE) echo ' selected="selected"';?>>Входа в предыдущий урок</option>
                                            <option value="<?=TrainingLesson::ENTER_IN_FIRST_LESSON_DATE;?>"<?if($lesson['shedule_relatively'] && $lesson['shedule_relatively'] == TrainingLesson::ENTER_IN_FIRST_LESSON_DATE) echo ' selected="selected"';?>>Входа в первый урок</option>
                                            <option value="<?=TrainingLesson::ENTER_IN_SPECIFIC_LESSON_DATE;?>"<?if($lesson['shedule_relatively'] && $lesson['shedule_relatively'] == TrainingLesson::ENTER_IN_SPECIFIC_LESSON_DATE) echo ' selected="selected"';?> data-show_on="lesson_specific">Входа в конкретный урок</option>
                                            <option value="<?=TrainingLesson::ENTER_IN_SPECIFIC_LESSON_PASSED;?>"<?if($lesson['shedule_relatively'] && $lesson['shedule_relatively'] == TrainingLesson::ENTER_IN_SPECIFIC_LESSON_PASSED) echo ' selected="selected"';?> data-show_on="lesson_specific">Прохождение конкретного урока</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="lesson_specific" class="hidden mt-20"><label>Выберите урок</label>
                                    <div class="select-wrap">
                                        <select name="shedule_relatively_specific_lesson">
                                            <?if(isset($lesson_list) && is_iterable($lesson_list)):
                                                foreach($lesson_list as $item):?>
                                                    <option value="<?=$item['lesson_id'];?>"<?if(isset($lesson['shedule_relatively_specific_lesson']) && $lesson['shedule_relatively_specific_lesson'] == $item['lesson_id']) echo ' selected="selected"';?>><?=$item['name'];?></option>
                                                <?endforeach;
                                            endif;?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-20"><label>Как быстро открыть доступ?</label>
                                    <div class="select-wrap">
                                        <select name="shedule_how_fast_open">
                                            <option value="1"<?if($lesson['shedule_how_fast_open'] && $lesson['shedule_how_fast_open'] == 1) echo ' selected="selected"';?>>На следующий день</option>
                                            <option value="2"<?if($lesson['shedule_how_fast_open'] && $lesson['shedule_how_fast_open'] == 2) echo ' selected="selected"';?> data-show_on="shedule_access_time_days">Через X дней</option>
                                            <option value="3"<?if($lesson['shedule_how_fast_open'] && $lesson['shedule_how_fast_open'] == 3) echo ' selected="selected"';?> data-show_on="shedule_access_time_weekday">Дождаться дня недели</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="hidden px-label-wrap mt-20" id="shedule_access_time_days"><label>Количество дней<span class="px-label">дн.</span></label>
                                    <input type="text" name="shedule_count_days"  value="<?=isset($lesson['shedule_count_days']) ? $lesson['shedule_count_days'] : 1;?>">
                                </div>

                                <div class="hidden mt-20" id="shedule_access_time_weekday"><label>День недели</label>
                                    <div class="select-wrap">
                                        <?if ($lesson && isset($lesson['shedule_access_time_weekday'])):?>
                                            <select name="shedule_access_time_weekday">
                                                <option value="1"<?if($lesson && $lesson['shedule_access_time_weekday'] == 1) echo ' selected="selected"';?>>Понедельник</option>
                                                <option value="2"<?if($lesson && $lesson['shedule_access_time_weekday'] == 2) echo ' selected="selected"';?>>Вторник</option>
                                                <option value="3"<?if($lesson && $lesson['shedule_access_time_weekday'] == 3) echo ' selected="selected"';?>>Среда</option>
                                                <option value="4"<?if($lesson && $lesson['shedule_access_time_weekday'] == 4) echo ' selected="selected"';?>>Четверг</option>
                                                <option value="5"<?if($lesson && $lesson['shedule_access_time_weekday'] == 5) echo ' selected="selected"';?>>Пятница</option>
                                                <option value="6"<?if($lesson && $lesson['shedule_access_time_weekday'] == 6) echo ' selected="selected"';?>>Суббота</option>
                                                <option value="7"<?if($lesson && $lesson['shedule_access_time_weekday'] == 7) echo ' selected="selected"';?>>Воскресенье</option>
                                            </select>

                                        <?else:?>
                                            <select name="shedule_access_time_weekday">
                                                <option value="1">Понедельник</option>
                                                <option value="2">Вторник</option>
                                                <option value="3">Среда</option>
                                                <option value="4">Четверг</option>
                                                <option value="5">Пятница</option>
                                                <option value="6">Суббота</option>
                                                <option value="7">Воскресенье</option>
                                            </select>
                                        <?endif;?>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-20 width-100"><label>Показывать урок, если не подошла дата открытия.</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="shedule_hidden" type="radio" value="2"<?if(isset($lesson['shedule_hidden']) && $lesson['shedule_hidden'] == 2) echo ' checked';?>><span>Да</span></label>
                                    <label class="custom-radio"><input name="shedule_hidden" type="radio" value="1"<?if(isset($lesson['shedule_hidden']) && $lesson['shedule_hidden'] == 1) echo ' checked';?>><span>Нет</span></label>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Закрытие урока</h4>
                        </div>
                        <div class="col-1-2">
                            <p class="width-100"><label>Дата скрытия урока</label><input placeholder="Укажите, если нужно" type="text" class="datetimepicker" value="<?if($lesson['end_date']) echo date("d.m.Y H:i", $lesson['end_date']);?>" autocomplete="off" name="end_date"></p>
                        </div>
                    </div>
                </div>

                <!-- 5 ВКЛАДКА ВНЕШНИЙ ВИД -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Настройки внешнего вида</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Показывать внешние<br>комментарии</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="show_comments" type="radio" value="1"<?if($lesson['show_comments'] == 1) echo ' checked';?>><span>Да</span></label>
                                    <label class="custom-radio"><input name="show_comments" type="radio" value="0"<?if($lesson['show_comments'] == 0) echo ' checked';?>><span>Нет</span></label>
                                </span>
                            </div>

                            <?/*ToDo убрать после раскомментирования ниже кода*/?>
                            <input type="hidden" value="1" name="show_hits">
                            <?/*<div class="width-100"><label>Показать просмотры</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="show_hits" type="radio" value="1"<?if($lesson['show_hits'] == 1) echo ' checked';?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio"><input name="show_hits" type="radio" value="0"<?if($lesson['show_hits'] == 0) echo ' checked';?>>
                                        <span>Нет</span>
                                    </label>
                                </span>
                            </div>*/?>

                            <div class="min-label-wrap width-100"><label>Продолжительность урока<span class="min-label">мин.</span></label>
                                <input type="text" size="35" value="<?=$lesson['duration'];?>" name="duration">
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Обложка</label><input type="file" name="cover">
                                <input type="hidden" name="current_img" value="<?=$lesson['cover'];?>">
                            </div>

                            <?if($lesson['cover']):?>
                                <div class="width-100 del_img_wrap">
                                    <img src="/images/training/lessons/<?=$lesson['cover'];?>" alt="" width="210">
                                    <span class="del_img_link">
                                        <button type="submit" form="del_img" value=" " title="Удалить изображение с сервера?" name="del_img">
                                            <span class="icon-remove"></span>
                                        </button>
                                    </span>
                                </div>
                            <?endif;?>

                            <div class="width-100"><label>Alt обложки</label>
                                <input type="text" name="img_alt" size="35" value="<?=$lesson['img_alt'];?>">
                            </div>
                        </div>

                        <div class="col-1-1">
                            <div class="width-100"><label>Описание урока (отображается в списке уроков)</label>
                                <textarea class="editor" name="less_desc"><?=$lesson['less_desc'];?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6 вкладка SEO  -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Настройки для SEO</h4>
                        </div>

                        <div class="col-1-2">
                            <p class="width-100"><label>Title</label>
                                <input type="text" name="title" value="<?=$lesson['title'];?>" placeholder="Title урока">
                            </p>

                            <p class="width-100"><label>Description</label>
                                <textarea name="meta_desc" rows="3" cols="40"><?=$lesson['meta_desc'];?></textarea>
                            </p>
                        </div>

                        <div class="col-1-2">
                            <p class="width-100"><label>Алиас</label>
                                <input type="text" name="alias" value="<?=$lesson['alias'];?>" placeholder="Алиас урока">
                            </p>

                            <p class="width-100"><label>Keyword</label>
                                <textarea name="meta_keys" rows="3" cols="40"><?=$lesson['meta_keys'];?></textarea>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="buttons-under-form mb-30">
                <p class="button-delete">
                    <a onclick="return confirm('Вы уверены?')" href="/admin/training/dellesson/<?=$training_id;?>/<?=$lesson_id;?>?token=<?=$_SESSION['admin_token'];?>" title="Удалить"><i class="icon-remove"></i>Удалить урок</a>
                </p>
                <a href="#ModalTransferLesson" class="button-delete button-copy-2" data-uk-modal="{center:true}">Скопировать в другой тренинг</a>
            </div>
        </div>

        <input type="hidden" name="sort" form="options" value="<?=!empty($sort_arr) ? max($sort_arr) : 0;?>">
    </form>
</div>

<form action="" id="options" method="POST">
    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
</form>

<form action="/admin/delimg/<?=$lesson['lesson_id'];?>" id="del_img" method="POST">
    <input type="hidden" name="path" value="images/training/lessons/<?=$lesson['cover'];?>">
    <input type="hidden" name="page" value="admin/training/editlesson/<?="{$lesson['training_id']}/{$lesson['lesson_id']}";?>">
    <input type="hidden" name="table" value="training_lessons">
    <input type="hidden" name="name" value="cover">
    <input type="hidden" name="where" value="lesson_id">
    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
</form>

<?require_once(__DIR__ . '/elements/index.php');
require_once(__DIR__ . '/tests/index.php');
require_once (ROOT . '/extensions/training/layouts/admin/admin-footer.php');?>

<div id="ModalTransferLesson" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="userbox  modal-userbox-2">
            <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close">
                <span class="icon-close"></span>
            </a>

            <div>
                <h3 class="modal-head">Выберите тренинг</h3>
                <div>
                    <form action="/admin/training/copytransfer" id="copytransfer" method="POST" class="select-curator-row">
                        <div class="select-wrap">
                            <select class="select" name="newtraining">
                                <?$trainings = Training::getTrainingListToList();
                                foreach($trainings as $training):
                                    if($training['training_id'] != $training_id):?>
                                        <option value="<?=$training['training_id']?>"><?=$training['name']?></option>
                                    <?endif;
                                endforeach;?>
                            </select>
                        </div>

                        <div>
                            <input type="hidden"  name="token" value="<?=$_SESSION['admin_token'];?>">
                            <div class="group-button-modal">
                                <button type="submit" name="transferlesson" value="<?=$lesson['lesson_id'];?>" class="button button-green">Скопировать</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>