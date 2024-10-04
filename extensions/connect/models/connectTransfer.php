<?php 
namespace Connect;
defined('BILLINGMASTER') or die;

/**
 * Class Transfer
 * Отвечает за перенос данных из различных сервисов
 *
 * @package Connect
 */
class Transfer {

	public $result = [
		'Telegram' => '',
		'Vkontakte' => '',
        'AutoPilot' => ''
	];

	public function __construct(){
		foreach ($this->result as $key => $value) 
			$this->result[$key] = self::$key;
	}

	public static function Telegram(){
		$old_data = \Db::_select_one('extensions', ['name' => 'telegram']);

        $params = self::validateData($old_data);
        if (!$params) {
            return null;
        }

        $service = \Connect::getServiceByName('telegram');

        if (
            (@ $service['service_params']['token'] != @ $params['token']) ||
            (@ $service['service_params']['username'] !=  @ $params['bot_name']) ||
            (@ $service['params']['use_webhook'] != @ $params['is_set_webhook'])
        ) {
            if ($method = \Connect::getServiceMethod('telegram', 'updSetting')) {

                $service['service_params']['token'] = @ $params['token'];
                $service['service_params']['username'] = @ $params['bot_name'];
                $service['enable'] = (int) @ $params['is_set_webhook'];
                $service['params']['tg_user_groups'] = @ $params['tg_user_groups'];

                if ($method($service['service_id'], $service, false)) {
                    return \Db::_update('extensions', ['name' => 'telegram'], ['params' => 'transferred_to_connect']);
                }
            }
        }

        return null;
	}

	public static function Vkontakte(){
		$old_data = \Db::_select_one('extensions', ['name' => 'vkontakte']);

        $old_params = self::validateData($old_data);
		if (!$old_params) {
		    return null;
        }

        $service = \Connect::getServiceByName('vkontakte');

    	if (
            (@ $service['service_params']['v'] != @ $old_params['vk_app']['v']) ||

            (@ $service['service_params']['app_id'] != @ $old_params['vk_app']['id']) ||
            (@ $service['service_params']['secret'] != @ $old_params['vk_app']['secret']) ||
            (@ $service['service_params']['service_key'] != @ $old_params['vk_app']['service_key']) ||

            (@ $service['service_params']['scope'] != @ $old_params['vk_auth_params']['scope']) ||
            (@ $service['service_params']['redirect_uri'] != @ $old_params['vk_auth_params']['redirect_uri']) ||
            (@ $service['service_params']['response_type'] != @ $old_params['vk_auth_params']['response_type']) ||

            (@ $service['service_params']['group_id'] != @ $old_params['vk_club']['id']) ||
            (@ $service['service_params']['chat_token'] != @ $old_params['vk_club']['key']) ||

            (@ $service['params']['msg'] != @ $old_params['vk_club']['notify']) ||
            (@ $service['params']['auth'] != @ $old_params['modules']['login']) ||

            (@ $service['enable'] != @ $old_data['enable'])
        ) {
            if ($method = \Connect::getServiceMethod('vkontakte', 'updSetting')) {

                $service['service_params']['v'] = $old_params['vk_app']['v'];

                $service['service_params']['app_id'] = $old_params['vk_app']['id'];
                $service['service_params']['secret'] = $old_params['vk_app']['secret'];
                $service['service_params']['service_key'] = $old_params['vk_app']['service_key'];

                $service['service_params']['scope'] = $old_params['vk_auth_params']['scope'];
                $service['service_params']['redirect_uri'] = $old_params['vk_auth_params']['redirect_uri'];
                $service['service_params']['response_type'] = $old_params['vk_auth_params']['response_type'];

                $service['service_params']['group_id'] = $old_params['vk_club']['id'];
                $service['service_params']['chat_token'] = $old_params['vk_club']['key'];

                $service['params']['msg'] = $old_params['vk_club']['notify'];
                $service['params']['auth'] = $old_params['modules']['login'];

                $service['enable'] = $old_data['enable'];

                if ($method($service['service_id'], $service, false)) {
                    return \Db::_update('extensions', ['name' => 'vkontakte'], ['params' => 'transferred_to_connect']);
                }
            }
        }

		return null;
	}

	public static function AutoPilot(bool $to_text = false){
		$prfx = PREFICS;
        $db = \Db::getConnection();
        $sql = "
            SELECT u.user_id as user_id, u.vk_url as vk_url, c.user_id as conn
            FROM {$prfx}users as u

            LEFT JOIN {$prfx}connect_users as c
                ON u.user_id = c.user_id

            WHERE 
                c.vk_id IS NULL
                AND
                u.vk_url LIKE '%vk.com/id%'
        ";
        $result = $db->query($sql);
        $result->execute();

        $res = [
            'error_data' => [],
            'error' => [],
            'success' => []
        ];

        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $vk_id = substr($row['vk_url'], strpos($row['vk_url'], 'vk.com/id') + 9);

            if (empty($vk_id) || !is_numeric($vk_id)) {
                $res['error_data'][] = $row;
            }
            elseif (\Connect::addUser($row['user_id'], 'vkontakte', $vk_id, $row['vk_url'])) {
                $res['success'][] = $row;
            }
            else {
                $res['error'][] = $row;
            }
        }

        if(!$to_text) {
            return $res;
        }

        $text = '';
        $count_res = [];

        foreach ($res as $status => $data) {
            $count_res[$status] = count($data);
        }

        switch ($count_res['success']) {
            case 0:
                $text .= "Новые пользователи не подключены;";
                break;
            case 1:
                $text .= "Подключен 1 пользователь;";
                break;
            case 2:
            case 3:
            case 4:
                $text .= "Подключено {$count_res[$status]} пользователя;";
                break;

            default:
                $text .= "подключено {$count_res['success']} пользователей;";
                break;
        }

        if ($count_res['error'] > 0) {
            switch ($count_res['error']) {
                case 1:
                    $text .= "Ошибка подключения 1 пользователя;";
                    break;
                default:
                    $text .= "Ошибка подключения {$count_res['error']} пользователей;";
                    break;
            }
        }

        if ($count_res['error_data'] > 0) {
            switch ($count_res['error_data']) {
                case 1:
                    $text .= "Ошибка данных подключения 1 пользователя;";
                    break;
                default:
                    $text .= "Ошибка данных подключения {$count_res['error']} пользователей;";
                    break;
            }
        }

        return $text;
	}

	private static function validateData($old_data) {
        if (
            !$old_data
            || empty($old_data)
            || !isset($old_data['params'], $old_data['enable'])
            || $old_data['params'] == 'transferred_to_connect'

            || empty($old_data['params'])
            || !($old_params = @ json_decode($old_data['params'], true))
            || !$old_params || !is_array($old_params)
            || !isset($old_params['modules'], $old_params['vk_auth_params'], $old_params['vk_app'], $old_params['vk_club'])
        ) {
            return false;
        }

        return $old_params;
    }

}