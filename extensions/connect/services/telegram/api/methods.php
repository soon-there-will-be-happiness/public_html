<?php
namespace Connect\Telegram\api;

use Connect\Telegram\api\src\main;

defined('CONNECT_TG_BOT') or die;

/**
 * Class Methods
 * Является прослойкой для работы с апи телеграмма.
 * @package Connect\Telegram\api
 */
class Methods{

	public $bot;
	public $bot_id;
	public $data;
	public $collback_id = false;

	/** @var src\main  */
	public $tg;

	private $tg_anw = false;
	private $next_msg;

	private $msgs;
	private $kbds;
	private $msgs_file = SERVICE_DIR . '/bot/config/messages.json';
	private $kbds_file = SERVICE_DIR . '/api/tmp/autoload-buttons.srlz';

	private $service_data;

	public function __construct(array $data, $collback_id = false, $bot = 'main'){
		$this->checkEditMessages();
		$this->data = $data;
		$this->bot  = $bot;
		$this->collback_id = $collback_id;

		$this->service_data = \Connect::getServiceByName('telegram');
        $this->tg = false;

        require_once (ROOT."/extensions/connect/services/telegram/tg_loader.php");


        if(@$this->service_data['status'] == 1 || !empty($this->service_data['service_params']['token']))
			$this->tg = new main($data, $this->service_data['service_params']['token']);

		$this->bot_id = explode(":", $this->service_data['service_params']['token'])[0];

		unset($this->service_data['service_params']);
	}

    /**
     * Получить значение свойства из класса `src\main`
     * @param false $code
     *
     * @return mixed
     */
    public function __invoke($code = false) {
        if($this->tg === false)
        	return false;

        if(is_string($code))
        	return @ $this->tg->$code;

        return true;
    }

    /**
     * Получить данные сервис
     * @param string $key
     *
     * @return bool|null
     */
    public function getService(string $key = ''){
    	if($this->tg === false)
    		return null;

    	if(empty($key))
	    	return $this->service_data;

    	return @ $this->service_data[$key];
    }

    /**
     * Удалить сообщение
     * @return $this
     */
	public function deleteMsg(){
		$this->collback_id = false;
		$this->tg->deleteMessage();
		return $this;
	}

    /**
     * Забанить юзера
     * @param false $user_id
     * @param false $chat_id
     *
     * @return false|mixed
     */
	public function ban($user_id = false, $chat_id = false){
		return $this->tg
			->_user_id($user_id)
			->_chat_id($chat_id)
			->kickChatMember();
	}

    /**
     * Разбанить юзера
     * @param false $user_id
     * @param false $chat_id
     *
     * @return false|mixed
     */
	public function unban($user_id = false, $chat_id = false){
		return $this->tg
			->_user_id($user_id)
			->_chat_id($chat_id)
			->unbanChatMember();
	}

    /**
     * Получить участника чата
     * @param false $user_id
     * @param false $chat_id
     *
     * @return false|mixed
     */
	public function getChatMember($user_id = false, $chat_id = false){
		return $this->tg
			->_user_id($user_id)
			->_chat_id($chat_id)
			->getChatMember();
	}

    /**
     * Получить чат
     * @param false $chat_id
     *
     * @return false|mixed
     */
	public function getChat($chat_id = false){
		return $this->tg
			->_chat_id($chat_id)
			->getChat();
	}

	public function answer($id, $data = false, bool $alert = false){

		if(is_string($data))
			$data = ['text' => $data];

		if($alert)
			$data = ['show_alert' => true];

		return $this->tg->answerCallbackQuery($id, $data);
	}

    /**
     * Отправить сообщение
     * @param string $text
     * @param false $keyboard
     *
     * @return false|mixed
     */
	public function sendMessage(string $text, $keyboard = false){
		return $this->tg
			->_addKeyboard($keyboard)
			->sendMessage($text, true);
	}

	public function sendMessageWithDeleteKeyboard(string $text) {
        return $this->tg
            ->_deleteKeyboard()
            ->sendMessage($text, true);
    }

	public function sendPreMessage(string $msg_code, $repl = false, $edit = false){
		$res = false;

		$msg = $this->getMsgs($this->bot);

		if(!isset($msg[$msg_code]))
			return false;

		$text = empty($msg[$msg_code][0]) ? false : $msg[$msg_code][0];

		if($text && is_array($repl) && !empty($repl)){
			if(isset($repl['!before']) || isset($repl[0]['!before']))
				$text = '!before' . $text;

			if(isset($repl['!after']) || isset($repl[0]['!after']))
				$text .= '!after';

			$text = str_replace($repl[0], $repl[1], $text);
		}

		if(isset($msg[$msg_code][1]) && ($data = $msg[$msg_code][1])){
			$edit = $edit 
				? $edit 
				: @ $data['edit_message'] == true;

			if(isset($data['keyboard']) && ($kb = $data['keyboard'])
				&& isset($msg[$kb])
			)
				$this->tg->_addKeyboard($msg[$kb]);


			if(isset($data['media']) && ($media = $data['media'])){
				$medias = $this->getMsgs('medias');

				if(is_array($media)){
					$send_medias = [];

					foreach ($media as $key) {
						if(isset($medias[$key]))
							$send_medias[] = $medias[$key];
					}

					$res = count($send_medias) > 1 
						? $this->tg->sendMediaGroup($send_medias, $text, (bool) $text)
						: $this->tg->sendMedia($send_medias[0], $text, (bool) $text);
				}

				elseif(isset($medias[$media]))
					$res = $this->tg->sendMedia($medias[$media], $text, (bool) $text);
			}
		}	


		if(!$res && $edit && is_numeric($edit) || $this->collback_id)
			$res = $this->tg->editMessageText($text, true, is_numeric($edit) ? $edit : $this->collback_id);

		elseif(!$res)
			$res = $this->tg->sendMessage($text, true) or false;


		if(isset($data['next_msg']))
			$res = $this->sendPreMessage($data['next_msg']);

		if(is_string($this->next_msg) && $this->next_msg != $msg_code){
			$next_msg = $this->next_msg;
			$this->next_msg = null;
			$res = $this->sendPreMessage($next_msg);
		}

		return $res;		
	}


	public function nextPreMessage($code){
		$this->next_msg = $code;

		return $this;
	}

	public function send_nextPreMessage(){
		if($this->next_msg)
			return $this->sendPreMessage($this->next_msg);

		return null;
	}

	private function getMsgs(string $bot = '', string $msg_id = ''){
		if(!$this->msgs){
			$file = file_get_contents($this->msgs_file);
			$this->msgs = @ json_decode($file, true);
		}

		if(empty($bot))
			return $this->msgs;

		if(empty($msg_id))
			return @ $this->msgs[$bot];

		return @ $this->msgs[$bot][$msg_id];
	}
	

	public function getButtonsData(){
		if(!$this->kbds)
			$this->kbds = unserialize(file_get_contents($kbds_file));

		if(isset($kbds[$this->bot]))
			return $kbds[$this->bot];

		$msgs = $this->getMsgs($this->bot);

		if(!$msgs)
			return [];

		$kb_data = [];

		foreach ($msgs as $key => $kb) {
			if(!isset($kb[0], $kb[1]) || $kb[0] != 'keyboard' || !is_array($kb[1]))
				continue;

			foreach ($kb[1] as $line) {
				foreach ($line as $btn) {
					if(isset($btn[0], $btn[1]))
						$kb_data[$btn[0]] = $btn[1];
				}
			}
		}

		$this->kbds[$this->bot] = $kb_data;

		file_put_contents($this->kbds_file, serialize($this->kbds));

		return $this->kbds[$this->bot];
	}

	public function getMessagesData(){
		$file = file_get_contents($this->msgs_file);
		$msgs = json_decode($file, true);

		return @ $msgs[$this->bot];
	}

	public function log($text, $line = false): void{
        \Log::add(0, $text, ["line" => $line]);
	}

	private function checkEditMessages(): void{
		if(!file_exists($this->msgs_file) 
			|| !is_array(@ json_decode(file_get_contents($this->msgs_file), true))
		)
			file_put_contents($this->msgs_file, $this->getDefaultMessages());

		$time = filemtime($this->msgs_file);

		if(file_exists($this->kbds_file)){
			$this->kbds = unserialize(file_get_contents($this->kbds_file));

			if(isset($this->kbds['__comment'], $this->kbds['__end_edit_time'])
				&& $time == $this->kbds['__end_edit_time']
			)
				return;
		}

		$this->kbds = [
			'__comment' => "DONT TOUCH.", 
			'__end_edit_time' => $time
		];

		file_put_contents($this->kbds_file, serialize($this->kbds));
	}

	private function getDefaultMessages(){
		return json_encode(
			[
				"main" => [
			        "start" => [
			        ],
			        "start-is_connect" => [
			        	"Вы уже подключили аккаунт к школе.\n\nЖелаете в будущем получать уведомления в этот чат?",
			            ["keyboard" => "noti-why_not"]
			        ],
			        "start-not_connect" => [
			        	"Подключите аккаунт Telegram в личном кабинете школы."
			        ],
			        "noti" => [
			            "Желаете в будущем получать уведомления от школы в этот чат?",
			            ["keyboard" => "noti-why_not"]
			        ],
			        "noti-why_not" => [
			            "keyboard",
			            [
			                [
			                    [
			                        "да",
			                        "%noti#on"
			                    ],
			                    [
			                        "Нет",
			                        "%noti#off#next_msg:#noti-no_pls-no"
			                    ]
			                ]
			            ]
			        ],
			        "noti-no_pls-no" => [
			            "Вы отказались от подписки. Если передумаете, просто нажмите кнопку ниже",
			            ["keyboard" => "noti-why_not-on"]
			        ],
			        "noti-no_pls" => [
			            "Хорошо! Если передумаете, просто нажмите кнопку ниже",
			            ["keyboard" => "noti-why_not-on"]
			        ],
			        "noti-on" => [
			            "✅\n\n  Вы успешно подписались на уведомления от школы.",
			            ["keyboard" => "noti-why_not-off"]
			        ],
			        "noti-why_not-off" => [
			            "keyboard",
			            [
			                [
			                    [
			                        "Отписаться от уведомлений",
			                        "%noti#off#r"
			                    ]
			                ]
			            ]
			        ],
			        "noti-off" => [
			            "✅\n\n  Вы успешно отписались от уведомлений школы.",
			            [
			                "keyboard" => "noti-why_not-on"
			            ]
			        ],
			        "noti-why_not-on" => [
			            "keyboard",
			            [
			                [
			                    [
			                        "Подписаться на уведомления",
			                        "%noti#on#r"
			                    ]
			                ]
			            ]
			        ],
			        "auth-off" => [
			            "Авторизация через Telegram невозможна."
			        ],
			        "auth-u-off" => [
			            "Авторизация через Telegram не подключена.\n\nПодключите его в настройках Connect внутри личного кабинета школы."
			        ],
			        "auth-suc" => [
			            "Авторизация через Telegram прошла успешно! Возвращайтесь в школу"
			        ],
                    "reg-suc" => [
                        "Регистрация через Telegram прошла успешно! Возвращайтесь в школу"
                    ],
			        "auth-no_conn" => [
			            "Для того, чтобы авторизоваться в Школе через Telegram, вам необходимо подключить его в личном кабинете."
			        ],
			        "conn-off" => [
			            "Подключение Telegram к школе невозможна."
			        ],
			        "conn-suc" => [
			            "Аккаунт школы успешно привязан к Telegram!",
			            ["next_msg" => "noti"]
			        ],
			        "conn-err" => [
			            "Не удалось привязать аккаунт через Telegram."
			        ],
			        "m_auth-false" => [
			            "[TEXT]\n_ _ _\n ⌛ Вход в школу был отклонен.",
			            ["edit_message" => true]
			        ]
			    ]
			], 
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);
	}

}