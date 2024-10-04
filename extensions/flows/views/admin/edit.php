<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<style>
.table-prods:hover {background:#efefef}
</style>
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Изменить поток</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/flows/">Потоки</a>
        </li>
        <li>Изменить поток</li>
    </ul>

    <span id="notification_block"></span>
    <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Изменить поток</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="save_flow" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/flows/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>События</li>
            </ul>
            <div class="admin_form">
                <div>
                    <h4 class="h4-border">Общие настройки</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <p class="width-100"><label>Название</label><input type="text" name="flow_name" value="<?=$flow['flow_name'];?>" placeholder="Название потока" required="required"></p>
                            <p class="width-100"><label>Название для учеников</label><input type="text" name="flow_title" value="<?=$flow['flow_title'];?>" placeholder="Название потока" required="required"></p>
                            <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                            <div class="width-100"><label>Статус</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1"<?php if($flow['status'] == 1) echo ' checked="checked"';?>><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0"<?php if($flow['status'] == 0) echo ' checked="checked"';?>><span>Откл</span></label>
                                </span>
                            </div>
                            
                            <div><label class="custom-chekbox-wrap" for="is_default">
                                <input type="checkbox" id="is_default" name="is_default" value="1" <?php if($flow['is_default'] == 1) echo ' checked="checked"'; ?>>
                                    <span class="custom-chekbox"></span>Поток по-умолчанию
                                </label>
                            </div>
                        </div>
                    
                        <div class="col-1-2">
                            <p class="width-100" title="-1 без лимита"><label>Лимит пользователей</label><input type="text" name="limit" placeholder="Лимит пользователей" value="<?=$flow['limit_users'];?>"></p>
                        </div>
                        
                        
                        <div class="col-1-1">
                            <h4>Привязка к продуктам</h4>
                            <table>
                            <?php if($flow_products){
                                foreach($flow_products as $product){?>
                                    
                                <tr class="table-prods">
                                    <td>
                                        <?php $prod_name = Product::getProductName($product);
                                        echo $prod_name['product_name'];?>
                                    </td>
                                    <td class="td-last">
                                        <form id="prod_<?=$product;?>" method="POST">
                                            <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                                            <input type="hidden" name="product_id" value="<?=$product;?>">
                                            <button type="submit" onclick="return confirm('Вы уверены?')" title="Удалить" name="del_product" class="button save button-red-rounding button-lesson"><span class="icon-remove"></span></button>
                                        </form>
                                    </td>
                                </tr>
                                    
                                <?php }
                            } else {
                                $flow_products = [];
                                echo '<tr><td style="color:red">У вас не указано привязки к продуктам. Обязательно укажите!</td></tr>';
                            }?>
                            </table>
                        </div>
                        
                        <div class="col-1-1">
                            <form class="row-line inner-flex-end" id="add_product" action="" method="POST">
                            <div class="col-1-4">
                                <label>Добавить продукт</label>
                                <div class="select-wrap">
                                    <select name="product_id">
                                        <option value="0">Нет</option>
                                        <?php foreach ($list_select as $item):
                                            if(in_array($item['product_id'], $flow_products)) continue;?>
                                            <option value="<?=$item['product_id'];?>"><?=$item['product_name'];?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-1-4">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                                <input type="submit" name="add_product" class="button save button-green-rounding add-prod-but" value="Добавить">
                            </div>
                            </form>
                        </div>
                    </div>
                    
                    
                    <h4 class="h4-border" style="margin-top: 40px;">Период</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <p><label>Дата начала потока</label><input type="text" class="datetimepicker" name="start_flow" value="<?=date("d.m.Y H:i", $flow['start_flow'])?>" autocomplete="off"></p>
                        </div>
                        
                        <div class="col-1-2">
                            <p><label>Дата завершения потока</label><input type="text" required="required" class="datetimepicker" value="<?=date("d.m.Y H:i", $flow['end_flow'])?>" name="end_flow" autocomplete="off"></p>     
                        </div>
                        
                        <div class="col-1-1">
                            <div class="width-100"><label>Показывать даты потока</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="show_period" type="radio" value="1"<?php if($flow['show_period'] == 1) echo 'checked="checked"';?>><span>Показать</span></label>
                                    <label class="custom-radio"><input name="show_period" type="radio" value="0"<?php if($flow['show_period'] == 0) echo 'checked="checked"';?>><span>Скрыть</span></label>
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-1-2">
                            <p><label>Дата начала продаж</label><input type="text" class="datetimepicker" name="public_start" value="<?=date("d.m.Y H:i", $flow['public_start'])?>" autocomplete="off"></p>
                            </div>
                        
                        <div class="col-1-2">
                            <p><label>Дата завершения продаж</label><input type="text" class="datetimepicker" required="required" name="public_end" value="<?=date("d.m.Y H:i", $flow['public_end'])?>" autocomplete="off"></p>
                        </div>
                    
                    </div>
                    
                    
                    <h4 class="h4-border" style="margin-top: 40px;">Статистика</h4>
                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="col-1-1">
                                <?$calc_sales = Flows::getStatistics($flow['flow_id']);
                                if($calc_sales):
                                    if(isset($calc_sales['paid']['count'])): //оплаченные счета?>
                                        <p><span class="checked-status"></span> Оплаченных счетов: <?=$calc_sales['paid']['count'];?> на сумму <?="{$calc_sales['paid']['sum']} {$setting['currency']}";?></a></p>
                                    <?endif;
                                    if(isset($calc_sales['issue']['count'])): //выписанные счета?>
                                        <p><span class="icon-stopwatch"></span> Выписанных счетов: <?=$calc_sales['issue']['count'];?> на сумму <?="{$calc_sales['issue']['sum']} {$setting['currency']}";?></a></p>
                                    <?endif;
                                    if(isset($calc_sales['expect_confirm']['count'])): //ждут подтверждения?>
                                        <p><span class="status-close"></span> Ожидают подтверждения: <?=$calc_sales['expect_confirm']['count'];?> на сумму <?="{$calc_sales['expect_confirm']['sum']} {$setting['currency']}";?></a></p>
                                    <?endif;
                                    if(isset($calc_sales['refund']['count'])): //возвраты?>
                                        <p><span class="icon-stat-2" style="font-size: 21px;"></span> Возвратов: <?=$calc_sales['refund']['count'];?>  на сумму <?="{$calc_sales['refund']['sum']} {$setting['currency']}";?></a></p>
                                    <?endif;
                                endif;?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="h4-border">Письмо при покупке потока (админу, куратору)</h4>
                    <div class="row-line">
                        
                        <div class="col-1-1">
                            <p class="label"><input type="text" name="letter[sell_emails]" value="<?if(isset($letter['sell_emails'])) echo $letter['sell_emails'];?>" title="Список email через запятую" placeholder="Список email через запятую"></p>
                            <p class="label"><input type="text" name="letter[sell_subject]" value="<?if(isset($letter['sell_subject'])) echo $letter['sell_subject'];?>" title="Тема письма" placeholder="Тема письма"></p>
                            <p><textarea name="letter[sell_text]" class="editor" rows="6" style="width:100%"><?if(isset($letter['sell_text'])) echo $letter['sell_text'];?></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                            </p>
                        </div>
                            
                            
                        <div class="col-1-1" style="margin: 20px 0 0 30px">
                            <h4>События при старте потока</h4>
                            
                        </div>
                    
                        <div class="col-1-2">
                            <div class="width-100"><label>Добавить группы</label>
                                <select size="7" class="multiple-select" multiple="multiple" name="add_groups[]">
                                    <?php if($group_list):
                                        $add_groups = json_decode($flow['groups'], true);
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>" <?php if(!empty($add_groups)){ if(in_array($user_group['group_id'], $add_groups)) echo ' selected="selected"'; }?>>
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                    
                        <div class="col-1-2">
                        &nbsp;
                        </div>
                    
                        <div class="col-1-2">
                            <div class="width-100">
                                <label>Добавить планы подписок</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="add_planes[]">
                                <?php $add_planes = json_decode($flow['planes'], true);
                                    if($plane_list):
                                        foreach($plane_list as $plane_one):?>
                                            <option value="<?=$plane_one['id'];?>"<?php if(!empty($add_planes)){ if(in_array($plane_one['id'], $add_planes)) echo ' selected="selected"'; }?>>
                                                <?=$plane_one['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                    
                        <div class="col-1-1">
                            <h5 class="h4-border" style="font-weight:normal">Письмо клиенту</h5>
                            <p class="label"><input type="text" name="letter[subject]" value="<?=$letter['subject'];?>" placeholder="Тема письма"></p>
                            <p><textarea name="letter[text]" class="editor" rows="6" style="width:100%"><?=$letter['text'];?></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                                [AUTH_LINK] - Ссылка с автоматическим входом<br>
                                [AUTH_LINK='/хвостссылки'] - Ссылка с автоматическим входом и редиректом на какую-либо страницу. Пример: [AUTH_LINK='/blog'] - после авторизации переход в блог<br>
                            </p><br />
                        </div>
                    </div>
                    
                    <h4 class="h4-border">События при завершении потока</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <div class="width-100"><label>Удалить группы</label>
                                <select size="7" class="multiple-select" multiple="multiple" name="del_groups[]">
                                    <?php if($group_list):
                                        $del_groups = json_decode($flow['del_groups'], true);
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>" <?php if(!empty($del_groups)){ if(in_array($user_group['group_id'], $del_groups)) echo ' selected="selected"'; }?>>
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-1-1">
                            <h5 class="h4-border" style="font-weight:normal">Письмо клиенту</h5>
                            <?php $letter = json_decode($flow['letter'], true);?>
                            <p class="label"><input type="text" name="letter[subject_after]" value="<?=$letter['subject_after'];?>"></p>
                            <p><textarea name="letter[text_after]" class="editor" rows="6" style="width:100%"><?=$letter['text_after'];?></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                                [AUTH_LINK] - Ссылка с автоматическим входом<br>
                                [AUTH_LINK='/хвостссылки'] - Ссылка с автоматическим входом и редиректом на какую-либо страницу. Пример: [AUTH_LINK='/blog'] - после авторизации переход в блог<br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    
        <p class="button-delete">
            <a onclick="return confirm('Вы уверены?')" href="<?php echo $setting['script_url'];?>/admin/flows/del/<?=$flow['flow_id'];?>?token=<?php echo $_SESSION['admin_token'];?>"><i class="icon-remove"></i>Удалить поток</a>
        </p>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
jQuery('.datetimepicker').datetimepicker({
format:'d.m.Y H:i',
lang:'ru'
});
</script>
</body>
</html>