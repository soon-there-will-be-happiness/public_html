<? defined('BILLINGMASTER') or die;?>

<div class="row-line">
    <div class="col-1-2">
        <p class="width-100">
            <label>ID сообщества (отправителя) числом</label>
            <input autocomplete="off" type="text" pattern="(\d+)" title="Число" name="service_params[group_id]" value="<?=@ $service['service_params']['group_id'];?>" class="blur">
        </p>
        <p>
            <label>Ключ доступа сообщества</label>
            <input autocomplete="off" type="text" name="service_params[chat_token]" value="<?=@ $service['service_params']['chat_token'];?>" class="blur">
        </p>
        <p class="width-100">
            <label>Версия VK API</label>
            <input autocomplete="off" disabled type="text" pattern="([\d.]+)" title="Число" name="service_params[v]" placeholder="5.130" value="<?=@ $service['service_params']['v'];?>">
        </p>

        <p>
            <? if(@ $service['service_params']['succ_chat'] == 1) :?>
                <span class="icon-stat-yes"></span> Ключ доступа сообщества настроен.
            <? else: ?>
                <span class="icon-info" style="color: #FFCA10;"></span> Проблема с ключем доступа сообщества.
            <? endif; ?>
        </p>

        <p>
            <? if(@ $service['service_params']['succ_app'] == 1) :?>
                <span class="icon-stat-yes"></span> Мини приложение настроено.
            <? else: ?>
                <span class="icon-info" style="color: #FFCA10;"></span> Проблема с мини-приложением.
            <? endif; ?>
        </p>

        <?if(Autopilot::getSettings('status')['status'] == 1): ?>
            <label class="custom-chekbox-wrap" title="Касается пользоватлей из AutoPilot, что не были подключены к Connect">
                <input type="checkbox"
                    name="transfer_autopilot"
                    value="1"
                >
                <span class="custom-chekbox"></span><b>Подключить пользователей из AutoPilot</b>
            </label>
        <? endif; ?>

    </div>

    <div class="col-1-2" id="vk_app_data">
        <p class="width-100">
            <label>ID приложения</label>
            <input autocomplete="off" type="text" pattern="(\d+)" title="Число" name="service_params[app_id]" value="<?=@ $service['service_params']['app_id'];?>" class="blur">
        </p>

        <p class="width-100">
            <label>Защищённый ключ</label>
            <input autocomplete="off" type="text" name="service_params[secret]" value="<?=@ $service['service_params']['secret'];?>" class="blur">
        </p>

        <p class="width-100">
            <label>Сервисный ключ доступа</label>
            <input autocomplete="off" type="text" name="service_params[service_key]" value="<?=@ $service['service_params']['service_key'];?>" class="blur">
        </p>

        <? /* <p class="width-100 <?php if(@ $service['service_params']['succ_app'] != 1) echo 'hidden';?>">
            <label>Права доступа приложения (список <a href="https://vk.com/dev.php?method=permissions" target="_blank">прав</a> через запятую)</label>
            <input autocomplete="off" type="text" name="service_params[scope]" value="<?=@ $service['service_params']['scope'];?>">
        </p> */ ?>

        <?php if(@ $service['service_params']['succ_app'] == 1):?>
            <p class="width-100"><label>Приложение ВКонтакте:</label>
                <a href="https://vk.com/editapp?id=<?=@ $service['service_params']['app_id'];?>&section=options" rel="noopener noreferrer" target="_blank" style="margin-top: 5px;display: inline-block;"> Открыть настройки приложения VK</a>
            </p>
        <?php endif; ?>
    </div>
    <input type="hidden" name="params[response_type]" value="<?=@ $service['params']['response_type'];?>">
</div>

<?
$comments[] = "При выпуске ключа сообщества, не забудьте 'Разрешить приложению доступ к управлению сообществом'.";
$comments[] = "В настройках приложения необходимо установить Доверенный redirect URI: "
    . "<input style='width: 100%; font-weight: bold; border:0; outline: none;' value="
    . \System::getSetting()['script_url'] . \Connect::getServiceClass('vkontakte')::$script_url
    . " onClick='this.select();'>";
?>