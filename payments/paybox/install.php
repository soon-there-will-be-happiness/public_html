<?php

// Создание папок
$folders = array(
    array('/payments/paybox')
);


// Перемещение файлов имя => куда
$files = array(
    array('/fail.php', 'payments/paybox/fail.php'),
    array('/form.php', 'payments/paybox/form.php'),
    array('/index.html', 'payments/paybox/index.html'),
    array('/params.php', 'payments/paybox/params.php'),
    array('/result.php', 'payments/paybox/result.php'),
    array('/success.php', 'payments/paybox/success.php'),
    array('/paybox.png', 'payments/paybox/paybox.png'),
    array('/paybox.php', 'payments/paybox/paybox.php'),

);

// Для установки в БД
$name = 'paybox';
$title = 'paybox';
$enable = 0;
$type = 'system';
$desc = '<p>Оплата через сервис paybox.</p>';
$params = '';

?>