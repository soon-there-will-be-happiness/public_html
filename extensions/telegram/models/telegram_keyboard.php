<?php 

class TelegramKeyboard{

	private $type;
	private $keyboard;
	private $datakb = false;
	
	function __construct(string $type, array $keyboard, $data = false)	{
		$this->type = $type;
		$this->keyboard = $keyboard;

		if(isset($data) && $type == 'keyboard')
			$this->datakb = $data;
	}

	function valid($data = false){

		$kb = [];

		if($this->type == 'inline'){
			foreach ($this->keyboard as $line) {
				$mas = [];

				foreach ($line as $button)
					$mas[] = $this->inlineKeyboardButton($button);
				
				$kb[] = $mas;
			}
			
			$kb = ['inline_keyboard' => $kb];
		}

		elseif($this->type == 'keyboard'){

			foreach ($this->keyboard as $line) {
				$mas = [];

				foreach ($line as $button) 
					$mas[] = $this->KeyboardButton($button);
				
				$kb[] = $mas;
			}
			
			$kb = ['keyboard' => $kb];

			if(is_array($this->datakb))
				$data = is_array($data) ? $data + $this->datakb : $this->datakb;

			if(is_array($data)){
				foreach ($this->datakb as $key => $value) 
					if(in_array($key, ['resize_keyboard', 'one_time_keyboard', 'input_field_placeholder', 'selective']))
						$kb[$key] = $value;
			}
		}

		elseif($this->type == 'remove'){

			$kb = ['remove_keyboard' => true];
		}

		if($kb == [])
			return false;

		return json_encode($kb);

	}


	private function KeyboardButton($button){

		$res = [];

		$res['text'] = $button[0];

		if(isset($button[1])){
			switch ($button[1]) {
				case 'contact':
				case 'request_contact':
					$res['request_contact'] = true;
					break;

				case 'location':
				case 'request_location':
					$res['request_location'] = true;
					break;
			}
		}

		return $res;
	}


	private function inlineKeyboardButton($button){
		$res = [];

		$res['text'] = $button[0];

		if(isset($button[1])){

			if(is_string($button[1]))
				$res['callback_data'] = $button[1];

			if(is_array($button[1])){

				foreach ($button[1] as $key => $value) {
					if(in_array($key, ['url', 'callback_data', 'switch_inline_query', 'switch_inline_query_current_chat', 'callback_game']))
						$res[$key] = $value;
				}
			}
		}

		else
			$res['callback_data'] = 'error_genirte';

		return $res;
	}
}