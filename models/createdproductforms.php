<?php defined('BILLINGMASTER') or die;

class createdproductforms {

    /**
     * Получить все созданые формы для списка
     *
     * @return array
     */
    public static function getFormsForList() {
        $db = Db::getConnection();
        $sql = "SELECT `id`, `name` FROM `".PREFICS."created_product_forms`";
        $result = $db->query($sql);
        return $result->fetchAll();
    }

    public static function getFormById($id) {
        $db = Db::getConnection();
        $id = intval($id);
        $sql = "SELECT * FROM `".PREFICS."created_product_forms` WHERE `id`=$id";

        $result = $db->query($sql);
        return $result->fetch();
    }

    /**
     * Создать форму и получить ее айди
     *
     * @param $formname
     * @param $json_products
     * @param $json_data
     * @param $form
     * @return bool
     */
    public static function saveForm($formname, $json_products, $json_data, $form) {
        $db = Db::getConnection();
        $sql = "INSERT INTO `".PREFICS."created_product_forms` (`name`, `products`, `data`, `form`) VALUES (:formname, :json_products, :json_data, :form);";

        $result = $db->prepare($sql);

        $result->bindParam(':formname', $formname, PDO::PARAM_STR);
        $result->bindParam(':json_products', $json_products, PDO::PARAM_STR);
        $result->bindParam(':json_data', $json_data, PDO::PARAM_STR);
        $result->bindParam(':form', $form, PDO::PARAM_STR);
        $result->execute();
        return $result ? $db->lastInsertId('') : $result;
    }

    public static function updateForm($id, $formname, $json_products, $json_data, $form) {
        $db = Db::getConnection();

        $sql = "UPDATE `".PREFICS."created_product_forms` SET `name`=:name, `products`=:products, `data`=:data, `form`=:form WHERE `id`=:id";
        $result = $db->prepare($sql);

        $result->bindParam(':name', $formname, PDO::PARAM_STR);
        $result->bindParam(':products', $json_products, PDO::PARAM_STR);
        $result->bindParam(':data', $json_data, PDO::PARAM_STR);
        $result->bindParam(':form', $form, PDO::PARAM_STR);
        $result->bindParam(':id', $id, PDO::PARAM_STR);

        return $result->execute();
    }

    public static function deleteForm($id) {
        $db = Db::getConnection();

        $sql = "DELETE FROM `".PREFICS."created_product_forms` WHERE `id`=:id";

        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }
}
