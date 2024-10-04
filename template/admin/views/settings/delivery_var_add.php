<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Создать способ доставки</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/deliverysettings/">Варианты доставки</a></li>
        <li>Создать способ доставки</li>
    </ul>

    <span id="notification_block"></span>

    <form action="" method="POST">
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title">Создать способ доставки</h3>
            <ul class="nav_button">
                <li>
                    <input type="submit" name="addmethod" value="Создать" class="button save button-white font-bold">
                </li>
                <li class="nav_button__last">
                    <a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/deliverysettings/">Закрыть</a>
                </li>
            </ul>
        </div>
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-2">
                    <h4>Основное</h4>
                    <p class="width-100">
                        <label>Название способа: </label>
                        <input type="text" name="name" placeholder="Название" required="required">
                    </p>

                    <div class="width-100">
                        <label>Статус: </label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio">
                                <input name="status" type="radio" value="1" checked="">
                                <span>Вкл</span>
                            </label>
                            <label class="custom-radio">
                                <input name="status" type="radio" value="0">
                                <span>Откл</span>
                            </label>
                        </span>
                    </div>

                    <p class="width-100">
                        <label>Стоимость:</label>
                        <span class="input-meaning">
                            <input type="text" name="tax" pattern="^[0-9]+$" placeholder="Целое число">
                            <span><?=System::getSetting()['currency']?></span>
                        </span>
                    </p>
                    <div class="width-100">
                        <div class="label">Оплата:</div>
                        <div class="select-wrap">
                            <select name="when_pay">
                                <option value="0">Оплатить сейчас или при получени</option>
                                <option value="1">Только сейчас</option>
                                <option value="2">Только при получении</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="col-1-2">
                    <h4>Дополнительно</h4>

                    <p class="width-100">
                        <label>Описание:</label>
                        <textarea name="ship_desc" rows="3" cols="40"></textarea>
                    </p>

                    <input type="hidden" name="token" value="<?= $_SESSION['admin_token'];?>">
                    <input type="hidden" name="type" value="<?= @$type;?>">
                    <input type="hidden" name="menu_id" value="1">
                    
                </div>
</div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>