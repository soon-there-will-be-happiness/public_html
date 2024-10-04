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

$now = time();
$name_jobs = "flow_cron";

$setting = System::getSetting();

// Найти необработанные записи

$start_list = Flows::searchInFlowMap(0, $now);
if($start_list){
    
    foreach($start_list as $map_item){
    
        // меняем статус в карте на 1
        Flows::updateMapStatus($map_item['map_id'], 1);
    
        // добавляем группы и т.д.   
        Flows::getFlowStartActions($map_item);
        
    }
}


// Найти завершённые потоки
$finish_list = Flows::searchInFlowMap(1, $now);
if($finish_list){
    
    foreach($finish_list as $finish_item){
    // меняем статус на 8 - завершён
    Flows::updateMapStatus($finish_item['map_id'], 8);
    
    // удаляем группы и т.д.
    Flows::getFlowFinishActions($finish_item);
    
    }
}

$db = Db::getConnection();  
$sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run) VALUES(:name_jobs, :last_run) ON DUPLICATE KEY UPDATE last_run = :last_run";
$result = $db->prepare($sql);
$result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
$result->bindParam(':last_run', $now, PDO::PARAM_INT);
$result->execute();
