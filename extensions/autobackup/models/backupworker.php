<?php


class BackupWorker {

    /** @var bool записывать ошибки в лог? */
    const WRITE_LOG = true;

    /** @var bool Отображать сообщения в консоли? - prod = false */
   const SHOW_LOG_IN_CONSOLE = false;

    /** @var array Лог действий */
    protected static $showInfoLog = [];

    public static function showInfo($message, $level = 1, $array_to_log = [], $n = "\n") {

        self::$showInfoLog[] = ["message"=>$message, "data" => $array_to_log];

        if (!defined("CURR_VER") && self::SHOW_LOG_IN_CONSOLE) {
            echo $n.$message.$n;
        }
    }


    /**
     * Сделать архив
     *
     * @param string $filename
     * @param array $files
     * @param null $localnameStr
     *
     * @return bool
     */
    protected function makeArchive(string $filename, array $files, $localnameStr = null) {

        self::showInfo("Создание архива $filename");

        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);


        foreach ($files as $file) {
            $arhFilename = is_array($file) ? $file['path'] : $file;

            $localname = trim($arhFilename, "/");

            $zip->addFile(ROOT . $arhFilename, $localnameStr ?? $localname);
            $zip->setCompressionName(ROOT . $arhFilename, ZipArchive::CM_LZMA);
        }

        $result = $zip->close();

        if ($result) {
            self::showInfo("Архив $filename создан!", 0);
        } else {
            self::showInfo("Архив $filename не создан!", 0);
        }

        return $result;
    }


    public static function getDate() {
        return date("d-m-Y_H-i-s", STARTTIME);
    }

    public static function getLogAsString($separator = "<br>") {
        $str = "";
        foreach (self::$showInfoLog as $log) {
            $str .= $log['message'].$separator;
        }

        return $str;
    }

    public static function getDomainName($domain = null, $atTheEndsSymbol = "_") {
        if (!$domain) {
            $domain = System::getSetting()['script_url'];
        }

        $matches = [];
        preg_match('/[\w-]+(?=(?:\.\w{2,6}){1,2}(?:\/|$))/', $domain, $matches);

        return $atTheEndsSymbol.$matches[0].$atTheEndsSymbol ?? "";
    }

}