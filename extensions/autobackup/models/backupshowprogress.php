<?php


class BackupShowProgress {

    /**
     * Получить массив вида ["task_id"=>[{прогресс задачи}] ]
     *
     * @param $tasks
     *
     * @return mixed
     */
    public static function getBackupsProgress($tasks) {
        return array_reduce($tasks, function ($arr, $task) {

            $taskProgress = self::getProgressToTaskById($task['id']);
            $arr[$task['id']] = $taskProgress;

            return $arr;
        }, []);
    }

    /**
     * Получить последнюю информацию о статусе выполнении задания
     *
     * @param $task_id
     *
     * @return array
     */
    public static function getProgressToTaskById($task_id, $uid = null) {
        $uid = $uid ? "AND `uid` = '$uid'" : "";
        return Db::getConnection()->query("
            SELECT * FROM `".PREFICS."backup_progress` 
            WHERE `start_time` = (SELECT MAX(`start_time`) FROM `".PREFICS."backup_progress` WHERE `task_id` = '$task_id' $uid)
            AND `task_id` = '$task_id' $uid"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получить последую группу прогресса по заданию
     * @param $task_id
     * @param $last_id
     * @param null $uid
     *
     * @return array
     */
    public static function getProgressToTaskByIdLast($task_id, $last_id, $uid = null) {
        $uid = $uid ? "AND `uid` = '$uid'" : "";
        return Db::getConnection()->query("
            SELECT * FROM `".PREFICS."backup_progress` 
            WHERE `start_time` = (SELECT MAX(`start_time`) FROM `".PREFICS."backup_progress` WHERE `task_id` = '$task_id' $uid)
            AND `task_id` = '$task_id' AND `id` > $last_id $uid"
        )->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Получить процент выполнения задания
     * @param $task
     * @param $progress
     *
     * @return float|int
     */
    public static function calculateTaskProgress($task, $progress) {
        $maxCountActions = count(BackupProgress::ACTIONS);
        $minCountActions = 2;

        $currentTaskTotalActions = self::getTotalTaskActions($task, $minCountActions);
        $currentTaskActionsCount = count($progress);

        $progressInPercent = round($currentTaskActionsCount / $currentTaskTotalActions, 2) * 100;

        return $progressInPercent;
    }

    /**
     * Получить количество действий задания
     * @param $task
     * @param int $minCountActions
     *
     * @return int|mixed
     */
    private static function getTotalTaskActions($task, $minCountActions = 2) {
        $currentTaskTotalActions = $minCountActions;

        if ($task['files_enable'])
            $currentTaskTotalActions += 2;
        if ($task['bd_enable'])
            $currentTaskTotalActions += 2;
        if ($task['clients_enable'])
            $currentTaskTotalActions += 2;

        return $currentTaskTotalActions;
    }
}