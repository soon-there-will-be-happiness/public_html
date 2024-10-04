<?php defined('BILLINGMASTER') or die;?>

<div id="modal_add_gallery" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-add-elem">
        <div class="userbox modal-userbox-3">
            <form enctype="multipart/form-data" action="/admin/training/lessons/element/add" method="POST">
                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                <input type="hidden" name="training_id" value="<?=$training_id;?>">
                <input type="hidden" name="lesson_id" value="<?=@$lesson_id;?>">
                <input type="hidden" name="element_type" value="<?=TrainingLesson::ELEMENT_TYPE_GALLERY?>">

                <div class="modal-admin_top">
                    <h3 class="modal-traning-title">
                        <span class="modal-traning-title-icon">
                            <img src="/template/admin/images/icons/gallery.png" alt="" style="filter: brightness(0);">
                        </span>Добавить галерею изображений
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
                            <h4>Основные настройки галереи</h4>
                            <div class="width-100"><label>Заголовок</label>
                                <input type="text" name="params[title]" placeholder="">
                            </div>

                            <div class="width-100"><label>Служебное название</label>
                                <input type="text" name="params[name]" placeholder="Название элемента в списке" required="required">
                            </div>
                        </div>

                        <div class="col-1-1">
                            <div>Перед тем как добавить изображения в галерею, сначала создайте элемент галереи нажав кнопку "Добавить"</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>