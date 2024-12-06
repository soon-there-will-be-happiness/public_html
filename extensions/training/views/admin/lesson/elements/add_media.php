<?php defined('BILLINGMASTER') or die;?>

<div id="modal_add_media" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-add-elem">
        <div class="userbox modal-userbox-3">
            <form enctype="multipart/form-data" action="/admin/training/lessons/element/add" method="POST">
                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                <input type="hidden" name="training_id" value="<?=$training_id;?>">
                <input type="hidden" name="lesson_id" value="<?=@$lesson_id;?>">
                <input type="hidden" name="element_type" value="<?=TrainingLesson::ELEMENT_TYPE_MEDIA?>">

                <div class="modal-admin_top">
                    <h3 class="modal-traning-title">
                        <span class="modal-traning-title-icon">
                            <img src="/extensions/training/web/admin/images/icons/video.svg" alt="">
                        </span>Добавить видео/аудио
                    </h3>
                    <ul class="modal-nav_button">
                        <li>
                            <input type="submit" name="add_element" value="Добавить" class="button save button-green">
                        </li>
                        <li class="modal-nav_button__last">
                            <a class="button uk-modal-close uk-close modal-nav_button__close" href="#close"><i class="icon-close"></i></a>
                        </li>
                    </ul>
                </div>

                <div class="admin_form">
                    <div class="row-line">
                        <div class="col-1-2">
                            <div class="width-100"><label>Тип элемента</label>
                                <div class="select-wrap">
                                    <select name="params[element_type]">
                                        <option value="2">Видеофайл</option>
                                        <option value="4" data-show_off="add_media_cover_box">Youtube / vimeo</option>
                                        <option value="7">Rutube</option>
                                        <option value="8">PeerTube</option>
                                        <option value="6" data-show_on="add_watermark_param">Кинескоп (kinescope.io)</option>
                                        <option value="3" data-show_off="add_media_cover_box">Аудио</option>
                                        <option value="5" data-show_on="add_media_file">Изображение</option>
                                        <option value="1">InfoProtector</option>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100"><label>Заголовок</label>
                                <input type="text" name="params[title]" placeholder="">
                            </div>

                            <div class="width-100"><label>Служебное название</label>
                                <input type="text" name="params[name]" placeholder="Название элемента в списке" required="required">
                            </div>

                            <div class="width-100"><label title="Для вывода водного знака нужно сконфигурировать свой плеер. Подробнее в справке.">Выводить watermark</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="params[show_watermark]" type="radio" value="1"><span>Да</span></label>
                                    <label class="custom-radio"><input name="params[show_watermark]" type="radio" value="0" checked><span>Нет</span></label>
                                </span>
                            </div>
                            
                            <div id="add_watermark_param" class="hidden">
                                <div class="width-100"><label>
                                    Размер водяного знака
                                    <span class="result-item-icon" data-uk-tooltip="" data-toggle="popover" data-content="Размер водяного знака указывается в пропорции размеру видео 1 - это 100%, 0.1 - 10%, 0.2 - это 20% и т.д. Настройка действует только для kinescope"><i class="icon-answer"></i></span>
                                </label>
                                    <input type="number" name="params[show_watermark_scale]" placeholder="" step="0.1">
                                </div>
                                
                                <div class="width-100"><label data-uk-tooltip="" title="Например видео у вас 30 мин. и вы хотите показать водяной знак 3 раза на 10 сек. Тогда вы ставите длительность показа 10 сек., а длительность скрытия 590 сек.">Длительность показа в секундах</label>
                                    <input type="number" name="params[show_watermark_visible]" placeholder="">
                                </div>
                                
                                <div class="width-100"><label>Длительность скрытия в секундах</label>
                                    <input type="number" name="params[show_watermark_hidden]" placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>URL</label>
                                <input id="add_media_url" type="text" name="params[url]" placeholder="" required="required">
                                <a id="add_media_file" class="hidden" href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=add_media_url&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" class="btn iframe-btn" type="button">Выбрать файл на сервере</a>
                            </div>

                            <div class="width-100" id="add_media_cover_box"><label>Обложка</label>
                                <input id="add_media_cover" type="text" name="params[cover]" placeholder="">
                                <a href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=add_media_cover&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" class="btn iframe-btn" type="button">Выбрать изображение</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>