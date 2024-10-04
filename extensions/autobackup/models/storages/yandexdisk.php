<?php


class YandexDisk {

    static $diskPath = "/autobackup/";

    private $token = "";

    public function __construct($token, $diskPath = "/autobackup/") {
        $this->token = $token;
        self::$diskPath = $diskPath;
    }

    public function makeFolder($diskPath = "") {
        if (empty($diskPath)) {
            $diskPath = self::$diskPath;
        }

        $getparams = http_build_query(["path" => $diskPath]);
        $url = "https://cloud-api.yandex.net/v1/disk/resources?".$getparams;
        $response = BackupBuckets::curl($url, "PUT", $this->getHeaders(), null,  null, false, "PUT");

        if (in_array($response['info']['http_code'], [201, 409])) {
            return $response;
        }

        throw new BucketException("Я.диск: не удалось создать папку '".$diskPath."'", ["solution" => "Создайте путь $diskPath самостоятельно", "response" => $response]);
    }

    public function uploadFile($filePath) {
        $response = $this->requestToUpload(basename($filePath));
        return $this->startUpload($response['href'], $response['method'], $filePath);
    }

    public function getFile($filePath, $savePath) {
        $urlPath = urlencode($filePath);

        $result = BucketGetter::curl("https://cloud-api.yandex.net/v1/disk/resources/download?path=".$urlPath, "GET", $this->getHeaders());
        $result['response'] = json_decode($result['response'], true);

        if (isset($result['response']['error'])) {
            throw new BucketException("Не получилось получить разрешение на скачивание файла в Я.диск", ["response" => $result, "request" => $this->getHeaders(), $urlPath], 5);
        }

        $result = BucketGetter::downloadCurl($result['response']['href'], $result['response']['method'], $this->getHeaders(), $savePath);

        if (!is_file($savePath)) {
            throw new BucketException("Не получилось скачать файл из Я.диска", ["response" => $result, "params" => $this], 5);
        }

        return $savePath;
    }

    public function removeFile($filepath) {
        $query = http_build_query(["path" => "disk:".$filepath, "permanently" => true]);
        $result = BackupRemover::curl("https://cloud-api.yandex.net/v1/disk/resources/?$query", "GET", $this->getHeaders(), null, null, false, "DELETE");

        if ($result['info']['http_code'] != 204) {
            throw new BucketException("Не получилось удалить файл из Я.Диск", ["response" => $result, "params" => $this, "request" => [$query, $this->getHeaders()]]);
        }

        return true;
    }

    private function requestToUpload($filename) {
        $url = "https://cloud-api.yandex.net/v1/disk/resources/upload?path=".$this->getDiskPath($filename);
        $result = BackupBuckets::curl($url, "GET", $this->getHeaders());

        $result['response'] = json_decode($result['response'], true);
        if (isset($result['response']['error'])) {
            throw new BucketException("Не получилось получить разрешение на загрузку файла в Я.диск", ["response" => $result , "yandexdisk" => $this]);
        }

        return $result['response'] ?? false;
    }

    private function startUpload($url, $method, $filepath) {
        $result = BackupBuckets::curl($url, $method, $this->getHeaders(), null, $filepath, true, $method);
        
        if ($result['info']['http_code'] != 201) {
            throw new BucketException("Не получилось загрузить файл в я.Диск", ["response" => $result , "params" => $this]);
        }

        return [
            "params" => ["yandex_disk" => $this->token],
            "diskPath" => $this->getDiskPath(basename($filepath), false),
        ];
    }

    private function getDiskPath($filename, $urlEncode = true) {
        $path = self::$diskPath.$filename;
        if ($urlEncode) {
            return urlencode($path);
        }
        return $path;
    }

    /**
     * Получить заголовки для запроса
     * @param array $toMerge
     *
     * @return array|string[]
     */
    private function getHeaders($toMerge = []) {
        $headers = ["Authorization: OAuth $this->token"];
        return array_merge($headers, $toMerge);
    }
}