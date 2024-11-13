<?php defined('BILLINGMASTER') or die;

$params = unserialize(base64_decode($payment['params']));
?>
<div class="row-line">
    <div class="col-1-2">
        <h4 class="h4-border">Параметры АТОЛ Pay</h4>
        <p><label>Токен API:</label>
            <input type="text" name="params[token]" value="<?php echo $params['token']; ?>"></p>
        <p><label>URL шлюза:</label>
            <input type="text" name="params[url]" value="<?php echo $params['url']; ?>"></p>
        <p><label>Логин:</label>
            <input type="text" name="params[login]" value="<?php echo $params['login']; ?>"></p>
        <p><label>Пароль:</label>
            <input type="password" name="params[password]" value="<?php echo $params['password']; ?>"></p>
    </div>
</div>
