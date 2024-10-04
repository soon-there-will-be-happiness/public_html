<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Обслуживание системы</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/settings/">Настройки</a></li>
        <li>Обслуживание</li>
    </ul>
    
    <?if(System::hasSuccess()) System::showSuccess();?>
    <?if(System::hasError()) System::showError();?>

    <div class="extension">
        <div class="extension-item">
            <div class="extension-img"><img src="/template/admin/images/ext/cheker.svg"></div>
            <div class="extension-center">
                <h4><a href="<?=$setting['script_url'];?>/admin/checksettings"><?=System::Lang('CHECK_SYSTEM');?></a></h4>
                <span>Ручная проверка состояния системы</span>
            </div>
            <div class="extension-status">
            </div>
           
        </div>
    </div>
    
    <div class="extension">
        <div class="extension-item">
            <div class="extension-img"><img src="/template/admin/images/ext/connect.svg"></div>
            <div class="extension-center">
                <h4><a href="<?=$setting['script_url'];?>/admin/settings/migrations"><?=System::Lang('FIX_BD');?></a></h4>
                <span>Проверка актуальности базы данных</span>
            </div>
            <div class="extension-status">
            </div>
           
        </div>
    </div>
    
    <div class="extension">
        <div class="extension-item">
            <div class="extension-img"><img src="/template/admin/images/ext/backup.svg"></div>
            <div class="extension-center">
                <h4><a href="<?=$setting['script_url'];?>/admin/backup"><?=System::Lang('BACKUP');?></a></h4>
                <span>Резервное копирование базы данных</span>
            </div>
            <div class="extension-status">
            </div>
        </div>
    </div>

    <?php if (System::CheckExtensension('autobackup')) { ?>
        <div class="extension">
            <div class="extension-item">
                <div class="extension-img"><img src="/template/admin/images/ext/autobackup.svg"></div>
                <div class="extension-center">
                    <h4><a href="<?=$setting['script_url'];?>/admin/autobackup">AutoBackup</a></h4>
                    <span>Автоматическое резервное копирование файлов сайта и базы данных</span>
                </div>
                <div class="extension-status">
                </div>
            </div>
        </div>
    <?php } ?>
    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>