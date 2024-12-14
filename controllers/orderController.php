<?php defined('BILLINGMASTER') or die;

class orderController extends baseController {


    /**
     * orderController constructor.
     */
    public function __construct() {
        parent::__construct();
        $error_page = new ErrorPage();
        ErrorPage::setPage('pay-error-page');
    }


    // НАЧАЛО ОФОРМЛЕНИЯ ЗАКАЗА
    public function actionBuy($id)
    {
        $id = intval($id);
        if ($this->settings['enable_sale'] == 0) {
            ErrorPage::returnError('Продажи закрыты');
        }

        $product = Product::getProductById($id);
        if (!$product) {
            ErrorPage::return404();
        }

        if ($product['status'] == 0 || $product['status'] == 9) {
            ErrorPage::returnError('Продажи закрыты');
        }

        if (!is_null($product['product_amt']) && $product['product_amt'] == 0) { // Доступность по количеству
            ErrorPage::getSelf()->show_top_menu = false;
            ErrorPage::returnError('Продукт закончился');
        }
        
        $date = time();
        Product::checkProductAvailableToUser($product, null, false, true);
        $name = $user_email = $phone = $surname = $patronymic = null;
        
        // ПОТОКИ для продукта
        $flows_ext = System::CheckExtensension('learning_flows', 1);
        if($flows_ext) {
            $params = json_decode(System::getExtensionSetting('learning_flows'), true);
            $flow_ids = Flows::getFlowForProduct($id);
            if($flow_ids){
                $flows = Flows::getActualFlowByIDs($flow_ids, $date);
                
                // Если нет актуальных потоков для продукта
                if(!$flows) {
                    // можно отправить уведомление админу что продукт есть, а потоков для него нет
                    ErrorPage::returnError('Продажи приостановлены');   
                } else {
                    
                    // Если поток один, то подсчитать кол-во свободных мест
                    if(count($flows) == 1){
                        $flow = Flows::getFlowByID($flows[0]['flow_id']);
                        $count_limit = Flows::countOrdersFromFlowID($flows[0]['flow_id']);
                        if($flow['limit_users'] > 0){
                            if($count_limit >= $flow['limit_users']) ErrorPage::returnError('Извините. Места закончились');    
                        }
                    }
                }
            }
        }

        $currency_list = false;
        if (is_string($this->settings['params'])) {
            $this->settings['params'] = json_decode($this->settings['params'], true);
        }
        if (isset($this->settings['params']['many_currency']) && $this->settings['params']['many_currency'] == 1) {
            $currency_list = Currency::getCurrencyList();
        }


        $user_email = !empty($_REQUEST['email']) ? System::checkemaildomain(htmlentities(trim(strtolower(mb_substr($_REQUEST['email'], 0, 50)),"'"))) : null;
        if ($product['sell_once']) { // продавать продукт один раз
            $user_id = User::isAuth();
            if ($user_id || $user_email) {
                $order = Order::getOrderByProductId2User($id, $user_id, $user_email);
                if ($order) {
                    ErrorPage::returnError('Товар можно купить только один раз');
                }
            }
        }

        $cookie = $this->settings['cookie']; // Получаем имя для куки
        $related_products = Product::getRelatedProductsByID($id, 1); // Получить данные сопутствующих продуктов
		$subs_id = isset($_GET['subs_id']) ? intval($_GET['subs_id']) : 0; // Продление мембершипа по map_id
		
		// Разделение фин.потока
        $org_id = Organization::getOrgByProduct($id); // получаем ID организации

        // Промо код
        if (isset($_POST['apply_promo']) && isset($_POST['promo']) && !empty($_POST['promo'])) {
            $promo_code = htmlentities(trim($_POST['promo']));

            if (!isset($_SESSION['promo_code']) || $_SESSION['promo_code'] != $promo_code) {
                $sale = Product::getSaleByPromoCode($promo_code);
                if ($sale && ($sale['count_uses'] === null || $sale['count_uses'] > 0)) {
                    if ($sale['count_uses'] > 0) {
                        Product::updSaleCountUses($sale['id'], $sale['count_uses'] - 1);
                    }
                    $_SESSION['promo_code'] = $promo_code;
                }
            }
        }


        $price = Price::getFinalPrice($id);

        $partner_id_promocode = $price['partner_id'];
        $use_partner = $price['usepartner'] ?? true;
        $nds_price = Price::getNDSPrice($price['real_price']);

        $custom_fields = CustomFields::getFields(CustomFields::PARSE_TYPE_ORDER);

        // Если нажата кнопка оформить заказ
        if (isset($_POST['buy']) && !empty($_POST['email']) && isset($_POST['time']) && isset($_POST['token'])) {
            $sign = md5($id.'s+m'.$_POST['time']);
            $id_promo=$_POST['promo'];
            
            if( $id_promo!=null){
                 $partner_id_promocode = $id_promo;
            }
            if ($date - intval($_POST['time']) < 2) {
                ErrorPage::returnError('Error 913');
            }

            if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                ErrorPage::returnError("E-mail адрес '$user_email' указан неверно.");
            }

            if (!User::isAuth()) {
                $checkUser = User::getUserIDatEmail($user_email);
                if (!$checkUser) {
                    $checkUser = "no-user";
                }
                Product::checkProductAvailableToUser($product, null, $checkUser);
            }

            // Тут переопределяем поле, если есть в запросе инпут со своей ценой
            if (isset($_POST['user_price']) && !empty($_POST['user_price']) && isset($product['price_minmax'])) {
                $price_min = explode(":", $product['price_minmax'])[0];
                $price_max = explode(":", $product['price_minmax'])[1];

                if (intval($_POST['user_price']) > intval($price_max) && !empty($price_max)) {
                    $price['real_price'] = intval($price_max);
                } elseif (intval($_POST['user_price']) < intval($price_min)) {
                    $price['real_price'] = intval($price_min);
                } else {
                    $price['real_price'] = intval($_POST['user_price']);
                }
            }

            $name = trim(htmlentities(mb_substr($_REQUEST['name'], 0, 255)));

            if (strpbrk($name, "'()$%&!")) {
                ErrorPage::returnError("Не используйте спец.символы");
            }
            if (strpos($name, 'script') || stripos($name, 'select')) {
                ErrorPage::returnError("R Tape loading error");
            }

            $phone = isset($_POST['phone']) ? htmlentities(mb_substr($_POST['phone'],0,25)) : null;
            if ($phone) {
                $phone = '+'.preg_replace("/[^\d]/", "", $phone);
            }

            $surname = isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : null;
            $patronymic = isset($_POST['patronymic']) ? htmlspecialchars($_POST['patronymic']) : null;
            $nick_telegram = isset($_POST['nick_telegram']) ? htmlspecialchars($_POST['nick_telegram']) : null;
            $nick_instagram = isset($_POST['nick_instagram']) ? htmlspecialchars($_POST['nick_instagram']) : null;
            $vk_page = isset($_POST['vk_page']) ? htmlspecialchars($_POST['vk_page']) : null;
            $vk_id = isset($_REQUEST['vk_id']) ? $_REQUEST['vk_id'] : null;

            $index = isset($_POST['index']) ? htmlspecialchars(mb_substr($_POST['index'],0, 8)) : null;
            $city = isset($_POST['city']) ? htmlentities(mb_substr($_POST['city'],0,50)) : null;
            $address = isset($_POST['address']) ? htmlentities(mb_substr($_POST['address'],0,255)) : null;
            $comment = isset($_POST['comment']) ? htmlentities($_POST['comment']) : null;

            $param = isset($_COOKIE["$cookie"]) ? htmlentities($_COOKIE["$cookie"]) : htmlentities($_SESSION["$cookie"]);
            $ip = System::getUserIp();
            $flow = isset($_POST['flows']) ? intval($_POST['flows']) : 0;
            $not_me = isset($_POST['not-me']) ? htmlspecialchars($_POST['not-me']) : false;

          
            // ПАРТНЁРКА ПРИ ЗАКАЗЕ
            $partner_id = null;
            if ($partner_id_promocode && $use_partner) {//если партнер из промокода и опция в акции "Начислять партнерские" включена
                $verify = Aff::PartnerVerify($partner_id_promocode);
                $partner_id = $verify && $verify['email'] != $user_email ? $partner_id_promocode : null;
            } elseif ($use_partner) {
                $partner_id = System::getPartnerId($user_email, $cookie);
                if($partner_id){
                    $verify = Aff::PartnerVerify($partner_id);
                    $partner_id = $verify && $verify['email'] != $user_email ? $partner_id : null;
                }
            }

            // КОРРЕКТИРОВКА СПЛИТ ТЕСТА для продуктов комплектаций
            $cookie_split = $this->settings['cookie'].'_split'; // Сформировали имя куки
            $var = null;

            if ($product['base_id'] != 0) { // если продукт - это комплектация основного
                $base_id = $product['base_id']; // Получить ID базового

                // Проверить куку и забрать значение
                if (isset($_COOKIE["$cookie_split"])) {
                    $cookie_arr = json_decode($_COOKIE["$cookie_split"], true);

                    if (array_key_exists($base_id, $cookie_arr)) {
                        $var = intval($cookie_arr["$base_id"]); // вариант описания

                        // Создать новую куку ID = вариант
                        $cookie_arr[$base_id] = $var;
                        setcookie("$cookie_split", json_encode($cookie_arr), time()+3600*24*30*12, '/');
                    }
                }
            } else {
                if (isset($_COOKIE["$cookie_split"])) {
                    $cookie_arr = json_decode($_COOKIE["$cookie_split"], true);
                    if (array_key_exists($id, $cookie_arr)) {
                        $var = intval($cookie_arr["$id"]); // вариант описания
                    }
                } else {
                    $var = null;
                }
            }

            // Запись заказа в БД
            $status = $base_id = 0;
            $sale_id = $price['sale_id'];
            $current_utm = System::getUtm();
            $nds_price = Price::getNDSPrice($price['real_price']);
            $from = 1;

            while (Order::checkOrderDate($date)) {
                $date += 1;
            }

            $add_order_id = Order::addOrder($id, $nds_price['price'], $nds_price['nds'], $name, $user_email,
                $phone, $index, $city, $address, $comment, $param, $partner_id, $date, $sale_id, $status,
                $base_id, $var, $product['type_id'], $product['product_name'], $ip, 0, $surname,
                $patronymic, $nick_telegram, $nick_instagram, 0, $current_utm, 0,
                $subs_id, $flow, $org_id, $from, $vk_id, $vk_page
            );

            if ($add_order_id) {
                if ($custom_fields) {
                    $custom_fields_data = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];
                    CustomFields::saveUserFields($this->user_id, $user_email, $custom_fields_data,
                        CustomFields::PARSE_TYPE_ORDER, $add_order_id
                    );
                }

                if (isset($_SESSION['promo_code'])) {
                    unset($_SESSION['promo_code']);
                }

                $order = Order::getOrder($add_order_id);
                if($not_me){
                    ToChild::addToChild($order['product_id'],$order['order_id'],$order['client_email']);
                }
                $domain = Helper::getDomain();
                $expire = $this->settings['order_life_time'] * 86400 + $date;
                setcookie("cl_eml", $user_email, $expire, '/', $domain);

                $client = User::getUserDataByEmail($user_email, null); // получаем данные клиента, если он есть.

                OrderTask::addTask($order['order_id'], OrderTask::STAGE_ACC_STAT); // добавление задач для крона по заказу

                // Если есть куки с именем и емейлом
                if (!isset($_COOKIE['emnam'])) {
                    $emnam = $user_email . '='.$name . '='.$phone;
                    setcookie('emnam', $emnam, time()+3600*24*30*3, '/');
                }

                //Отправка писем, по адресам которые указаны в настройках
                $emailsToSent = System::getEmailsToAccountStatementIfItIsEnabled();
                if ($emailsToSent) {
                    foreach ($emailsToSent as $email) {
                        $email = trim($email);
                        if ($email == "") {
                            continue;
                        }
                        $to_child=ToChild::searchByOrderId($order['order_id']);
                        $is_child_attached = $to_child !== false;
                        // +KEMSTAT-8
                        $product = Product::getProductDataForSendOrder($order['product_id']);
                        Email::sendMessageAccountStatement($email, $order['order_id'], $order['client_name'], $surname?$surname:'', $order['product_id'], $product['product_name'], $order['client_email'], $order['client_phone'], $order['order_date'], $nds_price['price'],  $is_child_attached);
                        // -KEMSTAT-8
                        
                    }
                }
            
                // Если у продукта есть апселл
                if ($product['upsell_1'] != 0) {
                    if ($product['type_id'] == 2) {
                        $_SESSION["delivery_$date"] = 1; // Запуск сессии для доставки
                    }
                    
                    $_SESSION["upsell_$date"] = 1; // Запуск сессии для идентификации апселла
                    System::redirectUrl("/offer/$date");
                } else {
                    if ($related_products) {
                        System::redirectUrl("/related/$date");
                    } else { 
                        if ($product['type_id'] == 2) {
                            $_SESSION["delivery_$date"] = 1;
                            System::redirectUrl("/delivery/$date");
                        } else {
                            setcookie("cookie_name", "cookie_value", time() + 3600, "/"); // Куки будет действовать 1 час

                            System::redirectUrl("/pay/$date");
                        }
                    }
                }
            }
        }
        
        $title_text = System::Lang('ORDER_REGISTRATION');

        $this->setSEOParams("$title_text {$product['product_title']}", $product['meta_desc'], $product['meta_keys']);
        $this->setViewParams('order', 'order/buy.php', null, null, 'order-by-page');

        require_once ("{$this->template_path}/main2.php");
        return true;
    }
    
	
	
	// ДОСРОЧНАЯ ОПЛАТА ЗАКАЗА ПО РАССРОЧКЕ
    public function actionAheadOrder($map_id)
    {
        $map_id = intval($map_id);
        $now = time();
        $userId = intval(User::checkLogged());
        $map_item = Order::getInstallmentMapData($map_id);

        // Создаём заказ для рассрочки
        if ($map_item['notif'] == 0) {
            // считаем сколько уже заплачено
            $pay_actions = unserialize(base64_decode($map_item['pay_actions']));
            $pay_summ = $count = 0;

            foreach ($pay_actions as $action) {
                $pay_summ = $pay_summ + $action['summ'];
                $count++;
            }

            $pay_periods = $map_item['max_periods'] - $count;
            $summ_to_pay = ($map_item['summ'] - $pay_summ) / $pay_periods;
            $new_order = Order::createNewOrderFromInstallment($map_item['order_id'], round($summ_to_pay),
                $map_item['email'], $now, $map_item['id'], $map_item['installment_id']
            );

            if ($new_order) {
                $comment = "Очередной платёж по рассрочке с ID ".$map_item['id'];
                $upd = Order::updateAdminCommentByOrder($new_order, $comment, $map_item['id']);

                $order = Order::getOrderDataByID($new_order, 5);
                $upd_notif = Order::updateNotifCount($map_item['id'], 1, $order['order_date']);

                if ($upd_notif) {
                    System::redirectUrl("/pay/{$order['order_date']}");
                }
            }
        }
        return true;
    }



    // ДОСРОЧНОЕ ПОГАШЕНИЕ РАССРОЧКИ
    public function actionAhead($id)
    {
        $id = intval($id);
        $now = time();
        $map_item = Order::getInstallmentMapData($id); // получить данные договора рассрочки
        $installment_data = Product::getInstallmentData($map_item['installment_id']); // получить настройки рассрочки

        // Если это первая попытка оплатить досрочно
        if ($map_item['ahead_id'] == 0) {
            $pay_actions = unserialize(base64_decode($map_item['pay_actions']));
            $pay_sum = 0;
            foreach ($pay_actions as $action) {
                $pay_sum += $action['summ'];
            }

            $total = $map_item['summ'] - $pay_sum;
            $new_order = Order::createNewOrderFromInstallment($map_item['order_id'], $total, $map_item['email'], $now, $id); // Создать заказ

            if ($new_order) {
                $update_map = Order::updateMapFromAhead($id, $new_order, "Досрочное погашение ID $id");
                System::redirectUrl("/pay/".$now);
            }
        } else {
            $order_data = Order::getOrderDataByID($map_item['ahead_id'], 5);
            $order_life_time = $this->settings['order_life_time'] * 86400;
            $expire_date = $order_data['expire_date'] > 0 ? $order_data['expire_date'] : $order_data['order_date'] + $order_life_time;
            
            if ($now < $expire_date) {
                System::redirectUrl("/pay/{$order_data['order_date']}");
            } else {
                // Удалем данные из ahead_id в карте рассрочек
                $update_map = Order::updateMapFromAhead($id, 0, null, 1);
            }
        }
    }



    // СОПУТСТВУЮЩИЕ ТОВАРЫ ИЛИ КОРЗИНА 2
    public function actionRelated($order_date)
    {
        $now = time();
        $cookie = $this->settings['cookie'];
        $tax = 0;
        $added_array = $rel_array = [];

        // Получить данные заказа
        $order = Order::getOrderData($order_date, 0);
        $life_time = $this->settings['order_life_time'] * 86400;
        $expire_date = $order['expire_date'] > 0 ? $order['expire_date'] : $order['order_date'] + $life_time;

        if ($now < $expire_date) {
            // Данные заказа по order_date
            $order_date = intval($order_date);
            $currency_list = false;

            if (isset($this->settings['params']['many_currency']) && $this->settings['params']['many_currency'] == 1) {
                $currency_list = Currency::getCurrencyList();
            }

            // Если продукт добавлен к заказу
            if (isset($_POST['add_offer'])) {
                $offer_id = intval($_POST['offer_id']);
                $related = Product::getRelatedItemByID($offer_id);

                if ($related) {
                    $product_data = Product::getProductById($related['product_id']);
                    $price = Price::getNDSPrice($related['price']);
                    
                    $update = Order::UpdateOrderAfterUpsell($order_date, $related['product_id'], $price['price'], $price['nds'], $product_data['type_id'], $product_data['product_name']);
                    if ($update) {
                        System::redirectUrl("/related/$order_date");
                    }
                } else {
                    ErrorPage::returnError("Ошибка при добавлении продукта");
                }
            }

            // УДАЛИТЬ ДАННЫЕ ИЗ ЗАКАЗА
            if (isset($_REQUEST['delete_item'])) {
                $item = intval($_REQUEST['item_id']);
                $del = Order::deleteOrderItem($order['order_id'], $item);
                if ($del) {
                    System::redirectUrl("/related/$order_date");
                }
            }

			if ($order) {
                // Получить данные сопутствующих продуктов
                $related_products = Product::getRelatedProductsByID($order['product_id'], 1);
                if (!$related_products) {
                    System::redirectUrl("/");
                }

                $product = Product::getProductById($order['product_id']);
                $order_items = Order::getOrderItems($order['order_id']);
			} else {
                ErrorPage::returnError("Не удалось получить данные заказа");
            }

            $total = 0;
            // Тут array_search может вернуть и 0, но это должно быть true! По этому тут такая констуркция. 
            $need_delivery = array_search(2, array_column($order_items, 'type_id')) !== false ? true : false;
            if ($need_delivery) {
                $_SESSION["delivery_$order_date"] = 1;
            }

            foreach ($order_items as $item) {
                $total = $total + $item['price'];
            }

            //Защита email и телефона в заказе
            $hide_cl_email = Order::isHideClientEmail($this->settings, $order);

            $this->setSEOParams('Корзина');
            $this->setViewParams('order', 'order/related.php', null, null, 'related-page');

            require_once ("{$this->template_path}/main2.php");
        } else {
            exit('Время заказа истекло');
        }
        return true;
    }


    /**
     * АПСЕЛЛЫ
     * @param $id
     * @return bool
     */
    public function actionOffer($id)
    {
        if (isset($_SESSION["upsell_$id"])) { // ПРОВЕРКА СЕССИИ
            $id = intval($id);
            if ($this->settings['enable_sale'] == 0) {
                exit('Продажи закрыты');
            }

            $order = Order::getOrderData($id, 0); // Получить данные заказа
            if (!$order) {
                ErrorPage::returnError("Заказ не найден");
            }

            $product = Product::getProductUpsellData($order['product_id']); // Получить ID апсельных продуктов
            $step = isset($_POST['upsell']) && isset($_POST['step']) ? (int)$_POST['step'] : 1;

            if (isset($_POST['upsell'])) {
                if ($_POST['result'] == 1) {
                    $upsell = Product::getProductData($product["upsell_{$step}"]); // Получаем цену и тип продукта
                    $price = !empty($product["upsell_{$step}_price"]) ? $product["upsell_{$step}_price"] : $upsell['price'];
                    $price = Price::getNDSPrice($price);
                    $upd = Order::UpdateOrderAfterUpsell($id, $product["upsell_$step"], $price['price'], $price['nds'], // Обновляем заказ в БД
                        $upsell['type_id'], $upsell['product_name']
                    );

                    if ($step > 1 && $upsell['type_id'] == 2) {
                        $_SESSION["delivery_$id"] = 1;
                    }
                }
                $step++;
            }

            if ($step <= 3 && $product["upsell_$step"] != 0) {
                $upsell = Product::getProductData($product["upsell_{$step}"]); // Получить данные продукта апселла
                if ($upsell) {
                    if ($step == 1 && $upsell['type_id'] == 2) {
                        $_SESSION["delivery_$id"] = 1;
                    }

                    $intro = $product["upsell_{$step}_desc"];
                    $text = $product["upsell_{$step}_text"];
                    $price = !empty($product["upsell_{$step}_price"]) ? $product["upsell_{$step}_price"] : $upsell['price'];
                    $old_price = !empty($product["upsell_{$step}_price"]) ? $upsell['price'] : false;
                    
                    $price = Price::getOnlyNDSPrice($price);
                    $old_price = Price::getOnlyNDSPrice($old_price);

                    $this->setSEOParams('Спецпредложение');
                    $this->setViewParams('', 'order/upsell.php', null, null, 'offer-page');

                    require_once ("{$this->template_path}/main2.php");
                    return true;
                } else { // Если продукт удалён, отправляем письмо админу
                    Email::AdminNotification($this->settings['admin_email'], $id);
                }
            }

            OrderTask::addTask($order['order_id'], OrderTask::STAGE_UPSELL); // добавление задач для крона по заказу

            $url = isset($_SESSION["delivery_$id"]) ? "/delivery/$id" : "/pay/$id";
            System::redirectUrl($url);
        } else {
            ErrorPage::returnError("Что-то не так, ошибка сессии");
        }
        return true;
    }




    // ДОСТАВКА ВЫБОР
    public function actionDelivery($order_date)
    {
        if (!isset($_SESSION["delivery_$order_date"])) {
            ErrorPage::returnError("Что-то не так, ошибка сессии");
        }

        // Данные заказа по order_date
        $order_date = intval($order_date);
        $order = Order::getOrderData($order_date, 0); // Получить данные заказа

        // Получить список продуктов заказа
        if ($order) {
            $order_items = Order::getOrderItems($order['order_id']);
        } else {
            ErrorPage::returnError("Не удалось получить данные заказа");
        }

        // Получить список способов доставки
        $delivery_methods = Order::getDeliveryMethods();
        $this->view['js'] = 1;

        if (isset($_POST['delivery_ok']) && isset($_POST['method']) && $_POST['pay']!= null ) {
            $method = intval($_POST['method']);
            $pay = intval($_POST['pay']);
            $total = intval($_POST['total']);

            if(@ $delivery_methods[$method]['when_pay'] == 1)
                $pay = 1;

            elseif(@ $delivery_methods[$method]['when_pay'] == 2)
                $pay = 0;

            if ($pay == 1) {
                $upd = Order::UpdateOrderDeliveryMethod($order_date, $method);
                System::redirectUrl("/pay/$order_date");
            }

            if ($pay == 0) {
                $metod_name = Order::getDeliveryMethodName($method);
                $i = 1;
                $o_item = array();
                foreach ($order_items as $item) {
                    $o_item[$i] = $item['product_name'];
                    $i++;
                }

                $upd = Order::UpdateOrderDeliveryMethod($order_date, $method);
                $send = Email::SendConfirmDelivery($order_date, $order['client_name'], $order['client_email'],
                    $o_item, $total, $metod_name
                );

                $this->setSEOParams('Подтвердите заказ');
                $this->setViewParams('', 'order/confirm_delivery.php', null,
                    null, 'order-page'
                );

                require_once ("{$this->template_path}/main2.php");
                return true;
            }
        } elseif (isset($_POST['delivery_ok']) && !isset($_POST['method'])) {
            $message = 'Выберите способ доставки';
        } elseif (isset($_POST['delivery_ok']) && $_POST['pay'] == null) {
            $message = 'Выберите вариант оплаты';
        }

        //Защита email и телефона в заказе
        $hide_cl_email = Order::isHideClientEmail($this->settings, $order);

        $this->setSEOParams('Выбор способа доставки');
        $this->setViewParams('', 'order/delivery.php', null, null, 'delivery-page');

        require_once ("{$this->template_path}/main2.php");
        return true;
    }


    // ПОДВТЕРЖДЕНИЕ ДОСТАВКИ
    public function actionConfirmdelivery($order_date)
    {
        if (isset($_GET['key'])) {
            $key = htmlentities($_GET['key']);

            // Получить данные заказа
            $order = Order::getOrderData($order_date, 0);

            // Сверить емейл в ключе и в заказе
            if (md5($order['client_email']) == $key) {
                // Изменить статус заказа на 7
                $upd = Order::UpdateOrderDeliveryConfirm($order_date, 7);

                // Отправить письмо админу
                Email::AdminDeliveryConfirm($order_date, $order['client_email'], $order['client_name']);

                $this->setSEOParams('Спасибо, заказ передан в обработку');
                $this->setViewParams('', 'order/delivery_confirmed.php');

                require_once ("{$this->template_path}/main2.php");
            } else {
                exit('Неверный ключ');
            }
        } else {
            System::redirectUrl('/');
        }
        return true;
    }


    // ОТМЕНА ЗАКАЗА
    public function actionCancelpay($order_date)
    {
        // Проверить дату заказа и текущую дату (в настройках получить сколько времени хранить заказ)
        $now = time();
        $cookie = $this->settings['cookie'];
        $tax = 0;
        $back = $this->settings['script_url'].'/lk/orders';
        
        // Получить данные заказа
        $order = Order::getOrderData($order_date, 0);

        $life_time = $this->settings['order_life_time'] * 86400;
        $expire_date = $order['expire_date'] > 0 ? $order['expire_date'] : $order['order_date'] + $life_time;

        if ($now < $expire_date) {
            // Данные заказа по order_date
            $order_date = intval($order_date);
            $hash = md5($order['client_email'].':'.$order_date);

            if (isset($_GET['key']) && $_GET['key'] == $hash) {
                $cancel = Order::deleteOrder($order['order_id'], null);
                if ($cancel) {
                    echo '<script>alert("Заказ удалён"); document.location.href = "'.$back.'";</script>';
                }
            } else {
                System::redirectUrl("/");
            }
        } else {
            System::redirectUrl("/");
        }
        return true;
    }

    public function actionAtolSuccess(){
        $this->setViewParams('payments', 'payments/atol/success.php', null, null, 'order-pay-page');

        require_once (ROOT . '/payments/atol/success.php');
        return true;
    }
    
    public function actionAtolResult(){
        $this->setViewParams('payments', '/payments/atol/result.php', null, null, 'order-pay-page');
        require_once (ROOT . '/payments/atol/result.php');

        return true;
    }
    /**
     * 
     * ОПЛАТА
     * @param $order_date
     * @return bool
     */
    public function actionPay($order_date)
    {

        $value =false;

        $this->view['noindex'] = true;
        if (isset($_COOKIE["cookie_name"])) {
            setcookie("cookie_name", "", time() - 3600, "/");
            $value =true;
        }

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'TelegramBot') !== false) {
            $value =false;

        }
        $now = time();
        $cookie = $this->settings['cookie'];
        $jquery_head = 1;
        $tax = 0;

		$order_date = intval($order_date);
		// Получить данные заказа
		// +KEMSTAT-30
        $order = Order::getOrderData($order_date);//, 0, 1);
        // -KEMSTAT-30
        if (!$order) {
            ErrorPage::returnError('Заказ не найден');
        }

        $org_separation = $order['org_id'] > 0 ? Organization::getOrgData($order['org_id']) : false;
        $currency_list = false;
        
        $params = json_decode($this->settings['params'], true);
        if (isset($params['many_currency']) && $params['many_currency'] == 1) {
            $currency_list = Currency::getCurrencyList();
        }

		if ($order['installment_map_id'] != 0) { // если рассрочка, то lifetime считается на 100 дней
		    $life_time = 100*86400;
            $expire_date = $order['order_date'] + $life_time;
        } else {
		    $life_time = $this->settings['order_life_time'] * 86400;
            $expire_date = $order['expire_date'];
        }
        
        if ($now >= $expire_date) {
            ErrorPage::returnError("Время заказа истекло");
        }

        // Данные заказа по order_date
        $this->setViewParams('order');
        $this->setSEOParams('Выбор способа оплаты');


        //Защита email и телефона в заказе
        $hide_cl_email = Order::isHideClientEmail($this->settings, $order);

        if ($order['ship_method_id'] != null) {
            $ship_method = System::getShipMethod($order['ship_method_id']);
            if ($ship_method['tax'] != 0) {
                $tax = $ship_method['tax'];
            }
        }

        // Получить список продуктов заказа
        if ($order) {
            if ($order['installment_map_id']) {
                $first_order_id = Installment::getFirstPayOrderId($order['installment_map_id']);
                $order_items = Order::getOrderItems($first_order_id);
                $installment_order_items = Order::getOrderItems($order['order_id']);
            } else {
                $order_items = Order::getOrderItems($order['order_id']);
            }

            if (!$order_items) {
                ErrorPage::returnError("Продукты заказа не найдены");
            }
        } else {
            ErrorPage::returnError("Не удалось получить данные заказа");
        }

        //Переменная с правильной ценой товаров в заказе(для платежек, где не используется $total, и цена формируется из списка товаров)
        $order_items_for_payments = Price::changeOrderItemsPriceWithDeposits($order, $installment_order_items ?? $order_items);

        $membership = System::CheckExtensension('membership', 1);
        $total = $order_sum = 0;
        $related_products = false;
        $get_member = false;

        $prod_ids = array_column($order_items, 'product_id');
        $prod_names = array_column($order_items, 'product_name');
        $recurrent_enable = false;

        $client = User::getUserDataByEmail($order['client_email'], null);
        $installments2order = true;
        
        $flows = System::CheckExtensension('learning_flows', 1);
        if($flows) $flows_params = json_decode(System::getExtensionSetting('learning_flows'), true);

        foreach ($order_items as $key => $item) {
            $product_data = Product::getProductData($item['product_id']); // получаем данные продукта подписки
            if (!$product_data) {
                exit('<p style="text-align:center">К сожалению этот заказ не может быть оплачен, товар снят с продаж<br /><a href="/">Вернуться на главную</a></p>');
            }

            if ($product_data['installment'] == 0) $installments2order = false; // запретить рассрочку

            if ($membership && $item['type_id'] == 3 && $product_data['subscription_id'] != null) {
                $get_member = 1;
                $plane = Member::getPlaneByID($product_data['subscription_id']);
                $act_link = false;
                $stop = false;

                if ($plane['recurrent_enable'] == 1) {
                    $recurrent_enable = true;
                }

                // Проверка активной подписки при первой покупке
                if ($plane['first_time'] > 0) {
                    $first_time_data = json_decode($plane['first_time_data'], 1);
                    $plane_list = $first_time_data['planes'] != null ? implode(",", $first_time_data['planes']) : false;

                    if ($first_time_data['link'] != null) {
                        $act_link = $first_time_data['link'];
                    }

                    // Проверка активных планов подписки
                    $check = Member::checkActivePlanes($plane_list);

                    if ($check == 0) {
                        $stop = 1;
                    }
                }

                // Проверка активной подписки, если разрешено продлевать только активные
                if ($order['subs_id'] > 0 && $plane['prolong_active'] == 1) {
                    $map = Member::getUserMemberMapByID($order['subs_id']);
                    if ($map['status'] == 0) {
                        $stop = 1;
                        if ($plane['prolong_link'] != null) {
                            $act_link = $plane['prolong_link'];
                        }
                    }
                }


                if ($stop) {
                    // завершаем оформление
                    $page['name'] = $page['custom_code'] = null;
                    $act = $act_link ? '<a href="'.$act_link.'">Перейдите по этой ссылке</a>' : 'Свяжитесь с нами';
                    $page['content'] = '<p style="text-align:center; padding:2em 0">К сожалению этот заказ не может быть оплачен, срок продления истёк.<br />'.$act.'.</p>';


                    $this->setViewParams('landing', 'static/static.php', [['title' => $page['name']]],
                        null, 'invert-page', 'content-wrap'
                    );

                    require_once ("{$this->template_path}/main.php");

                    $del = Order::deleteOrder($order['order_id'], null); // удалить заказ
                    unset($_SESSION['subs_id']);
                    return true;
                }
            }

            if ($key == 0) {
                $related_products = Product::getRelatedProductsByID($item['product_id'], 1);
            }

            $order_sum += $order['installment_map_id'] && $item['old_price'] !== null ? $item['old_price'] : 0;
        }

        $total = Order::getOrderTotalSum($order['order_id']);

        // ЕСЛИ ЗАКАЗ 0 рублей
        if ($total == 0) {
            //Получаем настройки, включена ли функция автовхода при бесп.заказе
            $autoAuth = json_decode($this->settings['params'], true);
            if (isset($autoAuth) && isset($autoAuth['enable_auto_auth_for_free_order'])) {
                $autoAuth = $autoAuth['enable_auto_auth_for_free_order'];
            }

            if ($autoAuth) {
                $issetUser = User::getUserDataByEmail($order['client_email']);
            }
            if($value)
            $render = Order::renderOrder($order);
            $redirect = false;

            //Авторизация при покупке
            if ($autoAuth) {

                if (!User::isAuth() && !$issetUser) {//Только что созданный пользователь
                    $createdUser = User::getUserDataByEmail($order['client_email']);
                    if ($createdUser) {
                        $auth = User::Auth($createdUser['user_id'], $createdUser['user_name']);
                    }
                    if (isset($auth)) {
                        if($value)
                        Remember::saveData($createdUser, true);
                    }
                } elseif (!User::isAuth() && $issetUser) {//Уже существующий до этого пользователь

                    $userdata = $issetUser;

                    if (isset($_COOKIE['user_token_buy'])) {
                        $user_token_buy = $_COOKIE['user_token_buy'];
                        $user_token = json_decode($userdata['auto_login'], true);

                        if ($user_token_buy == $user_token['token']) {
                            $auth = User::Auth($userdata['user_id'], $userdata['user_name']);
                            if (isset($auth)) {
                                if($value)
                                Remember::saveData($userdata, true);
                            }
                        }

                    }
                }

            }


            foreach ($order_items as $item) {
                $product = Product::getProductData($item['product_id']);
                if ($product['redirect_after']) {
                    $redirect = $product['redirect_after'];
                }
            }

            if ($redirect) {
                System::redirectUrl($redirect);
            }

            // Если в настройках указано давать скачивать сразу
            // то выдаём страницу download.php
            // Если нет, то отправляем заказ на емейл.
            if ($this->settings['simple_free_dwl'] == 1) {
                $this->setSEOParams('Скачать');
                $this->setViewParams('order', 'order/free_load.php');
            } else {
                if(intval($order['product_id'])==31){
                    System::redirectUrl("/lk/mytrainings");
                    return true;
                }
                $this->setSEOParams('Спасибо!');
                $this->setViewParams('order', 'order/thanks.php', null,
                    null, 'order-page'
                );
            }
            
            require_once ("{$this->template_path}/main2.php");
            return true;
        } else {
            $products_ids = array_column($order_items, 'product_id');
            $installment_list = Installment::getInstallments2Products($products_ids, 0, $total);
            $prepayment_list = Installment::getInstallments2Products($products_ids, 1, $total);

            if (!$installments2order) {
                $installment_list = $prepayment_list = false;
            }
        }



        // РУЧНОЙ СПОСОБ
        if (isset($_POST['custom_pay']) && isset($_COOKIE["$cookie"])) {
            $payment_id = intval($_POST['payment']);
            $gateway = htmlentities($_POST['gateway']);
            $purse = htmlentities($_POST['card_number']);
            $summ = intval($_POST['summ']);
            if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
            if (isset($_SESSION['sale_id'])) unset($_SESSION['sale_id']);

            // Обновить статус заказа
            $upd = Order::UpdateOrderCustom($order_date, $payment_id);

            // Отправить письмо админу
            if ($upd) {

                $send = Email::AdminCustomOrder($order_date, $this->settings['secret_key'], $this->settings['admin_email'], $order['client_email'], $gateway, $purse, $summ, $order['client_name'], $order['client_phone'], $this->settings['script_url'], $order['order_id']);
            }

            $custom_data = Order::getDataCustomModule();
            require_once (ROOT.'/payments/custom/success.php');
            return true;
        }


        // ОПЛАТА ОТ ОРГАНИЗАЦИИ
        if (isset($_POST['company_pay']) && isset($_COOKIE["$cookie"])) {
            $payment_module = Order::getDataCustomModule('company'); // Данные платёжки
            $payment_params = unserialize(base64_decode($payment_module['params'])); // извлечь параметры

            $payment_id = intval($_POST['payment']);
            $organization = htmlentities($_POST['organization']);
            $inn = intval($_POST['inn']);
            $summ = intval($_POST['summ']);
            $bik = isset($_POST['bik']) ? htmlentities($_POST['bik']) : null;
            $rs = isset($_POST['rs']) ? htmlentities($_POST['rs']) : null;
            $country = isset($_POST['country']) ? htmlentities($_POST['country']) : null;
            $city = isset($_POST['city']) ? htmlentities($_POST['city']) : null;
            $address = isset($_POST['address']) ? htmlentities($_POST['address']) : null;

            if (isset($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
            if (isset($_SESSION['sale_id'])) {
                unset($_SESSION['sale_id']);
            }

            $payment_data = array();
            $payment_data = unserialize(base64_decode($order['order_info']));
            $payment_data['rs'] = $rs;
            $payment_data['city'] = $city;
            $payment_data['address'] = $address;

            $payment_data['org'] = $organization;
            $payment_data['inn'] = $inn;
            $payment_data['bik'] = $bik;

            $payment_data = base64_encode(serialize($payment_data));

            // Обновить статус заказа на ручной
            $upd = Order::UpdateOrderCustom($order_date, $payment_id, $payment_data);
                        // Отправить письмо админу

            if ($upd) {
                $send = Email::AdminCompanyOrder($order_date, $this->settings['secret_key'],
                    $this->settings['admin_email'], $order['client_email'], $summ,
                    $order['client_name'], $order['client_phone'], $this->settings['script_url'],
                    $order['order_id'], $organization, $inn, $bik, $rs, $country, $city, $address
                );
            }
            require_once (ROOT . '/payments/company/success.php');
            return true;
        }



        // Получить список платёжных модулей
        $payments = $select_payments = Order::getPayments();
        $products = Product::getProducts2Order($order['order_id']);

        foreach ($products as $product) {
            if ($product['select_payments']) {
                $prod_select_payments = unserialize($product['select_payments']);

                foreach ($select_payments as $key => $value) {
                    if (!isset($select_payments[$key]) || in_array($value['payment_id'], $prod_select_payments)) {
                        continue;
                    } else {
                        unset($select_payments[$key]);
                    }
                }
            }
        }

        if ($select_payments) {
            $payments = $select_payments;
        }
        $total += $tax;
        if ($order_sum) {
            $order_sum += $tax;
        }

        $this->setViewParams('order', 'order/payments.php', null, null, 'order-pay-page');

        $this->view['js'] = 1;
        require_once ("{$this->template_path}/main2.php");

        if (isset($_SESSION["upsell_$order_date"])) {
            unset($_SESSION["upsell_$order_date"]);
        }

        if (isset($_SESSION["delivery_$order_date"])) {
            unset($_SESSION["delivery_$order_date"]);
        }
        return true;  
 
    }


    public function actionPrepayment() {
        $now = time();
        $jquery_head = 1;
        $tax = 0;
        $order_date = isset($_POST['order_date']) ? intval($_POST['order_date']) : null;
        $order = $order_date ? Order::getOrderData($order_date, 0) : null; // Получить данные заказа
        $order_items = $order ? Order::getOrderItems($order['order_id']) : null; // Получить список продуктов заказа

        if (!$order_date || !$order || !$order_items) {
            ErrorPage::returnError("Не удалось получить данные заказа");
        }

        $life_time = $this->settings['order_life_time'] * 86400; // Проверить дату заказа и текущую дату (в настройках получить сколько времени хранить заказ)
        $expire_date = $order['expire_date'] > 0 ? $order['expire_date'] : $order['order_date'] + $life_time;

        if ($now >= $expire_date) {
            ErrorPage::returnError("error");
        }

        if (isset($_POST['installment'])) {
            $installment_id = intval($_POST['installment_id']);
            $related_products = $installment = false;
            $this->setSEOParams('Заявка на рассрочку');
            $total = $tax;

            foreach ($order_items as $item) {
                $product = Product::getProductDataForSendOrder($item['product_id']);
                if ($product['installment'] == 0) {
                    ErrorPage::returnError("Ошибка рассрочки для этого товара");
                }
                $total += $item['price'];
            }

            $installment_data = Product::getInstallmentData($installment_id);
            if ($installment_data) {
                OrderTask::addTask($order['order_id'], OrderTask::STAGE_INSTALLMENT, $installment_id);
                $installment_total = $total + $installment_data['increase'];
            }


            $total = 0;
            foreach ($order_items as $item) {
                $total = $total + $item['price'];
            }

            $installment_data = Product::getInstallmentData($installment_id);
            if (!$installment_data) {
                ErrorPage::returnError("Не верные данные рассрочки");
            }

            $info = unserialize(base64_decode($order['order_info']));
            $name = trim($order['client_name']);
            $soname = isset($info['surname']) ? $info['surname'] : null;
            $otname = isset($info['patronymic']) ? $info['patronymic'] : null;
            $passport = isset($info['passport']) ? $info['passport'] : null;
            $email = $order['client_email'];
            $phone = $order['client_phone'];
            $city = $order['client_city'];
            $address = isset($info['client_address']) ? $info['client_address'] : null;
            $install_title = $installment_data['title'];

            $letters = unserialize(base64_decode($installment_data['letters']));
            $sms = unserialize(base64_decode($installment_data['sms']));
            $instalment_map_status = 0;
            $admin_comment = 'Рассрочка '.$install_title. ' | Первый платёж';

            // Доп. стоимость рассрочки
            $increase_pay = $installment_data['increase'] > 0 ? $installment_data['increase'] / $installment_data['max_periods'] : 0;
            $order_summ = $preupd = $expired = false;
            $first = true;
            $total += $installment_data['increase'];

            // Создать запись с данными рассрочки
            $map = Installment::writeInstalmentMap($order['order_id'], $total, $instalment_map_status,
                $installment_data, $email, 0, null, null, $now
            );

            if ($map) {
                $good = $this->settings['script_url'].'/installment/vote?key='.md5($this->settings['secret_key']).'&order='.$order['order_id'].'&map_id='.$map.'&install_id='.$installment_id.'&answer=1';
                $bad = $this->settings['script_url'].'/installment/vote?key='.md5($this->settings['secret_key']).'&order='.$order['order_id'].'&map_id='.$map.'&install_id='.$installment_id.'&answer=0';

                // Отправить письмо админу с заявлением
                $subject = 'Заявление на рассрочку';
                $letter = "
                    <p>Рассрочка $install_title</p>
                    <p>ФИО: $soname $name $otname<br />
                    Email: $email<br />
                    ТЕЛЕФОН: $phone<br />
                    ПАСПОРТ: $passport<br />
                    ГОРОД: $city<br />
                    АДРЕС: $address
                    </p>";

                if ($installment_data['approve'] != 1) {
                    $letter .= "
                        <p>-------------</p>
                        
                        <p><a href='$good'>Одобрить рассрочку</a></p>
                        <p><a href='$bad'>Отклонить заявку</a></p>
                        ";
                }

                $send = Email::SendMessageToBlank($this->settings['admin_email'], $name, $subject, $letter);
                $update = Order::updateSummForInstallment($order['order_id'], $installment_data['first_pay'],
                    $admin_comment, $map, $order_summ, $preupd, $increase_pay, $expired, $first
                );
            }

            // Если авто одобрение
            if ($installment_data['approve'] == 1) {
                // Обновить статус заказа
                $change = Order::updateStatusInstallment($order_date, 5, $map);
                System::redirectUrl("/pay/$order_date");
            } else {
                $change = Order::updateStatusInstallment($order_date, 3); // на рассмотрении

                // Показать страницу спасибо.
                $this->setSEOParams('Спасибо!');
                $this->setViewParams('', 'order/installment_wait.php', null,
                    null, 'order-page'
                );

                require_once ("{$this->template_path}/main2.php");
                exit;
            }
        }

        exit('error');
    }


    /**
     * РАССРОЧКА
     */
    public function actionInstallment()
    {
        if (isset($_POST['is_prepayment']) && $_POST['is_prepayment']) {
           return $this->actionPrepayment();
        }

        $now = time();
        $jquery_head = 1;
        $tax = 0;
        $order_date = isset($_POST['order_date']) ? intval($_POST['order_date']) : null;
        $order = $order_date ? Order::getOrderData($order_date, 0) : null; // Получить данные заказа
        $order_items = $order ? Order::getOrderItems($order['order_id']) : null; // Получить список продуктов заказа

        if (!$order_date || !$order || !$order_items) {
            ErrorPage::returnError("Не удалось получить данные заказа");
        }

        $life_time = $this->settings['order_life_time'] * 86400; // Проверить дату заказа и текущую дату (в настройках получить сколько времени хранить заказ)
        $expire_date = $order['expire_date'] > 0 ? $order['expire_date'] : $order['order_date'] + $life_time;

        if ($now >= $expire_date) {
            ErrorPage::returnError("error");
        }

        if (isset($_POST['installment']) && isset($_POST['installment_id'])) {
            $installment_id = intval($_POST['installment_id']);
            $related_products = $installment = false;
            $this->setSEOParams('Заявка на рассрочку');
            $_SESSION['installment'] = 1;
            $total = $tax;

            foreach ($order_items as $item) {
                $product = Product::getProductDataForSendOrder($item['product_id']);
                if ($product['installment'] == 0) {
                    ErrorPage::returnError("Ошибка рассрочки для этого товара");
                }
                $total += $item['price'];
            }

            $installment_data = Product::getInstallmentData($installment_id);
            if ($installment_data) {
                OrderTask::addTask($order['order_id'], OrderTask::STAGE_INSTALLMENT, $installment_id);
                $installment_total = $total + $installment_data['increase'];
            }

            if ($installment_data['max_periods'] == 2) {
                $this->setSEOParams('Оформление предоплаты');
            }


            //Защита email и телефона в заказе
            $hide_cl_email = Order::isHideClientEmail($this->settings, $order);
            $this->setViewParams('', 'order/installment.php', null,
                null, 'installment-page'
            );

            require_once ("{$this->template_path}/main2.php");
            exit;
        }


        // Отправка заявки на рассрочку
        if (isset($_POST['go_installment']) && isset($_SESSION['installment'])) {
            $total = 0;
            foreach ($order_items as $item) {
                $total = $total + $item['price'];
            }

            $name = htmlentities(trim($_POST['name']));
            $soname = isset($_POST['soname']) ? htmlentities($_POST['soname']) : null;
            $otname = isset($_POST['otname']) ? htmlentities($_POST['otname']) : null;
            $passport = isset($_POST['passport']) ? htmlentities($_POST['passport']) : null;
            $email = $order['client_email'];
            $phone = isset($_POST['phone']) ? htmlentities($_POST['phone']) : null;
            $city = isset($_POST['city']) ? htmlentities($_POST['city']) : null;
            $address = isset($_POST['address']) ? htmlentities($_POST['address']) : null;
            $install_id = intval($_POST['install_id']);
            $install_title = htmlentities($_POST['install_title']);

            $installment_data = Product::getInstallmentData($install_id);
            if (!$installment_data) {
                ErrorPage::returnError("Не верные данные рассрочки");
            }

            $letters = unserialize(base64_decode($installment_data['letters']));
            $sms = unserialize(base64_decode($installment_data['sms']));
            $url = $url2 = false;

            if (isset($_FILES['skan']) && $_FILES["skan"]["size"] != 0) {
                $fd = mkdir(ROOT ."/tmp/$now/", 0755);
                $tmp_name = $_FILES["skan"]["tmp_name"]; // Временное имя картинки на сервере
                $img = $_FILES["skan"]["name"]; // Имя картинки при загрузке

                $folder = ROOT . "/tmp/$now/"; // папка для сохранения
                $path = $folder . $img; // Полный путь с именем файла
                if (is_uploaded_file($tmp_name)) {
                    move_uploaded_file($tmp_name, $path);
                }

                $url = $this->settings['script_url'].'/tmp/'.$now.'/'.$img;
            }

            if (isset($_FILES['skan2']) && $_FILES["skan2"]["size"] != 0) {
                $tmp_name = $_FILES["skan2"]["tmp_name"]; // Временное имя картинки на сервере
                $img2 = $_FILES["skan2"]["name"]; // Имя картинки при загрузке

                $folder = ROOT . "/tmp/$now/"; // папка для сохранения
                $path2 = $folder . $img2; // Полный путь с именем файла
                if (is_uploaded_file($tmp_name)) {
                    move_uploaded_file($tmp_name, $path2);
                }

                $url2 = $this->settings['script_url'].'/tmp/'.$now.'/'.$img2;
            }

            $instalment_map_status = 0;
            $admin_comment = 'Рассрочка '.$install_title. ' | Первый платёж';

            // Доп. стоимость рассрочки
            $increase_pay = $installment_data['increase'] > 0 ? $installment_data['increase'] / $installment_data['max_periods'] : 0;
            $order_summ = $preupd = $expired = false;
            $first = true;
            $total += $installment_data['increase'];

            // Создать запись с данными рассрочки
            $map = Installment::writeInstalmentMap($order['order_id'], $total, $instalment_map_status,
                $installment_data, $email, 0, null, null, $now
            );

            if ($map) {
                $good = $this->settings['script_url'].'/installment/vote?key='.md5($this->settings['secret_key']).'&order='.$order['order_id'].'&map_id='.$map.'&install_id='.$install_id.'&answer=1';
                $bad = $this->settings['script_url'].'/installment/vote?key='.md5($this->settings['secret_key']).'&order='.$order['order_id'].'&map_id='.$map.'&install_id='.$install_id.'&answer=0';

                // Отправить письмо админу с заявлением
                $subject = 'Заявление на рассрочку';
                $letter = "
                    <p>Рассрочка $install_title</p>
                    <p>ФИО: $soname $name $otname<br />
                    Email: $email<br />
                    ТЕЛЕФОН: $phone<br />
                    ПАСПОРТ: $passport<br />
                    ГОРОД: $city<br />
                    АДРЕС: $address
                    </p>";

                if ($url) $letter .= "<p>СКАН ПАСПОРТА: <br /><a href='$url'>$url</a></p>";
                if ($url2) $letter .= "<p>СКАН ПАСПОРТА 2: <br /><a href='$url2'>$url2</a></p>";

                if ($installment_data['approve'] != 1) {
                    $letter .= "
                        <p>-------------</p>
                        
                        <p><a href='$good'>Одобрить рассрочку</a></p>
                        <p><a href='$bad'>Отклонить заявку</a></p>
                        ";
                }

                $send = Email::SendMessageToBlank($this->settings['admin_email'], $name, $subject, $letter);
                $update = Order::updateSummForInstallment($order['order_id'], $installment_data['first_pay'], $admin_comment, $map, $order_summ, $preupd, $increase_pay, $expired, $first);
            }

            // Если авто одобрение
            if ($installment_data['approve'] == 1) {
                // Обновить статус заказа
                $change = Order::updateStatusInstallment($order_date, 5, $map);
                System::redirectUrl("/pay/$order_date");
            } else {
                $change = Order::updateStatusInstallment($order_date, 3); // на рассмотрении

                // Показать страницу спасибо.
                $this->setSEOParams('Спасибо!');
                $this->setViewParams('', 'order/installment_wait.php', null,
                    null, 'order-page'
                );

                require_once ("{$this->template_path}/main2.php");
                exit;
            }
        }

        exit('error');
    }
    
    
    // ПОДТВЕРЖДЕНИЕ РАССРОЧКИ или ОТКЛОНЕНИЕ
    public function actionVoteinstallment()
    {
        if (isset($_GET['key']) && isset($_GET['order']) && isset($_GET['install_id']) && isset($_GET['map_id'])) {
            
            $now = time();
            $hash = md5($this->settings['secret_key']);
            if ($hash == $_GET['key']) {
                
                $order_id = intval($_GET['order']);
                $order = Order::getOrderDataByID($order_id, 3);
                
                if ($order) {
                    
                    $map_id = intval($_GET['map_id']);
                    $installment_id = intval($_GET['install_id']);
                    $installment_data = Product::getInstallmentData($installment_id);
                    $letters = unserialize(base64_decode($installment_data['letters']));
                    $sms = unserialize(base64_decode($installment_data['sms']));
                    $phone = $order['client_phone'];
                    
                    $replace = array(
                    '[NAME]' => $order['client_name'],
                    '[CLIENT_NAME]' => $order['client_name'],
                    '[EMAIL]' => $order['client_email'],
                    '[ORDER]' => $order['order_date'],
                    '[LINK]' => $this->settings['script_url'].'/pay/'.$order['order_date'],
                    );
                    
                    if ($_GET['answer'] == 1) {
                        // ПОДТВЕРЖДЕНИЕ
                        $upd = Order::updateStatusInstallment($order['order_date'], 5, $map_id);
                        // Отправляем письмо и смс клиенту
                        
                        $text = strtr($letters['letter_good'], $replace);
                        $send = Email::SendMessageToBlank($order['client_email'], $order['client_name'], $letters['subject_good'], $text);

                        $message = strtr($sms['text_good'], $replace); 
                        $send_sms = SMSC::sendSMS($phone, $message);

                        exit('<h1>Всё ок. Рассрочка одобрена</h1>');
                    } else {
                        // ОТКЛОНЕНИЕ
                        $upd = Order::updateStatusInstallment($order['order_date'], 4);
                        // Отылаем емейл клиенту
                        $text = strtr($letters['letter_bad'], $replace);
                        $send = Email::SendMessageToBlank($order['client_email'], $order['client_name'], $letters['subject_bad'], $text);
                        
                        if ($sms['send_bad'] == 1 && $phone != null) {
                            $message = strtr($sms['text_bad'], $replace);
                            $send_sms = SMSC::sendSMS($phone, $message);
                        }

                        exit('<h1>Рассрочка отклонена</h1>');
                    }
                } else {
                    ErrorPage::returnError("Заказ еще только выполняется");
                    exit('Order is render');
                }
            } else {
                ErrorPage::returnError("Неправильный ответ");
            }
        } else {
            ErrorPage::returnError("Неправильный ответ");
        }
    }
    
    
    
    
    // ПОДТВЕРЖДЕНИЕ РУЧНОЙ СПОСОБ
    public function actionConfirm()
    {
        if (isset($_GET['date']) && isset($_GET['key'])) {
            
            $order_date = intval($_GET['date']);
            $md5 = $_GET['key'];
            // Найти заказ в БД по order_date
            $order = Order::getOrderData($order_date, 0);
            
            if ($order) {
                if (md5($order['order_id'].$this->settings['secret_key']) == $md5) {
                    
                    // Обновить и обработать заказ
                    $render = Order::renderOrder($order);
                    $user_id = isset($_SESSION['admin_user']) ? intval($_SESSION['admin_user']) : 0; 
                    
                    if ($render) {
                        $log = ActionLog::writeLog('orders', 'confirm', 'order', $order['order_id'], time(), $user_id, json_encode($_GET));
                        echo '<h1 style="text-align:center; padding:1em 0">Всё ок. Заказ одобрен</h1>';  
                    } else {
                        echo 'Ошибка обновления и обработки заказа';
                    }
                }
            } else {
                ErrorPage::returnError("Заказ не найден");
            }
        } else {
            ErrorPage::returnError("Ошибка передачи параметров");
        }
        return true;
    }


    /**
     * ИНФОРМАЦИЯ ПО ЗАКАЗУ
     * @param $order_date
     */
    public function actionOrderInfo($order_date) {
        $h2 = $h3 = $h3_class = '';

        $email = isset($_GET['client_email']) ? htmlentities($_GET['client_email']) : null;
        $order = Order::getOrderData($order_date);

        if ($order && $email && $email == $order['client_email']) {
            $order_items = order::getOrderItems($order['order_id']);
            $products = Product::getProducts2Order($order['order_id']);
            $total = Order::getOrderTotalSum($order['order_id']);
            $order_info = $order['order_info'] ? unserialize(base64_decode($order['order_info'])) : null;
            $surname = isset($order_info['surname']) ? $order_info['surname'] : '';
            $client_name = $order['client_name'] . ($surname ? " $surname" : '');

            // модуль оплаты post-credit
            $pos_credit_data = Order::getPaymentSetting('poscredit');
            if ($pos_credit_data['status']) {
                $posCredit = new PosCredit();
                $profile_id = isset($_GET['profile_id']) ? (int)$_GET['profile_id'] : null;
                $pc_order = $posCredit->getOrder($order['order_id'], $profile_id);

                if ($pc_order) {
                    $this->setSEOParams('Информация по кредиту');
                    $h2 = 'Заявка на рассрочку';
                    $h3 = "Статус: {$posCredit->getStatusText($pc_order['status'])}";
                    $h3_class = "pos-credit-status status-{$pc_order['status']}";
                } else {
                    ErrorPage::return404();
                }
            }
        } else {
            ErrorPage::return404();
        }

        $this->setViewParams('order-info', 'order/info.php');
        require_once ("{$this->template_path}/main2.php");
        return true;
    }
    
    
    // API 
    public function actionApi()
    {
        $cookie = $this->settings['cookie'];

        if ($this->settings['enable_sale'] == 0) {
            exit('Продажи закрыты');
        }

        $template = $this->template_path;
        if (isset($_REQUEST['skey']) && $_REQUEST['skey'] == $this->settings['secret_key'] && isset($_REQUEST['email']) && isset($_REQUEST['prod_id'])) {
            // paid api
            if (isset($_REQUEST['paid'])) {
                $sign = $_REQUEST['sign'];
                $real_sign = !empty($this->settings['private_key']) ? md5($this->settings['private_key'].';'.$_REQUEST['email']) : false;
            } else {
                $sign = false;
            }

            $valid_email = explode('@', $_REQUEST['email']);

            if (isset($valid_email[1])) {
                $email = htmlentities(trim(strtolower($_REQUEST['email'])));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    exit("E-mail адрес '$email' указан неверно.\n");
                }

                $replace_name = '';
                if (isset($this->settings['params']) && !empty($this->settings['params']['not_exist_name'])) {
                    $replace_name = $this->settings['params']['not_exist_name'] == '[EMAIL]' ? $email : $this->settings['params']['not_exist_name'];
                }

                $name = isset($_REQUEST['name']) && !empty($_REQUEST['name']) ? htmlentities(mb_substr($_REQUEST['name'], 0, 255)) : $replace_name;
                if (strpbrk($name, "'()-$%&!")) {
                    exit('Do not use special characters!'); 
                }

                $phone = isset($_REQUEST['phone']) ? htmlentities($_REQUEST['phone']) : null;
                
                if (strpos($name, 'script') || strpos($email, '<script')) {
                    exit('R Tape loading error');
                }
                
                $prod_id = intval($_REQUEST['prod_id']);
				$request_price = isset($_REQUEST['price']) ? intval($_REQUEST['price']) : null;
                
                if (isset($_REQUEST['promo'])) {
                    $promo_code = htmlentities(trim($_REQUEST['promo']));

                    if (!isset($_SESSION['promo_code']) || $_SESSION['promo_code'] != $promo_code) {
                        $sale = Product::getSaleByPromoCode($promo_code);
                        if ($sale && ($sale['count_uses'] === null || $sale['count_uses'] > 0)) {
                            if ($sale['count_uses'] > 0) {
                                Product::updSaleCountUses($sale['id'], $sale['count_uses'] - 1);
                            }
                            $_SESSION['promo_code'] = $promo_code;
                        }
                    }
                }

                $partner_id = null;
                if (isset($_REQUEST['pid'])) {
                    $pid = intval($_REQUEST['pid']);
                    if ($pid && Aff::AffHits($pid)) {
                        $partner_id = $pid;
                    }
                } else {
                    $partner_id = System::getPartnerId($email, $cookie);
                }

                $browser = isset($_REQUEST['browser']) ? intval($_REQUEST['browser']) : 1;
				$comment = isset($_REQUEST['comment']) ? htmlentities($_REQUEST['comment']) : null;
          
                
                // получить данные продукта
                $product = Product::getProductById($prod_id);
                $price = Price::getFinalPrice($prod_id);
				$org_id = Organization::getOrgByProduct($prod_id);

                // Получить данные сопутствующих продуктов
                $related_products = Product::getRelatedProductsByID($prod_id, 1);

                // Запись заказа в БД
                $status = $base_id = 0;
                $sale_id = $price['sale_id'];
                $var = null;
                $index = isset($_REQUEST['index']) ? mb_strimwidth(htmlentities($_REQUEST['index']),0,8) : null;
                $city = isset($_REQUEST['city']) ? htmlentities($_REQUEST['city']) : null;
                $address = isset($_REQUEST['address']) ? htmlentities($_REQUEST['address']) : null;
                $date = time();
				$param = isset($_COOKIE["$cookie"]) ? htmlentities($_COOKIE["$cookie"]) : htmlentities($_SESSION["$cookie"]);
                //$param = $date.';0;;/api';
                $ip = System::getUserIp();
                $utm = System::getUtm($_REQUEST);
                $from = 3;

                while (Order::checkOrderDate($date)) {
                    $date = $date + 1;
                }
				
				if ($request_price != null) {
				    $price['real_price'] = $request_price;
                }
                
                $nds_price = Price::getNDSPrice($price['real_price']);
                $vk_id = isset($_REQUEST['vk_id']) ? (int)$_REQUEST['vk_id'] : null;
                $vk_url = null;
                $ok_id = isset($_REQUEST['ok_id']) ? (int)$_REQUEST['ok_id'] : null;
                $flow_id = isset($_REQUEST['flow_id']) ? (int)$_REQUEST['flow_id'] : 0;

                $add_order = Order::addOrder($prod_id, $nds_price['price'], $nds_price['nds'], $name, $email, $phone, $index, $city,
                    $address, $comment, $param, $partner_id, $date, $sale_id, $status, $base_id, $var,
                    $product['type_id'], $product['product_name'], $ip, 0, null, null,
                    null, null, 0, $utm, 0, 0, $flow_id, $org_id, $from, $vk_id, $vk_url, $ok_id
                );
                
                if ($add_order) {
                    if (isset($_SESSION['promo_code'])) {
                        unset($_SESSION['promo_code']);
                    }

                    $order = Order::getOrder($add_order);
                    OrderTask::addTask($order['order_id'], OrderTask::STAGE_ACC_STAT); // добавление задач для крона по заказу

                    if (isset($_REQUEST['custom_fields'])) {
                        $user_id = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : null;
                        if (!$user_id && $email) {
                            $user = User::getUserDataByEmail($email);
                            $user_id = $user ? $user['user_id'] : 0;
                        }
                        CustomFields::saveFields2Api($_REQUEST, $user_id, $email);
                    }


                    // Если у продукта есть апселл
                    if ($product['upsell_1'] != 0) {
                        if ($product['type_id'] == 2) $_SESSION["delivery_$date"] = 1; // Запуск сессии для доставки
                        $_SESSION["upsell_$date"] = 1; // Запуск сессии для идентификации апселла
                        System::redirectUrl("{$this->settings['script_url']}/offer/$date");
                    } else {
                        if ($product['type_id'] == 2) {
                            $_SESSION["delivery_$date"] = 1;
                            System::redirectUrl("/delivery/$date");
                        } else {
							$order_items = Order::getOrderItems($add_order);
							$total = 0;
							$i = 0;

							foreach ($order_items as $item) {
								$total = $total + $item['price'];
								$i++;
                            }

                            if ($total == 0 && $browser == 0) {
                                $render = Order::renderOrder($order);
                            } elseif ($total > 0 && isset($_REQUEST['paid'])) {
                                if ($real_sign && $sign == $real_sign) {
                                    $render = Order::renderOrder($order);
                                }
                                
                                $redirect = false;
                                if (!empty($product['redirect_after'])) {
                                    $redirect = $product['redirect_after'];
                                    if ($redirect) {
                                        System::redirectUrl($redirect);
                                    }
                                }
                            } else {
                                System::redirectUrl("/pay/$date");
                            }
                        }
                    }
                    
                    // Если есть сопутствующие товары
                    if ($related_products) {
                        System::redirectUrl("/related/$date");
                    }
                }
                
                echo 'Ok';
            } else {
                ErrorPage::return404();
            }
        } else {
            ErrorPage::return404();
        }
        return true;
    }
    
    
    
    public function actionRulesinstallment($id)
    {
        $installment_data = Product::getInstallmentData($id);

        $this->setSEOParams('Условия рассрочки');
        $this->setViewParams('', 'order/installment_rule.php');

        require_once ("{$this->template_path}/main2.php");
        return true;
    }


    public function actionSuccess($payment) {
        if (file_exists(ROOT."/payments/$payment/success.php")) {
            require (ROOT."/payments/$payment/success.php");
            require_once (ROOT.'/payments/success.php');
        } else {
            ErrorPage::return404();
        }
    }


    public function actionFail($payment) {
        if (file_exists(ROOT."/payments/$payment/fail.php")) {
            require (ROOT."/payments/$payment/fail.php");
            require_once (ROOT.'/payments/fail.php');
        } else {
            ErrorPage::return404();
        }
    }


    /**
     * @param string $is_page
     * @param null $path
     * @param string $body_class
     * @param bool $use_css
     */
    protected function setViewParams2($is_page = '', $path = null, $body_class = 'cart-page', $use_css = true) {
        $this->view = [
            'is_page' => $is_page,
            'use_css' => $use_css,
        ];

        if ($path) {
            $this->view['path'] = Template::getPath($path);
            $this->view['body_class'] = $body_class;
        }
    }
}