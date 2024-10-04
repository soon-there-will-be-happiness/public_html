<?php defined('BILLINGMASTER') or die;

$dir = __DIR__;

$filesToLoad = [
    $dir."/api/methods.php",
    $dir."/api/src/medias.php",
    $dir."/api/src/main.php",
    $dir."/api/src/keyboard.php",

    $dir."/bot/src/commandhandler.php",
    $dir."/bot/src/mainFunctions.php",
    $dir."/bot/src/telegram.php",
    $dir."/bot/src/updatehandler.php",
    $dir."/bot/controller.php",
];

foreach ($filesToLoad as $file) {
    require_once $file;
}
