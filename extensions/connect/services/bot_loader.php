<?php defined('BILLINGMASTER') or die;

if(!isset($name, $updates) || empty($updates))
    ErrorPage::return404();

$config = parse_ini_file('config.ini');

defined('SERVICE_DIR') or define('SERVICE_DIR', __DIR__ . "/{$name}");

$dir = SERVICE_DIR . '/bot';
$controller_file = $dir . '/controller.php';

if(!file_exists($controller_file)) {
    ErrorPage::return404();
}

if (ucfirst($name) == "Telegram") {
    define('CONNECT_TG_BOT', 1);
    require_once (SERVICE_DIR."/tg_loader.php");
}

$class = 'Connect\\' . ucfirst($name). '\bot\Controller';

if(method_exists($class, 'update')){
    $updates = json_decode($updates, true);
    new $class($updates);
}