<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Журнал событий</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/conditions/"><?=System::Lang('CONDITIONS');?></a></li>
        <li><a href="/admin/conditions/log/">Журнал событий</a></li>
        <li>Событие</li>
    </ul>

    <span id="notification_block"></span>

    <div class="admin_form">
        <div class="row-line">
            <div class="col-1-1 mb-0">
                <h4>Основное</h4>
            </div>

            <div class="col-1-1">
                <div class="gray-block-info">
                    <div class="width-100">
                        <div><strong>Email: </strong><?=$event['user_email'];?></div>
                        <div><span>Дата выполнения: </span><?=date('d.m.Y H:i:s', $event['date']);?></div>
                        <div><span>Статус: </span><?=Conditions::getEventStatuses($event['status'], $event['act_status']);?></div>
                        <div><span>Событие: </span><?=Conditions::getEvents($event['action'], $event['act_params']);?></div>
                        <?if($event['act_params']):?>
                            <div><span>Значения: </span>
                                <?foreach ($event['act_params']['actions'] as $key => $value):?>
                                    <?=$key > 0 ? ', ' : '';?><a href="<?=Conditions::getEventUrl($event['action'], $value);?>" target="_blank"><?=$value;?></a>
                                <?endforeach;?>
                            </div>
                        <?endif;?>
                    </div>
                </div>
                <input type="hidden" name="token" value="a8cf2a57f445a37a4c3ba9758d718f75">
            </div>
        </div>
    </div>

    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
  jQuery('.datetimepicker').datetimepicker({
    format:'d.m.Y H:i',
    lang:'ru'
  });
</script>
</body>