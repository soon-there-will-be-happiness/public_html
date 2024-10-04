<?php define('BILLINGMASTER', 1);

// Настройки системы
require_once(dirname(__FILE__) . '/../../components/db.php');
require_once(dirname(__FILE__) . '/../../config/config.php');

$root = dirname(__FILE__) . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');

$history_id = isset($_GET['history_id']) ? intval($_GET['history_id']) : null;
$comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : null;
$attach_name = isset($_GET['name']) ? urldecode($_GET['name']) : null;

if (($history_id || $comment_id) && $attach_name) {
    $answer = $history_id ? TrainingLesson::getUserAnswer($history_id) : TrainingLesson::getComment($comment_id);
    if ($answer && !empty($answer['attach'])) {
        $attachments = json_decode($answer['attach'], true);
        foreach ($attachments as $attachment) {
            if ($attach_name == $attachment['name']) {
                $path = ROOT . urldecode($attachment['path']);
                if (file_exists($path)) {
                    System::fileForceDownload($path, $attachment['name']);
                }
            }
        }
    }
}

header('HTTP/1.0 204 No Content');