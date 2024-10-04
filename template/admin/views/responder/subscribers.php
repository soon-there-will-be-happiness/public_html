<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('EMAIL_SUBSCRIBERS');?></h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>Подписчики рассылок</li>
    </ul>

    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li class="nav_gorizontal__parent-wrap">
                <a class="button-red-rounding" href="/admin/subscribers/add/"><?=System::Lang('ADD_SUBS');?></a>
            </li>
            <li><a class="button-yellow-rounding" href="/admin/subscribers/import/"><?=System::Lang('IMPORT_SUBS');?></a></li>
        </ul>
    </div>

    <div class="filter admin_form">
        <form action="" method="get">
            <div class="search-row">
                <div>
                    <div class="select-wrap">
                        <select name="delivery">
                            <option value="0">По подписке</option>
                            <?$delivery_list = Responder::getDeliveryList(2);
                            foreach($delivery_list as $delivery):
                                $selected = $filter['delivery'] == $delivery['delivery_id'] ? ' selected="selected"' : '';?>
                                <option value="<?=$delivery['delivery_id'];?>"<?=$selected;?>><?=$delivery['name'];?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                </div>

                <div class="mr-auto">
                    <input type="text" name="email" placeholder="По e-mail" value="<?=$filter['email'];?>">
                </div>

                <div>
                    <div class="button-group">
                        <input class="button-blue-rounding" type="submit" name="filter" value="Фильтр">
                        <a class="red-link" href="/admin/subscribers">Сбросить</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?endif;
    if(isset($_GET['fail'])):?>
        <div class="admin_warning">Не возможно удалить!</div>
    <?endif;?>

    <div class="admin_form admin_form--margin-top">
        <p>Всего записей: <?=$total;?></p>
        <div class="overflow-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class="text-left">Имя</th>
                        <th class="text-left">Email</th>
                        <th class="text-left">Рассылка</th>
                        <th class="td-last"></th>
                    </tr>
                </thead>

                <tbody>
                    <?php if($subs_list):
                        foreach($subs_list as $subs):
                            $user = User::getUserDataByEmail($subs['email'], null);?>
                            <tr<?php if($subs['cancelled'] != 0 || $subs['confirmed'] == 0) echo ' class="off"';
                                if($subs['spam'] != 0) echo ' class="refund"';?>>
                                <td><?=$subs['id'];?></td>
                                <td class="text-left"><?=$user ? $user['user_name'] : $subs['subs_name'];?></td>
                                <td class="text-left"><?=$subs['email'];?></td>
                                <td class="text-left"><?$deliver = Responder::getDeliveryData($subs['delivery_id']); echo $deliver['name'];?></td>
                                <td class="td-last"><a class="link-delete" onclick="return confirm('Вы уверены?')" href="/admin/subscribers/del/<?=$subs['id'];?>?token=<?=$_SESSION['admin_token'];?>" title="Удалить"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                            </tr>
                        <?endforeach;
                    else:
                        echo 'Нет подписчиков';
                    endif;?>
                </tbody>
            </table>
        </div>
    </div>


    <?=$pagination->get();?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>