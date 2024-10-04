<? defined('BILLINGMASTER') or die;?>

<? if(@ $service['service_params']['username'] && ($login = $service['service_params']['username'])); ?>
    
<div class="row-line">
    <div class="col-1-2">
        <div class="width-100">
            <label title="API токен, выданный при создании бота">API Токен:</label>
            <input type="text" required name="service_params[token]" 
                value="<?=@ $service['service_params']['token'];?>" 
                <?= " style=\"border-color: " . (isset($login) ? "green" : "red") . ";\""?>
            >
        </div>

        <?php if(!empty($service['params']['tg_user_groups']) && is_array($service['params']['tg_user_groups'])):
            foreach ($service['params']['tg_user_groups'] as $value): ?>
                <input type="hidden" name="params[tg_user_groups][]" value="<?=$value?>">
        <?php endforeach;
        endif;?>

        <? if(isset($login)): ?>
        <div class="width-100">
            <label title="Логин Telegram бота">Логин бота: <a href="//t.me/<?=$login?>">@<?=$login?></a></label>
        </div>
        <? endif; ?>
    </div>

    <div class="col-1-2">
        <div class="width-100">
            <br>
            <p>
                <span class="icon-info" style="color: #FFCA10;"></span>
                Во время работы сервиса используется WebHook бота.
            </p>
        </div>
    </div>
</div>
<? 
$comments[] = "Для качественной проверки наличия 'зайцев' в чате, включите боту доступ к сообщениям бесед"; 
?>