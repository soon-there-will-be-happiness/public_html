<?php defined('BILLINGMASTER') or die;

/** ХЕДЕР **/

class NewSimpleHeader {

    /**
     * @param $logotype
     * @param $fix_head
     * @return bool
     */
    public static function updSystemSettings($logotype, $fix_head) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'settings SET logotype = :logotype, fix_head = :fix_head WHERE setting_id = 1';
        $result = $db->prepare($sql);

        $result->bindParam(':logotype', $logotype, PDO::PARAM_STR);
        $result->bindParam(':fix_head', $fix_head, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $slogan
     * @return bool
     */
    public static function updSystemMainSettings($slogan) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'settings_main_page SET slogan = :slogan WHERE settings_main_page_id = 1';
        $result = $db->prepare($sql);
        $result->bindParam(':slogan', $slogan, PDO::PARAM_STR);

        return $result->execute();
    }
}
