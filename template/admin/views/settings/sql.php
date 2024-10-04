<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Сделать SQL запрос</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/settings/">Настройки</a>
        </li>
        <li>Сделать SQL запрос</li>
    </ul>
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Сделать SQL запрос</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="get_sql" value="Вперёд" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/settings/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="admin_form">
            <div class="row-line">
                <?php if(isset($result)):?>
                <div class="col-1-1">
                    <h4>Результат</h4>
                    <pre><?=print_r($result);?></pre>
                </div>
                <?php endif;?>
                
                <div class="col-1-2">
                    <h4>SQL</h4>
                    <p class="width-100"><label></label><textarea rows="4" cols="45" name="sql"></textarea></p>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                </div>
                <div class="col-1-2">
                    <h4>Поиск дублей email</h4>
                    <p class="width-100">SELECT `email`, COUNT(`email`) AS `count` FROM `<?=PREFICS;?>users` GROUP BY `email` HAVING `count` > 1</p>
                    
                </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>
</body>
</html>