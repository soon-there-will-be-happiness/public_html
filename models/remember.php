<?php defined('BILLINGMASTER') or die;

class Remember {


    /**
     * @param $user
     * @return string
     */
    public static function getToken($user) {
        $token = hash('sha256', "{$user['email']}{$user['pass']}");

        return $token;
    }


    /**
     * @param $user_id
     * @param $token
     * @return bool
     */
    public static function saveToken($user_id, $token) {
        $sql = 'UPDATE '.PREFICS.'users SET token = :token WHERE user_id = :user_id';
        $db = Db::getConnection();
        $result = $db->prepare($sql);
        $result->bindParam(':token', $token, PDO::PARAM_STR);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $user
     * @param $is_remember
     * @return bool
     */
    public static function saveData($user, $is_remember) {
        $token = self::getToken($user);
        if ($is_remember) {
            if (!$user['token'] || $user['token'] != $token) {
                $res = self::saveToken($user['user_id'], $token);
                if (!$res) {
                    return false;
                }
            }
            setcookie("sm_remember_me", $token, time() + 86400 * 30, '/', Helper::getDomain()); // 30 дней
        } elseif(!$is_remember && isset($_COOKIE['sm_remember_me'])) {
            setcookie("sm_remember_me", '', time() - 86400 * 30, '/', Helper::getDomain()); // -30 дней
        }
    }


    /**
     * @param $token
     * @return bool|mixed
     */
    public static function getUserByToken($token) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS.'users WHERE token = :token');
        $result->bindParam(':token', $token, PDO::PARAM_STR);

        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * @param $setting
     */
    public static function userAuth($setting) {
        $token = htmlentities($_COOKIE['sm_remember_me']);
        $user = self::getUserByToken($token);
        $ip = System::getUserIp();
        $user_agent = htmlentities($_SERVER['HTTP_USER_AGENT']);

        if ($user && $user['token'] == self::getToken($user)
        && ($setting['multiple_authorizations'] || UserSession::isAllowAuth($user['user_id'], $ip, $user_agent))) {
            User::Auth($user['user_id'], $user['user_name']);
        }
    }
}
