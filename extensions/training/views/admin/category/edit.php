<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/extensions/training/layouts/admin/admin-head.php');?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Изменить категорию</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a><a href="/admin/logout/" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/training">Тренинги</a></li>
        <li><a href="/admin/training/cats">Список категорий</a></li>
        <li>Изменить категорию</li>
    </ul>

    <form action="" method="POST" enctype="multipart/form-data">
        <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Изменить категорию</h3>
            <ul class="nav_button">
                <li><input type="submit" name="editcat" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/training/cats/">Закрыть</a></li>
            </ul>
        </div>

        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-2">
                    <h4>Основное</h4>
                    <p class="width-100"><label>Название:</label>
                        <input type="text" name="name" value="<?=$cat['name'];?>" placeholder="Название категории" required="required">
                    </p>

                    <div class="width-100"><label>Родительская категория:</label>
                        <div class="select-wrap">
                            <select name="parent_cat">
                                <option value="0">Нет</option>
                                <?php $cat_list = TrainingCategory::getCatList(false);
                                if($cat_list):
                                    foreach($cat_list as $category):
                                        if ($category['cat_id'] == $cat['cat_id']) {
                                            continue;
                                        }?>
                                        <option value="<?=$category['cat_id'];?>"<?php if($cat['parent_cat'] == $category['cat_id']) echo ' selected="selected"';?>><?=$category['name'];?></option>
                                    <?php endforeach;
                                endif;?>
                            </select>
                        </div>
                    </div>
                    
                    
                    <p class="width-100"><label>Сортировка:</label>
                        <input type="text" value="<?=$cat['sort'];?>" name="sort">
                    </p>
                    
                    <div class="width-100"><label>Статус:</label>
                        <div class="select-wrap">
                            <select name="status">
                                <option value="1"<?php if($cat['status'] == 1) echo ' selected="selected"';?>>Включен</option>
                                <option value="0"<?php if($cat['status'] == 0) echo ' selected="selected"';?>>Отключен</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="width-100"><label>Обложка:</label><input type="file" name="cover">
                        <input type="hidden" name="current_img" value="<?=$cat['cover'];?>">
                    </div>

                    <?php if(!empty($cat['cover'])):?>
                        <div class="del_img_wrap">
                        <img src="/images/training/category/<?=$cat['cover'];?>" alt="" width="150">
                        <span class="del_img_link">
                            <button type="submit" form="del_img" title="Удалить изображение с сервера?" name="del_img"><span class="icon-remove"></span></button>
                        </span>
                        </div>
                    <?php endif;?>

                    <p><label>Alt:</label>
                        <input type="text" size="35" value="<?=$cat['img_alt'];?>" name="img_alt" placeholder="Альтернативный текст">
                    </p>
                    
                    <div class="width-100"><label>Описание категории:</label>
                        <textarea name="cat_desc"><?=$cat['cat_desc'];?></textarea>
                    </div>

                    <div class="width-100"><label>Хлебные крошки</label>
                        <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="breadcrumbs_status" type="radio" value="1"<?if($cat['breadcrumbs_status']) echo ' checked';?>><span>Показать</span></label>
                                    <label class="custom-radio"><input name="breadcrumbs_status" type="radio" value="0"<?if(!$cat['breadcrumbs_status']) echo ' checked';?>><span>Скрыть</span></label>
                                </span>
                    </div>

                    <div class="width-100"><label>Отдельные настройки блока HERO для этой категории</label>
                        <?php $hero_data = json_decode($cat['hero_params'], true); $hero_data = isset($hero_data['enabled']) && $hero_data['enabled'] == 1 ? $hero_data : null; ?>
                        <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="hero[enabled]" type="radio" value="1"<?if(isset($hero_data['enabled']) && $hero_data['enabled']) echo ' checked';?> data-show_on="CategoryHero"><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="hero[enabled]" type="radio" value="0"<?if(!isset($hero_data['enabled']) || isset($hero_data['enabled']) && !$hero_data['enabled']) echo ' checked';?> data-show_off="CategoryHero"><span>Выкл</span></label>
                                </span>
                    </div>

                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                </div>
                
                <div class="col-1-2">
                    <h4>SEO</h4>
                    <p class="width-100"><label>Алиас:</label>
                        <input type="text" name="alias" value="<?=$cat['alias'];?>" placeholder="Алиас категории">
                    </p>

                    <p class="width-100"><label>Title:</label>
                        <input type="text" name="title" value="<?=$cat['title'];?>" placeholder="Title категории">
                    </p>

                    <p class="width-100"><label>Meta Description:</label>
                        <textarea name="meta_desc" rows="3" cols="40"><?=$cat['meta_desc'];?></textarea>
                    </p>

                    <p class="width-100"><label>Meta Keys:</label>
                        <textarea name="meta_keys" rows="3" cols="40"><?=$cat['meta_keys'];?></textarea>
                    </p>


                </div>

            </div>
            <div id="CategoryHero">
            <h4 class="h4-border mt-20">Блок HERO</h4>
            <div class="row-line">
                <div class="col-1-2">
                    <div class="width-100"><label>Показать обложку HERO</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="hero[status]" type="radio" value="1"<?if(!isset($hero_data['status']) || $hero_data['status']) echo ' checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="hero[status]" type="radio" value="0"<?if(isset($hero_data['status']) && !$hero_data['status']) echo ' checked';?>><span>Выкл</span></label>
                        </span>
                    </div>

                    <div class="width-100"><label>Фоновое изображение:</label>
                        <div class="fon-wrap2">
                            <input id="fieldID" type="text" name="hero[img]" value="<?= $hero_data['img'] ?? $this->tr_settings['hero']?>">
                            <a href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=fieldID&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" class="btn iframe-btn" type="button">Выбрать изображение</a>
                        </div>
                    </div>

                    <div class="width-100"><label>Позиция фона:</label>
                        <div class="select-wrap">
                            <select name="hero[position]">
                                <option value="center center"<?= isset($hero_data['position']) && $hero_data['position'] == "center center" ? " checked" : $this->tr_settings['position'] = "center center" ? " checked" : ""?>>По центру</option>
                                <option value="center top"<?= isset($hero_data['position']) && $hero_data['position'] == "center top" ? " checked" : $this->tr_settings['position'] = "center top" ? " checked" : ""?>>Сверху и по центру </option>
                            </select>
                        </div>
                    </div>

                    <p class="px-label-wrap"><label>Высота блока:<span class="px-label">px</span></label>
                        <input type="text" name="hero[heroheigh]" value="<?= $hero_data['heroheigh'] ?? $this->tr_settings['heroheigh'] ?? "450" ?>">
                    </p>

                    <p class="px-label-wrap"><label>Высота блока на мобильных:<span class="px-label">px</span></label>
                        <input type="text" name="hero[heromobileheigh]" value="<?= $hero_data['heromobileheigh'] ?? $this->tr_settings['heromobileheigh'] ?? "450" ?>">
                    </p>
                </div>

                <div class="col-1-2">
                    <p class="width-100"><label>Оверлей цвет:</label><input type="color" name="hero[overlaycolor]" value="<?= $hero_data['overlaycolor'] ?? $this->tr_settings['overlaycolor'] ?? "450" ?>"></p>
                    <div class="width-100"><label>Оверлей прозрачность:</label>
                        <div class="select-wrap">
                            <select name="hero[overlay]">
                                <option value="1.0"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "1.0") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '1.0') echo ' selected="selected"';?>>1.0</option>
                                <option value="0.9"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.9") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.9') echo ' selected="selected"';?>>0.9</option>
                                <option value="0.8"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.8") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.8') echo ' selected="selected"';?>>0.8</option>
                                <option value="0.7"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.7") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.7') echo ' selected="selected"';?>>0.7</option>
                                <option value="0.6"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.6") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.6') echo ' selected="selected"';?>>0.6</option>
                                <option value="0.5"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.5") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.5') echo ' selected="selected"';?>>0.5</option>
                                <option value="0.4"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.4") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.4') echo ' selected="selected"';?>>0.4</option>
                                <option value="0.3"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.3") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.3') echo ' selected="selected"';?>>0.3</option>
                                <option value="0.2"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.2") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.2') echo ' selected="selected"';?>>0.2</option>
                                <option value="0.1"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.1") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.1') echo ' selected="selected"';?>>0.1</option>
                                <option value="0.0"<?php if(isset($hero_data['overlay']) && $hero_data['overlay'] == "0.0") { echo ' selected="selected"';} else if(isset($this->tr_settings['overlay']) && $this->tr_settings['overlay'] == '0.0') echo ' selected="selected"';?>>0.0</option>
                            </select>
                        </div>
                    </div>

                    <p class="width-100"><label>Заголовок блока:</label>
                        <input type="text" name="hero[heroheader]" value="<?= $hero_data['heroheader'] ?? $this->tr_settings['heroheader'] ?? "Наши тренинги" ?>">
                    </p>

                    <p class="width-100"><label>Цвет заголовка:</label>
                        <input type="color" name="hero[color]" value="<?= $hero_data['color'] ?? $this->tr_settings['color'] ?? "#ffffff" ?>">
                    </p>

                    <p class="px-label-wrap">
                        <label>Размер заголовка:<span class="px-label">px</span></label>
                        <input type="text" name="hero[fontsize]" value="<?= $hero_data['fontsize'] ?? $this->tr_settings['fontsize'] ?? "48" ?>">
                    </p>

                    <p class="px-label-wrap">
                        <label>Размер заголовка для мобильных:<span class="px-label">px</span></label>
                        <input type="text" name="hero[fontsize_mobile]" value="<?= $hero_data['fontsize'] ?? $this->tr_settings['fontsize'] ?? "20" ?>">
                    </p>
                </div>
            </div>



        </div>
    </form>
    
    <form action="/admin/delimg/<?=$cat['cat_id'];?>" id="del_img" method="POST">
        <input type="hidden" name="path" value="images/training/category/<?=$cat['cover'];?>">
        <input type="hidden" name="page" value="admin/training/editcat/<?=$cat['cat_id'];?>">
        <input type="hidden" name="table" value="training_cats">
        <input type="hidden" name="name" value="cover">
        <input type="hidden" name="where" value="cat_id">
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
    </form>

    <?php require_once (ROOT . '/extensions/training/layouts/admin/admin-footer.php');?>
</div>
</body>
</html>