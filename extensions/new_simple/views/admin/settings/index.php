<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');
$ext_main_settings = isset($ext_settings['main']) ? $ext_settings['main'] : null;?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');?>

<div class="main">
    <div class="top-wrap">
        <h1>Общие настройки</h1>

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

    <form enctype="multipart/form-data" action="" method="POST">
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Настройки шаблона</h3>

            <ul class="nav_button">
                <li><input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/extensions/?type=template">Закрыть</a></li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Свой код</li>
            </ul>

            <div class="admin_form">
                <div>
                    <h4 class="h4-border">Основное</h4>

                    <div class="row-line">
                        <div class="col-1-2">
                            <div style="display:none" class="width-100"><label>Шаблон по умолчанию</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1" <?if($enable == 1) echo 'checked';?>><span>Да</span></label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0" <?if($enable == 0) echo 'checked';?>><span>Нет</span></label>
                                </span>
                            </div>
                            

                            <div class="width-100 hidden"><label>Телефон</label>
                                <input type="text" name="settings[phone]" placeholder="8(495) 123-34-34" value="<?=$settings['phone'];?>">
                            </div>

                            <div class="width-100 hidden"><label>Телефон (ссылка)</label>
                                <input type="text" name="settings[phone_link]" placeholder="+74951233443" value="<?=$settings['phone_link'];?>">
                            </div>

                            <div class="width-100"><label>Позиция Sidebar</label>
                                <div class="select-wrap">
                                    <select name="main_settings[sidebar]">
                                        <option value="left"<?if(isset($main_settings['sidebar']) && $main_settings['sidebar'] == 'left') echo ' selected="selected"';?>>Слева</option>
                                        <option value="right"<?if(isset($main_settings['sidebar']) && $main_settings['sidebar'] == 'right') echo ' selected="selected"';?>>Справа</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="h4-border">Свой код</h4>

                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="width-100">
                                <h4>Код в head</h4>
                                <textarea name="settings[counters_head]" cols="55" rows="6"><?=$settings['counters_head'];?></textarea>
                            </div>
                        </div>

                        <div class="col-1-1">
                            <div class="width-100">
                                <h4>Счётчики (перед /body)</h4>
                                <textarea name="settings[counters]" cols="55" rows="6"><?=$settings['counters'];?></textarea>
                            </div>
                        </div>


                        <div class="col-1-1">
                            <h4>Свои CSS стили</h4>
                            <div class="width-100">
                                <textarea name="main_settings[custom_css]" style="height:550px"><?=$main_settings['custom_css'];?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
    </form>

    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>