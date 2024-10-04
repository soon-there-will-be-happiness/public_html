<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Список форм</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/products/">Продукты</a>
        </li>
        <li>Формы</li>
    </ul>
    <div class="nav_gorizontal">
        <div class="row-line flex-right">
            <a class="button-red-rounding" href="/admin/products/form">Создать форму</a>
        </div>
    </div>

    <span id="notification_block"></span>

    <div class="admin_form">
        <div class="overflow-container">
                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th class="text-left">Название</th>
                        <th class="td-last"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($forms):
                    foreach($forms as $form):?>
                    <tr>
                        <td><?php echo $form['id'];?></td>
                        <td class="text-left"><a href="<?php echo $setting['script_url'];?>/admin/products/form/edit/<?php echo $form['id'];?>"><?php echo $form['name'];?></a></td>
                        <td class="td-last"><a class="link-delete" onclick="return confirm('Вы уверены?')" href="<?php echo $setting['script_url'];?>/admin/products/form/del/<?php echo $form['id'];?>?token=<?php echo $_SESSION['admin_token'];?>" title="Удалить"><i class="fas fa-times" aria-hidden="true"></i></a></td>
                    </tr>
                    <?php endforeach;
                    endif;?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>