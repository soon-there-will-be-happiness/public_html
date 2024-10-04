<?php 
namespace Connect\Vkontakte\api\src;

defined('BILLINGMASTER') or die;

class main{

    /** @var bool Запись ошибок  */
	public $logger = false;

	public $from_id;
	public $chat_id;
	public $msg_id;
	public $c_msg_id;

	public $send_to = false;

	public $reply_markup = false;
	public $random_id;

	private $access_token;
	private $group_id;
	private $v = '5.130';

    /**
     * main constructor.
     *
     * @param array $data
     * @param string $token
     */
	public function __construct(array $data, string $token){

		$this->access_token = $token;

        $this->from_id = $data['from_id'] ?? 0;

        $this->chat_id = $data['peer_id'] ?? $this->from_id;

		$this->msg_id = $data['message_id'] ?? null;

		$this->c_msg_id = $data['conversation_message_id'] ?? null;

		$this->random_id = floor(microtime(true) * 1000);
	}

    /**
     * @param false $id
     * @return $this
     */
	function _chat_id($id = false){
		if(is_numeric($id))
			$this->chat_id = $id;

		return $this;
	}

    /**
     * @param false $id
     * @return $this
     */
	function _user_id($id = false){
		if(is_numeric($id))
			$this->from_id = $id;

		return $this;
	}

    /**
     * @param $id
     * @return $this
     */
	function _setChat($id){
		
		$this->send_to = $id;

		return $this;
	}

    /**
     * @param $keyboard
     * @return $this
     */
	function _addKeyboard($keyboard){

		#todo
		
		return $this;
	}

    /**
     * Отправить сообщение
     * @param string $text
     *
     * @return false|mixed
     */
	function MessagesSend(string $text){

		$data = [
			'message' => $text,
			'peer_id' => $this->send_to,
			'random_id' => $this->random_id
		];

		return $this->request("messages.send", $data);
	}

    /**
     * Ответ при нажатии кнопки
     * @param $event_id
     * @param $event_data
     * @return false|mixed
     */
	function sendMessageEventAnswer($event_id, $event_data){

		$data = [
			'event_id' => $event_id,
			'user_id' => $this->from_id,
			'peer_id' => $this->chat_id,
			'event_data' => $event_data
		];

		return $this->request("messages.sendMessageEventAnswer", $data);
	}

    /**
     * @param array $user_ids
     * @param false $fields
     * @param false $name_case
     *
     * @return false|mixed
     */
    function usersGet(array $user_ids, $fields = false, $name_case = false) {

        $data = ['user_ids' => $user_ids];

        if ($fields) {
            $data['fields'] = $fields;
        }

        if ($name_case) {
            $data['name_case'] = $name_case;
        }

        return $this->request("users.get", $data);
    }

    /**
     * Редактировать сообщение
     * @param string $text
     * @param int    $conversation_message_id
     * @return false|mixed
     */
	function MessagesEdit(string $text, int $conversation_message_id = 0){
		if($conversation_message_id == 0 && $this->c_msg_id) 
			$conversation_message_id = $this->c_msg_id;

		$data = [
			'message' => $text,
			'peer_id' => $this->send_to,
			'conversation_message_id' => $conversation_message_id == 0
		];

		return $this->request("messages.edit", $data);
	}

    /**
     * @param $message_ids
     * @return false|mixed
     */
	function MessagesDelete($message_ids){
		$data = [
			'message_ids' => $message_ids
		];

		return $this->request("messages.delete", $data);
	}

    /**
     * Отправить запрос в api
     *
     * @param string $method
     * @param array  $data
     * @return false|mixed
     */
	function request(string $method, array $data = []){
        $data['access_token'] = $this->access_token;
        $data['v'] = $this->v;

        if (isset($data['peer_id']) && !$data['peer_id']) {
            $data['peer_id'] = $this->chat_id;
        }

        $curl = new \Curl('https://api.vk.com/method/' . $method);
        $curl->POST($data);

        if ($curl() && $result = json_decode($curl(), true)) {

            if ($this->logger) {
                $msg = !isset($result['ok']) ? "error" : "log";
                $msg .= ":".$method;
                \Log::add(!isset($result['ok']) ? 4 : 1,
                    "Connect " . $msg,
                    ["result" => $result, "method" => $method, "curl" => $curl]
                );
            }

            return isset($result['response']) ? $result : false;
        }

        return false;
	}

}

