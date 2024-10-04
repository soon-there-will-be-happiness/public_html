<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Проверка настроек системы</h1>
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
        <li>Проверка настроек</li>
    </ul>

    <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>

    <div class="admin_form admin_form--margin-top">
        <div class="row-line">
            <div class="col-1-1">
                <h4>Проверка настроек системы</h4>

                <div class="settingsChecker-wrapper">

                    <?php foreach ($result as $item) {?>
                        <div class="settings-item">
                            <div class="setting-title">
                                <?= $item['name'] ?>
                            </div>
                            <div class="setting-status">
                                <?php if ($item['status']) { ?>
                                    <div class="icon-ok"></div>
                                <?php } else { ?>
                                    <div class="icon-notOk"></div>
                                <?php } ?>
                            </div>
                            <div class="setting-message">
                                <?= $item['message'] ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>


            </div>
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