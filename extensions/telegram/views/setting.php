<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Настройки Telegram</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a><a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/extensions/">Расширения</a></li>
        <li>Настройки Telegram</li>
    </ul>

    <span id="notification_block"></span>
    <?php if(Telegram::hasSuccess()) Telegram::showSuccess();?>
    <?php if(Telegram::hasError()) Telegram::showError();?>

    <? 
    $service = Connect::getServiceByName('telegram');

    System::modalFormGenerate("edit_{$service['name']}_{$service['service_id']}", "/admin/connect/ajax/{$service['name']}", 
        [
            'name' => $service['name'], 
            'service_id' => $service['service_id'],
            'title' => $service['title']
        ], 'connect_set'
    );
    ?>

    <form action="" method="POST">
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/nastr-tren.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Настройки Telegram</h3>
                </div>
            </div>

            <ul class="nav_button">
                <li>
                    <!-- <input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"> -->
                    <a href="#edit_<?=$service['name'] . '_' . $service['service_id']?>" data-uk-modal="{center:true}" class="button save button-white font-bold">
                        <p> 
                        <? if($service['status'] == 1): ?>
                            <span class="icon-stat-yes" style="background: #fff; border-radius: 10px; font-size: 19px;"></span>
                        <? elseif($service['status'] == 0): ?>
                            <span class="icon-info" style="font-size: 19px; color: #FFCA10;"></span>
                        <? elseif($service['status'] > 1): ?>
                            <span class="icon-info" style="font-size: 19px; color: #E04265;"></span>
                        <? endif; ?>
                            Настройки
                        </p>
                    </a>
                </li>
                <li class="nav_button__last">
                    <a class="button red-link" href="/admin/extensions/">Закрыть</a>
                </li>
            </ul>
        </div>

            
        <div class="admin_form">
            <center>
                <p style="font-size: 18px; margin-bottom: 20px;">
                    <i>Настройки <u>делегированы</u> в <b>Connect: Telegram</b></i>
                </p>
            </center>
            <div class="row-line">
                <div class="col-1-2">
                    <h4 class="h4-border">Основное</h4>
            
                    <div class="width-100">
                        <a href="javascript:void(0)" name="del_stowaways">Удалить пользоваталей из чатов, у которых недолжно быть к ним доступа</a>
                    </div>

                    <div class="width-100">
                        <a href="javascript:void(0)" name="remove_from_blacklist">Удалить пользоваталей из ЧС, у которых есть доступ</a>
                    </div>

                </div>
                
                <div class="col-1-2">
                    <h4 class="h4-border">Участники</h4>
                    <? /* ?>
                    <div class="width-100"><label>Группы пользователей, для которых выводить ссылку для привязки telegram</label>
                        <select class="multiple-select" size="7" multiple="multiple" name="telegram[params][tg_user_groups][]">
                            <?php if($group_list):
                                foreach($group_list as $user_group):?>
                                    <option value="<?=$user_group['group_id'];?>"<?php if (!empty($params['params']['tg_user_groups']) && in_array($user_group['group_id'], $params['params']['tg_user_groups'])) echo ' selected="selected"';?>><?=$user_group['group_title'];?></option>
                                <?php endforeach;
                            endif;?>
                        </select>
                    </div>
                    <? */ ?>

                    <div class="width-100">
                        <a href="/admin/telegramsetting/memberslist">Cписок участников</a>
                    </div>

                    <div class="width-100">
                        <a href="/admin/telegramsetting/log">Cписок событий</a>
                    </div>
                </div>
            </div>

           
        </div>
        <div class="reference-link">
            <a class="button-blue-rounding" target="_blank" href="https://support.school-master.ru/knowledge_base/item/232110"><i class="icon-info"></i>Справка по расширению</a>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<script src="/extensions/telegram/web/admin/js/main.js?v=<?=CURR_VER;?>"></script>
<?$title = 'Удаление пользоваталей из чатов, у которых недолжно быть к ним доступа';require_once(ROOT . '/lib/progressbar/html.php');?>
</body>
</html>