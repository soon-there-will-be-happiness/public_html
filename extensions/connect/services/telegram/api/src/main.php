<?php 
namespace Connect\Telegram\api\src;

defined('CONNECT_TG_BOT') or die;

class main{

	public $logger = false; /*false - откл, 1 - запись ошибок, 2 - запись всего*/

	public $from_id;
	public $chat_id;
	public $msg_id;

	public $send_to = false;

	public $reply_markup = false;

	private $token;

	public function __construct(array $data, string $token){
		$this->token = $token;

        $this->from_id = $data['from']['id'] ?? 0;

        $this->chat_id = $data['chat']['id'] ?? $data['message']['chat']['id'] ?? $this->from_id;

        $this->msg_id = $data['message_id'] ?? $data['message']['message_id'] ?? null;
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
		if(!is_array($keyboard) || !is_array(@ $keyboard[1]) || !is_array(@ $keyboard[1][0]))
			return $this;

		$kb_c = new keyboard($keyboard);

		$kb = $kb_c->valid(['resize_keyboard' => true]);

		$this->reply_markup = $kb;

		return $this;
	}

    /**
     * @return $this
     */
	function _deleteKeyboard(){
        $this->reply_markup = '{"remove_keyboard": true}';

		return $this;
	}

    /**
     * @param string $text
     * @param $html
     * @param false $message_id
     * @return false|mixed
     */
	function editMessageText(string $text, $html, $message_id = false){

		if (!is_numeric($message_id))
			$message_id = $this->msg_id;

		$data = [
			'text' => $text,
			'message_id' => $message_id
		];

		if (is_array($html)){
			foreach ($html as $key => $value)
				$data['$key'] = $value;

		} else if ($html)
			$data['parse_mode'] = "html";

		return $this->request('editMessageText', $data);
	}

    /**
     * @param array $keyboard
     * @param false $message_id
     * @return false|mixed
     */
	function editMessageReplyMarkup(array $keyboard, $message_id = false){

		if (!is_numeric($message_id))
			$message_id = $this->msg_id;

		$data = ['message_id' => $message_id];

		$kb = new keyboard($keyboard);

		$kb = $kb->valid();

		$this->reply_markup = $kb;

		return $this->request('editMessageReplyMarkup', $data);
	}

    /**
     * @param string $callback_query_id
     * @param false $req
     * @return false|mixed
     */
	function answerCallbackQuery(string $callback_query_id, $req = false){
		$data = [
			'callback_query_id' => $callback_query_id,
			'chat_id' => $this->chat_id
		];

		if ($req){
			foreach ($req as $key => $value) {
				if (in_array($key, ['text', 'show_alert', 'url', 'cache_time']))
					$data[$key] = $value;
			}
		}

		return $this->request('answerCallbackQuery', $data);
	}

    /**
     * Отправить сообщение
     * @param $text
     * @param false $html
     * @return false|mixed
     */
	function sendMessage($text, $html = false){
		$data = [
			"text" => $text
		];

		if (is_array($html)){
			foreach ($html as $key => $value) {
                $data[$key] = $value;
            }
		} elseif ($html) {
            $data['parse_mode'] = "html";
        }

		return $this->request('sendMessage', $data);
	}

    /**
     * @param $photo
     * @param false $caption
     * @param false $html
     * @return false|mixed
     */
	function sendPhoto($photo, $caption = false, $html = false){
		$data = [
			"photo" => $photo
		];

		if (is_string($caption))
			$data['caption'] = $caption;

		if (is_array($html)) {
			foreach ($html as $key => $value)
				$data[$key] = $value;

		} else if($html)
			$data['parse_mode'] = "html";

		return $this->request('sendPhoto', $data);
	}

    /**
     * @param $audio
     * @param false $caption
     * @param false $html
     * @return false|mixed
     */
	function sendAudio($audio, $caption = false, $html = false){
		$data = [
			"audio" => $audio
		];

		if (is_string($caption))
			$data['caption'] = $caption;

		if (is_array($html)){
			foreach ($html as $key => $value)
				$data[$key] = $value;

		} else if($html)
			$data['parse_mode'] = "html";

		return $this->request('sendPhoto', $data);
	}

    /**
     * @param $media
     * @param $caption
     * @param $html
     * @return false|mixed
     */
    function sendMedia($media, $caption, $html) {
        $data = [
            'chat_id' => $this->chat_id,
            'caption' => $caption
        ];

        if ($html)
            $data['parse_mode'] = "HTML";

        if (is_string($media)) {
            $data['media'] = new CURLFile($media);
        } elseif (is_array($media) && isset($media['media'])) {
            $data['media'] = $media['media'];
        } else {
            return false;
        }

        $method = match ($media['type']) {
            'photo' => 'sendPhoto',
            'audio' => 'sendAudio',
            'video' => 'sendVideo',
            'document' => 'sendDocument',
            'animation' => 'sendAnimation',
            'voice' => 'sendVoice',
            default => false
        };

        return $method ? $this->request($method, $data) : false;
    }


    /**
     * @param $medias
     * @param false $msg
     * @param false $parse_mode_html
     * @return false|mixed
     */
	function sendMediaGroup($medias, $msg = false, $parse_mode_html = false){
		$media = new medias($medias);

		$data = [
			"media" => $media->valid($msg, $parse_mode_html)
		];

		return $this->request('sendMediaGroup', $data);
	}

    /**
     * @param $text
     * @param false $message_id
     * @param false $html
     * @return false|mixed
     */
	function replyMessage($text, $message_id = false, $html = false){
		if(!is_numeric($message_id))
			$message_id = $this->msg_id;

		$data = [
			"reply_to_message_id" => $message_id,
			"text" => $text
		];

		if (is_array($html)){
			foreach ($html as $key => $value)
				$data['$key'] = $value;

		} else if($html)
			$data['parse_mode'] = "html";

		return $this->request('sendMessage', $data);
	}

    /**
     * @param false $message_id
     * @return false|mixed
     */
	function deleteMessage($message_id = false){
		if (!is_numeric($message_id))
			$message_id = $this->msg_id;

		$data = [
			'message_id' => $message_id
		];

		return $this->request('deleteMessage', $data);
	}

    /**
     * @param string $file_id
     * @return mixed
     */
	function getFile(string $file_id){
		$data = [
			'file_id' => $file_id
		];

		return $this->request('getFile', $data)['result'];
	}

    /**
     * @return false|mixed
     */
	function kickChatMember(){
		$data = [
			'user_id' => $this->from_id
		];

		return $this->request('kickChatMember', $data);
	}

    function kickChatUser($tg_user_id){
        $data = [
            'user_id' => $tg_user_id
        ];

        return $this->request('kickChatMember', $data);
    }

    /**
     * @return false|mixed
     */
	function unbanChatMember(){
		$data = [
			'user_id' => $this->from_id
		];

		return $this->request('unbanChatMember', $data);
	}

    /**
     * @return false|mixed
     */
	function getChatMember(){
		$data = [
			'user_id' => $this->from_id
		];

		return $this->request('getChatMember', $data);
	}

    /**
     * @return false|mixed
     */
	function getChat(){
		return $this->request('getChat');
	}


    /**
     * Отправить запрос в тг
     * @param string $method
     * @param array $data
     *
     * @return false|mixed
     */
	private function request(string $method, array $data = []) {

		if(!$this->send_to)
			$this->send_to = $this->chat_id;

		if($this->reply_markup){
			$data['reply_markup'] = $this->reply_markup;
			$this->reply_markup = false;
		}

		if(!isset($data['chat_id']))
			$data['chat_id'] = $this->send_to;

        $curl = new \Curl('https://api.telegram.org/bot' . $this->token . '/'.$method);
        $curl->POST($data);

        if($curl() && $result = json_decode($curl(), true)){

        	if($this->logger){

        	    \Log::add(
                    !isset($result['ok']) ? 4 : 1,
                    "Connect ". !isset($result['ok']) ? "error" : "log" .":".$method,
                    ["result" => $result]
                );
        	}

            return isset($result['ok']) && $result['ok'] ? $result : false;
		}

		return false;
	}

}
