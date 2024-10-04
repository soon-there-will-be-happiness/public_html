<?php define('BILLINGMASTER', 1);

if(!isset($_GET['method']) || !file_exists(__DIR__ . "/methods/{$_GET['method']}.php")){
	ANSWER(false, 'Not found method');
}

define('PAYMENT_DIR', __DIR__ . '/..');

session_start();
// Настройки системы
require_once(PAYMENT_DIR . '/../../components/db.php');
require_once(PAYMENT_DIR . '/../../config/config.php');

$root = PAYMENT_DIR . '/../../';
define('ROOT', $root);
define("PREFICS", $prefics);

require_once (ROOT . '/components/autoload.php');

if(!isset($_SESSION["admin_token"], $_POST['token'])
    || $_POST['token'] != $_SESSION['admin_token']
)
   ANSWER(false, 'No access', ['log' => $_POST['token'] . "   =    " . $_SESSION['admin_token']]);

if(!isset($_POST['show']) && (empty(@ $_POST['order_id']) || !is_numeric($_POST['order_id'])))
    ANSWER(false, 'Error params: order_id');

require_once __DIR__ . '/main.php';

$order_id = isset($_POST['order_id']) ? (int) trim($_POST['order_id']) : null;

$payment_name = 'dolyame';
$dolyame = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($dolyame['params']));

$api = new Dolyame_functions($params);

$order = $order_id ? Order::getOrder($order_id) : [];



require_once __DIR__ . "/methods/{$_GET['method']}.php";






function ANSWER(bool $status = true, string $comment = '', array $data = []): void{
    $result = [
        'status' => $status ? 'success' : 'error'
    ];

    if(!empty($comment))
        $result['comment'] = $comment;

    if(!empty($data))
        $result['result'] = $data;

    header('Content-Type: application/json');

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
    exit();

}