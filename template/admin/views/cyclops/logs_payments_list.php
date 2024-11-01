<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Просмотр логов</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank"><?php echo System::Lang('GO_SITE');?></a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red"><?php echo System::Lang('QUIT');?></a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Логи payments</li>
    </ul>

    <span id="notification_block"></span>

    <div class="filter admin_form">
        <form action="/admin/cyclops/payments/" method="get">
            <div class="filter-row filter-flex-end">
                <div class="filter-1-3">
                    <div class="select-wrap">

                        <select name="identify">
                            <option value="0">Тип</option>
                            <?php foreach ($identifies as $identify) { ?>
                                <option
                                    value="<?= $identify? 'Идентифицирован':'Анонимный' ?>"
                                    <?= isset($_GET['identify']) && $_GET['identify'] === $identify ? "selected" : "" ?>
                                ><?= $identify? 'Идентифицирован':'Анонимный' ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <input name="amount" placeholder="Сумма" value="<?= isset($_GET['amount']) &&  $_GET['amount'] ?? $_GET['amount'] ?>">
                    </div>
                </div>
                <div class="filter-bottom">
                    <div>
                        <div class="order-filter-result"></div>
                    </div>

                    <div class="button-group">
                        <input class="button-blue-rounding" type="submit" name="filter" value="Найти">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="admin_form admin_form--margin-top">

        <? if($logs){ ?>
        <div class="overflow-container">
            <table class="table">
            <tr>
                <th title="ID платежа">ID</th>
                <th style="width: 100px;" class="text-left" title="Сумма">Сумма</th>
                <th class="text-right" title="Статус платежа">Статус</th>
            </tr>
            <? foreach($logs as $log):?>
                <tr>
                    <td class="text-left">
                        <a href="/admin/logs/<?= $log['id'] ?>">
                            <?= $log['id']; ?>
                        </a>
                    </td>
                    <td class="text-left" style="width:100px;">
                        <a href="/admin/logs/<?= $log['id'] ?>">
                            <?= $log['amount']; ?>
                        </a>
                    </td>
                    <td class="text-right">
                        <?= $log['identify'] ?>
                    </td>
                </tr>

            <? endforeach;?>
        </table>
        </div>
        <? } else echo 'Нет платежей';?>
        <?php echo $pagination->get(); ?>

    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

</body>
</html>