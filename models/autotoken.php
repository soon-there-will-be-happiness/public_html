<?php defined('BILLINGMASTER') or die;

Class AutoToken{
    public static function CheckToken(string $token, string $login,string $password) {
        $payment = Order::getPaymentDataForAdmin(25);
   
        $params = unserialize(base64_decode($payment['params']));

        $api_url = 'https://online.atol.ru/possystem/v5/getToken';
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

            $error = $data['data']['error'] ?? '';
            if($error==null||$error==''){
           

                return false;
            }
            else
            {
                $params['token']=$data['data']['token'];
                $params = base64_encode(serialize( $params));

                return $data['data']['token'] ;
            }
        } else {
            return false;
        }
    }
}?>