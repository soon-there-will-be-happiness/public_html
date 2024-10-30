<?php 

// Создание папок
$folders = array(
array('/payments/atol')
);


// Перемещение файлов имя => куда
$files = array(
array('/fail.php', 'payments/atol/fail.php'),
array('/form.php', 'payments/atol/form.php'),
array('/index.html', 'payments/atol/index.html'),
array('/params.php', 'payments/atol/params.php'),
array('/result.php', 'payments/atol/result.php'),
array('/success.php', 'payments/atol/success.php'),
array('/atol.png', 'payments/atol/atol.png')

);

// Для установки в БД
$name = 'atol'; 
$title = 'Яндекс Касса'; 
$enable = 0;
$type = 'system';
$desc = '<p>Оплата через сервис Яндекс касса. Принимаются банковский карты, Яндекс.Деньги, Webmoney, QIWI.</p>';
$params = '';

?>