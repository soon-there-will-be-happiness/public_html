<?php defined('BILLINGMASTER') or die; 

class Recurrent {
    
  
    // Синхронизация подписок с Cloudpayments
    public static function updateTimeSubsMap($subscription_id)
    {
        $payment = Order::getPaymentSetting('cloudpayments');
        $params = unserialize(base64_decode($payment['params']));
        
        if($params['sync'] == 0) return false;
        
        $public_id = $params['public_id'];
        $pass_api = $params['pass_api'];
        $setting = System::getSetting();
        
        $url = 'https://api.cloudpayments.ru/subscriptions/get';
        $response = [
            'Id' => $subscription_id,
        ];
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_USERPWD, sprintf('%s:%s', $public_id, $pass_api));
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($response));
        $result = curl_exec($curl);
        curl_close($curl);
        
        $result = json_decode($result, 1);

        if($result['Model']['Status'] != 'Active') return false;
        
        // Получаем дату след списания в КП
        // Прибавляем 6 часов 
        // Обновляем подписку
        
        $cloud_date = strtotime($result['Model']['NextTransactionDateIso']);
        $end_date = $cloud_date + 21600;
        $status = 1;

        /*   Новый метод  */
        $data = json_encode($result['Model']);
        $now = time();
        
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS."member_maps SET end = :end_date, status = :status WHERE subscription_id = '$subscription_id'";
        $result = $db->prepare($sql);
        $result->bindParam(':end_date', $end_date, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->execute();
        
        /*$sql = 'INSERT INTO '.PREFICS.'member_sync_log (subscription_id, date, data) 
            VALUES (:subs_id, :date, :data)';
        
        $result2 = $db->prepare($sql);
        $result2->bindParam(':subs_id', $subscription_id, PDO::PARAM_STR);
        $result2->bindParam(':date', $now, PDO::PARAM_INT);
        $result2->bindParam(':data', $data, PDO::PARAM_STR);
        $result2->execute();*/
        
        
        return $result;
        
        /*  конец новый метод */
             
    }
}