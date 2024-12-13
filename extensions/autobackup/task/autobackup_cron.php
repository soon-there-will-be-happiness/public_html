<?php define('BILLINGMASTER', 1);
$max_exec_time = 14400;//4 часа
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
    $error = $errstr ." в файле: ". $errfile . ":" . $errline;
    echo "\n Ошибка:".$error."\n";

    Log::add(4, "Возникла ошибка при бэкапе" , [
        "num" => $errno,
        "text" => $errstr,
        "file" => $errfile,
        "line" => $errline,
    ], type: "autobackup");
});

//php C:\OpenServer\domains\smdemo.ru\extensions\autobackup\task\autobackup_cron.php

// Записать в кронлог информацию о запуске/ошибке
BackupCronHandler::writeStartupOrErrorInfoToCronlog($autobackupExtName, $STARTTIME);

BackupCronHandler::init($argv, $max_exec_time);


$taskid = BackupCronHandler::$taskid;
$uidForTask = BackupCronHandler::$uid;
$isSmart = BackupCronHandler::$isSmart;

$extSettings = BackupCronHandler::getExtSettings();

$part_size = BackupCronHandler::getPartSize($extSettings);


$tasks = BackupCronHandler::getTasks($taskid, $isSmart);


if (empty($tasks)) {
    die();
}

foreach ($tasks as $task) {
    BackupWorker::showInfo("=================================="."\n\n"."ЗАПУСК ЗАДАНИЯ: ".$task['name']."\n");

    try {

        BackupProgress::progress($task, STARTTIME, $uidForTask ?? null);

        //Обновить запись о задании
        $nextAction = BackupTables::getNextActionTime(STARTTIME, $task);
        BackupTables::updateTask($task['id'], STARTTIME,$nextAction);

        $backup = new BackupData(
            $task['bd_enable'],
            $task['files_enable'],
            $task['clients_enable'],
            json_decode($task['folders_include'], true),
            json_decode($task['folders_exclude'], true),
            json_decode($task['files_exclude'], true),
            $task['id'],
            $task['divide_to_parts'],
            $part_size,
            json_decode($task['selected_storages'], true)
        );

        $backup->run();

        BackupProgress::progress()->finishTask();

        if ($taskid) {
            $mess = "Задание {$taskid} \"{$task['name']}\" было выполнено вручную";
            Log::add(1, $mess, [], "autobackup");
        }

    } catch (Exception $exception) {
        echo $mess = $exception->getMessage();
        BackupProgress::progress()->finishTask(1, $mess);

        $errorMsg = "Ошибка выполнения задания id: ".$task['id']."- ".$task['name']."<br>Текст ошибки: $mess";
        BackupCronHandler::sendMessage($extSettings, $errorMsg, "Ошибка. Отчёт по резервному копированию ".BackupWorker::getDomainName(null, ""));

        # Залогировать критическую ошибку
        Log::add(5,$errorMsg, ["caught_exception" => $exception, "task" => $task], "autobackup");
        $result = Db::getConnection()->prepare("INSERT INTO " . PREFICS . "cron_logs(`jobs_cron`, `last_run`, `text_error`) VALUES(:name_jobs, :last_run, :error)  ON DUPLICATE KEY UPDATE `last_run` = :last_run, `text_error` = :error");
        $result->bindParam(':name_jobs', $autobackupExtName, PDO::PARAM_STR);
        $result->bindParam(':last_run', $STARTTIME, PDO::PARAM_INT);
        $result->bindParam(':error', $mess, PDO::PARAM_STR);

        $result->execute();
    }
}

# удалить старые копии бекапов
$copiesToRemove = BackupRemover::getCopiesToRemove();
$removeStatus = BackupRemover::removeCopies($copiesToRemove);

$copiesToRemove = BackupRemover::getCopiesToRemove(true);
$removeStatus = BackupRemover::removeCopies($copiesToRemove);

if (!$uidForTask) {
    BackupTables::clearTmp();
}
if (!isset($errorMsg)) {
    BackupCronHandler::sendMessage($extSettings, BackupWorker::getLogAsString(), "Успешно. Отчёт по резервному копированию " . BackupWorker::getDomainName(null, ""));
}