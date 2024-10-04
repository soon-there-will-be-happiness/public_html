<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');
/**
 * @var array $folders
 * @var array $files
 * @var array $backupTask
 * @var array $storages
 */

$backupTask['period'] = json_decode($backupTask['period'], true);
$backupTask['folders_include'] = json_decode($backupTask['folders_include'], true);
$backupTask['folders_exclude'] = json_decode($backupTask['folders_exclude'], true);
$backupTask['files_exclude'] = json_decode($backupTask['files_exclude'], true);
?>
<?php if (!$storages) { ?>
    <script>
        alert("У вас не добавлено хранилищ");
        document.location.href="/admin/autobackup/storage";
    </script>
<?php
    die("<a href=\"/admin/autobackup/storage\">Перейти к хранилищам</a>");
} ?>

<style>
    .time-input-2 {
        width: 100%;
        border: 1px solid #d6d6d6;
        border-radius: 5px;
        padding: 0 15px;
        height: 31px;
        color: #000;
        font-size: 14px;
        font-family: 'Open Sans', sans-serif;
        background: #fff;
    }
</style>
<div class="main" style="padding-bottom: 90px;">
    <div class="top-wrap">
    <h1>Создать задание</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/autobackup/">Резервное копирование</a>
        </li>
        <li>Редактировать задание</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data" id="backupForm">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/extensions/autobackup/web/images/task_edit.svg">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Редактировать задание</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="addBackupTask" data-goal="1" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/autobackup/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">

                <div class="col-1-1 no-margin-bottom">
                    <h4>Общие настройки</h4>
                </div>

                <div class="col-1-2">
                    <p class="width-100"><label>Название</label>
                        <input type="text" name="name" placeholder="Название задания" required="required" value="<?= $backupTask['name'] ?>">
                    </p>

                    <div class="width-100"><label>Хранилище</label>
                        <div class="select">
                            <?php $backupTask['selected_storages'] = json_decode($backupTask['selected_storages'], true) ?? []; ?>
                            <select name="selected_storages[]" multiple="multiple" class="multiple-select" size="20" id="backup_selected_storages">
                                <?php foreach ($storages as $storage) { ?>
                                    <option value="<?= $storage['id'] ?>"  <?= in_array($storage['id'], $backupTask['selected_storages']) ? "selected" : "" ?>  ><?= $storage['title']  ?> - <?= adminBackupController::BUCKETS_LIST[$storage['type']] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="width-100">
                        <label>
                            Количество хранимых копий
                        </label>
                        <div class="select-wrap">
                            <select name="amount_copies">
                                <option value="2" <?= @$backupTask['amount_copies'] == 2 ? " selected" : "" ?>>2</option>
                                <option value="3" <?= @$backupTask['amount_copies'] == 3 ? " selected" : "" ?>>3</option>
                                <option value="4" <?= @$backupTask['amount_copies'] == 4 ? " selected" : "" ?>>4</option>
                                <option value="5" <?= @$backupTask['amount_copies'] == 5 ? " selected" : "" ?>>5</option>
                                <option value="6" <?= @$backupTask['amount_copies'] == 6 ? " selected" : "" ?>>6</option>
                                <option value="7" <?= @$backupTask['amount_copies'] == 7 ? " selected" : "" ?>>7</option>
                                <option value="8" <?= @$backupTask['amount_copies'] == 8 ? " selected" : "" ?>>8</option>
                                <option value="9" <?= @$backupTask['amount_copies'] == 9 ? " selected" : "" ?>>9</option>
                                <option value="10" <?= @$backupTask['amount_copies'] == 10 ? " selected" : "" ?>>10</option>
                                <option value="11" <?= @$backupTask['amount_copies'] == 11 ? " selected" : "" ?>>11</option>
                                <option value="12" <?= @$backupTask['amount_copies'] == 12 ? " selected" : "" ?>>12</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>
                <div class="col-1-2">
                    <div class="width-100">
                        <label>Статус: </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="status" type="radio" value="1" <?= $backupTask['status'] == 1 ? "checked" : "" ?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="status" type="radio" value="0" <?= $backupTask['status'] == 0 ? "checked" : "" ?>><span>Выкл</span></label>
                        </span>
                    </div>
                    <p class="width-100">
                        <label>Описание</label>
                        <textarea rows="4" cols="45" name="task_desc"><?= $backupTask['desc'] ?></textarea>
                    </p>
                </div>

                <div class="col-1-1 no-margin-bottom">
                    <h4>Периодичность</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100"><label>Расписание</label>
                        <div class="select-wrap">
                            <select name="period[type]">
                                <option value="3" <?= $backupTask['period']['type'] == 3 ? "selected" : "" ?>>Каждый день</option>
                                <option value="2" data-show_on="from_days" <?= $backupTask['period']['type'] == 2 ? "selected" : "" ?>>По дням недели</option>
                            </select>
                        </div>
                    </div>

                    <div class="width-100 hidden" id="from_days">
                        <label>Выберите день недели</label>
                        <div class="select-wrap">
                            <select name="period[day]">
                                <option value="1" <?= $backupTask['period']['day'] == 1 ? "selected" : "" ?>>Понедельник</option>
                                <option value="2" <?= $backupTask['period']['day'] == 2 ? "selected" : "" ?>>Вторник</option>
                                <option value="3" <?= $backupTask['period']['day'] == 3 ? "selected" : "" ?>>Среда</option>
                                <option value="4" <?= $backupTask['period']['day'] == 4 ? "selected" : "" ?>>Четверг</option>
                                <option value="5" <?= $backupTask['period']['day'] == 5 ? "selected" : "" ?>>Пятница</option>
                                <option value="6" <?= $backupTask['period']['day'] == 6 ? "selected" : "" ?>>Суббота</option>
                                <option value="0" <?= $backupTask['period']['day'] == 0 ? "selected" : "" ?>>Воскресенье</option>
                            </select>
                        </div>
                    </div>

                    <div class="width-100">
                        <label>Время <span class="result-item-icon" data-toggle="popover" data-content="Лучше выбирать время, когда посетителей меньше всего. Например ночью"><i class="icon-answer"></i></span></label>
                        <input type="time" title="Следующий запуск: <?=date("d.m.Y H:i", $backupTask['next_action']);?>" class="time-input-2" autocomplete="off" name="period[time]" value="<?= $backupTask['period']['time'] ?>">
                    </div>
                </div>



                <div class="col-1-1 no-margin-bottom">
                    <h4>Состав резервной копии</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100 margin-bottom-15">
                        <label>Включить файлы в бекап: </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="file_enable" type="radio" value="1" <?= $backupTask['files_enable'] == 1 ? "checked" : "" ?> id="backupEnableFiles"><span>Вкл</span></label>
                            <label class="custom-radio"><input name="file_enable" type="radio" value="0" <?= $backupTask['files_enable'] == 0 ? "checked" : "" ?> id="backupDisableFiles"><span>Выкл</span></label>
                        </span>
                    </div>
                    <div class="width-100 margin-bottom-15">
                        <label>Включить базу данных в бэкап: </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="bd_enable" type="radio" value="1" <?= $backupTask['bd_enable'] == 1 ? "checked" : "" ?> id="backupEnableDb"><span>Вкл</span></label>
                            <label class="custom-radio"><input name="bd_enable" type="radio" value="0" <?= $backupTask['bd_enable'] == 0 ? "checked" : "" ?> id="backupDisableDb"><span>Выкл</span></label>
                        </span>
                    </div>
                    <div class="width-100 margin-bottom-15">
                        <label>Включить базу клиентов в csv: </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="clients_enable" type="radio" value="1" <?= $backupTask['clients_enable'] == 1 ? "checked" : "" ?> id="backupEnableClients"><span>Вкл</span></label>
                            <label class="custom-radio"><input name="clients_enable" type="radio" value="0" <?= $backupTask['clients_enable'] == 0 ? "checked" : "" ?> id="backupDisableClients"><span>Выкл</span></label>
                        </span>
                    </div>
                </div>

                <div class="col-1-2 backupSize-BlockWrapper">
                    <div class="width-100" title="Примерные размеры бекапов. Желательно, чтобы свободного пространства на сервере было больше">
                        <div class="backupSizeWrapper">
                            <div class="backupSize-item"><div><b>Размер бекапа*:</div> <span id="backupAllSize">...</span></b></div>
                            <div class="backupSize-item">Файлы: <span id="backupFilesSize">...</span></div>
                            <div class="backupSize-item">БД: <span id="backupDbSize">...</span></div>
                            <div class="backupSize-item">База клиентов: <span id="backupClientsSize">...</span></div>
                        </div>
                    </div>
                </div>

                <div class="col-1-1 no-margin-bottom">
                    <h4>Расширенные настройки</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100 margin-bottom-15">
                        <label>Делить бекап на части
                            <span class="result-item-icon" data-toggle="popover" data-content="Бекап сохраняется по частям. Если на сервере мало свободного места - включите данный пункт">
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="divide_to_parts" type="radio" value="1" <?= $backupTask['divide_to_parts'] == 1 ? "checked" : "" ?>><span>Да</span></label>
                            <label class="custom-radio"><input name="divide_to_parts" type="radio" value="0" <?= $backupTask['divide_to_parts'] == 0 ? "checked" : "" ?>><span>Нет</span></label>
                        </span>
                    </div>
                    <div class="width-100"><label>Исключить папки</label>
                        <div class="select">
                            <select name="folder_exclude[]" multiple="multiple" class="multiple-select" size="20" id="backupFolderExclude">
                                <?php foreach ($folders as $folder) { ?>
                                    <option
                                            value="<?= str_replace(ROOT, "", $folder); ?>"
                                        <?= in_array(str_replace(ROOT, "", $folder), $backupTask['folders_exclude'] ?? []) ? "selected" : "" ?>
                                    ><?= str_replace(ROOT, "", $folder); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Исключить файлы
                            <span class="result-item-icon" data-toggle="popover" data-content="Какие файлы нужно исключить из бекапа. Указывается путь до файла от директории приложения. 1 строка - 1 файл">
                                <i class="icon-answer"></i>
                            </span>
                        </label>
                        <textarea name="exclude_files" id="filesExcludeField"><?php foreach ($backupTask['files_exclude'] as $file) { ?><?= $file.PHP_EOL ?><?php } ?></textarea>
                    </div>
                </div>

                <div class="col-1-2">

                </div>


            </div>
        </div>
    </form>
    
    
    <p class="button-delete" style="margin-bottom: 24px;">
        <a onclick="showDeleteAlert()">
            <i class="icon-remove"></i><?=System::Lang('DELETE_TASK');?>
        </a>
    </p>
</div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<div class="hidden block_fullscreen" style="display: flex; justify-content: center; align-items: center" id="BackupRemoveTask" onclick="this.classList.add('hidden')">
    <div class="inner_block_task_alert" id="inner_block_download_copy" onclick="event.stopPropagation()">
        <h4>Что удалить?</h4>
        <div class="row-line" style="justify-content: space-between">
            <a href="/admin/autobackup/removetask/<?=$backupTask['id'];?>?token=<?=$_SESSION['admin_token'];?>">Удалить задание</a>
            <a href="/admin/autobackup/removetask/<?=$backupTask['id'];?>?token=<?=$_SESSION['admin_token'];?>&withcopys=1">Удалить задание вместе с файлами </a>
        </div>
    </div>
</div>

<script>
    function showDeleteAlert() {
        document.getElementById("BackupRemoveTask").classList.remove("hidden");
    }
</script>


<script src="/extensions/autobackup/web/getBackupSize.js"></script>
<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">

</body>
</html>