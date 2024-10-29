<?php defined('BILLINGMASTER') or die;

class AtolDB {

    // Поиск записи по id
    public static function findRecordById($id) {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'atol WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function findRecordByOrderId($id) {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'atol WHERE order_id = :id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Вставка новой записи
    public static function insertRecord($url, $order_id, $status) {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'atol (url, order_id, status) VALUES ("'.$url.'", '.$order_id.',false)';
        $stmt = $db->prepare($sql);

        return $stmt->execute();
    }

    // Изменение статуса с TRUE на FALSE
    public static function updateStatusToFalse($id) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'atol SET status = 0 WHERE order_id = :id AND status = 1';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
