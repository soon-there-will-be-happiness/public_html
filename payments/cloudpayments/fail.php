<?if (!defined('CURR_VER')) {
    require_once "{$_SERVER['DOCUMENT_ROOT']}/index.php";
} else {
    if (!empty($_REQUEST)) {
        $payment = Order::getPaymentSetting('cloudpayments');
        $params = unserialize(base64_decode($payment['params']));

        $email = $_REQUEST['AccountId'];
        $reason = $_REQUEST['Reason'];
        $reasonCode = $_REQUEST['ReasonCode'];
        $SubscriptionId = $_REQUEST['SubscriptionId'];
        $CardLastFour = $_REQUEST['CardLastFour'];

        $user = User::getUserDataByEmail($email);
        $phone = !empty($user['phone']) ? trim($user['phone']) : false;
        $url_update = 'https://orders.cloudpayments.ru/s/update/'.$SubscriptionId; // ссылка на перепривязку карты

        $replace = array(
            '[CARD]' => $CardLastFour,
            '[LINK]' => $url_update,
        );

        if ($reasonCode == 5054 && $params['expiredCard'] == 1) {
            $message = $params['expiredsms'];
            $text = strtr($message, $replace);

            if ($phone) {
                SMSC::sendSMS($phone, $text, null, 0, 0, 0, false, 1);
            }
        }


        if ($reasonCode == 5051 && $params['nomoney'] == 1) {
            $message = $params['nomoneysms'];
            $text = strtr($message, $replace);

            if ($phone) {
                SMSC::sendSMS($phone, $text, null, 0, 0, 0, false, 1);
            }
        }

        //https://orders.cloudpayments.ru/s/update/sc_db973e8ba763ee95319f0d0e633cc
        exit('{"code":0}');
    }
}