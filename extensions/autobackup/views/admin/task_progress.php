<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Резервное копирование. Задачи</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/autobackup">Резервное копирование</a></li>
        <li>Задачи</li>
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

    <style>
        .course-list-item__center {
            width: 62%;
        }
        .extension-status {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-around;
        }
        .button-red {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .button-red:hover {
            background: #E04265;
            color: #fff;
        }
    </style>
 
    <span id="notification_block"></span>
    <h2 style="font-size: 22px">Делаем резервную копию</h2>
    <div>
        <input type="hidden" name="sort_upd_url" value="/admin/products/updatesort">
            <div class="course-list-item backuprestorewrapper">
                <div class="d-flex">
                    <div class="course-list-item__left">
                        <img src="/extensions/autobackup/web/images/<?= $task['status'] ? 'task_on.svg' : 'task_off.svg'; ?>" alt="">
                    </div>

                    <div class="course-list-item__center">
                        <div class="row-line backupTask_title">
                            <h4 class="course-list-item__name">
                                <a href="/admin/autobackup/edittask/<?= $task['id'] ?>">
                                    <?= $task['name'] ?>
                                </a>
                            </h4>
                            <div class="storages">
                                <?php $task_storages_types = BackupTables::getSelectedStoragesForTask($task) ?? []; ?>
                                <?= in_array(0, $task_storages_types) ? '<img src="/extensions/autobackup/web/images/ftp.svg">' : "" ?>
                                <?= in_array(1, $task_storages_types) ? '<img src="/extensions/autobackup/web/images/yandex.svg">' : "" ?>
                                <?= in_array(2, $task_storages_types) ? '<img src="/extensions/autobackup/web/images/dropbox.svg">' : "" ?>
                                <?= in_array(3, $task_storages_types) ? '<img src="/extensions/autobackup/web/images/s3.svg">' : "" ?>
                                <?= in_array(4, $task_storages_types) ? '<img src="/extensions/autobackup/web/images/local.svg">' : "" ?>
                                <?= in_array(5, $task_storages_types) ? '<img src="/extensions/autobackup/web/images/google-drive.svg">' : "" ?>
                            </div>
                        </div>

                        <div class="course-list-item__data" style="flex-direction: column;">
                            <div class="backupTask_methods">
                                <?php if ($task['files_enable']) { ?>
                                    <span class="backupTaskmethod"><i class="icon-list"></i>Файлы&nbsp;&nbsp;</span>
                                <?php } ?>
                                <?php if ($task['bd_enable']) { ?>
                                    <span class="backupTaskmethod"><i class="icon-list"></i>БД&nbsp;</span>
                                <?php } ?>
                                <?php if ($task['clients_enable']) { ?>
                                    <span class="backupTaskmethod"><i class="icon-list"></i>База клиентов&nbsp;</span>
                                <?php } ?>
                            </div>
                        </div>

                    </div>

                    <div class="extension-status">
                    </div>
                </div>
                <?php
                $lastActionId = 0;
                if($tasks_progress) { ?>
                    <div class="progress_text">
                        Прогресс: <span id="progressInt"></span>%
                    </div>
                    <div class="progress">
                        <div class="bar" id="progressbar"></div>
                    </div>

                    <div class="backup_messages_wrapper">
                        <div class="header"><b>Процесс:</b></div>
                        <div class="messages" id="backup_messages">
                            <?php
                            foreach ($tasks_progress as $action) { $lastActionId = $action['id'] ?>
                                <div class="message"><?php
                                    if ($action['executing_action'] != "finished") {
                                        echo BackupProgress::ACTIONS[$action['executing_action']];
                                    } elseif ($action['is_error'] == 1) {
                                        echo "Задача не завершена";
                                    } else {
                                        echo BackupProgress::ACTIONS[$action['executing_action']];
                                    }
                                ?></div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } else { ?>
                    <div style="margin-top: 8px">
                        <p>Задание не запущено.<br> Возможно, произошла ошибка при запуске. Обновите страницу. Если это не помогает, то <a href="/admin/logs/">проверьте логи</a></p>
                    </div>
                <?php } ?>

                <?php
                    $haveErrors = false;
                    foreach ($tasks_progress as $action) {
                        if ($action['is_error']) {
                            $haveErrors = true;
                            break;
                        }
                    }
                ?>

                <div class="backup_errors_wrapper <?= $haveErrors ? "" : "hidden" ?>" id="backup_errors_block">
                    <div class="header"><b>Ошибки</b></div>
                    <div class="messages" id="backup_errors">
                        <?php foreach ($tasks_progress as $action) { if ($action['is_error']) {  ?>
                            <div class="message"><?= $action['executing_action'].":".$action['message']?></div>
                        <?php } } ?>
                    </div>
                </div>




            </div>

        <?php if (isset($_GET['back']) && !empty($_GET['back'])) { ?>
            <?php if (isset($_GET['type']) || $_GET['type'] == 0) { ?>

            <?php } else { ?>
                <div style="margin-top: 8px;">
                    <a href="<?= $_GET['back'] ?? '/admin' ?>">Назад</a>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">
<script src="/extensions/autobackup/web/backupProgress.js"></script>

<script>
    let last_id = "<?=$lastActionId?>";
    const task_id = "<?= $task['id'] ?>";
    const uid = "<?= $_GET['uid'] ?? 0 ?>";
    document.addEventListener("DOMContentLoaded", function () {
        setProgress(<?= $progress ?>)
        checkToNewData(task_id, last_id);
    });

    async function checkToNewData(taskid, lastid) {
        let response = await fetch("/admin/autobackup/api/progress/" + taskid + "?lastid=" + lastid + "&uid=" + uid);
        if (response.status != 201) {
            checkToNewData(taskid, lastid);
        } else {

            let data = await response.json();
            setProgress(data.progress);
            last_id = setNewData(data.data);
            checkToNewData(taskid, last_id);
        }

    }

    function setNewData(messages) {
        let responselastid;
        messages.forEach(function(message) {
            document.getElementById("backup_messages").innerHTML += '<div class="message">' + message.executing_action + '</div>';
            if (message.is_error === "1") {
                document.getElementById("backup_errors_block").classList.remove("hidden");
                document.getElementById("backup_errors").innerHTML += '<div class="message">' + message.message + '</div>';
            }
            responselastid = message.id;
        });

        return responselastid;
    }

</script>
</body>
</html>