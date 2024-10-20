<?php defined('BILLINGMASTER') or die;

class TelegramProduct{

    public static function addOrUpdate($user_id, $product_id, $telegram)
    {
        $db = Db::getConnection();
    
        // Шаг 1: Проверка существования записи с таким id_proguct
        $checkSql = 'SELECT COUNT(*) FROM '.PREFICS.'telegram_product WHERE product_id = :product_id AND user_id = :user_id';
        $checkResult = $db->prepare($checkSql);
        $checkResult->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $checkResult->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $checkResult->execute();
        $exists = $checkResult->fetchColumn();
    
        // Шаг 2: В зависимости от результата выполняем либо обновление, либо вставку
        if ($exists) {
            // Обновление записи
            $updateSql = 'UPDATE '.PREFICS.'telegram_product SET telegram = :telegram WHERE product_id = :product_id AND user_id = :user_id';
            $updateResult = $db->prepare($updateSql);
            $updateResult->bindParam(':telegram', $telegram, PDO::PARAM_STR);
            $updateResult->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $updateResult->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            return $updateResult->execute();
        } else {
            // Вставка новой записи
            $insertSql = 'INSERT INTO '.PREFICS.'telegram_product (user_id, product_id, telegram) VALUES (:user_id, :product_id, :telegram)';
            $insertResult = $db->prepare($insertSql);
            $insertResult->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $insertResult->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $insertResult->bindParam(':telegram', $telegram, PDO::PARAM_STR);
            return $insertResult->execute();
        }
    }
    
    

    public static function searchByProductId($user_id, $product_id) {

        $db = Db::getConnection();
        $query = "SELECT *
                    FROM ".PREFICS."telegram_product WHERE product_id = '$product_id' AND user_id = '$user_id' ORDER BY id DESC";
        $result = $db->query($query);

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
                }
        return !empty($data) ? $data[0] : false;
        }
}
?>