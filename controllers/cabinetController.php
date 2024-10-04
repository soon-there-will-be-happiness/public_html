<?php defined('BILLINGMASTER') or die;

class cabinetController extends baseController {
    
    // ГЛАВНАЯ СТРАНИЦА ЛИЧНОГО КАБИНЕТА
    public function actionIndex()
    {

        if ($this->settings['enable_cabinet'] == 0) {
            ErrorPage::return404();
        }

        $userId = intval(User::checkLogged()); // Проверка авторизации
        $user = User::getUserById($userId); // Данные юзера
        $custom_fields = CustomFields::getFields(CustomFields::PARSE_TYPE_LK);

        if (isset($_POST['update'])) {
            $name = htmlentities($_POST['name']);
            if (strpbrk($name, "'()-$%&!")) {
                ErrorPage::returnError('Не используйте специальные символы', null, 421);
            }

            $phone = htmlentities($_POST['phone']);
            $zipcode = isset($_POST['zipcode']) ? htmlentities($_POST['zipcode']) : null;
            $city = isset($_POST['city']) ? htmlentities($_POST['city']) : null;
            $address = isset($_POST['address']) ? htmlentities($_POST['address']) : null;

            $surname = isset($_POST['surname']) ? htmlentities($_POST['surname']) : null;
            $patronymic = isset($_POST['patronymic']) ? htmlentities($_POST['patronymic']) : null;
            $nick_instagram = isset($_POST['nick_instagram']) ? htmlentities($_POST['nick_instagram']) : null;

            $sex = !empty($_POST['sex']) ? htmlentities($_POST['sex']) : null;
            $day = intval($_POST['bith_day']);
            $month = intval($_POST['bith_month']);
            $year = intval($_POST['bith_year']);

            $upd = User::UpdateUserSelf(
                $userId, $name, $phone, $zipcode, $city, $address, $surname,
                $patronymic, $nick_instagram, $sex, $day, $month, $year
            );

            if ($upd) {
                if ($custom_fields) {
                    $custom_fields_data = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];
                    CustomFields::saveUserFields($userId, null, $custom_fields_data);
                }

                System::redirectUrl('/lk', $upd);
            }
        }

        $this->setSEOParams('Личный кабинет');
        $this->setViewParams('lk', 'users/lk.php', false, null, 'lk-page');

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    // СТРАНИЦА ЗАКАЗОВ В ЛК
    public function actionOrders()
    {
        if ($this->settings['enable_cabinet'] == 0) {
            ErrorPage::return404();
        }

        $params = json_decode($this->settings['params'], true);
        $userId = intval(User::checkLogged()); // Проверка авторизации
        
        // Данные юзера
        $user = User::getUserById($userId);
        $user_groups = User::getGroupByUser($userId);
        $is_paid_orders = !isset($params['show_free_orders_in_lk']) || $params['show_free_orders_in_lk'] ? null : true;
        $orders = Order::getUserOrders($user['email'], 1, $is_paid_orders);
        
        $life_time = $this->settings['order_life_time'] * 86400;
        $now = time();
        $time = $now - $life_time;
        $orders_nopay = Order::getUserNopayOrders($user['email'], $time, $now);
        
        if (isset($_POST['getlink'])) {
            $order_date = intval($_POST['order']);
            
            // Получить массив данных заказа по order_date со статусом 1
            $order = Order::getOrderData($order_date, 1);
            if ($order) {
                $upd = Order::UpdateOrderDwl($order_date, time());
            }
            
            // Вызов метода для отсылки писем клиенту
            $send = Order::getDwlOrder($order, $user_groups);
            System::redirectUrl('/lk/orders', $send);
        }

        $this->setSEOParams('Мои заказы');
        $this->setViewParams('lk', 'users/orders.php',
            false, null, 'my-orders-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    // СМЕНИТЬ ПАРОЛЬ ИЗ ЛИЧНОГО КАБИНЕТА
    public function actionChangepass()
    {
        
        if ($this->settings['enable_cabinet'] == 0) {
            ErrorPage::return404();
        }
        
        // Проверка авторизации
        $userId = intval(User::checkLogged());
        
        // Данные юзера
        $user = User::getUserById($userId);
        
        if (isset($_POST['changepass'])) {
            $pass = $_POST['pass'];
            
            $change = User::ChangePass($userId, $pass);
            if ($change) header("Location: ".$this->settings['script_url']."/lk?success");
        }

        $this->setSEOParams('Сменить пароль');
        $this->setViewParams('lk', 'users/changepass.php', false,
            null, 'changepass-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }


    /**
     * СТРАНИЦА ПОДПИСОК МЕМБЕРШИПА ЮЗЕРА
     */
    public function actionMembership()
    {
        if ($this->settings['enable_cabinet'] == 0) {
            ErrorPage::return404();
        }
        
        $membership = System::CheckExtensension('membership', 1);
        if (!$membership) {
            ErrorPage::return404();
        }

        $now = time();

        // Проверка авторизации
        $userId = intval(User::checkLogged());

        // Данные юзера
        $user = User::getUserById($userId);

        $recurrent = 1; // получить только рекурренты
        $myplanes = Member::getRecurrentPlanesByUser($userId);

        if (isset($_GET['action']) && $_GET['action'] == 'pause') {
            $id = intval($_GET['id']);
            $act = Member::pauseMember($id, 0);
            if ($act) {
                // отправка уведомления
                System::redirectUrl('/lk/membership', true);
            }
        }

        $this->setSEOParams('Мои подписки');
        $this->setViewParams('lk', 'users/membership.php',
            false, null, 'my-subs-page');

        require_once ("{$this->template_path}/main.php");
        return true;
    }
}