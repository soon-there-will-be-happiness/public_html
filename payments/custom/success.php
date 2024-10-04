<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($custom_data['params']));

$title = 'Спасибо!';
$h2 = System::Lang('CUSTOM_SUCCESS_THANK');
$html = $params['thanks'];
require_once (ROOT.'/payments/success.php');