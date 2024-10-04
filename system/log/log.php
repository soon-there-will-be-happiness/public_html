<?php


class Log
{
    /** @var bool записывать ли лог в файл, помимо бд? */
    const WriteInFile = true;

    /** @var string формат даты */
    const DateFormat = 'm/d/Y h:i:s';

    /** @var int Уровень критичности события, при котором админу нужно послать уведомление */
    const LevelToNotifyAdmin = 4;

    const LogLevels = [
        0 => "[DEBUG]",
        1 => "[INFO]",
        2 => "[NOTICE]",
        3 => "[WARNING]",
        4 => "[ERROR]",
        5 => "[CRITICAL]",
        6 => "[ALERT]",
        7 => "[EMERGENCY]",
    ];


    private static $logdir = ROOT.'/log/';
    private static $date;

    //Значения по дефолту
    const DefaultType = "app";
    const DefaultLevel = 0;
    const DefaultMessage = "";
    const DefaultContext = [];



    /**
     * Записать событие
     * Дока по логированию: /system/log/log_docs.md
     *
     * @param int $level - уровень события(от 0 до 7). Где 0 - событие отладки, 4 - ошибка, 7 - система не работает. Подробнее в методе Log::logLevelToText
     * @param string $message - сообщение
     * @param array $context - массив с значениями, которые могут дать полезную информацию о случившемся
     * @param string $type - тип. Для события произошедшего в расширении - название этого расширения. "app" - в ином случае
     *
     * @return bool
     */
    public static function add($level = self::DefaultLevel, $message = self::DefaultMessage, $context = self::DefaultContext, $type = self::DefaultType) {
        self::$date = time();
        return self::writeLog($message, self::$date, $context, $level, $type, 0);
    }


    /**
     * Записать в лог
     *
     * @param $message
     * @param $date
     * @param $context
     * @param $level
     * @param $type
     * @param int $in_arhive
     *
     * @return bool
     */
    private static function writeLog($message, $date, $context, $level, $type, $in_arhive = 0) {
        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE);

        $fileRes = self::writeLogInFile($message, date(self::DateFormat, $date), $context, $level, $type);
        $dbRes = self::writeLogInDb($message, $date, $contextJson, $level, $type, $in_arhive);
        if ($level >= self::LevelToNotifyAdmin && $dbRes) {
            AdminNotice::addNotice($message, "/admin/logs/$dbRes");
        }


        return $dbRes && $fileRes;
    }


    /**
     * Записать в бд
     *
     * @param $message
     * @param $date
     * @param $context
     * @param $level
     * @param $type
     * @param int $in_arhive
     *
     * @return integer | bool
     */
    private static function writeLogInDb($message, $date, $context, $level, $type, $in_arhive = 0) {
        $sql = "INSERT INTO `".PREFICS."log` (message, date, context, level, type, in_arhive) VALUES (:message, :date, :context, :level, :type, :in_arhive)";
        $result = Db::getConnection()->prepare($sql);
        $result = $result->execute([
            ':message' => $message,
            ':date' => $date,
            ':context' => $context,
            ':level' => $level,
            ':type' => $type,
            ':in_arhive' => $in_arhive
        ]);
        $id = Db::getConnection()->lastInsertId();

        return $result ? $id : false;
    }

    /**
     * Записать в файл(если self::WriteInFile = true)
     *
     * @param $message
     * @param $date
     * @param $context
     * @param $level
     * @param $type
     *
     * @return bool
     */
    private static function writeLogInFile($message, $date, $context, $level, $type) {

        if (!self::WriteInFile) {
            return true;
        }

        $text =
            self::logLevelToText($level)." - ".$date." - ".$type." - ".
            $message."\n".
            json_encode($context, JSON_PRETTY_PRINT).
            "\n"."\n"
        ;

        if (!is_dir(dirname(self::$logdir))) {
            mkdir(dirname(self::$logdir));
        }

        $logfile = self::$logdir.$type ?? self::DefaultType;

        return (bool) file_put_contents($logfile, $text, FILE_APPEND);
    }

    /**
     * Конвертировать уровень лога в текст
     *
     * @param $level
     *
     * @return string
     */
    public static function logLevelToText($level) {
        switch ($level) {
            case 1:
                return self::LogLevels[1];//Интересное событие
            case 2:
                return self::LogLevels[2];//Существенные события, но не ошибки
            case 3:
                return self::LogLevels[3];//Исключительные случаи, но не ошибки
            case 4:
                return self::LogLevels[4];//Ошибки исполнения, не требующие сиюминутного вмешательства
            case 5:
                return self::LogLevels[5];//Критические состояния (компонент системы недоступен, неожиданное исключение)
            case 6:
                return self::LogLevels[6];//Действие требует вмешательства
            case 7:
                return self::LogLevels[7];//Система не работает
            default:
                return self::LogLevels[0];//Информация для отладки
        }
    }


    const ENABLE_XHPROF = true;

    public static function xhprofStart() {
        if (function_exists('xhprof_enable') && self::ENABLE_XHPROF) {
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }
    }

    public static function xhprofDisable() {
        if (!function_exists('xhprof_disable') || !self::ENABLE_XHPROF) {
            return ;
        }
        $result = xhprof_disable();
        $url = $_SERVER['HTTP_HOST'] ?? "" . $_SERVER['REQUEST_URI'] ?? "";
        $result['serverdata'] = ['date' => time(), 'url' => $url];

        file_put_contents(ROOT."/profilinglogs/file", json_encode($result) . "\n", FILE_APPEND);
    }

}