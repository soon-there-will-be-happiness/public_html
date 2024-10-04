<?php defined('BILLINGMASTER') or die;


class UserSession {


    /**
     * @param $id
     * @return bool|mixed
     */
    public static function getSession($id) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT * FROM '.PREFICS."user_sessions WHERE id = :id");
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * @param $user_id
     * @param $session_id
     * @return bool|mixed
     */
    public static function getSession2User($user_id, $session_id) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT * FROM '.PREFICS."user_sessions WHERE session_id = :session_id AND user_id = :user_id");
        $result->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * @param $user_id
     * @return array|bool
     */
    public static function getSessions($user_id) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT * FROM '.PREFICS."user_sessions WHERE user_id = :user_id ORDER BY id DESC");
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $user_id
     * @param $session_id
     * @param $settings
     */
    public static function processes2UserAuth($user_id, $session_id, $settings) {
        $save_user_info = self::saveUserData($user_id, $session_id);

        if ($save_user_info && !$settings['multiple_authorizations']) {
            $db = Db::getConnection();
            $query = 'SELECT id FROM '.PREFICS.'user_sessions
                      WHERE user_id = :user_id AND session_id <> :session_id AND status = 1';
            $result = $db->prepare($query);
            $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $result->bindParam(':session_id', $session_id, PDO::PARAM_STR);

            if ($_result = $result->execute()) {
                $data = $result->fetchAll(PDO::FETCH_ASSOC);

                $count = count($data);
                $array_values = [];
                foreach ($data as $key => $line) {
                    $array_values[] = "'".$line['id']."'";
                    unset($data[$key]);
                }

                $data['ids'] = implode(",", $array_values);
                $data['count'] = $count;

                $limit = $data['count'] ? $data['count'] - $settings['user_sessions']['count'] + 1 : 0;
                if ($limit > 0) {
                    $db->query('UPDATE '.PREFICS."user_sessions SET status = 0 WHERE id IN({$data['ids']})
                                         ORDER BY auth_date ASC LIMIT $limit"
                    )->execute();
                }
            }
        }
    }


    /**
     * @param $user_id
     * @param $session_id
     * @return bool
     */
    public static function saveUserData($user_id, $session_id) {
        $ip = System::getUserIp();
        $user_agent = htmlentities($_SERVER['HTTP_USER_AGENT']);
        $time = time();

        $db = Db::getConnection();
        $query = 'INSERT INTO '.PREFICS."user_sessions (`session_id`, `user_id`, `ip`, `user_agent`, `auth_date`)
                  VALUES (:session_id, :user_id, :ip, :user_agent, :auth_date)
                  ON DUPLICATE KEY UPDATE status = 1, ip = :ip, user_agent = :user_agent, auth_date = :auth_date";
        $result = $db->prepare($query);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':session_id', $session_id, PDO::PARAM_STR);
        $result->bindParam(':ip', $ip, PDO::PARAM_STR);
        $result->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
        $result->bindParam(':auth_date', $time, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $id
     * @return bool
     */
    public static function deleteUserData($id) {
        $db = Db::getConnection();
        $result = $db->prepare('DELETE FROM '.PREFICS.'user_sessions WHERE id = :id');
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * ВЫКИНУТЬ ПОЛЬЗОВАЕТЛЯ ИЗ СИСТЕМЫ, ЕСЛИ ПОЛЬЗОВАТЕЛЮ ОТКЛЮЧИЛИ СЕССИЮ
     * (ЧЕРЕЗ АДМИНКУ ИЛИ ЗАШЕЛ ДРУГОЙ ПОЛЬЗОВАТЕЛЬ ПОД ТЕКУЩИМ ЛОГИНОМ)
     * @return bool
     */
    public static function userLogOut() {
        if (isset($_SESSION['user'])) {
            $user_id = (int)$_SESSION['user'];
            $user_session_data = UserSession::getSession2User($user_id, session_id());
            if ($user_session_data && in_array($user_session_data['status'], [0, 2])) {
                return User::userLogOut();
            }
        }

        return false;
    }


    /**
     * @param $id
     * @param $status
     * @return false|PDOStatement
     */
    public static function updStatus($id, $status) {
        $db = Db::getConnection();
        $result = $db->query('UPDATE '.PREFICS."user_sessions SET status = $status WHERE id = $id");

        return $result;
    }


    /**
     * @param $time_delete
     * @return bool
     */
    public static function deleteOldSessions($time_delete) {
        $date = time() - $time_delete * 86400 * 30;
        $db = Db::getConnection();
        $result = $db->prepare('DELETE FROM '.PREFICS.'user_sessions WHERE auth_date < :date AND status <> 2');
        $result->bindParam(':date', $date, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $user_id
     * @param $ip
     * @return mixed
     */
    public static function isUserBlocked($user_id, $ip) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT COUNT(id) FROM '.PREFICS.'user_sessions 
                                         WHERE user_id = :user_id AND ip = :ip AND status = 2'
        );
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':ip', $ip, PDO::PARAM_STR);
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @param $user_id
     * @param $ip
     * @param $user_agent
     * @return bool
     */
    public static function isAllowAuth($user_id, $ip, $user_agent) {
        $time = time() - 180; // 3 минуты
        $db = Db::getConnection();
        $result = $db->prepare('SELECT COUNT(id) FROM '.PREFICS.'user_sessions 
                                         WHERE user_id = :user_id AND status = 1 AND auth_date > :time
                                         AND (ip <> :ip OR user_agent <> :user_agent)'
        );
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':ip', $ip, PDO::PARAM_STR);
        $result->bindParam(':user_agent', $user_agent, PDO::PARAM_STR);
        $result->bindParam(':time', $time, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0] > 0 ? false : true;
    }


    /**
     * @param $settings
     */
    public static function writeUsersWithSuspiciousActivity($settings) {
        $db = Db::getConnection();
        $db->query('DELETE FROM '.PREFICS.'user_sessions_suspicious_activity');

        if (isset($settings['user_sessions']['count_notice']) && $settings['user_sessions']['count_notice']) {
            $count_sessions = (int)$settings['user_sessions']['count_notice'];
            $end_time = time();
            $start_time = $end_time - 86400;

            $query = "SELECT MAX(user_id) FROM ".PREFICS."user_sessions
                      WHERE auth_date > $start_time GROUP BY user_id
                      HAVING COUNT(id) > $count_sessions";
            $result = $db->query($query);

            while ($row = $result->fetch()) {
                $user_id = $row[0];
                $db->query('INSERT INTO '.PREFICS."user_sessions_suspicious_activity (`user_id`) VALUES ($user_id)");
                AdminNotice::addNotice("Подозрительная активность (id:$user_id)", "/admin/users/edit/$user_id");
            }
        }
    }


    /**
     * @param $user_id
     * @return bool
     */
    public static function hasSuspiciousActivity($user_id) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT COUNT(id) FROM '.PREFICS.'user_sessions_suspicious_activity  WHERE user_id = :user_id');
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0] > 0 ? true : false;
    }
}

