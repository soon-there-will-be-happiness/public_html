<?php

class GoogleDriveAPI {

    private $accessToken;
    private $folderID = "";

    public function __construct($accessToken, $folderID) {
        $this->accessToken = $accessToken;
        $this->folderID = $folderID;
    }

    /**
     * Получить заголовки
     * @param array $toMerge
     *
     * @return array|string[]
     */
    private function getAuthHeaders($toMerge = []) {
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
        ];
        return array_merge($headers, $toMerge);
    }

    # Методы работы с файлами

    /**
     * Отправить файл в гугл диск
     * @param $filepath
     *
     * @return array
     */
    public function uploadFile($filepath) {
        $metadata = [
            "name" => basename($filepath),
        ];

        if (!empty($this->folderID)) {
            $metadata["parents"] = [$this->folderID];
        }

        $boundary = "------WebKitFormBoundary" . md5(rand());
        $payload = "--{$boundary}\r\n" .
            "Content-Type: application/json; charset=UTF-8\r\n\r\n" .
            json_encode($metadata) .
            "\r\n--{$boundary}\r\n" .
            "Content-Type: application/octet-stream\r\n" .
            "Content-Transfer-Encoding: binary\r\n\r\n" .
            file_get_contents($filepath) .
            "\r\n--{$boundary}--\r\n";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getAuthHeaders([
            "Content-Type: multipart/related; boundary={$boundary}"
        ]));

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        return ["response" => $response, "info" => $info];
    }

    /**
     * Скачать файл
     * @param $fileId - айди файла в гугл диске
     * @param $savePath - путь, куда загружать файл
     *
     * @return bool
     * @throws BucketException
     */
    public function getFile($fileId, $savePath) {
        $headers = $this->getAuthHeaders();

        $url = "https://www.googleapis.com/drive/v3/files/$fileId?alt=media";
        $response = BucketGetter::downloadCurl($url, "GET", $headers, $savePath);

        if ($response['http_code'] != 200) {
            throw new BucketException("GoogleDrive: не удалось скачать файл", ["response" => $response, "functionParams" => [$fileId, $savePath]]);
        }

        if (!is_file($savePath)) {
            throw new BucketException("GoogleDrive: не удалось сохранить загруженный файл", ["response" => $response, "functionParams" => [$fileId, $savePath]]);
        }

        return true;
    }

    /**
     * Удалить файл
     * @param $file_id
     *
     * @return bool
     * @throws BucketException
     */
    public function removeFile($file_id) {
        $url = 'https://www.googleapis.com/drive/v3/files/' . $file_id;
        $headers = $this->getAuthHeaders(['Content-Type: application/json']);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 204) {
            throw new BucketException("GoogleDrive: не получилось удалить файл", ["request" => [$headers, $url], "response" => $response, "info" => $info]);
        }

        return true;
    }

    # Авторизация

    /**
     * Обменять userCode(authorization_code) на токены
     *
     * @param $userCode
     * @param $client_id
     * @param $client_secret
     * @param $redirect_uri
     *
     * @return array
     * @throws BucketException
     */
    public static function getGoogleTokensFromCode($userCode, $client_id, $client_secret, $redirect_uri) {
        $request = [
            "code"          => $userCode,
            "client_id"     => $client_id,
            "client_secret" => $client_secret,
            "redirect_uri"  => $redirect_uri,
            "grant_type"    => "authorization_code",
        ];

        $response = System::curl("https://www.googleapis.com/oauth2/v3/token", $request);
        if ($response['info']['http_code'] != 200) {
            throw new BucketException("Google api: Не удалось получить токены из кода подтверждения", ["request" => $request, "response" => $response]);
        }

        $responseJson = json_decode($response['content'], true);
        if (!$responseJson) {
            throw new BucketException("Google api: Пришли плохие данные при попытке получить токен из кода подтверждения", ["request" => $request, "response" => $response, "json" => $responseJson]);
        }

        $responseJson['expires_in'] = time() + $responseJson['expires_in'] - 10;

        return $responseJson;
    }

    /**
     * Проверить, не истек ли токен. Получить новый если истек
     * @param $googleResponse
     * @param $googleData
     *
     * @return mixed
     * @throws BucketException
     */
    public static function checkGoogleTokenExpires($googleResponse, $googleData) {
        if (time() < $googleResponse['expires_in']) {
            return $googleResponse;
        }

        $access_token_url = 'https://oauth2.googleapis.com/token';
        $request = array(
            'client_id' => $googleData['google_client_id'],
            'client_secret' => $googleData['google_client_secret'],
            'refresh_token' => $googleResponse['refresh_token'],
            'grant_type' => 'refresh_token'
        );

        $response = System::curl($access_token_url, $request);
        if ($response['info']['http_code'] != 200) {
            throw new BucketException("Google api: не удалось обновить токен", ["request" => $request, "response" => $response]);
        }

        $responseJson = json_decode($response['content'], true);

        $googleResponse['expires_in'] = time() + $responseJson['expires_in'] - 10;
        $googleResponse['access_token'] = $responseJson['access_token'];

        return $googleResponse;
    }

}