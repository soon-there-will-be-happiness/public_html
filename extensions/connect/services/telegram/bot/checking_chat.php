<?php
namespace Connect\Telegram\bot;

defined('CONNECT_TG_BOT') or die;

if(isset($this->user, $this->data, $this->api, $this->chat, $this->all_chats) && $this->chat){

    $this->chat['data']['users'] ?? $this->chat['data']['users'] = [];

    if(!isset($this->chat['data']['users'][$user_id])){
        $this->chat['data']['users'][$user_id] = time();
        \Db::_update('telegram_chats', ['data' => $this->chat['data']], ['chat_id' => $this->chat['chat_id']]);
    }

	if ($this->user && $this->user['sm_user_id']) {

        $user_chats = src\Telegram::getChats($this->user['sm_user_id']);

        # удаляем пользователя из тг, если у него нет этого чата/канала в его группах/подписках
        if (!$user_chats || !in_array(($this->api)('chat_id'), $user_chats)) 
           	src\Telegram::delUserFromChats($this->user['sm_user_id'], null, ($this->api)('chat_id'), false, 
           		src\Telegram::EVENT_DEL_USER_FROM_CHAT
           	);
   	}

   	# пользователя нет в списке участников или не зарегистрирован в системе
    elseif($this->all_chats && in_array(($this->api)('chat_id'), $this->all_chats))  

        # удаляем пользователя из тг, если чат указан в настройках группы/подписки
        src\Telegram::delUserFromChat($user_id, ($this->api)('chat_id'), 
        	src\Telegram::EVENT_DEL_USER_FROM_CHAT, 0, $this->user['sm_user_id']
        ); 

    if($this->user['sm_user_id'] == null || $this->user['sm_user_id'] == 0){

        src\Telegram::delUserFromChat($user_id, ($this->api)('chat_id'));

    	#todo
    }
}


