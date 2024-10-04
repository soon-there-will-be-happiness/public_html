<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Просмотр лога #<?= $log['id'] ?><?= $log['in_arhive'] ? " (в архиве)" : "" ?></h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/logs/">Логи</a></li>
        <li>Просмотр лога #<?= $log['id'] ?><?= $log['in_arhive'] ? " (в архиве)" : "" ?></li>
    </ul>

    <span id="notification_block"></span>
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1">
                    <h3>Просмотр лога #<?= $log['id'] ?><?= $log['in_arhive'] ? " (в архиве)" : "" ?></h3>
                </div>
                <div class="col-1-1">
                    <div class="margin-bottom-15">
                        <b>Дата события</b><br>
                        <?= date(Log::DateFormat, $log['date']) ?>
                    </div>
                    <div class="margin-bottom-15">
                        <b>Сообщение</b><br>
                        <?= $log['message'] ?>
                    </div>
                </div>

                    <div class="col-1-2">
                        <div class="margin-bottom-15">
                            <b>
                                Уровень критичности
                                <span class="result-item-icon" data-toggle="popover" data-original-title="" title=""
                                      data-content="Насколько критично событие. 0 - информационное, 7 - система не работает">
                                    <i class="icon-answer"></i>
                                </span>
                            </b><br>
                            <?= $log['level'] ." - ". Log::logLevelToText($log['level']) ?>
                        </div>
                        <div class="margin-bottom-15">
                            <b>
                                Тип
                                <span class="result-item-icon" data-toggle="popover" data-original-title="" title=""
                                      data-content="Место где произошло. Это может быть расширение, или же система в целиком - app">
                                    <i class="icon-answer"></i>
                                </span>
                            </b><br>
                            <?= $log['type'] ?>
                        </div>
                    </div>
                <div class="col-1-2">
                    <div class="margin-bottom-15">
                        <b>Действия: </b><br>
                        <a href="/admin/logs/changearhive/<?= $log['id'] ?>?to=<?= $log['in_arhive'] ? 0 : 1 ?>" class="text-decoration-none"><?= $log['in_arhive'] ? "Убрать из архива" : "Переместить в архив" ?></a><br>
                        <a href="/admin/logs/delete/<?= $log['id'] ?>" class="color-red text-decoration-none">Удалить</a>
                    </div>
                </div>

                <?php if ($log['context']) { ?>
                    <div class="col-1-1">
                        <h1>Контекст</h1>
                        <pre style="white-space: pre-wrap; word-wrap: break-word;"><?= json_encode($log['context'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></pre>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>