<?php defined('BILLINGMASTER') or die;

class AutoToken{

    
    public static function CheckToken( string $login,string $password) {
        $payment = Order::getPaymentDataForAdmin(25);

        $params = unserialize(base64_decode($payment['params']));
        $api_url = $params['url2'];
        $api_url =    $api_url .'/getToken';
        $data = [
            'login' => $login,
            'pass' => $password
        ];
        $headers = [
            "Content-Type: application/json"
        ];
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        curl_close($ch);
        if ($http_code == 200) {
            $data = json_decode($response, true);

            $error = $data['error'] ?? '';
            if($error==null||$error==''){

                $params['token2']=$data['token'];
                $params['token_date'] = date('Y-m-d');
                $params = base64_encode(serialize( $params));
                $edit = Order::EditPaymentsParams(25, $params);
                return $data['token'] ;
            }
            else
            {
                LogEmail:: PaymentError( json_encode($response['error']), "atol/token.php","update token");
                return false;
            }
        } else {
            LogEmail:: PaymentError( $http_code, "atol/token.php","crytical");
            return false;
        }
    }


    public static function SendCheck($order){
        $payment = Order::getPaymentDataForAdmin(25);

        $params = unserialize(base64_decode($payment['params']));
        $items = array();
        $token = $params['token'];
        $api_url = $params['url2'];
        $login= $params['login'];
        $pass=$params['password'];
        $order_items = Order::getOrderItems($order['order_id']);

        $inn=$params['inn'];
        $sno=$params['sno'];
        $email=$params['email'];
        $group_code=$params['group_code'];
        $payment_address=$params['payment_address'];
        $currentDate = new DateTime();
        $isTokenExpired = true;
        if (!empty($params['token_date'])) {
            try {
                $tokenDate = new DateTime($params['token_date']);
                $dateDiff = $currentDate->diff($tokenDate)->days;
                if ($dateDiff <= 1 && $tokenDate <= $currentDate) {
                    $isTokenExpired = false;
                }
            }
            catch (Exception $e) {
                $isTokenExpired = true;
            }
        } else {
            $isTokenExpired = true;
        }
        if ($isTokenExpired) {
            $token=AutoToken::CheckToken($login, $pass);
        }
        $token=AutoToken::CheckToken($login, $pass);
        $token = $params['token2'];
        if($order['partner_id']!=0){
            $partner = Aff::getPartnerReq($order['partner_id']);
            $serializedData = $partner['requsits'];
            $data = unserialize($serializedData);
        }
        foreach($order_items as $item){
    $items[] = [
                "sum" => intval($order['summ']),
                "vat" => ["type" => "none"],
                "name" => $item['product_name'],
                "price" => intval($item['price']),
                "measure" => 0,
                "quantity" => 1,
                "payment_method" => "full_prepayment",
                "payment_object" => 1,
               
            ];

            $supplier_info="";
            if($order['partner_id']!=0){
                $partner = Aff::getPartnerReq($order['partner_id']);
                $user=User::getUserById( $partner['user_id']);
                $serializedData = $partner['requsits'];
                $data = unserialize($serializedData);
                   $items[] = [
                "sum" => intval($order['summ']),
                "vat" => ["type" => "none"],
                "name" => $item['product_name'],
                "price" => intval($item['price']),
                "measure" => 0,
                "quantity" => 1,
                "payment_method" => "full_prepayment",
                "payment_object" => 1,
               "agent_info"=>[
                        "type"=> "another",
                        "paying_agent"=>
                        [ 
                            "operation"=> "Партнер", 
                            "phones"=>[(string)$user['phone']],],
                            "receive_payments_operator"=> 
                            [ 
                                "phones"=> [(string)$user['phone'],
                            ],
                        ],
                    ],
                    "supplier_info" => [
                        "phones" => [(string)$user['phone']],
                        "name" => $data['rs']['off_name'],
                        "inn" => (string)$data['rs']['inn'],
                    ],
                    
            ];

            }
        
        }
        $data = [
            "receipt" => [
                "items" => $items,
                "total" => intval($order['summ']),
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
                        "sum" => intval($order['summ']),
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
            
            LogEmail:: PaymentError( json_encode($payment_data['error'])."\n".json_encode($data),"atol/result.php","sell");
        }else{
            PointDB::updateUUID($order['order_id'],$payment_data['uuid']);
        }
    }
}?>