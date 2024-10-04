<?php defined('BILLINGMASTER') or die;

class atolapiController {

    public function SendDataToAtol($url,$data){
        $headers = ['Content-Type: application/json']; // заголовки нашего запроса
        $data_json = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        $result = curl_exec($curl); // результат POST запроса
       return $result;
    }
}