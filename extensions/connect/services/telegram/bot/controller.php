<?php
namespace Connect\Telegram\bot;
defined('BILLINGMASTER') or die;

use Connect\Telegram\bot\src\CommandHandler;
use Connect\Telegram\bot\src\updateHandler;
use Connect\Telegram\bot\src\Telegram;

/**
 * Controller. Класс отвечает за обработку входящих вебхуков от телеграмма
 * @package Connect\Telegram\bot
 */
class Controller{

    use updateHandler;

    /** @var array пришедшие данные */
	private $data;
	/** @var \Connect\Telegram\bot\src\mainFunctions */
	private $api;
	private $btns;

	private $user;
	private $chat;

	private $all_chats;

	function __construct(array $updates, string $bot = 'main'){
		$this->all_chats = src\Telegram::getChats();

		$update = $this->update($updates, $bot);
		if (!$update) {
		    return;
        }

        src\Telegram::$api = $this->api;
		$this->requestHandler($update);
	}

    /**
     * Обработать входящий колбек тг
     * @param $update
     */
	private function requestHandler($update) {
        if(isset($update['from']['is_bot']) && $update['from']['is_bot'] != true) {
            return;
        }

        $type = $update[0];
        $user_id = @$this->data['from']['id'];
        $args = isset($update[1]) && is_array($update[1]) ? $update[1] : [];

        if ($user_id) {
            $this->user = src\Telegram::getUserByUserId($user_id, @ $this->data['from']);
        }

        if (!$this->user && isset($this->data['from'])) {
            $this->user = $this->data['from'];
            $userAddResult = src\Telegram::addUnregisteredUser($this->user, $this->user);
            if ($userAddResult) {
                $this->user = src\Telegram::getUserByUserId($user_id, $this->user);
            }
        }

        if(($this->api)('chat_id') < 0 && !empty($update['chat'])){
            $this->chat = src\Telegram::getChat($update['chat']);
            $this->checkChat($user_id);
        } elseif ((!is_numeric($this->user['sm_user_id']) || $this->user['sm_user_id'] == 0) && $type != 'Command') {
            return;
        }

        if(method_exists($this, 'new' . $type) && ($method = 'new' . $type)){
            $this->$method(... $args);
        }

    }

    // МЕТОДЫ ОБРАБОТЧИКИ ВХОДЯЩЕГО КОЛБЕКА.
    // начинаются с new...

	# новое сообщение
	private function newMessage($text){

	}
	private function newChannelPost($text){

	}

    /**
     * Событие. Новая команда
     * @param $cmd
     * @return mixed
     */
	private function newCommand($cmd){
	    $handler = new CommandHandler($cmd, $this->user, $this->data);
	    return $handler->execCmd();
	}

    /**
     * Событие. Нажата кнопка с клавиатуры
     * @param $text
     * @return mixed|null
     */
	private function newButtonSub($text){
		if($this->api->sendPreMessage($this->btns[$text]))
			return null;

        $cmd = $this->btns[$text];
        if(substr($cmd, 0, 1) == '%')
            $cmd = substr($cmd, 1);
        $cmd = explode('#', $cmd);

        $handler = new CommandHandler($cmd, $this->user, $this->data);
        $handler->setIsBtn(true);

        return $handler->execCmd();
	}

    /**
     * Событие. Нажата inline кнопка
     * @param $query
     * @param $q_id
     * @return mixed
     */
	private function newInlineQuery($query, $q_id){
        $handler = new CommandHandler($query, $this->user, $this->data);
        $handler->setIsInlineQuery(true);
        $handler->setQ_id($q_id);

        return $handler->execCmd();
	}

    /**
     * Событие. Добавлен новый юзер в чат
     * @param $members
     */
    private function newChatMembers($members){
        if($members[0]['id'] == $this->api->bot_id) { // добавили этого бота
            return $this->onBotAdded();
        }
        $chatID = $this->api->tg->chat_id;
        $kickNewUser = false;
        $newUser = \Connect::getUserByServiceID("telegram",$members[0]['id']);

        if (!$this->user || !$newUser) { //Юзер не зарегистрирован - кик
            return $this->api->ban($members[0]['id'], $chatID);
        }


        $canAccess = $this->checkUserGroupOrSub($newUser, $chatID);

        if (!$canAccess) {
            return $this->api->ban($members[0]['id'], $chatID);
        }
    }

    private function onBotAdded() {
        return $this->api->sendMessageWithDeleteKeyboard("Hello! Chat added.\n\nChat ID: <code>" . ($this->api)('chat_id') . "</code>");
    }

    private function checkUserGroupOrSub($user, $chatID) {
        $haveGroup = false;
        $haveSub = false;
        $user_id = $user['user_id'] ?? $user['id'];
        if (!$user_id) {
            return false;
        }

        $userGroups = \User::getGroupByUser($user_id);
        $userSubs = \Member::getActivePlanes2User($user_id);

        if ($userGroups) {
            foreach ($userGroups as $userGroup) {
                $userGroup = \User::getUserGroupData($userGroup);
                if (!$userGroup) {
                    continue;
                }
                if ($userGroup['del_tg_chats'] == $chatID) {
                    $haveGroup = true;
                    break;
                }
            }
        }

        if ($userSubs) {
            foreach ($userSubs as $userSub) {
                if ($userSub['del_tg_chats'] == $chatID) {
                    $haveSub = true;
                    break;
                }
            }
        }

        return $haveGroup || $haveSub;
    }

    /**
     * Событие. Юзер вышел из чата
     * @param $member
     */
    private function newLeftChat($member){
        if($member['id'] == $this->api->bot_id) // удалили этого бота
            return;

        /*else{ // удалили другого участника #todo
            // $this->api->sendMessage('Bye...');
            //$this->api->sendMessage('Bye...');
        }*/
    }
}