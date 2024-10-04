<?php 
namespace Connect\Telegram\bot\src;

defined('CONNECT_TG_BOT') or die;

require_once SERVICE_DIR . '/api/methods.php';

class mainFunctions extends \Connect\Telegram\api\Methods{

	const EVENT_DEL_USER_FROM_CHAT = 1;
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUP = 2; // СОБЫТИЕ ПРИ УДАЛЕНИИ ГРУППЫ
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUPS = 3; // СОБЫТИЕ ПРИ УДАЛЕНИИ ГРУПП (ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЛИ РЕДАКТИРОВАНИИ ЕГО ГРУПП)
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_SUBS = 4; // СОБЫТИЕ ПРИ УДАЛЕНИИ ПОДПИСКИ
    const EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES = 5; // СОБЫТИЕ ПРИ УДАЛЕНИИ ЗАЙЦЕВ ИЗ АДМИНКИ
    const EVENT_DEL_USER_FROM_BLACKLIST = 6; // СОБЫТИЕ ПРИ УДАЛЕНИИ ПОЛЬЗОВАТЕЛЯ ИЗ ЧС TG

    /**
     * Получить названия событий
     * @return array
     */
    public static function getEventsTitles() {
        return [
            self::EVENT_DEL_USER_FROM_CHAT => 'Вход пользователя в чат/канал',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUP => 'Удаление группы',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_GROUPS => 'Удаление пользователя или его группы из админки',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_SUBS => 'Удаление подписки',
            self::EVENT_DEL_USER_FROM_CHAT_TO_DEL_HARES => 'Удаление зайцев (из админки)',
            self::EVENT_DEL_USER_FROM_BLACKLIST => 'Удаление пользователя из ЧС',
        ];
    }


	function delUserFromChat(){

	}
	
    /**
     * ЗАПИСАТЬ ЛОГ
     * @param $event_type
     * @param $event_value
     * @param $sm_user_id
     * @param $user_id
     * @param $chat_id
     * @return bool
     */
    private function writeLog($event_type, $event_value, $sm_user_id, $user_id, $chat_id) {
        $db = \Db::getConnection();
        $date = time();
        $sql = "INSERT INTO ".PREFICS."telegram_log (event_type, event_value, sm_user_id, user_id, chat_id, date)
                VALUES(:event_type, :event_value, :sm_user_id, :user_id, :chat_id, '$date')";

        $result = $db->prepare($sql);
        $result->bindParam(':event_type', $event_type, PDO::PARAM_INT);
        $result->bindParam(':event_value', $event_value, PDO::PARAM_INT);
        $result->bindParam(':sm_user_id', $sm_user_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);

        return $result->execute();
    }

}