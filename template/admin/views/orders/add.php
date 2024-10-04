<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('CREATE_ORDER');?></h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a><a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/orders/">Заказы</a>
        </li>
        <li>Создать заказ</li>
    </ul>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/zakaz.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0"><?=System::Lang('CREATE_ORDER');?></h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="add" value="<?=System::Lang('SAVE');?>" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?=$setting['script_url'];?>/admin/orders/"><?=System::Lang('CLOSE');?></a></li>
            </ul>
        </div>

        <span id="notification_block"></span>

        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-2">
                    <h4><?=System::Lang('BASIC');?></h4>
                    <p class="width-100"><label><?=System::Lang('CLIENT_NAME');?>:</label>
                        <input type="text" name="name" value="<?=isset($_GET['name']) ? $_GET['name'] : '';?>">
                    </p>

                    <p class="width-100"><label>Email:</label>
                        <input type="text" name="email" value="<?=isset($_GET['email']) ? $_GET['email'] : '';?>">
                    </p>

                    <p class="width-100"><label><?=System::Lang('CLIENT_PHONE');?>:</label>
                        <input type="text" name="phone" value="<?=isset($_GET['phone']) ? $_GET['phone'] : '';?>">
                    </p>

                    <p class="width-100"><label><?=System::Lang('CITY');?>:</label>
                        <input type="text" name="city" value="<?=isset($_GET['city']) ? $_GET['city'] : '';?>">
                    </p>

                    <p class="width-100"><label><?=System::Lang('POSTCODE');?>:</label>
                        <input type="text" name="index" value="<?=isset($_GET['index']) ? $_GET['index'] : '';?>">
                    </p>

                    <p class="width-100"><label><?=System::Lang('ADDRESS');?>:</label>
                        <textarea cols="40" rows="2" name="address"><?=isset($_GET['address']) ? $_GET['address'] : '';?></textarea>
                    </p>

                    <div class="width-100"><label><?=System::Lang('STATUS');?>:</label>
                        <div class="select-wrap">
                            <select name="status" required>
                                <option value="">-- Выберите --</option>
                                <option value="1"><?=System::Lang('PAID');?></option>
                                <option value="0"><?=System::Lang('NOT_PAID');?></option>
                                <option value="2"><?=System::Lang('VERIFY');?></option>
                                <option value="7"><?=System::Lang('CLIENT_CONFIRM');?></option>
                                <option value="9"><?=System::Lang('REFUND');?></option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                </div>

                <div class="col-1-2">
                    <div class="round-block">
                        <p class="width-100"><strong><?=System::Lang('PARTNER');?>:</strong> нет</p>
                        <p><strong>IP:</strong> ---</p>
                        <p class="width-100"><label><?=System::Lang('ADMIN_COMMENT');?>:</label><textarea cols="55" rows="2" name="admin_comment"></textarea></p>
                    </div>
                </div>

                <div class="col-1-1">
                    <h4><?=System::Lang('ORDER_CONTENT');?></h4>
                    <div class="width-100">
                        <select class="multiple-select" name="order_items[]" multiple="multiple" size="10">
                            <?php $products = Product::getProductListOnlySelect();
                            foreach($products as $product):?>
                                <option value="<?=$product['product_id']?>"><?php if(!empty($product['service_name'])) echo $product['service_name']; else echo $product['product_name'];?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="width-100"><label>Цена для продуктов в заказе:</label>
                        <div class="select-wrap">
                            <select name="price">
                                <option value="1">Обычная цена</option>
                                <option value="0">Цена со скидкой</option>
                                <option value="3">1 <?=$setting['currency'];?></option>
                                <option value="2">Нулевая</option>
                                <option value="4">Разделить цену заказа на все продукты поровну</option>
                            </select>
                        </div>
                    </div>

                    <div class="width-100"><label>Общая сумма заказа:</label>
                        <input type="text" required="required" name="summ">
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>