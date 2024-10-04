<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');?>

<div class="main">
    <div class="top-wrap">
        <h1>Просмотр договора рассрочки</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a>
            <a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/installment/map/">Список договоров рассрочки</a></li>
        <li>Просмотр договора</li>
    </ul>

    <span id="notification_block"></span>

    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/installment.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Договор рассрочки ID: <?=$id;?> </h3>
                </div>
            </div>

            <ul class="nav_button">
                <li><input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/installment/map/">Закрыть</a></li>
            </ul>
        </div>

        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 mb-0"><h4>Основное</h4></div>
                <div class="col-1-2">
                    <div class="gray-block-2">
                        <p class="width-100">
                            <strong>Рассрочка: </strong>
                            <a href="/admin/installment/edit/<?=$install_map_item['installment_id'];?>" target="_blank"><?php $installment = Product::getInstallmentData($install_map_item['installment_id']); echo $installment['title'];?></a>
                        </p>
                        <p class="width-100">
                            Кол-во платежей: <?=$install_map_item['max_periods'];?>
                        </p>
                        <p class="width-100">
                            Дата создания: <?=date("d.m.Y H:i:s", $install_map_item['create_date']);?>
                        </p>
                        <p class="width-100">
                            Заказ ID: <a href="/admin/orders/edit/<?=$install_map_item['order_id'];?>" target="_blank"><?=$install_map_item['order_id'];?></a>
                        </p>
                        <p class="width-100">
                            Начальная сумма рассрочки, <?php $start_summ = $install_map_item['start_summ'] != 0 ? $install_map_item['start_summ'] : $install_map_item['summ']; echo $start_summ;?> <?=$setting['currency'];?>
                        </p>

                    <?php $pay_actions = !empty($install_map_item['pay_actions']) ? unserialize(base64_decode($install_map_item['pay_actions'])) : null;
                    $total = $pay_actions ? array_sum(array_column($pay_actions, 'summ')) : 0;?>

                    <p class="width-100">Осталось оплатить: <?=$install_map_item['summ'] - $total;?> <?=$setting['currency'];?></p>

                    <?php if($install_map_item['status'] != 2):?>
                        <p class="width-100">Следующий платёж: <?php if($install_map_item['next_pay'] !=0) echo date("d.m.Y H:i:s", $install_map_item['next_pay']);?></p>
                    <?php endif;?>

                    <p class="width-100">Статус: <strong class="green-text"><?=getStatus($install_map_item['status']);?></strong></p>
                    
                    <?php if($install_map_item['status'] == 0 && $install_map_item['approved'] == 0){
                        $link = $setting['script_url'].'/installment/vote?key='.md5($setting['secret_key']).'&order='.$install_map_item['order_id'].'&map_id='.$install_map_item['id'].'&install_id='.$install_map_item['installment_id'];?>
                    <a href="<?=$link.'&answer=1';?>">Одобрить</a> |
                    <a style="color:#E04265" href="<?=$link.'&answer=0';?>">Отклонить</a> 
                    <?php } else {?>

                    <div class="width-100 mb-0"><label>Изменить статус</label>
                        <div class="select-wrap">
                            <select name="status">
                                <option value="<?=$install_map_item['status'];?>"><?=getStatus($install_map_item['status']);?></option>
                                <?php if($install_map_item['status'] != 0):?>
                                    <option value="0">Остановлена</option>
                                <?php endif;

                                if($install_map_item['status'] != 1):?>
                                    <option value="1">Активна</option>
                                <?php endif;

                                if($install_map_item['status'] != 2):?>
                                    <option value="2">Полностью оплачена</option>
                                <?php endif;?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    </div>
                </div>

                <div class="col-1-2">

                    <?php $user = User::getUserDataByEmail($install_map_item['email']);?>
                    <p class="width-100"><label><a href="/admin/users/edit/<?=$user['user_id'];?>" target="_blank">Клиент:</a></label>
                        <input type="text" name="change_email" value="<?=$install_map_item['email'];?>">
                    </p>

                    <p class="width-100"><label>Скорректировать сумму рассрочки, <?=$setting['currency'];?>:</label>
                        <input type="text" name="summ" value="<?=$install_map_item['summ'];?>">
                    </p>

                    <?php if($install_map_item['status'] != 2):?>
                        <div class="width-100 min-label-wrap">
                            <label>Заморозить договор на XX дней:<span class="min-label">дней</span></label>
                            <input type="text" size="3" name="freeze">
                            <input type="hidden" name="next_pay" value="<?=$install_map_item['next_pay'];?>">
                        </div>
                    <?php endif;?>

                    <p class="width-100"><label>Примечание: </label>
                        <textarea name="comment"><?=$install_map_item['comment'];?></textarea>
                    </p>

                    <?php if($install_map_item['ahead_id'] != 0):?>
                        <p>Ожидается досрочное погашение - <a target="_blank" href="/admin/orders/edit/<?=$install_map_item['ahead_id'];?>"><?=$install_map_item['ahead_id'];?></a>.
                        <br /><a href="/admin/installment/delahead/<?=$id;?>/<?=$install_map_item['ahead_id'];?>?token=<?=$_SESSION['admin_token'];?>" onclick="return confirm('Вы уверены?')">Отменить досрочное погашение</a></p>
                    <?php endif;?>

                    <?php if($install_map_item['next_order'] != 0):?>
                        <p>Ожидается платёж по рассрочке: <a target="_blank" href="/pay/<?=$install_map_item['next_order'];?>"><?=$install_map_item['next_order'];?></a>
                        <br /><a href="/admin/installment/delnextorder/<?=$id;?>/<?=$install_map_item['next_order'];?>?token=<?=$_SESSION['admin_token'];?>" onclick="return confirm('Вы уверены?')">Отменить заказ</a></p>
                    <?php endif;?>
                </div>


                <div class="col-1-1 mt-10">
                    <h4 class="mb-30">Платежи</h4>
                    <?php if(!empty($install_map_item['pay_actions'])):?>
                        <div class="table-responsive">
                            <table>
                                    <tr>
                                        <th class="text-left">Номер</th>
                                        <!--th class="text-left">ID заказа</th-->
                                        <th class="text-left">Дата</th>
                                        <th class="text-left">Сумма</th>
                                        <th>Act</th>
                                    </tr>

                                    <?php foreach($pay_actions as $num_pay => $action):?>
                                        <tr class="td-not-border">
                                            <td><?=$num_pay;?></td>
                                            <!--td><a class="text-decoration-none" href="#">123199911122</a></td-->
                                            <td><?=date("d-m-Y H:i", $action['date'])?></td>
                                            <td><?=$action['summ']?> <?=$setting['currency'];?></td>
                                            <td class="text-center"><a class="link-delete" onclick="return confirm('Вы уверены?')" href="/admin/installment/map/del/<?="$id/$num_pay";?>?token=<?=$_SESSION['admin_token'];?>" title="Удалить"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                                        </tr>
                                    <?php endforeach;?>
                                </table>
                        </div>
                    <?php endif;?>
                </div>

                <?php if($install_map_item['status'] != 2):?>
                    <div class="col-1-1 mt-10">
                        <div class="width-100">
                            <label>Добавить платёж</label>
                            <form action="" method="POST">
                                <div class="form-row">
                                    <div class="form-row-name">
                                        <div class="select-wrap">
                                            <select name="new_pay_type">
                                                <option value="0">В один из платежей</option>
                                                <option value="1">Создать новый платёж</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row-name">
                                        <input type="text" name="sum" placeholder="Сумма">
                                        <input type="hidden" name="pay_action" value="<?=$install_map_item['pay_actions'];?>">
                                        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                                    </div>

                                    <div class="form-row-submit">
                                        <input type="submit" name="new_pay_add" class="button save button-green-rounding button-lesson" value="Добавить">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </form>
    <div class="buttons-under-form">
        <div class="reference-button">
            <a href="https://lk.school-master.ru/rdr/49" target="_blank"><i class="icon-answer-2"></i>Справка</a>
        </div>
    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>

<?php function getStatus($status) {
    if($status == 0) {
        return 'На рассмотрении / В ожидании';
    } elseif($status == 1) {
        return '<span class="color-gray">Идут платежи</span>';
    } elseif($status == 2) {
        return 'Полностью оплачена';
    } elseif($status == 9) {
        return '<span style="color:red">Просрочен платёж</span>';
    }
}?>