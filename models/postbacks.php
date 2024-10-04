<?php defined('BILLINGMASTER') or die;

class PostBacks {

    const ACT_TYPE_USER_REGISTRATION = 1;
    const ACT_TYPE_CREATE_ORDER = 2;
    const ACT_TYPE_PAY_ORDER = 3;


    /**
     * @param $act
     * @param $partner_id
     * @param $client_name
     * @param $client_email
     * @param $client_phone
     * @param $user_id
     * @param null $order
     * @param null $order_sum
     */
    public static function sendData($act, $partner_id, $client_name, $client_email, $client_phone, $user_id,
                                    $order = null, $order_sum = null) {
        $req = Aff::getPartnerReq($partner_id);
        $params = $req && isset($req['postbacks']) ? json_decode($req['postbacks'], true) : null;

        if ($params) {
            $url = null;

            switch ($act) {
                case self::ACT_TYPE_USER_REGISTRATION:
                    $url = $params['register'];
                    break;
                case self::ACT_TYPE_CREATE_ORDER:
                    $url = $order_sum > 0 || !isset($params['only_paid']) ? $params['add_order'] : null;
                    break;
                case self::ACT_TYPE_PAY_ORDER:
                    $url = $order_sum > 0 || !isset($params['only_paid']) ? $params['pay_order'] : null;
                    break;
            }

            if ($url) {
                if (isset($order)) {
                    $utmStr = $order['utm'];
                    $firstLetter = $utmStr[0];
                    if ($firstLetter == '?') {
                        $utmStr = substr($utmStr, 1);
                    }
                    mb_parse_str($utmStr, $utm);
                }
                $replace = [
                    '{NAME}' => urlencode(trim($client_name)),
                    '{EMAIL}' => trim($client_email),
                    '{PHONE}' => trim($client_phone),
                    '{USER_ID}' => $user_id,
                    '{ORDER_ID}' => $order ? $order['order_id'] : '',
                    '{ORDER_NUM}' => $order ? $order['order_date'] : '',
                    '{SUMM}' => $order_sum !== null ? $order_sum : '',
                ];
                if (isset($order) && is_array($utm)) {
                    $replace['{UTM_SOURCE}'] = $utm['utm_source'] ?? '';
                    $replace['{UTM_MEDIUM}'] = $utm['utm_medium'] ?? '';
                    $replace['{UTM_CAMPAIGN}'] = $utm['utm_campaign'] ?? '';
                    $replace['{UTM_CONTENT}'] = $utm['utm_content'] ?? '';
                    $replace['{UTM_TERM}'] = $utm['utm_term'] ?? '';
                    $replace['{UTM_REFERRER}'] = $utm['utm_referrer'] ?? '';
                }

                $url = strtr($url, $replace);
            }
            $result = System::curl($url);
        }
    }
}