<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1><?php echo System::Lang('DELIVERY_VARIANTS');?></h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank"><?php echo System::Lang('GO_SITE');?></a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red"><?php echo System::Lang('QUIT');?></a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Варианты доставки</li>
    </ul>

    <span id="notification_block"></span>

    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li><a class="button-yellow-rounding" href="/admin/deliverysettings/add/">Создать новый способ</a></li>
        </ul>
    </div>
    
    <div class="admin_form admin_form--margin-top">

        <? if($velivery_methods){ ?>
        <table class="table">
            <tr>
                <th>ID</th>
                <th class="text-left">Название</th>
                <th></th>
                <th>Act</th>
            </tr>
            <? foreach($velivery_methods as $method):?>
                <tr<?php if($method['status'] != 1) echo ' class="off"';?>>
                    <td>
                        <?=$method['method_id']; ?>
                    </td>
                    <td class="text-left">
                        <a href="/admin/deliverysettings/edit/<?=$method['method_id']; ?>">
                            <?=$method['title']?>
                        </a>
                    </td>
                    <td class="text-left extension-status">
                        <div class="ext-status <?php if($method['status'] == 1) echo 'on'; else echo 'off';?>"></div>
                    </td>
                    <td>
                        <a class="link-delete" onclick="return confirm('Вы уверены?')" title="Удалить"
                        href="/admin/deliverysettings/del/<?=$method['method_id'];?>?name=<?=$method['title']?>&token=<?=$_SESSION['admin_token'];?>">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
            
            <? endforeach;?>
        </table>
        <? } else echo 'Нет способов доставки';?>

    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

</body>
</html>