<?php defined('BILLINGMASTER') or die;

class cyclopsApi {
    private static $instance = null;
    private $apiUrl;
    private $signSystem;
    private $signThumbprint;
    private $privateKeyPath;

    public function __construct($type) {
        // Load environment variables from .env or configuration
        $this->apiUrl = [
            'jsonrpc' => "https://{$type}.tochka.com/api/v1/cyclops/v2/jsonrpc",
            'tender-helpers' => "https://{$type}.tochka.com/api/v1/tender-helpers/jsonrpc",
            'upload' => "https://{$type}.tochka.com/api/v1/cyclops/upload_document"
        ];
        $this->signSystem = $_ENV['SIGN_SYSTEM'];
        $this->signThumbprint = $_ENV['SIGN_THUMBPRINT'];
        $this->privateKeyPath = $_ENV['PRIVATE_KEY_PATH'];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CyclopsApi('api');
        }
        return self::$instance;
    }

    /**
     * Generates a SHA-256 signature for the given message and encodes it in base64.
     */
    protected function generateSignature($message)
    {
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        if (!$privateKey) {
            throw new Exception("Failed to load private key.");
        }

        // Generate the signature
        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        // Encode the signature in base64 and remove line breaks
        return str_replace(["\r", "\n"], '', base64_encode($signature));
    }

    /**
     * @throws Exception
     */
    protected function makeRequest($api, $method, $params) {
        $payload = json_encode([
            "jsonrpc" => "2.0",
            "method" => $method,
            "params" => $params,
            "id" => uniqid() // "908ca508-f1f1-4256-9c43-9ba7ad9c45fb"
        ]);

        $signature = $this->generateSignature($payload);

        $headers = [
            'Content-Type: application/json',
            'sign-system: ' . $this->signSystem,
            'sign-data: ' . $signature,//hash('sha256', $payload),
            'sign-thumbprint: ' . $this->signThumbprint,
        ];

        // Initialize cURL
        $ch = curl_init($this->apiUrl[$api]);

        // Set options for cURL request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute and handle response
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            Log::add(5,'Curl error', ["error" => curl_error($ch), "URL" => $this->apiUrl[$api]],'cyclops.log');
            throw new Exception('Curl error: ' . curl_error($ch));
        }


        curl_close($ch);
        Log::add(0,'Return request', [
            "url" => ''.$this->apiUrl[$api],
            "res" => ''.$response,
            "payload" => $payload,
            "headers" => $headers
        ], 'cyclops.log');
        return json_decode($response, true);
    }

    /**
     * @throws Exception
     */
    private function uploadDocument($url, $filePath)
    {
        $fileData = file_get_contents($filePath);
        if ($fileData === false) {
            throw new Exception("Failed to read file: $filePath");
        }

        $signature = $this->generateSignature($fileData);

        $headers = [
            'Content-Type: application/pdf', // Укажите нужный Content-Type в зависимости от типа файла
            "sign-data: " . $signature,
            "sign-thumbprint: ". $this->signThumbprint,
            "sign-system: " . $this->signSystem
        ];

        $ch = curl_init($url);
        // Set options for cURL request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);
        Log::add(0,'Return request', [
            "url" => ''.$this->apiUrl['jsonrpc'],
            "res" => ''.$response,
            "fileData" => $fileData,
            "headers" => $headers
        ], 'cyclops.log');
        return json_decode($response, true);
    }

    /**
     * @throws Exception
     */
    public function echo($text) {
        $params = ["text" => $text];
        return $this->makeRequest('jsonrpc','echo', $params);
    }

    # BETA-function for simulation transact

    /**
     * @throws Exception
     */
    public function transfer_money($amount, $purpose = "ТЕСТОВЫЙ СЛОЙ - Перевод денег, без НДС", $payer_account='40702810713500000456', $payer_bank_code='044525104') {
        $params = [
            "recipient_account" => $_ENV['NOMINAL_ACCOUNT'],
            "recipient_bank_code" => $_ENV['BIC'],
            "amount" => $amount,
            "purpose" => $purpose,
            "payer_account" => $payer_account,
            "payer_bank_code" => $payer_bank_code,
        ];
        return $this->makeRequest('tender-helpers','transfer_money', $params);
    }

    /**
     * @throws Exception
     */
    public function create_beneficiary_ul($inn, $name, $kpp, $ogrn = null, $nominal_account_code=null, $nominal_account_bic=null) {
        $params = [
            "inn" => $inn,
            "beneficiary_data" => [
                'name' => $name,
                'kpp' => $kpp,
            ]
        ];

        if ($ogrn !== null) {
            $params['beneficiary_data']['ogrn'] = $ogrn;
        }

        if ($nominal_account_code !== null) {
            $params['nominal_account_code'] = $nominal_account_code;
        }

        if ($nominal_account_bic !== null) {
            $params['nominal_account_bic'] = $nominal_account_bic;
        }
        return $this->makeRequest('jsonrpc','create_beneficiary_ul', $params);
    }

    /**
     * @throws Exception
     */
    public function create_beneficiary_fl($inn, $first_name, $last_name, $birth_date, $birth_place, $passport_number, $passport_date, $registration_address, $middle_name=null, $passport_series=null, $resident = true, $nominal_account_code=null, $nominal_account_bic=null) {
        $params = [
            "inn" => $inn,
            "beneficiary_data" => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'birth_date' => $birth_date,
                'birth_place' => $birth_place,
                'passport_number' => $passport_number,
                'passport_date' => $passport_date,
                'registration_address' => $registration_address,
            ]
        ];
        if ($middle_name !== null) {
            $params['beneficiary_data']['middle_name'] = $middle_name;
        }
        if ($resident !== true) {
            $params['beneficiary_data']['resident'] = $resident;
        } else {
            if ($passport_series !== null) {
                $params['beneficiary_data']['passport_series'] = $passport_series;
            } else {
                Log::add(0,'Wrong data', ["resident" => $resident, "passport_series_empty" => isset($passport_series)],'cyclops.log');
                return false;
            }
        }

        if ($nominal_account_code !== null) {
            $params['nominal_account_code'] = $nominal_account_code;
        }

        if ($nominal_account_bic !== null) {
            $params['nominal_account_bic'] = $nominal_account_bic;
        }
        return $this->makeRequest('jsonrpc','create_beneficiary_fl', $params);
    }

    public function updateBeneficiaryUL($beneficiaryId, $name, $kpp, $ogrn = null)
    {
        if (!$beneficiaryId || !$name || !$kpp) {
            throw new InvalidArgumentException('Beneficiary ID, name, and KPP are required for updating legal entity details.');
        }

        $params = [
            'beneficiary_id' => $beneficiaryId,
            'beneficiary_data' => [
                'name' => $name,
                'kpp' => $kpp,
            ]
        ];

        if ($ogrn) {
            $params['beneficiary_data']['ogrn'] = $ogrn;
        }

        return $this->makeRequest('jsonrpc','update_beneficiary_ul', $params);
    }

    public function updateBeneficiaryIP($beneficiaryId, $firstName, $lastName, $middleName = null)
    {
        if (!$beneficiaryId || !$firstName || !$lastName) {
            throw new InvalidArgumentException('Beneficiary ID, first name, and last name are required for updating individual entrepreneur details.');
        }

        $params = [
            'beneficiary_id' => $beneficiaryId,
            'beneficiary_data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        ];

        if ($middleName) {
            $params['beneficiary_data']['middle_name'] = $middleName;
        }

        return $this->makeRequest('jsonrpc','update_beneficiary_ip', $params);
    }

    public function updateBeneficiaryFL(
        $beneficiaryId,
        $firstName,
        $lastName,
        $birthDate,
        $birthPlace,
        $passportSeries,
        $passportNumber,
        $passportDate,
        $registrationAddress,
        $middleName = null
    ) {
        if (!$beneficiaryId || !$firstName || !$lastName || !$birthDate || !$birthPlace || !$passportSeries || !$passportNumber || !$passportDate || !$registrationAddress) {
            throw new InvalidArgumentException('All required fields must be provided for updating individual details.');
        }

        $params = [
            'beneficiary_id' => $beneficiaryId,
            'beneficiary_data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => $birthDate,
                'birth_place' => $birthPlace,
                'passport_series' => $passportSeries,
                'passport_number' => $passportNumber,
                'passport_date' => $passportDate,
                'registration_address' => $registrationAddress
            ]
        ];

        if ($middleName) {
            $params['beneficiary_data']['middle_name'] = $middleName;
        }

        return $this->makeRequest('jsonrpc','update_beneficiary_fl', $params);
    }

    public function listBeneficiary($page = 1, $perPage = 50, $filters = [])
    {
        // Validate and sanitize input parameters
        if ($perPage > 1000) {
            throw new InvalidArgumentException("The 'per_page' parameter cannot exceed 1000.");
        }

        $params = [
            'page' => $page,
            'per_page' => $perPage,
            'filters' => []
        ];

        // Add filters if provided
        if (!empty($filters)) {
            if (isset($filters['inn'])) {
                $params['filters']['inn'] = $filters['inn'];
            }
            if (isset($filters['is_active'])) {
                $params['filters']['is_active'] = $filters['is_active'];
            }
            if (isset($filters['legal_type'])) {
                $params['filters']['legal_type'] = $filters['legal_type'];
            }
            if (isset($filters['nominal_account_code'])) {
                $params['filters']['nominal_account_code'] = $filters['nominal_account_code'];
            }
            if (isset($filters['nominal_account_bic'])) {
                $params['filters']['nominal_account_bic'] = $filters['nominal_account_bic'];
            }
        }

        // Execute the request
        return $this->makeRequest('jsonrpc','list_beneficiary', $params);
    }

    /**
     * @throws Exception
     */
    public function get_beneficiary($beneficiary_id) {
        $params = [
            "beneficiary_id" => $beneficiary_id
        ];
        return $this->makeRequest('jsonrpc','get_beneficiary', $params);
    }

    /**
     * @throws Exception
     */
    public function deactivate_beneficiary($beneficiary_id) {
        $params = [
            "beneficiary_id" => $beneficiary_id
        ];
        return $this->makeRequest('jsonrpc','deactivate_beneficiary', $params);
    }

    /**
     * @throws Exception
     */
    public function activate_beneficiary($beneficiary_id) {
        $params = [
            "beneficiary_id" => $beneficiary_id
        ];
        return $this->makeRequest('jsonrpc','activate_beneficiary', $params);
    }


    //
    // Виртуальные счета
    //

    /**
     * @throws Exception
     */
    public function create_virtual_account($beneficiary_id, $virtual_account_type = null) {
        $params = [
            "beneficiary_id" => $beneficiary_id
        ];

        if ($virtual_account_type !== null) {
            $params['virtual_account_type'] = 'for_ndfl';
        }
        return $this->makeRequest('jsonrpc','create_virtual_account', $params);
    }

    /**
     * @throws Exception
     */
    public function list_virtual_account($page = 1, $per_page = 50, $beneficiary = []) {
        $params = [
            'page' => $page,
            'per_page' => min($per_page, 1000), // ограничение на max 1000
        ];

        if (!empty($beneficiary)) {
            $beneficiary_filter = [];
            if (isset($beneficiary['id'])) {
                $beneficiary_filter['id'] = $beneficiary['id'];
            }
            if (isset($beneficiary['is_active'])) {
                $beneficiary_filter['is_active'] = $beneficiary['is_active'];
            }
            if (isset($beneficiary['legal_type'])) {
                $beneficiary_filter['legal_type'] = $beneficiary['legal_type'];
            }
            if (isset($beneficiary['inn'])) {
                $beneficiary_filter['inn'] = $beneficiary['inn'];
            }

            $params['filters']['beneficiary'] = $beneficiary_filter;
        }

        return $this->makeRequest('jsonrpc','list_virtual_account', $params);
    }

    /**
     * @throws Exception
     */
    public function get_virtual_account($virtual_account) {
        $params = [
            "virtual_account" => $virtual_account
        ];
        return $this->makeRequest('jsonrpc','get_virtual_account', $params);
    }

    /**
     * @throws Exception
     */
    public function list_virtual_transaction($page = 1, $per_page = 50, $filters = []) {
        $params = [
            'page' => $page,
            'per_page' => min($per_page, 1000), // ограничение на max 1000
        ];

        // Условие для фильтров
        if (!empty($filters)) {
            $filters_array = [];

            if (isset($filters['virtual_account'])) {
                $filters_array['virtual_account'] = $filters['virtual_account'];
            }
            if (isset($filters['deal_id'])) {
                $filters_array['deal_id'] = $filters['deal_id'];
            }
            if (isset($filters['payment_id'])) {
                $filters_array['payment_id'] = $filters['payment_id'];
            }
            if (isset($filters['created_date_from'])) {
                $filters_array['created_date_from'] = $filters['created_date_from'];
            }
            if (isset($filters['created_date_to'])) {
                $filters_array['created_date_to'] = $filters['created_date_to'];
            }
            if (isset($filters['incoming'])) {
                $filters_array['incoming'] = $filters['incoming'];
            }
            if (isset($filters['operation_type'])) {
                $filters_array['operation_type'] = $filters['operation_type'];
            }
            if (isset($filters['include_block_operations'])) {
                $filters_array['include_block_operations'] = $filters['include_block_operations'];
            }

            $params['filters'] = $filters_array;
        }

        return $this->makeRequest('jsonrpc','list_virtual_transaction', $params);
    }

    /**
     * @throws Exception
     */
    public function refund_virtual_account($virtual_account, $recipient, $purpose = null, $ext_key = null, $identifier = null) {
        // Основные параметры запроса
        $params = [
            "virtual_account" => $virtual_account,
            "recipient" => [
                "amount" => $recipient['amount'],
                "account" => $recipient['account'],
                "bank_code" => $recipient['bank_code'],
                "name" => $recipient['name']
            ]
        ];

        // Добавляем необязательные параметры при их наличии
        if (isset($recipient['inn'])) {
            $params['recipient']['inn'] = $recipient['inn'];
        }
        if (isset($recipient['kpp'])) {
            $params['recipient']['kpp'] = $recipient['kpp'];
        }
        if (isset($recipient['document_number'])) {
            $params['recipient']['document_number'] = $recipient['document_number'];
        }
        if ($purpose !== null) {
            $params['purpose'] = $purpose;
        }
        if ($ext_key !== null) {
            $params['ext_key'] = $ext_key;
        }
        if ($identifier !== null) {
            $params['identifier'] = $identifier;
        }

        // Выполнение запроса через JSON-RPC
        return $this->makeRequest('jsonrpc','refund_virtual_account', $params);
    }


    /**
     * @throws Exception
     */
    public function transfer_between_virtual_accounts($from_virtual_account, $to_virtual_account, $amount) {
        // Параметры запроса
        $params = [
            "from_virtual_account" => $from_virtual_account,
            "to_virtual_account" => $to_virtual_account,
            "amount" => $amount
        ];

        // Выполнение запроса через JSON-RPC
        return $this->makeRequest('jsonrpc','transfer_between_virtual_accounts', $params);
    }

    // Метод для перевода между виртуальными счетами v2
    public function transferBetweenVirtualAccountsV2($fromVirtualAccount, $toVirtualAccount, $amount, $purpose = null, $extKey = null) {
        $params = [
            "from_virtual_account" => $fromVirtualAccount,
            "to_virtual_account" => $toVirtualAccount,
            "amount" => $amount
        ];
        if ($purpose) {
            $params["purpose"] = $purpose;
        }
        if ($extKey) {
            $params["ext_key"] = $extKey;
        }

        return $this->makeRequest('jsonrpc','transfer_between_virtual_accounts_v2', $params);
    }

    // Метод для получения статуса перевода
    public function getVirtualAccountsTransfer($transferId) {
        $params = [
            "transfer_id" => $transferId
        ];

        return $this->makeRequest('jsonrpc','get_virtual_accounts_transfer', $params);
    }


    //
    // Платежи
    //

    public function listPayments($page = 1, $perPage = 50, $filters = []) {
        $params = [
            "page" => $page,
            "per_page" => $perPage,
        ];

        if ($filters !== null) $params["filters"] = $filters;
        return $this->makeRequest('jsonrpc','list_payments', $params);
    }

    public function listPaymentsV2($page = 1, $perPage = 50, $filters = []) {
        $params = [
            "page" => $page,
            "per_page" => $perPage
        ];
        if ($filters !== null) $params["filters"] = $filters;
        $response = $this->makeRequest('list_payments_v2', $params);

        if (isset($response['result']['payments'])) {
            return [
                "payments" => $response['result']['payments'],
                "meta" => $response['result']['meta']
            ];
        } else {
            throw new Exception("Error listing payments: " . $response['error']['message']);
        }
    }

    public function getPayment($paymentId) {
        $params = [
            "payment_id" => $paymentId,
        ];
        return $this->makeRequest('jsonrpc','get_payment', $params);
    }

    public function identifyPayment($paymentId, $owners, $isReturnedPayment = false) {
        $params = [
            "payment_id" => $paymentId,
            "is_returned_payment" => $isReturnedPayment,
            "owners" => $owners,
        ];
        return $this->makeRequest('jsonrpc','identification_payment', $params);
    }

    public function identifyReturnedPaymentByDeal($paymentId, $dealId, $initialPaymentId = null) {
        $params = [
            "payment_id" => $paymentId,
            "deal_id" => $dealId
        ];

        if ($initialPaymentId !== null) {
            $params["initial_payment_id"] = $initialPaymentId;
        }

        $response = $this->makeRequest('jsonrpc','identification_returned_payment_by_deal', $params);

        if (isset($response['result']['recipient_number'])) {
            return $response['result']['recipient_number'];
        } else {
            throw new Exception("Error identifying returned payment by deal: " . $response['error']['message']);
        }
    }

    public function refundPayment($paymentId, $amount = null, $virtualAccounts = null, $purpose = null, $purposeNds = null, $documentNumber = null) {
        $params = [
            "payment_id" => $paymentId
        ];

        if ($amount !== null) $params["amount"] = $amount;
        if ($virtualAccounts !== null) $params["virtual_accounts"] = $virtualAccounts;
        if ($purpose !== null) $params["purpose"] = $purpose;
        if ($purposeNds !== null) $params["purpose_nds"] = $purposeNds;
        if ($documentNumber !== null) $params["document_number"] = $documentNumber;

        $response = $this->makeRequest('jsonrpc','refund_payment', $params);

        if (isset($response['result']['payment_id'])) {
            return $response['result']['payment_id'];
        } else {
            throw new Exception("Error refunding payment: " . $response['error']['message']);
        }
    }

    public function complianceCheckPayment($recipientName, $recipientInn, $recipientBankCode, $recipientAccount, $payerAccount = null, $payerBankCode = null, $amount, $purpose) {
        $params = [
            "recipient_name" => $recipientName,
            "recipient_inn" => $recipientInn,
            "recipient_bank_code" => $recipientBankCode,
            "recipient_account" => $recipientAccount,
            "amount" => $amount,
            "purpose" => $purpose,
        ];
        if ($payerAccount !== null) $params["payer_account"] = $payerAccount;
        if ($payerBankCode !== null) $params["payer_bank_code"] = $payerBankCode;

        return $this->makeRequest('jsonrpc','jsonrpc','compliance_check_payment', $params);
    }

    public function paymentOfTaxes($amount, $payer, $recipients, $extKey = null) {
        $params = [
            "amount" => $amount,
            "payer" => $payer,
            "recipients" => $recipients,
        ];
        if ($extKey !== null) $params["ext_key"] = $extKey;

        return $this->makeRequest('jsonrpc','jsonrpc','payment_of_taxes', $params);
    }

    public function generatePaymentOrder($paymentId) {
        $params = [
            "payment_id" => $paymentId
        ];

        $response = $this->makeRequest('jsonrpc','generate_payment_order', $params);

        if (isset($response['result']['payment_order'])) {
            return base64_decode($response['result']['payment_order']); // Decodes base64 to get PDF content
        } else {
            throw new Exception("Error generating payment order: " . $response['error']['message']);
        }
    }

    //
    // Сделки
    //

    public function createDeal($amount, $payers, $recipients) {
        $params = [
            "ext_key" => uniqid('deal-', true),
            "amount" => $amount,
            "payers" => $payers,
            "recipients" => $recipients
        ];

        $response = $this->makeRequest('jsonrpc','create_deal', $params);

        if (isset($response['result']['deal_id'])) {
            return $response['result']['deal_id'];
        } else {
            throw new Exception("Error creating deal: " . $response['error']['message']);
        }
    }

    public function updateDeal($dealId, $dealData) {
        $params = [
            "deal_id" => $dealId,
            "deal_data" => $dealData
        ];

        $response = $this->makeRequest('jsonrpc','update_deal', $params);

        if (isset($response['result']['deal_id'])) {
            return $response['result'];
        } else {
            throw new Exception("Error updating deal: " . $response['error']['message']);
        }
    }

    public function listDeals($page = 1, $perPage = 50, $fieldNames = [], $filters = []) {
        $params = [
            "page" => $page,
            "per_page" => $perPage,
            "field_names" => $fieldNames
        ];
        if ($filters !== null) $params["filters"] = $filters;
        $response = $this->makeRequest('jsonrpc','list_deals', $params);

        if (isset($response['result']['deals'])) {
            return $response['result'];
        } else {
            throw new Exception("Error listing deals: " . $response['error']['message']);
        }
    }

    public function getDeal($dealId) {
        $params = [
            "deal_id" => $dealId
        ];

        $response = $this->makeRequest('jsonrpc','get_deal', $params);

        if (isset($response['result']['deal'])) {
            return $response['result']['deal'];
        } else {
            throw new Exception("Error retrieving deal information: " . $response['error']['message']);
        }
    }

    public function executeDeal($dealId, $recipientsExecute = null) {
        $params = [
            "deal_id" => $dealId
        ];

        if ($recipientsExecute !== null) $params["recipients_execute"] = $recipientsExecute;

        $response = $this->makeRequest('jsonrpc','execute_deal', $params);

        if (isset($response['result']['deal_id'])) {
            return $response['result']['deal_id'];
        } else {
            throw new Exception("Error executing deal: " . $response['error']['message']);
        }
    }

    public function rejectDeal($dealId) {
        $params = [
            "deal_id" => $dealId
        ];

        $response = $this->makeRequest('jsonrpc','rejected_deal', $params);

        if (isset($response['result']['deal'])) {
            return $response['result']['deal'];
        } else {
            throw new Exception("Error rejecting deal: " . $response['error']['message']);
        }
    }

    public function cancelDealWithExecutedRecipients($dealId) {
        $params = [
            "deal_id" => $dealId
        ];

        $response = $this->makeRequest('jsonrpc','cancel_deal_with_executed_recipients', $params);

        if (isset($response['result']['deal_id'])) {
            return $response['result']['deal_id'];
        } else {
            throw new Exception("Error cancelling deal with executed recipients: " . $response['error']['message']);
        }
    }

    public function complianceCheckDeal($dealId) {
        $params = [
            "deal_id" => $dealId
        ];

        $response = $this->makeRequest('jsonrpc','compliance_check_deal', $params);

        if (isset($response['result']['compliance_check_payments'])) {
            return $response['result']['compliance_check_payments'];
        } else {
            throw new Exception("Error in compliance check for deal: " . $response['error']['message']);
        }
    }

    //
    // Документы
    //

    public function listDocuments($page = 1, $perPage = 50, $filters = []) {
        $params = [
            "page" => $page,
            "per_page" => $perPage
        ];

        if ($filters !== null) $params["filters"] = $filters;
        $response = $this->makeRequest('jsonrpc','list_documents', $params);

        if (isset($response['result']['documents'])) {
            return $response['result'];
        } else {
            throw new Exception("Error listing documents: " . $response['error']['message']);
        }
    }

    public function getDocument($documentId)
    {
        // Validate the document ID
        if (empty($documentId) || !is_string($documentId)) {
            throw new InvalidArgumentException("The 'document_id' parameter must be a non-empty string.");
        }

        $params = [
            'document_id' => $documentId
        ];

        // Execute the request
        $result = $this->makeRequest('jsonrpc','get_document', $params);

        // Parse and return the document data if available
        return $result['document'] ?? null;
    }

    public function listBankSbp() {
        $response = $this->makeRequest('jsonrpc','list_bank_sbp', []);

        if (isset($response['result']['banks'])) {
            return $response['result']['banks'];
        } else {
            throw new Exception("Error listing SBP banks: " . $response['error']['message']);
        }
    }

    public function generateSbpQrCode($amount, $purpose, $nominalAccountCode = null, $nominalAccountBic = null, $width = 300, $height = 300) {
        $params = [
            "amount" => $amount,
            "purpose" => $purpose,
            "width" => $width,
            "height" => $height
        ];

        if ($nominalAccountCode !== null) $params["nominal_account_code"] = $nominalAccountCode;
        if ($nominalAccountBic !== null) $params["nominal_account_bic"] = $nominalAccountBic;

        $response = $this->makeRequest('jsonrpc','generate_sbp_qrcode', $params);

        if (isset($response['result']['qrcode'])) {
            return $response['result']['qrcode'];
        } else {
            throw new Exception("Error generating SBP QR code: " . $response['error']['message']);
        }
    }

    // Метод для загрузки документа по бенефициару
    public function uploadDocumentBeneficiary($beneficiaryId, $documentType, $documentDate, $documentNumber, $filePath)
    {
        $url = "{$this->apiUrl['upload']}/beneficiary?beneficiary_id={$beneficiaryId}&document_type={$documentType}&document_date={$documentDate}&document_number={$documentNumber}";
        return $this->uploadDocument($url, $filePath);
    }

    // Метод для загрузки документа по сделке
    public function uploadDocumentDeal($beneficiaryId, $dealId, $documentType, $documentDate, $documentNumber, $filePath)
    {
        $url = "{$this->apiUrl['upload']}/deal?beneficiary_id={$beneficiaryId}&deal_id={$dealId}&document_type={$documentType}&document_date={$documentDate}&document_number={$documentNumber}";
        return $this->uploadDocument($url, $filePath);
    }
}
