<?php defined('BILLINGMASTER') or die;

class GetFunnels {

    /**
     * ДОБАВИТЬ СДЕЛКУ
     * @param $client_email
     * @param $client_name
     * @param $client_phone
     * @param $order_id
     * @param $amount
     * @param $prod_names
     * @param $prod_srv_names
     * @param $pay_status
     * @param $partner_id
     * @param $order
     * @return bool|mixed
     */
    public static function addDeal($client_email, $client_name, $client_phone, $order_id, $amount,
                                   $prod_names, $prod_srv_names, $pay_status, $partner_id, $order) {
        $settings = self::getSettings();
        $params = unserialize($settings);
        
        $account_name = trim($params['params']['account_name']);
        $secret_key = trim($params['params']['secret_key']);
        $partner_id_fname = isset($params['params']['partner_id_fname']) ? trim($params['params']['partner_id_fname']) : null;
        $partner_id_fname_to_user = isset($params['params']['partner_id_fname_to_user']) ? trim($params['params']['partner_id_fname_to_user']) : null;
        $prod_srv_names_fname = isset($params['params']['prod_srv_names_fname']) ? trim($params['params']['prod_srv_names_fname']) : null;
        
        if (!$account_name || !$secret_key) {
            return false;
        }
        
        require_once(__DIR__ . '/../lib/getcourse/autoload.php');
        
        $deal = new \GetCourse\Deal();
        $deal::setAccountName($account_name);
        $deal::setAccessToken($secret_key);
    
        $prod_names = trim(html_entity_decode(implode(', ', $prod_names)));
        $prod_srv_names = !empty(array_filter($prod_srv_names, 'strlen')) ? trim(html_entity_decode(implode(', ', $prod_srv_names))) : null;
        $result = false;
        
        try {
            $deal
                ->setEmail($client_email)
                ->setOverwrite()
                ->setProductTitle($prod_names);

            if ($pay_status == 'expected') { // создание заказа
                $deal->setDealReturnNumber(1);
            } else { // оплата заказа
                $deal_data = self::getDealData($order_id);
                $deal_number = $deal_data ? $deal_data['deal_number'] : null;
                if (!$deal_number) {
                    self::writeError("Данные сделки для заказа №{$order_id} отсутствуют");
                    return false;
                }
                $deal->setDealNumber($deal_number);
            }

            $deal->setDealCost($amount)
                ->setPaymentType('OTHER')
                ->setPaymentStatus($pay_status);

            $fio = explode(' ', trim($client_name));
            $first_name = $fio[0];
            $last_name = isset($fio[1]) ? $fio[1] : null;

            if ($order['order_info']) {
                $order_info = unserialize(base64_decode($order['order_info']));
                if (isset($order_info['surname']) && $order_info['surname']) {
                    $last_name = trim($order_info['surname']);
                }
            }

            if ($first_name) {
                $deal->setFirstName($first_name);
            }

            if ($last_name) {
                $deal->setLastName($last_name);
            }

            $client_phone = trim($client_phone);
            if ($client_phone) {
                $deal->setPhone($client_phone);
            }

            $partner_id = (int)$partner_id;
            if ($partner_id) {
                if ($partner_id_fname_to_user) {
                    $deal->setUserAddField($partner_id_fname_to_user,  $partner_id);
                }
                if ($partner_id_fname) {
                    $deal->setDealAddField($partner_id_fname,  $partner_id);
                }
            }

            if ($prod_srv_names && $prod_srv_names_fname) {
                $deal->setDealAddField($prod_srv_names_fname, $prod_srv_names);
            }

            if (isset($params['params']['send_utm'])) {
                $utm = $order['utm'] ? System::getUtmData($order['utm']) : null;
                if ($utm) {
                    foreach ($utm as $key => $val) {
                        $deal->setDealAddField($key, $val);
                    }
                }
            }

            $result = $deal->apiCall('add');
            if ($result && $pay_status == 'expected') { // создание заказа
                $deal_number = (int)$result->result->deal_number;
                if ($deal_number) {
                    $result = self::saveDealData($order_id, $deal_number);
                } else {
                    self::writeError("ID сделки для заказа №{$order_id} в api-данных отсутствует");
                }
            }
        } catch (Exception $e) {
            self::writeError($e->getMessage());
        }
    
        return $result;
    }


    /**
     * ИЗМЕНИТЬ СТАТУС
     * @param $client_email
     * @param $order_id
     * @param $deal_status
     * @param $pay_status
     * @return bool|mixed
     */
    public static function changePayStatus($client_email, $order_id, $deal_status, $pay_status) {
        $settings = self::getSettings();
        $params = unserialize($settings);
    
        $account_name = trim($params['params']['account_name']);
        $secret_key = trim($params['params']['secret_key']);
    
        if (!$account_name || !$secret_key) {
            return false;
        }
    
        require_once(__DIR__ . '/../lib/getcourse/autoload.php');
    
        $deal = new \GetCourse\Deal();
        $deal::setAccountName($account_name);
        $deal::setAccessToken($secret_key);
        $result = false;
        $deal_data = self::getDealData($order_id);

        if ($deal_data && $deal_data['deal_number']) {
            try {
                $result = $deal
                    ->setEmail($client_email)
                    ->setDealNumber($deal_data['deal_number'])
                    ->setDealStatus($deal_status)
                    ->setPaymentStatus($pay_status)
                    ->apiCall('add');
            } catch (Exception $e) {
                self::writeError($e->getMessage());
            }
        } else {
            self::writeError("Данные сделки для заказа №{$order_id} отсутствуют");
        }
    
        return $result;
    }
    
    
    /**
     * ПОЛУЧИТЬ НАСТРОЙКИ
     * @return bool
     */
    public static function getSettings()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT params FROM ".PREFICS."extensions WHERE name = 'getfunnels'");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data['params'] : false;
    }
    
    
    /**
     * ПОЛУЧИТЬ СТАТУС
     * @return bool
     */
    public static function getStatus()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT enable FROM ".PREFICS."extensions WHERE name = 'getfunnels'");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data['enable'] : false;
    }
    
    
    /**
     * СОХРАНИТЬ НАСТРОЙКИ
     * @param $params
     * @param $status
     * @return bool
     */
    public static function saveSettings($params, $status) {
        $db = Db::getConnection();
        $sql = "UPDATE ".PREFICS."extensions SET params = :params, enable = :enable WHERE name = 'getfunnels'";
        $result = $db->prepare($sql);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':enable', $status, PDO::PARAM_INT);

        return $result->execute();
    }
    
    
    /**
     * ЗАПИСАТЬ ОШИБКУ В ЛОГ
     * @param $error_msg
     */
    public function writeError($error_msg) {
        $error = date('d.m.Y H:i:s', time()) . " Error: $error_msg";
        file_put_contents(__DIR__ . '/../log.txt', PHP_EOL . $error, FILE_APPEND);
    }


    /**
     * @param $order_id
     * @param $deal_number
     * @return bool
     */
    public static function saveDealData($order_id, $deal_number) {
        $db = Db::getConnection();
        $sql = "INSERT INTO ".PREFICS."get_course_deals (order_id, deal_number) VALUES (:order_id, :deal_number)";
        $result = $db->prepare($sql);

        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $result->bindParam(':deal_number', $deal_number, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $order_id
     * @return bool|mixed
     */
    public static function getDealData($order_id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."get_course_deals WHERE order_id = $order_id");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }
}