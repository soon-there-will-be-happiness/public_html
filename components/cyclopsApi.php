<?php defined('BILLINGMASTER') or die;

class cyclopsApi {
    private static $instance = null;
    private $apiUrl;
    private $signSystem;
    private $signThumbprint;

    public function __construct() {
        // Load environment variables from .env or configuration
        $this->apiUrl = 'https://pre.tochka.com/api/v1/cyclops/v2/jsonrpc';
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

    public function makeRequest($method, $params) {
        $payload = json_encode([
            "jsonrpc" => "2.0",
            "method" => $method,
            "params" => $params,
            "id" => uniqid() // "908ca508-f1f1-4256-9c43-9ba7ad9c45fb"
        ]);

        $headers = [
            'Content-Type: application/json',
            'sign-system: ' . $this->signSystem,
            'sign-data: ' . '12345',
            'sign-thumbprint: ' . $this->signThumbprint,
        ];

        // Initialize cURL
        $ch = curl_init($this->apiUrl);

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
        return $this->makeRequest('echo', $params);
    }
}
