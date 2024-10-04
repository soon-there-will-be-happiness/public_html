<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Статусы для менеджеров</h1>
        <div class="logout">
            <a href="<?php echo $setting['script_url'];?>" target="_blank"><?php echo System::Lang('GO_SITE');?></a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red"><?php echo System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/orders">Заказы</a>
        </li>
        <li>Статусы для менеджеров</li>
    </ul>

    <div class="notification-end-plan mb-50" style="background-color: #fff;">
        <div class="notification-end-plan__icon"><i class="icon-info"></i></div>
        <div class="notification-end-plan__text">
            <p>На платформе School-Master имеются 2 типа статусов.</p>
            <p>- Статус работы с заказом, устанавливается менеджером в процессе работы. Можно создавать разные статусы.</p>
            <p>- Статус оплаты заказа. Меняется автоматически или менеджером. Добавлять новые нельзя.</p>
        </div>
    </div>

    <div class="status-title-wrap">
        <h4 class="status-title">Статусы работы с заказом</h4>
        <div>
            <div class="nav_gorizontal mb-0">
                <ul class="nav_gorizontal__ul flex-right">
                    <li class="nav_gorizontal__parent-wrap"><a class="button-red-rounding" href="/admin/settings/crmstatus/add/">Добавить статус работы с заказом</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    
    <!--div class="filter">
    </div-->
    
    <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
    
    <div class="admin_form admin_form--margin-top">
        
        <div class="overflow-container">
            <?php if($statuses){?>
            <table class="table table-status">
                <thead>
                    <tr>
                        <th class="text-left">Название</th>
                        <th class="text-left">Описание</th>
                        <th class="table-status-td-last"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($statuses as $status):?>
                    <tr>
                        <td class="text-left"><a href="/admin/settings/crmstatus/edit/<?=$status['id'];?>"><?php echo $status['title'];?></a></td>
                        <td class="text-left"><?php echo $status['status_desc'];?></td>
                        <td class="table-status-td-last"><a class="link-delete" onclick="return confirm('<?php echo System::Lang('YOU_SHURE');?>?')" href="<?php echo $setting['script_url'];?>/admin/settings/crmstatus/del/<?php echo $status['id'];?>?token=<?php echo $_SESSION['admin_token'];?>" title="<?php echo System::Lang('DELETE');?>"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                    </tr>
                    <?php endforeach;
                    } else echo '<p style="margin:2em 0">Статусы ещё не созданы</p>';?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="status-title-wrap mt-50">
        <h4 class="status-title">Статусы оплаты заказа</h4>
    </div>
    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <table class="table table-status">
                <thead>
                    <tr>
                        <th class="text-left">Название</th>
                        <th class="text-left">Описание</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left">Новый</td>
                        <td class="text-left">Не оплаченный заказ</td>
                    </tr>
                    <tr>
                        <td class="text-left">Проверить приход</td>
                        <td class="text-left">Клиент вырал ручной перевод на карту или расчётный счёт. Задача проверить поступление и подтвердить заказ.</td>
                    </tr>
                    <tr>
                        <td class="text-left">Подтверждён клиентом</td>
                        <td class="text-left">Подтвердил по Email доставку физического товара</td>
                    </tr>
                    <tr>
                        <td class="text-left">Оплачен</td>
                        <td class="text-left">Заказ оплачен. Выставлены доступы, произведены начисления партнёрам</td>
                    </tr>
                    <tr>
                        <td class="text-left">Ожидаем возврат клиенту</td>
                        <td class="text-left">Помечаются заказы ожидающие возврата клиенту. Задача выполнить возврат оплаты и выставить статус "Возврат" у продукта в заказе. После выставления статуса снимаются доступы и корректируются начисления.</td>
                    </tr>
                    <tr>
                        <td class="text-left">Возврат</td>
                        <td class="text-left">Выполнен возврат, полный или частичный. Все доступы снимаются, начисления корректируются.</td>
                    </tr>
                    <tr>
                        <td class="text-left">Ложный</td>
                        <td class="text-left">Тестовый заказ, не участвует в статистике</td>
                    </tr>
                    <tr>
                        <td class="text-left">Отменён</td>
                        <td class="text-left">Заказ не будут оплачивать. Участвует в статистике. Сохраняется для истории.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php if(isset($is_pagination) && $is_pagination == true) echo $pagination->get(); ?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>