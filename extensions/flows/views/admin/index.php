<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Список потков</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/products/">Продукты</a>
        </li>
        <li>Потоки</li>
    </ul>
    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
        <li><a class="button-red-rounding" href="<?php echo $setting['script_url'];?>/admin/flows/add/">Создать поток</a></li>
        <li><a title="Общие настройки потоков" class="settings-link" target="_blank" href="/admin/flowsetting/"><i class="icon-settings-bold"></i></a></li>
        </ul>
    </div>

    <span id="notification_block"></span>
    <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
    <div class="admin_form">
        <div class="overflow-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="text-left">Название</th>
                        <th class="text-left">Продукты</th>
                        <th class="text-left">Начало / завершение</th>
                        <th class="text-left">Мест</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($flow_list){
                    foreach($flow_list as $flow):?>
                    <tr>
                        <td class="text-left"><a href="<?=$setting['script_url'];?>/admin/flows/edit/<?=$flow['flow_id'];?>"><?=$flow['flow_name'];?></a></td>
                        <td class="text-left"><?php $products = Flows::getProductsInFlow($flow['flow_id']);
                        if($products){
                        foreach($products as $product){
                        $prod_name = Product::getProductName($product);
                        echo $prod_name['product_name'].'<br />';
                        }
                        }?></td>
                        <td class="text-left"><?=date("d.m.Y", $flow['start_flow']);?> - <?=date("d.m.Y", $flow['end_flow']);?></td>
                        <td><?=Flows::countOrdersFromFlowID($flow['flow_id']);?> / <?php if($flow['limit_users']> 0) echo $flow['limit_users']; else echo '∞';?></td>
                    </tr>
                    <?php endforeach;
                    };?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>