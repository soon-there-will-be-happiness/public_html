<?php defined('BILLINGMASTER') or die;

$params = unserialize(base64_decode($payment['params']));
?>
<div class="row-line">
    <div class="col-1-2">
        <h4 class="h4-border">Параметры АТОЛ Pay</h4>
        <p><label>Токен Оплаты:</label>
            <input type="text" name="params[token]" value="<?php echo $params['token']; ?>"></p>
        <p><label>URL шлюза:</label>
            <input type="text" name="params[url]" value="<?php echo $params['url']; ?>"></p>
    </div>
    <div class="col-1-2">
        <h4 class="h4-border">Параметры АТОЛ Чека</h4>
        <p><label>URL шлюза:</label>
        <input type="text" name="params[url2]" value="<?php echo $params['url2']; ?>"></p>
        <p><label>Групповой код:</label>
        <input type="text" name="params[group_code]" value="<?php echo $params['group_code']; ?>"></p>
        <p><label>Токен API:</label>
            <input type="text" name="params[token2]" value="<?php echo $params['token2']; ?>"></p>
        <p><label>Логин:</label>
            <input type="text" name="params[login]" value="<?php echo $params['login']; ?>"></p>
        <p><label>Пароль:</label>
            <input type="password" name="params[password]" value="<?php echo $params['password']; ?>"></p>
        <p><label>INN:</label>
            <input type="text" name="params[inn]" value="<?php echo $params['inn']; ?>"></p>
        <p><label>SNO:</label>
            <input type="text" name="params[sno]" value="<?php echo $params['sno']; ?>"></p>
        <p><label>Email компании:</label>
            <input type="text" name="params[email]" value="<?php echo $params['email']; ?>"></p>
        <p><label>Адресс компании:</label>
            <input type="text" name="params[payment_address]" value="<?php echo $params['payment_address']; ?>"></p>
        <p><label>Время обновления токена:</label>
            <input type="date" name="params[token_date]" value="<?php echo $params['token_date']; ?>"></p>
    </div>
</div>
