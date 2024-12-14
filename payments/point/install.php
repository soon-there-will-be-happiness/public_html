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
array('/point.png', 'payments/point/point.png')

);

// Для установки в БД
$name = 'Точка'; 
$title = 'Точка'; 
$enable = 0;
$type = 'system';
$desc = '<p>Оплата через сервис Точка. Принимаются банковские карты, СПБ.</p>';
$params = '';

?>
