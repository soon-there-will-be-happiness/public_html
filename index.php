<?php define('BILLINGMASTER', 1);

define('START', microtime(1));
define('CURR_VER', '3.9.9');


// Настройки
require_once 'setting.php';

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

// Assuming the .env file is in the root directory
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

ini_set('display_errors', $setting['debug_mode']);
error_reporting(E_ALL);

// Получение UTM меток
$utm = System::getUtm($_GET);
if ($utm) {
    $_SESSION['current_utm'] = $utm;
    $search = Stat::searchChannel($utm); // возрврат channel_id
    if ($search) {
        $utm = $search;
    }
}


// Проверка Куков
if(!isset($_COOKIE["$cookie"])){
    
    if(isset($_SERVER['HTTP_REFERER'])) $refer = htmlentities($_SERVER['HTTP_REFERER']);
    else $refer = 0; 
    
    $req = htmlentities($_SERVER["REQUEST_URI"]);
    
    $enter = time();
    
    // Параметры визита: время 1 входа + рефер + utm метки + url страницы входа
    $visit_param = $enter . ';'. $refer . ';'. $utm . ';'.$req;

    setcookie("$cookie", $visit_param, time()+3600*24*30*12, '/', $domain);
    // Дублируем в сессию
    $_SESSION["$cookie"] = $visit_param;
}


// ЕСЛИ В URL ЕСТЬ ПАРТНЁРСКИЙ ID
if($partnership = System::CheckExtensension('partnership', 1)) {
    if (isset($_GET['pr'])) {

        $partner_id = intval($_GET['pr']);

        // ПРОВЕРИТЬ НАЛИЧИЕ ПАРТНЁРА В СИСТЕМЕ + ЗАПИСЬ ПЕРЕХОДА В СТАТИСТИКУ
        $verify = Aff::AffHits($partner_id);
        if ($verify) {

            // НАСТРОЙКИ Партнёрки
            $aff_set = unserialize($partnership['params']);
            $aff_life = intval($aff_set['params']['aff_life']);
            if (empty($aff_life)) $aff_life = 365;

            // Если учитывается последний кто сделал продажу
            if ($aff_set['params']['real_partner'] == 0) {

                // ЕСЛИ КУКА УЖЕ ЕСТЬ, ТО СТАРТАНУТЬ СПЕЦ. СЕССИЮ
                if (isset($_COOKIE["aff_$cookie"])) {
                    $_SESSION["aff_$cookie"] = $partner_id;
                } else {

                    // ЕСЛИ КУКИ НЕТ, ТО ЗАПИСАТЬ КУКУ И СЕССИЮ
                    setcookie("aff_$cookie", $partner_id, time() + 3600 * 24 * $aff_life, '/', $domain, false, true);
                    $_SESSION["aff_$cookie"] = $partner_id; // Дублируем в сессию
                }

            } else {
                // Если учитываем первого кто привёл
                if (!isset($_COOKIE["aff_$cookie"])) {
                    setcookie("aff_$cookie", $partner_id, time() + 3600 * 24 * $aff_life, '/', $domain, false, true);
                    $_SESSION["aff_$cookie"] = $partner_id; // Дублируем в сессию
                } else {
                    $_SESSION["aff_$cookie"] = intval($_COOKIE["aff_$cookie"]);
                }
            }
        }
    } else {
        if (isset($_COOKIE["aff_$cookie"])){
            $_SESSION["aff_$cookie"] = intval($_COOKIE["aff_$cookie"]);
        }
    }
}

if (!empty($_POST)) {
    if (strpos($_SERVER['REQUEST_URI'], '/admin') !== 0 && !isset($_SESSION['admin_token']) && isset($_SESSION['user_token'])) {
        if (isset($_POST['token']) || isset($_GET['token'])) {
            $user_token = isset($_POST['token']) ? $_POST['token'] : $_GET['token'];
        } elseif(isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $user_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if (!isset($user_token) || $user_token != $_SESSION['user_token']) {
            System::redirectUrl($_SERVER['REQUEST_URI']);
        }
    }
    
    if (isset($_POST['phone']) && isset($_POST['phone_code'])) {
        $phone = preg_replace("/[-\s]/", "", $_POST['phone']);
        $_POST['phone'] = $_POST['phone_code'] . $phone;
    }
}

if (strpos($_SERVER['REQUEST_URI'], '/admin') !== 0) {
    $setting = System::getSetting();

    if (isset($_COOKIE['sm_remember_me']) && !User::isAuth()) {
        Remember::userAuth($setting);
    }

    if (!$setting['multiple_authorizations'] && UserSession::userLogOut() && $_SERVER['REQUEST_URI'] !== '/') {
        System::redirectUrl('/');
    }

    if(!isset($_SESSION['connect_token'])){
        $connect_token = time() . ':' . System::generateStr(32);
        $_SESSION['connect_token'] = $connect_token;
    }
}
if (isset($_SESSION['registered_by_connect']) && $_SESSION['registered_by_connect'] == true) {
    unset($_SESSION['registered_by_connect']);
    System::redirectUrl("/lk?registered_by_connect");
}

$off = 0;
if (function_exists('xhprof_enable') && $off != 0) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}
// Вызов Роутера
$router = new Router();
$router->run();

if (function_exists('xhprof_disable') && $off != 0) {
    $result = xhprof_disable();
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $result['serverdata'] = ['date' => time(), 'url' => $url];
    file_put_contents('profilinglogs/file', json_encode($result) . "\n", FILE_APPEND);
}