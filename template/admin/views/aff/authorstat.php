<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>История начислений и выплат</h1>
    <div class="logout">
        <a href="/" target="_blank"><?php echo System::Lang('GO_SITE');?></a><a href="/admin/logout" class="red"><?php echo System::Lang('QUIT');?></a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/aff/">Партнерка</a>
        </li>
        <li>История начислений и выплат</li>
    </ul>
    <div class="nav_gorizontal">
    </div>

    <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
<div class="admin_form admin_form--margin-top">

        <div class="row-line">
            <div class="col-1-1">
                <p><strong>ID: <?php echo $id;?><br /><?php echo $user['user_name'];?><br /><?php echo $user['email'];?></strong></p>
            </div>
        </div>

</div>
    <div class="admin_form admin_form--margin-top">
            <div class="row-line">
                <div class="col-1-1">
                    <h4>Начисления / Выплаты</h4>
                    <div class="overflow-container">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <th>ID заказа</th>
                            <th>Сумма, <?php echo $setting['currency'];?></th>
                            <th>Дата</th>
                        </tr>
                        <?php if($items):
         foreach($items as $item):?>
                        <tr>
                            <td><?php echo $item['id'];?></td>
                            <td>
                                <?php if($item['order_id'] != 0){?>
                                <a href="/admin/orders/edit/<?php echo $item['order_id'];?>" target="_blank"><?php echo $item['order_id'];?></a>
                                <?php } else echo 'Выплата'?>
                            </td>
                            
                            <?php if($item['order_id'] != 0){?>
                            <td><form action="" method="POST" id="reload_<?php echo $item['id'];?>">
                                <input type="text" style="width:70px; padding:4px; margin-right:10px" name="summ" value="<?php echo $item['summ'];?>">
                                <input type="image" src="/template/admin/images/reload.png" style="position: relative; top:6px;" title="Обновить" name="reload">
                                <input type="hidden" name="stat_id" value="<?php echo $item['id'];?>">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>"></form>
                            </td>
                            <?php } else {?>
                                <td><?=$item['pay'];?></td>
                            <?php }?>
                            <td><?php echo date("d-m-Y H:i:s", $item['date']);?></td>
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
                            <th>Выплачено, <?php echo $setting['currency'];?></th>
                            <th>Дата</th>
                        </tr>
                        <?php if($pays):
            foreach($pays as $pay):?>
                        <tr>
                            <td><?php echo $pay['id'];?></td>
                            <td><?php echo $pay['pay'];?></td>
                            <td><?php echo date("d-m-Y H:i:s", $pay['date']);?></td>
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