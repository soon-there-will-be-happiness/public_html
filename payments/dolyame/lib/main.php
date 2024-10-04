<?php defined('BILLINGMASTER') or die;

class Dolyame_functions{

	public $prefix = "dd_SM";

	protected $api_url  = 'https://partner.dolyame.ru/v1/orders/';

	protected $script_url = '';
	protected $certPath = '';
	protected $keyPath  = '';
	protected $logger   = false; // сохранение логов запроса ['show_error' - вывод ошибки на экран]

	private $correlationId;
	private $authorization;

	function __construct($params){

		$this->correlationId = $this->generateCorrelationId();

		$this->authorization = ($params['login'] . ":" . $params['password']);

		if(isset($params['cert_path']) && !empty($params['cert_path']))
			$this->certPath = __DIR__ . '/../files/' . $params['cert_path'];

		if(isset($params['key_path']) && !empty($params['key_path']))
			$this->keyPath = __DIR__ . '/../files/' . $params['key_path'];
	}

	public function setScriptURL($url){
		$this->script_url = $url;

		return $this;
	}


	public function CreateLink($order_id, $amount, $items, $user_info = []){

	    $data = [
	        'order' => [
	            'id' => $order_id . '-' . $this->prefix,
	            'amount' => $amount,
	            'items' => $items
	        ],
	        'fail_url' => "{$this->script_url}/fail.php",
	        'success_url' => "{$this->script_url}/success.php",
	        'notification_url' => "{$this->script_url}/result.php"
	    ];

	    if(!empty($user_info)){
	    	$data['client_info'] = $user_info;
	    }

	    $res = $this->execute('create', json_encode($data));

	    if($res){
	    	if(isset($res['link']))
	    		$res = $res['link'];
	    }

	    return $res;
	}

	public function orderInfo($order_id){

	    $res = $this->execute($order_id . '-' . $this->prefix . '/info', '', false);

	    return $res;
	}

	public function orderCancel($order_id){

	    $res = $this->execute($order_id . '-' . $this->prefix . '/cancel', json_encode($data));

	    return $res;
	}

	public function orderCommit($order_id, $amount, $items){

	    $data = [
            'amount' => $amount,
            'items' => $items
	    ];

	    $res = $this->execute($order_id . '-' . $this->prefix . '/commit', json_encode($data));

	    return $res;
	}

	public function orderRefund($order_id, $amount, $items){

	    $data = [
            'amount' => $amount,
            'returned_items' => $items
	    ];

	    $res = $this->execute($order_id . '-' . $this->prefix . '/refund', json_encode($data));

	    return $res;
	}



	protected function execute(string $method, string $data, bool $post = true){
		
		$headers = [
			"Content-Type: application/json",
			"X-Correlation-ID: $this->correlationId",
			"Authorization: Basic " . base64_encode($this->authorization) . ""
		];

		$responseHeaders = '';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_url . $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->authorization);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
			$responseHeaders .= $header;
			return strlen($header);
		});

		$res = date('d.m.Y H:i:s') . " [execute]\n  ";

		if ($this->certPath) {

			if (!file_exists($this->certPath)) 
				$res .= " [!] Cert path did\'t exist: {$this->certPath}\n  ";
			
			if (!file_exists($this->keyPath)) 
				$res .= " [!] Key path did\'t exist: {$this->keyPath}\n  ";
		
			if (!is_readable($this->certPath)) 
				$res .= " [!] Can\'t read cert file: {$this->certPath}\n  ";

			if (!is_readable($this->keyPath)) 
				$res .= " [!] Can\'t read key file: {$this->keyPath}\n  ";

			curl_setopt($ch, CURLOPT_SSLCERT, $this->certPath);
			curl_setopt($ch, CURLOPT_SSLKEY, $this->keyPath);
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		if($post){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}else{
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET');
		}
		

		$out = curl_exec($ch);

		$curlError = curl_error($ch);
		if ($curlError) 
			$res .= " [!] {$curlError}\n  ";
	
		$res .= "method: {$method}\n  ";
		$res .= "request: {$data}\n  ";
		$res .= "response: {$out}\n  ";

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$response = json_decode($out, true);

		if ($code == 200) {
			if ($this->logger)
				file_put_contents(__DIR__ . '/../files/requests.log', $res . "\n", FILE_APPEND);

			return $response;

		} elseif ($code == 429) {
			$headers = $this->parseHeadersToArray($responseHeaders);
			sleep($headers['X-Retry-After']);

			return $this->execute($method, $data);
		}
		
		if (isset($response['type']) && $response['type'] == 'error') 
			$res .= " [!] {$response['description']}\n  ";
		
		if (isset($response['message'])) 
			$res .= " [!] {$response['message']}\n  ";
		
		if (!empty($response['details'])) {
			$list = array_map(
				function($key, $value){return "$key - $value";},
				array_keys($response['details']),
				array_values($response['details'])
			);

			$res .= " [!] " . implode($list) . "\n  ";
		}

		if (!$response) 
			$res .= "response: {$out}";

		if($this->logger){
			file_put_contents(__DIR__ . '/../files/requests.log', '[ERROR] ' . $res . "\n\n", FILE_APPEND);

			if(is_string($this->logger) && $this->logger === 'show_error'){
				echo "<pre>"; var_dump($res); exit();
			}
		}

		return null;
	}

	private function generateCorrelationId(){

		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	private function parseHeadersToArray($rawHeaders){

		$lines = explode("\r\n", $rawHeaders);
		$headers = [];

		foreach($lines as $line) {
			if (strpos($line, ':') === false)
				continue;
			
			list($key, $value) = explode(': ', $line);
			$headers[$key] = $value;
		}

		return $headers;
	}

}