<?php defined('BILLINGMASTER') or die;

/**ФУТЕР**/

class NewSimpleFooter {


    /**
     * @param $soc_but
     * @return bool
     */
    public static function updSystemSettings($soc_but) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'settings SET socbut = :soc_but WHERE setting_id = 1';
        $result = $db->prepare($sql);
        $result->bindParam(':soc_but', $soc_but, PDO::PARAM_STR);

        return $result->execute();
    }

    /**
     * @param $copyright
     * @param $politics_link
     * @param $politics_text
     * @param $offer_link
     * @param $offer_text
     * @return bool
     */
    public static function updSystemMainSettings($copyright, $politics_link, $politics_text, $offer_link, $offer_text) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'settings_main_page SET copyright = :copyright, politika_link = :politics_link,
                politika_text = :politics_text, oferta_link = :offer_link, oferta_text = :offer_text
                WHERE settings_main_page_id = 1';
        $result = $db->prepare($sql);
        $result->bindParam(':copyright', $copyright, PDO::PARAM_STR);
        $result->bindParam(':politics_link', $politics_link, PDO::PARAM_STR);
        $result->bindParam(':politics_text', $politics_text, PDO::PARAM_STR);
        $result->bindParam(':offer_link', $offer_link, PDO::PARAM_STR);
        $result->bindParam(':offer_text', $offer_text, PDO::PARAM_STR);

        return $result->execute();
    }
}
