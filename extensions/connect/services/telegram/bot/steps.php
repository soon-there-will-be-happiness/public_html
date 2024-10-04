<? defined('CONNECT_TG_BOT') or die;

$orig = $cmd;

if(!is_array($cmd))
	$cmd = (explode("#", $cmd));
$answer = false;

if(isset($cmd[1]) && $cmd[(count($cmd)-1)] == 'delete_this_message'){
	array_pop($cmd);
	$this->api->deleteMsg();
}

if(isset($cmd[2]) && $cmd[(count($cmd)-2)] == 'send_inline:'){
	$answer = array_pop($cmd);
	array_pop($cmd);
}

if(isset($cmd[2]) && $cmd[(count($cmd)-2)] == 'next_msg:'){
	$this->api->nextPreMessage(array_pop($cmd));
	array_pop($cmd);
}

if(!isset($this->user['sm_user_id']) && is_numeric($this->user['sm_user_id']) || $this->user['sm_user_id'] == 0)
	return;

switch ($cmd[0]) {
	
	case 'noti':
	case 'notif':
	case 'notify':
	case 'notification':
		if(!isset($cmd[1])){
			$this->api->sendPreMessage('noti');
			break;
		}

		$notif = @ $this->user['params']['telegram']['noti'];

		if($cmd[1] == 'off' && \Connect::setNotifStatus($this->user['user_id'], 'telegram', false))
			$this->api->sendPreMessage('noti-off');

		elseif($cmd[1] == 'on' && \Connect::setNotifStatus($this->user['user_id'], 'telegram', true))
			$this->api->sendPreMessage('noti-on');

		else
			$this->api->sendPreMessage('noti');

		break;
	
	default:
		if(isset($text))
			$text = $cmd[0];

		if(@ $cmd_type == 'inline')
			$query = $cmd[0];

		$this->api->sendPreMessage($cmd[0]);
		break;
} 
