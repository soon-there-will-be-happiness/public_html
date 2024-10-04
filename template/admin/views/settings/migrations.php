<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Проверка актуальности базы данных</h1>
        <div class="logout">
            <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/services/">Обслуживание</a>
        </li>
        <li>Проверка БД</li>
    </ul>

    <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>

    <div class="admin_form admin_form--margin-top">
        <div class="row-line">
            <div class="col-1-2">
                <h4>Проверка базы данных</h4>

                <div class="migrations-info margin-bottom-15">
                    <?= $dbCheckResult['message'] ?>
                </div>

                <?php if ($dbCheckResult['status'] == "needRunMigrations") { ?>
                    <form action="" method="POST">
                        <p><input type="submit" class="button button-green-border-rounding" name="runMigrations" value="Запустить миграции">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>"></p>
                        <p class="small">* Перед выполнением миграций следует сделать <a href="/admin/backup/">бэкап базы данных</a></p>
                    </form>
                <?php } ?>

            </div>
            <div class="col-1-2">
                <h4>Информация</h4>
                <div class="margin-bottom-15">Статус: <b><?= \Migrations\migrationHandler::checkDbConvertStatus($dbCheckResult['status']) ?></b></div>
                <div class="margin-bottom-15" title="Версия проекта исходя из миграций: <?= $dbCheckResult['projectVersion'] ?>">Версия SM: <?= CURR_VER ?></div>
                <div class="margin-bottom-15">Версия БД: <?= $dbCheckResult['dbVersion'] ?></div>
                <div class="margin-bottom-15">Количество выполненных миграций: <?= $completedMigrationsCount ?></div>
            </div>
            <?php if (isset($migrationResult) && $migrationResult) { ?>

                <div class="col-1-1">

                    <h4>Результаты выполненных миграций</h4>
                    <div class="margin-bottom-15">Успешно выполнено: <?= $migrationResult['success'] ?></div>
                    <div class="margin-bottom-15">Выполнено с ошибками: <?= $migrationResult['notSuccess'] + $migrationResult['errors']  ?> </div>
                    <div class="margin-bottom-15">Было установлено раньше: <?= $migrationResult['before'] ?></div>

                    <?php if (isset($migrationResult['executedMigrations']) && !empty($migrationResult['executedMigrations'])) { ?>

                        <h5 id="migrations-btn"><a><b>Лог выполненной миграции</b></a></h5>
                        <div id="migrations-log" class="hidden">

                            <?php foreach ($migrationResult['executedMigrations'] as $key => $migration) { ?>
                                <div class="migration-log" style="margin-bottom: 8px;">

                                    <div class="<?= $migration['status'] ? 'green-text' : 'red-link' ?>">
                                        <b><?= $migration['version'] ?>/<?= $key ?></b>
                                        <span class="">(<?= $migration['time'] ?>s)</span>
                                    </div>

                                    <div style="padding-left: 8px;">
                                        <div>Статус: <?= $migration['status'] ? 'Успешно' : 'Неудачно' ?></div>
                                        <?php if ($migration['exceptionMess']) { ?>
                                            <span class="red-link">Ошибка:
                                                <span style="font-family: monospace"><?= $migration['exceptionMess'] ?></span>
                                            </span>
                                        <?php } ?>
                                    </div>

                                </div>
                            <?php } ?>

                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<script>
    let migrateBtn = document.getElementById("migrations-btn");
    let logBlock = document.getElementById("migrations-log");
    migrateBtn.addEventListener("click", function () {
       logBlock.classList.toggle("hidden");
    });
</script>
</body>
</html>