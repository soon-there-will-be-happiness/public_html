<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 16.08.2018
 * Time: 7:35
 */

namespace esas\hutkigrosh\utils;


use Exception;
use Logger as Log4php;
use Throwable;

class Logger
{
    private $logger;

    /**
     * LoggerDefault constructor.
     * @param $logger
     */
    public function __construct($name)
    {
        $this->logger = Log4php::getLogger($name);
    }


    public static function init()
    {
        $dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/logs';
        self::createSafeDir($dir);
        Log4php::configure(array(
            'rootLogger' => array(
                'appenders' => array('fileAppender'),
                'level' => 'INFO',
            ),
            'appenders' => array(
                'fileAppender' => array(
                    'class' => 'LoggerAppenderFile',
                    'layout' => array(
                        'class' => 'LoggerLayoutPattern',
                        'params' => array(
                            'conversionPattern' => '%date{Y-m-d H:i:s,u} | %logger{0} | %-5level | %msg %n %ex',
                        )
                    ),
                    'params' => array(
                        'file' => $dir . '/hutkigrosh.log',
                        'append' => true
                    )
                )
            )
        ));
    }

    public static function getLogger($name)
    {
        return new Logger($name);
    }

    public function error($message, $throwable = null)
    {
        $this->logger->error($message, self::wrapp($throwable));
    }

    public function warn($message, $throwable = null)
    {
        $this->logger->warn($message, self::wrapp($throwable));
    }

    public function info($message, $throwable = null)
    {
        $this->logger->info($message, self::wrapp($throwable));
    }

    public function debug($message, $throwable = null)
    {
        $this->logger->debug($message, self::wrapp($throwable));
    }

    public function fatal($message, $throwable = null)
    {
        $this->logger->fatal($message, self::wrapp($throwable));
    }

    public function trace($message, $throwable = null)
    {
        $this->logger->trace($message, self::wrapp($throwable));
    }

    /**
     * В библиотеке log4php v 2.3.0 есть баг с вывводом trace, при работе с php 7
     */
    private static function wrapp(Throwable $th = null)
    {
        if ($th == null)
            return null;
        elseif ($th instanceof Exception)
            return $th;
        else
            return new Exception($th->getMessage(), $th->getCode(), $th);
    }

    /**
     * Создает директорию с файлом .htaccess
     * Для ограничения доступа из вне к файлам логов
     * @param $dirname
     * @throws Exception
     */
    private static function createSafeDir($dirname)
    {
        if (!is_dir($dirname) && !mkdir($dirname)) {
            throw new Exception("Can not create log dir[" . $dirname . "]");
        }
        $file = $dirname . '/.htaccess';
        if (!file_exists($file)) {
            $content =
                '<Files *.log>Deny from all</Files>' . PHP_EOL;
            file_put_contents($file, $content);
        }
    }
}