<?php defined('BILLINGMASTER') or die;

$params = unserialize(base64_decode($payment['params']));
?>
<div class="row-line">
    <div class="col-1-2">
        <h4 class="h4-border">Параметры АТОЛ Pay</h4>
        <p><label>Токен API:</label>
            <input type="text" name="params[token]" value="<?php echo $params['token']; ?>"></p>
        <p><label>URL возврата:</label>
            <input type="text" name="params[return_url]" value="<?php echo $params['return_url']; ?>"></p>
        <p><label>URL уведомления:</label>
            <input type="text" name="params[notification_url]" value="<?php echo $params['notification_url']; ?>"></p>
    </div>
</div>
