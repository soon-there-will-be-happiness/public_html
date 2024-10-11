<?php defined('BILLINGMASTER') or die;
class ToChild{

    public static function addToChild($id_parther, $id_order,$client_email)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'child (id_parther, id_order, status, client_email) 
            VALUES ( '.$id_parther.', '.$id_order.', false, "'.$client_email.'" )';

        $result = $db->prepare( $sql);
        return $result->execute();
    }
    public static function searchByPartherAndOrderId($client_email, $id_order) {

        $db = Db::getConnection();
        $query = "SELECT *
                    FROM ".PREFICS."child WHERE client_email = '$client_email' AND  id_order = '$id_order' ORDER BY id DESC";
        $result = $db->query($query);

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
                }
        return !empty($data) ? $data : false;
        }
    public static function close( $id_order,$email){
    $db = Db::getConnection();
    $sql = 'UPDATE '.PREFICS.'child SET child_email = :email, status=true WHERE id_order = '.$id_order;

    $result = $db->prepare($sql);
    $result->bindParam(':email',  $email, PDO::PARAM_STR);

    return $result->execute();
}
    public static function searchByOrderId( $id_order) {

        $db = Db::getConnection();
        $query = "SELECT *
                    FROM ".PREFICS."child WHERE id_order = '$id_order' ";
        $result = $db->query($query);

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;

    }


}
class TelegramProduct{

    public static function addOrUpdate($id_proguct, $telegram)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'telegram_proguct (id_proguct, telegram) 
            VALUES ( '.$id_proguct.', '.$telegram.'" ) ON DUPLICATE KEY UPDATE id_proguct= '.$id_proguct.',telegram='.$telegram;

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
        return !empty($data) ? $data : false;
        }
}
?>