<?if (!defined('CURR_VER')) {
    require_once "{$_SERVER['DOCUMENT_ROOT']}/index.php";



    if (isset($_REQUEST["orderId"]) && isset($_REQUEST['orderidsm'])) {
        $smId = intval($_REQUEST['orderidsm']);

        $order = Order::getOrder($smId);

        if (!$order) {


            $orderInfo = unserialize(base64_decode($order['order_info']));

            if (isset($orderInfo['sberbankOrderId'])) {

                    if ($orderInfo['sberbankOrderId'] === $_REQUEST['orderId']) {

                            require_once ROOT."/payments/sberbank/load.php";

                            $sberParams = Order::getPaymentSetting("sberbank");
                            $sberParams = unserialize(base64_decode($sberParams['params'])); //login, password

                                if ($sberParams['login'] != "" || $sberParams['password'] != "") {
                                    $sber = new SberBank($sberParams['login'], $sberParams['password']);

                                    $result = $sber->getOrderStatus($orderInfo['sberbankOrderId']);//Получить статус заказа

                                    if ($result) {
                                            if (isset($result["orderStatus"]) && $result["orderStatus"] == 2) {//Заказ оплачен
                                                //dd
                                            }
                                    }

                                }

                    }


            }
        }


    }
}
