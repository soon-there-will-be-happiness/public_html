<?php defined('BILLINGMASTER') or die;


class TrainingPublicHomework {

    /**
     * @param $lesson_id
     * @param $data
     * @param $public_homework
     * @return bool
     */
    public static function saveSettings($lesson_id, $data, $public_homework) {
        $db = Db::getConnection();
        if ($public_homework) {
            $query = 'UPDATE '.PREFICS.'training_public_homework_settings SET status = :status, statuses = :statuses,
                      user_choose = :user_choose WHERE lesson_id = :lesson_id';
        } else {
            $query = 'INSERT INTO '.PREFICS.'training_public_homework_settings (lesson_id, status, statuses, user_choose) 
                      VALUES (:lesson_id, :status, :statuses, :user_choose)';
        }

        $statuses = isset($data['statuses']) ? implode(',', $data['statuses']) : null;
        $result = $db->prepare($query);
        $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->bindParam(':status', $data['status'], PDO::PARAM_INT);
        $result->bindParam(':statuses',$statuses, PDO::PARAM_STR);
        $result->bindParam(':user_choose', $data['user_choose'], PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param $lesson_id
     * @return bool|mixed
     */
    public static function getSettings($lesson_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS."training_public_homework_settings WHERE lesson_id = :lesson_id");
        $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if ($data && $data['statuses']) {
            $data['statuses'] = explode(',', $data['statuses']);
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param $lesson_id
     * @param $user_id
     * @param null $limit
     * @param int $offset
     * @return array|bool
     */
    public static function getOtherHomeworks($lesson_id, $user_id, $limit = null, $offset = 0) {
        $db = Db::getConnection();
        $query = "SELECT thw.* FROM ".PREFICS."training_home_work AS thw
                  LEFT JOIN ".PREFICS."training_public_homework_settings AS tphs
                  ON tphs.lesson_id = thw.lesson_id 
                  WHERE thw.lesson_id = :lesson_id
                  AND thw.user_id <> :user_id AND tphs.status = 1 
                  AND (thw.public = 1 || (tphs.user_choose = 0 AND FIND_IN_SET(thw.status, tphs.statuses)))
                  ORDER BY thw.create_date DESC";
        $query .= $limit ? " LIMIT $limit" : '';
        $query .= $offset ? " OFFSET $offset" : '';

        $result = $db->prepare($query);
        $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data ? $data : false;
    }



    /**
     * КОЛИЧЕСТВО ПУБЛИЧНЫХ ДОМАШНИХ ЗАДАНИЙ ДРУГИХ ПОЛЬЗОВАТЕЛЕЙ
     * @param $lesson_id
     * @param $user_id
     * @return bool
     */
    public static function getCountOtherHomeworks($lesson_id, $user_id) {
        $db = Db::getConnection();
        $query = "SELECT COUNT(thw.homework_id) FROM ".PREFICS."training_home_work AS thw
                  LEFT JOIN ".PREFICS."training_public_homework_settings AS tphs
                  ON tphs.lesson_id = thw.lesson_id 
                  WHERE thw.lesson_id = :lesson_id
                  AND thw.user_id <> :user_id AND tphs.status = 1 
                  AND (thw.public = 1 || (tphs.user_choose = 0 AND FIND_IN_SET(thw.status, tphs.statuses)))";

        $result = $db->prepare($query);
        $result->bindParam(':lesson_id', $lesson_id, PDO::PARAM_INT);
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @param $settings
     * @return bool
     */
    public static function isAllowMakePublic($settings) {
        if ($settings['status'] && $settings['user_choose']) {
            return true;
        }

        return false;
    }


    /**
     * @param $data
     * @param $lesson_id
     * @param $user_id
     * @return bool
     */
    public static function addComment($data, $lesson_id, $user_id) {
        $homework_id = (int)$data['homework_id'];
        $comment = TrainingHomeWork::getSafeAnswer($data['user_comment']);
        $attach = null;

        if ($comment) {
            if (isset($_FILES['public_homework_user_attach']) && $_FILES['public_homework_user_attach']['size'][0] > 0) {
                $attach = TrainingHomeWork::uploadAttach($_FILES['public_homework_user_attach'], $lesson_id, Training::USER_TYPE_USER);
            }

            return TrainingLesson::writeComment($homework_id, $user_id, 0, $comment, 0, $attach);
        }

        return false;
    }
}