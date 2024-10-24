<?php defined('BILLINGMASTER') or die;

$params = unserialize(base64_decode($payment['params']));
?>

<p><label>Shop_ID</label><br />
<input type="text" name="params[ya_shop_id]" value="<?php echo $params['ya_shop_id'];?>"></p>

<p><label>SCID:</label><br />
<input type="text" name="params[ya_scid]" value="<?php echo $params['ya_scid'];?>"></p>

<p><label>API Token:</label><br />
<input type="text" name="params[api_token]" value="<?php echo $params['api_token'];?>"></p>

<p><label>Password:</label><br />
<input type="text" name="params[pass]" value="<?php echo $params['pass'];?>">
</p>
