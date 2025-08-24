<?php defined('BILLINGMASTER') or die;

class adminOrdersController extends AdminBase {
    
    // СПИСОК ЗАКАЗОВ
    public function actionIndex()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_orders'])) {
            System::redirectUrl("/admin");
        }
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_GET['reset']) && isset($_SESSION['filter_orders'])) {
            unset($_SESSION['filter_orders']);
            System::redirectUrl("/admin/orders");
        }

        $conditions = isset($_GET['filter']) ? OrderFilter::getConditions($_GET) : null;

        // ПАГИНАЦИЯ
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total_order = $conditions ? Order::countOrdersWithConditions($conditions) : Order::countOrders(); // кол-во заказов всего.
        $is_pagination = !isset($_POST['load_csv']) ? true : false;
        $pagination = new Pagination($total_order, $page, $setting['show_items']);

        if ($conditions) {
            $order_list = Order::getOrdersWithConditions($conditions, $page, $setting['show_items'], $is_pagination);
        } else {
            $order_list = Order::getOrderAdminList($page, $setting['show_items'], $is_pagination);
        }
        
        //$order_list = Order::getOrderAdminList_v2($page, $setting['show_items'], $is_pagination, $conditions);

        $total_sum = Order::getOrdersTotalSum($conditions);

        if (isset($_POST['load_csv'])) {
            $time = time();
            $main_fields = ['order_id', 'order_date',  'create_date', 'product_id', 'product_name','product_price', 
                'summ', 'type_order', 'client_name', 'client_surname', 'client_email', 'client_phone', 'client_city', 'client_address', 
                'client_index', 'client_comment', 'admin_comment', 'partner_id',
                'status', 'payment_id', 'payment_name', 'payment_date', 'installment_map_id'];
            // Если используются фин. потоки то добавлем поле
            $is_fin_potok = Organization::getOrgList();
            if ($is_fin_potok !== false) {
                array_push($main_fields, 'fin_potok');
            }

            $add_fields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_referrer',
                'userId_YM', 'userId_GA', 'roistat_visitor'
            ];

            $fields = array_merge($main_fields, $add_fields);
            $csv = implode(';', $fields) . PHP_EOL;
            $csv = str_replace(['order_date', 'installment_map_id'], ['order_number', 'installment_ID'], $csv);
            $count_fields = count($fields);

            $fp = fopen(ROOT.'/tmp/orders_'.$time.'.csv','w');
            fwrite($fp, $csv); // Добавляем заголовок

            if ($order_list) {
                foreach ($order_list as $order) {
                    $summ = Order::getOrderTotalSum($order['order_id']);
                    $items = Order::getOrderItems($order['order_id']);
                    $type_order = array_unique(array_column($items, 'type_id'));
                    if (count($type_order)>1) { // Тут точно смешанный заказ 
                        $type_order = 'Смешанный';
                    } else {
                        $type_order = $type_order[0] == 1 ? 'Цифровой' : 'Физический';
                    }
                    $surname = User::getUserDataByEmail($order['client_email']);
                    $payment_name = isset($order['payment_id']) ? Order::getPaymentDataForAdmin($order['payment_id']) : '';
                    $order_info = $order['order_info'] ? unserialize(base64_decode($order['order_info'])) : null;
                    $fin_potok = Product::getFinpotokFromOrder($order['order_id']);
                    
                    if ($order['utm']) {
                        $utm = System::getUtmData($order['utm']);
                        $order_info = $order_info ? array_merge($order_info, $utm) : $utm;
                    }

                    $csv = array();
                    foreach ($fields as $key => $field) {
                        if (in_array($field, $add_fields)) {
                            $value = $order_info && isset($order_info[$field]) ? $order_info[$field] : '';
                        } else {
                            if ($field == 'summ') {
                                $value = $summ;
                            } elseif ($field == 'client_surname') {
                                $value = isset($surname['surname']) ? $surname['surname'] : 'Не указано';
                            } elseif ($field == 'type_order') {
                                // здесь надо вычислить смешанный или цифровой или физический товар  в заказе
                                $value = $type_order ? $type_order : 'Не указано';
                            } elseif ($field == 'fin_potok') {
                                $value = isset($fin_potok['org_name']) ? $fin_potok['org_name'] : 'Не указано';
                            } elseif ($field == 'payment_name') {
                                $value = isset($payment_name['name']) ? $payment_name['name'] : 'Не указано';
                            } elseif ($field == 'product_id' && $items) {
                                $value = implode('|',array_column($items,'product_id'));
                            } elseif ($field == 'product_name' && $items) {
                                $value = implode('|',array_column($items,'product_name'));
                            } elseif ($field == 'product_price' && $items) {
                                $value = implode('|',array_column($items,'price'));
                            } elseif ($field == 'create_date' && $items) {
                                $value = date("d.m.Y H:i:s", $order['order_date']);
                            } else {
                                $value = $field == 'payment_date' && $order['payment_date'] ? date("d.m.Y H:i:s", $order['payment_date']) : $order[$field];
                            }
                        }
                        array_push($csv, $value);
                    }
                    fputcsv($fp, $csv,';');
                }

                $write = fclose($fp);
                if ($write){
                    System::redirectUrl("/tmp/orders_{$time}.csv");
                }
            }
        }

        $title = 'Заказы - список';
        require_once (ROOT . '/template/admin/views/orders/index.php');
        return true;
    }


    /**
     * ДОБАВИТЬ ЗАКАЗ ВРУЧНУЮ
     */
    public static function actionAdd()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_orders'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getMainSetting();
        
        if (isset($_POST['add']) && isset($_POST['order_items']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {

            if(!isset($acl['change_orders']))
                System::redirectUrl('/admin/orders');

            $name = htmlentities(mb_substr($_POST['name'], 0, 255));
            $email = htmlentities(trim(strtolower(mb_substr($_POST['email'], 0, 50))));
            $phone = isset($_POST['phone']) ? htmlentities(mb_substr($_POST['phone'],0,50)) : null;
            $index = isset($_POST['index']) ? htmlspecialchars(mb_substr($_POST['index'],0, 8)) : null;
            $city = isset($_POST['city']) ? htmlentities(mb_substr($_POST['city'],0,255)) : null;
            $address = isset($_POST['address']) ? htmlentities(mb_substr($_POST['address'],0,255)) : null;
            
            $status = $_POST['status'];
            $comment = $_POST['admin_comment'];
            $price = intval($_POST['price']);
            
            $sum = intval($_POST['summ']);
            $date = time();
            $sale_id = null;
            $order_items = $_POST['order_items'];
            
            $add = Order::addCustomOrder($order_items[0], $date, $sum, $name, $email, $phone, $city,
                $address, $index, $comment, $sale_id, $partner_id = null, $status, $order_items, $price
            );
            
            if ($add) {
                $log = ActionLog::writeLog('orders', 'add', '', 0, time(), $_SESSION['admin_user'], json_encode($_POST));
                OrderTask::addTask($add, OrderTask::STAGE_ACC_STAT);

                if ($status == 1) {
                    $order = Order::getOrderToAdmin($add);
                    $render = Order::renderOrder($order);
                }
                System::setNotif(true);
                System::redirectUrl('/admin/orders');
            }
            
        }

        $title = 'Заказы - добавить новый';
        require_once (ROOT . '/template/admin/views/orders/add.php');
        return true;
    }
    
    
    // РЕДАКТИРОВАТЬ ЗАКАЗ
    public function actionEdit($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_orders'])) 
            System::redirectUrl('/admin');
        
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        $params = json_decode($setting['params'], true);
        $order = Order::getOrderToAdmin($id);
        $fin_potok = Product::getFinpotokFromOrder($id);


        if (isset($_REQUEST['deleteInstallmentPaymentAction'])) {
            $installmentData = Order::getInstallmentMapData($order['installment_map_id']);
            $payment_actions = unserialize(base64_decode($installmentData['pay_actions']));

            unset($payment_actions[$_REQUEST['deleteInstallmentPaymentAction']]);

            $pay_str = base64_encode(serialize($payment_actions));
            $upd = Installment::updateIntallmentMapPayActions($order['installment_map_id'], $pay_str);
            if ($upd) {
                $log = ActionLog::writeLog('installments', 'delete', 'installment', $id, time(), $_SESSION['admin_user'], json_encode($_REQUEST));
                System::setNotif(true);
                System::redirectUrl("/admin/orders/edit/$id");
            } else {
                System::redirectUrl("/admin/orders/edit/$id", false);
            }
        }
        if (isset($_POST['add_manager'])) { // Если обновление менеджера
            $manager_id = intval($_POST['manager_id']);
            $edit = Order::UpdateOrderManager($id, $manager_id);
            if ($edit) {

                System::setNotif(true);
                System::redirectUrl("/admin/orders/edit/$id");
            }
        }
        
        // Если обновление продукта заказа
        if (isset($_POST['reload_order_item'])) {
            
            $order_item_id = intval($_POST['reload_order_item']);
            $price = isset($_POST['price']) ? intval($_POST['price']) : null;
            $flow_id = isset($_POST['flow_id']) ? intval($_POST['flow_id']) : false;
            
            $update_product = $price !== null ? Order::updatePrice($order_item_id, $id, $price) : false;
            
            $prod_id = isset($_POST['prod_id']) ? intval($_POST['prod_id']) : null;
            if ($prod_id) {
                $update_product = Order::updateProductId($order_item_id, $id, $prod_id, $order['product_id'], $flow_id);
            }
            
            if ($update_product) {
                System::setNotif(true);
                System::redirectUrl("Location: /admin/orders/edit/$id");
            }
        }
        
        // Если удаление продукта из заказа
        if (isset($_POST['order_item_delete']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $order_item = intval($_POST['order_item_delete']);
            $del = Order::deleteOrderItem($id, $order_item);

            if ($del) 
                System::setNotif(true, "Удалено!", 3);

            System::redirectUrl("/admin/orders/edit/$id");
        }

        // Добавить платеж(предоплата)
        if (isset($_POST['add_prepayment']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {

            $installmentData = Order::getInstallmentMapData($order['installment_map_id']);
            $payment_actions = unserialize(base64_decode($installmentData['pay_actions']));

            $predate = strtotime($_POST['prepayment_date']);
            $presumm = $_POST['prepayment_summ'];

            $payment_actions[] = ['summ'=> $presumm, 'date'=> $predate];

            $pay_str = base64_encode(serialize($payment_actions));
            $upd = Installment::updateIntallmentMapPayActions($order['installment_map_id'], $pay_str);

            if ($upd) {
                $log = ActionLog::writeLog('installments', 'add', 'installment', $id, time(), $_SESSION['admin_user'], json_encode($_REQUEST));
                System::setNotif(true, "Платеж успешно добавлен!", 3);
            }
            System::redirectUrl("/admin/orders/edit/$id");
        }
        
        // Если возврат товара из заказа
        if (isset($_POST['refund']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $product_id = intval($_POST['id']);
            $pincode = htmlentities($_POST['pin']);
            $order_item_id = intval($_POST['order_item']);
            $order_item = $order_item_id ? Order::getOrderItem($order_item_id) : null;
            $email = htmlentities($_POST['email']);
            
            $change = Order::ChangeStatus($id, $order_item_id); // меняем статус в таблице _order_items
            
            if ($change) {
                // получаем группы продукта и удаляем их из user_group_map
                $user = User::getUserDataByEmail($email); // данные юзера
                $product = Product::getProductById($product_id); // данные продукта
                if ($product && $product['group_id']) {
                    $delgroups = User::deleteUserGroupsFromList($user['user_id'], $product['group_id']);
                }
    
                // если есть подписка на рассылку - отписываем
                $responder = System::CheckExtensension('responder', 1);
                if ($responder && $product['delivery_sub']) {
                    foreach (unserialize($product['delivery_sub']) as $delivery_sub) {
                        $delsubs = Responder::DeleteSubsRow($email, $delivery_sub);
                    }
                }
    
                // если есть подписка на доступ - останавливаем
                $member = System::CheckExtensension('membership', 1);
                if ($member && $product['subscription_id'] != null) {
                    // удалить планы подписок и всё что с ними связано
                    $delsub = Member::delMemberByEmail($user['user_id'], $product['subscription_id']);
                }
    
                // если начислены авторские - удаляем
                // если в настройках удалять, то если начислены комиссионные партнёрам - удаляем
                $partnership = System::CheckExtensension('partnership', 1);
                if ($partnership) {
                    $del_author_transaction = Aff::deleteAuthorTransaction($id, $product_id);
        
                    $aff_params = unserialize(System::getExtensionSetting('partnership')); // настройки партнёрки
                    if ($aff_params['params']['delpartnercomiss'] == 1) {
                        $del_partner_transaction = Aff::deletePartnerTransaction($id, $product_id);
                    }
                }
    
                // РАСШИРЕНИЕ GetFunnels
                if (System::CheckExtensension('getfunnels', 1)) {
                    GetFunnels::changePayStatus($order['client_email'], $id, 'waiting_for_return', 'returned');
                }

                if ($order_item && $order_item['flow_id']) {
                    Flows::delFlow2OrderItemId($order_item['flow_id'], $order_item['order_item_id']);
                }

                System::setNotif(true);
                System::redirectUrl("/admin/orders/edit/$id");
            }
        }

        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if(!isset($acl['change_orders']))
                System::redirectUrl("/admin");
            
            $order_date = $payment_date = false;
            $expire_date = !empty($_POST['expire_date']) ? strtotime($_POST['expire_date']) : 0;
            $name = $_POST['name'];
            $surname = isset($_POST['surname']) ? $_POST['surname'] : null;
            $email = $_POST['client_email'];
            $phone = $_POST['phone'];
            $city = $_POST['city'];
            $index = $_POST['index'];
            $address = $_POST['address'];
            $comment = $_POST['client_comment'];
            $admin_comment = $_POST['admin_comment'];
            $ship_status = isset($_POST['ship_status']) ? $_POST['ship_status'] : null;
            $crm_status = isset($_POST['crm_status']) ? intval($_POST['crm_status']) : 0;
            $manager_id = $order['manager_id'];
            $order_info = $order['order_info'] ? unserialize(base64_decode($order['order_info'])) : null;

            if (isset($_POST['crm_status']) && $_POST['crm_status'] > 0 ) {
                if ($order['manager_id'] == 0) {
                    $admin = User::getUserById(intval($_SESSION['admin_user']));
                    if($admin['role'] == 'manager') {
                        $manager_id = $admin['user_id'];
                    }
                }
            }

            if (isset($order['installment_map_id']) && $order['installment_map_id'] != 0 && isset($_POST['installment'])) {

                $installmentData = Order::getInstallmentMapData($order['installment_map_id']);
                $payment_actions = unserialize(base64_decode($installmentData['pay_actions']));

                $new_payment_actions = [];
                foreach ($_POST['installment']['payment'] as $payItem) {
                    $predate = strtotime($payItem['date']);
                    if ($payItem['summ'] != '') {
                        $presumm = $payItem['summ'];
                    } else {
                        continue;
                    }
                    $new_payment_actions[] = ['summ'=> $presumm, 'date'=> $predate];
                }

                $pay_str = base64_encode(serialize($new_payment_actions));
                $upd = Installment::updateIntallmentMapPayActions($order['installment_map_id'], $pay_str);
            }

            
            $status = $order['status'];
            
            if ($_POST['change_status'] !== '') {
                $change_status = intval($_POST['change_status']);
                
                switch($change_status) {
                    case 1: // Оплачен
                        $status = 1;
                        $render = Order::renderOrder($order);
                        break;
                    case 97: // Ожидаем возврата
                        $status = 97;
                        break;
                    case 0: // Не оплачен
                        $status = 0;
                        Flows::delFlows2Order($id);
                        break;
                    case 98: // Ложный
                        $status = 98;
                        $cancel = Order::cancelOrderAction($id, $email);
                        break;
                    case 99: // Отменён (удалён)
                        $status = 99;
                        $del = Order::deleteOrder($id, null);
                        $cancel = Order::cancelOrderAction($id, $email);
                        break;
                }
                
            }

            if ($surname !== null && $order_info) {
                $order_info['surname'] = $surname;
            }

            $upd = Order::updateOrderToAdmin($id, $name, $email, $phone, $city, $index, $address,
                $status, $ship_status, $comment, $admin_comment, $order_date, $payment_date, $expire_date,
                $crm_status, $manager_id, base64_encode(serialize($order_info))
            );

            if ($upd) {
                $log = ActionLog::writeLog('orders', 'edit', 'order', $id, time(), $_SESSION['admin_user'], json_encode($_POST));

                System::setNotif(true);
                System::redirectUrl("/admin/orders/edit/$id");
            }
        }

        $title = 'Заказы - редактировать заказ';
        require_once (ROOT . '/template/admin/views/orders/edit.php');
        return true;
    }
    
    
    // УДАЛИТЬ ЗАКАЗ
    public function actionDel($id)
    {
        $acl = self::checkAdmin();
        if(!isset($acl['show_orders']))
            System::redirectUrl("/admin");

        if(!isset($acl['del_orders'])) {
            System::setNotif(false);
            System::redirectUrl("/admin/orders");
        }

        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $order = Order::getOrder($id);
		$setting = System::getSetting();
        $params = json_decode($setting['params'], true);
        
        if(isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']){
            
            $ondelete = isset($params['allow_admin_to_delete_orders']) && $params['allow_admin_to_delete_orders'] == 1 ? $params['allow_admin_to_delete_orders'] : null;
            
            $del = Order::deleteOrder($id, $ondelete);
            
            if($del) {
                $log = ActionLog::writeLog('orders', 'delete', 'order', $id, time(), $_SESSION['admin_user'], $order['order_date']);
                System::setNotif(true, "Заказ удален!");
                System::redirectUrl("/admin/orders");

            } else {
                System::setNotif(false);
                System::redirectUrl("/admin/orders/edit/$id");
            }
        }
    }


    public static function actionAddProduct($order_id) {
        if (isset($_POST['add_product']) && isset($_POST['product_id']) && isset($_POST['product_price'])) {
            $acl = self::checkAdmin();
            if (!isset($acl['change_orders'])) 
                System::redirectUrl('/admin/orders');

            $setting = System::getSetting();
            $product_price = (int)$_POST['product_price'];
            $product_id = (int)$_POST['product_id'];
            $product = Product::getProductData($product_id);
            $order = Order::getOrderToAdmin($order_id);

            if (!$order || !$product) {
                require_once (ROOT . '/template/'.$setting['template'].'/404.php');
            }

            $order_items = Order::getOrderItems($order_id);
            $number = count($order_items) + 1;
            $cast = $order_items[0]['cast'];

            $res = Order::addOrderItem($order_id, $product_id, $product['type_id'], $number, $product_price, $cast, $product['product_name'], $order['status']);
            if ($res) {
                $amount = Order::getOrderTotalSum($order_id);
                $upd = Order::updateOrderSum($order_id, $amount);

                if ($upd) 
                    System::setNotif(true);

                System::redirectUrl("/admin/orders/edit/$order_id");
            }
        }
    }

    
    /*public function actionDelPartner() {
        $acl = self::checkAdmin();

        if (!isset($acl['show_orders'])) 
            System::redirectUrl("/admin");

        if ($_POST['delpartner'] == true) {
            $del_partner_transaction = Aff::deletePartnerTransaction($_POST['order_id']);
            $del_partner_from_order = Aff::deletePartnerFromOrder($_POST['order_id']);
            $data = ['success' => true];
            header('Content-Type: application/json');
            echo json_encode($data);
        }
    }*/
    public function actionDelPartner()
{
    // 0. доступ
    $acl = self::checkAdmin();
    if (!isset($acl['show_orders'])) {
        System::redirectUrl('/admin');
    }

    // 1. валидация входящих данных
    $orderId   = (int)($_POST['order_id']   ?? 0);
    $partnerId = (int)($_POST['partner_id'] ?? 0);

    if (!$orderId || !$partnerId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'bad_params']);
        return;
    }

    // 2. бизнес-логика
    $ok1 = Aff::deletePartnerTransaction($orderId, $partnerId);
    $ok2 = Aff::deletePartnerFromOrder($orderId, $partnerId);

    // 3. ответ
    header('Content-Type: application/json');
    echo json_encode(['success' => ($ok1 && $ok2)]);
}


    public function actionFastFilter() {

        $id = $_REQUEST['id'] ?? null;
        $client = $_REQUEST['client'] ?? null;
        $product_id = $_REQUEST['product'] ?? null;
        $status = $_REQUEST['status'] ?? null;



        if (!isset($id) && !isset($client) && !isset($product_id) && !isset($status)) {
            http_response_code(404);
            die(json_encode([
                "status" => false,
                "message" => "По запросу ничего не найдено!",
            ]));
        }

        $clauses = [];

        if (isset($id)) {

            if (strlen($id) > 8) {

                if (strlen($id) == 10) {
                    $order_list[0] = Order::getOrderData($id);
                } else {
                    $order_list[0] = false;
                }

                if (!$order_list[0]) {
                    http_response_code(404);
                    die(json_encode([
                        "status" => false,
                        "message" => "По запросу ничего не найдено!",
                    ]));
                }

            } else {
                $id = intval($id);
                $clauses[] = " o.order_id = '" . $id . "'";
            }
        }

        if (!isset($order_list)) {

            if (isset($client)) {//почта заказчика или его имя

                if (filter_var($client, FILTER_VALIDATE_EMAIL)) {
                    $clauses[] = " o.client_email = '" . $client . "'";
                } else {
                    $clauses[] = " (o.client_name LIKE '%" . $client . "%' OR o.client_email LIKE '%" . $client . "%')";
                }
            }

            if (isset($product_id) && $product_id != 0) {
                $clauses[] = " p.product_id = '" . $product_id . "'";
            }

            if (isset($status)) {
                $clauses[] = " o.status = '" . $status . "'";
            }

            $clauses = implode(' AND', $clauses);

            $order_list = Order::getOrdersWithConditions($clauses);

        }

        if (!$order_list) {
            http_response_code(404);
            echo json_encode([
                "status" => false,
                "message" => "По запросу ничего не найдено!",
            ]);
            die();
        }

        $setting = System::getSetting();
        $createfunc = true;

        foreach ($order_list as $order):
            if ($order) {
                include(ROOT . "/template/admin/views/orders/order_card.php");
            }
        endforeach;

        die();
    }

    public function actionPrepaymentAdd(int $orderId) {
        header('Content-Type: application/json');
        $order = Order::getOrder($orderId);

        if (!isset($_SESSION["admin_user"])) {
            http_response_code(401);
            die(json_encode([
                'status' => false,
                'message' => "Не авторизован",
            ]));
        }

        if (!$order) {
            die(json_encode([
                'status' => false,
                'message' => "Заказа не существует"
            ]));
        }

        if (!isset($_REQUEST['sum'])) {
            die(json_encode([
                'status' => false,
                'message' => "Не указана сумма предоплаты",
            ]));
        }

        if ($order['deposit']) {
            $updateDeposit = json_decode($order['deposit'],true);
        } else {
            $updateDeposit = [];
        }
        $time = time();
        $newPayment = [
            'sum' => intval($_REQUEST['sum']),
            'time' => $time,
            'userId' => $_SESSION["admin_user"],
        ];

        array_push($updateDeposit, $newPayment);


        $result = Order::updateOrderDeposits($orderId, json_encode($updateDeposit));

        http_response_code(201);
        die(json_encode([
            'status' => $result,
            'time' => date('j.m.Y'),
        ]));
    }


    /**
     *ПРАВКА СУММ У ЗАКАЗОВ
     */
    public function actionFixOrdersSum() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_orders'])) {
            System::redirectUrl('/admin');
        }

        $total_order = Order::countOrders();

        if ($total_order) {
            for ($i = 1; $i <= $total_order; $i++) {
                $order_list = Order::getOrderAdminList($i, 500, 1);
                if ($order_list) {
                    foreach ($order_list as $order) {
                        if ($order['installment_map_id']) {
                            continue;
                        }

                        $total = Order::getOrderTotalSum($order['order_id']);
                        if ($total != $order['summ']) {
                            Order::updateOrderSum($order['order_id'], $total);
                        }
                    }
                }
            }

            exit('Суммы заказов для статистики успешно поправлены');
        } else {
            exit('Нет заказов для исправления цен');
        }
    }
}