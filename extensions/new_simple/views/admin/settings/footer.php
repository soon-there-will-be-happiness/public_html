<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');
$settings['socbut'] = unserialize(base64_decode($settings['socbut']));?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');?>

<div class="main">
    <div class="top-wrap">
        <h1>Футер</h1>

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

    <form action="" method="POST">
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Футер</h3>

            <ul class="nav_button">
                <li><input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/extensions/?type=template">Закрыть</a></li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Документы</li>
            </ul>

            <div class="admin_form">
                <div>
                    <h4 class="h4-border">Содержимое в футере</h4>

                    <div class="row-line">
                        <div class="col-1-2">
                            <div class="width-100"><label>Instagram</label>
                                <input type="text" name="settings[socbut][instagram]" value="<?if(isset($settings['socbut']['instagram'])) echo $settings['socbut']['instagram']?>">
                            </div>

                            <div class="width-100"><label>Telegram</label>
                                <input type="text" name="settings[socbut][tg]" value="<?if(isset($settings['socbut']['tg'])) echo $settings['socbut']['tg']?>">
                            </div>

                            <div class="width-100"><label>Youtube</label>
                                <input type="text" name="settings[socbut][youtube]" value="<?if(isset($settings['socbut']['youtube'])) echo $settings['socbut']['youtube']?>">
                            </div>

                            <div class="width-100"><label>Вконтакте</label>
                                <input type="text" name="settings[socbut][vk]" value="<?if(isset($settings['socbut']['vk'])) echo $settings['socbut']['vk']?>">
                            </div>

                            <div class="width-100"><label>Facebook</label>
                                <input type="text" name="settings[socbut][fb]" value="<?if(isset($settings['socbut']['fb'])) echo $settings['socbut']['fb']?>">
                            </div>

                            <div class="width-100 mb-40"><label>Одноклассники</label>
                                <input type="text" name="settings[socbut][ok]" value="<?if(isset($settings['socbut']['ok'])) echo $settings['socbut']['ok']?>">
                            </div>

                            <div class="width-100 mb-40"><label>Яндекс Дзен</label>
                                <input type="text" name="settings[socbut][dzen]" value="<?if(isset($settings['socbut']['dzen'])) echo $settings['socbut']['dzen']?>">
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><div class="label">Копирайты</div>
                                <textarea name="main_settings[copyright]" rows="3" cols="55"><?=$main_settings['copyright'];?></textarea>
                            </div>

                            <input type="hidden" name="settings[token]" value="<?=$_SESSION['admin_token'];?>">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4 class="h4-border">Политика обработки персональных данных</h4>
                        </div>

                        <div class="col-1-1">
                            <div class="width-100"><label>Текст для ссылки</label>
                                <input type="text" name="main_settings[politics_link]" value="<?=$main_settings['politika_link']?>">
                            </div>

                            <div class="width-100"><label>Содержимое</label>
                                <textarea class="editor" type="text" name="main_settings[politics_text]" rows="3" cols="55"><?=$main_settings['politika_text']?></textarea>
                            </div>
                        </div>


                        <div class="col-1-1 mb-0">
                            <h4 class="h4-border">Договор оферты</h4>
                        </div>

                        <div class="col-1-1">
                            <div class="width-100"><label>Текст для ссылки</label>
                                <input type="text" name="main_settings[offer_link]" value="<?=$main_settings['oferta_link']?>">
                            </div>

                            <div class="width-100"><label>Содержимое</label>
                                <textarea class="editor" type="text" name="main_settings[offer_text]" rows="3" cols="55"><?=$main_settings['oferta_text']?></textarea>
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