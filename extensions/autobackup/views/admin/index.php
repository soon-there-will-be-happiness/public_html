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
 
    <span id="notification_block"></span>

    <div class="course-list">
        <input type="hidden" name="sort_upd_url" value="/admin/products/updatesort">
        <?php if($tasks):
            foreach($tasks as $task):
                $period = json_decode($task['period'], true);
        ?>
                <div class="course-list-item d-flex">
                    <div class="course-list-item__left">
                        <img class="backuptaskimg" src="/extensions/autobackup/web/images/<?= $task['status'] ? 'task_on.svg' : 'task_off.svg'; ?>" alt="">
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
                            <div>
                                <?php if ($period['type'] == 2) { ?>
                                    <div class="backupTaskmethod">
                                        <i class="icon-time"></i> <?= BackupTables::convertDayWeek($period['day']) ?> <?= $period['time'] ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="backupTaskmethod">
                                        <i class="icon-time"></i>Каждый день в <?= $period['time'] ?>
                                    </div>
                                <?php } ?>
                            </div>
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
                        <a class="button-red" onclick="showStartBackupAlert('<?= $task['id'] ?>')"><img src="/extensions/autobackup/web/play.svg" width="14">&nbsp;Сделать бэкап</a>
                        <a class="text-decoration-none" href="/admin/autobackup/copys?task_id=<?= $task['id'] ?>">Посмотреть копии</a>
                    </div>
                </div>
            <?php endforeach;
        else: ?>
            <div class="course-list-item">
                <p>
                    На этой странице будет находиться список заданий для резервного копирования.
                </p>
                <p>Для начала:</p>
                <p>
                    1. Добавьте хранилище для резервного копирования. <a href="/admin/autobackup/storage/">Добавить</a><br>
                    2. Создайте первое задание для резервного копирования. <a href="/admin/autobackup/addtask">Добавить</a>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
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

<div class="hidden block_fullscreen" style="display: flex; justify-content: center; align-items: center" id="BackupStartAlert">
    <div class="inner_block_task_alert">
        <h1 style="font-size: 24px;">Создать бэкап</h1>
        <div>
            <p>Для создания бэкапа вручную откроется новая страница.</p>
            <p>Не закрывайте страницу с прогрессом до завершения резервного копирования.</p>
        </div>
        <div class="btnwrapper">
            <a class="button-red" id="backup_start_btn" style="font-size: 14px;">Сделать резервную копию</a>
        </div>
    </div>
</div>

<script>
    function showStartBackupAlert(id) {
        document.getElementById("BackupStartAlert").classList.remove("hidden");

        document.getElementById("backup_start_btn").onclick = () => {
            startBackup(id);
        }

        document.getElementById("BackupStartAlert").addEventListener("click", function () {
            document.getElementById("BackupStartAlert").classList.add("hidden");
        })
    }


    async function startBackup(id) {
        let loader = document.getElementById("loaderBackupTask");
        let result_text = document.getElementById("inner_block_task_result_text");

        loader.classList.remove("hidden");
        let response = await fetch("/admin/autobackup/run/" + id + "?token=<?= $_SESSION['admin_token'] ?>");
        let responseText = await response.text();
        loader.classList.add("hidden");

        let headers = await response.headers;

        let uid = "";
        if (headers.has("x-data-uid")) {
            uid = headers.get("x-data-uid")
        }

        if (uid) {
            document.location.href = "/admin/autobackup/progress/" + id + "?uid=" + uid;
        }
    }
</script>
<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">
</body>
</html>