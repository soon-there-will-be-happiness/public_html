<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Список хранилищ</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/autobackup/">Резервное копирование</a></li>
        <li>Список хранилищ</li>
    </ul>
    
    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">

            <li class="nav_gorizontal__parent-wrap">
                <div class="nav_gorizontal__parent-red">
                    <a href="/admin/autobackup/storage/add/" class="nav-click button-red-rounding">Добавить хранилище</a>
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
        <?php if($buckets):
            foreach($buckets as $bucket):
                $bucketParam = json_decode($bucket['params'], true);
            ?>
                <div class="course-list-item d-flex storagewrapper" style="padding: 15px 30px; align-items: center;">
                    <div class="course-list-item__left">
                        <div class="storages">
                            <?= $bucket['type'] == 0 ? '<img src="/extensions/autobackup/web/images/ftp.svg" width="30">' : "" ?>
                            <?= $bucket['type'] == 1 ? '<img src="/extensions/autobackup/web/images/yandex.svg" width="30">' : "" ?>
                            <?= $bucket['type'] == 2 ? '<img src="/extensions/autobackup/web/images/dropbox.svg" width="30">' : "" ?>
                            <?= $bucket['type'] == 3 ? '<img src="/extensions/autobackup/web/images/s3.svg" width="30">' : "" ?>
                            <?= $bucket['type'] == 4 ? '<img src="/extensions/autobackup/web/images/local.svg" width="30">' : "" ?>
                            <?= $bucket['type'] == 5 ? '<img src="/extensions/autobackup/web/images/google-drive.svg" width="30">' : "" ?>
                        </div>
                    </div>
        
                    <div class="course-list-item__center">
                        <h4 class="course-list-item__name" style="padding: 0; margin-bottom: 0;">
                            <a href="/admin/autobackup/storage/edit/<?= $bucket['id'] ?>">
                                <?= $bucket['title'] ?>
                            </a>
                        </h4>
                    </div>
                    
                    <div class="extension-status">
                        <?php if ($bucket['status']) { ?>
                            <img src="/extensions/autobackup/web/images/success.svg" width="30">
                        <?php } else { ?>
                            <img src="/extensions/autobackup/web/images/notsuccess.svg" width="30">
                        <?php } ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="course-list-item d-flex">
                <a href="/admin/autobackup/storage/add/" class="text-decoration-none">Добавьте хранилище для резервных копий</a>
            </div>
        <?php endif;?>
    </div>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<link rel="stylesheet" type="text/css" href="/extensions/autobackup/web/autobackup.css">
</body>
</html>