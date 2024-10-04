<?php define('BILLINGMASTER', 1);

// Настройки системы
require_once (dirname(__FILE__) . '/../components/db.php');
require_once (dirname(__FILE__) . '/../config/config.php');

$root = dirname(__FILE__) . '/../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');

System::enableLongWaitForQueries();
$setting = System::getSetting();
$time = time();
$name_jobs = "cond_cron";

$conditions = Conditions::searchConditions($time);
if ($conditions) {
    foreach ($conditions as $cond) {
        $render = Conditions::renderCond($cond, true);
    }
}

$db = Db::getConnection();  
$sql = "INSERT INTO ".PREFICS."cron_logs(jobs_cron, last_run) VALUES(:name_jobs, :last_run)  ON DUPLICATE KEY UPDATE last_run = :last_run";
$result = $db->prepare($sql);
$result->bindParam(':name_jobs', $name_jobs, PDO::PARAM_STR);
$result->bindParam(':last_run', $time, PDO::PARAM_INT);
$result->execute();

?>

