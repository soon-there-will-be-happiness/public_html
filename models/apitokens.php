<?php


class ApiTokens
{
    const salt = 'jgCbTz18K8CozNHdqMe';

    /**
     * Сгенерировать токен определенной длины
     *
     * @param $length
     * @return string
     * @throws Exception
     */
    public static function generateToken($length) {
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        $notEncryptedToken = '';
        $strlen = strlen($base);
        for ($i = 0; $i < $length; ++$i) {
            $notEncryptedToken .= $base[random_int(0, $strlen - 1)];
        }
        return $notEncryptedToken;
    }

    /**
     * Хеширование токена
     *
     * @param $token
     * @return false|string
     */
    public static function encryptToken($token) {
        return hash('sha256', self::salt.$token);
    }

    /**
     * Проверить существует ли токен и получить его данные
     *
     * @param $token
     * @return mixed
     */
    public static function checkAndGetToken($token) {
        $token = self::encryptToken($token);
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."api_tokens WHERE `token` = '$token'");
        return $result->fetch();
    }

    /**
     * Проверить существует ли refresh token и получить его данные
     * @param $token
     * @return mixed
     */
    public static function checkAndGetRefreshToken($token) {
        $token = self::encryptToken($token);
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."api_tokens WHERE `refresh_token` = '$token' LIMIT 1");
        return $result->fetch();
    }

    /**
     * Обновить токены
     *
     * @param $tokenId
     * @param $access_token
     * @param $refresh_token
     * @param $expire
     * @return bool
     */
    public static function refreshToken($tokenId, $access_token, $refresh_token, $expire) {

        $access_token = self::encryptToken($access_token);
        $refresh_token = self::encryptToken($refresh_token);

        $db = Db::getConnection();

        $sql = 'UPDATE '.PREFICS.'api_tokens SET token = :access_token, refresh_token = :refresh_token, expire = :expire WHERE id = :tokenId';
        $result = $db->prepare($sql);

        $result->bindParam(':access_token', $access_token, PDO::PARAM_STR);
        $result->bindParam(':refresh_token', $refresh_token, PDO::PARAM_STR);
        $result->bindParam(':expire', $expire, PDO::PARAM_STR);
        $result->bindParam(':tokenId', $tokenId, PDO::PARAM_STR);

        return $result->execute();
    }

    /**
     * Создать токен
     *
     * @param $user_id
     * @param $access_token
     * @param $refresh_token
     * @param $expire
     * @return bool
     */
    public static function createToken($user_id, $access_token, $refresh_token, $expire) {
        $access_token = self::encryptToken($access_token);
        $refresh_token = self::encryptToken($refresh_token);

        $db = Db::getConnection();
        $time = time();
        $sql = 'INSERT INTO '.PREFICS."api_tokens  (`user_id`, `token`, `refresh_token`, `created_at`, `expire`, `last_used_at`) 
                VALUES ('$user_id', '$access_token', '$refresh_token', '$time', '$expire', '$time');";
        $result = $db->prepare($sql);

        return $result->execute();
    }

    public static function updateLastUsedAt($tokenId) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'api_tokens SET last_used_at = :last_used_at WHERE id = :tokenId';
        $result = $db->prepare($sql);
        $time = time();
        $result->bindParam(':last_used_at', $time, PDO::PARAM_STR);
        $result->bindParam(':tokenId', $tokenId, PDO::PARAM_STR);

        return $result->execute();
    }

    public static function getTokenByUserId($user_id) {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM `'.PREFICS.'api_tokens` WHERE user_id = '.$user_id.' ORDER BY `expire` DESC LIMIT 1';
        $result = $db->query($sql);

        return $result->fetch();
    }

}