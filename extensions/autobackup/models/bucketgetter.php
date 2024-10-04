<?php


trait BucketGetter {

    private $filename;
    private $tmp_dir = ROOT."/tmp/";
    private $backup_file = "";

    ////////////////////////////////////
    /// Методы получения файлов из хранилищ
    ///
    /// Должны возвращать полный путь до полученного файла из хранилища
    /// Или BucketException в случае ошибки получения
    ////////////////////////////////////

    public static function curl($url, $method = "GET", $header = [], $body = null, $filePath = null, $downloadFilePath = null) {
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

        if ($filePath) {
            $fp = fopen($filePath, 'r');
            if (!$fp) {
                die("Не возможно открыть файл $filePath");
            }
            curl_setopt($ch, CURLOPT_UPLOAD, true);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
            curl_setopt($ch, CURLOPT_INFILE, $fp);
        }

        if ($downloadFilePath) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        $res = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);
        if ($downloadFilePath) {
            file_put_contents($downloadFilePath, $res);
        }

        return ["response" => $res, "info" => $info];
    }

    public static function downloadCurl($url, $method = "GET", $headers = [], $savePath) {
        if (is_file($savePath)) {
            unlink($savePath);
        }

        file_put_contents($savePath, "");
        $file = fopen($savePath, "w+");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);
        fclose($file);

        return $info;
    }

    private function getFileByFTP($path, $params) {
        $ip = $params['ftp_ip'];
        $port = $params['ftp_port'];
        $login = $params['ftp_login'];
        $pass = $params['ftp_pass'];

        $ftp = @ftp_connect($ip, $port);
        if (!$ftp) {
            throw new BucketException("Не удалось установить FTP соединение с $ip:$port");
        }

        $login = @ftp_login($ftp, $login, $pass);
        if (!$login) {
            throw new BucketException("Не удалось авторизоваться в FTP $ip:$port. Пользователь: $login");
        }

        $result = @ftp_get($ftp, $this->tmp_dir.$this->filename, $path, FTP_BINARY);

        if (!$result) {
            throw new BucketException("Не удалось получить файл $path в FTP $ip:$port. Пользователь: $login");
        }

        ftp_close($ftp);

        return $this->tmp_dir.$this->filename;
    }

    private function getFileFromYandexDisk($path, $params) {
        $token = $params["yandex_disk"];

        $yandexDisk = new YandexDisk($token);

        return $yandexDisk->getFile($path, $this->tmp_dir.$this->filename);
    }

    private function getFileFromDropbox($params) {
        $token = $params['data']['params']['dropbox_token'];

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

        $result = BucketGetter::checkDropboxAuth($token, $refresh_token,$appKey, $appSecret);
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
        $dropboxApi->getFile($params['data']['response']['path_lower'], $this->tmp_dir.$this->filename);

        return $dropboxApi->getFile($params['data']['response']['path_lower'], $this->tmp_dir.$this->filename);
    }

    private function getFileFromS3($params) {
        $keyid = $params['data']['params']['s3_keyid'];
        $secret = $params['data']['params']['s3_secret'];
        $folder = $params['data']['params']['s3_bucket'];
        $endpoint = $params['data']['params']['s3_endpoint'];
        $filename = $this->filename;

        try {
            $s3 = S3::setAuth($keyid, $secret);
            S3::$endpoint = $endpoint;

            if (!is_file($this->tmp_dir . $this->filename)) {
                file_put_contents($this->tmp_dir . $this->filename, "");
            }

            $result = S3::getObject($folder, $filename, $this->tmp_dir . $this->filename);

            if ($result->error) {
                throw new BucketException("Не удалось получить файл из s3");
            }

        } catch (S3Exception $exception) {
            throw new BucketException("getFileFromS3: " . $exception->getMessage(), $exception, $exception->getCode(), $exception);
        }

        return $this->tmp_dir.$this->filename;
    }

    private function getFileFromLocal($params) {
        $path = $params['data']['path'];
        if (!is_file($path)) {
            throw new BucketException("Файла $path не существует");
        }

        $res = copy($path,$this->tmp_dir.$this->filename);
        if (!$res) {
            throw new BucketException("Не удалось скопировать файл $path");
        }

        return $this->tmp_dir.$this->filename;
    }

    private function getFileFromGoogleDrive($params) {
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

        $googleDrive = new GoogleDriveAPI($accessToken, $newParams['google_folder_id'] ?? "");
        $googleDrive->getFile($params['data']['response']['id'], $this->tmp_dir.$this->filename);

        return $this->tmp_dir.$this->filename;
    }

    public static function checkItACodeOrTokenDropbox($dropbox_token_or_code) {
        return strpos($dropbox_token_or_code, "sl.") === 0;
    }


    public static function checkDropboxAuth($token_or_code, $refresh_token = null, $appid, $app_secret) {
        $isToken = self::checkItACodeOrTokenDropbox($token_or_code);

        if ($isToken) {
            //Проверить авторизацию если это токен
            $authHeader = ["Authorization: Bearer $token_or_code", "Content-Type: application/json"];
            $res = self::curl("https://api.dropboxapi.com/2/check/user", "POST", $authHeader, json_encode(["query" => "foo"]));

            if ($res['info']['http_code'] == 200) { //Токен актуальный
                return true;
            } else {

                if (!$refresh_token) {
                    throw new BucketException("Dropbox. Токен не актуален, но refresh_token не получен");
                }

                $ch = curl_init();

                $postData = ["refresh_token" => $refresh_token, "grant_type" => "refresh_token"];

                curl_setopt($ch, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_USERPWD, $appid . ':' . $app_secret);

                $headers = [];
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);

                $resJson = json_decode($result, true);

                if (curl_errno($ch) || isset($resJson['error'])) {
                    throw new BucketException("Ошибка обновление токена dropbox", ['response' => $resJson, "curl_error" => curl_error($ch) ?? false, "data" => $postData]);
                }

                return array_merge($resJson, ["refresh_token" => $refresh_token]);//Токен обновился. refresh_token не обновляется
            }

            //Если не получилось авторизоваться, то обновить токен

        } else {//Получить токен из кода
            $ch = curl_init();

            $postData = [
                "code" => $token_or_code,
                "grant_type" => "authorization_code"
            ];

            curl_setopt($ch, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_USERPWD, $appid . ':' . $app_secret);

            $headers = [];
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            $resJson = json_decode($result, true);

            if (curl_errno($ch) || isset($resJson['error'])) {
                throw new BucketException("Ошибка в авторизации dropbox из кода доступа. ", ['response' => $resJson, "curl_error" => curl_error($ch) ?? false, "data" => $postData]);
            }

            return $resJson;//Токен обновился
        }



    }

}