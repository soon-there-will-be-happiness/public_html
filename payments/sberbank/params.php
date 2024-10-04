<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));
$host = System::getSetting()['script_url'];
?>
<div class="row-line">
    <div class="col-1-2">
        <h4 class="h4-border">Параметры</h4>

        <p><label>Логин</label>
            <input type="text" name="params[login]" value="<?= @$params['login'];?>">
        </p>

        <p><label>Пароль</label>
            <input type="text" name="params[password]" value="<?= @$params['password'];?>">
        </p>
    </div>
    <div class="col-1-1">
        <h4 class="h4-border">Токен callback</h4>
        <p>
            <span>
                Нужен для обновления статуса заказа, когда он был успешно оплачен.<br>
                Чтобы его получить, нужно перейти в ЛК сбербанка, настройки, пункт "callback-уведомления". Включить их.<br>
            </span>
            <div class="margin-bottom-15">
                Выставить настройки:<br>
                Ссылка: <?= $host ?>/payments/sberbank/result.php<br>
                HTTP-метод: GET<br>
                Тип callback-a: Статический<br>
                Тип подписи: Симметричный<br>
                Callback-токен: сгенерировать и вставить в поле "callback token"<br>
                Операции: поставить только "успешное списание"<br>
                Сохранить настройки
            </div>
            <label>Callback-токен</label>
            <input type="text" name="params[key]" value="<?= @$params['key'] ?>" placeholder="callback token">
        </p>
    </div>
</div>
