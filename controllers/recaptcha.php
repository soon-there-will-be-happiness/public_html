<?php


class recaptcha
{
        /** @var string токен проверки */
    private $gRecaptchaResponse;
        /** @var string адрес апи сервера */
    private $apiUrl = "https://www.google.com/recaptcha/api/siteverify";
        /** @var string секретный токен */
    private $secret;
        /** @var float минимальное значение оценки */
    private $minimalScoreVerifyValue;

    /**
     * reCaptcha constructor.
     *
     * @param string $token
     * @param string $secret
     * @param float $minimalScoreVerifyValue
     */
    public function __construct(string $token, string $secret, $minimalScoreVerifyValue = 0.5) {
        $this->secret = $secret;
        $this->gRecaptchaResponse = $token;
        $this->minimalScoreVerifyValue = $minimalScoreVerifyValue;
    }

    /**
     * Проверить капчу
     * @return bool
     */
    public function checkCaptcha() {
        $response = $this->getResponseFromApi();
        $response = json_decode($response, true);
        return $this->checkSuccess($response);
    }

    /**
     * Запрос к api серверу
     *
     * @return false|string
     */
    private function getResponseFromApi() {
        $url = $this->apiUrl.'?secret='.$this->secret.'&response='.$this->gRecaptchaResponse;
        return file_get_contents($url);
    }

    /**
     * Проверить достаточный ли балл
     * возвращает true если балл больше минимального значения
     * @param array $response
     * @return bool
     */
    private function checkSuccess(array $response) {
        if ($response['success'] == true && $response['score'] >= $this->minimalScoreVerifyValue) {
            return true;
        } else {
            return false;
        }
    }
}