<?php


class SberBank {

    const API_URL = "https://securepayments.sberbank.ru/payment/rest/";//https://3dsec.sberbank.ru/payment/rest/

    private $login = "";
    private $pass = "";

    private $requestData;
    private $orderBundle;
    private $requestTo = "";


    private $taxSystem = 0;


    public function __construct($login, $pass) {
        $this->login = $login;
        $this->pass = $pass;
    }

    public function createOrder($order, $total) {
        $this->requestData = [];
        $this->orderBundle = [];

        $this->requestData["orderNumber"] = $order['order_id']; // номер заказа из sm
        $this->requestData["amount"] = $total * 100; // сумма в копейках

        $this->requestData["returnUrl"] = System::getSetting()['script_url']."/payments/sberbank/success.php?orderidsm=".$order['order_id']; //успешная оплата
        $this->requestData["failUrl"] = System::getSetting()['script_url']."/payments/sberbank/fail.php?orderidsm=".$order['order_id'];

        $this->requestData["description"] = "Оплата заказа №".$order['order_date'];

        $this->requestData["email"] = $order['client_email'];
        $this->requestData["taxSystem"] = $this->taxSystem;


        !$order['client_phone'] ?? $this->requestData["phone"] = $order['client_phone'];

        $this->requestTo = "register.do";

        return $this;
    }

    public function addOrderBundle(array $order, $custom_items = null) {
        $items = $custom_items ?? Order::getOrderItems($order['order_id']);

        $cart = [];
        foreach ($items as $item) {//Содержимое заказа
            $cart[] = [
                'positionId' => $item["order_item_id"],
                'name' => html_entity_decode($item['product_name']),
                'quantity' => [
                    'value' => 1,
                    'measure' => 'шт'
                ],
                'itemAmount' => $item['price'] * 100,
                'itemCode' => $item['product_id'],
                //'tax' => ['taxType' => 0, 'taxSum' => 0],
                'itemPrice' => $item['price'] * 100,
            ];
        }

        $this->orderBundle["cartItems"]['items'] = $cart;

        $this->orderBundle["customerDetails"] = [//Информация о клиенте
            "email" => $order['client_email']
        ];

        return $this;
    }

    public function getRequestData() {
        $this->requestData["userName"] = $this->login;
        $this->requestData["password"] = $this->pass;

        if (!empty($this->orderBundle)) {
            $this->requestData["orderBundle"] = json_encode($this->orderBundle, JSON_UNESCAPED_UNICODE);
        }

        return $this->requestData;
    }


    public function send() {

        $requestData = $this->getRequestData();


        $result = System::curl(self::API_URL . $this->requestTo, $requestData);

        $response = json_decode($result['content'], true) ?? false;

        if (!$response) {
            return false;
        }

        return $response;
    }


    public function getOrderStatus($sber_order_id) {
        $this->requestData = [];
        $this->requestData["orderId"] = $sber_order_id;

        $this->requestTo = "getOrderStatusExtended.do";

        return $this->send();
    }

    public static function generateSign($data, $openKey) {//Симметричная криптография
        if (empty($data)) {
            return false;
        }
        $checksum = $data['checksum'] ?? false;
        unset($data['checksum']);

        ksort($data);

        $flat = "";
        foreach ($data as $key => $value) {
            $flat .= $key.";".$value.";";
        }

        return strtoupper(hash_hmac('sha256' , $flat , $openKey));
    }

}