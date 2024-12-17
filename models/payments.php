<?php

class Payments {

    use ResultMessage;

    // Метод для добавления записи в таблицу payments_tochka
    public static function addPaymentTochka($payment_id, $amount, $payment_date, $description, $status = 'unmatched') {
        $sql = "INSERT INTO " . PREFICS . "payments_tochka (payment_id, amount, payment_date, description, status)
                VALUES (:payment_id, :amount, :payment_date, :description, :status)";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        return $stmt->execute();
    }

    // Метод для получения платежа из payments_tochka по ID
    public static function getPaymentTochkaByPaymentId($payment_id) {
        $sql = "SELECT * FROM " . PREFICS . "payments_tochka WHERE payment_id = :payment_id";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Метод для получения всех платежей из таблицы payments_tochka
    public static function getAllPaymentsTochka($status = null) {
        $sql = "SELECT * FROM " . PREFICS . "payments_tochka";
        if ($status) {
            $sql .= " WHERE status = :status";
        }
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        if ($status) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getJoinedOrdersAndPayments()
    {
        
        $sql = "SELECT 
                    orders.order_id,
                    orders.product_id,
                    orders.order_date,
                    orders.payment_date,
                    orders.summ,
                    matched_payments.id AS matched_payment_id,
                    matched_payments.payment_id,
                    matched_payments.system_record_id,
                    matched_payments.amount,
                    matched_payments.match_date
                FROM 
                   " . PREFICS . "orders AS orders
                JOIN 
                   " . PREFICS . "matched_payments AS matched_payments
                ON 
                    matched_payments.system_record_id = orders.order_id
                WHERE 
                    matched_payments.payment_id = 0";

        // Подключение к базе данных
        $db = Db::getConnection(); // Метод для получения соединения с БД
        $stmt = $db->prepare($sql);

        // Выполнение запроса
        $stmt->execute();

        // Возвращаем результат в виде ассоциативного массива
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Метод для добавления записи в таблицу matched_payments
    public static function addMatchedPayment($payment_id, $system_record_id, $amount) {
        $sql = "INSERT INTO " . PREFICS . "matched_payments (payment_id, system_record_id, amount)
                VALUES (:payment_id, :system_record_id, :amount)";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_STR);
        $stmt->bindParam(':system_record_id', $system_record_id, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount);

        return $stmt->execute();
    }
    public static function updatePaymentId($id, $payment_id) {
        $sql = "UPDATE " . PREFICS . "matched_payments SET payment_id = :payment_id WHERE id = :id";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_STR);

        return $stmt->execute();
    }
    // Метод для получения всех сопоставленных платежей
    public static function getAllMatchedPayments() {
        $sql = "SELECT * FROM " . PREFICS . "matched_payments";
        $db = Db::getConnection();
        $result = $db->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Метод для получения платежа из payments_tochka по ID
    public static function getPaymentTochkaById($id) {
        $sql = "SELECT * FROM " . PREFICS . "payments_tochka WHERE id = :id";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Метод для обновления статуса платежа в таблице payments_tochka
    public static function updatePaymentStatus($id, $status) {
        $sql = "UPDATE " . PREFICS . "payments_tochka SET status = :status WHERE id = :id";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        return $stmt->execute();
    }

    // Метод для удаления записи из таблицы payments_tochka
    public static function deletePaymentTochka($id) {
        $sql = "DELETE FROM " . PREFICS . "payments_tochka WHERE id = :id";
        $db = Db::getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
// Удалить запись из matched_payments
public static function deleteMatchedPayment($id) {
    $db = Db::getConnection();
    $sql = "DELETE FROM " . PREFICS . "matched_payments WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

}
