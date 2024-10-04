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
        <li>Логи</li>
    </ul>

    <span id="notification_block"></span>

    <div class="filter admin_form">
        <form action="/admin/logs/" method="get">
            <div class="filter-row filter-flex-end">
                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="level">
                            <option value="null">Уровень критичности</option>
                            <?php foreach ($levels as $level) { ?>
                                <option
                                    value="<?= $level ?>"
                                    <?= isset($_GET['level']) &&  $_GET['level'] == "$level" ? "selected" : "" ?>
                                ><?= $level." - ". Log::logLevelToText($level) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">

                        <select name="type">
                            <option value="0">Тип</option>
                            <?php foreach ($types as $type) { ?>
                                <option
                                    value="<?= $type['type'] ?>"
                                    <?= isset($_GET['type']) && $_GET['type'] === $type['type'] ? "selected" : "" ?>
                                ><?= $type['type'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="in_arhive">
                            <option value="0">Статус</option>
                            <option value="1" <?= isset($_GET['in_arhive']) &&  $_GET['in_arhive'] == 1 ? "selected" : "" ?>>В архиве</option>
                        </select>
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
                <th title="Дата события. Текущее время на сервере: <?= date(Log::DateFormat); ?>">Дата</th>
                <th style="width: 100px;" class="text-left" title="Что произошло">Сообщение</th>
                <th class="text-right" title="Насколько критично событие">Уровень</th>
                <th title="Где случилось">Тип</th>
            </tr>
            <? foreach($logs as $log):?>
                <tr>
                    <td class="text-left">
                        <a href="/admin/logs/<?= $log['id'] ?>">
                            <?= date(Log::DateFormat, $log['date']); ?>
                        </a>
                    </td>
                    <td class="text-left" style="width:100px;">
                        <a href="/admin/logs/<?= $log['id'] ?>">
                            <?= $log['message']; ?>
                        </a>
                    </td>
                    <td class="text-right">
                        <?= Log::logLevelToText($log['level']) ?>
                    </td>
                    <td>
                        <?=$log['type'] ?>
                    </td>

                    <!--<td>
                        <a class="link-delete" onclick="return confirm('Вы уверены?')" title="Удалить"
                        href="/admin/deliverysettings/del/<?/*=$method['method_id'];*/?>?name=<?/*=$method['title']*/?>&token=<?/*=$_SESSION['admin_token'];*/?>">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </a>
                    </td>-->
                </tr>
            
            <? endforeach;?>
        </table>
        </div>
        <? } else echo 'Нет логов';?>
        <?php echo $pagination->get(); ?>

    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

</body>
</html>