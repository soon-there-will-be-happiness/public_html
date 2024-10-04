<?php

class BackupRemover {

    private $filename = "";
    private $storage_type;

    private $params;
    private $sql_id;

    private $nowBucketData;
    private $nowBucketId;


    /**
     * Создать объект удаления файла из хранилища
     *
     * @param $filename
     * @param $storage_type
     * @param $params
     * @param $sql_id
     * @param $bucket_id
     */
    private function __construct($filename, $storage_type, $params, $sql_id, $bucket_id) {
        $this->filename = $filename;
        $this->storage_type = $storage_type;
        $this->params = $params;
        $this->sql_id = $sql_id;
        $this->nowBucketId = $bucket_id;
    }

    /**
     * Удалить файл из хранилищ
     *
     * @param $filename
     * @param $storage_type
     * @param $params
     * @param $sql_id
     * @param $bucket_id
     *
     * @return static|null
     */
    public static function removeFileInStorages($filename, $storage_type, $params, $sql_id, $bucket_id) {
        $self = new BackupRemover($filename, $storage_type, $params, $sql_id, $bucket_id);
        return $self->removeFiles();
    }

    /**
     * Вызвать метод удаления
     * @return $this|null
     */
    private function removeFiles() {
        $result = false;
        try {
            switch ($this->storage_type) {
                case 0://ftp
                    $result = $this->removeFileFromFtp();
                    break;
                case 1://Я.диск
                    $result = $this->removeFileFromYandexDisk();
                    break;
                case 2://DROPBOX
                    $result = $this->removeFileFromDropbox();
                    break;
                case 3://S3
                    $result = $this->removeFileFromS3();
                    break;
                case 4://Local
                    $result = $this->removeFileFromLocal();
                    break;
                case 5:
                    $result = $this->removeFileFromGoogleDrive();
                    break;
            }
        } catch (BucketException $bucketException) {
            BackupWorker::showInfo($bucketException->getMessage());
        }
        if (!$result) {
            return null;
        }

        return $this;
    }

    //////////////////////////////////////////
    /// Методы удаления файлов из хранилища
    /////////////////////////////////////////

    private function removeFileFromS3() {
        $params = $this->params['data']['params'];

        $keyid = $params['s3_keyid'];
        $secret = $params['s3_secret'];
        $folder = $params['s3_bucket'];
        $endpoint = $params['s3_endpoint'];

        S3::setAuth($keyid, $secret);
        S3::$endpoint = $endpoint;

        return S3::deleteObject($folder, $this->filename);
    }

    private function removeFileFromFtp() {
        $params = $this->params['data']['params'];
        $ip = $params['ftp_ip'];
        $port = $params['ftp_port'];
        $login = $params['ftp_login'];
        $pass = $params['ftp_pass'];

        $server_path = $this->params['data']['server_filename'];

        $ftp = @ftp_connect($ip, $port);
        if (!$ftp) {
            throw new BucketException("Не удалось установить FTP соединение с $ip:$port", $this->params);
        }

        $login = @ftp_login($ftp, $login, $pass);
        if (!$login) {
            throw new BucketException("Не удалось авторизоваться в FTP $ip:$port. Пользователь: $login", $this->params);
        }

        @$result = ftp_delete($ftp, $server_path);
        if (!$result) {
            throw new BucketException("Не удалось удалить файл $this->filename на ftp-сервере $ip:$port", $this->params);
        }

        return true;
    }

    private function removeFileFromYandexDisk() {
        $params = $this->params['data']['params'];
        $token = $params['yandex_disk'];
        $filepath = $this->params['data']['diskPath'];

        $yandexDisk = new YandexDisk($token);

        return $yandexDisk->removeFile($filepath);
    }

    private function removeFileFromDropbox() {
        $path = $this->params['data']['response']['path_lower'];

        $this->nowBucketData = BackupTables::getBucket($this->nowBucketId);
        $this->nowBucketData['params'] = json_decode($this->nowBucketData['params'], true);
        $token = $this->nowBucketData['params']['dropbox_token'];
        $refresh_token = $this->nowBucketData['params']['dropbox_refresh_token'];

        $appKey = $this->nowBucketData['params']['dropbox_app_key'] ?? "";
        $appSecret = $this->nowBucketData['params']['dropbox_app_secret'] ?? "";

        if (empty($appKey) || empty($appSecret)) {
            throw new BucketException("Не указан appKey и appSecret в хранилище id".$this->nowBucketData['id'], [
                "params" => $this->nowBucketData['params'],
            ]);
        }

        $result = BucketGetter::checkDropboxAuth($token, $refresh_token, $appKey, $appSecret);
        if (is_array($result)) {//если пара токенов была обновлена -> обновить запись в бд
            $this->nowBucketData['params']['dropbox_token'] = $result['access_token'];
            $this->nowBucketData['params']['dropbox_refresh_token'] = $result['refresh_token'];
            $token = $result['access_token'];

            BackupTables::editBucket($this->nowBucketData['id'], $this->nowBucketData['title'], $this->nowBucketData['type'], json_encode($this->nowBucketData['params']));
        }
        if (!$result) {
            throw new BucketException("Не удалось авторизоваться в dropbox", ["token" => $token, "refresh_token" => $refresh_token]);
        }

        $dropboxApi = new DropboxApi($appKey, $appSecret, $token);

        return $dropboxApi->removeFile($path);
    }

    private function removeFileFromLocal() {
        $path = $this->params['data']['path'];
        if (!is_file($path)) {
            return true;
        }

        $res = unlink($path);
        if (!$res) {
            throw new BucketException("Не удалось удалить локальный файл $path");
        }

        return true;
    }

    private function removeFileFromGoogleDrive() {
        $this->nowBucketData = BackupTables::getBucket($this->nowBucketId);
        $this->nowBucketData['params'] = json_decode($this->nowBucketData['params'], true);

        $google = $this->nowBucketData['params']['google'] ?? false;
        if (!$google OR empty($google['access_token'])) {
            throw new BucketException("Google api: нет токена. Проверьте хранилище {$this->nowBucketData['id']}");
        }

        $newGoogleResponse = GoogleDriveAPI::checkGoogleTokenExpires($google, $this->nowBucketData['params']);
        $newParams = $this->nowBucketData['params'];
        $newParams['google'] = $newGoogleResponse;
        BackupTables::editBucket($this->nowBucketData['id'], $this->nowBucketData['title'], $this->nowBucketData['type'], json_encode($newParams));

        $google = $newParams['google'];
        $accessToken = $google['access_token'];

        $fileId = $this->params['data']['response']['id'];
        if(empty($fileId)) {
            throw new BucketException("GoogleDrive: нет данных о файле", ["params" => $this->params]);
        }

        $googleDrive = new GoogleDriveAPI($accessToken, $newParams['google_folder_id'] ?? "");

        return $googleDrive->removeFile($fileId);
    }

    public function removeCopyInDatabase($id = null) {
        if (!$id) {
            $id = $this->sql_id;
        }

        $sql = "DELETE FROM `".PREFICS."backup_copys` WHERE `id` = :id";
        $result = Db::getConnection()->prepare($sql);

        return $result->execute([':id' => $id]);
    }


    # Вспомогательные методы

    public static function sortCopiesToTasks($backupCopies) {
        $sortedCopys = [];
        foreach ($backupCopies as $copy) {
            $sortedCopys[$copy['task_id']][] = $copy;
        }

        return $sortedCopys;
    }

    public static function sortCopiesToTypeFromCopiesTasks($backupCopies) {
        $sortedCopys = [];
        foreach ($backupCopies as $task_id => $taskCopies) {
            foreach ($taskCopies as $copy) {
                $type = BackupTables::getBackupType($copy['filename']);
                $sortedCopys[$task_id][$type][] = $copy;
            }
        }

        return $sortedCopys;
    }

    public static function sortCopiesParts($copys) {
        $copys = self::sortCopiesToTasks($copys);
        $copys = self::sortCopiesToTypeFromCopiesTasks($copys);
        $sortedByDate = [];
        //Сортировка по дате(набору копий)
        foreach ($copys as $task_id => $taskCopies) {
            foreach ($taskCopies as $type => $typeCopies) {
                foreach ($typeCopies as $key => $typeCopy){
                    $sortedByDate[$task_id][$type][$typeCopy['date']][] = $typeCopy;
                }
            }
        }
        $copys = $sortedByDate;

        $toRemove = [];
        //Выбрать неактуальные копии
        foreach ($copys as $task_id => $taskCopies) {
            foreach ($taskCopies as $type => $copy) {

                $task = BackupTables::getTaskById($task_id);
                if (!$task) {//если задание не было найдено - удалить копию
                    $toRemove[] = $copy;
                    continue;
                }

                $copyCount = $task['amount_copies'] ?? 5;//Кол-во удаленных копий
                $keys = array_keys($copy);
                if (count($keys) < $copyCount) {
                    continue;
                }

                $keys = array_reverse($keys);
                $toRemoveKeys[] = array_slice($keys, 0, count($keys) - $copyCount);

                foreach ($toRemoveKeys as $removeKeys) {
                    foreach ($removeKeys as $removeKey) {
                        foreach ($copy[$removeKey] as $removeCopies) {
                            $toRemove[] = $removeCopies;
                        }
                    }
                }
            }
        }

        return !empty($toRemove) ? $toRemove : false;
    }



    /**
     * Сделать http запрос
     *
     * @param $url
     * @param string $method
     * @param array $header
     * @param null $body
     * @param null $filePath
     * @param false $postFile
     *
     * @return array
     */
    public static function curl($url, $method = "GET", $header = [], $body = null, $filePath = null, $postFile = false, $replace_method = false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($method == "PUT") {
            curl_setopt($ch, CURLOPT_PUT, true);
        }
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($filePath && $postFile) {
            $fp = fopen($filePath, 'r');
            if (!$fp) {
                throw new BucketException("Не возможно открыть файл $filePath");
            }
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
            curl_setopt($ch, CURLOPT_INFILE, $fp);
        }
        if ($postFile) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        }

        if ($replace_method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $replace_method);
        }

        $res = curl_exec($ch);
        $info = curl_getinfo($ch);

        return ["response" => $res, "info" => $info];
    }


    /**
     * Получить не актуальные копии
     * @param false $partsOrFull - если true то будут получены бекапы разделенные на части, иначе - только целые бекапы
     *
     * @return array
     */
    public static function getCopiesToRemove($partsOrFull = false) {
        if ($partsOrFull) {
            $allCopies = BackupTables::getBackupCopies("WHERE `is_part` = 1");
        } else {
            $allCopies = BackupTables::getBackupCopies("WHERE `is_part` = 0");
        }

        if (!$allCopies) {
            return [];
        }

        $sorted = [];
        foreach ($allCopies as $copy) {
            $task = $copy['task_id'];
            $date = $copy['date'];
            $sorted[$task][$date][] = $copy;
        }
        $allCopies = $sorted;

        $toRemove = [];
        foreach ($allCopies as $task_id => $copyGroupTask) {
            $task = BackupTables::getTaskById($task_id);
            $taskCopiesAmount = $task['amount_copies'];

            $countOfCopies = count($copyGroupTask);
            if ($countOfCopies <= $taskCopiesAmount) {
                continue;
            }

            $copiesDate = array_keys($copyGroupTask);
            sort($copiesDate);

            $toRemoveDate = array_slice($copiesDate, 0, $taskCopiesAmount - 1);

            foreach ($toRemoveDate as $removeDate) {
                $toRemove = array_merge($toRemove, $copyGroupTask[$removeDate]);
            }

        }

        return $toRemove;
    }

    public static function removeCopies($copies = [], $alwaysRemoveFromDb = false) {
        $removed = [];

        foreach ($copies as $copy) {

            if (in_array($copy['filename'] . $copy['id'], $removed)) {
                continue;
            }

            BackupWorker::showInfo("Удаление " . $copy['filename'] . " из хранилищ");

            $res = BackupRemover::removeFileInStorages($copy['filename'], $copy['params']['bucketType'], $copy['params'], $copy['id'], $copy['bucket_id']);
            if ($res || $alwaysRemoveFromDb) {
                $removed[] = $copy['filename'] . $copy['id'];
                $res->removeCopyInDatabase();
            }
        }

        return $removed;
    }

}