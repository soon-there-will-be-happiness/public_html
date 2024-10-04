<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');
/**
 * @var $copys array
 */
?>

<style>
    .flex-wrap {
        flex-wrap: wrap;
    }
</style>
<div class="main">
    <div class="top-wrap">
        <h1>Резервные копии</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/autobackup/">Резервное копирование</a></li>
        <li>Резервные копии</li>
    </ul>
    
    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            
            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent-red">
                    <a href="/admin/autobackup/addtask" class="nav-click button-red-rounding">Создать задание</a>
                </div>
            </li>
            
            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent nav_gorizontal__parent-yellow">
                    <a href="javascript:void(0);" class="nav-click button-yellow-rounding">Хранилища</a>
                    <span class="nav-click icon-arrow-down"></span>
                </div>
                <ul class="drop_down">
                    <li><a href="/admin/autobackup/storage/add/" class="text-decoration-none">Добавить хранилище</a></li>
                    <li><a href="/admin/autobackup/storage/" class="text-decoration-none">Список хранилищ</a></li>
                    <li><a href="/admin/autobackup/copys/" class="text-decoration-none">Сделанные бэкапы</a></li>
                </ul>
            </li>

            <li><a title="Настройки автобекапов" class="settings-link" href="/admin/autobackupsettings/"><i class="icon-settings-bold"></i></a></li>

        </ul>
    </div>


 
    <span id="notification_block"></span>


    <div class="filter admin_form" style="background: none; padding: 0;">
        <form action="" method="GET">
            <div class="filter-row" style="flex-wrap: nowrap">
                <div style="width: 25%">
                    <div class="select-wrap">
                        <select name="task_id">
                            <option value="">Задание</option>
                            <?php foreach ($allTasks as $task) { ?>
                                <option value="<?=$task['id']?>" <?= $_GET['task_id'] ?? "" == $task['id'] ? " selected" : "" ?>><?=$task['name']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="" style="width: 25%">
                    <div class="datetimepicker-wrap" style="background: #fff;">
                        <input type="text" class="datetimepicker" name="mindate" placeholder="с" value="<?= $_GET['mindate'] ?? "" ?>" autocomplete="off">
                    </div>
                </div>

                <div class="" style="width: 25%">
                    <div class="datetimepicker-wrap" style="background: #fff;">
                        <input type="text" class="datetimepicker" name="maxdate" placeholder="с" value="<?= $_GET['maxdate'] ?? "" ?>" autocomplete="off">
                    </div>
                </div>

                <div style="width: 25%">
                    <div class="button-group">
                        <input class="button-blue-rounding" type="submit" name="filter" value="Найти" style="width: 100%;">
                        <!--<a class="red-link" href="/admin/products">Сбросить</a>-->
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="course-list">
        <input type="hidden" name="sort_upd_url" value="/admin/products/updatesort">
        <?php if($copys):
            foreach($copys as $copyKey => $copy): ?>
                <div class="course-list-item iscopy d-flex">
                    <div class="course-list-item__left" style="display: flex; align-items: center; justify-content: center;">
                        <img src="/extensions/autobackup/web/images/<?= empty($copy['copys']) ? "notsuccess.svg": "success.svg" ?>" width="24" height="24" alt="">
                    </div>

                    <div class="course-list-item__center">
                        <div class="row-line backupTask_title">
                            <h4 class="course-list-item__name">
                                <?= $copy['is_smart'] ?
                                    "Умный бекап. " . SmartBackup::getNameByType($copy['smart_type'])
                                    : $copy['task']['name']
                                ?>
                            </h4>
                            <div class="storages">
                                <?= in_array(0, $copy['bucketTypes']) ? '<img src="/extensions/autobackup/web/images/ftp.svg">' : "" ?>
                                <?= in_array(1, $copy['bucketTypes']) ? '<img src="/extensions/autobackup/web/images/yandex.svg">' : "" ?>
                                <?= in_array(2, $copy['bucketTypes']) ? '<img src="/extensions/autobackup/web/images/dropbox.svg">' : "" ?>
                                <?= in_array(3, $copy['bucketTypes']) ? '<img src="/extensions/autobackup/web/images/s3.svg">' : "" ?>
                                <?= in_array(4, $copy['bucketTypes']) ? '<img src="/extensions/autobackup/web/images/local.svg">' : "" ?>
                                <?= in_array(5, $copy['bucketTypes']) ? '<img src="/extensions/autobackup/web/images/google-drive.svg">' : "" ?>
                            </div>
                        </div>
                        <div class="course-list-item__data" style="flex-direction: column;">
                            <div>
                                <div class="row-line">
                                    <span class="backupTaskmethod">
                                        <i class="icon-time"></i><?= $copy['date'] ? date("d.m.Y H:i:s", $copy['date']) : "не запускалось" ?>
                                    </span>
                                    <span class="backupTaskmethod size">
                                        <img src="/extensions/autobackup/web/images/size.svg" width="16">&nbsp;
                                        <?= $copy['fullSize'] > 1000000 ?
                                            round($copy['fullSize'] / 1000 / 1000, 2)." МБ" :
                                            round($copy['fullSize'] / 1000, 2)." КБ";
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="backupTask_methods">
                                <?= in_array("db", $copy['types']) ? '<span class="backupTaskmethod"><i class="icon-list"></i>'."БД</span>" : "" ?>
                                <?= in_array("file", $copy['types']) ? '<span class="backupTaskmethod"><i class="icon-list"></i>'."файлы</span>" : "" ?>
                                <?= in_array("clients", $copy['types']) ? '<span class="backupTaskmethod"><i class="icon-list"></i>'."Клиенты в csv</span>" : "" ?>
                            </div>
                        </div>

                    </div>

                    <div class="extension-status">
                        <?php if (!empty($copy['copys']))  { ?>
                            <a class="button-red" onclick="showBackupRestoreAlert('backup_restore_<?= $copyKey ?>')" id="backup_restore_<?= $copyKey ?>" data-copy='<?= json_encode($copy) ?>'>
                                <img src="/extensions/autobackup/web/images/restore.png" width="16">&nbsp;Восстановить
                            </a>
                            <?php if (!$copy['is_smart']) { ?>
                                <a class="text-decoration-none" onclick="showBackupDownloadAlert('backup_restore_<?= $copyKey ?>')">Скачать</a>
                            <?php } ?>
                        <?php } else { ?>
                        <?php } ?>
                    </div>
                </div>
            <?php endforeach;
        else: ?>
            <div class="course-list-item">
                <p>
                    Нет сделанных копий
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<div class="hidden block_fullscreen" style="display: flex; justify-content: center; align-items: center" id="BackupRestoreAlert">
    <form id="restore_form" action="/" method="post">
        <div class="inner_block_task_alert" id="inner_block_task_alert">
            <h1 style="font-size: 24px;">Восстановить резервную копию?</h1>
            <div>
                <p>После нажатия восстановить будет запущен процесс восстановления из резервной копии.</p>
                <p>Восстановление может занять время.</p>
                <p>Не закрывайте страницу с восстановлением пока не завершится процесс.</p>
            </div>
            <div class="row-line">
                <div class="col-1-2">
                    <label>Выберете источник</label>
                    <span class="custom-radio-wrap bucket_type_restore">
                        <label class="custom-radio hidden" id="from_bucket_type_0">
                            <input name="from_bucket" type="radio" value="0" required><span>Ftp</span>
                        </label>
                        <label class="custom-radio hidden" id="from_bucket_type_1">
                            <input name="from_bucket" type="radio" value="1" required><span>Я.диск</span>
                        </label>
                        <label class="custom-radio hidden" id="from_bucket_type_2">
                            <input name="from_bucket" type="radio" value="2" required><span>Dropbox</span>
                        </label>
                        <label class="custom-radio hidden" id="from_bucket_type_3">
                            <input name="from_bucket" type="radio" value="3" required><span>S3</span>
                        </label>
                        <label class="custom-radio hidden" id="from_bucket_type_4">
                            <input name="from_bucket" type="radio" value="4" required><span>Локальное</span>
                        </label>
                        <label class="custom-radio hidden" id="from_bucket_type_5">
                            <input name="from_bucket" type="radio" value="4" required><span>Google drive</span>
                        </label>
                    </span>
                </div>
                <div class="col-1-2">
                    <label>Что восстанавливать?</label>
                    <div class="select-wrap bucket_type_restore">
                        <select name="restore_type" id="restore_type">
                            <option value="0">Всё</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="btnwrapper">
                <input type="hidden" name="task_id" id="restore_copy_task_id">
                <input type="hidden" name="backup_date" id="restore_copy_date">
                <input type="submit" class="button-red" value="Восстановить" data-goal="1" style="font-size: 14px;">
            </div>
        </div>
    </form>
</div>

<div class="hidden block_fullscreen" style="display: flex; justify-content: center; align-items: center" id="BackupRestoreDownload">
    <div class="inner_block_task_alert" id="inner_block_download_copy">
        <h4>Что скачивать?</h4>
        <div class="row-line" style="justify-content: space-between">
            <a class="hidden" href="/admin/autobackup/copys/download/" target="_blank" id="backup_download_file">Бэкап файлов</a>
            <a class="hidden" href="/admin/autobackup/copys/download/" target="_blank" id="backup_download_db">Бэкап базы данных</a>
            <a class="hidden" href="/admin/autobackup/copys/download/" target="_blank" id="backup_download_clients">Клиенты в CSV</a>
        </div>
    </div>
</div>

<div
     id="loaderBackupTask"
     class="hidden block_fullscreen"
     title="Ожидание запуска задания..."
     style="
     background-image: url('/template/admin/images/spinner2.gif');
     background-repeat: no-repeat; background-position: center;
     background-size: 2%;
     "
></div>

<script src="/extensions/autobackup/web/backupRestore.js"></script>

<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
    jQuery('.datetimepicker').datetimepicker({
        format:'d.m.Y H:i',
        lang:'ru'
    });
</script>
</body>
</html>