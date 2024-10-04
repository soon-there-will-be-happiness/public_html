<?php defined('BILLINGMASTER') or die;

class Helpers
{

    use ResultMessage;


    /**
     * @param $array
     * @return mixed
     */
    public static function arraySort($array) {
        usort($array, function($a, $b) {
            global $field;
            if ($a['sort'] == $b['sort']) {
                return 0;
            }

            return $a['sort'] < $b['sort'] ? -1 : 1;
        });

        return $array;
    }


    /**
     * @param $use_sorts
     * @return mixed
     */
    public static function getNextSortValue(&$use_sorts) {
        $sort = 1;

        if ($use_sorts) {
            while (in_array($sort, $use_sorts)) {
                $sort++;
            }
        }
        $use_sorts[] = $sort;

        return $sort;
    }
}