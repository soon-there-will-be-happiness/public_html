<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));


$callback_data = json_decode(file_get_contents('php://input'), true);
Log::add(0,'Curl error', ["error" => $callback_data],'return.log');

if (isset($callback_data['orderId']) && isset($callback_data['status'])) {
    $order_id = intval($callback_data['orderId']);
    $status = $callback_data['status'];
    $order = Order::getOrderDataByID($order_id,100);
    if ($status =="success") {
        Order::renderOrder($order);
        $order = Order::getOrderDataByID($order_id,100);
        $order_items = Order::getOrderItems($order['order_id']);
        $out_summ =$order['summ'];
        $items = array();
        $token = $params['token'];
        $api_url = $params['url2'];
        $login= $params['login'];
        $pass=$params['password'];


        $inn=$params['inn'];
        $sno=$params['sno']; 
        $email=$params['email'];   $group_code=$params['group_code'];
        $payment_address=$params['payment_address'];
        $token=AutoToken::CheckToken($login, $pass);$token = $params['token2'];
        foreach($order_items as $item){
            $items[] = [
                "sum" => $order['summ'],
                "vat" => ["type" => "none"],
                "name" => $item['product_name'],
                "price" => $item['price'],
                "measure" => 0,
                "quantity" => 1,
                "payment_method" => "full_prepayment",
                "payment_object" => 1
            ];
        }
        $data = [
            "receipt" => [
                "items" => $items,
                "total" => $order['summ'],
                "client" => [
                    "email" => $order['client_email'],
                    "phone" =>  $order['client_phone'],
                ],
                "company" => [
                    "inn" => $inn,
                    "sno" =>  $sno,
                    "email" => $email,
                    "payment_address" => $payment_address
                ],
                "payments" => [
                    [
                        "sum" => $order['summ'],
                        "type" => 1
                    ]
                ]
            ],
            "timestamp" => date("d.m.Y H:i:s"),
            "external_id" =>$order['order_date'],
        ];
        $headers = [
            "Content-Type: application/json, charset=utf-8",
            "Token: $token",
        ];
        $ch = curl_init( $api_url."/".$group_code ."/sell");

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        $payment_data = json_decode($response, true);

        if (is_array($payment_data['error'])) {
            LogEmail:: PaymentError( json_encode($payment_data['error']),"atol/result.php","sell");
        }
        Cyclops::Run($order);
                echo "OK $order_id";
            } elseif ($status == 'fail') {
                echo "Payment failed";
            } else {
                echo "Unknown status";
            }
    } else {
        echo "Invalid callback data";
    }
    System::redirectUrl($setting['script_url'] . '/payments/atol/result');
?>
