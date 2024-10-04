<? defined('BILLINGMASTER') or die;

$comments = [];
?>
<style type="text/css">
    <? @ require_once __DIR__ . '/../../../web/css/admin_style.css'; ?>
</style>
<div class="admin_form connect_set">
	<h4 class="h4-border">
        <ul class="nav_button close_bar">
            <li class="flex">
                <span class="logotp"></span>
                <h3 class="traning-title mb-0">Настройки <?=$title?></h3>
            </li>
            <li class="nav_button__last">
            	<a class="uk-modal-close uk-close modal-nav_button__close red-link" href="#close">Закрыть</a>
            </li>
        </ul>
    </h4>
    <form method="POST" id="setting_form_<?=$get?>">
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
        <input type="hidden" name="service[id]" value="<?=$id;?>">
        <input type="hidden" name="service[name]" value="<?=$get;?>">
<?
if (isset($service, $iss_perms, $file) && !empty($service) && file_exists($file) && $save_btn = true) {
    require_once $file;
?>

    <h4 class="h4-border">
    </h4>

        <div class="row-line" <?if($service['status'] > 1) echo " style=\"display:none;\"" ?>>
            <div class="col-1-2">
                <div class="width-100" data-id="<?=$service['name']?>-enable">
                    <label>Включить сервис?</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio">
                            <input name="enable" data-id="<?=$service['name']?>-enable-yes" type="radio" value="1"<? if(@ $service['status'] == '1') echo ' checked';?>
                            data-show_on="<?=$service['name']?>-lk_conn,<?=$service['name']?>-auth,<?=$service['name']?>-msg">
                            <span>Вкл</span>
                        </label>
                        <label class="custom-radio">
                            <input name="enable" type="radio" value="0"<? if(@ $service['status'] != '1') echo ' checked';?>
                            data-show_off="<?=$service['name']?>-lk_conn,<?=$service['name']?>-auth,<?=$service['name']?>-msg">
                            <span>Откл</span>
                        </label>
                    </span>
                </div>

                <? if(isset($service['types']['lk_conn'])) :?>
                <div class="width-100" data-id="<?=$service['name']?>-lk_conn">
                    <label>Подключение к <?=$title?> через ЛК клиента:</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio">
                            <input name="params[lk_conn]" type="radio" value="1"<? if(@ $service['params']['lk_conn'] == '1') echo ' checked';?>>
                            <span>Вкл</span>
                        </label>
                        <label class="custom-radio">
                            <input name="params[lk_conn]" type="radio" value="0"<? if(@ $service['params']['lk_conn'] != '1') echo ' checked';?>>
                            <span>Откл</span>
                        </label>
                    </span>
                </div>
                <? endif; ?>

                <? if(isset($service['types']['auth'])) :?>
                <div class="width-100" data-id="<?=$service['name']?>-auth">
                    <label>Авторизация через <?=$title?>:</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio">
                            <input name="params[auth]" type="radio" value="1"<? if(@ $service['params']['auth'] == '1') echo ' checked';?>>
                            <span>Вкл</span>
                        </label>
                        <label class="custom-radio">
                            <input name="params[auth]" type="radio" value="0"<? if(@ $service['params']['auth'] != '1') echo ' checked';?>>
                            <span>Откл</span>
                        </label>
                    </span>
                </div>
                <? endif; ?>

                <? if(isset($service['types']['msg'])) :?>
                <div class="width-100" data-id="<?=$service['name']?>-msg">
                    <label>Дублирование писем в <?=$title?> (при возможности):</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio">
                            <input name="params[msg]" type="radio" value="1" <? if(@ $service['params']['msg'] == '1') echo ' checked';?>>
                            <span>Вкл</span>
                        </label>
                        <label class="custom-radio">
                            <input name="params[msg]" type="radio" value="0" <? if(@ $service['params']['msg'] != '1') echo ' checked';?>>
                            <span>Откл</span>
                        </label>
                    </span>
                </div>
                <? endif; ?>

            </div>

            <div class="col-1-2">

            <? if(isset($service['types']['important'])) :?>
                <div class="width-100">
                    <p>
                        <span class="icon-info" style="color: #FFCA10;"></span>
                        Статус сервиса напрямую взаимосвязан с расширением.
                    </p>
                </div>
            <? endif;?>

            <? if(!empty($comments)): ?>
                <div class="width-100">
                <? foreach ($comments as $comment): ?>
                    <p>
                        <em>
                            <u>Примечание:</u>
                            <?=$comment?>
                        </em>
                    </p>
                <? endforeach; ?>
                </div>
            <? endif; ?>

            </div>
        </div>

    <? if($service['status'] > 1): ?>
        <p>Сервис не настроен.</p>
        <p>Обновите данные</p>
    <? endif;?>
<?
}
else {
    echo "Произошла ошибка.";
}
?>
    	<ul class="nav_button save_bar">
        <? if(@ $save_btn): ?>
            <li>
            	<input type="submit" name="<?=$get?>" value="Сохранить" class="button-green">
            </li>
        <? endif; ?>
        </ul>
    </form>

    <script type="text/javascript">
        $('#setting_form_<?=$get?>').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: '/admin/connect/ajax/submit_form/<?=$id?>/<?=$get?>',
                data: $(this).serialize(),
                success: function(data) {
                    location.reload();
                },
                error: function(data){
                    ntf = new Notification();
                    ntf.addMessage('Ошибка обработки запроса.', 'warning', 5);
                    UIkit.modal('#edit_<?=$name?>_<?=$id?>').hide();
                }
            });
            return false;
        });
    </script>
</div>