<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('MEMBER_SUBS');?></h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li>Планы подписок</li>
    </ul>

    <span id="notification_block"></span>
    
    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li class="nav_gorizontal__parent-wrap">
                <a class="button-red-rounding" href="/admin/membersubs/add/"><?=System::Lang('CREATE_PLANE');?></a>
            </li>
            <li>
                <a class="button-yellow-rounding" href="/admin/memberusers/">Список участников</a>
            </li>
            <li>
                <a title="Общие настройки тренингов" class="settings-link" target="_blank" href="/admin/membersetting">
                    <i class="icon-settings"></i>
                </a>
            </li>
        </ul>
    </div>

    <div class="admin_form admin_form--margin-top">
        <table class="table">
            <tr>
                <th>ID</th>
                <th class="text-left">Название</th>
                <th class="text-left">Период</th>
                <th>Act</th>
            </tr>
            
            <?php if($planes):
                foreach($planes as $plane):
                    $plane_name = (empty($plane['service_name'])) ? $plane['name'] : $plane['service_name']; ?>

                    <tr<?php if($plane['status'] == 0) echo ' class="off"';?>>
                        <td>
                            <?=$plane['id']; ?>
                        </td>
                        <td class="text-left">
                            <a href="/admin/membersubs/edit/<?=$plane['id']; ?>">
                                <?=$plane_name?>
                            </a>
                            <?php if($plane['recurrent_enable'] == 1) echo ' <sup title="Рекурренты"> R </sup> ';?>
                        </td>
                        <td class="text-left">
                            <?=$plane['lifetime']; ?>
                        </td>
                        <td>
                            <a class="link-delete" onclick="return confirm('Вы уверены?')" href="/admin/membersubs/delete/<?=$plane['id'];?>?token=<?=$_SESSION['admin_token'];?>&name=<?=$plane_name?>" title="Удалить">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach;
            else:?>
                <p>Вы пока не добавили план подписки</p>
            <?php endif;?>
        </table>
    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>