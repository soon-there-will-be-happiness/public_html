<?php defined('BILLINGMASTER') or die;

class cyclopsApi {
    private static $instance = null;
    private $apiUrl;
    private $signSystem;
    private $signThumbprint;

    public function __construct() {
        // Load environment variables from .env or configuration
        $this->apiUrl = [
            'jsonrpc' => 'https://pre.tochka.com/api/v1/cyclops/v2/jsonrpc',
            'tender-helpers' => 'https://pre.tochka.com/api/v1/tender-helpers/jsonrpc'
        ];
        $this->signSystem = $_ENV['SIGN_SYSTEM'];
        $this->signThumbprint = $_ENV['SIGN_THUMBPRINT'];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CyclopsApi();
        }
        return self::$instance;
    }

    public function makeRequest($api, $method, $params) {
        $payload = json_encode([
            "jsonrpc" => "2.0",
            "method" => $method,
            "params" => $params,
            "id" => uniqid() // "908ca508-f1f1-4256-9c43-9ba7ad9c45fb"
        ]);

        $headers = [
            'Content-Type: application/json',
            'sign-system: ' . $this->signSystem,
            'sign-data: ' . '12345',//hash('sha256', $payload),
            'sign-thumbprint: ' . $this->signThumbprint,
        ];

        // Initialize cURL
        $ch = curl_init($this->apiUrl[$api]);

        // Set options for cURL request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute and handle response
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            Log::add(5,'Curl error', ["error" => curl_error($ch)],'cyclops.log');
            throw new Exception('Curl error: ' . curl_error($ch));
        }


        curl_close($ch);
        Log::add(0,'Return request', [
            "url" => ''.$this->apiUrl[$api],
            "res" => ''.$response, "payload" => $payload, "headers" => $headers],'cyclops.log');
        return json_decode($response, true);
    }

    public function echo($text) {
        $params = ["text" => $text];
        return $this->makeRequest('jsonrpc','echo', $params);
    }

    # BETA-function for simulation transact
    public function transfer_money($amount,$purpose = "ТЕСТОВЫЙ СЛОЙ - Перевод денег, без НДС", $payer_account='40702810713500000456', $payer_bank_code='044525104') {
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

    public function create_beneficiary_ul($inn, $name, $kpp, $ogrn = null,$nominal_account_code=null, $nominal_account_bic=null) {
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

    public function create_beneficiary_fl($inn, $first_name,$last_name,$birth_date, $birth_place, $passport_number,$passport_date,$registration_address, $middle_name=null,$passport_series=null, $resident = true,$nominal_account_code=null, $nominal_account_bic=null) {
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
        return $this->makeRequest('jsonrpc','create_beneficiary_ul', $params);
    }
}
