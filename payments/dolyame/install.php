<?php 

$folders = array(
	array('/payments/dolyame'),

	array('/payments/dolyame/lib'),
	array('/payments/dolyame/lib/methods'),
	
	array('/payments/dolyame/files')
);

$files = array(
	array('/bill_create.php', 'payments/dolyame/bill_create.php'),
	array('/dolyame.png', 'payments/dolyame/dolyame.png'),
	array('/fail.php', 'payments/dolyame/fail.php'),
	array('/form.php', 'payments/dolyame/form.php'),
	array('/index.html', 'payments/dolyame/index.html'),
	array('/params.php', 'payments/dolyame/params.php'),
	array('/refund_modal.php', 'payments/dolyame/refund_modal.php'),
	array('/result.php', 'payments/dolyame/result.php'),
	array('/success.php', 'payments/dolyame/success.php'),

	array('/lib/ajax.php', 'payments/dolyame/lib/ajax.php'),
	array('/lib/main.php', 'payments/dolyame/lib/main.php'),
	array('/lib/sm_fns.php', 'payments/dolyame/lib/sm_fns.php'),
	array('/lib/webhook.php', 'payments/dolyame/lib/webhook.php'),

	array('/lib/methods/confirm.php', 'payments/dolyame/lib/methods/confirm.php'),
	array('/lib/methods/get.php', 'payments/dolyame/lib/methods/get.php'),
	array('/lib/methods/refund.php', 'payments/dolyame/lib/methods/refund.php'),
);

$name = 'dolyame'; 
$title = 'Долями от Тинькофф'; 
$enable = 0;
$type = 'system';
$desc = '<p>Оплата через сервис "Долями" от Тинькофф.</p>';
$params = '';

?>