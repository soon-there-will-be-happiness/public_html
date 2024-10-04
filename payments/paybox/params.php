<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));?>
<div class="row-line">
    <div class="col-1-2">
        <h4 class="h4-border">Параметры</h4>

        <p><label>pg_merchant_id</label>
            <input type="text" name="params[pg_merchant_id]" value="<?= @$params['pg_merchant_id'];?>">
        </p>

        <p><label>paybox_merchant_secret</label>
            <input type="text" name="params[paybox_merchant_secret]" value="<?= @$params['paybox_merchant_secret'];?>">
        </p>

    </div>
</div>
