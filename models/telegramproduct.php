<?php defined('BILLINGMASTER') or die;

class TelegramProduct{

    public static function addOrUpdate($id_proguct, $telegram)
    {
        $db = Db::getConnection();
    
        // Шаг 1: Проверка существования записи с таким id_proguct
        $checkSql = 'SELECT COUNT(*) FROM '.PREFICS.'telegram_proguct WHERE id_proguct = :id_proguct';
        $checkResult = $db->prepare($checkSql);
        $checkResult->bindParam(':id_proguct', $id_proguct, PDO::PARAM_INT);
        $checkResult->execute();
        $exists = $checkResult->fetchColumn();
    
        // Шаг 2: В зависимости от результата выполняем либо обновление, либо вставку
        if ($exists) {
            // Обновление записи
            $updateSql = 'UPDATE '.PREFICS.'telegram_proguct SET telegram = :telegram WHERE id_proguct = :id_proguct';
            $updateResult = $db->prepare($updateSql);
            $updateResult->bindParam(':telegram', $telegram, PDO::PARAM_STR);
            $updateResult->bindParam(':id_proguct', $id_proguct, PDO::PARAM_INT);
            return $updateResult->execute();
        } else {
            // Вставка новой записи
            $insertSql = 'INSERT INTO '.PREFICS.'telegram_proguct (id_proguct, telegram) VALUES (:id_proguct, :telegram)';
            $insertResult = $db->prepare($insertSql);
            $insertResult->bindParam(':id_proguct', $id_proguct, PDO::PARAM_INT);
            $insertResult->bindParam(':telegram', $telegram, PDO::PARAM_STR);
            return $insertResult->execute();
        }
    }
    
    

    public static function searchByProductId($id_product) {

        $db = Db::getConnection();
        $query = "SELECT * FROM ".PREFICS."telegram_product
                WHERE id_product = '$id_proguct' AND user_id = '$user_id'
                ORDER BY id DESC";
        $result = $db->query($query);

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
                }
        return !empty($data) ? $data[0] : false;
        }
}
?>