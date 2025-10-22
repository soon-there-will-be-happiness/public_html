<?php defined('BILLINGMASTER') or die;

class PointSubscriptionService {
    
    /**
     * Проверяет статусы всех неоплаченных подписок и обновляет их
     */
    public static function checkAllSubscriptionsStatus() {
        // Получаем все неоплаченные записи
        $pendingRecords = PointDB::getPendingRecords();
        
        if (empty($pendingRecords)) {
            return ['success' => true, 'message' => 'Нет неоплаченных подписок для проверки'];
        }
        
        $results = [];
        $updatedCount = 0;
        $errors = [];
        
        foreach ($pendingRecords as $record) {
            $result = self::checkSingleSubscriptionStatus($record);
            
            if ($result['success'] && $result['status_updated']) {
                $updatedCount++;
                self::processPaidOrder($record['order_id']);
            }
            //TODO бзаимодействие с бенефициаром

            $order = Order::getOrderDataByID($pendingRecords["order_id"],100);
            $partner = AFF::getPartnerReq($order['partner_id']);
            $pay= Order::getCyclopPaymentID($order['partner_id']);
            if(!$pay){
                //todo создать бенефициара
            }



            $transaction = AFF::getPartnerTransactionReq($order['partner_id'], $order['order_id']);
            $serializedData = $partner['requsits'];
            $data = unserialize($serializedData);


            if (!$result['success']) {
                $errors[] = "Order {$record['order_id']}: " . $result['message'];
            }
            
            $results[$record['order_id']] = $result;
        }
        
        return [
            'success' => true,
            'updated_count' => $updatedCount,
            'total_checked' => count($pendingRecords),
            'errors' => $errors,
            'details' => $results
        ];
    }
    
    /**
     * Проверяет статус одной подписки по operationId
     */
    public static function checkSingleSubscriptionStatus($record) {
        // Получаем параметры из записи или из связанных данных
      $payment = Order::getPaymentSetting('point');
      $params = unserialize(base64_decode($payment['params']));
        
        if (!$params || !isset($params['token']) || !isset($params['api_url'])) {
            return [
                'success' => false, 
                'message' => 'Не найдены параметры для запроса (token или api_url)'
            ];
        }
        
        $token = $params['token'];
        $api_url = $params['api_url'];
        $operationId = $record['operationId'];
        
        if (empty($operationId)) {
            return [
                'success' => false, 
                'message' => 'Отсутствует operationId для проверки статуса'
            ];
        }
        
        // Выполняем запрос к API
        $response = self::makeStatusApiRequest($api_url, $operationId, $token);
        
        if (!$response['success']) {
            return $response;
        }
        
        // Обрабатываем ответ и обновляем статус если нужно
        return self::processStatusResponse($response['data'], $record);
    }
    

    /**
     * Выполняет запрос к API для проверки статуса подписки
     */
    private static function makeStatusApiRequest($api_url, $operationId, $token) {
        $curl = curl_init();
        
        // Формируем URL для проверки статуса конкретной подписки
        $url = $api_url . '/subscriptions/' . urlencode($operationId) . '/status';
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return ['success' => false, 'message' => 'CURL Error: ' . $error];
        }
        
        if ($http_code != 200) {
            return [
                'success' => false, 
                'message' => 'HTTP Error: ' . $http_code, 
                'response' => $response
            ];
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false, 
                'message' => 'JSON decode error: ' . json_last_error_msg()
            ];
        }
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Обрабатывает ответ API и обновляет статус подписки
     */
    private static function processStatusResponse($apiData, $record) {
        if (!isset($apiData['Data']['status'])) {
            return [
                'success' => false, 
                'message' => 'Invalid API response structure: status not found'
            ];
        }
        
        $status = $apiData['Data']['status'];
        $orderId = $record['order_id'];
        
        // Определяем, нужно ли обновлять статус на "оплачено"
        $shouldUpdate = self::shouldUpdateStatus($status);
        
        $result = [
            'success' => true,
            'subscription_status' => $status,
            'status_updated' => false,
            'order_id' => $orderId,
            'operationId' => $record['operationId']
        ];
        
        if ($shouldUpdate) {
            // Обновляем статус в базе данных
            $success = PointDB::updateStatusToTrue($orderId);
            
            if ($success) {
                $result['status_updated'] = true;
                $result['message'] = "Статус подписки обновлен на 'оплачено'";
                
                // Логируем успешное обновление
                self::logStatusUpdate($orderId, $status);
            } else {
                $result['success'] = false;
                $result['message'] = 'Ошибка при обновлении статуса в базе данных';
            }
        } else {
            $result['message'] = "Статус подписки не требует обновления: {$status}";
        }
        
        return $result;
    }
    
    /**
     * Определяет, нужно ли обновлять статус на "оплачено"
     */
    private static function shouldUpdateStatus($status) {
        $activeStatuses = ['Active', 'Completed'];
        
        return in_array($status, $activeStatuses);
    }
    
    /**
     * Логирует обновление статуса
     */
    private static function logStatusUpdate($orderId, $status) {
        error_log("[" . date('Y-m-d H:i:s') . "] Order {$orderId} status updated to paid. Subscription status: {$status}");
    }
    
    /**
     * Обрабатывает оплаченный заказ - выполняет дополнительные действия
     */
    private static function processPaidOrder($order_id) {
        try {
            // Получаем данные заказа
            $order = Order::getOrder($order_id);
            
            if (!$order) {
                error_log("Order {$order_id} not found after payment success");
                return false;
            }
            
            // Рендерим заказ
            Order::renderOrder($order);
            
            // Обновляем статус в PointDB (уже сделано выше, но для надежности)
            PointDB::updateStatusToTrue($order_id);
            
            // Отправляем чек
            AutoToken::sendCheck($order);
            
            // Логируем успешное выполнение
            error_log("[" . date('Y-m-d H:i:s') . "] Order {$order_id} processed successfully: rendered and check sent");
            
            return true;
            
        } catch (Exception $e) {
            // Логируем ошибку, но не прерываем выполнение для других заказов
            error_log("[" . date('Y-m-d H:i:s') . "] Error processing order {$order_id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Проверяет статус конкретной подписки по order_id
     */
    public static function checkSubscriptionByOrderId($orderId) {
        $record = PointDB::findRecordByOrderId($orderId);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Запись не найдена'];
        }
        
        $result = self::checkSingleSubscriptionStatus($record);
        
        // Если статус обновлен, обрабатываем заказ
        if ($result['success'] && $result['status_updated']) {
            self::processPaidOrder($orderId);
        }
        
        return $result;
    }
    
    /**
     * Проверяет статус конкретной подписки по operationId
     */
    public static function checkSubscriptionByOperationId($operationId) {
        $record = PointDB::findRecordByOperationId($operationId);
        
        if (!$record) {
            return ['success' => false, 'message' => 'Запись не найдена'];
        }
        
        $result = self::checkSingleSubscriptionStatus($record);
        
        // Если статус обновлен, обрабатываем заказ
        if ($result['success'] && $result['status_updated']) {
            self::processPaidOrder($record['order_id']);
        }
        
        return $result;
    }
    
    /**
     * Принудительно обрабатывает заказ как оплаченный (для тестирования)
     */
    public static function forceProcessOrder($orderId) {
        return self::processPaidOrder($orderId);
    }
}