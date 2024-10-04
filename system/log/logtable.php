<?php


class LogTable {

    const Table = PREFICS."logs";

    /**
     * Получить логи в админке
     *
     * @param $filters
     * @param int $page
     * @param int $limit
     * @param string $select
     *
     * @return array
     */
    public static function getLastLogs($filters, $page = 1, $limit = 10, $select = "*") {
        $offset = ($page - 1) * $limit;
        $where = "WHERE `in_arhive` = ".$filters['in_arhive'];
        if ($filters['type']) {
            $where .= " AND `type` = '{$filters['type']}'";
        }

        if ($filters['level'] !== false) {
            $where .= " AND `level` = '{$filters['level']}'";
        }

        $result = [];
        $result['logs'] = Db::getConnection()->query("SELECT `id`, `message`, `date`, `level`, `type` FROM `".PREFICS."log` $where ORDER BY `id` desc LIMIT $limit OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
        $result["pages"] = Db::getConnection()->query("SELECT COUNT(*) as total FROM `".PREFICS."log` $where")->fetch();

        return $result;
    }


    /**
     * Получить лог
     *
     * @param $id
     *
     * @return mixed
     */
    public static function getLog($id) {
        return Db::getConnection()->query("SELECT * FROM `".PREFICS."log` WHERE `id` = '$id'")->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Установить, лог в архиве или нет
     *
     * @param $id
     * @param int $in_arhive 1 - в архиве, 0 - нет
     *
     * @return bool
     */
    public static function changeLogArchive($id, int $in_arhive) {
        $result = Db::getConnection()->prepare("UPDATE `".PREFICS."log` SET `in_arhive` = $in_arhive WHERE `id` = $id");
        return $result->execute();
    }

    /**
     * Удалить лог
     *
     * @param $id
     *
     * @return bool
     */
    public static function deleteLog($id) {
        $result = Db::getConnection()->prepare("DELETE FROM `".PREFICS."log` WHERE `id` = $id");
        return $result->execute();
    }

    /**
     * Получить типы в логах
     *
     * @return array
     */
    public static function getLogTypes() {
        return Db::getConnection()->query("SELECT DISTINCT `type` FROM `".PREFICS."log`")->fetchAll(PDO::FETCH_ASSOC);
    }
}