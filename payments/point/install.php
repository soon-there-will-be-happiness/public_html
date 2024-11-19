<?php 

// Создание папок
$folders = array(
array('/payments/point')
);


// Перемещение файлов имя => куда
$files = array(

array('/form.php', 'payments/point/form.php'),
array('/index.html', 'payments/point/index.html'),
array('/params.php', 'payments/point/params.php'),
array('/result.php', 'payments/point/result.php'),
array('/success.php', 'payments/point/success.php'),
array('/atol.png', 'payments/point/image.png')

);

// Для установки в БД
$name = 'Точка'; 
$title = 'Яндекс Касса'; 
$enable = 0;
$type = 'system';
$desc = '<p>Оплата через сервис Яндекс касса. Принимаются банковский карты, Яндекс.Деньги, Webmoney, QIWI.</p>';
$params = '';

?>