<?php

class DropboxApi {

    private $appKey = "";
    private $appSecret = "";
    private $token = "";

    # Загрузка через сессию
    private $sessionID = "";
    const SESSION_CHUNK_SIZE = 1024 * 1024 * 100; // 100 MB

    /**
     * @param $appKey
     * @param $appSecret
     * @param $token
     */
    public function __construct($appKey, $appSecret, $token) {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->token = $token;
    }

    # Методы работы с файлами

    /**
     * Загрузить файл
     * @param $filePath
     *
     * @return mixed - данные о файле
     * @throws BucketException
     */
    public function uploadFile($filePath) {
        if (!is_file($filePath)) {
            throw new BucketException("Dropbox: не найден файл для отправки", ["file" => $filePath]);
        }

        if (filesize($filePath) >= 152043520) {
            return $this->sendBigFile($filePath);
        }

        return $this->sendSmallFile($filePath);
    }

    /**
     * Скачать файл
     * @param $filePath - путь до файла в dropbox
     * @param $savePath - путь сохранения файла
     *
     * @return mixed - путь до сохраненного файла
     * @throws BucketException
     */
    public function getFile($filePath, $savePath) {
        $headers = $this->getHeaders([
            "Content-Type: text/plain",
            "Dropbox-API-Arg: " . json_encode([
                "path" => $filePath,
            ])
        ]);

        $result = BucketGetter::downloadCurl("https://content.dropboxapi.com/2/files/download", "POST", $headers, $savePath);
        if ($result['http_code'] != 200) {
            throw new BucketException("Не удалось скачать файл с DROPBOX", ["response" => $result, "dropboxApi" => $this]);
        }

        if (!is_file($savePath)) {
            throw new BucketException("Dropbox: не удалось сохранить загруженный файл", ["savepath" => $savePath, "info" => $result['info']]);
        }

        return $savePath;
    }

    /**
     * Удалить файл
     * @param $path
     *
     * @return bool
     * @throws BucketException
     */
    public function removeFile($path) {
        $headers = $this->getHeaders(["Content-Type: application/json"]);

        $res = BackupRemover::curl("https://api.dropboxapi.com/2/files/delete_v2", "POST", $headers, json_encode(["path" => $path]));

        $resJson = json_decode($res['response'], true);

        if (isset($resJson['error']) || $res['info']['http_code'] != 200) {
            throw new BucketException("Dropbox: не удалось удалить файл", ["response" => $res, "path" => $path]);
        }

        return true;
    }


    /**
     * Отправить маленький файл
     * @param $filePath
     *
     * @return mixed - ответ сервера
     * @throws BucketException
     */
    private function sendSmallFile($filePath) {
        $headers = $this->getHeaders([
            "Content-Type: application/octet-stream",
            "Dropbox-API-Arg: " . json_encode([
                "path" => "/autobackup/".basename($filePath),
                "mode" => "add",
                "autorename" => false,
                "mute" => false,
                "strict_conflict" => false,
            ])
        ]);

        $res = BackupBuckets::curl("https://content.dropboxapi.com/2/files/upload/", "POST", $headers, null, $filePath, true);

        $resJson = json_decode($res['response'], true);
        if (isset($resJson['error']) || $res['info']['http_code'] != 200) {
            throw new BucketException("Не удалось загрузить файл в DROPBOX", ["dropboxapi" => $this, "request_headers" => $headers, "response" => $res]);
        }

        return $resJson;
    }

    /**
     * Отправить большой файл(через сессии)
     * @param $filePath
     *
     * @return mixed - ответ сервера
     * @throws BucketException
     */
    private function sendBigFile($filePath) {
        $file = fopen($filePath, 'rb');
        $fileSize = filesize($filePath);

        $this->sessionID = $this->startUploadSession();

        $chunkSize = self::SESSION_CHUNK_SIZE;
        $numChunks = ceil($fileSize / $chunkSize);

        $offset = 0;
        for ($i = 0; $i < $numChunks; $i++) {
            $chunk = fread($file, $chunkSize);
            $chunkSizeUploaded = strlen($chunk);

            $this->appendToUploadSession($chunk, $offset);
            $offset += $chunkSizeUploaded;
        }

        fclose($file);

        $resultData = $this->finishUploadSession(basename($filePath), $offset);

        return $resultData;
    }


    /**
     * Начать сессию загрузки
     * @return mixed
     * @throws BucketException
     */
    private function startUploadSession() {
        $url = "https://content.dropboxapi.com/2/files/upload_session/start";
        $headers = $this->getHeaders([
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: {"close": false}',
        ]);

        $response = BackupBuckets::curl($url, "POST", $headers);
        if ($response['info']['http_code'] != 200) {
            throw new BucketException("Dropbox: не удалось начать сессию загрузки файла");
        }

        $responseJson = json_decode($response['response'], true);
        if (!$responseJson) {
            throw new BucketException("Dropbox: не удалось получить id сессии загрузки файла");
        }

        return $responseJson['session_id'];
    }

    /**
     * Отправить кусок файла в сессию
     * @param $fileChunk
     * @param $offset
     *
     * @return bool
     * @throws BucketException
     */
    private function appendToUploadSession($fileChunk, $offset) {
        $url = "https://content.dropboxapi.com/2/files/upload_session/append_v2";
        $headers = $this->getHeaders([
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: '.json_encode([
                "close" => false,
                "cursor" => [
                    "offset" => $offset,
                    "session_id" => $this->sessionID,
                ],
            ])
        ]);

        $response = BackupBuckets::curl($url,"POST", $headers, $fileChunk);
        if ($response['info']['http_code'] != 200) {
            throw new BucketException("Dropbox: не удалось добавить часть файла через сессию", ["response" => $response, "request_headers" => $headers]);
        }

        return true;
    }

    /**
     * Завершить сессию(сохранение файла в dropbox)
     * @param $fileName
     * @param $offset
     *
     * @return mixed
     * @throws BucketException
     */
    private function finishUploadSession($fileName, $offset) {
        $url = "https://content.dropboxapi.com/2/files/upload_session/finish";
        $headers = $this->getHeaders([
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: '.json_encode([
                "cursor" => [
                    "offset" => $offset,
                    "session_id" => $this->sessionID,
                ],
                "commit" => [
                    "path" => "/autobackup/".$fileName,
                    "mode" => "add",
                    "autorename" => false,
                    "mute" => false,
                    "strict_conflict" => false,
                ]
            ])
        ]);

        $result = BackupBuckets::curl($url, "POST", $headers);

        $resJson = json_decode($result['response'], true);
        if (isset($resJson['error']) || $result['info']['http_code'] != 200) {
            throw new BucketException("Dropbox: не удалось завершить сессию загрузки файла", ["dropboxapi" => $this, "request_headers" => $headers, "response" => $result]);
        }

        return $resJson;
    }


    /**
     * Получить заголовки для запроса
     * @param array $toMerge
     *
     * @return array|string[]
     */
    private function getHeaders($toMerge = []) {
        $headers = ["Authorization: Bearer $this->token"];
        return array_merge($headers, $toMerge);
    }

}