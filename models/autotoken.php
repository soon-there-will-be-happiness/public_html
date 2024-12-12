<?php defined('BILLINGMASTER') or die;

class AutoToken {

    /**
     * Выполняет cURL-запрос
     */
    private static function sendCurlRequest(string $url, array $headers, array $data = [], string $method = 'POST'): array {
        $ch = curl_init($url);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['response' => $response, 'httpCode' => $httpCode];
    }

    /**
     * Проверяет и обновляет токен
     */
    public static function checkToken(string $login, string $password): ?string {
        $payment = Order::getPaymentDataForAdmin(25);
        $params = unserialize(base64_decode($payment['params']));
        $apiUrl = rtrim($params['url2'], '/') . '/getToken';

        $data = [
            'login' => $login,
            'pass' => $password
        ];
        $headers = ["Content-Type: application/json"];

        $result = self::sendCurlRequest($apiUrl, $headers, $data);

        if ($result['httpCode'] === 200) {
            $response = json_decode($result['response'], true);
            if (empty($response['error'])) {
                $params['token2'] = $response['token'];
                $params['token_date'] = date('Y-m-d');
                Order::EditPaymentsParams(25, base64_encode(serialize($params)));
                return $response['token'];
            } else {
                LogEmail::PaymentError(json_encode($response['error']), "atol/token.php", "update token");
            }
        } else {
            LogEmail::PaymentError($result['httpCode'], "atol/token.php", "crytical");
        }

        return null;
    }

    /**
     * Проверяет, истек ли токен
     */
    private static function isTokenExpired(?string $tokenDate): bool {
        if (empty($tokenDate)) {
            return true;
        }

        try {
            $currentDate = new DateTime();
            $tokenDateTime = new DateTime($tokenDate);
            return $currentDate->diff($tokenDateTime)->days > 1 || $tokenDateTime > $currentDate;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Отправляет чек
     */
    public static function sendCheck(array $order): void {
        $payment = Order::getPaymentDataForAdmin(25);
        $params = unserialize(base64_decode($payment['params']));

        if (self::isTokenExpired($params['token_date'])) {
            $params['token2'] = self::checkToken($params['login'], $params['password']);
        }

        $orderItems = Order::getOrderItems($order['order_id']);
        $items = self::prepareItems($orderItems, $order['partner_id'],$order['summ']);

        $data = [
            "receipt" => [
                "items" => $items,
                "total" => intval(1),
                "client" => [
                    "email" => $order['client_email'],
                    "phone" => $order['client_phone'],
                ],
                "company" => [
                    "inn" => $params['inn'],
                    "sno" => $params['sno'],
                    "email" => $params['email'],
                    "payment_address" => $params['payment_address'],
                ],
                "payments" => [[
                    "sum" => intval(1),
                    "type" => 1,
                ]],
            ],
            "timestamp" => date("d.m.Y H:i:s"),
            "external_id" => $order['order_date'],
        ];

        $headers = [
            "Content-Type: application/json",
            "Token: {$params['token2']}",
        ];

        $result = self::sendCurlRequest(
            rtrim($params['url2'], '/') . "/{$params['group_code']}/sell",
            $headers,
            $data
        );

        $paymentData = json_decode($result['response'], true);

        if (!empty($paymentData['error'])) {
            LogEmail::PaymentError(json_encode($paymentData['error']) . "\n     " . json_encode($data), "atol/result.php", "sell");
        } else {
            PointDB::updateUUID($order['order_id'], $paymentData['uuid']);
        }
    }

    /**
     * Подготавливает список товаров
     */
    private static function prepareItems(array $orderItems, int $partnerId, $summ): array {
        $items = [];
        $setting = System::getSetting();

        foreach ($orderItems as $item) {
    
            if ($partnerId !== 0) {
                $partner = Aff::getPartnerReq($partnerId);
                $user = User::getUserById($partner['user_id']);
                $data = unserialize($partner['requsits']);
                $phone = preg_replace('/\s+/', '', $user['phone']);
                $items[] = [
                    "sum" =>intval(1),
                    "vat" => ["type" => "none"],
                    "name" => $item['product_name'],
                    "price" => intval(1),
                    "measure" => 0,
                    "quantity" =>  1,
                    "payment_method" => "full_prepayment",
                    "payment_object" => 1,
                    "agent_info" => [
                        "type" => "another",
                    ],
                    "supplier_info" => [
                        "phones" => [(string) $phone,
                        "name" => $data['rs']['off_name'],
                        "inn" => (string)$data['rs']['inn'],
                    ],
                ];
            }
        }

        return $items;
    }
}
