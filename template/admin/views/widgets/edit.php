<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('EDIT_WIDGET');?> <?=$widget['widget_type'];?> (ID: <?=$widget['widget_id'];?>)</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/widgets/">Список виджетов</a></li>
        <li><?=System::Lang('EDIT_WIDGET');?> <?=$widget['widget_type'];?></li>
    </ul>

    <form action="" method="POST" enctype="multipart/form-data">
        <?php if(isset($_GET['success'])):?>
            <div class="admin_message"><?=System::Lang('SAVED').'!';?></div>
        <?php endif;?>

        <div class="admin_top admin_top-flex">
            <h3 class="traning-title"><?=System::Lang('EDIT_WIDGET');?> <?=$widget['widget_type'];?></h3>
            <ul class="nav_button">
                <li><input type="submit" name="editwidget" value="<?=System::Lang('SAVE');?>" class="button save button-white font-bold"></li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/widgets/"><?=System::Lang('CLOSE');?></a>
                </li>
            </ul>
        </div>

        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 mb-0">
                    <h4><?=System::Lang('BASIC');?></h4>
                </div>

                <div class="col-1-2">
                    <p class="width-100"><label><?=System::Lang('WIDGET_TITLE');?></label>
                        <input type="text" name="title" value="<?=$widget['widget_title'];?>" placeholder="<?=System::Lang('WIDGET_TITLE');?>" required="required">
                    </p>

                    <div class="width-100"><label><?=System::Lang('STATUS');?></label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio">
                                <input name="status" type="radio" value="1" <?php if($widget['status'] == 1) echo 'checked';?>><span>Вкл</span>
                            </label>
                            <label class="custom-radio">
                                <input name="status" type="radio" value="0" <?php if($widget['status'] == 0) echo 'checked';?>><span>Откл</span>
                            </label>
                        </span>
                    </div>

                    <p class="width-100"><label><?=System::Lang('SORT');?></label>
                        <input type="text" size="3" value="<?=$widget['sort'];?>" name="sort">
                    </p>

                    <?php $xml = simplexml_load_file(ROOT . '/template/'. $setting['template'].'/'.$setting['template'].'.xml');?>
                    <div class="width-100"><label>Позиция вывода</label>
                        <div class="select-wrap">
                            <select name="position">
                                <?php foreach($xml->positions->position as $position):?>
                                    <option value="<?=$position;?>"<?php if($position == $widget['position']) echo 'selected="selected"';?>><?=$position;?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>

                    <div class="width-100"><label>Кто будет видеть</label>
                        <div class="select-wrap">
                            <select name="private">
                                <option value="0"<?php if($widget['private'] == 0) echo ' selected="selected"'?>>Все пользователи</option>
                                <option value="1"<?php if($widget['private'] == 1) echo ' selected="selected"'?>>Только авторизованные пользователи</option>
                            </select>
                        </div>
                    </div>

                    <p class="width-100"><label>CSS класс</label>
                        <input type="text" name="suffix" value="<?=$widget['suffix'];?>" placeholder="suffix">
                    </p>
                    
                    <div class="width-100"><label>Ширина модуля</label>
                        <div class="select-wrap">
                            <select name="width">
                                <option value="0"<?php if($widget['width'] == 0) echo ' selected="selected"'?>>Не задано</option>
                                <option value="1"<?php if($widget['width'] == 1) echo ' selected="selected"'?>>1 колонка</option>
                                <option value="2"<?php if($widget['width'] == 2) echo ' selected="selected"'?>>2 колонки</option>
                                <option value="3"<?php if($widget['width'] == 3) echo ' selected="selected"'?>>3 колонки</option>
                                <option value="4"<?php if($widget['width'] == 4) echo ' selected="selected"'?>>4 колонки</option>
                                <option value="5"<?php if($widget['width'] == 5) echo ' selected="selected"'?>>5 колонок</option>
                                <option value="6"<?php if($widget['width'] == 6) echo ' selected="selected"'?>>6 колонок</option>
                                <option value="7"<?php if($widget['width'] == 7) echo ' selected="selected"'?>>7 колонок</option>
                                <option value="8"<?php if($widget['width'] == 8) echo ' selected="selected"'?>>8 колонок</option>
                                <option value="9"<?php if($widget['width'] == 9) echo ' selected="selected"'?>>9 колонок</option>
                                <option value="10"<?php if($widget['width'] == 10) echo ' selected="selected"'?>>10 колонок</option>
                                <option value="11"<?php if($widget['width'] == 11) echo ' selected="selected"'?>>11 колонок</option>
                                <option value="12"<?php if($widget['width'] == 12) echo ' selected="selected"'?>>12 колонок</option>
                            </select>
                        </div>
                    </div>

                    <p class="width-100"><label>Заметки</label>
                        <textarea name="desc" rows="3" cols="40"><?=$widget['widget_desc'];?></textarea>
                    </p>
                </div>

                <div class="col-1-2">
                    <div class="width-100"><label>Показывать на страницах</label>
                        <select class="multiple-select" name="page[]" multiple="multiple" size="4" required="required">
                            <option value="main"<?php if(in_array("main", $pages)) echo ' selected="selected"';?>><?=System::Lang('MAIN_PAGE');?></option>
                            <option value="catalog"<?php if(in_array("catalog", $pages)) echo ' selected="selected"';?>><?=System::Lang('CATALOG');?></option>
                            <option value="courses_index"<?php if(in_array("courses_index", $pages)) echo ' selected="selected"';?>><?=System::Lang('COURSES');?> главная</option>
                            <option value="courses"<?php if(in_array("courses", $pages)) echo ' selected="selected"';?>>Мои курсы</option>
                            <option value="lessons_list"<?php if(in_array("lessons_list", $pages)) echo ' selected="selected"';?>><?=System::Lang('LESSONS_LIST');?></option>
                            <option value="lesson_page"<?php if(in_array("lesson_page", $pages)) echo ' selected="selected"';?>><?=System::Lang('LESSON_PAGE');?></option>
                            <option value="forum"<?php if(in_array("forum", $pages)) echo ' selected="selected"';?>><?=System::Lang('FORUM');?></option>
                            <option value="forum-category"<?php if(in_array("forum-category", $pages)) echo ' selected="selected"';?>><?=System::Lang('FORUM');?> категории</option>
                            <option value="forum-branch"<?php if(in_array("forum-branch", $pages)) echo ' selected="selected"';?>><?=System::Lang('FORUM');?> ветки</option>
                            <option value="forum-topic"<?php if(in_array("forum-topic", $pages)) echo ' selected="selected"';?>><?=System::Lang('FORUM');?> темы</option>
                            
                            
                            <option value="feedback"<?php if(in_array("feedback", $pages)) echo ' selected="selected"';?>><?=System::Lang('ASK_QUESTION');?></option>
                            <option value="reviews"<?php if(in_array("reviews", $pages)) echo ' selected="selected"';?>><?=System::Lang('REVIEWS');?></option>
                            <option value="lk"<?php if(in_array("lk", $pages)) echo ' selected="selected"';?>><?=System::Lang('LK');?></option>
                            <option value="aff"<?php if(in_array("aff", $pages)) echo ' selected="selected"';?>><?=System::Lang('PARTNERSHIP');?></option>
                            <option value="blog"<?php if(in_array("blog", $pages)) echo ' selected="selected"';?>><?=System::Lang('BLOG');?></option>
                            <option value="gallery"<?php if(in_array("gallery", $pages)) echo ' selected="selected"';?>><?=System::Lang('GALLERY');?></option>
                            <option value="static"<?php if(in_array("static", $pages)) echo ' selected="selected"';?>><?=System::Lang('STATIC_PAGES');?></option>
                            <option value="order"<?php if(in_array("order", $pages)) echo ' selected="selected"';?>>Страница заказа</option>
                            <option value="viewproduct"<?php if(in_array("viewproduct", $pages)) echo ' selected="selected"';?>><?=System::Lang('VIEW_PRODUCT');?></option>
                        
                            <option value="training_index"<?php if(in_array("training_index", $pages)) echo ' selected="selected"';?>>Тренинги 2.0 главная</option>
                            <option value="training"<?php if(in_array("training", $pages)) echo ' selected="selected"';?>>Тренинги 2.0 тренинг</option>
                            <option value="section"<?php if(in_array("section", $pages)) echo ' selected="selected"';?>>Тренинги 2.0 разделы</option>
                            <option value="lesson"<?php if(in_array("lesson", $pages)) echo ' selected="selected"';?>>Тренинги 2.0 уроки</option>
                            <option value="my_trainings"<?php if(in_array("my_trainings", $pages)) echo ' selected="selected"';?>>Тренинги 2.0 мои тренинги</option>

                            <option value="auth" <?php if(in_array("auth", $pages)) echo ' selected="selected"';?>>Страница авторизации</option>

                        </select>
                    </div>
                    
                    <?php if($en_courses):?>
                    <div class="width-100"><label>Показывать на курсах (тренинги 1.0)</label>
                        <select class="multiple-select" name="show_for_course[]" multiple="multiple" size="4">
                            <?php $course_list = Course::getCourseList(0, 0);
                            if($course_list):
                                foreach($course_list as $course):?>
                                    <option value="<?=$course['course_id'];?>"<?php if(in_array($course['course_id'], $show)) echo ' selected="selected"';?>><?=$course['name'];?></option>
                                <?php endforeach;
                            endif;?>
                        </select>
                    </div>
                    <?php endif;?>
                    
                    <?php if($en_training):?>
                    <div class="width-100"><label>Показывать на тренингах 2.0</label>
                        <select class="multiple-select" name="show_for_training[]" multiple="multiple" size="4">
                            <?php $trainings_list = Training::getTrainingList();
                            if($trainings_list):
                                foreach($trainings_list as $training):?>
                                    <option value="<?=$training['training_id'];?>"<?php if(in_array($training['training_id'], $show_training)) echo ' selected="selected"';?>><?=$training['name'];?></option>
                                <?php endforeach;
                            endif;?>
                        </select>
                    </div>
                    <?php endif;?>

                    <div class="width-100"><label>Показать группе пользователей</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio">
                                <input name="showByGroup" type="radio" value="0" <?php if($widget['showByGroup'] == 0) echo 'checked';?> data-show_off="widget_group_select"><span>Откл</span>
                            </label>
                            <label class="custom-radio">
                                <input name="showByGroup" type="radio" value="1" <?php if($widget['showByGroup'] == 1) echo 'checked';?> data-show_on="widget_group_select"><span>Показывать только выбранным группам</span>
                            </label>
                            <label class="custom-radio">
                                <input name="showByGroup" type="radio" value="2" <?php if($widget['showByGroup'] == 2) echo 'checked';?> data-show_on="widget_group_select"><span>Показывать всем, кроме выбранных групп</span>
                            </label>
                        </span>
                    </div>
                    <?php
                        $selected_groups = [];
                        if (isset($widget['showGroups'])) {
                            $selected_groups = json_decode($widget['showGroups'], true);
                        }
                    ?>
                    <div class="width-100" id="widget_group_select"><label>Группы для показа</label>
                        <select class="multiple-select" name="showGroups[]" multiple="multiple" size="4">
                            <?php $groups = User::getUserGroups();
                            if($groups):
                                foreach($groups as $group):?>
                                    <option value="<?=$group['group_id'];?>" <?= in_array($group['group_id'], $selected_groups) ? " selected" : "" ?>><?=$group['group_title'];?></option>
                                <?php endforeach;
                            endif;?>
                        </select>
                    </div>


                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                </div>
            </div>

            <div class="row-line">
                <div class="col-1-1 mb-0">
                    <h4>Настройки заголовоков</h4>
                </div>
                
                <div class="col-1-2">
                    <div class="width-100"><label>Показать Заголовок</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="show_header" type="radio" value="1" <?php if($widget['show_header'] == 1) echo 'checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="show_header" type="radio" value="0" <?php if(!$widget['show_header']) echo 'checked';?>><span>Откл</span></label>
                        </span>
                    </div>

                    <div class="width-100"><label>Заголовок</label>
                        <input type="text" name="header" placeholder="Заголовок" value="<?=$widget['header'];?>">
                    </div>

                    <div class="width-100"><label>Показать подзаголовок</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="show_subheader" type="radio" value="1" <?php if($widget['show_subheader'] == 1) echo 'checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="show_subheader" type="radio" value="0" <?php if($widget['show_subheader'] == 0) echo 'checked';?>><span>Откл</span></label>
                        </span>
                    </div>

                    <div class="width-100"><label>Подзаголовок</label>
                        <input type="text" name="subheader" placeholder="Подзаголовок" value="<?=$widget['subheader'];?>">
                    </div>
                </div>

                <div class="col-1-2">
                    <div class="width-100">
                        <label>Показать кнопку</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio">
                                <input name="show_right_button" type="radio" value="1"<?php if($widget['show_right_button']) echo 'checked'; ?>><span>Да</span>
                            </label>
                            <label class="custom-radio">
                                <input name="show_right_button" type="radio" value="0"<?php if(!$widget['show_right_button']) echo 'checked'; ?>><span>Нет</span>
                            </label>
                        </span>
                    </div>

                    <div class="width-100"><label>Название кнопки</label>
                        <input type="text" name="right_button_name" placeholder="Ссылка кнопки" value="<?=$widget['right_button_name'];?>">
                    </div>

                    <div class="width-100"><label>Ссылка кнопки</label>
                        <input type="text" name="right_button_link" placeholder="Ссылка кнопки" value="<?=$widget['right_button_link'];?>">
                    </div>
                </div>
            </div>

            <?php require_once(ROOT . "/template/admin/views/widgets/types/{$widget['widget_type']}/edit.php");?>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>