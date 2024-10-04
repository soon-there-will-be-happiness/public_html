<?php define('BILLINGMASTER', 1);

// Настройки системы
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');
require_once (ROOT . '/vendor/autoload.php');
System::enableLongWaitForQueries();

$settings = System::getSetting(true);

if (!isset($settings['params']['emaillogs_compress']) || $settings['params']['emaillogs_compress'] == 0) {
    die("\n".'   Сжатие логов выключено. Включите в настройках'."\n");
}

$time = time();


if (in_array('--inflate', $argv)) {
    echo "\n"."Запуск разжатия логов"."\n";
    $inflate = true;
} else {
    echo "\n"."Запуск сжатия логов"."\n";
    $inflate = false;
}

$showitems = 100;
if (in_array('--nolimit', $argv)) {
    $msg = "Будет выполнена эта операция над всеми записями"."\n";
    $nolimit = true;
} else {
    $msg = "Будет выполнена эта операция над $showitems записями. Следующий запуск - следующие $showitems записей"."\n";
    $nolimit = false;
}


sleep(1);

//сохранять номер стр в файле
if (file_exists( ROOT.'/task/pageForDeflate.php')) {
    $page = require (ROOT.'/task/pageForDeflate.php');
} else {
    $page = 1;
}

$log = Email::getLog($page, $showitems, true, false, null, null, null, $nolimit);

if ($log && is_array($log)) {
    $count = count($log);
} else {
    die("Сжатие завершено, отключите задание крона!\n");
}
echo "$msg Выполняется заход № $page";

if ($log) {
    $i = 1;
    foreach ($log as $note) {
        $emailLog = Email::getLogData($note['id']);
        if (!$emailLog) {
            continue;
        }
        if (!$inflate) {
            $letterDeflated = gzdeflate($emailLog['letter'], 9);
            $letterDeflated = utf8_encode($letterDeflated);
        } else {
            $letterDeflated = $emailLog['letter'];
        }

        $res = Email::updateLogLetter($note['id'], $letterDeflated);
        if (!$res) {
            echo 'Ошибка вставки в бд'."\n";
        }

        //echo "\r".$page." * ".$count;
        $i++;
    }
    $page++;
    file_put_contents(ROOT.'/task/pageForDeflate.php', "<?php".PHP_EOL. 'return ' . var_export($page, true) . ";".PHP_EOL);
}