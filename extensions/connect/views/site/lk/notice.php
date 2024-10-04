<? defined('BILLINGMASTER') or die;
?>

<div class="alert-message" data-id="notice_conn_<?=$service['name']?>">
    <div class="alert-message-left">
        <h4>Важно для получения доступа!</h4>
        <div class="alert-message-text">
            В купленных программах Вам будет предоставлен доступ <nobr>к группе</nobr>
            в <?=$service['title']?>. Чтобы система Вас случайно <nobr>не выкинула</nobr> <nobr>из группы,</nobr>
            привяжите аккаунт <?=$service['title']?> к вашему профилю. И тогда это сообщение исчезнет.
        </div>
    </div>
    
    <div class="alert-message-right">
        <a href="javascript:void(0);" 
            data-connect="<?=$service['name']?>"
            data-connect-attach_text="<?=System::Lang('BIND');?>" 
            data-set_elmnt_id="notice_conn_<?=$service['name']?>"
            class="btn getlink btn-red button_right"
        >
            <span class="loading_text-ani"></span>
        </a>
    </div>
</div>
