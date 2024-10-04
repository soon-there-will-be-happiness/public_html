<?php defined('BILLINGMASTER') or die;

class AdminNotice {


    /**
     * @param $text
     * @param string $url
     * @return bool
     */
    public static function addNotice($text, $url = '') {
        $date = time();
        $db = Db::getConnection();
        $result = $db->prepare('INSERT INTO '.PREFICS."admin_notices (`text`, `date`, `url`) VALUES (:text, :date, :url)");
        $result->bindParam(':text', $text, PDO::PARAM_STR);
        $result->bindParam(':date', $date, PDO::PARAM_INT);
        $result->bindParam(':url', $url, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @return bool|mixed
     */
    public static function getNotices() {
        $db = Db::getConnection();
        $result = $db->query('SELECT * FROM '.PREFICS."admin_notices ORDER BY id DESC");
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $status
     * @return mixed
     */
    public static function getCountNotices($status) {
        $db = Db::getConnection();
        $result = $db->query('SELECT COUNT(id) FROM '.PREFICS."admin_notices WHERE status = $status");
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @return false|string
     */
    public static function getNoticesHtml() {
        ob_start();
        require_once (ROOT . "/template/admin/layouts/admin-notices.php");
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }


    /**
     * @param null $id
     * @return false|PDOStatement
     */
    public static function delNotices($id = null) {
        $db = Db::getConnection();
        $where = $id ? "WHERE id = $id" : '';
        $result = $db->query('DELETE FROM '.PREFICS."admin_notices $where");

        return $result ? true : false;
    }


    /**
     * @param $status
     * @return false|PDOStatement
     */
    public static function updStatusNotices($status) {
        $db = Db::getConnection();
        $result = $db->query('UPDATE '.PREFICS."admin_notices SET status = $status WHERE status <> $status");

        return $result ? true : false;
    }


    /**
     * @return bool
     */
    public static function delOldNotices() {
        $count_notices = self::getCountNotices(0);
        if ($count_notices < 31) {
            return false;
        }

        $limit = $count_notices - 30;
        $db = Db::getConnection();
        $result = $db->query('DELETE FROM '.PREFICS."admin_notices WHERE status = 0 ORDER BY id ASC LIMIT $limit");

        return $result ? true : false;
    }
}
