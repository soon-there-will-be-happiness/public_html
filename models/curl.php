<?php defined('BILLINGMASTER') or die;

class Curl {

	public $url;
	public $headers;
	public $files;

	public $ssl_ver = false;

	public $errors = [];

	public $req_type = '';
	public $result;

	protected $ch;

	function __construct(string $url, array $headers = []){
		$this->url = $url;

		$this->setHeaders($headers);

		$this->ch = curl_init(); 
	}

	public function __invoke(){
		return @ $this->result;
	}

	public function GET(array $data = [], bool $to_json = false){
		$this->req_type = 'GET';
		if($to_json){
			$this->addHeaders(['Content-Type' => 'application/json']);
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		}

		else{
			$this->url = $this->url . '?' . http_build_query($data, '', '&');
			$data = null;
		}

		$this->data = $data;

		$this->url()->httpheader();

		curl_setopt($this->ch, CURLOPT_POSTFIELDS, @ $data);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');

		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_ver);
		curl_setopt($this->ch, CURLOPT_HEADER, false);

		return $this->result();
	}

	public function POST(array $data = [], bool $to_json = false){
		$this->req_type = 'POST';
		if($to_json){
			$this->addHeaders(['Content-Type' => 'application/json']);
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		}

		else
			$data = http_build_query($data, '', '&');

		$this->data = $data;

		$this->url()->httpheader();

		curl_setopt($this->ch, CURLOPT_POST, 1);

		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, @ $data);

		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_ver);
		curl_setopt($this->ch, CURLOPT_HEADER, false);

		return $this->result();
	}

	public function addHeaders(array $headers){
		$this->headers = $headers + $this->headers;

		return $this;
	}

	public function setHeaders(array $headers){
		$this->headers = $headers;

		return $this;
	}

	public function getErrors(){
		return $this->errors;
	}

	public function showErrors(){
		$is_console = PHP_SAPI == 'cli' || (!isset($_SERVER['DOCUMENT_ROOT']) && !isset($_SERVER['REQUEST_URI']));

		if(!$is_console) print('<pre>');

		foreach ($this->errors as $error) {
			print('_______________________');
			foreach ($error as $name => $value) {
				print('    ' . $name . ' => ') . 
					(is_string($value) ? print($value) : print_r($value));
			}
		}

		if(!$is_console) print('</pre>');
	}

	private function httpheader(){

		if(!empty($this->headers) && is_array($this->headers)){
			$hd = [];
			foreach ($this->headers as $key => $value)
				$hd[] = $key . ": " . $value;

			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $hd);
		}

		return $this;
	}

	private function url(){
		curl_setopt($this->ch, CURLOPT_URL, $this->url);

		return $this;
	}

	private function result(){
		if($this->result)
			return $this->result;

		$this->result = curl_exec($this->ch);

		if(curl_errno($this->ch)){
			$this->errors[] = [
				'request_type' => $this->req_type,
				'error' => "#" . curl_errno($this->ch) . ": " . curl_error($this->ch),
				'url' => $this->url,
				'data' => $this->data,
				'headers' => $this->headers
			];

			$this->result = false;
		}

		curl_close($this->ch);

		return $this->result;
	}
}
    