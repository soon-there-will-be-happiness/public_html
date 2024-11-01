<?php defined('BILLINGMASTER') or die;

class Cyclops
{
    public static function Run($order)
    {
        $setting = System::getSetting();

        try {
            if ($order['partner_id'] != null && $order['partner_id'] > 0) {
                $partner = AFF::getPartnerReq($order['partner_id']);
                $transaction = AFF::getPartnerTransactionReq($order['partner_id'], $order['order_id']);
                $serializedData = $partner['requsits'];
                // Десериализация данных
                $data = unserialize($serializedData);

                if ($data !== false) {
                    // Извлечение данных
                    $rs = $data['rs']['rs'] ?? null;
                    $name = $data['rs']['off_name'] ?? null;
                    $bik = $data['rs']['bik'] ?? null;
                    $itn = $data['rs']['itn'] ?? null;
                    $accountNumber = $data['rs']['rs'];
                    $officeName = $data['rs']['off_name'];
                    $bik = $data['rs']['bik'];
                    $inn = $data['rs']['inn'];
                    $secondAccountNumber = $data['rs']['rs2'];
                    $fullName = $data['rs']['fio'];
                    $birthDate = $data['rs']['birthday'];
                    $passportNumber = $data['rs']['passport'];
                    $birthPlace = $data['rs']['birth-place'];
                    $passportDate = $data['rs']['passport-date'];
                    $passportAddress = $data['rs']['passport-address'];
                    $rs2 = $data['rs']['rs2'];
                    $passport = explode(' ', trim($passportNumber));
                    $passportNumber = $passport[0] ?? null;
                    $passportSeries = $passport[2] ?? null;

                    $nameParts = explode(' ', trim($name));
                    $surname = $nameParts[0] ?? null;
                    $firstName = $nameParts[1] ?? null;
                    $patronymic = $nameParts[2] ?? null;

                    $api = CyclopsApi::getInstance();
                    $response = $api->transfer_money($order['summ']);
                    $response = $api->listPayments(1, 50, ['identify' => false]);
                    $paymentId = $response['result']['payments'][0];
                    $response = $api->getPayment($paymentId);
                    $response = $api->create_beneficiary_fl(
                        $inn,
                        $firstName,
                        $surname,
                        $birthDate,
                        $birthPlace,
                        $passportNumber,
                        $passportDate,
                        $passportAddress,
                        $patronymic,
                        $passportSeries,
                        true
                    );

                    $currentDate = date("Y-m-d");
                    $beneficiary_id = $response["result"]['beneficiary']['id'];

                    sleep(20);
                    $response = $api->uploadDocumentBeneficiary(
                        $beneficiary_id,
                        'contract_offer',
                        $currentDate,
                        '0002',
                        $_SERVER['DOCUMENT_ROOT'] . '/ben.pdf'
                    );
                    $document_id = $response["document_id"];
                    $response = $api->getDocument($document_id);
                    $response = $api->create_virtual_account($beneficiary_id);
                    $virtual_account = $response["result"]['virtual_account'];

                    $response = $api->identifyPayment($paymentId, [
                        ['virtual_account' => $virtual_account, 'amount' => $order['summ']]
                    ]);

                    $response = $api->createDeal($order['summ'], [
                        [
                            'virtual_account' => $virtual_account,
                            'amount' => $order['summ']
                        ]
                    ], [
                        [
                            'number' => 1,
                            'type' => 'commission',
                            'amount' => intval($order['summ']) - intval($transaction['summ'])
                        ],
                        [
                            "number" => 2,
                            "type" => "payment_contract",
                            "amount" => intval($transaction['summ']),
                            "account" => $rs,
                            "bank_code" => $bik,
                            "name" => $firstName . " " . $surname,
                            "inn" => $inn
                        ]
                    ]);

                    $deal_id = $response;
                    $response = $api->uploadDocumentDeal(
                        $beneficiary_id,
                        $deal_id,
                        'contract_offer',
                        $currentDate,
                        '0002',
                        'ben.pdf'
                    );
                    $document_id = $response["document_id"];
                    $response = $api->getDocument($document_id);
                    $response = $api->executeDeal($deal_id);
                    sleep(20);
                    $response = $api->getDeal($deal_id);
                    $response = $api->deactivate_beneficiary($beneficiary_id);
                    $response = $api->activate_beneficiary($beneficiary_id);
                } else {
                    Email::SendEmailAdminAboutProblem($setting['admin_email'], $order['order_id'], " нет данных в PartnerReq");
                    Email::SendEmailAdminAboutProblem($setting['support_email'], $order['order_id'], " нет данных в PartnerReq");
                    return false;
                }
            } else {
                Email::SendEmailAdminAboutProblem($setting['admin_email'], $order['order_id'], " нет partner_id");
                Email::SendEmailAdminAboutProblem($setting['support_email'], $order['order_id'], " нет partner_id");
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::add(0, 'Curl error', ["error" => $e], 'cyclops.log');
            Email::SendEmailAdminAboutProblem($setting['admin_email'], $order['order_id'], " другая причина\n$e");
            Email::SendEmailAdminAboutProblem($setting['support_email'], $order['order_id'], " другая причина\n$e");
            return false;
        }
    }

    public static function AddBeneficiaries($id, $user_id, $is_active, $is_added_to_ms, $legal_type)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'beneficiaries(id, user_id, is_active, is_added_to_ms, legal_type) 
                VALUES (:id, :user_id, :is_active, :is_added_to_ms, :legal_type)';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        $result->bindParam(':is_added_to_ms', $is_added_to_ms, PDO::PARAM_INT);
        $result->bindParam(':legal_type', $legal_type, PDO::PARAM_STR);
        $result->execute();
        
        $result = $db->query("SELECT * FROM " . PREFICS . "beneficiaries WHERE id = '$id'");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        return $data ?? false;
    }

    public static function AddVirtualAccounts($id, $balance, $beneficiary_id, $type = 'стандарт', $blocked_cash = null)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'virtual_accounts(id, balance, beneficiary_id, type, blocked_cash) 
                VALUES (:id, :balance, :beneficiary_id, :type, :blocked_cash)';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':balance', $balance, PDO::PARAM_STR);
        $result->bindParam(':beneficiary_id', $beneficiary_id, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_STR);
        $result->bindParam(':blocked_cash', $blocked_cash, PDO::PARAM_STR);
        $result->execute();
        
        $result = $db->query("SELECT * FROM " . PREFICS . "virtual_accounts WHERE id = '$id'");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        return $data ?? false;
    }

    public static function AddDeals($ext_key, $status, $amount, $payer_id, $recipient_id, $recipients)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'deals(ext_key, status, amount, payer_id, recipient_id, recipients) 
                VALUES (:ext_key, :status, :amount, :payer_id, :recipient_id, :recipients)';
        $result = $db->prepare($sql);
        $result->bindParam(':ext_key', $ext_key, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_STR);
        $result->bindParam(':amount', $amount, PDO::PARAM_STR);
        $result->bindParam(':payer_id', $payer_id, PDO::PARAM_STR);
        $result->bindParam(':recipient_id', $recipient_id, PDO::PARAM_STR);
        $result->bindParam(':recipients', $recipients, PDO::PARAM_STR);
        $result->execute();
        
        $result = $db->query("SELECT * FROM " . PREFICS . "deals WHERE ext_key = '$ext_key'");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        return $data ?? false;
    }

    public static function AddDocuments($number, $type, $deal_id, $beneficiary_id, $date, $document_id, $binary_content, $success_added)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'documents(number, type, deal_id, beneficiary_id, date, document_id, binary_content, success_added) 
                VALUES (:number, :type, :deal_id, :beneficiary_id, :date, :document_id, :binary_content, :success_added)';
        $result = $db->prepare($sql);
        $result->bindParam(':number', $number, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_STR);
        $result->bindParam(':deal_id', $deal_id, PDO::PARAM_STR);
        $result->bindParam(':beneficiary_id', $beneficiary_id, PDO::PARAM_STR);
        $result->bindParam(':date', $date, PDO::PARAM_STR);
        $result->bindParam(':document_id', $document_id, PDO::PARAM_STR);
        $result->bindParam(':binary_content', $binary_content, PDO::PARAM_LOB);
        $result->bindParam(':success_added', $success_added, PDO::PARAM_INT);
        $result->execute();
        
        $result = $db->query("SELECT * FROM " . PREFICS . "documents WHERE number = '$number'");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        return $data ?? false;
    }

    public static function addPayment($id, $amount=null, $identify=false, $virtual_account_id = null, $deal_id = null) {
        $db = Db::getConnection();
        $sql = "INSERT INTO ".PREFICS."cyclop_payments(id, amount, identify, virtual_account_id, deal_id) VALUES(:id, :amount, :identify, :virtual_account_id, :deal_id)";
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':amount', $amount, PDO::PARAM_STR);
        $result->bindParam(':identify', $identify, PDO::PARAM_BOOL);
        $result->bindParam(':virtual_account_id', $virtual_account_id, PDO::PARAM_STR);
        $result->bindParam(':deal_id', $deal_id, PDO::PARAM_STR);
        $result->execute();
    }

    public static function getPayments($filters, $page = 1, $limit = 10, $select = "*") {
        $db = Db::getConnection();
        $offset = ($page - 1) * $limit;
        $where = "WHERE `in_arhive` = ".$filters['in_arhive'];
        if ($filters['amount']) {
            $where .= " AND `amount` = '{$filters['amount']}'";
        }

        if ($filters['identify'] !== false) {
            $where .= " AND `identify` = '{$filters['identify']}'";
        }

        $result = [];
        $result['logs'] = $db ->query("SELECT `id`, `amount`, `status` FROM `".PREFICS."cyclop_payments` $where ORDER BY `id` desc LIMIT $limit OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
        $result["pages"] = $db ->query("SELECT COUNT(*) as total FROM `".PREFICS."cyclop_payments` $where")->fetch();

        return $result ?? false;
    }
}
?>
