<?php defined('BILLINGMASTER') or die;

class Currency {
    
    
    // Список валют
    public static function getCurrencyList($status = false)
    {
        $db = Db::getConnection();
		if($status) {
            $sql = "WHERE status = 1";
        } else {
            $sql = null;
        }
        $result = $db->query("SELECT * FROM ".PREFICS."currency $sql");
        $data = $result->fetchAll(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }
    
    
    
    // Добавить валюту
    public static function addCurrency($name, $simbol, $code, $tax, $status)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'currency (name, simbol, code, tax, status ) 
                VALUES (:name, :simbol, :code, :tax, :status)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':simbol', $simbol, PDO::PARAM_STR);
        $result->bindParam(':tax', $tax, PDO::PARAM_STR);
        $result->bindParam(':code', $code, PDO::PARAM_INT);
  		$result->bindParam(':status', $status, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // Изменить валюту
    public static function editCurrency($id, $name, $simbol, $code, $tax, $status)
    {
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'currency SET name = :name, simbol = :simbol, code = :code, tax = :tax, status = :status WHERE id = '.$id;
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':simbol', $simbol, PDO::PARAM_STR);
        $result->bindParam(':tax', $tax, PDO::PARAM_STR);
        $result->bindParam(':code', $code, PDO::PARAM_INT);
  		$result->bindParam(':status', $status, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // ПОЛУЧИТЬ данные валюты
    public static function getCurrencyData($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."currency WHERE id = $id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if(!empty($data)) return $data;
        else return false;
    }
    
    
    // УДАЛИТЬ ВАЛЮТУ
    public static function delCurrency($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'currency WHERE id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $result->execute();
    }
}