<?php defined('BILLINGMASTER') or die; ?>


<form enctype="multipart/form-data" action="/admin/training/lessons/element/edit/<?=$element['id'];?>" method="POST">
    <input type="hidden" name="token" value="<?= $_SESSION['admin_token']; ?>">
    <input type="hidden" name="training_id" value="<?= $lesson['training_id']; ?>">
    <input type="hidden" name="lesson_id" value="<?= @$lesson_id; ?>">
    <input type="hidden" name="element_type" value="<?= TrainingLesson::ELEMENT_TYPE_GALLERY ?>">
    <div class="modal-admin_top">
        <h3 class="modal-traning-title">
                        <span class="modal-traning-title-icon">
                            <img src="/template/admin/images/icons/gallery.png" alt="" style="filter: brightness(0);">
                        </span>Галерея изображений
        </h3>
        <ul class="modal-nav_button">
            <li>
                <input type="submit" name="edit_element" value="Сохранить" class="button save button-green">
            </li>
            <li class="modal-nav_button__last">
                <a class="button uk-modal-close uk-close modal-nav_button__close" href="#close"><i
                            class="icon-close"></i></a>
            </li>
        </ul>
    </div>
    <div class="admin_form">
        <div class="row-line">
            <div class="col-1-2">
                <h4>Основные настройки галереи</h4>
                <div class="width-100"><label>Заголовок</label>
                    <input type="text" name="params[title]" placeholder="" value="<?= @$element['params']['title'] ?>">
                </div>
                <div class="width-100"><label>Служебное название</label>
                    <input type="text" name="params[name]" placeholder="Название элемента в списке" value="<?= @$element['params']['name'] ?>">
                </div>
                <!--<div class="width-100"><label><b>Какие изображения показывать?</b></label>
                    <?php
/*                    $galleryEnabled = (bool)System::CheckExtensension('gallery');
                    */?>
                    <select name="params[showImages]">
                        <option value="0" <?php /*if(@$element['params']['showImages'] == '0') echo ' selected="selected"';*/?>>Из "списка элементов галереи"</option>
                        <?php /*if ($galleryEnabled) { */?>
                            <option value="1" <?php /*if(@$element['params']['showImages'] == '1') echo ' selected="selected"';*/?> data-show_on="GalleryCatSettings">Изображения из категории галереи</option>
                            <option value="2" <?php /*if(@$element['params']['showImages'] == '2') echo ' selected="selected"';*/?> data-show_on="GalleryCatSettings">Оба варианта</option>
                        <?php /*} */?>
                    </select>
                </div>-->
                <div class="width-100" id="GalleryCatSettings"><label>Изображения из категории</label>
                    <?php $galleryCat = Gallery::getCatList(1); ?>
                    <select name="params[galleryCat]">
                        <?php foreach ($galleryCat as $cat) {?>
                            <option value="<?= $cat['cat_id'] ?>" <?php if(@$element['params']['galleryCat'] == $cat['cat_id']) echo ' selected="selected"';?> ><?= $cat['cat_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>


            </div>
            <div class="col-1-2">
                    <h4 class="h4-border">Внешний вид</h4>
                    <p><label>Ширина элемента галереи, пиксели:</label>
                        <input type="text" size="4" name="params[gallery][width]" value="<?=@$element['params']['gallery']['prev_height'] ?? 300?>">
                    </p>
                    <p><label>Высота элемента галереи, пиксели:</label>
                        <input type="text" size="4" name="params[gallery][height]" value="<?=@$element['params']['gallery']['height'] ?? 300?>">
                    </p>

                    <p><label>Стиль галереи:</label>
                        <?php if (!isset($element['params']['gallery']['style'])) { $element['params']['gallery']['style'] = "slider";} ?>
                        <select name="params[gallery][style]">
                            <option value="columns"<?php if(@$element['params']['gallery']['style'] == 'columns') echo ' selected="selected"';?>>Колонки</option>
                            <option value="justified"<?php if(@$element['params']['gallery']['style'] == 'justified') echo ' selected="selected"';?>>Ряды</option>
                            <option value="grid"<?php if(@$element['params']['gallery']['style'] == 'grid') echo ' selected="selected"';?>>Плитка</option>
                            <option value="slider"<?php if(@$element['params']['gallery']['style'] == 'slider') echo ' selected="selected"';?>>Слайдер</option>
                            <option value="carousel"<?php if(@$element['params']['gallery']['style'] == 'carousel') echo ' selected="selected"';?>>Карусель</option>
                        </select>
                    </p>
                <!--<h4>Добавить изображение</h4>

                <div class="width-100"><label>Название изображения</label>
                    <input type="text" name="params[image][title]" placeholder="">
                </div>

                <div class="width-100"><label>Описание изображения</label>
                    <textarea name="params[image][desc]"></textarea>
                </div>

                <div class="width-100"><label>Alt изображения</label>
                    <input type="text" name="params[image][alt]" placeholder="текст заместо картинки">
                </div>


                <p class="width-100">
                    <label>Файл изображения</label>

                    <span class="custom-radio-wrap">
                        <label class="custom-radio">
                            <input type="radio" name="_use_file1" data-show_on="download_file_gallery"
                                   data-show_off="select_file_gallery" value="1" checked>
                            <span>Загрузить файл</span>
                        </label>

                        <label class="custom-radio">
                            <input type="radio" name="_use_file1" data-show_on="select_file_gallery"
                                   data-show_off="download_file_gallery" value="0">
                            <span>Файл с сервера</span>
                        </label>
                    </span>-->

                    <!--<div id="download_file_gallery">
                        <input type="file" name="attach_gallery">
                        <br>
                        <br>
                    </div>
                    <?php
/*                        if (!is_dir(ROOT."/load/training/lessons/".$lesson['lesson_id'])) {
                            mkdir(ROOT."/load/training/lessons/".$lesson['lesson_id']);
                        }
                    */?>
                    <div id="select_file_gallery">
                        <input id="select_file_input_gallery" type="text" name="params[image][url]" placeholder="Ссылка на изображение"
                               value="" autocomplete="off">
                        <a href="javascript:void(0)"
                           onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=0&popup=1&field_id=select_file_input_gallery&relative_url=0&f_path=load/training/lessons/<?/*= $lesson['lesson_id']; */?>', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')"
                           class="btn iframe-btn" type="button">Выбрать файл</a>
                    </div>
                </p>-->
            </div>



            <div class="col-1-1">
               <!-- <h4>Список элементов галереи</h4>
                <?php /*if (isset($element['params']['images']) && is_array($element['params']['images'])) { */?>
                    <?php /*$i = 0; foreach ($element['params']['images'] as $key => $image) { */?>
                        <div class="row-line" style="justify-content: space-between; align-items: center; flex-wrap: nowrap;">
                            <div style="width: 60%;">
                                <b>Изображение #<?/*= ++$i; */?></b>
                                <div>Название: <?/*= $image['title'] */?></div>
                                <div>Описание: <?/*= $image['desc'] */?></div>
                                <div>Alt: <?/*= $image['alt'] */?></div>
                                <a href="/admin/training/lesson/element/removegalleryitem/<?/*= $lesson['training_id'] */?>/<?/*= $element['lesson_id'] */?>/<?/*= $element['id'] */?>?token=<?/*= $_SESSION['admin_token']; */?>&imageid=<?/*= $key */?>">Удалить изображение</a>
                            </div>
                            <a href="<?/*= $image['url'] */?>" target="_blank" title="Нажмите, чтобы открыть изображение в новой вкладке">
                                <img src="<?/*= $image['url'] */?>" alt="<?/*= $image['alt'] */?>" style="display: block; height: 150px; object-fit: contain;">
                            </a>
                        </div>
                    <?php /*}
                } else { */?>
                    <div>Изображений еще не добавлено</div>
                --><?php /*} */?>
            </div>
        </div>
    </div>
</form>