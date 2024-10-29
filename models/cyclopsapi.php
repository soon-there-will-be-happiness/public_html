<?php defined('BILLINGMASTER') or die;

class Cyclops {

    public static function Run($order){
        if($order['partner_id']!=null&&$order['partner_id']>0){
            $parther=AFF::getPartnerReq($order['partner_id']);
            $transaction=AFF::getPartnerTransactionReq($order['partner_id'],$order['order_id'],);
            $serializedData =  $parther['requsits'];
            // Десериализация данных
            $data = unserialize($serializedData);
            
            // Проверка на успешность десериализации
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
                $rs2= $data['rs']['rs2'];
                $passport = explode(' ', trim($passportNumber));
                $passportNumber=$passport[0] ?? null;
                $passportSeries=$passport[2] ?? null;


                $nameParts = explode(' ', trim($name));
                $surname = $nameParts[0] ?? null;
                $firstName = $nameParts[1] ?? null;
                $patronymic = $nameParts[2] ?? null;
                $api = CyclopsApi::getInstance();
                $response = $api->transfer_money($order['summ']);// payment sum
                $response = $api->listPayments(1,50,['identify' => false]);
                $paymentId = $response['result']['payments'][0];
                $response = $api->getPayment($paymentId);
                $response = $api->create_beneficiary_fl(
                    $inn ,
                    $firstName ,
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
                $beneficiary_id=$response["result"]['beneficiary']['id'];
                sleep(20);
                $response = $api->uploadDocumentBeneficiary($beneficiary_id,'contract_offer',      $currentDate ,'0002', $_SERVER['DOCUMENT_ROOT'] . '/ben.pdf');
                $document_id=$response["document_id"];
                $response = $api->getDocument($document_id);
                $response = $api->create_virtual_account($beneficiary_id);
                $virtual_account=$response["result"]['virtual_account'];
                $response = $api->identifyPayment($paymentId,[['virtual_account' => $virtual_account,'amount'=>$order['summ']]]);
                $order_items = Order::getOrderItems($order['order_id']);
                $response = $api->createDeal($order['summ'],[
                    [
                        'virtual_account' => $virtual_account,
                        'amount' => $order['summ']
                    ]
                ],[
                        [
                            'number' => 1,
                            'type' => 'commission',
                            'amount' => intval($order['summ']) -intval($transaction['summ'])
                        ],[
                            "number" => 2,
                            "type"=> "payment_contract",
                            "amount"=> intval($transaction['summ']),
                            "account"=> $rs,
                            "bank_code"=>   $bik ,
                            "name"=> $firstName ." ".$surname,
                            "inn"=> $inn
                        ]
                    ]
                );
                $deal_id=$response;
                $response = $api->uploadDocumentDeal($beneficiary_id,$deal_id,'contract_offer',$currentDate ,'0002','ben.pdf');
                $document_id = $response["document_id"];
                $response = $api->getDocument($document_id);
                $response = $api->executeDeal($deal_id);
                sleep(20);
                $response = $api->getDeal($deal_id);
                $response = $api->deactivate_beneficiary($beneficiary_id);
                $response = $api->activate_beneficiary($beneficiary_id);
            } else {
            }
        }else {
        }
        return true;
    }
}


?>