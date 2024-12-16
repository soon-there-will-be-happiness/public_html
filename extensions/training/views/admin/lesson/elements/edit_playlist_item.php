<?php defined('BILLINGMASTER') or die;?>

<form enctype="multipart/form-data" action="/admin/training/lessons/playlistitem/edit/<?=$playlist_item['id'];?>" method="POST">
    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
    <input type="hidden" name="training_id" value="<?=$lesson['training_id'];?>">

    <div class="modal-admin_top">
        <h3 class="modal-traning-title">
            <span class="modal-traning-title-icon">
                <img src="/extensions/training/web/admin/images/icons/pleylist-icon.svg" alt="">
            </span>Редактировать плейлист
        </h3>
        <ul class="modal-nav_button">
            <li>
                <input type="submit" name="edit_playlist_item" value="Сохранить" class="button save button-green">.
            </li>
            <li class="modal-nav_button__last">
                <a class="button uk-modal-close uk-close modal-nav_button__close" href="#close"><i class="icon-close"></i></a>
            </li>
        </ul>
    </div>

    <div class="admin_form">
        <div class="row-line">
            <div class="col-1-1 mb-0">
                <h4>Редактировать элемент плейлиста <?=$playlist_item['params']['title'];?></h4>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Тип элемента</label>
                    <div class="select-wrap">
                        <select name="params[type]">
                            <option value="1"<?if($playlist_item['params']['type'] == 1) echo ' selected="selected"';?>>Infoprotector</option>
                            <option value="2"<?if($playlist_item['params']['type'] == 2) echo ' selected="selected"';?>>Видео</option>
                            <option value="3"<?if($playlist_item['params']['type'] == 3) echo ' selected="selected"';?>>Аудио</option>
                            <option value="4"<?if($playlist_item['params']['type'] == 4) echo ' selected="selected"';?> data-show_off="adit_pl_item_cover_box">Ссылка на видеохостинг</option>
                            <option value="5"<?if($playlist_item['params']['type'] == 5) echo ' selected="selected"';?> data-show_off="edit_pl_item_url,edit_pl_item_time">Изображение</option>
                            <option value="4"<?if($playlist_item['params']['type'] == 4) echo ' selected="selected"';?> data-show_off="adit_pl_item_cover_box">Youtube / vimeo</option>
                            <option value="6"<?if($playlist_item['params']['type'] == 6) echo ' selected="selected"';?> data-show_on="edit_pl_item_watermark_param">Кинескоп (kinescope.io)</option>
                            <option value="7"<?if($playlist_item['params']['type'] == 7) echo ' selected="selected"';?>>Rutube</option>
                            <option value="8"<?if($playlist_item['params']['type'] == 8) echo ' selected="selected"';?>>PeerTube</option>
                        </select>
                    </div>
                </div>
                <div class="width-100"><label>Заголовок</label>
                    <input type="text" name="params[title]" required="required" value="<?=$playlist_item['params']['title'];?>">
                </div>
                <div class="width-100" id="edit_pl_item_time"><label>Продолжительность</label>
                    <input type="text" name="params[time]" value="<?=$playlist_item['params']['time'];?>">
                </div>
                <div class="width-100"><label title="Для вывода водного знака нужно сконфигурировать свой плеер. Подробнее в справке.">Выводить watermark</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="params[show_watermark]" type="radio" value="1"<?php if($playlist_item['params']['show_watermark']) echo ' checked';?>><span>Да</span></label>
                        <label class="custom-radio"><input name="params[show_watermark]" type="radio" value="0"<?php if(!$playlist_item['params']['show_watermark']) echo ' checked';?>><span>Нет</span></label>
                    </span>
                </div>
                <div id="edit_pl_item_watermark_param" class="hidden">
                    <div class="width-100"><label>
                            Размер водяного знака
                            <span class="result-item-icon" data-uk-tooltip="" data-toggle="popover"
                                  data-content="Размер водяного знака указывается в пропорции размеру видео 1 - это 100%, 0.1 - 10%, 0.2 - это 20% и т.д. Настройка действует только для kinescope"><i
                                        class="icon-answer"></i></span>
                        </label>
                        <input type="number" name="params[show_watermark_scale]" placeholder="" step="0.1">
                    </div>
                    <div class="width-100"><label data-uk-tooltip=""
                                                  title="Например видео у вас 30 мин. и вы хотите показать водяной знак 3 раза на 10 сек. Тогда вы ставите длительность показа 10 сек., а длительность скрытия 590 сек.">Длительность показа в секундах</label>
                        <input id="add_media_cover" type="number" name="params[show_watermark_visible]" placeholder="">
                    </div>
                    <div class="width-100"><label>Длительность скрытия в секундах</label>
                        <input id="add_media_cover" type="number" name="params[show_watermark_hidden]" placeholder="">
                    </div>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100" id="edit_pl_item_url"><label>URL</label>
                    <input type="text" name="params[url]" value="<?=$playlist_item['params']['url'];?>">
                </div>
                <div class="width-100" id="adit_pl_item_cover_box"><label>Обложка</label>
                    <input id="edit_pl_item_cover" type="text" name="params[cover]" value="<?=$playlist_item['params']['cover'];?>">
                    <a href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=edit_pl_item_cover&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" class="btn iframe-btn" type="button">Выбрать изображение</a>
                </div>
            </div>
        </div>
    </div>
</form>