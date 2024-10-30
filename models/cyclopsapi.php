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
                $data = unserialize($serializedData);

                if ($data !== false) {
                    // Извлечение данных
                    $inn = $data['rs']['inn'] ?? null;
                    $firstName = $data['rs']['off_name'] ? explode(' ', $data['rs']['off_name'])[1] : null;
                    $surname = $data['rs']['off_name'] ? explode(' ', $data['rs']['off_name'])[0] : null;
                    $birthDate = $data['rs']['birthday'] ?? null;
                    $birthPlace = $data['rs']['birth-place'] ?? null;
                    $passportNumber = $data['rs']['passport'] ?? null;
                    $passportDate = $data['rs']['passport-date'] ?? null;
                    $passportAddress = $data['rs']['passport-address'] ?? null;
                    $passportSeries = explode(' ', $passportNumber)[2] ?? null;

                    $api = CyclopsApi::getInstance();
                    $response = $api->create_beneficiary_fl(
                        $inn,
                        $firstName,
                        $surname,
                        $birthDate,
                        $birthPlace,
                        $passportNumber,
                        $passportDate,
                        $passportAddress,
                        $surname,
                        $passportSeries,
                        true
                    );

                    $beneficiary_id = $response["result"]['beneficiary']['id'];
                    $response = $api->create_virtual_account($beneficiary_id);

                    $response = $api->createDeal($order['summ'], [
                        ['virtual_account' => $response["result"]['virtual_account'], 'amount' => $order['summ']]
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
                            "inn" => $inn
                        ]
                    ]);

                    $deal_id = $response;
                    $response = $api->uploadDocumentDeal(
                        $beneficiary_id,
                        $deal_id,
                        'contract_offer',
                        date("Y-m-d"),
                        '0002',
                        'ben.pdf'
                    );
                    $response = $api->executeDeal($deal_id);

                } else {
                    Email::SendEmailAdminAboutProblem($setting['admin_email'], $order['order_id'], "нет данных в PartnerReq");
                    return false;
                }
            } else {
                Email::SendEmailAdminAboutProblem($setting['admin_email'], $order['order_id'], "нет partner_id");
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::add(0, 'Curl error', ["error" => $e], 'cyclops.log');
            Email::SendEmailAdminAboutProblem($setting['admin_email'], $order['order_id'], "ошибка: $e");
            return false;
        }
    }

    public static function AddBeneficiaries($id, $user_id, $is_active, $is_added_to_ms, $legal_type)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'cyclop_beneficiaries(id, user_id, is_active, is_added_to_ms, legal_type) 
                VALUES (:id, :user_id, :is_active, :is_added_to_ms, :legal_type)';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        $result->bindParam(':is_added_to_ms', $is_added_to_ms, PDO::PARAM_INT);
        $result->bindParam(':legal_type', $legal_type, PDO::PARAM_STR);
        $result->execute();
        
        return $result->fetch(PDO::FETCH_ASSOC) ?? false;
    }

    public static function AddVirtualAccounts($id, $balance, $beneficiary_id, $type = 'стандарт', $blocked_cash = null)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'cyclop_virtual_accounts(id, balance, beneficiary_id, type, blocked_cash) 
                VALUES (:id, :balance, :beneficiary_id, :type, :blocked_cash)';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_STR);
        $result->bindParam(':balance', $balance, PDO::PARAM_STR);
        $result->bindParam(':beneficiary_id', $beneficiary_id, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_STR);
        $result->bindParam(':blocked_cash', $blocked_cash, PDO::PARAM_STR);
        $result->execute();
        
        return $result->fetch(PDO::FETCH_ASSOC) ?? false;
    }

    public static function AddDeals($ext_key, $status, $amount, $payer_id, $recipient_id, $recipients)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'cyclop_deals(ext_key, status, amount, payer_id, recipient_id, recipients) 
                VALUES (:ext_key, :status, :amount, :payer_id, :recipient_id, :recipients)';
        $result = $db->prepare($sql);
        $result->bindParam(':ext_key', $ext_key, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_STR);
        $result->bindParam(':amount', $amount, PDO::PARAM_STR);
        $result->bindParam(':payer_id', $payer_id, PDO::PARAM_STR);
        $result->bindParam(':recipient_id', $recipient_id, PDO::PARAM_STR);
        $result->bindParam(':recipients', $recipients, PDO::PARAM_STR);
        $result->execute();
        
        return $result->fetch(PDO::FETCH_ASSOC) ?? false;
    }

    public static function AddDocuments($number, $type, $deal_id, $beneficiary_id, $date, $document_id, $binary_content, $success_added)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO ' . PREFICS . 'cyclop_documents(number, type, deal_id, beneficiary_id, date, document_id, binary_content, success_added) 
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
        
        return $result->fetch(PDO::FETCH_ASSOC) ?? false;
    }
}
?>
