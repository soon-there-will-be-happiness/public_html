<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Добавить статус</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/orders">Заказы</a>
        </li>
        <li>
            <a href="/admin/settings/crmstatus">Статусы для менеджеров</a>
        </li>
        <li>Добавить статус</li>
    </ul>
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Добавить статус</h3>
                    <p class="mt-0">для менеджера</p>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="addstatus" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/settings/crmstatus/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 mb-0"><h4>Основное</h4></div>
                <div class="col-1-2">
                    <p class="width-100"><label>Название: </label><input type="text" name="title" placeholder="Название статуса" required="required"></p>
                    <p class="width-100"><label>Описание:</label><textarea rows="4" cols="45" name="status_desc"></textarea></p>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>
                <div class="col-1-2">
                    
                </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>