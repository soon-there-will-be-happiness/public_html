<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Изменить автосерию писем | ID = <?=$delivery['delivery_id'];?></h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/responder/auto">Автосерии</a></li>
        <li>Изменить автосерию писем</li>
    </ul>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Изменить автосерию писем</h3>
            <ul class="nav_button">
                <li><input type="submit" name="edit" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/responder/auto/">Закрыть</a>
                </li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Действие</li>
            </ul>

            <div class="admin_form">
                <div>
                    <div class="row-line">
                        <div class="col-1-2">
                            <h4>Основное</h4>
                            <p class="width-100"><label>Название: </label>
                                <input type="text" name="name" value="<?=$delivery['name'];?>" placeholder="Название" required="required">
                            </p>

                            <div><label>Описание: </label>
                                <textarea name="desc" cols="45" rows="4"><?=$delivery['delivery_desc'];?></textarea>
                            </div>
                            <p class="width-100"><label>Перенаправление на страницу после  подписки на рассылку: </label>
                                <input type="text" name="redirect_url" placeholder="https://your-page/" value="<?=$delivery['redirect_url'];?>">
                            </p>
                            <input type="hidden" name="type" value="2">
                            <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                        </div>

                        <div class="col-1-1">
                            <h4>Требовать подтверждения:</h4>
                            <div class=""><label>Подтверждение e-mail: </label>
                                <div class="select-wrap">
                                    <select name="confirmation">
                                        <option value="0"<?php if($delivery['confirmation'] == 0) echo ' selected="selected"';?>>Нет</option>
                                        <option value="1"<?php if($delivery['confirmation'] == 1) echo ' selected="selected"';?>>Всегда</option>
                                        <option value="2"<?php if($delivery['confirmation'] == 2) echo ' selected="selected"';?>>При подписке через форму</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-1 mce-tinymce-wrap">
                            <h4>Текст после подтверждения email:</h4>
                            <textarea name="after_confirm_text" class="editor"><?=$delivery['after_confirm_text'];?></textarea>
                        </div>

                        <div class="col-1-1">
                            <h4>Письмо подтверждения:</h4>
                            <p class="width-100"><label>Тема письма: </label>
                                <input type="text" value="<?=$delivery['confirm_subject'];?>" name="confirm_subject">
                            </p>

                            <p class="width-100"><label>Текст письма: </label>
                                <textarea name="confirm_body" class="editor"><?=$delivery['confirm_body'];?></textarea>
                            </p>

                            <div class="width-100 tags_letter">
                                <p><strong>Теги для подстановки:</strong></p>
                                <p>[NAME] - имя подписчика<br>
                                    [DELIVERY] - имя рассылки<br>
                                    [EMAIL] - емейл подписчика
                                </p>
                                <p>[CONFIRM_LINK] - ссылка для подтверждения</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <h4>Подписка на рассылку</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>При подписке добавить группы пользователю</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="add_user_groups[]">
                                    <?php if($group_list = User::getUserGroups()):
                                        $add_groups = json_decode($delivery['add_user_groups'], true);
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>"<?php if($add_groups && in_array($user_group['group_id'], $add_groups)) echo ' selected="selected"';?>>
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
    jQuery('.datetimepicker').datetimepicker({
        format:'d.m.Y H:i',
        lang:'ru'
    });
</script>
<style>
    .mce-tinymce-wrap .mce-tinymce{
        width: 100% !important;
    }
</style>
</body>
</html>