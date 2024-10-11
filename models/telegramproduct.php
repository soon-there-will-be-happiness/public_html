<?php defined('BILLINGMASTER') or die;

class TelegramProduct{

    public static function addOrUpdate($id_proguct, $telegram)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'telegram_proguct (id_proguct, telegram) 
            VALUES ( '.$id_proguct.', "'.$telegram.'" ) ON DUPLICATE KEY UPDATE id_proguct= '.$id_proguct.',telegram="'.$telegram.'"';

        $result = $db->prepare( $sql);
        return $result->execute();
    }

    public static function searchByProguctId($id_proguct) {

        $db = Db::getConnection();
        $query = "SELECT *
                    FROM ".PREFICS."telegram_proguct WHERE id_proguct = '$id_proguct'  ORDER BY id DESC";
        $result = $db->query($query);

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
                }
        return !empty($data) ? $data[0] : false;
        }
}
?>