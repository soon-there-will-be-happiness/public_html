<?php


class mobizen
{
    const APIURL = 'https://api.mobizon.kz/service/';
    public $apiKey = '';

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public static function sendSMS($phone, $text) {
        $mobizenParams = self::getSettings();

        $mobizen = new mobizen($mobizenParams['apikey']);
        $result = $mobizen->smsApiRequest($phone, $text);
        SMS::WriteLog($phone, $text);

        return $result['code'] == 0 || $result['code'] == 100 ? true : false;
    }

    protected function smsApiRequest($phone, $text) {
        $getParams = '?recipient='.$phone;
        $getParams .= '&text='.urlencode($text);
        $getParams .= '&apiKey='.$this->apiKey;

        $url = $this::APIURL .'message/sendsmsmessage'. $getParams;
        $data = System::curl($url);
        $data = json_decode($data['content'], true);
        return $data;
    }

    public static function getSettings() {
        $settings = System::getSetting();
        $mobizenParams = json_decode($settings['mobizon'], true);
        return $mobizenParams;
    }
}