<?php

class LogRemover {

    public static function removeOutdatedLogs($nowTime, $logs_life_time) {
        $dateToDelete = strtotime("-$logs_life_time days", $nowTime);
        $result = Db::getConnection()->prepare("DELETE FROM `".PREFICS."log` WHERE `date` < '$dateToDelete'");
        return $result->execute();
    }


}