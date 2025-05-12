<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('EDIT_ORDER');?> <?=$order['order_date'];?> | ID: <?=$order['order_id'];?></h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a><a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/orders/">Заказы</a></li>
        <li>Редактировать заказ</li>
    </ul>
    
    <span id="notification_block"></span>
    
    <form action="" method="POST" id="order" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/zakaz.svg" alt="">
                </div>
                
                <div>
                    <h3 class="traning-title mb-0">Заказ
                        <a title="Ссылка на заказ" target="_blank" href="/pay/<?=$order['order_date'];?>">
                            <?=$order['order_date'];?>
                        </a>
                    </h3>
                    <p class="mt-0">Создан: <?=date("d.m.Y H:i:s", $order['order_date']);?> 
                </div>
            </div>
            
            <ul class="nav_button">
                <li><input type="submit" name="save" value="<?=System::Lang('SAVE');?>" class="button save button-white font-bold"></li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/orders/">Закрыть</a>
                </li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-2">
                    
                    <?php $link = User::getUserIDatEmail($order['client_email']);?>
                    <p class="width-100"><label>
                        <?php if($link){?>
                        <a target="_blank" href="/admin/users/edit/<?=$link;?>">
                            <?=System::Lang('CLIENT_NAME');?>:
                        </a>
                        <?php } else {?>
                            <?=System::Lang('CLIENT_NAME');?>:
                        <?php } ?>
                        </label>
                        <input type="text" name="name" value="<?=$order['client_name'];?>">
                    </p>
                    
                    <?php $order_info = unserialize(base64_decode($order['order_info']));
                    if(isset($order_info['surname'])):?>
                    <p class="width-100"><label>Фамилия:</label>
                        <input type="text" name="surname" value="<?=$order_info['surname'];?>">
                    </p>
                    <?php endif;?>
                    
                    <p class="width-100"><label>Email: </label>
                        <input type="text" name="client_email" value="<?=$order['client_email'];?>">
                    </p>
                    <p class="width-100"><label><?=System::Lang('CLIENT_PHONE');?>:</label>
                        <input type="text" name="phone" value="<?=$order['client_phone'];?>">
                    </p>
                    <p class="width-100"><label><?=System::Lang('CITY');?>:</label>
                        <input type="text" name="city" value="<?=$order['client_city'];?>">
                    </p>
                    <p class="width-100"><label><?=System::Lang('POSTCODE');?>:</label>
                        <input type="text" name="index" value="<?=$order['client_index'];?>">
                    </p>
                    <p class="width-100"><label><?=System::Lang('ADDRESS');?>:</label>
                        <textarea cols="40" rows="2" name="address"><?=$order['client_address'];?></textarea>
                    </p>
    
                    <div><label><?=System::Lang('COMMENT_CLIENT');?>:</label>
                        <textarea cols="40" rows="2" name="client_comment"><?=$order['client_comment'];?></textarea>
                    </div>
                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                </div>
    
                <div class="col-1-2">
                    <div class="round-block mb-20">

                        <div class="order-data-table">
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    <strong>Дата заказа:</strong>
                                </div>
                                <div class="order-data-table__right">
                                    <?=date("d.m.Y H:i:s", $order['order_date']);?>
                                </div>
                            </div>

                            <?php if($order['payment_date'] != null):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Дата оплаты:
                                </div>
                                <div class="order-data-table__right">
                                    <?=date("d.m.Y H:i:s", $order['payment_date']);?>
                                </div>
                            </div>
                            <?php endif;?>

                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Изменён:
                                </div>
                                <div class="order-data-table__right">
                                    <?php if($order['last_update'] == 0) echo 'Никогда'; else echo date("d.m.Y H:i:s", $order['last_update']);?>
                                </div>
                            </div>
                            
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Заказ создан:
                                </div>
                                <div class="order-data-table__right">
                                    <?=CreatedBy($order['create_from']);?>
                                </div>
                            </div>

                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    IP:
                                </div>
                                <div class="order-data-table__right">
                                    <?=$order['ip'];?>
                                </div>
                            </div>

                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Система оплаты:
                                </div>
                                <div class="order-data-table__right">
                                    <?php 
                                    if($order['payment_id'] != null) {
                                        $payment_data = Order::getPaymentDataForAdmin($order['payment_id']);
                                        echo $payment_data['title'];
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <?php $log_list = Stat::getPayLogGET($order['order_date']);
                            if($log_list){
                                $count_log = count($log_list);
                                if($count_log > 0):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Логи платежа:
                                </div>
                                <div class="order-data-table__right">
                                    <a href="/admin/paylog?order_date=<?=$order['order_date'];?>" target="_blank"><?=$count_log;?></a>
                                </div>
                            </div>
                            <?php endif; }?>

                            <?php if(isset($fin_potok) && $fin_potok != null):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Юр. лицо:
                                </div>
                                <div class="order-data-table__right">
                                    <?=$fin_potok['org_name'];?>
                                </div>
                            </div>
                            <?php endif;?>

                            <?php // Партнёры
                            if($order['partner_id'] != null):
                            $partner_list = Aff::getTransactionByOrder($order['order_id'], 'aff');
                            $i = 1;
                            if($partner_list){
                                foreach($partner_list as $partner):?>
                            
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    <?=System::Lang('PARTNER');?> <?=$i;?>:
                                </div>
                                <div class="order-data-table__right">
                                    <div class="del-partner-row">
                                        <div class="del-partner-text"><?=$partner['summ'];?> <?=$setting['currency'];?> (<a target="_blank" href="/admin/users/edit/<?=$partner['user_id'];?>"><?php $partner_name = User::getUserNameByID($partner['user_id']); echo $partner_name['user_name'];?></a>)</div>
                                        <div id="del_partner" class="del_partner">
                                            <a onclick="deletePartner();"><i class="icon-remove"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $i++;
                            endforeach;
                            } else {?>
                            <?php if (isset($order['partner_id']) && $order['partner_id'] != 0) { ?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    <?=System::Lang('PARTNER');?>:
                                </div>
                                <div class="order-data-table__right">
                                    <div class="del-partner-row">
                                        <div class="del-partner-text"> <a target="_blank" href="/admin/users/edit/<?=$order['partner_id'] ?? ''?>"><?php $order['partner_id'] ? $partner_name = User::getUserNameByID($order['partner_id']) : $partner_name['user_name'] = ''; echo $partner_name['user_name'];?></a></div>
                                        <div id="del_partner" class="del_partner">
                                            <a onclick="deletePartner();"><i class="icon-remove"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php } }
                             endif;?>
                             
                            <?php // Авторы
                            $autor_list = Aff::getTransactionByOrder($order['order_id'], 'author');
                            $a = 1;
                            if($autor_list){
                                foreach($autor_list as $autor):?>
                            
                                <div class="order-data-table__row">
                                    <div class="order-data-table__left">
                                        <?=System::Lang('AUTHOR');?> <?=$a;?>:
                                    </div>
                                    <div class="order-data-table__right">
                                        <div class="del-partner-row">
                                            <div class="del-partner-text"><?=$autor['summ'];?> <?=$setting['currency'];?> (<a target="_blank" href="/admin/users/edit/<?=$autor['user_id'];?>"><?php $partner_name = User::getUserNameByID($autor['user_id']); echo $partner_name['user_name'];?></a>)</div>
                                            
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                            }?>

                            <? if($order['sale_id'] != null):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Акция:
                                </div>
                                <div class="order-data-table__right">
                                    <a target="_blank" href="/admin/sale/edit/<?= $order['sale_id'];?>"><?= $order['sale_id'];?></a>
                                </div>
                            </div>
                            <?php endif;?>

                            <?php if($order['channel_id'] != 0):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Канал:
                                </div>
                                <div class="order-data-table__right">
                                    <?php $channel = Stat::getChannelData($order['channel_id']);?> <a target="_blank" href="/admin/channels/edit/<?php echo $order['channel_id'];?>"><?php echo $channel['name'];?></a>
                                </div>
                            </div>
                            <?php endif;?>
                            
                            <?php if(isset($params['crm_status']) && $params['crm_status'] == 1):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Менеджер:
                                </div>
                                <div class="order-data-table__right">
                                    <?php $manager = User::getUserNameByID($order['manager_id']);?> <?php if($manager) echo $manager['user_name'].' <a href="#manager_add" data-uk-modal>(изм.)'; else echo '<a href="#manager_add" data-uk-modal>Назначить'?></a>
                                </div>
                            </div>
                            <?php endif;?>

                        </div>

                        <?php // модуль оплаты post-credit
                        $pos_credit_settings = Order::getPaymentSetting('poscredit');
                        if ($pos_credit_settings['status']):
                            $posCredit = new PosCredit();
                            $pc_orders = $posCredit->getOrders($order['order_id']);
                            if ($pc_orders):?>
                                <p>
                                    <?php foreach ($pc_orders as $key => $pc_order):
                                        $client_status = PosCredit::getClientStatusText($pc_order['client_status']);?>
                                        <?php if($key) echo '<hr>';?>
                                        <p>
                                            <p><strong>ID заявки: </strong><?=$pc_order['profile_id'];?></p>
                                            <p><strong>Статус: </strong><?=$client_status;?></p>
                                            <?php if($pc_order['bank']):?>
                                                <p><strong>Выбранный банк: </strong><?=$pc_order['bank'];?></p>
                                            <?php endif;?>
                                            <p><a target="_blank" href="/order-info/<?=$order['order_date'];?>?profile_id=<?=$pc_order['profile_id'];?>&client_email=<?=$order['client_email'];?>">Информация по рассрочке</a></p>
                                        </p>
                                    <?php endforeach;?>
                                </p>
                            <?php endif;
                        endif;?>
                    </div>
                    
                    
                    <div class="round-block mb-20">
                        <div class="order-data-table">
                        <?php if(!empty($order['order_info'])):?>
                            <div class="order-data-table__row">
                                <div class="order-data-table__left">
                                    Доп. инфо:
                                </div>

                                <div class="order-data-table__right">
                                    <p><?php if(isset($order_info['surname'])) echo 'Фамилия: '.$order_info['surname'];?><br />
                                        <?php if(isset($order_info['nick_telegram'])) echo 'Ник в телеграм: '.$order_info['nick_telegram'];?><br />
                                        <?php if(isset($order_info['nick_instagram'])) echo 'Ник в инстаграм: '. $order_info['nick_instagram'];?><br />
                                        <?php if(isset($order_info['vk_id'])) echo 'Страница вконтакте: '. $order_info['vk_id'];?><br />
                                        <?php if(isset($order_info['ok_id'])) echo 'ID OK: '. $order_info['ok_id'];?><br />
                                        <?php if(isset($order_info['org'])) echo 'Организация: '.$order_info['org'];?><br />
                                        <?php if(isset($order_info['inn'])) echo 'ИНН: '.$order_info['inn'];?><br />
                                        <?php if(isset($order_info['bik'])) echo 'БИК: '.$order_info['bik'];?><br />
                                        <?php if(isset($order_info['rs'])) echo 'Счёт: '.$order_info['rs'];?><br />
                                        <?php if(isset($order_info['address'])) echo 'Адрес: '.$order_info['address'];?><br />
                                        <?php if(isset($order_info['aff2'])) echo 'Партнёр №2: '.$order_info['aff2'];?><br />
                                        <?php if(isset($order_info['aff3'])) echo 'Партнёр 32: '.$order_info['aff3'];?><br />
                                        <?php if(isset($order_info['aff_summ'])) echo 'Сумма партнёрских: '.$order_info['aff_summ'];?>
                                        <?php if(!empty($order['subs_id'])) echo 'Продление подписки: <a target="_blank" href="/admin/memberusers/edit/'.$order['subs_id'].'">'.$order['subs_id'].'</a>';?><br />
                                    </p>

                                    <?if($custom_fields_info = CustomFields::getFieldsInfoToOrder($order['order_id'])):?>
                                        <p><strong>Кастомные поля:</strong>
                                            <?=$custom_fields_info;?>
                                        </p>
                                    <?endif;?>
                                </div>
                            </div>
                            <?php endif;?>
                        </div>
                    </div>

                    <?php $order_info = $order['order_info'] ? unserialize(base64_decode($order['order_info'])) : null;
                    $utm_keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_referrer'];
                    $order_utm = $order['utm'] ? System::getUtmData($order['utm']) : null;?>

                    <div class="nav_gorizontal statistics-tags">
                        <div class="nav_gorizontal__parent-wrap">
                            <div class="nav_gorizontal__parent">
                                <a href="javascript:void(0);" class="nav-click">Метки систем статистики</a>
                                <span class="nav-click icon-arrow-down"></span>
                            </div>

                            <ul class="drop_down">
                                <?php foreach ($utm_keys as $utm_key):?>
                                    <li class="flex flex-nowrap">
                                        <div class="statistics-tags-item__key"><?=$utm_key;?></div>
                                        <div class="statistics-tags-item__val"><?=$order_utm && isset($order_utm[$utm_key]) ? $order_utm[$utm_key] : '...';?></div>
                                    </li>
                                <?php endforeach;?>

                                <li class="flex flex-nowrap">
                                    <div class="statistics-tags-item__key">clientID YM</div>
                                    <div class="statistics-tags-item__val"><?=isset($order_info['userId_YM']) ? $order_info['userId_YM'] : '...';?></div>
                                </li>

                                <li class="flex flex-nowrap">
                                    <div class="statistics-tags-item__key">clientID GA</div>
                                    <div class="statistics-tags-item__val"><?=isset($order_info['userId_GA']) ? $order_info['userId_GA'] : '...';?></div>
                                </li>

                                <li class="flex flex-nowrap">
                                    <div class="statistics-tags-item__key">roistat_visitor</div>
                                    <div class="statistics-tags-item__val"><?=isset($order_info['roistat_visitor']) ? $order_info['roistat_visitor'] : '...';?></div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="width-100 mb-30">
                        <!-- ToDo: Список всех статусов по цветам:
                        payment-state-value--green  - зеленый;
                        payment-state-value--yellow - желтый
                        payment-state-value--red    - красный;
                        payment-state-value--gray   - серый;
                        payment-state-value--black  - черный -->
                        <div class="payment-state">
                            <div class="payment-state-title"><?=System::Lang('STATUS');?> оплаты:</div><span class="payment-state-value payment-state-value<?=$order['status'];?>"><?=Order::getStatusText($order['status']);?></span>
                        </div>
                        <div class="select-wrap">
                            <select name="change_status">
                                <option value="">Изменить статус</option>
                                <?php if($order['status'] != 3 && $order['status'] != 4):?>
                                <?php if (isset($acl['change_orders']) && $order['status'] != 1 && $order['status'] != 99):?>
                                <option value="1">Оплачен</option>
                                <?php endif;?>
                                <?php endif;?>
                                <option value="0">Не оплачен</option>
                                <option value="98">Ложный</option>
                                <option value="97">Ожидаем возврата клиенту</option>
                                <?php if($order['status'] != 99):?>
                                <option value="99">Отменён</option>
                                <?php endif;?>
                            </select>
                        </div>
                    </div>
                    
                    <?php if(isset($params['crm_status']) && $params['crm_status'] == 1):
                    $statuses = Order::getCRMStatusList();?>
                    <div class="width-100">
                        <div class="payment-state">
                            <div class="payment-state-title">Статус менеджера:</div>
                            <?php if($order['crm_status'] > 0):?>
                                <span class="payment-state-value payment-state-value--blue">
                                    <?php $status_data = Order::getCRMStatus($order['crm_status']); echo $status_data['title'];?>
                                </span>
                            <?php endif;?>
                        </div>
                        <div class="select-wrap">
                            <select name="crm_status">
                                <option value="0">- не задан -</option>
                                <?php if($statuses):
                                foreach($statuses as $crm_status):?>
                                <option value="<?=$crm_status['id'];?>"<?php if($crm_status['id'] == $order['crm_status']) echo ' selected="selected"';?>><?=$crm_status['title'];?></option>
                                <?php endforeach;
                                endif;?>
                            </select>
                        </div>
                    </div>
                    <?php endif;?>
                    

                    <?php if($order['ship_method_id'] != null):?>
                        <div class="width-100"><label><?=System::Lang('STATUS_DELIVERY');?>:</label>
                            <div class="select-wrap">
                                <select name="ship_status">
                                    <option value="0"<?php if(isset($order['ship_status']) && $order['ship_status'] == '0') echo ' selected="selected"';?>>-- не выбрано --</option>
                                    <option value="1"<?php if(isset($order['ship_status']) && $order['ship_status'] == '1') echo ' selected="selected"';?>>готовится</option>
                                    <option value="2"<?php if(isset($order['ship_status']) && $order['ship_status'] == '2') echo ' selected="selected"';?>>отправлен</option>
                                    <option value="3"<?php if(isset($order['ship_status']) && $order['ship_status'] == '3') echo ' selected="selected"';?>>получен клиентом</option>
                                    <option value="4"<?php if(isset($order['ship_status']) && $order['ship_status'] == '4') echo ' selected="selected"';?>>вернулся назад</option>
                                </select>
                            </div>
                        </div>
                    <?php endif;?>

                    <?php if($order['ship_method_id'] != null):
                            $ship_method = System::getShipMethod($order['ship_method_id']);?>
                    <div class="width-100">
                        <div class="order-data-table__row">
                            <div class="order-data-table__left">
                                Способ доставки:
                            </div>
                            <div class="order-data-table__right">
                                <?=$ship_method['title'];?>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                    
                    <?php if($order['installment_map_id'] != 0):?>
                    <?php $instalement = Order::getInstallmentMapData($order['installment_map_id']);?>
                        <?php if ($instalement['max_periods'] == 2) { ?>
                        <div class="width-100">
                            <a href="/admin/installment/map/<?=$order['installment_map_id'];?>" target="_blank">Предоплата ID <?=$order['installment_map_id'];?></a>
                        </div>
                        <?php } else { ?>
                            <div class="width-100">
                                <a href="/admin/installment/map/<?=$order['installment_map_id'];?>" target="_blank">Рассрочка ID <?=$order['installment_map_id'];?></a>
                            </div>
                        <?php } ?>
                    <?php endif;?>
                    
                    <?php if($order['status'] == 3):
                        $map = Order::getInstallmentMapData($order['installment_map_id']);
                        $link = $setting['script_url'].'/installment/vote?key='.md5($setting['secret_key']).'&order='.$order['order_id'].'&map_id='.$order['installment_map_id'].'&install_id='.$map['installment_id'];?>
                    <a target="_blank" href="<?=$link.'&answer=1';?>">Одобрить</a> |
                    <a style="color:#E04265" target="_blank" href="<?=$link.'&answer=0';?>">Отклонить</a> 
                    <?php endif;?>
                    
                </div>
            </form>
    
            <div class="col-1-1 mt-10">
                <h4 class="h4-border"><?=System::Lang('ORDER_CONTENT');?></h4>
                <div class="overflow-container">
                    <table class="table-no-border table-tightly">
                        <?php $items = Order::getOrderItems($order['order_id']);
                        $total = 0;
                        if($items):
                            foreach($items as $item):?>
                                <tr<?php if($item['status'] == 0) echo ' class="off"'; elseif($item['status'] == 9) echo ' class="refund"';?>>

                                    <td style="width: 42px;">
                                        <form action="" method="POST" id="del_<?=$item['order_item_id'];?>">
                                            <input type="hidden" name="order_item_delete" value="<?=$item['order_item_id'];?>">
                                            <button class="button-red-rounding button-lesson" type="submit" title="Удалить" name="reload"><span class="icon-remove"></span></button>
                                            <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                                        </form>
                                    </td>
                                    <td>
                                        <form class="price-input-form" action="" id="reload__<?=$item['order_item_id'];?>" method="POST">
                                            <input required style="width:60px;margin-right:10px;" placeholder="ID" type="text" name="prod_id" value="<?=$item['product_id'];?>">
                                            <input type="hidden" name="reload_order_item" value="<?=$item['order_item_id'];?>">
                                            <input type="image" src="/template/admin/images/reload.png" title="Обновить" name="reload">
                                        </form>
                                    </td>
                                    
                                    <td><?php $product_data = Product::getProductName($item['product_id']);
                                        $prod_name = !empty($product_data['service_name']) ? "{$product_data['service_name']}{$product_data['mess']}" : "{$product_data['product_name']}{$product_data['mess']}";?>
                                        <a target="_blank" href="/admin/products/edit/<?=$item['product_id'];?>"><?=$prod_name;?></a>
                                    </td>

                                    <?php if($item['status'] == 1):?>
                                    <td style="width: 42px;">
                                        <form action="" id="order_<?=$item['order_item_id'];?>" method="POST">
                                            <input type="hidden" name="id" value="<?=$item['product_id'];?>">
                                            <input type="hidden" name="pin" value="<?=$item['pincode'];?>">
                                            <input type="hidden" name="order_item" value="<?=$item['order_item_id'];?>">
                                            <input type="hidden" name="email" value="<?=$order['client_email'];?>">
                                            <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                                            <button class="button-return button-black-rounding button-lesson" type="submit" name="refund" value="" title="Сделать возврат"><i class="icon-stat-2"></i></button>
                                        </form>
                                    </td>
                                    <?php endif;?>

                                    <td><input type="text" style="width:110px" value="<?=$item['pincode'];?>" placeholder="ключ"></td>

                                    <td>
                                        <form class="price-input-form" action="" id="reload_<?=$item['order_item_id'];?>" method="POST">
                                            <div class="price-input-wrap">
                                                <input class="price-input" type="text" name="price" value="<?=$item['price'];?>">
                                                <div class="price-input-cur"><?=$setting['currency'];?></div>
                                            </div>

                                            <input type="hidden" name="reload_order_item" value="<?=$item['order_item_id'];?>">
                                            <input type="image" src="/template/admin/images/reload.png" title="Обновить" name="reload">
                                        </form>
                                    </td>

                                </tr>
                                <?php if($item['flow_id'] > 0):?>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>
                                        <div class="select-wrap">
                                            <select name="flow_id" form="reload__<?=$item['order_item_id'];?>" <?php if($order['status'] == 1) echo ' disabled="disabled"';?>>
                                                <?php $flow_list = Flows::getFlows();
                                                foreach($flow_list as $flow){?>
                                                <option value="<?=$flow['flow_id'];?>"<?php if($flow['flow_id'] == $item['flow_id']) echo ' selected="selected"';?>>Поток: <?=$flow['flow_name'];?></option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif;?>
                                <?php $total = $total + $item['price'];
                            endforeach;
                        endif;?>
                    </table>
                </div>


                <?php if($order['status'] != 1):?>
                    <div class="mt-10">
                        <a class="link-no-underline" href="#modal_add_product" data-uk-modal="{center:true}">Добавить товар к заказу</a>
                    </div>
                <?php endif;?>

                <p class="mt-10 text-right"><strong>Сумма заказа: <?="$total {$setting['currency']}";?></strong></p>

                <?php if ($order['status'] != 1) {?>
                    <?php $deposits = json_decode($order['deposit'], true); $depositsSum = 0;?>
                    <div style="text-align: right;">
                        <div style="display: inline-block; <?= $deposits ? "background-color: #F8F9FD; padding: 16px 16px;" : ""; ?> border-radius: 10px; min-width: 330px;"
                             id="wrapPrePayments">
                            <div class="mt-10 text-right" id="prepaymentListWrap">
                                <?php if ($deposits) {
                                    foreach ($deposits as $deposit) {
                                        $depositsSum += $deposit['sum']; ?>
                                            <div style="margin-bottom: 8px; text-align: right;">
                                                <div style="margin-top: 8px;">Предоплата <?= date('j.m.Y', $deposit['time']) ?>: <?= $deposit['sum'] ?> р.</div>
                                            <div>
                                                <i style="font-size: 12px;">добавил <?= User::getUserById($deposit['userId'])['user_name'] ?></i>
                                            </div>
                                        </div>
                                <?php } } ?>
                            </div>
                            <p class="text-right <?= !$deposits ? "hidden" : ""; ?>" id="RemAmount" style="margin-top: 8px;">
                                <strong>Осталось оплатить: <span id="prepaymentRemainingAmount">
                                        <?= $total - $depositsSum ?>
                                    </span> р.
                                </strong>
                            </p>
                            <p class="mt-10 text-right" style="margin-top: -8px; margin-bottom: 0px;">
                                <a class="link" onclick="showPrepaymentAddWrap()">Добавить предоплату</a>
                            </p>
                            <div id="prepaymentAddWrap" class="row-line hidden"
                                 style="justify-content: flex-end; margin-top: 8px;">
                                <div class="price-input-wrap" bis_skin_checked="1">
                                    <input class="price-input" type="text" name="price" value="100"
                                           id="prepaymentAddInput">
                                    <div class="price-input-cur" bis_skin_checked="1">р.</div>
                                </div>
                                <input onclick="sendNewPrepayment(); showPrepaymentAddWrap();"
                                       style="width:100px; margin-left: 0px;" name="add_block" readonly
                                       class="button save button-green-rounding button-lesson" value="Применить">
                            </div>
                        </div>
                    </div>
                    <script>
                        function showPrepaymentAddWrap() {
                            document.getElementById("prepaymentAddWrap").classList.toggle("hidden");
                        }

                        let noDeposits = <?= !$deposits ? '1' : '0' ?>;

                        async function sendNewPrepayment() {
                            //Получить данные
                            let addSum = document.getElementById("prepaymentAddInput").value;

                            //Отправить запрос
                            let response = await fetch('/admin/orders/prepaymentadd/<?=$order["order_id"]?>?sum=' + addSum, {
                                method: "GET",
                                credentials: "include",
                            });
                            let status = await response.status;
                            switch (await status) {
                                case 201:
                                    response = await response.json();
                                    if (!response.status) {
                                        alert('Произошла ошибка2');
                                        return console.log(response);
                                    }
                                    changeData(response.time, addSum);
                                    break;
                                default:
                                    alert('Произошла ошибка');
                                    console.log(response.text());
                                    break;
                            }
                        }

                        function changeData(time, sum) {
                            sum = parseInt(sum);

                            if (noDeposits == 1) {
                                document.getElementById("RemAmount").classList.remove('hidden');
                                document.getElementById("wrapPrePayments").style.backgroundColor = "#F8F9FD";
                                document.getElementById("wrapPrePayments").style.padding = "16px 16px";
                                noDeposits = 0;
                            }

                            document.getElementById("prepaymentListWrap").innerHTML += "<div>Предоплата " + time + ": " + sum + " р.</div>";

                            let remainingAmount = document.getElementById("prepaymentRemainingAmount").innerText !== "" ? document.getElementById("prepaymentRemainingAmount").innerText : <?= $total - $depositsSum ?>;
                            remainingAmount = parseInt(remainingAmount);
                            document.getElementById("prepaymentRemainingAmount").innerText = remainingAmount - sum;
                        }
                    </script>
                <?php } ?>
                <style>
                    #prepaymentListWrap br {
                        margin-bottom: 8px !important;
                    }
                </style>



                <?php if($order['payment_date'] == null):
                        if($order['expire_date'] == 0) $expire = $order['order_date'] + $setting['order_life_time'] * 86400;
                        else $expire = $order['expire_date'];?>
                <div style="display: none;">
                <?php if($order['installment_map_id'] != 0 && isset($instalement) && $instalement['max_periods'] == 2):?>
                    <?php $pay_actions = unserialize(base64_decode($instalement['pay_actions'])); ?>

                    <h4>Предоплата</h4>

                    <?php if (isset($pay_actions) && is_array($pay_actions) && count($pay_actions) > 0) {?>
                            <?php $contributed = 0; foreach ($pay_actions as $pay) { $contributed += $pay['summ']; } ?>
                        <div class="width-100">
                            <table>
                                <tr>
                                    <th class="text-left">Номер</th>
                                    <th class="text-left">Дата</th>
                                    <th class="text-left">Сумма</th>
                                    <th>Act</th>
                                </tr>
                            <?php  $i = 0; foreach ($pay_actions as $key => $pay_action) { ?>
                                <tr class="td-not-border">
                                    <td><?=$i+1;?></td>
                                    <td><input class="datetimepicker" type="text" name="installment[payment][<?=$i?>][date]" value="<?=date("d-m-Y H:i:s", $pay_action['date'] ?? null)?>"></td>
                                    <td><input type="text" name="installment[payment][<?=$i?>][summ]" value="<?= $pay_action['summ'] ?>"></td>
                                    <td class="text-center"><a class="link-delete" onclick="return confirm('Вы уверены?')" href="/admin/orders/edit/<?=$order['order_id']?>?deleteInstallmentPaymentAction=<?=$key?>" title="Удалить"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                                </tr>
                            <?php $i++; } ?>
                            </table>
                        </div>
                        <a class="link-no-underline" href="#Prepayment_add" data-uk-modal="{center:true}">Добавить платеж</a>

                    <?php } else { ?>
                        <a class="link-no-underline" href="#Prepayment_add" data-uk-modal="{center:true}">Внести платеж</a>
                <?php } endif;?>
                <?php endif;?>
                    <?php if ($order['status'] != 1) { ?>
                </div>
                <div class="due-date"><strong>Крайний срок оплаты:</strong>
                    <input style="border:none; padding: 0 0 0 5px; width: auto; text-decoration:underline; color:#0772A0"
                           class="datetimepicker" required type="text" name="expire_date"
                           value="<?= date("d.m.Y H:i:s", $expire ?? ""); ?>">
                </div>
            <?php } ?>
            </div>
            <div class="col-1-1 mt-10">
                <div class="round-block"><label><?=System::Lang('ADMIN_COMMENT');?>:</label>
                    <textarea cols="55" rows="2" name="admin_comment"><?=$order['admin_comment'];?></textarea>
                </div>
            </div>
        </div>
<?php if(isset($params['allow_admin_to_delete_orders']) && $params['allow_admin_to_delete_orders'] == 1):?>
    <p class="button-delete" style="margin-bottom: 24px;">
        <a onclick="return confirm('<?=System::Lang('YOU_SHURE');?>?')" href="<?=$setting['script_url'];?>/admin/orders/del/<?=$order['order_id'];?>?token=<?=$_SESSION['admin_token'];?>" title="<?=System::Lang('DELETE');?>">
            <i class="icon-remove"></i><?=System::Lang('DELETE_ORDER');?>
        </a>
    </p>
<?php endif; ?>
    </div>



    <div id="Prepayment_add" class="uk-modal">
        <div class="uk-modal-dialog uk-modal-dialog-3" style="padding:0">
            <div class="userbox modal-userbox-3">
                <form id="Prepayment_form" action="" method="POST">
                    <div class="admin_top admin_top-flex">
                        <h3 class="traning-title">Внести платеж</h3>
                        <ul class="nav_button">
                            <li>
                                <input type="submit" name="add_prepayment" value="Сохранить" class="button save button-white font-bold">
                            </li>
                            <li class="nav_button__last">
                                <a class="button red-link uk-modal-close uk-close" href="#close">Закрыть</a>
                            </li>
                        </ul>
                    </div>

                    <div class="admin_form">
                        <div class="row-line">
                            <div class="col-1-2">
                                <div class="due-date" style="border:none; padding: 0; margin-top:0;"><span style="display: block; margin-bottom: 8px;">Дата:</span>
                                    <input class="datetimepicker" required type="text" name="prepayment_date" value="<?= date('d.m.Y H:i:s', time()) ?>">
                                </div>
                                <p class="width-100"><label>Сумма: </label>
                                    <input type="text" name="prepayment_summ" value="">
                                </p>
                                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                            </div>
                        </div>
                    </div>



                </form>
            </div>
        </div>
    </div>


    <div id="manager_add" class="uk-modal">
        <div class="uk-modal-dialog uk-modal-dialog-3" style="padding:0">
            <div class="userbox modal-userbox-3">
                <form id="manager" action="" method="POST">
                <div class="admin_top admin_top-flex">
                    <h3 class="traning-title">Изменить менеджера</h3>
                    <ul class="nav_button">
                        <li>
                            <input type="submit" name="add_manager" value="Сохранить" class="button save button-white font-bold">
                        </li>
                        <li class="nav_button__last">
                            <a class="button red-link uk-modal-close uk-close" href="#close">Закрыть</a>
                        </li>
                    </ul>
                </div>
                <div class="admin_form">
                    <div class="row-line">
                        <div class="col-1-2">
                            <select name="manager_id">
                                <option value="0">- не назначен -</option>
                                <?php $manager_list = User::getManagerList();
                                if($manager_list):
                                    foreach($manager_list as $manager):?>
                                <option value="<?=$manager['user_id']?>"<?php if($manager['user_id'] == $order['manager_id']) echo ' selected="selected"';?>><?=$manager['user_name'];?></option>
                                <?php endforeach; 
                                endif;?>
                            </select>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    require_once(__DIR__ . '/add_product.php');
    require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
  jQuery('.datetimepicker').datetimepicker({
    format:'d.m.Y H:i:s',
    lang:'ru'
  });
  
   function deletePartner() {
      if (confirm('Вы точно хотите удалить партнера из заказа и начисления ?')) {

         $.ajax({
           url: '/admin/orders/delpartner',
           method: 'post',
           dataType: 'json',
           data: {order_id:"<?php echo $order['order_id'];?>", partner_id:"<?php echo $order['partner_id'];?>", delpartner: 'true'},
           success: function(data) {
             if (data.success) {
                $('#part_id').remove();
                $('#del_partner').empty();
                $('#del_partner').html('<p>Партнер удален...</p>');                 
             }
            }
         });
      }
    };

   $('.statistics-tags .drop_down').click(function() {
     return false;
   });
</script>
</body>
</html>
<?php function CreatedBy($type)
{
    switch($type){
        case 0:
        $title = 'нет данных';
        break;
        
        case 1:
        $title = 'пользователем';
        break;
        
        case 2:
        $title = 'из админки';
        break;
        
        case 3:
        $title = 'через API';
        break;
		
		case 4:
        $title = 'системой';
        break;
    }
    return $title;    
}
?>