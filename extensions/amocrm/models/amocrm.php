<?php defined('BILLINGMASTER') or die;

class AmoCRM {
    
    const EVENT_TYPE_ACC_STAT = 1; // ВЫПИСКА СЧЕТА
    const EVENT_TYPE_ACC_PAY = 2; // ОПЛАТА СЧЕТА
    const EVENT_TYPE_INSTLMNT_SELECTED = 3; // ВЫБРАНА ОПЛАТА РАССРОЧКОЙ
    const EVENT_TYPE_INSTLMNT_PAY = 4; // БЫЛА ПРОИЗВЕДЕНА ОПЛАТА В РАССРОЧКУ
    const EVENT_TYPE_GEN_TRIAL = 5; // ГЕНЕРАЦИЯ ТРИАЛА
    const EVENT_TYPE_GIVE_FP = 6; // ВЫДАЧА БЕСПЛАТНОГО ПРОДУКТА
    const EVENT_TYPE_DEBTORS_INSTLMNT = 7; // ПРОСРОЧЕННАЯ РАССРОЧКА

    private static $pip_id;
    private static $stage_id;
    private static $finish_stage_id = null;
    private static $is_send_partner;
    private static $sys_settings;
    private static $params;


    /**
     * @param $event_type
     * @return bool
     */
    private static function init($event_type) {
        self::$sys_settings = System::getSetting();
        $settings = AmoCRM::getSettings();
        self::$params = unserialize($settings);
        self::$is_send_partner = isset(self::$params['params']['send_partner']) && self::$params['params']['send_partner'] ? 1 : false;

        switch ($event_type) {
            case self::EVENT_TYPE_ACC_STAT: // ВЫПИСКА СЧЕТА
                self::$pip_id = self::$params['params']['pip_acc_stat'];
                self::$stage_id = self::$params['params']['stage_acc_stat'];
                break;
            case self::EVENT_TYPE_ACC_PAY: // ОПЛАТА СЧЕТА
                self::$pip_id = self::$params['params']['pip_acc_pay'];
                self::$stage_id = self::$params['params']['stage_acc_pay'];
                break;
            case self::EVENT_TYPE_INSTLMNT_SELECTED: // ВЫБРАНА РАССРОЧКА
                self::$pip_id = self::$params['params']['pip_acc_stat'];
                self::$stage_id = self::$params['params']['stage_acc_stat'];
                break;
            case self::EVENT_TYPE_INSTLMNT_PAY: // ОПЛАТА РАССРОЧКОЙ
                self::$pip_id = isset(self::$params['params']['pip_instlmnt_pay']) ? self::$params['params']['pip_instlmnt_pay'] : null;
                self::$stage_id = isset(self::$params['params']['stage_instlmnt_pay']) ? self::$params['params']['stage_instlmnt_pay'] : null;
                self::$finish_stage_id = self::$params['params']['stage_acc_pay'];
                break;
            case self::EVENT_TYPE_GEN_TRIAL: // ГЕНЕРАЦИЯ ТРИАЛА
                self::$pip_id = isset(self::$params['params']['pip_gen_trial']) ? self::$params['params']['pip_gen_trial'] : null;
                self::$stage_id = isset(self::$params['params']['stage_gen_trial']) ? self::$params['params']['stage_gen_trial'] : null;
                break;
            case self::EVENT_TYPE_GIVE_FP: // ВЫДАЧА БЕСПЛАТНОГО ТОВАРА
                self::$pip_id = isset(self::$params['params']['pip_give_fp']) ? self::$params['params']['pip_give_fp'] : null;
                self::$stage_id = isset(self::$params['params']['stage_give_fp']) ? self::$params['params']['stage_give_fp'] : null;
                break;
            case self::EVENT_TYPE_DEBTORS_INSTLMNT: // СПИСОК ДОЛЖНИКОВ ПО РАССРОЧКАМ
                self::$pip_id = isset(self::$params['params']['pip_debtors_instlmnt']) ? self::$params['params']['pip_debtors_instlmnt'] : null;
                self::$stage_id = isset(self::$params['params']['stage_debtors_instlmnt']) ? self::$params['params']['stage_debtors_instlmnt'] : null;
                break;
            default:
                return false;
                break;
        }

        if (!self::$pip_id || !self::$stage_id || ($event_type  == self::EVENT_TYPE_INSTLMNT_PAY  && !self::$finish_stage_id)) {
            return false;
        }

        return true;
    }


    /**
     * ЗАПУСК ПРОЦЕССОВ ПРИ ОПЛАТЕ СЧЕТА
     * @param $order
     * @param $total_sum
     * @param $prod_names
     * @param $installment_data
     * @param $installment_map_data
     * @param $partners_payouts
     * @return bool|false|PDOStatement
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    public static function processes2PayOrder($order, $total_sum, $prod_names, $installment_data, $installment_map_data, $partners_payouts) {
        if ($total_sum > 0) { // если это платный продукт
            if ($order['installment_map_id'] != 0) { // рассрочка
                return self::processLeads2Installment(self::EVENT_TYPE_INSTLMNT_PAY, $order, $total_sum,
                    $prod_names, $installment_data, $installment_map_data, $partners_payouts
                );
            } else {
                return self::updLead(self::EVENT_TYPE_ACC_PAY, $order, $total_sum, $order['partner_id'], $partners_payouts);
            }
        } else { // если это бесплатный продукт
            return self::deleteLeadData($order['order_id']); // удалить данные по сделкам
        }
    }


    /**
     * @param $event_type
     * @param $order
     * @param $amount
     * @param $prod_names
     * @param $installment_data
     * @param $installment_map_data
     * @param null $partners_payouts
     * @return bool
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    public static function processLeads2Installment($event_type, $order, $amount, $prod_names, $installment_data,
                                                    $installment_map_data, $partners_payouts = null) {
        if (self::init($event_type)) { // оплата рассрочки
            require_once (__DIR__ . '/../../../vendor/autoload.php');

            $api = new AmoCRMApi(self::$sys_settings, self::$params);
            if (!$api->auth()) {
                self::writeError('Ошибка аутентификации');
                return false;
            }

            $number_pay = Installment::getCountPays($installment_map_data['pay_actions']);
            $partner_id = $number_pay < 2 ? $order['partner_id'] : null;
            $partners_payouts = $number_pay < 2 ? $partners_payouts : null;
            $order_info = $order['order_info'] != null ? unserialize(base64_decode($order['order_info'])) : null;
            $cl_name = $order['client_name'];
            $cl_surname = $order_info && isset($order_info['surname']) ? $order_info['surname'] : null;
            $lead_data = self::getLeadData($order['order_id']);
            if (!$lead_data) {
                $lead_data = self::getLeadData2Installment($order['installment_map_id']);
            }

            if ($lead_data) {
                if ($number_pay > 1) { // обновить id заказа по сделке
                    self::updOrderId2Installment($order['installment_map_id'], $order['order_id']);
                } elseif($number_pay == 1) {
                    self::updData2Installment($order['order_id'], $order['installment_map_id'], $number_pay);
                }

                $lead_model = $api->searchLead($lead_data['lead_id']);
                if ($lead_model) {
                    $upd = $api->updateLeadsCollection($lead_model, null, self::$pip_id, self::$finish_stage_id,
                        $amount, $partner_id, $partners_payouts
                    );
                    if (!$upd) {
                        self::writeError("Ошибка обновления сделки для рассрочки, lead_id: {$lead_data['lead_id']}, order_id: {$order['order_id']}");
                    }
                } else {
                    self::writeError("Ошибка получения сделки для рассрочки, lead_id: {$lead_data['lead_id']}, order_id: {$order['order_id']}");
                }
            } else { // если не находятся данные по заказу
                $lead_name = "{$order['client_name']} (платеж: ".($number_pay).')';
                $add = self::_addLead($event_type, $api, self::$pip_id, self::$finish_stage_id, $lead_name, $order,
                    $order['client_email'], $cl_name, $cl_surname, $order['client_phone'], $amount, $prod_names,
                    null, null, null, $number_pay
                );
                if (!$add) {
                    self::writeError("Ошибка данных сделки для рассрочки, order_id: {$order['order_id']}");
                }
            }

            if ($installment_map_data['status'] != 2 && $installment_data)  { // если рассрочка не завершена, добавляем сделку с информацией о новом платеже
                $lead_name = "{$order['client_name']} (платеж: ".($number_pay + 1).')';
                $inst_total_sum = Installment::getTotalSumOrder($installment_data, $installment_map_data);
                $pays = Installment::getPays($installment_data, $inst_total_sum);
                $amount = $pays['other_pay'];

                $statistics_data = $number_pay >= 2 && $lead_data ? $lead_data['statistics_data'] : Order::getStatisticsData($order);
                $partners_data = $number_pay >= 2 && $lead_data ? $lead_data['partners_data'] : [
                    'partner_id' => $order['partner_id'],
                    'partners_payouts' => $partners_payouts,
                ];

                $add = self::_addLead($event_type, $api, self::$pip_id, self::$stage_id, $lead_name,
                    array_merge($order, ['order_id' => 0]), $order['client_email'], $cl_name, $cl_surname,
                    $order['client_phone'],  $amount, $prod_names, $statistics_data, $partners_data,
                    $installment_map_data['next_pay'], $number_pay + 1
                );
                if (!$add) {
                    self::writeError("Ошибка создания сделки для рассрочки по будущему платежу, order_id: {$order['order_id']}, installment_map_id: {$order['installment_map_id']}");
                }
            } elseif($installment_map_data['status'] == 2 && $order['installment_map_id']) { // если рассрочка завершена
                self::deleteLeadData(null, $order['installment_map_id']); // удалить данные по сделкам
            }
        }
    }


    /**
     * ДОБАВИТЬ СДЕЛКУ
     * @param $event_type
     * @param null $order
     * @param null $amount
     * @param array $prod_names
     * @param null $statistics_data
     * @param null $cl_email
     * @param null $cl_name
     * @param null $cl_phone
     * @return bool
     * @throws \AmoCRM\Exceptions\AmoCRMMissedTokenException
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    public static function addLead($event_type, $order = null, $amount = null, $prod_names = [], $statistics_data = null, $cl_email = null, $cl_name = null, $cl_phone = null)
    {
        $cl_surname = null;
        if ($order) {
            $cl_email = $order['client_email'];
            $order_info = unserialize(base64_decode($order['order_info']));
            $cl_name = $order['client_name'];
            $cl_surname = isset($order_info['surname']) ? $order_info['surname'] : null;
            $cl_phone = $order['client_phone'];
        }

        if (!self::init($event_type) || !$cl_name || !$cl_email) {
            return false;
        }

        require_once (__DIR__ . '/../../../vendor/autoload.php');
        $api = new AmoCRMApi(self::$sys_settings, self::$params);
        if (!$api->auth()) {
            self::writeError('Ошибка аутентификации');
            return false;
        }

        $add = self::_addLead($event_type, $api, self::$pip_id, self::$stage_id, $cl_name, $order, $cl_email,
            $cl_name, $cl_surname, $cl_phone, $amount, $prod_names, $statistics_data
        );
        if (!$add) {
            self::writeError("Ошибка создания сделки".($order ? ", order_id: {$order['order_id']}" : ''));
        }
    }


    /**
     * @param $event_type
     * @param AmoCRMApi $api
     * @param $pip_id
     * @param $stage_id
     * @param $lead_name
     * @param $order
     * @param $cl_email
     * @param $cl_name
     * @param $cl_surname
     * @param $cl_phone
     * @param $amount
     * @param $prod_names
     * @param $statistics_data
     * @param null $partners_data
     * @param null $instlmnt_next_pay_date
     * @param null $instlmnt_number_pay
     * @return bool
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    private static function _addLead($event_type, AmoCRMApi $api, $pip_id, $stage_id, $lead_name, $order, $cl_email, $cl_name, $cl_surname,
        $cl_phone, $amount, $prod_names, $statistics_data, $partners_data = null, $instlmnt_next_pay_date = null, $instlmnt_number_pay = null)
    {
        $contacts_collection = $api->searchContactsCollection(null, $cl_email);
        if (!$contacts_collection) {
            $contacts_collection = $api->createContactsCollection($cl_name, $cl_surname, $cl_email, $cl_phone);
            if ($contacts_collection) {
                $contacts_collection = $api->addContactsCollection($contacts_collection);
            }
        }

        if ($contacts_collection) {
            $order_date = $order ? $order['order_date'] : null;
            $leads_collection = $api->createLeadsCollection($lead_name, $pip_id, $stage_id, $amount,
                $prod_names, $order_date, $statistics_data, $partners_data, $instlmnt_next_pay_date
            );

            $leads_collection = $api->addLeadsCollection($leads_collection);
            if ($leads_collection) {
                if (in_array($event_type, [self::EVENT_TYPE_GIVE_FP, self::EVENT_TYPE_ACC_STAT]) || ($event_type == self::EVENT_TYPE_INSTLMNT_PAY && $instlmnt_next_pay_date)) {
                    $contact_id = $contacts_collection->first()->getid();
                    $lead_id = $leads_collection->first()->getId();
                    self::addLeadData($lead_id, $contact_id, $order['order_id'], $order['installment_map_id'],
                        $statistics_data, $partners_data, $instlmnt_number_pay);
                }

                return $api->addContact2Lead($contacts_collection, $leads_collection) ? true : false;
            }
        }

        return false;
    }


    /**
     * ОБНОВИТЬ СДЕЛКУ
     * @param $event_type
     * @param $order
     * @param $amount
     * @param null $partner_id
     * @param array $partners_payouts
     * @param array $prod_names
     * @return bool
     */
    public static function updLead($event_type, $order, $amount = null, $partner_id = null, $partners_payouts = [], $prod_names = []) {
        if (!self::init($event_type) || !$order) {
            return false;
        }

        require_once (__DIR__ . '/../../../vendor/autoload.php');
        $api = new AmoCRMApi(self::$sys_settings, self::$params);
        if (!$api->auth()) {
            self::writeError('Ошибка аутентификации');
            return false;
        }

        if (self::init($event_type) && $lead_data = self::getLeadData($order['order_id'])) {
            $lead_model = $api->searchLead($lead_data['lead_id']);

            if ($lead_model) {
                $lead_name = $event_type == self::EVENT_TYPE_INSTLMNT_SELECTED ? "{$order['client_name']} ({$order['installment_title']})" : null;

                $upd = $api->updateLeadsCollection($lead_model, $lead_name, self::$pip_id, self::$stage_id, $amount, $partner_id, $partners_payouts, $prod_names);
                if ($upd && $event_type == self::EVENT_TYPE_ACC_PAY) {
                    return self::deleteLeadData($order['order_id']); // удалить данные по сделкам (если это обычный заказ)
                }
            }

            return false;
        }
    }


    /**
     * ПОЛУЧИТЬ НАСТРОЙКИ
     * @return bool
     */
    public static function getSettings()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT params FROM ".PREFICS."extensions WHERE name = 'amocrm'");
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
        $result = $db->query(" SELECT enable FROM ".PREFICS."extensions WHERE name = 'amocrm'");
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
        $sql = "UPDATE ".PREFICS."extensions SET params = :params, enable = :enable WHERE name = 'amocrm'";
        $result = $db->prepare($sql);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':enable', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ О СДЕЛКЕ
     * @param $order_id
     * @return bool|mixed
     */
    private static function getLeadData($order_id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."amocrm WHERE order_id = $order_id ORDER BY lead_id DESC LIMIT 1");

        $data = $result->fetch(PDO::FETCH_ASSOC);
        if (!empty($data)) {
            $data['statistics_data'] = $data['statistics_data'] ? json_decode($data['statistics_data'], true) : null;
            $data['partners_data'] = $data['partners_data'] ? json_decode($data['partners_data'], true) : null;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ О СДЕЛКЕ
     * @param $installment_map_id
     * @return bool|mixed
     */
    private static function getLeadData2Installment($installment_map_id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."amocrm WHERE installment_map_id = $installment_map_id AND order_id = 0 LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            $result = $db->query("SELECT * FROM ".PREFICS."amocrm WHERE installment_map_id = $installment_map_id ORDER BY order_id, lead_id DESC LIMIT 1");
            $data = $result->fetch(PDO::FETCH_ASSOC);
        }

        if (!empty($data)) {
            $data['statistics_data'] = $data['statistics_data'] ? json_decode($data['statistics_data'], true) : null;
            $data['partners_data'] = $data['partners_data'] ? json_decode($data['partners_data'], true) : null;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ОБНОВИТЬ ID ЗАКАЗА ДЛЯ РАССРОЧКИ
     * @param $installment_map_id
     * @param $order_id
     * @return bool
     */
    public static function updOrderId2Installment($installment_map_id, $order_id) {
        $db = Db::getConnection();
        $result = $db->prepare("UPDATE ".PREFICS."amocrm SET order_id = :order_id WHERE
                                        installment_map_id = :installment_map_id AND order_id = 0"
        );
        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $result->bindParam(':installment_map_id', $installment_map_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ОБНОВИТЬ ДАННЫЕ ЗАКАЗА ДЛЯ РАССРОЧКИ
     * @param $order_id
     * @param $installment_map_id
     * @param $number_pay
     * @return bool
     */
    private static function updData2Installment($order_id, $installment_map_id, $number_pay) {
        $db = Db::getConnection();
        $result = $db->prepare("UPDATE ".PREFICS."amocrm SET installment_map_id = :installment_map_id,
                                        installment_number_pay = :installment_number_pay WHERE order_id = :order_id"
        );
        $result->bindParam(':installment_map_id', $installment_map_id, PDO::PARAM_INT);
        $result->bindParam(':installment_number_pay', $number_pay, PDO::PARAM_INT);
        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);

        return $result->execute();
    }



    /**
     * ДОБАВИТЬ ДАННЫЕ О СДЕЛКЕ
     * @param $lead_id
     * @param $contact_id
     * @param $order_id
     * @param null $installment_map_id
     * @param null $statistics_data
     * @param null $partners_data
     * @param null $instlmnt_number_pay
     * @return bool
     */
    private static function addLeadData($lead_id, $contact_id, $order_id, $installment_map_id = null,
                                        $statistics_data = null, $partners_data = null, $instlmnt_number_pay = null) {
        $installment_map_id = $installment_map_id ?: null;

        if ($statistics_data) {
            $statistics_data = json_encode($statistics_data);
        }

        if ($partners_data) {
            $partners_data = json_encode($partners_data);
        }

        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'amocrm (lead_id, contact_id, order_id, installment_map_id, installment_number_pay, 
                    statistics_data, partners_data) 
                VALUES (:lead_id, :contact_id, :order_id, :installment_map_id, :installment_number_pay, :statistics_data,
                    :partners_data)';

        $result = $db->prepare($sql);
        $result->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
        $result->bindParam(':contact_id', $contact_id, PDO::PARAM_INT);
        $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $result->bindParam(':installment_map_id', $installment_map_id, PDO::PARAM_INT);
        $result->bindParam(':installment_number_pay', $instlmnt_number_pay, PDO::PARAM_INT);
        $result->bindParam(':statistics_data', $statistics_data, PDO::PARAM_STR);
        $result->bindParam(':partners_data', $partners_data, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @param $order_id
     * @param null $installment_map_id
     * @return false|PDOStatement
     */
    private static function deleteLeadData($order_id, $installment_map_id = null) {
        $db = Db::getConnection();
        $where = "WHERE " . ($order_id ? "order_id = $order_id" : "installment_map_id = $installment_map_id");

        return $db->query("DELETE FROM ".PREFICS."amocrm $where");
    }


    /**
     * @param $error
     */
    public static function writeError($error) {
        $error = date('d.m.Y H:i:s', time()) . " Error: $error";

        file_put_contents(__DIR__ . '/../log.log', PHP_EOL . $error, FILE_APPEND);
    }
}