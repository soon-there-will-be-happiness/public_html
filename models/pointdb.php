<?php defined('BILLINGMASTER') or die;

class PointDB {

    // Поиск записи по id
    public static function findRecordById($id) {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'point WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function findRecordByOrderId($id) {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'point WHERE order_id = :id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Вставка новой записи
    public static function insertRecord($url, $order_id, $status,$operationId) {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'point (url, order_id, status, operationId) VALUES ("'.$url.'", '.$order_id.',false,"'.$operationId.'")';
        $stmt = $db->prepare($sql);
        return $stmt->execute();
    }

    // Изменение статуса с TRUE на FALSE
    public static function updateStatusToFalse($id) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'point SET status = 0 WHERE order_id = :id AND status = 1';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
     public static function updateStatusToTrue($id) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'point SET status = 1, Payment_date = NOW()   WHERE order_id = :id AND status = 0';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
     public static function updateUUID($id,$uuid) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'point SET uuid = "'.$uuid.'"  WHERE order_id = :id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public static function findRecordByOperationId($id) {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'point WHERE operationId = :id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function getRecordsWithStatus() {
        $db = Db::getConnection();
        $sql = '
            SELECT 
                dp.id AS point_id,
                dp.order_id,
                dp.status,
                dp.operationId,
                dp.url,
                do.product_id,
                do.order_date,
                do.payment_date,
                do.summ
            FROM 
                '.PREFICS.'point dp
            JOIN 
                '.PREFICS.'orders do
            ON 
                dp.order_id = do.order_id
            WHERE 
                dp.status = 1';
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
