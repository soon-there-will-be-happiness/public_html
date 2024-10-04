<?php define('BILLINGMASTER', 1);
$max_exec_time = 7200;//2 часа
ini_set('max_execution_time', $max_exec_time);
ini_set('default_charset', 	"UTF-8");
mb_internal_encoding('UTF-8');

$rootDir = dirname(__FILE__, 4);
require_once($rootDir . '/components/db.php');
require_once($rootDir . '/config/config.php');

$root = $rootDir;
define('ROOT', $root);
define("PREFICS", $prefics);
ini_set('display_errors', 1);

define("STARTTIME", time());
$STARTTIME = STARTTIME;

//Загрузка классов
require_once (ROOT . '/components/autoload.php');
require_once (ROOT . "/extensions/autobackup/config/autobackup_class_loader.php");
$autobackupExtName = "autobackup";

set_error_handler(function ($errno, $errstr, $errfile, $errline) {

    echo "\n".$errstr."\n";

    Log::add(4, "Возникла ошибка при восстановлении бэкапа" , [
        "num" => $errno,
        "text" => $errstr,
        "file" => $errfile,
        "line" => $errline,
    ], "autobackup");
});


try {
    $restoreHandler = new RestoreCronHandler($max_exec_time);
    if (RestoreCronHandler::$task_id == 0) {
        $task = SmartBackup::getSmartTask(0);
    } else {
        $task = BackupTables::getTaskById(RestoreCronHandler::$task_id) ?? [];
    }

    RestoreProgress::progress($task,$STARTTIME, RestoreCronHandler::$uid);

    $restoreHandler->validateArgs();
    $restoreHandler->restore();

    RestoreProgress::progress()->finishTask();

} catch (Exception $exception) {
    RestoreProgress::progress()
       ->finishTask(true, $exception->getMessage());
}

RestoreProgress::unsetObj();


