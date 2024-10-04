<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Список договоров на рассрочку</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/installment/">Рассрочки</a>
        </li>
        <li>Договора рассрочки</li>
    </ul>

    <div class="nav_gorizontal"></div>

    <?php $today = date("j");
    $days = date("t");
    $now = time();
    $hour = date("G");
    $end = $now + (($days - $today) * 86400);
    
    $install_pays = Product::getSummFromInstallmentCurrMonth($now, $end);
    if($install_pays):
        $summ = 0;?>
        <div class="filter admin_form">
            <?php foreach ($install_pays as $pay){
                $installment = Product::getInstallmentData($pay['installment_id']);
                $pay_item = ($pay['summ'] / 100) * $installment['other_pay'];
                $summ = $summ + $pay_item;
            }?>

            <p class="width-100">Ещё в этом месяце планируется получить платежей на: <?=round($summ)?> <?=$setting['currency'];?></p>
        </div>
    <?php endif;?>
	
    <span id="notification_block"></span>

    <div class="filter admin_form">
        <form action="/admin/installment/map" method="GET">
            <div class="filter-row filter-flex-end">
                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="type">
                            <option value="">Тип рассрочки</option>
                            <?$installemts = product::getInstalments(1);
                            if($installemts):
                                foreach ($installemts as $installemt):?>
                                    <option value="<?=$installemt['id'];?>"<?php if($filter['type'] == $installemt['id']) echo ' selected="selected"';?>><?=$installemt['title'];?></option>
                                <?endforeach;
                            endif;?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <div class="select-wrap">
                        <select name="status">
                            <option value="">Статус</option>
                            <?$statuses = [0,1,2,9];
                            foreach ($statuses as $status):?>
                                <option value="<?=$status;?>"<?php if($filter['status'] === $status) echo ' selected="selected"';?>><?=Installment::getStatusText($status);?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                </div>

                <div class="filter-1-3">
                    <input type="text" name="email" value="<?=$filter['email'] ? $filter['email'] : '';?>" placeholder="E-mail">
                </div>

                <div class="filter-bottom">
                    <div>
                        <div class="order-filter-result">
                            <?php if($filter && $filter['is_filter']):?>
                                <div><p>Отфильтровано: <?=$total_items;?> объекта</p></div>
                            <?php endif;

                            if($instalment_map):?>
                                <input class="csv__link" type="submit" name="load_csv" value="Выгрузить в csv">
                            <?php endif;?>
                        </div>
                    </div>

                    <div class="button-group">
                        <?php if($filter['is_filter']):?>
                            <a class="red-link" href="/admin/installment/map?reset">Сбросить</a>
                        <?php endif;?>

                        <input class="button-blue-rounding" type="submit" name="filter" value="Найти">
                    </div>
                </div>
            </div>
        </form>

    </div>

    <div class="admin_form admin_form--margin-top">
        <div class="overflow-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th class="text-left">Просмотр</th>
                        <th class="text-left">Email</th>
                        <th class="text-left">Срок</th>
                        <th class="td-last">Сумма</th>
                        <th class="td-last">След. платёж</th>
                        <th class="td-last">Статус</th>
                        <th class="td-last">Del</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if($instalment_map):?>
                        <?php foreach($instalment_map as $item):?>
                            <tr>
                                <td><?=$item['id'];?><?php if(!empty($item['comment'])) echo '<span title="Есть комментарий">*</span> ';?></td>
                                <td><a href="/admin/installment/map/<?=$item['id'];?>">Просмотр</a></td>
                                <td class="text-left"><?=$item['email'];?></td>
                                <td class="text-left"><?=$item['max_periods'];?></td>
                                <td class=""><?=$item['summ'];?></td>
                                <td><?php if($item['next_pay'] > 0) echo date("d.m.Y", $item['next_pay']);?></td>
                                <td class=""><?=getStatus($item['status']);?></td>
                                <td><a class="link-delete" onclick="return confirm('Вы уверены?')" href="<?=$setting['script_url'];?>/admin/installment/delmap/<?=$item['id'];?>?token=<?=$_SESSION['admin_token'];?>" title="Удалить"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                            </tr>
                        <?php endforeach;?>
                    <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>
    <?=$pagination->get();
    require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
	
	<?php function getStatus($status)
    {
        if($status == 0) return 'Требуется подтверждение';
        if($status == 1) return 'Активна';
        if($status == 9) return '<span style="color:red">'.System::Lang('EXPIRED').'</span>';
        if($status == 2) return 'Завершена';
    }
    ?>
</div>
</body>
</html>