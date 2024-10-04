<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');?>

<div class="main">
    <div class="top-wrap">
        <h1>Хедер</h1>

        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/extensions/?type=template">Шаблоны</a></li>
        <li>Настройки шаблона</li>
    </ul>

    <?if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно</div>
    <?endif;?>

    <form enctype="multipart/form-data" action="" method="POST" >
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Хедер</h3>

            <ul class="nav_button">
                <li><input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/extensions/?type=template">Закрыть</a></li>
            </ul>
        </div>

        <div class="admin_form">
            <h4 class="h4-border">Содержимое шапки</h4>

            <div class="row-line">
                <div class="col-1-2">
                    <div class="width-100"><label>Логотип сайта:</label>
                        <input id="fieldID" type="text" name="settings[logotype]" value="<?if(!empty($settings['logotype'])) echo $settings['logotype'];?>" >
                        <a href="javascript:void(0)" onclick="javascript:window.open('/lib/file_man/filemanager/dialog.php?type=1&popup=1&field_id=fieldID&relative_url=0', 'okno', 'width=845, height=400, status=no, toolbar=no, menubar=no, scrollbars=yes, resizable=yes')" class="btn iframe-btn" type="button">Выбрать изображение</a>

                        <?if(!empty($settings['logotype'])):?>
                            <div style="margin:1em 0">
                                <img src="<?=$settings['logotype'];?>" alt="">
                            </div>
                        <?endif;?>
                    </div>

                    <div class="width-100"><label>Слоган</label>
                        <input type="text" name="main_settings[slogan]" size="25" value="<?=$main_settings ? $main_settings['slogan'] : '';?>">
                    </div>

                    <div class="width-100"><label>Зафиксировать шапку:</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="settings[fix_head]" type="radio" value="1"<?if($settings['fix_head'] == '1') echo ' checked';?>><span>Да</span></label>
                            <label class="custom-radio"><input name="settings[fix_head]" type="radio" value="0"<?if($settings['fix_head'] == '0') echo ' checked';?>><span>Нет</span></label>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
    </form>

    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>