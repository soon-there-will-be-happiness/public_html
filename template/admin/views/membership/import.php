<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Импорт подписок</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a><a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/memberusers/">Участники</a></li>
        <li>Импорт подписок</li>
    </ul>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="is_new" value="0">
        <?php if(isset($_GET['success'])):?>
            <div class="admin_message">
                Всего записей:  <?=intval($_GET['total']);?>
                <br />Успешно добавлено пользователей: <?=intval($_GET['success']);?>
                <br />Дублей: <?=$_GET['dupl'];?>
                <br />Исключены: <?=$_GET['wrong'];?>
            </div>
        <?php endif;?>
    
        <?php if(isset($_GET['fail'])):?>
            <div class="admin_warning">Ни один пользователь не добавлен</div>
        <?php endif;?>

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/import-user.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Импорт</h3>
                </div>
            </div>

            <ul class="nav_button">
                <li><input type="submit" name="import" value="Импортировать" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a href="<?=$setting['script_url'];?>/admin/users/">Закрыть</a></li>
            </ul>
        </div>

        <div class="admin_form">
            <h4 class="h4-border"><?=System::Lang('BASIC');?></h4>
            <div class="row-line">
                <div class="col-1-1">
                    <div class="width-100">
                        <label>Выберите файл (csv)
                            <span class="result-item-icon" data-toggle="popover" data-content='Разделитель "," или ";"<br>Формат даты "30" или "1666089999"'><i class="icon-answer"></i></span>
                        </label>
                        <input type="file" name="file">
                    </div>
                </div>
                <div class="col-1-2">
                    <h4><strong>Порядок полей</strong></h4>
                    <div class="width-100"><label>1 поле: </label>
                        <div class="select-wrap">
                            <select name="first_field">
                                <option value="name" selected>Имя</option>
                                <option value="email">Email</option>
                                <option value="subsId">Id подписки</option>
                                <option value="expire">Дата окончания</option>
                            </select>
                        </div>
                    </div>
                    <div class="width-100"><label>2 поле: </label>
                        <div class="select-wrap">
                            <select name="second_field">
                                <option value="name">Имя</option>
                                <option value="email" selected>Email</option>
                                <option value="subsId">Id подписки</option>
                                <option value="expire">Дата окончания</option>
                            </select>
                        </div>
                    </div>
                    <div class="width-100"><label>3 поле:</label>
                        <div class="select-wrap">
                            <select name="third_field">
                                <option value="name">Имя</option>
                                <option value="email">Email</option>
                                <option value="subsId" selected>Id подписки</option>
                                <option value="expire">Дата окончания</option>
                            </select>
                        </div>
                    </div>
                    <div class="width-100"><label>4 поле:</label>
                        <div class="select-wrap">
                            <select name="fourth_field">
                                <option value="name">Имя</option>
                                <option value="email">Email</option>
                                <option value="subsId">Id подписки</option>
                                <option value="expire" selected>Дата окончания</option>
                            </select>
                        </div>
                    </div>




                </div>

                <div class="col-1-2">
                    <h4><strong>Настройки</strong></h4>
                    <div class="width-100"><label>Разделитель</label>
                        <input type="text" name="separator" value=";">
                    </div>

                    <!--<div class="width-100"><label>Если имя отсутствует, заменять на:</label>
                        <input type="text" name="empty_name" value="Дорогой друг">
                    </div>-->

                    <div class="width-100"><label>Отправить пароль пользователю?</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="sendEmail" type="radio" value="1" checked><span>Да</span></label>
                            <label class="custom-radio"><input name="sendEmail" type="radio" value="0"><span>Нет</span></label>
                        </span>
                    </div>

                    <!--<div class="width-100"><label class="custom-chekbox-wrap">
                            <input type="checkbox" value="1" name="is_client" checked>
                            <span class="custom-chekbox"></span>Клиент?
                        </label></div>

                    <div class="width-100"><label class="custom-chekbox-wrap">
                            <input type="checkbox" value="1" name="is_partner">
                            <span class="custom-chekbox"></span>Партнер?
                        </label></div>
                    <div class="width-100"><label class="custom-chekbox-wrap" title="Если галочка стоит, то все импортированые пользователи будут подписаны на рассылку">
                            <input type="checkbox" value="1" name="is_subs">
                            <span class="custom-chekbox"></span>Получать рассылку?
                        </label></div>-->

                    <div class="width-100"><label>Тип даты окончания</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="expireType" type="radio" value="1" checked><span title="Запись принимается в кол-ве дней. Пример: '30'">Относительный</span></label>
                            <label class="custom-radio"><input name="expireType" type="radio" value="0"><span title="Подписка окончиться как указано в записи(unix timestamp). Пример: '1666089999'">Абсолютный</span></label>
                        </span>
                    </div>

                    <!--<div class="width-100"><label class="custom-chekbox-wrap">
                        <input type="checkbox" value="1" name="is_client">
                        <span class="custom-chekbox"></span>Отправить пароль пользователю?
                    </label></div>-->
                </div>
                <?php if (isset($_POST['import'])) { ?>
                    <div class="col-1-1">
                        <h4>Результаты импорта:</h4>
                        <div>Всего записей: <?=$countAll ?> </div>
                        <div>Успешно импортировано: <?=$successCount ?> </div>

                        <?php if(isset($errors)) {?>
                            <div><b>Ошибки в файле:</b></div>
                            <?php foreach ($errors as $key=> $error) {
                                $line = $key + 1?>
                                <div> <?= "Строка $line : $error"?></div>
                        <?php } } ?>

                        <?php if(isset($serverErrors[0])) {?>
                            <div><b>Сервер:</b></div>
                            <?php foreach ($serverErrors as $serverError) { ?>
                                <div><?= json_encode($serverError) ?></div>
                        <?php }} ?>
                    </div>
                <?php } ?>
            </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    <?php $title = 'Импорт пользователей';require_once(ROOT . '/lib/progressbar/html.php');?>
</div>
</body>
</html>