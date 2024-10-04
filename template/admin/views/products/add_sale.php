<?defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Создать акцию</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?=$setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/products/">Продукты</a></li>
        <li><a href="/admin/sales/">Акции</a></li>
        <li>Создать акцию</li>
    </ul>

    <span id="notification_block"></span>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div><img src="/template/admin/images/icons/hot-sale.png" alt=""></div>
                <div><h3 class="traning-title mb-0">Создать акцию</h3></div>
            </div>

            <ul class="nav_button">
                <li><input type="submit" name="addsale" value="Создать" class="button save button-green-rounding"></li>
                <li class="nav_button__last"><a class="button button-red-rounding" href="<?=$setting['script_url'];?>/admin/sales/">Закрыть</a></li>
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
                    <p class="width-100"><label>Название: </label><input type="text" name="name" placeholder="Название акции" required="required"></p>

                    <div class="width-100">
                        <label>Статус: </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="status" type="radio" value="1" checked=""><span>Вкл</span></label>
                            <label class="custom-radio"><input name="status" type="radio" value="0"><span>Откл</span></label>
                        </span>
                    </div>

                    <div class="width-100">
                        <label>Тип: </label>
                        <div class="select-wrap">
                            <select name="type">
                                <option value="1">Красная цена</option>
                                <option data-show_on="promo_calc_discount_box,count_uses" value="2">Промокод</option>
                                <?if($setting['use_cart'] == '1'):?>
                                    <option data-show_on="promo_cart_box,warning_sale_cart" value="5">Скидка в корзине</option>
                                <?endif;?>
                                <!--option value="3">Динамическая</option-->
                            </select>
                        </div>
                    </div>

                    <div class="hidden" id="promo_calc_discount_box">
                        <div class="width-100">
                            <label>Тип скидки: </label>
                            <div class="select-wrap">
                                <select name="discount_type">
                                    <option value="summ">Сумма</option>
                                    <option value="percent">Проценты</option>
                                </select>
                            </div>
                        </div>
					
                        <p class="width-100"><label>Скидка: </label><input type="text" size="4" name="discount" placeholder="Размер скидки"></p>

                        <div class="width-100">
                            <label>Считать скидку для промокода от:</label>
                            <div class="select-wrap">
                                <select name="promo_calc_discount">
                                    <option value="1">Базовой цены</option>
                                    <option value="2">Красной цены</option>
                                </select>
                            </div>
                        </div>

                        <p class="width-100">
                            <label>Промокод: </label>
                            <input type="text" name="promo" placeholder="Промокод">
                        </p>

                        <div class="width-100">
                            <label>Описание:</label>
                            <textarea rows="4" cols="45" name="desc"></textarea>
                        </div>
                    </div>

                    <div class="hidden params-level" id="promo_cart_box">
               
                        <div class="width-100">
                            <label>Зависит от</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio">
                                    <input name="params[level_type]" type="radio" value="count_prod" onchange="changelevel(this);" checked><span>Кол-ва продуктов</span>
                                </label>
                                <label class="custom-radio">
                                    <input name="params[level_type]" type="radio" value="summ_prod" onchange="changelevel(this);"><span>Суммы продуктов</span>
                                </label>
                            </span>
                        </div>

                        <div class="width-100">
                            <label>Тип скидки: </label>
                            <div class="select-wrap">
                                <select name="params[discount_type]">
                                    <option value="summ">Сумма</option>
                                    <option value="percent">Проценты</option>
                                </select>
                            </div>
                        </div>

                        <div id="params-level" class="width-100">
                            <div id="line-level" class="add-label-row">
                                <div class="add-label-title">от кол-ва</div>
                                <div class="add-label-type">
                                    <label>Уровень</label>
                                    <input name="params[count_or_summ][]" type="text" value="">
                                </div>
                                <div class="add-label-type">
                                    <label title="Скидка будет применена на каждую товарную позицию">Размер скидки</label>
                                    <input name="params[size_discount][]" type="text" value="">
                                </div>
                            </div>
         
                            <div class="add-label-button">
                                <a class="add-label-btn js-add"><i class="icon-circle-plus"></i>Добавить уровень</a>
                            </div>
                        </div>


                    </div>
                    <div class="width-100">
                        <label>Начислять партнерские?</label>
                        <div class="select-wrap">
                            <select name="params[usepartnersaccrue]">
                                <option value="1" selected>Начислять</option>
                                <option value="0">Не начислять</option>
                            </select>
                        </div>
                    </div>
                    <?$extension = System::CheckExtensension('partnership', 1);
                    if($extension):?>
                        <p class="width-100"><label>Привязка к партнёру: </label><input type="text" name="partner_id" placeholder="ID партнёра"></p>
                    <?endif;?>
                    

                </div>

                <div class="col-1-2">
                    <div class="width-100"><label>Начало</label><input type="text" class="datetimepicker" name="start" autocomplete="off"></div>
                    <p class="width-100"><label>Окончание</label><input type="text" class="datetimepicker" name="finish" autocomplete="off"></p>

                    <div class="width-100">
                        <label>Действует на товары:</label>
                        <select class="multiple-select" name="product[]" multiple="multiple" size="10">
                            <?$product_list = Product::getProductListOnlySelect();
                            foreach ($product_list as $product):?>
                                <option value="<?=$product['product_id'];?>"><?=$product['product_name'];?></option>
                                <?if($product['service_name']):?>
                                    <option disabled="disabled" class="service-name">(<?=$product['service_name'];?>)</option>
                                <?endif;
                            endforeach?>
                        </select>
                        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    </div>
                    <div class="width-100" style="margin-bottom: 30px;"><label>Действует на категории </label><br />
                        <select name="categories[]" multiple="multiple" class="multiple-select" size="10">
                            <?$categories = Product::getAllCatList();
                            foreach ($categories as $category) {?>
                                <option value="<?=$category['cat_id'];?>"><?=$category['cat_name'];?></option>
                            <?}?>
                        </select>
                    </div>
                    <!--p><label>Время действия, часы (динамическая): </label><input type="text" name="duration" placeholder="Часы"></p-->

                    <p id="count_uses" class="hidden"><label title="Количество использований (пустое значение - без ограничений)">Количество использований</label>
                        <input type="number" value="" name="count_uses" max="8388607">
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