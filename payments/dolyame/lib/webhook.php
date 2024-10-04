<?php defined('BILLINGMASTER') or die;

class Dolyame_webhook{

	protected $api_url = 'https://partner.dolyame.ru/v1/orders/';
	protected $mask_ip = "91.194.226.0/23";

	protected $webhook = '';
	protected $data    = '';
	protected $logger  = false; // сохранение логов запроса

	private $api;

	function __construct($webhook){

		$this->webhook = $webhook;
		$this->data = json_decode($webhook, true);

	}

	public function isVerify(){
		$res = false;

		if(isset(
			$this->data['id'],
			$this->data['status'],
			$this->data['amount']
		)){
			$ip = $_SERVER['REMOTE_ADDR'];
			$res = $this->cidr_match($ip);
		}

		if($this->logger)
			file_put_contents(__DIR__ . '/../files/requests.log', date('d.m.Y H:i:s') . " [new Webhook] \n" . $webhook . "\n     > isVerify: " . $res ? 'true' : 'false' . "\n\n", FILE_APPEND);

		return $res;
	}

	public function getStatus(){

		return @ $this->data['status'];
	}

	public function getOrderID(){

		if(is_numeric($this->data['id']))
			return $this->data['id'];

		return explode("-", $this->data['id'])[0];
	}

	public function getOrderAmount(){

		return @ $this->data['amount'];
	}

	public function getClientInfo(){

		return @ $this->data['client_info'];
	}

	public function getPaymentShedule(){

		return @ $this->data['payment_schedule'];
	}

	private function cidr_match($ip){

	    list ($subnet, $bits) = explode('/', $this->mask_ip);

	    if ($bits === null) 
	        $bits = 32;
	    
	    $ip = ip2long($ip);
	    $subnet = ip2long($subnet);
	    $mask = -1 << (32 - $bits);

	    $subnet &= $mask; 

	    return ($ip & $mask) == $subnet;
	}
}