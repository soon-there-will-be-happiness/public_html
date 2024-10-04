<?php 
namespace Connect\Telegram\api\src;

defined('CONNECT_TG_BOT') or die;

class keyboard{

	private $type;
	private $keyboard;
	private $datakb = false;
	
	function __construct(array $keyboard)	{
		$this->type = $keyboard[0];
		$this->keyboard = $keyboard[1];

		if(isset($keyboard[2]) && $keyboard[0] == 'keyboard')
			$this->datakb = $keyboard[2];
	}

    /**
     * @param array $data
     * @return false|string
     */
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

		if($this->type == 'keyboard'){

			foreach ($this->keyboard as $line) {
				$mas = [];

				foreach ($line as $button) 
					$mas[] = $this->KeyboardButton($button);
				
				$kb[] = $mas;
			}
			
			$kb = ['keyboard' => $kb];

			if(is_array($this->datakb)){
				foreach ($this->datakb as $key => $value) 
					if(in_array($key, ['resize_keyboard', 'one_time_keyboard', 'input_field_placeholder', 'selective']))
						$kb[$key] = $value;
			}

			if(is_array($data)){
				foreach ($data as $key => $value) 
					if(in_array($key, ['resize_keyboard', 'one_time_keyboard', 'input_field_placeholder', 'selective']))
						$kb[$key] = $value;
			}
		}

		if($kb == [])
			return false;

		return json_encode($kb);

	}

    /**
     * @param $button
     * @return array
     */
	private function KeyboardButton($button){

		$res = [];

		$res['text'] = $button[0];

		if(isset($button[1])){
			switch ($button[1]) {
				case 'con':
				case 'cont':
				case 'contact':
				case 'request_contact':
					$res['request_contact'] = true;
					break;
				case 'loc':
				case 'locat':
				case 'location':
				case 'request_location':
					$res['request_location'] = true;
					break;
			}
		}

		return $res;
	}

    /**
     * @param $button
     * @return array
     */
	private function inlineKeyboardButton($button){
        $res = [];

        $res['text'] = $button[0];

        if (isset($button[1])) {

            if (is_string($button[1])) {
                $res['callback_data'] = $button[1];
            }

            if (is_array($button[1])) {
                foreach ($button[1] as $key => $value) {
                        if ($key == 'url' || $key == 'callback_data' || $key == 'switch_inline_query' || $key == 'switch_inline_query_current_chat' || $key == 'callback_game') {
                            $res[$key] = $value;
                        }
                }
            }
        } else {
            $res['callback_data'] = 'error_genirte';
        }

        return $res;
	}
}