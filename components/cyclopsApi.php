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
            'tender-helpers' => 'https://pre.tochka.com/api/v1/tender-helpers/'
            ];
        $this->signSystem = $_ENV['SIGN_SYSTEM'];
        $this->signThumbprint = $_ENV['SIGN_THUMBPRINT'];
        Log::add(0,'Debug', ["system" => $_ENV['SIGN_SYSTEM'], "t" => $_ENV['SIGN_THUMBPRINT']],'cyclops.log');

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
            'sign-data: ' . hash('sha256', $payload),
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
        Log::add(0,'Return request', ["res" => ''.$response, "payload" => $payload, "headers" => $headers],'cyclops.log');
        return json_decode($response, true);
    }

    public function echo($text) {
        $params = ["text" => $text];
        return $this->makeRequest('jsonrpc','echo', $params);
    }

    # BETA-function for simulation transact
    public function transfer_money($amount,$purpose = "ТЕСТОВЫЙ СЛОЙ - Перевод денег, без НДС", $payer_account=40702810713500000456, $payer_bank_code=044525104) {
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
}
