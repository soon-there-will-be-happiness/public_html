<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');
/**
 * @var array $folders
 * @var array $files
 * @var array $storages
 */
?>
<?php if (!$storages) { ?>
    <script>
        alert("У вас не добавлено хранилищ");
        document.location.href="/admin/autobackup/storage";
    </script>
<?php die("<a href=\"/admin/autobackup/storage\">Перейти к хранилищам</a>"); } ?>
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
        <li>Создать задание</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data" id="backupForm">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/extensions/autobackup/web/images/task_edit.svg">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Создать задание</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" data-goal="1" name="addBackupTask" value="Создать" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/autobackup/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 no-margin-bottom">
                    <h4>Общие настройки</h4>
                </div>


                <div class="col-1-2 margin-30-bottom">
                    <p class="width-100"><label>Название</label><input type="text" name="name" placeholder="Название задания" required="required"></p>

                    <div class="width-100 margin-bottom-15"><label>Хранилище</label>
                        <div class="select">
                            <select name="selected_storages[]" multiple="multiple" class="multiple-select" size="20" id="backup_selected_storages">
                                <?php foreach ($storages as $storage) { ?>
                                    <option value="<?= $storage['id'] ?>"><?= $storage['title']  ?> - <?= adminBackupController::BUCKETS_LIST[$storage['type']] ?></option>
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
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                        </div>
                    </div>

                </div>



                <div class="col-1-2">
                    <p class="width-100"><label>Описание</label><textarea rows="4" cols="45" name="task_desc"></textarea></p>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>

                <div class="col-1-1 no-margin-bottom">
                    <h4>Периодичность</h4>
                </div>

                <div class="col-1-2 margin-30-bottom">
                    <div class="width-100"><label>Расписание</label>
                        <div class="select-wrap">
                            <select name="period[type]">
                                <option value="3">Каждый день</option>
                                <option value="2" data-show_on="from_days">По дням недели</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="width-100 hidden" id="from_days">
                        <label>Выберите день недели</label>
                        <div class="select-wrap">
                            <select name="period[day]">
                                <option value="1">Понедельник</option>
                                <option value="2">Вторник</option>
                                <option value="3">Среда</option>
                                <option value="4">Четверг</option>
                                <option value="5">Пятница</option>
                                <option value="6">Суббота</option>
                                <option value="0">Воскресенье</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="width-100">
                        <label>Время <span class="result-item-icon" data-toggle="popover" data-content="Лучше выбирать время, когда посетителей меньше всего. Например ночью. Сейчас: <?=date("H:i", time());?>"><i class="icon-answer"></i></span></label>
                        <input type="time" title="Текущее время на сервере <?=date("H:i:s", time() );?>" class="time-input-2" autocomplete="off" name="period[time]" value="00:00" required>
                    </div>
                </div>


                <div class="col-1-1 no-margin-bottom">
                    <h4>Состав резервной копии</h4>
                </div>

                <div class="col-1-2">
                    <div class="width-100 margin-bottom-15">
                        <label>Включить файлы в бекап</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="file_enable" type="radio" value="1" checked id="backupEnableFiles"><span>Вкл</span></label>
                            <label class="custom-radio"><input name="file_enable" type="radio" value="0" id="backupDisableFiles"><span>Выкл</span></label>
                        </span>
                    </div>
                    <div class="width-100 margin-bottom-15">
                        <label>Включить базу данных в бэкап</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="bd_enable" type="radio" value="1" checked="" id="backupEnableDb"><span>Вкл</span></label>
                            <label class="custom-radio"><input name="bd_enable" type="radio" value="0" id="backupDisableDb"><span>Выкл</span></label>
                        </span>
                    </div>
                    <div class="width-100 margin-bottom-15">
                        <label>Включить базу клиентов в csv</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="clients_enable" type="radio" value="1" id="backupEnableClients"><span>Вкл</span></label>
                            <label class="custom-radio"><input name="clients_enable" type="radio" value="0" id="backupDisableClients" checked><span>Выкл</span></label>
                        </span>
                    </div>
                </div>
                <div class="col-1-2 backupSize-BlockWrapper">
                    <div class="width-100" title="Примерные размеры бекапов. Желательно, чтобы свободного пространства на сервере было больше">
                        <div class="backupSizeWrapper">
                            <div class="backupSize-item"><div><b>Размер бекапа:</div> <span id="backupAllSize">...</span></b></div>
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
                            <label class="custom-radio"><input name="divide_to_parts" type="radio" value="1"><span>Да</span></label>
                            <label class="custom-radio"><input name="divide_to_parts" type="radio" value="0" checked=""><span>Нет</span></label>
                        </span>
                    </div>

                    <div class="width-100">
                        <label>Исключить папки</label>
                        <div class="select">
                            <select name="folder_exclude[]" multiple="multiple" class="multiple-select" size="20" id="backupFolderExclude">
                                <?php foreach ($folders as $folder) { ?>
                                    <option value="<?= $folderKey = str_replace(ROOT, "", $folder); ?>"
                                        <?= $folderKey == adminBackupController::DEFAULT_LOCAL_STORAGE_PATH ? "selected" : "" ?>
                                        <?= $folderKey == "/tmp" ? "selected" : "" ?>
                                    ><?= str_replace(ROOT, "", $folder); ?></option>
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
                        <textarea name="exclude_files" id="filesExcludeField"></textarea>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>

<script src="/extensions/autobackup/web/getBackupSize.js"></script>
<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">

</html>