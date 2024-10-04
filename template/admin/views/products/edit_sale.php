<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Изменить акцию</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?=$setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/products/">Продукты</a></li>
        <li><a href="/admin/sales/">Акции</a></li>
        <li>Изменить акцию</li>
    </ul>
    
    <span id="notification_block"></span>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/hot-sale.png" alt="">
                </div>
                
                <div>
                    <h3 class="traning-title mb-0">Изменить акцию</h3>
                </div>
            </div>
            
            <ul class="nav_button">
                <li><input type="submit" name="edit" value="Сохранить" class="button save button-green-rounding"></li>
                <li class="nav_button__last"><a class="button button-red-rounding" href="/admin/sales/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 mb-0">
                    <h4>Основное</h4>
                </div>
            </div>
            
            <div class="row-line">
                <div class="col-1-2">
                    <p><label>Название</label>
                        <input type="text" name="name" placeholder="Название акции" value="<?=$sale['name'];?>" required="required">
                    </p>

                    <div class="width-100">
                        <label>Статус</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio">
                                <input name="status" type="radio" value="1" <?if($sale['status'] == 1) echo 'checked';?>><span>Вкл</span>
                            </label>
                            <label class="custom-radio">
                                <input name="status" type="radio" value="0" <?if($sale['status'] == 0) echo 'checked';?>><span>Откл</span>
                            </label>
                        </span>
                    </div>
                    
                    <div class="width-100"><label>Тип</label>
                        <div class="select-wrap">
                            <select name="type">
                                <option value="1"<?if($sale['type'] == 1) echo ' selected="selected"';?>>Красная цена</option>
                                <option data-show_on="promo_calc_discount_box,count_uses" value="<?=in_array($sale['type'], [2,9]) ? $sale['type'] : 2;?>"<?if(in_array($sale['type'], [2,9])) echo ' selected="selected"';?>>Промокод</option>
                                <?if($setting['use_cart'] == '1'):?>
                                    <option data-show_on="promo_cart_box,warning_sale_cart" value="5"<?if($sale['type'] == 5) echo ' selected="selected"';?>>Скидка в корзине</option>
                                <?endif;?>
                                <!--option value="3"<?// if($sale['type'] == 3) echo ' selected="selected"';?>>Динамическая</option-->
                            </select>
                        </div>
                    </div>

                    <div class="hidden" id="promo_cart_box">
                        <div class="width-100">
                            <label>Зависит от</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio">
                                    <input name="params[level_type]" type="radio" value="count_prod" onchange="changelevel(this);" <?if($params['level_type'] == 'count_prod') echo 'checked';?>><span>Кол-ва продуктов</span>
                                </label>
                                <label class="custom-radio">
                                    <input name="params[level_type]" type="radio" value="summ_prod" onchange="changelevel(this);" <?if($params['level_type'] == 'summ_prod') echo 'checked';?>><span>Суммы продуктов</span>
                                </label>
                            </span>
                        </div>
                        <div class="width-100">
                            <label>Тип скидки</label>
                            <div class="select-wrap">
                                <select name="params[discount_type]">
                                    <option value="summ"<?if($params['discount_type'] == 'summ') echo ' selected="selected"';?>>Сумма</option>
                                    <option value="percent"<?if($params['discount_type'] == 'percent') echo ' selected="selected"';?>>Проценты</option>
                                </select>
                            </div>
                        </div>

                  
                        <div id="params-level" class="width-100">
                            <?$i = 0;
                            if (isset($params['count_or_summ']) && is_array($params['count_or_summ'])) {
                                foreach ($params['count_or_summ'] as $level):?>
                                <div id="line-level" class="add-label-row">
                                    <div class="add-label-title">от <?if($params['level_type'] == 'summ_prod') echo 'суммы'; else echo 'кол-ва';?></div>
                                    <div class="add-label-type">
                                        <label>Уровень</label>
                                        <input name="params[count_or_summ][]" type="text" value="<?=$level;?>">
                                    </div>
                                    <div class="add-label-type">
                                        <label title="Скидка будет применена на каждую товарную позицию">Размер скидки</label>
                                        <input name="params[size_discount][]" type="text" value="<?=$params['size_discount'][$i];?>">
                                    </div>
                                    <?if($i>0):?>
                                        <a onclick="return deleteField(this)" class="remove-label"><span class="icon-remove"></span></a>
                                    <?endif;?>
                                </div>
                                <?$i++;
                                endforeach;
                            }?>
                            
                            <div class="add-label-button">
                                <a class="add-label-btn js-add"><i class="icon-circle-plus"></i>Добавить уровень</a>
                            </div>
                        </div>
                    </div>
                
                    
                    <div class="hidden" id="promo_calc_discount_box">
                        <div class="width-100">
                            <label>Тип скидки</label>
                            <div class="select-wrap">
                                <select name="discount_type">
                                    <option value="summ"<?if($sale['discount_type'] == 'summ') echo ' selected="selected"';?>>Сумма</option>
                                    <option value="percent"<?if($sale['discount_type'] == 'percent') echo ' selected="selected"';?>>Проценты</option>
                                </select>
                            </div>
                        </div>
                        
                        <p><label>Скидка</label>
                            <input type="text" size="4" value="<?=$sale['discount'];?>" name="discount" placeholder="Размер скидки">
                        </p>
        
                        <div class="width-100">
                            <label>Считать скидку для промокода от</label>
                            <div class="select-wrap">
                                <select name="promo_calc_discount">
                                    <option value="1"<?if($sale['promo_calc_discount'] == '1') echo ' selected="selected"';?>>Базовой цены</option>
                                    <option value="2"<?if($sale['promo_calc_discount'] == '2') echo ' selected="selected"';?>>Красной цены</option>
                                </select>
                            </div>
                        </div>
                        
                        <p><label>Промокод</label>
                            <input type="text" name="promo" value="<?=$sale['promo_code'];?>" placeholder="Промокод">
                        </p>
                        <p><label>Описание</label>
                            <textarea rows="4" cols="45" name="desc"><?=$sale['sale_desc'];?></textarea>
                        </p>

                    </div>
                    <div class="width-100" style="margin-top: 15px;">
                        <label>Начислять партнерские?</label>
                        <div class="select-wrap">
                            <select name="params[usepartnersaccrue]">
                                <option value="1" <?=isset($params['usepartnersaccrue']) && $params['usepartnersaccrue'] == 1 ? 'selected' : ''  ?>>Начислять</option>
                                <option value="0" <?=isset($params['usepartnersaccrue']) && $params['usepartnersaccrue'] != 1 ? 'selected' : ''  ?>>Не начислять</option>
                            </select>
                        </div>
                    </div>
                    <?$extension = System::CheckExtensension('partnership', 1);
                    if($extension):?>
                        <p><label>Привязка к партнёру</label>
                            <input type="text" value="<?=$sale['partner_id'];?>" name="partner_id" placeholder="ID партнёра">
                        </p>
                    <?endif;?>
                </div>
                
                <div class="col-1-2">
                    <p><label>Начало</label>
                        <input type="text" class="datetimepicker" value="<?=date("d.m.Y H:i", $sale['start']);?>" name="start" autocomplete="off">
                    </p>
                    
                    <p><label>Завершение</label>
                        <input type="text" class="datetimepicker" value="<?=date("d.m.Y H:i", $sale['finish']);?>" name="finish" autocomplete="off">
                    </p>

                    <div class="width-100"><label>Действует на товары</label><br />
                        <select name="product[]" multiple="multiple" class="multiple-select" size="10">
                            <?$product_list = Product::getProductListOnlySelect();
                            foreach ($product_list as $product):
                                $products_arr = unserialize($sale['products']);?>
                                <option value="<?=$product['product_id'];?>"<?if($products_arr != null && in_array($product['product_id'], $products_arr)) echo ' selected="selected"';?>><?=$product['product_name'];?></option>
                                <?if ($product['service_name']):?>
                                    <option disabled="disabled" class="service-name">(<?=$product['service_name'];?>)</option>
                                <?endif;
                            endforeach;?>
                        </select>
                        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    </div>
                    <div class="width-100"><label>Действует на категории </label><br />
                        <select name="categories[]" multiple="multiple" class="multiple-select" size="10">
                            <?$categories = Product::getAllCatList();
                            $categories_arr = unserialize($sale['categories']);
                            foreach ($categories as $category) {?>
                                <option value="<?=$category['cat_id'];?>"<?if($categories_arr != null && in_array($category['cat_id'], $categories_arr)) echo ' selected="selected"';?> ><?=$category['cat_name'];?></option>
                            <?}?>
                        </select>

                        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    </div>
                    
                    <?/*<p><label>Время действия, часы (динамическая)</label><input type="text" value="<?=$sale['duration'];?>" name="duration" placeholder="Часы"></p>*/?>

                    <p id="count_uses" class="hidden"><label title="Количество использований (пустое значение - без ограничений)">Количество использований</label>
                        <input type="number" value="<?=$sale['count_uses'];?>" name="count_uses" max="8388607">
                    </p>
                </div>
            </div>
            
            <div id="warning_sale_cart" class="hidden width-100 mt-20">
                <div class="notification-end-plan">
                    <div class="notification-end-plan__icon"><i class="icon-info"></i></div>
                    <div class="notification-end-plan__text">
                        <p><strong>Важно!</strong> Данная акция не взаимодействует с другими акциями</p>
                    </div>
                </div>
            </div>

            <div class="row-line">
                <div class="col-1-1 mb-0">
                    <h4>Статистика по акции</h4>
                </div>
            </div>
            <?$calc_sales = Product::getCountAndSumToOrdersSale($sale['id']);?>
            <div class="row-line mt-20">
                <div class="col-1-1">
                <?if($calc_sales):
                    if(isset($calc_sales[1][0]['count'])): //оплаченные счета?>
                        <p><span class="checked-status"></span> Оплаченных счетов: <a style="text-decoration: none" href="/admin/orders/?segment=segment&condition_type%5B0%5D=sale_id&sale_id%5B0%5D=<?=$sale['id']?>&logic_type%5B0%5D=and&group_index%5B0%5D=0&invert%5B0%5D=0&condition_type%5B1%5D=order_status&order_status%5B1%5D=1&logic_type%5B1%5D=and&group_index%5B1%5D=0&invert%5B1%5D=0&filter=1&groups_data=%7B%220%22%3A%7B%22groups%22%3A%5B%5D%2C%22conditions%22%3A%5B0%2C1%5D%2C%22logic_type%22%3A%22and%22%2C%22index%22%3A0%2C%22invert%22%3A0%7D%7D&all_start_elmnts-1-1665413977=segment%3Dall%26condition_type%255B0%255D%3D%26logic_type%255B0%255D%3D%26group_index%255B0%255D%3D%26invert%255B0%255D%3D%26groups_data%3D&form_is_update-1=1665413981"><?=$calc_sales[1][0]['count'];?> на сумму <?="{$calc_sales[1][0]['summ']} {$setting['currency']}";?></a></p>
                    <?endif;
                    if(isset($calc_sales[0][0]['count'])): //выписанные счета?>
                        <p><span class="icon-stopwatch"></span> Выписанных счетов: <a style="text-decoration: none" href="/admin/orders/?segment=segment&condition_type%5B0%5D=sale_id&sale_id%5B0%5D=<?=$sale['id']?>&logic_type%5B0%5D=and&group_index%5B0%5D=0&invert%5B0%5D=0&condition_type%5B1%5D=order_status&order_status%5B1%5D=0&logic_type%5B1%5D=and&group_index%5B1%5D=0&invert%5B1%5D=0&filter=1&groups_data=%7B%220%22%3A%7B%22groups%22%3A%5B%5D%2C%22conditions%22%3A%5B0%2C1%5D%2C%22logic_type%22%3A%22and%22%2C%22index%22%3A0%2C%22invert%22%3A0%7D%7D&all_start_elmnts-1-1665413977=segment%3Dall%26condition_type%255B0%255D%3D%26logic_type%255B0%255D%3D%26group_index%255B0%255D%3D%26invert%255B0%255D%3D%26groups_data%3D&form_is_update-1=1665413981"><?=$calc_sales[0][0]['count'];?> на сумму <?="{$calc_sales[0][0]['summ']} {$setting['currency']}";?></a></p>
                    <?endif;
                    if(isset($calc_sales[2][0]['count'])): //ждут подтверждения?>
                        <span class="status-close"></span> Ожидают подтверждения:  <a style="text-decoration: none" href="/admin/orders/?segment=segment&condition_type%5B0%5D=sale_id&sale_id%5B0%5D=<?=$sale['id']?>&logic_type%5B0%5D=and&group_index%5B0%5D=0&invert%5B0%5D=0&condition_type%5B1%5D=order_status&order_status%5B1%5D=2&logic_type%5B1%5D=and&group_index%5B1%5D=0&invert%5B1%5D=0&filter=1&groups_data=%7B%220%22%3A%7B%22groups%22%3A%5B%5D%2C%22conditions%22%3A%5B0%2C1%5D%2C%22logic_type%22%3A%22and%22%2C%22index%22%3A0%2C%22invert%22%3A0%7D%7D&all_start_elmnts-1-1665413977=segment%3Dall%26condition_type%255B0%255D%3D%26logic_type%255B0%255D%3D%26group_index%255B0%255D%3D%26invert%255B0%255D%3D%26groups_data%3D&form_is_update-1=1665413981"><?=$calc_sales[2][0]['count'];?> на сумму <?="{$calc_sales[2][0]['summ']} {$setting['currency']}";?></a></p>
                    <?endif;
                    if(isset($calc_sales[9][0]['count'])): //возвраты?>
                        <p><span class="icon-stat-2" style="font-size: 21px;"></span> <a style="text-decoration: none" href="/admin/orders/?segment=segment&condition_type%5B0%5D=sale_id&sale_id%5B0%5D=<?=$sale['id']?>&logic_type%5B0%5D=and&group_index%5B0%5D=0&invert%5B0%5D=0&condition_type%5B1%5D=order_status&order_status%5B1%5D=9&logic_type%5B1%5D=and&group_index%5B1%5D=0&invert%5B1%5D=0&filter=1&groups_data=%7B%220%22%3A%7B%22groups%22%3A%5B%5D%2C%22conditions%22%3A%5B0%2C1%5D%2C%22logic_type%22%3A%22and%22%2C%22index%22%3A0%2C%22invert%22%3A0%7D%7D&all_start_elmnts-1-1665413977=segment%3Dall%26condition_type%255B0%255D%3D%26logic_type%255B0%255D%3D%26group_index%255B0%255D%3D%26invert%255B0%255D%3D%26groups_data%3D&form_is_update-1=1665413981"><?=$calc_sales[9][0]['count'];?> <?=System::addTermination($calc_sales[9][0]['count'], 'возврат[TRMNT]');?> на сумму <?="{$calc_sales[9][0]['summ']} {$setting['currency']}";?></a></p>
                    <?endif;
                else:?>
                    <div class="paid_message">
                    <?if($sale['type'] == 2):?>
                            <p>Промокод еще не использовался</p>
                        <?else:?>
                            <p>По этой акции ещё не было продаж</p>
                        <?endif;?>
                    </div>
                <?endif;?>
                </div>
            </div>
        </div>
    </form>
    <?require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
jQuery('.datetimepicker').datetimepicker({
format:'d.m.Y H:i',
lang:'ru'
});

var button_add = document.querySelector('.js-add');
button_add.addEventListener( 'click', function () {

    var paramslevel = document.querySelector('#params-level');
    var element = document.querySelector('#line-level').cloneNode( true );
    var element_button = document.querySelector('.add-label-button');
    let del_button = document.createElement('a');
    del_button.setAttribute("onclick", "return deleteField(this)");
    del_button.classList.add('remove-label');
    del_button.innerHTML = '<span class="icon-remove"></span>';
    paramslevel.insertBefore(element, element_button);
    element.appendChild(del_button);
    
} );


function deleteField(a) {
    var element_del = a.parentNode;
    element_del.remove();
}

function changelevel(obj) {
    if (obj.value == 'count_prod') {
        var el = document.querySelectorAll('.add-label-title');
        el.forEach(function(element) {
            element.textContent = 'от кол-ва';
        });
    } else {
       var el = document.querySelectorAll('.add-label-title');
       el.forEach(function(element) {
            element.textContent = 'от суммы';
        });
    }
    
}

</script>
</body>
</html>