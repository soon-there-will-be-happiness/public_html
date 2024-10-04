<?php


class ApiBaseController
{
    private $settings;
    /** @var float|int Время "протухания" токена */
    static $timeToExpire = 60*60*24*180; //180 дней


    /**
     * apiBaseController constructor.
     */
    public function __construct() {
        /*$this->settings = System::getSetting(true);*/
        //Задаем хедеры
        header('Content-Type: application/json; charset=utf-8;');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: token, Content-Type, authorization');
        header('Access-Control-Max-Age: 1728000');
        //Проверяем авторизацию
        if ($_SERVER['REQUEST_URI'] != '/api2/refreshtoken') {// Если это не запрос о перевыпуске токена
            /*$this->authorizationCheck();*/
        }
        switch ($_SERVER['REQUEST_URI']) {
            case '/api2/refreshtoken':
                break;
            case '/api2/gettoken'://временно
                break;
            default:
                $this->authorizationCheck();
                break;
        }
    }



/**
 * Авторизация запроса
 */

    /**
     * Получить токен из запроса
     * @return mixed|void
     */
    public function getRequestToken() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        } else {
            return $this->response(['status'=> false, 'message' => 'Отсутствует токен.'], 403);
        }
    }

    /**
     * Проверить токен
     */
    public function authorizationCheck() {

        $token = $this->getRequestToken();

        $result = apiTokens::checkAndGetToken($token);

        if (!is_array($result)) {//токен не найден
            return $this->response(['status'=> false, 'message' => 'Не авторизован.'], 403);
        }
        if ($result['expire'] < time()) {//Если токен устарел
            return $this->response(['status'=> false, 'message' => 'Токен устарел.'], 403);
        }
        //Успех, обновить запись об использовании токена
        return apiTokens::updateLastUsedAt($result['id']);
    }


    /**
     * Роут api2/refreshtoken
     * Обновить пару токенов
     *
     * Возвращает клиенту новую пару токенов, а также дату их протухания
     * @throws Exception
     */
    public function actionRefreshToken() {

        $refreshToken = $this->getRequestToken();
        $result = apiTokens::checkAndGetRefreshToken($refreshToken);

        if (!is_array($result)) {//токен не найден
            return $this->response(['status'=> false, 'message' => 'Refresh token не найден.'], 403);
        }
        $token_id = $result['id'];

        $tokens = [
            'access_token' => apiTokens::generateToken(64),
            'refresh_token' => apiTokens::generateToken(64),
            'expire' => time() + self::$timeToExpire,
        ];
        $result = apiTokens::refreshToken($token_id, $tokens['access_token'], $tokens['refresh_token'], $tokens['expire']);

        return $this->response(['status'=>true, 'tokens'=> $tokens], 201);
    }


    public static function paginate() {
        $limit = 10;
        $page = $_GET['page'] * $limit ?? 0;
        return "LIMIT $limit OFFSET $page";
    }

    /**
     * Отдать ответ на запрос
     *
     *
     * @param string | mixed | array $content если массив - ответ в виде json
     * @param int $code - код http ответа
     *
     */
    public function response($content = "", $code = 200) {

        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        http_response_code($code);

        exit($content);
    }

    public static function responsestatic($content = "", $code = 200){
        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }
        http_response_code($code);
        exit($content);
    }
}
