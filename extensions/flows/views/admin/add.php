<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Создать поток</h1>
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
        <li>Создать поток</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Создать поток</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="add_flow" value="Создать" class="button save button-white font-bold"></li>
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
                <h4 class="h4-border">Основные настройки</h4>
                <div class="row-line">
                    <div class="col-1-2">
                        <p class="width-100"><label>Название</label><input type="text" name="flow_name" placeholder="Название потока" required="required"></p>
                        <p class="width-100"><label>Название для учеников: </label><input type="text" name="flow_title" placeholder="Название потока" required="required"></p>
                        <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                        
                        <div class="width-100"><label>Статус</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio"><input name="status" type="radio" value="1" checked=""><span>Вкл</span></label>
                                <label class="custom-radio"><input name="status" type="radio" value="0"><span>Откл</span></label>
                            </span>
                        </div>
                        
                        <div><label class="custom-chekbox-wrap" for="is_default">
                            <input type="checkbox" id="is_default" name="is_default" value="1">
                                <span class="custom-chekbox"></span>Поток по-умолчанию
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-1-2">
                        <div class="width-100">
                            <label>Действует на товары</label>
                            <select class="multiple-select" name="products[]" multiple="multiple" size="10" required="required">
                                <?$product_list = Product::getProductListOnlySelect();
                                foreach ($product_list as $product):?>
                                    <option value="<?=$product['product_id'];?>"><?=$product['product_name'];?></option>
                                    <?if($product['service_name']):?>
                                        <option disabled="disabled" class="service-name">(<?=$product['service_name'];?>)</option>
                                    <?endif;
                                endforeach?>
                            </select>
                        </div>
                        <p class="width-100" title="-1 без лимита"><label>Лимит пользователей: </label><input type="text" name="limit" placeholder="Лимит пользователей"></p>
                    </div>
                </div>
                
                
                <h4 class="h4-border">Период</h4>
                <div class="row-line">
                    <div class="col-1-2">
                        <p><label>Дата начала потока</label><input type="text" class="datetimepicker" name="start_flow" autocomplete="off" placeholder="От"></p>
                        
                    </div>
                    
                    <div class="col-1-2">
                        <p><label>Дата завершения потока</label><input type="text" required="required" class="datetimepicker" name="end_flow" autocomplete="off" placeholder="До"></p>
                    </div>
                    
                    <div class="col-1-1">
                        <div class="width-100"><label>Показывать даты потока</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio"><input name="show_period" type="radio" value="1"><span>Показать</span></label>
                                <label class="custom-radio"><input name="show_period" type="radio" value="0" checked="checked"><span>Скрыть</span></label>
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-1-2">
                        <p><label>Дата начала продаж</label><input type="text" class="datetimepicker" name="public_start" autocomplete="off" placeholder="От"></p>
                    </div>
                    
                    <div class="col-1-2">
                        <p><label>Дата завершения продаж</label><input type="text" class="datetimepicker" required="required" name="public_end" autocomplete="off" placeholder="До"></p>
                    </div>
                </div>
                </div>
                    
                
                <div>
                    <h4 class="h4-border">Письмо при покупке потока (админу, куратору)</h4>
                    <div class="row-line">
                        
                        <div class="col-1-1">
                            <p class="label"><input type="text" name="letter[sell_emails]" value="" title="Список email через запятую" placeholder="Список email через запятую"></p>
                            <p class="label"><input type="text" name="letter[sell_subject]" value="" title="Тема письма" placeholder="Тема письма"></p>
                            <p><textarea name="letter[sell_text]" class="editor" rows="6" style="width:100%"></textarea></p>
        
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
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>">
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
                            <div class="width-100"><label>Добавить планы подписок</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="add_planes[]">
                                <?php if($plane_list):
                                        foreach($plane_list as $plane):?>
                                            <option value="<?=$plane['id'];?>">
                                                <?=$plane['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-1-1">
                            <h5 class="h4-border" style="font-weight:normal">Письмо клиенту</h5>
                            
                            <p class="label"><input type="text" name="letter[subject]" value="" placeholder="Тема письма"></p>
                            <p><textarea name="letter[text]" class="editor" rows="6" style="width:100%"></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                                [AUTH_LINK] - Ссылка с автоматическим входом<br>
                                [AUTH_LINK='/хвостссылки'] - Ссылка с автоматическим входом и редиректом на какую-либо страницу. Пример: [AUTH_LINK='/blog'] - после авторизации переход в блог<br>
                            </p>
                        </div>
                        
                        
                        <div class="col-1-1" style="margin: 20px 0 0 30px">
                            <h4>События при завершении потока</h4>
                        </div>
                        
                        <div class="col-1-2">
                            <div class="width-100"><label>Удалить группы</label>
                                <select size="7" class="multiple-select" multiple="multiple" name="del_groups[]">
                                    <?php if($group_list):
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>">
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-1-1">
                            <h5 class="h4-border" style="font-weight:normal">Письмо клиенту</h5>
                            <p class="label"><input type="text" name="letter[subject_after]" placeholder="Тема письма"></p>
                            <p><textarea name="letter[text_after]" class="editor" rows="6" style="width:100%"></textarea></p>
        
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
        </div>
    </form>
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