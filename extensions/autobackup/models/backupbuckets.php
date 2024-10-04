<?php


class BackupBuckets {

    /** @var string путь к файлу, который нужно отправить */
    private $sendFilePath;
    /** @var array массив хранилищ для отправки */
    private $bucketsList = [];

    /** @var array Текущие данные хранилища */
    private $nowBucketData;

    private function __construct($file) {
        $this->sendFilePath = $file;
        $this->bucketsList = self::getBuckets();
    }

    /**
     * Отправить файл в хранилища
     * @param $archive
     *
     * @return bool
     * @throws BucketException
     */
    public static function send($archive) {
        $buckets = new self($archive);
        return $buckets->sendToClouds();
    }


    /**
     * Запустить загрузку файлов во все хранилища
     * @return bool
     * @throws BucketException
     */
    private function sendToClouds() {
        $sendResult = false;

        foreach ($this->bucketsList as $bucket) {
            if (boolval($this->sender($bucket))) {
                $sendResult = true;
            }
        }

        if (!$sendResult) {
            throw new BucketException("Не удалось загрузить файл ($this->sendFilePath) ни в одно хранилище");
        }

        return $sendResult;
    }

    /**
     * Получить включенные хранилища для отправки
     *
     * @return array
     * @throws BackupException
     */
    private static function getBuckets() {
        $buckets = BackupTables::getBucketsByIds(BackupData::$storages);

        if (!$buckets) {
            throw new BackupException("Нет хранилищ для загрузки файлов бекапа");
        }

        foreach ($buckets as $key => $bucket) {
            $buckets[$key]['params'] = json_decode($bucket['params'], true);
        }

        return $buckets;
    }

    /**
     * Получить размер файла
     * @param $file
     *
     * @return false|int
     */
    private static function getFileInfo($file) {
        return filesize($file);
    }

    /**
     * Проверить, является ли файл бекапа частью
     *
     * @param $filename
     *
     * @return bool
     */
    private static function checkFileIsPart($filename) {
        $filename = str_replace(ROOT, "", $filename);
        $result = strpos($filename, "-part");
        return boolval($result);
    }


    /**
     * Определить куда отправить файл и запустить отправку
     *
     * @param $bucketData
     *
     * @return array|false
     */
    private function sender($bucketData) {
        $result = false;
        $this->nowBucketData = $bucketData;
        $params = $bucketData['params'];
        try {
            switch ($bucketData['type']) {
                case 0:
                    BackupWorker::showInfo("Архив $this->sendFilePath отправляется в FTP ");
                    $result = $this->sendToFTP($params);
                    break;

                case 1:
                    BackupWorker::showInfo("Архив $this->sendFilePath отправляется в Я.Диск ");
                    $result = $this->sentToYandexDisk($params);
                    break;

                case 2:
                    BackupWorker::showInfo("Архив $this->sendFilePath отправляется в DROPBOX ");
                    $result = $this->sendToDropbox($params);
                    break;

                case 3:
                    BackupWorker::showInfo("Архив $this->sendFilePath отправляется в S3");
                    $result = $this->sendToS3($params);
                    break;

                case 4:
                    BackupWorker::showInfo("Архив $this->sendFilePath отправляется в локальное хранилище");
                    $result = $this->sendToLocal($params);
                    break;
                case 5:
                    BackupWorker::showInfo("Архив $this->sendFilePath отправляется в google drive");
                    $result = $this->sendToGoogleDrive($params);
                    break;


                default:
                    throw new BackupException("Не правильный тип хранилища!", ["bucketData" => $bucketData]);
                    break;
            }

            //Записать информацию о загрузке файла
            $params = json_encode([
                "bucketType" => $bucketData['type'],
                "data" => $result,
                "is_smart" => BackupCronHandler::$isSmart,
                "smart_type" => BackupCronHandler::$smartType,
            ]);
            BackupTables::addBackupCopy(
                STARTTIME,
                basename($this->sendFilePath),
                self::getFileInfo($this->sendFilePath),
                BackupData::$currentTaskId,
                $bucketData['id'],
                $params,
                self::checkFileIsPart($this->sendFilePath) ? 1 : 0,
                BackupCronHandler::$isSmart,
                BackupCronHandler::$smartType
            );

        } catch (BucketException $bucketException) {} catch (BackupException $backupException) {}

        return $result;
    }


    ///////////////////////////////////////
    /// Методы отправки файлов в хранилища
    ///////////////////////////////////////


    private function sendToFTP($params) {
        $ip = $params['ftp_ip'];
        $port = $params['ftp_port'];
        $login = $params['ftp_login'];
        $pass = $params['ftp_pass'];
        $path = $params['ftp_path'];


        $ftp = @ftp_connect($ip, $port);
        if (!$ftp) {
            throw new BucketException("Не удалось установить FTP соединение с $ip:$port", $params);
        }


        $login = @ftp_login($ftp, $login, $pass);
        if (!$login) {
            throw new BucketException("Не удалось авторизоваться в FTP $ip:$port. Пользователь: $login", $params);
        }

        @ftp_pasv($ftp, true);
        @ftp_set_option($ftp, FTP_TIMEOUT_SEC, 7200);


        $path = "/".trim($path, "/")."/";
        $filename = basename($this->sendFilePath);


        $result = ftp_put($ftp, $path.$filename, $this->sendFilePath, FTP_BINARY);
        if (!$result) {
            throw new BucketException("Не удалось передать файл $this->sendFilePath на ftp-сервер $ip:$port в директорию $path$filename", $params);
        }

        @ftp_close($ftp);

        return [
            "params" => $params,
            "server_filename" => $path.$filename
        ];
    }

    private function sentToYandexDisk($params) {
        $token = $params['yandex_disk'];

        $yandexDisk = new YandexDisk($token);
        $yandexDisk->makeFolder();

        return $yandexDisk->uploadFile($this->sendFilePath);
    }


    private function sendToDropbox($params) {
        $this->nowBucketData = BackupTables::getBucket($this->nowBucketData['id']);
        $this->nowBucketData['params'] = json_decode($this->nowBucketData['params'], true);
        $token = $this->nowBucketData['params']['dropbox_token'];
        $refresh_token = $this->nowBucketData['params']['dropbox_refresh_token'] ?? "";

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
        $result = $dropboxApi->uploadFile($this->sendFilePath);

        return [
            "params" => $params,
            "response" => $result,
        ];
    }


    private function sendToS3($params) {
        $keyid = $params['s3_keyid'];
        $secret = $params['s3_secret'];

        $folder = $params['s3_bucket'];
        $endpoint = $params['s3_endpoint'];


        $filename = basename($this->sendFilePath);

        try {
            $s3 = S3::setAuth($keyid, $secret);
            S3::$endpoint = $endpoint;
            $result = S3::putObject(S3::inputFile($this->sendFilePath, false), $folder, $filename, S3::ACL_PUBLIC_READ_WRITE);
        } catch (S3Exception $exception) {
            throw new BucketException("Не удалось отправить файл в S3. Ошибка: " . $exception->getMessage(), $params,  $exception->getCode(), $exception);
        }

        return ["params" => $params];
    }

    private function sendToLocal($params) {
        $path = "/".trim(empty($params['local_path']) ? adminBackupController::DEFAULT_LOCAL_STORAGE_PATH : $params['local_path'], "/")."/";
        $path = ROOT.$path;

        if (!is_dir($path)) {
            $res = mkdir($path);
            if (!$res) {
                throw new BucketException("Не удалось создать локальную папку $path");
            }
        }

        $copyPath = $path.basename($this->sendFilePath);
        $res = copy($this->sendFilePath, $copyPath);
        if (!$res) {
            throw new BucketException("Не удалось поместить файл в локальную папку $path");
        }

        return ["path" => $copyPath];
    }


    private function sendToGoogleDrive($params) {
        $google = $params['google'];
        if (!$google OR empty($google['access_token'])) {
            throw new BucketException("Google api: нет токена. Проверьте хранилище {$this->nowBucketData['id']}");
        }

        //Проверить актуальность токена/получить новый
        $newGoogleResponse = GoogleDriveAPI::checkGoogleTokenExpires($google, $params);

        $newParams = $params;
        $newParams['google'] = $newGoogleResponse;

        BackupTables::editBucket($this->nowBucketData['id'], $this->nowBucketData['title'], $this->nowBucketData['type'], json_encode($newParams));

        $google = $newParams['google'];
        $accessToken = $google['access_token'];

        //Загрузка файла

        $googleDrive = new GoogleDriveAPI($accessToken, $newParams['google_folder_id'] ?? "");
        $response = $googleDrive->uploadFile($this->sendFilePath);

        if ($response['info']['http_code'] != 200) {
            throw new BucketException("Google drive: не удалось загрузить файл", ["file" => $this->sendFilePath, "storage_params" => $newParams, "response" => $response]);
        }

        $responseJson = json_decode($response['response']);

        return ["response" => $responseJson, "params" => $newParams];
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


}
