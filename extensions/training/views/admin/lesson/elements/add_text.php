<?php defined('BILLINGMASTER') or die;?>

<div id="modal_add_text" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-add-elem">
        <div class="userbox modal-userbox-3">

            <form enctype="multipart/form-data" action="/admin/training/lessons/element/add" method="POST">
                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                <input type="hidden" name="training_id" value="<?=$training_id;?>">
                <input type="hidden" name="lesson_id" value="<?=@$lesson_id;?>">
                <input type="hidden" name="element_type" value="<?=TrainingLesson::ELEMENT_TYPE_TEXT;?>">
                
                <div class="modal-admin_top">
                    <h3 class="modal-traning-title">
                        <span class="modal-traning-title-icon">
                            <img src="/extensions/training/web/admin/images/icons/text.svg" alt="">
                        </span>Добавить текст
                    </h3>
                    <ul class="modal-nav_button">
                        <li>
                            <input type="submit" name="add_element" value="Добавить" class="button save button-green">
                        </li>
                        <li>
                            <a class="button modal_button-fullscreen" href="#fullscreen">
                                <img src="/extensions/training/web/admin/images/icons/expand.svg" alt="" data-fullscreen="on"  title="Расширить окно">
                                <img src="/extensions/training/web/admin/images/icons/shrink.svg" alt="" data-fullscreen="off" title="Уменьшить окно" style="display: none;">
                            </a>
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
                                    <select name="params[type]">
                                        <option value="1">Текст</option>
                                        <option value="2" data-show_on="add_text_title">Аккордеон</option>
                                        <option value="3" data-show_on="add_text_title">Всплывающее окно</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2 hidden" id="add_text_title">
                            <div class="width-100"><label>Заголовок</label>
                                <input type="text" name="params[title]">
                            </div>
                        </div>

                        <div class="col-1-1">
                            <div class="width-100"><label>Текстовый редактор</label>
                                <textarea class="editor" name="params[text]" rows="3" cols="40"></textarea>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Служебное название</label>
                                <input type="text" name="params[name]" placeholder="Название элемента в списке" required="required">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>