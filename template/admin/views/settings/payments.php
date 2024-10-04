<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Список платёжных модулей</h1>
        <div class="logout">
            <a href="<?= $setting['script_url'];?>" target="_blank">Перейти на сайт</a>
            <a href="<?= $setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Список платёжных модулей</li>
    </ul>

    <span id="notification_block"></span>

    <div class="admin_form">
        <form action="" method="POST" enctype="multipart/form-data">
            <ul>
                <li class="search-row">
                    <span class="search-row mr-auto">
                        Установить новый модуль:  
                        <input type="file" name="payment" value="Установить">
                    </span>
                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    <input type="submit" value="Установить" class="button save button-green-rounding" name="install_payment">
                </li>
            </ul>
        </form>
        <ul>
            <li class="search-row">
                <label class="check_label">
                    <input type="checkbox" data-show_off="payment_off">
                    <span>Показать только активные модули</span>
                </label>
            </li>
        </ul>
    </div>
    
    <? if($_SERVER['HTTP_HOST'] == 'shishonin-doc.ru'):?>
    <p>
        <a href="/admin/organizations">Организации</a>
    </p>
    <? endif;?>
    
    <? if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>

    <div class="extension">
        <? if(isset($payments)):
        foreach($payments as $payment):?>
        <div class="extension-item" data-id="payment_<?=$payment['status'] == 1 ? "on" : "off"?>">
            <div class="extension-img">
                <img src="/payments/<?=$payment['name'];?>/<?= $payment['name'];?>.png">
            </div>

            <div class="extension-center">
                <h4 class="mb-0 pb-0">
                    <a href="<?= $setting['script_url'];?>/admin/paysettings/<?= $payment['payment_id'];?>">
                        <?= $payment['title'];?>
                    </a>
                </h4>
            </div>

            <div class="extension-status">
                <? if($payment['status'] == 1) $status = 'on'; else $status = 'off';?>
                <a style="text-decoration: none;" class="ext-status <?= $status; ?>"
                   href="/admin/paysettings/changestatus/<?= $payment['payment_id']?>?token=<?=$_SESSION['admin_token']?>&status=<?=$payment['status'] ? "0": "1" ?>"></a>
            </div>
        </div>
        <? endforeach;
        endif;?>

        </div>
            <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
        </div>
    </div>
</body>
</html>