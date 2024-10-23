<?php defined('BILLINGMASTER') or die;

class cyclopsApi {
    private static $instance = null;
    private $apiUrl;
    private $signSystem;
    private $signThumbprint;

    public function __construct() {
        // Load environment variables from .env or configuration
        $this->apiUrl = 'https://pre.tochka.com/api/v1/cyclops/v2/jsonrpc';
        $this->signSystem = getenv('SIGN_SYSTEM');
        $this->signThumbprint = getenv('SIGN_THUMBPRINT');
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
            "id" => uniqid()
        ]);

        $headers = [
            'Content-Type: application/json',
            'sign-system: ' . $this->signSystem,
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
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    public function echo($text) {
        $params = ["text" => $text];
        return $this->makeRequest('echo', $params);
    }
}
