<?php


class ErrorPage extends baseController
{
    static $errorPage;
    static $page;


    /**
     * @return ErrorPage
     */
    public static function getSelf() {

        if (!isset(self::$errorPage)) {
            self::$errorPage = new ErrorPage();
            self::$page = self::$page ? self::$page : 'error-page';
        }

        return self::$errorPage;
    }


    /**
     *
     */
    public static function return404() {
        require_once ROOT.'/template/404.php';
        die();
    }


    /**
     * @param $message
     * @param string $messageHeader
     * @param int $statusCode
     */
    public static function returnError($message, $messageHeader = 'Ошибка', $statusCode = 200) {
        $self = self::getSelf();//чтобы получить объект
        http_response_code($statusCode);
        $self->showHtml($message, $messageHeader);
        die();
    }


    /**
     * @param $message
     * @param $messageHeader
     */
    public function showHtml($message, $messageHeader) {
        $this->setSEOParams($messageHeader);
        $this->setViewParams('landing', '/errorpage.php', false, null, self::$page);
        require_once ("{$this->template_path}/main.php");
    }


    /**
     * @param $page
     */
    public static function setPage($page) {
        self::$page = $page;
    }


    /**
     * @param $page
     */
    public static function setParameter($page) {
        self::$page = $page;
    }

}