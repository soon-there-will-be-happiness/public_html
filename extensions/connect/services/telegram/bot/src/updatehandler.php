<?php


namespace Connect\Telegram\bot\src;


use Connect\Telegram\api\Methods;

/**
 * Trait updateHandler
 * Отвечает за парсинг объекта Update, полученный из api
 *
 * @package Connect\Telegram\bot\src
 */
trait updateHandler {

    /** @var mainFunctions */
    private $api;
    private $data;
    private $btns;
    /** @var string тип запроса */
    private $requestType = "";

    /**
     * Определить тип пришедшего сообщения
     *
     * @param array $updates
     * @param string $bot
     *
     * @return array|false|null
     */
    public function update(array $updates, string $bot) {
        $parsedData = $this->parseUpdate($updates);

        $this->api = $this->initApi($parsedData, $bot);
        if (!($this->api)()) {
            return false;
        }
        $this->data = $parsedData['data'];
        $this->requestType = $parsedData['type'];

        return $this->getMethodForUpdate($parsedData);
    }

    /**
     * Создать апи тг
     *
     * @param array $parsedData
     * @param $bot
     *
     * @return mainFunctions
     */
    private function initApi(array $parsedData, $bot) {
        if ($parsedData['type'] == "callback_query") {
            return new mainFunctions($parsedData['data'], $parsedData['message']['message_id'], $bot);
        }

        return new mainFunctions($parsedData['data'], false, $bot);
    }

    /**
     * Сделать из массива update массив более пригодный для работы
     *
     * @param array $updates
     *
     * @return mixed|null
     */
    private function parseUpdate(array $updates) {
        switch (true) {
            case isset($updates['message']):
                $type = "message";
                $data = $updates['message'];
                break;

            case isset($updates['callback_query']):
                $type = "callback_query";
                $data = $updates['callback_query'];
                break;

            case isset($updates['my_chat_member']):
                $type = "my_chat_member";
                $data = $updates['my_chat_member'];
                break;

            case isset($updates['chat_member']):
                $type = "chat_member";
                $data = $updates['chat_member'];
                break;

            case isset($updates['channel_post']):
                $type = "channel_post";
                $data = $updates['channel_post'];
                break;

            default:
                return null;
        }

        return ["type" => $type, "data" => $data];
    }



    // ОПРЕДЕЛЕНИЕ ТИПА СООБЩЕНИЯ

    /**
     * Получить метод для обработки этого типа сообщения
     *
     * @param array $parsedData
     * @return array|null
     */
    private function getMethodForUpdate(array $parsedData) {
        $type = $parsedData['type'];
        $update = $parsedData['data'];

        switch ($type) {
            case "message":
                return $this->handleUpdateTypeMessage($update);

            case "callback_query":
                return ['InlineQuery', [$update['data'], $update['id']]];

            case "my_chat_member" OR "chat_member":
                return ['ChatPerm', [$update['new_chat_member']], true];

            case "channel_post":
                return ['ChannelPost', [$update['text']]];

            default:
                return null;
        }
    }

    /**
     * Получить метод для сообщения типа "message"
     * @param $update
     * @return array
     */
    private function handleUpdateTypeMessage($update) {
        if (isset($update['new_chat_members']) && $update['new_chat_members'])
            return ['ChatMembers', [$update['new_chat_members']], true];

        if (isset($update['left_chat_participant']) && $update['left_chat_participant'])
            return ['LeftChat', [$update['left_chat_participant']], true];

        $text = $update['text'];
        if(substr($text, 0, 1) == "/")
            return ['Command', [explode(" ", substr($text, 1))]];

        $this->btns = $this->api->getButtonsData();
        if(isset($this->btns[$text]))
            return ['ButtonSub', [$text]];

        return ['Message', [$text]];
    }

    /**
     * Проверка чата
     * @param $user_id
     */
    protected function checkChat($user_id) {
        if(isset($this->user, $this->data, $this->api, $this->chat, $this->all_chats) && $this->chat){

            $this->chat['data']['users'] ?? $this->chat['data']['users'] = [];

            if(!isset($this->chat['data']['users'][$user_id])){
                $this->chat['data']['users'][$user_id] = time();
                \Db::_update('telegram_chats', ['data' => $this->chat['data']], ['chat_id' => $this->chat['chat_id']]);
            }

            if ($this->user && $this->user['sm_user_id']) {

                $user_chats = Telegram::getChats($this->user['sm_user_id']);

                # удаляем пользователя из тг, если у него нет этого чата/канала в его группах/подписках
                if (!$user_chats || !in_array(($this->api)('chat_id'), $user_chats))
                    Telegram::delUserFromChats($this->user['sm_user_id'], null, ($this->api)('chat_id'), false,
                        Telegram::EVENT_DEL_USER_FROM_CHAT
                    );
            }

            # пользователя нет в списке участников или не зарегистрирован в системе
            elseif($this->all_chats && in_array(($this->api)('chat_id'), $this->all_chats))

                # удаляем пользователя из тг, если чат указан в настройках группы/подписки
                Telegram::delUserFromChat($user_id, ($this->api)('chat_id'),
                    Telegram::EVENT_DEL_USER_FROM_CHAT, 0, $this->user['sm_user_id']
                );

            if($this->user['sm_user_id'] == null || $this->user['sm_user_id'] == 0){
                Telegram::delUserFromChat($user_id, ($this->api)('chat_id'));
            }
        }
    }

}