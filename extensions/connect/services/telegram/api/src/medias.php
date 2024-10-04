<?php 
namespace Connect\Telegram\api\src;

defined('CONNECT_TG_BOT') or die;

class medias{

	private $medias;
	
	function __construct(array $medias)	{
		$this->medias = $medias;
	}

    /**
     * @param false $msg
     * @param false $parse_mode_html
     * @return false|string
     */
	function valid($msg = false, $parse_mode_html = false){

		$res = [];

		foreach ($this->medias as $media) {

			$type = $media['type'];
			if(method_exists($this, $type)){

				$media_array = $this->$type($media);

				if($media_array !== false)
					$res[] = $media_array;
			}
		}

		if(is_string($msg)){
			$res[0]['caption'] = $msg;

			if($parse_mode_html)
				$res[0]['parse_mode'] = 'html';
		}
		
		if($res == [])
			return false;

		return json_encode($res);
	}

    /**
     * @return false
     */
	function get() {
		$type = $this->medias['type'];

		if (method_exists($this, $type)){
			$media_array = $this->$type($this->medias);

			if($media_array !== false){
				$media_array[$media_array['type']] = $media_array['media'];
				unset($media_array['type'], $media_array['media']);
				$res = $media_array;
			}

		} else {
            return false;
        }

		return $res;
	}


    /**
     * @param $media
     * @return false|string[]
     */
	private function photo($media){
		$res = [
			'type' => 'photo'
		];

		if (isset($media['file_id']))
			$res['media'] = $media['file_id'];

		elseif (isset($media['file_url']) || isset($media['url']))
			$res['media'] = $media['file_url'] ?? $media['url'];

		elseif (isset($media['attach']))
			$res['media'] = "attach://" . $media['attach'];


		if (isset($media['caption']) || isset($media['text']))
			$res['caption'] = $media['caption'] ?? $media['text'];

		if (isset($media['parse_mode']))
			$res['parse_mode'] = is_string($media['parse_mode']) ? $media['parse_mode'] : 'html';

		if (!isset($res['type'], $res['media']))
			return false;

		return $res;
	}

    /**
     * @param $media
     * @return false|string[]
     */
	private function video($media){

		$res = [
			'type' => 'video'
		];

		if(isset($media['file_id']))
			$res['media'] = $media['file_id'];

		elseif(isset($media['file_url']) || isset($media['url']))
			$res['media'] = isset($media['file_url']) ? $media['file_url'] : $media['url'];

		elseif(isset($media['attach']))
			$res['media'] = "attach://" . $media['attach'];

		if(isset($media['thumb']))
			$res['thumb'] = $media['thumb'];
		
		if(isset($media['mime_type']))
			$res['mime_type'] = $media['mime_type'];

		if(isset($media['width']))
			$res['width'] = $media['width'];

		if(isset($media['height']))
			$res['height'] = $media['height'];

		if(isset($media['duration']))
			$res['duration'] = $media['duration'];

		if(isset($media['support_streaming']))
			$res['support_streaming'] = $media['support_streaming'];

		if(isset($media['caption']) || isset($media['text']))
			$res['caption'] = isset($media['caption']) ? $media['caption'] : $media['text'];

		if(isset($media['parse_mode']))
			$res['parse_mode'] = is_string($media['parse_mode']) ? $media['parse_mode'] : 'html';

		if(!isset($res['type'], $res['media']))
			return false;

		return $res;
	}

    /**
     * @param $media
     * @return false|string[]
     */
	private function animation($media){
		$res = [
			'type' => 'animation'
		];

		if (isset($media['file_id']))
			$res['media'] = $media['file_id'];

		elseif (isset($media['file_url']) || isset($media['url']))
			$res['media'] = isset($media['file_url']) ? $media['file_url'] : $media['url'];

		elseif (isset($media['attach']))
			$res['media'] = "attach://" . $media['attach'];


		if (isset($media['thumb']))
			$res['thumb'] = $media['thumb'];

		if (isset($media['width']))
			$res['width'] = $media['width'];

		if (isset($media['height']))
			$res['height'] = $media['height'];

		if (isset($media['duration']))
			$res['duration'] = $media['duration'];

		if (isset($media['caption']) || isset($media['text']))
			$res['caption'] = isset($media['caption']) ? $media['caption'] : $media['text'];

		if (isset($media['parse_mode']))
			$res['parse_mode'] = is_string($media['parse_mode']) ? $media['parse_mode'] : 'html';

		if (!isset($res['type'], $res['media']))
			return false;

		return $res;
	}

    /**
     * @param $media
     * @return false|string[]
     */
	private function audio($media){
		$res = [
			'type' => 'audio'
		];

		if (isset($media['file_id']))
			$res['media'] = $media['file_id'];

		elseif (isset($media['file_url']) || isset($media['url']))
			$res['media'] = $media['file_url'] ?? $media['url'];

		elseif (isset($media['attach']))
			$res['media'] = "attach://" . $media['attach'];


		if (isset($media['thumb']))
			$res['thumb'] = $media['thumb'];

		if (isset($media['duration']))
			$res['duration'] = $media['duration'];

		if (isset($media['performer']))
			$res['performer'] = $media['performer'];

		if (isset($media['title']))
			$res['title'] = $media['title'];

		if (isset($media['caption']) || isset($media['text']))
			$res['caption'] = $media['caption'] ?? $media['text'];

		if (isset($media['parse_mode']))
			$res['parse_mode'] = is_string($media['parse_mode']) ? $media['parse_mode'] : 'html';

		if (!isset($res['type'], $res['media']))
			return false;

		return $res;
	}

    /**
     * @param $media
     * @return false|string[]
     */
	private function voice($media){
		$res = [
			'type' => 'voice'
		];

		if (isset($media['file_id']))
			$res['media'] = $media['file_id'];

		elseif (isset($media['file_url']) || isset($media['url']))
			$res['media'] = $media['file_url'] ?? $media['url'];

		elseif (isset($media['attach']))
			$res['media'] = "attach://" . $media['attach'];


		if (isset($media['duration']))
			$res['duration'] = $media['duration'];

		if (isset($media['caption']) || isset($media['text']))
			$res['caption'] = $media['caption'] ?? $media['text'];

		if (isset($media['parse_mode']))
			$res['parse_mode'] = is_string($media['parse_mode']) ? $media['parse_mode'] : 'html';

		if (!isset($res['type'], $res['media']))
			return false;

		return $res;
	}

    /**
     * @param $media
     * @return false|string[]
     */
	private function document($media){
		$res = [
			'type' => 'document'
		];

		if (isset($media['file_id']))
			$res['media'] = $media['file_id'];

		elseif (isset($media['file_url']) || isset($media['url']))
			$res['media'] = $media['file_url'] ?? $media['url'];

		elseif (isset($media['attach']))
			$res['media'] = "attach://" . $media['attach'];


		if (isset($media['thumb']))
			$res['thumb'] = $media['thumb'];

		if (isset($media['disable_content_type_detection']))
			$res['disable_content_type_detection'] = $media['disable_content_type_detection'];

		if (isset($media['caption']) || isset($media['text']))
			$res['caption'] = $media['caption'] ?? $media['text'];

		if (isset($media['parse_mode']))
			$res['parse_mode'] = is_string($media['parse_mode']) ? $media['parse_mode'] : 'html';

		if (!isset($res['type'], $res['media']))
			return false;

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

			if (is_string($button[1]))
				$res['callback_data'] = $button[1];

			if (is_array($button[1])) {

				foreach ($button[1] as $key => $value) {
					if ($key == 'url' || $key == 'callback_data' || $key == 'switch_inline_query' || $key == 'switch_inline_query_current_chat' || $key == 'callback_game')
						$res[$key] = $value;
				}
			}
		} else
			$res['callback_data'] = 'error_genirte';

		return $res;
	}
}