<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Настройки интеграции с Автопилотом</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/extensions/">Расширения</a>
        </li>
        <li>Настройки интеграции с Автопилотом</li>
    </ul>

    <span id="notification_block"></span>
    
    <? 
    $service = Connect::getServiceByName('vkontakte');

    System::modalFormGenerate("edit_{$service['name']}_{$service['service_id']}", "/admin/connect/ajax/{$service['name']}", 
        [
            'name' => $service['name'], 
            'service_id' => $service['service_id'],
            'title' => $service['title']
        ], 'connect_set'
    );
    ?>

    <form action="" method="POST">
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>" class="hide">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/ext/autopilot.svg" alt="" width="45px" height="45px">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Интеграция с Автопилотом</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li>
                    <input type="submit" name="save" value="Сохранить" class="button save button-white font-bold">
                </li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/extensions/">Закрыть</a>
                </li>
            </ul>
        </div>

        <?php if(isset($_GET['success'])):?>
            <div class="admin_message">Успешно!</div>
        <?php endif;?>

        <div class="admin_form">

            <center>
                <p style="font-size: 18px; margin-bottom: 20px;">
                    <i>Некоторые настройки <u>делегированы</u> в <b>
                        <a href="#edit_<?=$service['name'] . '_' . $service['service_id']?>" data-uk-modal="{center:true}">Connect: ВКонтакте </a>
                    </b></i>
                </p>
            </center>
            <h4 class="h4-border">Управление расширением</h4>
            <div class="row-line">
                <div class="col-1-2">
                    <p class="width-100">
                        <label>Включить расширение (должно быть настроено)</label>
                        <span class="custom-radio-wrap" style="min-height: 31px;margin-top: 13px;">
                            <label class="custom-radio">
                                <input name="status" type="radio" value="1" <?php if($status == 1) echo 'checked';?>><span>Вкл</span>
                            </label>
                            <label class="custom-radio">
                                <input name="status" type="radio" value="0" <?php if($status != 1) echo 'checked';?>><span>Откл</span>
                            </label>
                        </span>
                    </p>
                </div>

                <div class="col-1-2">
                    <p class="width-100"> </p>
                </div>
            </div>

           

            <h4 class="h4-border" style="margin-top: 40px;">Интеграция с Автопилотом</h4>
            <div class="row-line">
                <div class="col-1-2">
                    <p class="width-100"><label>URL-адрес для запросов:</label>
                        <input type="text" disabled="" value="<?=$setting['script_url'];?>/autopilot/api" >
                    </p>

                    <p class="width-100">
                        <a href="/admin/settings/" rel="noopener noreferrer" target="_blank">Настроить поля интеграции</a>
                    </p>
                </div>

                <div class="col-1-2">
                    <p class="width-100">
                        <label>Ключ API:</label>
                        <input type="text" disabled="" value="<?=$setting['secret_key'];?>" class="blur">
                    </p>

                </div>
            </div>

            <?php if (isset($service['group_id']) && $service['group_id']): ?>
            <div class="row-line">
                <p class="width-100">
                    <a href="https://skyauto.me/groups/edit/<?=$service['group_id'];?>#input-sm_url" rel="noopener noreferrer" target="_blank">
                        Открыть настройки Автопилота
                    </a>
                </p>
            </div>
            <?php endif ?>

        </div>

        <div class="reference-link">
            <a class="button-blue-rounding" target="_blank" href="https://support.school-master.ru/knowledge_base/item/232127">
                <i class="icon-info"></i>Справка по расширению
            </a>
        </div>
    </form>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>

</div>

</body>
</html>