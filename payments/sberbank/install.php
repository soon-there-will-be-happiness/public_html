<?php

// Создание папок
$folders = array(
    array('/payments/sberbank')
);


// Перемещение файлов имя => куда
$files = array(
    array('/createorder.php', 'payments/sberbank/createorder.php'),
    array('/fail.php', 'payments/sberbank/fail.php'),
    array('/form.php', 'payments/sberbank/form.php'),
    array('/params.php', 'payments/sberbank/params.php'),
    array('/result.php', 'payments/sberbank/result.php'),
    array('/success.php', 'payments/sberbank/success.php'),
    array('/sberbank.png', 'payments/sberbank/sberbank.png'),

    array('/load.php', 'payments/sberbank/load.php'),
    array('/sberbank.php', 'payments/sberbank/sberbank.php'),
);

// Для установки в БД
$name = 'sberbank';
$title = 'Сбербанк';
$enable = 0;
$type = 'system';
$desc = '<p>Оплата через сбербанк эквайринг.</p>';
$params = '';

?>