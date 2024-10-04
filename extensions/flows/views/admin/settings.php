<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<style>
.table-prods:hover {background:#efefef}
</style>
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Настройки потоков</h1>
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
        <li>Настройки потоков</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">
        
        <?php if(isset($_GET['success'])) echo '<div class="admin_message">Успешно!</div>'?>
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Настройки потоков</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="save_flow" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/flows/">Закрыть</a></li>
            </ul>
        </div>
        <div class="admin_form">
            <h4>Потоки</h4>
            <div class="row-line">
                <div class="col-1-2">
                    <div class="width-100"><label>Статус</label>
                        <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="status" type="radio" value="1" <?php if($enable == 1) echo 'checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="status" type="radio" value="0" <?php if($enable == 0) echo 'checked';?>><span>Откл</span></label>
                        </span>
                    </div>
                    <p class="width-100"><label>Название для пользователей для выбора в заказе</label><input type="text" name="params[user_title]" value="<?=$params['user_title'];?>" placeholder="Название потоков" ></p>
                    <p class="width-100"><label>Название для пользователей для показа в заказе</label><input type="text" name="params[order_title]" value="<?if(isset($params['order_title'])) echo $params['order_title'];?>" placeholder="Название потоков" ></p>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                    
                </div>
                <div class="col-1-1">
                    <h4>Задание для крона / <span class="small" style="color: #0772A0;">раз в 30 минут</span></h4>
                    <?php $flow_cron =  System::getCronLog('flow_cron');
                    
                    if (!empty($flow_cron)):?>
                    <p>Последний запуск был: <?=date("d-m-Y H:i:s", $flow_cron['last_run']);?>
                        <?php if($flow_cron['jobs_error'] == 1):?>
                            <div style="color:red"> Есть ошибки!</div></p>
                        <?php endif;?>
                    </p>
                    <?php endif;?>
                    <input type="text" value="php <?php echo ROOT ?>/task/flow_cron.php">
                </div>
            </div>
        </div>
    </form>
    
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>