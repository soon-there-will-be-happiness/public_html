<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>История начислений и выплат</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a><a href="/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/aff/">Партнёрка</a></li>
        <li>История начислений</li>
    </ul>

    <div class="nav_gorizontal"></div>

    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?endif?>

    <div class="admin_form admin_form--margin-top">
        <div class="row-line">
            <div class="col-1-1">
                <p><strong>ID: <?=$id;?><br /><?=$user['user_name'];?><br /><?=$user['email'];?></strong></p>
            </div>
        </div>
    </div>

    <div class="admin_form admin_form--margin-top">
        <div class="row-line">
            <div class="col-1-1">
                <h4>Начисления</h4>
                <div class="overflow-container">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>ID заказа<br>Номер заказа</th>
                            <th>Email клиента</th>
                            <th>Сумма, <?=$setting['currency'];?></th>
                            <th>Дата</th>
                        </tr>

                        <?php if($items):
                            foreach($items as $item):?>
                                <tr>
                                    <td><?=$item['id'];?></td>

                                    <td>
                                        <?php if($item['order_id'] != 0){?>
                                            <a href="/admin/orders/edit/<?=$item['order_id'];?>" target="_blank"><?=$item['order_id'];?></a><br>
                                        <?php } else echo 'Выплата';?>
                                    </td>

                                    <td><?if($item['client_email']) {
                                            echo $item['client_email'];
                                        } elseif($item['order_id']) {
                                            $order = Order::getOrder($item['order_id']);
                                            echo $order ? $order['client_email'] : '';
                                        }?>
                                    </td>

                                    <td>
                                        <?php if($item['order_id'] != 0):?>
                                            <form action="" method="POST" id="reload_<?=$item['id'];?>">
                                                <input type="text" style="width:70px; padding:4px; margin-right:10px" name="summ" value="<?=$item['summ'];?>">
                                                <input type="image" src="/template/admin/images/reload.png" style="position: relative; top:6px;" title="Обновить" name="reload">
                                                <input type="hidden" name="stat_id" value="<?=$item['id'];?>">
                                                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                                            </form>
                                        <?php else:
                                            echo $item['pay'];
                                        endif;?>
                                    </td>

                                    <td><?=date("d-m-Y H:i:s", $item['date']);?></td>
                                </tr>
                            <?php endforeach;
                        endif;?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="admin_form admin_form--margin-top">
        <div class="row-line">
            <div class="col-1-1">
                <h4>Выплаты отдельно</h4>
                <div class="overflow-container">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>Выплачено, <?=$setting['currency'];?></th>
                            <th>Дата</th>
                        </tr>
                        <?php if($pays):
                            foreach($pays as $pay):?>
                                <tr>
                                    <td><?=$pay['id'];?></td>
                                    <td><?=$pay['pay'];?></td>
                                    <td><?=date("d-m-Y H:i:s", $pay['date']);?></td>
                                </tr>
                            <?php endforeach;
                        endif;?>
                    </table>
                </div>
            </div>
        </div>
    </div>
        <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>