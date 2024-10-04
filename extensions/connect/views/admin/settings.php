<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); 
?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<style type="text/css">
    .connect_form .width-100{
        width: 100%;
    }
    .connect_form .width-100 label{
        padding-left: 18px;
    }
    .connect_form .width-100 label.all{
        padding-left: 10px;
        margin-bottom: 12px;
    }
    th.btns{
        display: flex; 
        justify-content: center;
    }
    th.btns a{
       height: 30px;
    }
    th.btns a:not(:last-child){
        margin-right: 10px !important;
    }
    th.btns a img{
        margin: 0;
        height: 25px;
        width: 25px;
    }
    th.btns a:has(> img){
        background: #fff;
        border-color: #000;
        padding: 2.5px;
    }
    .connect_form .superstructure{
        text-decoration: none;
        font-weight: normal;
    }
</style>
<div class="main">
    <div class="top-wrap">
        <h1>Настройки интеграции</h1>
        <div class="logout">
            <a href="/" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/extensions/">Расширения</a></li>
        <li>Настройки интеграции Connect</li>
    </ul>

    <span id="notification_block"></span>

    <form action="" method="POST" class="connect_form">
        <div class="admin_top admin_top-flex connect">
            <div class="admin_top-inner">
                <img src="/template/admin/images/ext/connect.svg" alt="" width="45px" height="45px">
                <h3 class="traning-title mb-0">Connect - интеграции</h3>
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

        <div class="admin_form">
            <div class="row-line">
                <div class="col-1-1 mb-0">
                    <h4 class="h4-border" data-hidden-btn="def_sett" data-autohiding="off">
                        Настройки по умолчанию внутри Connect: 
                        <span class="superstructure">
                            <label class="custom-chekbox-wrap" title="Изменить настройки всех, кто уже пдключил Connect">
                                <input type="checkbox" 
                                    name="set_settings_default" 
                                    value="1"
                                >
                                <span class="custom-chekbox"></span>применить ко всем пользователям
                            </label>
                        </span>
                    </h4>
                </div>

                <? foreach ($services as $id => $service):
                    if(isset($service['types']['hidden']))
                        continue; 
                    $points = 0;
                ?>
                <div class="col-1-2" data-id="def_sett" data-def_sett_service="<?=$service['name']?>">
                    <div class="width-100">
                        <p>
                            <? if($service['status'] == 1): ?>
                            <span class="icon-stat-yes" 
                                title="Сервис включен" 
                            ></span>
                            <? elseif($service['status'] == 0): ?>
                            <span class="icon-info" style="color: #FFCA10;" 
                                title="Сервис отключен" 
                            ></span>
                            <? else: ?>
                            <span class="icon-info" style="color: #E04265;" 
                                title="Сервис не настроен" 
                            ></span>
                            <? endif; ?>

                            <b><?=$service['title']?>:</b>
                        </p>
                        <? if(isset($service['types']['msg']) && ++$points): ?>
                            <label class="custom-chekbox-wrap"
                            <? if($service['status'] != 1 || $service['params']['msg'] != 1) print('title="Включите в сервисе" style="opacity: 0.7;"'); ?>
                            >
                                <input type="checkbox" 
                                    name="params[settings_default][<?=$service['name']?>][msg]" 
                                    value="1"
                                    <? if(@ $params['settings_default'][$service['name']]['msg']) echo ' checked="checked"'; ?>
                                >
                                <span class="custom-chekbox"></span>Дублирование уведомлений
                            </label>
                        <? endif ;?>
                        <? if(isset($service['types']['auth']) && ++$points): ?>
                            <label class="custom-chekbox-wrap"
                            <? if($service['status'] != 1 || $service['params']['auth'] != 1) print('title="Включите в сервисе" style="opacity: 0.7;"'); ?>
                            >
                                <input type="checkbox" 
                                    name="params[settings_default][<?=$service['name']?>][auth]" 
                                    value="1"
                                    <? if(@ $params['settings_default'][$service['name']]['auth']) echo ' checked="checked"'; ?>
                                >
                                <span class="custom-chekbox"></span>Авторизация
                            </label>
                        <? endif ;?>
                    </div>

                    <?if($points < 1): ?>
                    <script type="text/javascript">
                        $('[data-def_sett_service="<?=$service["name"]?>"]').remove();
                    </script>
                    <? endif;?>
                </div>
                <? endforeach; ?>
            </div>

            <div class="row-line">
                <div class="width-100">
                    <h4 class="h4-border" data-hidden-btn="when_msg" data-autohiding="off">
                        Случаи отсылки копий писем: 
                        <?if(empty($params['when_msg'])) echo "<span class='icon-info' style='color: #E04265;' title='Ни один случай не выбран'>";?>
                    </h4>

                    <label class="custom-chekbox-wrap all" style="margin-left: 10px;" data-id="when_msg">
                        <input type="checkbox" data-selectall='chb_w_m'
                            name="params[when_msg][]" 
                            value="all"
                            <? if(in_array('all', $params['when_msg'])) echo ' checked="checked"'; ?>
                        >
                        <span class="custom-chekbox"></span>Выбрать все
                    </label>
                        
                <? foreach (Email::getMsgCases() as $case_key => $case_name): ?>

                    <label class="custom-chekbox-wrap" data-id="when_msg">
                        <input type="checkbox" data-id="chb_w_m"
                            name="params[when_msg][]" 
                            value="<?=$case_key?>"
                            <? if(in_array($case_key, $params['when_msg'])) echo ' checked="checked"'; ?>
                        >
                        <span class="custom-chekbox"></span><?=$case_name?>
                    </label>

                <? endforeach; ?>

                </div>
            </div>
            <script type="text/javascript">
                $('input[data-id]:checkbox').click( function(){
                    id = $(this).data('id');
                    all_count = $('input[data-id="' + id + '"]:checkbox').length;
                    ch_count = $('input[data-id="' + id + '"]:checkbox:checked').length;
                    console.log(ch_count);

                    $('input[data-selectall="' + id + '"]').prop('checked', all_count == ch_count);
                });
                $('input[data-selectall]:checkbox').click(function(){
                    id = $(this).data('selectall');
                    $('input[data-id="' + id + '"]').prop('checked', $(this).is(":checked"));

                });
            </script>
        </div>

         <div class="admin_form admin_form--margin-top">
            <div class="row-line">

            <? if($services): ?>
                <table class="table">
                    <tbody>
                        <tr>
                            <th class="hidden-640px">ID</th>
                            <th>Название</th>
                            <th>Действие</th>
                            <th><span class="hidden-640px">Статус</span></th>
                        </tr>
            <? foreach ($services as $id => $service): 
                if(isset($service['types']['hidden']))
                    continue; 

                $prms = $service;
                unset($prms['params'], $prms['types']); ?>
                    <tr>
                        <th class="hidden-640px">
                            <?=$id?>
                        </th>
                        <th>
                            <?=$service['title']?>
                        </th>

                        <th class="btns">
                            <a href="#edit_<?=$service['name'] . '_' . $id?>" data-uk-modal="{center:true}" class="button-green" style="margin: 10px 0;">Настроить</a>
                            <?if(isset($service['types']['bot'])): ?>
                                <a href="#bot_<?=$service['name'] . '_' . $id?>" data-uk-modal="{center:true}" class="button-green" style="margin: 10px 0;">
                                    <img src="\extensions\connect\web\images\bot.png" width="30px" height="30px">
                                </a>

                            <? 
                                System::modalFormGenerate("bot_{$prms['name']}_{$id}", "/admin/connect/ajax/bot/{$prms['name']}", $prms, 'connect_set');
                            endif;?>
                        </th>

                        <th>
                        <? System::modalFormGenerate("edit_{$prms['name']}_{$id}", "/admin/connect/ajax/{$prms['name']}", $prms, 'connect_set');
                        if($service['status'] == 1): ?>
                            <span class="icon-stat-yes"></span> <span> Сервис включен.</span>
                        <? elseif($service['status'] == 0): ?>
                            <span class="icon-info" style="color: #FFCA10;"></span> <span> Сервис отключен.</span>
                        <? elseif($service['status'] > 1): ?>
                            <span class="icon-info" style="color: #E04265;"></span> <span> Сервис не настроен.</span>
                        <? endif; ?>
                        </th>
                    </tr>
            <? endforeach; ?>
                    </tbody>
                </table>
            <? else: ?>
                <h3>Сервисы не найдены.</h3>
            <? endif; ?>
            </div>
        </div>


        <div class="reference-link">
            <a class="button-blue-rounding" target="_blank" href="https://support.school-master.ru/knowledge_base/item/283416"><i class="icon-info"></i>Справка по расширению</a>
        </div>
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>" class="hide">
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

</body>
</html>