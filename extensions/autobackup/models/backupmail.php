<?php


class BackupMail {

    public $message;
    public $header;
    private $emails_to_send = [];
    static $settings = [];


    /**
     * BackupMail constructor.
     * @param $emails_to_send
     */
    public function __construct($emails_to_send) {
        $this->emails_to_send = $emails_to_send ?? [];
        if (empty(self::$settings)) {
            self::$settings = System::getSetting();
        }
    }

    /**
     * Установить тему и текст сообщения
     * @param $header
     * @param $message
     *
     * @return $this
     */
    public function setMessage($header, $message) {
        $this->header = $header;
        $this->message = $message;
        return $this;
    }

    /**
     * Отправить email
     */
    public function sendMessage() {
        foreach ($this->emails_to_send as $email) {
            Email::sender($email, $this->header, $this->message, self::$settings, self::$settings['sender_name'], self::$settings['sender_email']);
        }
    }

    /**
     * Получить эмейлы для отправки из настроек
     * @param $extEmails
     *
     * @return array|mixed
     */
    public static function getEmailsToSend($extEmails) {
        if (!empty($extEmails)) {
            return $extEmails;
        }
        self::$settings = System::getSetting();

        return empty(self::$settings['admin_email']) ? [] : [self::$settings['admin_email']];
    }

}