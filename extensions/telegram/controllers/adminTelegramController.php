<?php defined('BILLINGMASTER') or die;

class adminTelegramController extends AdminBase {

    /**
     * НАСТРОЙКИ TELEGRAM
     */
    public function actionSettings() {
        System::checkPermission('change_users');

        // if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
        //     $data = $_POST['telegram'];
        //     $bot_name = trim($data['params']['bot_name']);
            
        //     if (strpos($bot_name, '@') === 0) {
        //         $bot_name = substr_replace($bot_name, '', 0, 1);
        //         $data['params']['bot_name'] = $bot_name;
        //     }
            
        //     $params = serialize($data);
        //     $status = intval($_POST['status']);
        //     $save = Telegram::saveSettings($params, $status);

        //     if ($save) {
        //         Telegram::addSuccess('Успешно');
        //     }
            
        //     header('Location: /admin/telegramsetting');
        //     exit;
        // }

        // $enable = Telegram::getStatus();
        // $settings = Telegram::getSettings();
        // $params = unserialize($settings);

        // $group_list = User::getUserGroups();

        $service = Connect::getServiceByName('telegram');

        $title='Расширения - настройки Telegram';
        require_once (__DIR__ . '/../views/setting.php');
    }
    
    
    /**
     * СОХРАНИТЬ ВЕБХУКИ
     */
    // public function actionSetWebhook() {
    //     $acl = self::checkAdmin();
    //     $name = $_SESSION['admin_name'];
    //     if (!isset($acl['change_users']) || !isset($_GET['token']) || $_GET['token'] != $_SESSION['admin_token']) {
    //         header("Location: /admin");
    //     }

    //     $main_settings = System::getSetting();
    //     $settings = Telegram::getSettings();
    //     $params = unserialize($settings);
    
    //     $api = new TelegramApi($params['params']['token']);
    //     $url = "{$main_settings['script_url']}/telegram/getupdates";
        
    //     if (strpos($url, 'https') !== 0) {
    //         Telegram::addError('Адрес сайта должен обеспечивать безопасное соединение (https)');
    //     } else {
    //         $res = $api->setWebHook($url);
    //         if ($res['ok'] !== 1) {
    //             Telegram::addSuccess('Успешно');
    //             $params['params']['is_set_webhook'] = 1;
    //             Telegram::saveSettings(serialize($params));
    //         } else {
    //             Telegram::addError($res['description']);
    //         }
    //     }

    //     header('Location: /admin/telegramsetting');
    // }
    
    /**
     * УДАЛИТЬ ВЕБХУКИ
     */
    // public function actionDelWebhook() {
    //     $acl = self::checkAdmin();
    //     $name = $_SESSION['admin_name'];
    //     if (!isset($acl['change_users']) || !isset($_GET['token']) || $_GET['token'] != $_SESSION['admin_token']) {
    //         header("Location: /admin");
    //         exit;
    //     }
        
    //     $main_settings = System::getSetting();
    //     $settings = Telegram::getSettings();
    //     $params = unserialize($settings);
        
    //     $api = new TelegramApi($params['params']['token']);
    //     $res = $api->delWebHook();
        
    //     if ($res['ok'] == 1) {
    //         $params['params']['is_set_webhook'] = 0;
    //         Telegram::saveSettings(serialize($params));
    //         Telegram::addSuccess('Успешно');
    //     } else {
    //         Telegram::addError($res['description']);
    //     }
        
    //     header('Location: /admin/telegramsetting');
    // }
    
    
    /**
     * ПОКАЗАТЬ СПИСОК УЧАСТНИКОВ КАНАЛА
     */
    public function actionMembersList() {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['show_users'])) {
            header("Location: /admin");
            exit;
        }
        
        $members = Telegram::getUsers();
        $title='Расширения - настройки Telegram - список участников канала';
        require_once (__DIR__ . '/../views/members.php');
    }


    /**
     * ПОКАЗАТЬ СПИСОК СОБЫТИЙ
     */
    public function actionLog() {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['show_users'])) {
            System::redirectUrl("/admin");
        }

        if (isset($_GET['reset'])) {
            unset($_SESSION['filter_telegram_log']);
        }

        $filter = isset($_SESSION['filter_telegram_log']) ? $_SESSION['filter_telegram_log'] : null;
        if (isset($_POST['filter'])) {
            $filter = [
                'email' => $_POST['email'] ? htmlentities($_POST['email']) : null,
                'username' => $_POST['username'] ? htmlentities($_POST['username']) : null,
                'sm_user_id' => isset($_POST['sm_user_id']) && $_POST['sm_user_id'] !== '' ? intval($_POST['sm_user_id']) : null,
                'user_id' => isset($_POST['user_id']) && $_POST['user_id'] !== '' ? intval($_POST['user_id']) : null,
                'chat_id' => isset($_POST['chat_id']) && $_POST['chat_id'] ? htmlentities($_POST['chat_id']) : null,
                'event_type' => $_POST['event_type'] ? intval($_POST['event_type']) : null,
                'start' => $_POST['start'] ? strtotime($_POST['start']) : null,
                'finish' => $_POST['finish'] ? strtotime($_POST['finish']) : null,
            ];

            $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;
            if ($filter['is_filter']) {
                $_SESSION['filter_telegram_log'] = $filter;
            }
        }

        $setting = System::getSetting();
        $is_pagination = true;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total = Telegram::getTotalLog($filter);
        $pagination = new Pagination($total, $page, $setting['show_items']);

        $time = time() - 30 * 86400; // получать данные событий за последние 30 дней
        $log_list = Telegram::getLogList($filter, $page, $setting['show_items']);
        $title='Расширения - настройки Telegram - список событий';
        require_once (__DIR__ . '/../views/log.php');
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ СПИСКА УЧАСТНИКОВ
     * @param $sm_user_id
     */
    public function actionDelMember($sm_user_id) {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['change_users']) || !isset($_GET['token']) || $_GET['token'] != $_SESSION['admin_token']) {
            header("Location: /admin");
            exit;
        }

        $del = Telegram::delMember($sm_user_id);
        if ($del) {
            header('Location: /admin/telegramsetting/memberslist?success');
        }
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ИЗ СПИСКА УЧАСТНИКОВ
     */
    public function actionDelStowaways() {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['change_users']) || !isset($_POST['token']) || $_POST['token'] != $_SESSION['admin_token']) {
            exit;
        }

        if (ini_get("max_execution_time") < 180) {
            ini_set("max_execution_time", 180); //увеличить время скрипта
        }
        
        if (isset($_GET['start']) && $_GET['start']) {
            unset($_SESSION['telegram']['del_stowaways']);
        }

        $resp = [
            'msg_error' => false,
            'processed' => isset($_SESSION['telegram']['del_stowaways']['processed']) ? $_SESSION['telegram']['del_stowaways']['processed'] : 0,
            'del_users' => isset($_SESSION['telegram']['del_stowaways']['del_users']) ? $_SESSION['telegram']['del_stowaways']['del_users'] : 0,
            'is_finish' => true,
            'progress' => 0,
        ];

        if (isset($_GET['start'])) {
            $limit = 1;
        } else {
            $limit = $resp['processed'] == 1 ? 4 : 5;
        }

        $total = Telegram::getTotalUsers();
        if (!$total) {
            $resp['msg_error'] = 'Пользователей для удаления из чатов не найдено';
            exit(json_encode($resp));
        }

        $users = Telegram::getUsers($resp['processed'], $limit);
        $del_users = Telegram::delStowaways($users);
        if ($del_users === false && $resp['del_users'] == 0) {
            $resp['msg_error'] = 'Чатов для удаления пользователей не найдено';
            exit(json_encode($resp));
        }

        $resp['processed'] += $limit;
        if ($del_users > 0) {
            $resp['del_users'] += $del_users;
            $_SESSION['telegram']['del_stowaways']['del_users'] = $resp['del_users'];
        }

        if ($resp['processed'] < $total) {
            $_SESSION['telegram']['del_stowaways']['processed'] = $resp['processed'];
            $resp['progress'] = intval($resp['processed'] / $total * 100);
            $resp['is_finish'] = false;
        } else {
            $resp['progress'] = 100;
            $resp['processed'] = $total;
            unset($_SESSION['telegram']['del_stowaways']);
        }

        echo json_encode($resp);
    }


    /**
     * УДАЛИТЬ ПОЛЬЗОВАТЕЛЕЙ ИЗ ЧС
     */
    public function actionRemoveFromBlacklist() {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (!isset($acl['change_users']) || !isset($_POST['token']) || $_POST['token'] != $_SESSION['admin_token']) {
            exit;
        }

        if (ini_get("max_execution_time") < 180) {
            ini_set("max_execution_time", 180); //увеличить время скрипта
        }

        if (isset($_GET['start']) && $_GET['start']) {
            unset($_SESSION['telegram']['del_from_blacklist']);
        }

        $resp = [
            'msg_error' => false,
            'processed' => isset($_SESSION['telegram']['del_from_blacklist']['processed']) ? $_SESSION['telegram']['del_from_blacklist']['processed'] : 0,
            'del_users' => isset($_SESSION['telegram']['del_from_blacklist']['del_users']) ? $_SESSION['telegram']['del_from_blacklist']['del_users'] : 0,
            'is_finish' => true,
            'progress' => 0,
        ];

        if (isset($_GET['start'])) {
            $limit = 1;
        } else {
            $limit = $resp['processed'] == 1 ? 4 : 5;
        }

        $total = Telegram::getTotalUsers();
        if (!$total) {
            $resp['msg_error'] = 'Пользователей для удаления из ЧС не найдено';
            exit(json_encode($resp));
        }

        $users = Telegram::getUsers($resp['processed'], $limit);
        $del_users = Telegram::removeFromBlacklist($users);
        if ($del_users === false && $resp['del_users'] == 0) {
            $resp['msg_error'] = 'Чатов для удаления пользователей из ЧС не найдено ';
            exit(json_encode($resp));
        }

        $resp['processed'] += $limit;
        if ($del_users > 0) {
            $resp['del_users'] += $del_users;
            $_SESSION['telegram']['del_from_blacklist']['del_users'] = $resp['del_users'];
        }

        if ($resp['processed'] < $total) {
            $_SESSION['telegram']['del_from_blacklist']['processed'] = $resp['processed'];
            $resp['progress'] = intval($resp['processed'] / $total * 100);
            $resp['is_finish'] = false;
        } else {
            $resp['progress'] = 100;
            $resp['processed'] = $total;
            unset($_SESSION['telegram']['del_from_blacklist']);
        }

        echo json_encode($resp);
    }
}