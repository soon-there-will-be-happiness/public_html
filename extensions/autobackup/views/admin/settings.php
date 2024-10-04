<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<style>
.table-prods:hover {background:#efefef}
</style>
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Настройки автобекапов</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/extensions">Расширения</a>
        </li>
        <li>Настройки</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">
        
        <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Настройки автобекапов</h3>
                </div>
            </div>
            <ul class="nav_button">
                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                <li><input type="submit" name="save_ext" value="Сохранить" class="button save button-white font-bold" data-goal="1"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/extensions/">Закрыть</a></li>
            </ul>
        </div>
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 no-margin-bottom">
                    <h4>Общие настройки</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100"><label>Статус</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="status" type="radio" value="1" <?php if($enable == 1) echo 'checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="status" type="radio" value="0" <?php if($enable == 0) echo 'checked';?>><span>Откл</span></label>
                        </span>
                    </div>
                    
                    <div class="width-100">
                        <label><a title="Получить лицензию в личном кабинете" href="https://lk.school-master.ru/getlicense" target="_blank">Ключ лицензии</a></label>
                        <input type="text" name="params[license]" placeholder="" value="<?= $params['license'] ?? null ?>">
                    </div>
                    <div class="width-100">
                        <label>
                            Размер части копии
                            <span class="result-item-icon" data-toggle="popover" data-content="Примерный размер части, для разделения больших архивов. Прим: если, размер какого-либо файла больше чем выбранный размер, то размер этой части копии будет равен размеру этого файла"><i class="icon-answer"></i></span>
                        </label>
                        <div class="select-wrap">
                            <select name="params[part_size]">
                                <option value="100000000" <?= @$params['part_size'] == 100000000 ? " selected" : "" ?>>100 мб</option>
                                <option value="200000000" <?= @$params['part_size'] == 200000000 ? " selected" : "" ?>>200 мб</option>
                                <option value="300000000" <?= @$params['part_size'] == 300000000 ? " selected" : "" ?>>300 мб</option>
                                <option value="400000000" <?= @$params['part_size'] == 400000000 ? " selected" : "" ?>>400 мб</option>
                                <option value="500000000" <?= @$params['part_size'] == 500000000 ? " selected" : "" ?>>500 мб</option>
                                <option value="600000000" <?= @$params['part_size'] == 600000000 ? " selected" : "" ?>>600 мб</option>
                                <option value="1000000000" <?= @$params['part_size'] == 1000000000 ? " selected" : "" ?>>1000 мб</option>
                            </select>
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Уведомления</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input data-show_on="emails_to_report" name="params[sendEmail]" type="radio" value="1" <?php if(@$params['sendEmail'] == 1) echo 'checked'?>><span>Да</span></label>
                            <label class="custom-radio"><input data-show_off="emails_to_report" name="params[sendEmail]" type="radio" value="0" <?php if(@$params['sendEmail'] == 0) echo 'checked'?>><span>Нет</span></label>
                        </span>
                    </div>
                    <div class="width-100" id="emails_to_report">
                        <label>
                            Email для отправки сообщений
                            <span class="result-item-icon" data-toggle="popover" data-content="Если нужно указать несколько почт, то почты следует указывать через перенос строки. Один email - одна строка. <br>Если поле пустое, то отправка сообщений будет на email админа указанный в настройках"><i class="icon-answer"></i></span>
                        </label>
                        <textarea name="params[emails_to_report]" placeholder="example@mail.com
example2@mail.com"><?= !empty($params['emails_to_report']) ? implode(PHP_EOL, $params['emails_to_report']) : "" ?></textarea>
                    </div>
                </div>

                <div class="col-1-1 no-margin-bottom">
                    <h4>Умный бэкап</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100">
                        <label>Бэкап БД перед импортом пользователей</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="params[smart_backup_import]" type="radio" value="1" <?php if(@$params['smart_backup_import'] == 1) echo 'checked'?>><span>Да</span></label>
                            <label class="custom-radio"><input name="params[smart_backup_import]" type="radio" value="0" <?php if(@$params['smart_backup_import'] == 0) echo 'checked'?>><span>Нет</span></label>
                        </span>
                    </div>
                    <div class="width-100">
                        <label>Бэкап БД перед обновлением</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="params[smart_backup_update]" type="radio" value="1" <?php if(@$params['smart_backup_update'] == 1) echo 'checked'?>><span>Да</span></label>
                            <label class="custom-radio"><input name="params[smart_backup_update]" type="radio" value="0" <?php if(@$params['smart_backup_update'] == 0) echo 'checked'?>><span>Нет</span></label>
                        </span>
                    </div>
                    <?php
                        $storages = BackupTables::getBuckets(1) ?? [];
                        $selected_storages = $params['smart_backup_storages'] ?? [];
                    ?>
                    <div class="width-100 margin-bottom-15"><label>Хранилище</label>
                        <div class="select">
                            <select name="params[smart_backup_storages][]" multiple="multiple" class="multiple-select" size="20" id="backup_selected_storages">
                                <?php foreach ($storages as $storage) { ?>
                                    <option value="<?= $storage['id'] ?>" <?= in_array($storage['id'], $selected_storages) ? "selected" : "" ?>>
                                        <?= $storage['title']  ?> - <?= adminBackupController::BUCKETS_LIST[$storage['type']] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="col-1-1 no-margin-bottom">
                    <h4>Временные файлы</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100">
                        <div class="margin-bottom-15">
                            Размер временных файлов: <?= $tempSize ?? 0 ?> МБ
                        </div>
                        <div>
                            <input type="submit" name="cleartemp" value="Очистить" class="button-black-rounding">
                        </div>
                    </div>
                </div>

                <div class="col-1-1">
                    <div class="div">
                        <h4>Задание для крона / <span class="small" style="color: #0772A0;">раз в 60 минут</span></h4>
                        <?php $flow_cron =  System::getCronLog('backup_cron');

                        if (!empty($flow_cron)):?>
                        <p>Последний запуск был: <?=date("d-m-Y H:i:s", $flow_cron['last_run']);?>
                            <?php if($flow_cron['jobs_error'] == 1):?>
                                <div style="color:red"> Есть ошибки!</div></p>
                            <?php endif;?>
                        </p>
                        <?php endif;?>
                        <input type="text" value="<?= $params['php_path'] ?? BackupCronHandler::DEFAULT_PHP_PATH ?> <?php echo str_replace('\\',"/", ROOT); ?>/extensions/autobackup/task/autobackup_cron.php">
                    </div>
                    <div class="col-1-2">
                        <p class="width-100" style="margin-top: 12px">
                            <label>
                                Путь до php
                                <span class="result-item-icon" data-toggle="popover" data-content="Путь до исполняемого файла php. Измените это значение, если текущая версия php - альтернативная"><i class="icon-answer"></i></span>
                            </label>
                            <input type="text" name="params[php_path]" placeholder="" value="<?= $params['php_path'] ?? BackupCronHandler::DEFAULT_PHP_PATH ?>">
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <div class="row-line" style="justify-content: space-between; margin-top: 0px;">
            <div class="reference-link">
                <a class="button-green-rounding" href="/admin/autobackup" style="height: 30px;">Перейти к бэкапам</a>
            </div>
            <div class="reference-link">
                <a class="button-blue-rounding" target="_blank" href="https://lk.school-master.ru/rdr/59">
                    <i class="icon-info"></i>Справка по расширению
                </a>
            </div>
        </div>
    </form>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">
</body>
</html>