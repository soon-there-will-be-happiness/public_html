<?php


namespace Migrations;

use cmd\colors;
use Exception;
use Migrations\interfaces\migrateHandler;

class   migrationHandler implements migrateHandler {

    /** @var string Директория до миграций */
    private $migrationDir = ROOT."/db/migrations/";

    /** @var string Выбранная версия */
    private $needVersion;

    /** @var mixed|string Тип задания */
    private $task;

    /** @var mixed|string Откуда вызов (веб/cmd) */
    private $callFrom;

    /** @var array Ошибки при выполнении миграций */
    public $errors = [];

    /** @var array Массив миграций */
    private $migrations = [];

    /** @var array Массив выполненных миграций */
    private $migrationsStatuses = [];


    /**
     * migrationHandler constructor.
     *
     * @param $needVersion - требуемая версия(пример: 380)
     * @param string $task - задание на выполнение(showList, migrate, rollback, rollbackShowList, fresh, refresh)
     * @param string $callfrom - откуда вызов(cmd/web)
     * @param false $file - конкретный файл миграции(пример: 380/_1_migration)
     */
    public function __construct($needVersion, $task = "migrate", $callfrom = "cmd", $file = false) {
        $this->needVersion = $needVersion;
        $this->task = $task;
        $this->callFrom = $callfrom;
        $this->migrations = $this->getMigrations($this->needVersion, $file);
    }

    /**
     * Получить список миграций
     * Возвращает массив миграций на выполнение
     *
     * @param $needVersion
     * @param false $file
     *
     * @return array|array[]|void
     */
    function getMigrations($needVersion, $file = false)
    {
        if (defined("CURR_VER")) {//Версия проекта из константы
            $project_version_const = preg_replace('/[^0-9]/', '', CURR_VER);
            @mkdir($this->migrationDir.$project_version_const);
        }

        //Если один файл - сформировать список из него
        if ($file) {
            if (!is_file($this->migrationDir . $file)) {
                return $this->errorCallback("Файл не найден!");
            }
            $file = explode("/", $file);
            return [$file[0] => [0 => $file[1]]];
        }

        //Получаем список версий
        $versions = scandir($this->migrationDir);
        $versions = array_slice($versions, 2);


        //Папки для поиска миграций(до нужной версии включительно)
        $migrationsDirs = [];
        if ($needVersion != "all") {
            $needVersion = intval($needVersion);

            if ($needVersion == 0 || $needVersion == 1) {
                $this->errorCallback("Неправильная версия");
            }

            if ($this->task == "rollback" || $this->task == "rollbackShowList") {//Если задание - откат миграций
                foreach ($versions as $key => $version) {
                    if ($version < $needVersion) {
                        unset($versions[$key]);
                    }
                }
            } else {
                foreach ($versions as $key => $version) {
                    if ($version > $needVersion) {
                        unset($versions[$key]);
                    }
                }
            }
        }
        $migrationsDirs = $versions;

        //Получаем массив вида ["версия" => ["файл1", "файл2"],...]
        $migrationsFiles = [];
        foreach ($migrationsDirs as $dir) {
            if (!is_dir($this->migrationDir . $dir)) {
                continue;
            }
            $files = scandir($this->migrationDir . $dir);
            $files = array_slice($files, 2);
            $migrationsFiles[$dir] = $files;
        }

        return $migrationsFiles;
    }

    /**
     * Возвращает массив миграций на выполнение
     * Если из консоли - вывод
     *
     * @return array|array[]|void
     */
    private function returnMigrationsList()
    {
        if ($this->callFrom != "cmd") {
            return $this->migrations;
        }

        $text = 'Список миграций';
        $text .= $this->needVersion != "all" ? " до версии $this->needVersion:" : ":";
        echo colors::colors()->colorize("\n" . $text, 'yellow') . "\n";

        foreach ($this->migrations as $key => $migrations) {
            echo colors::colors()->colorize($key, 'light_green') . "\n";
            if (!isset($migrations[0])) {
                echo "    Нет миграций для этой версии \n";
                continue;
            }
            foreach ($migrations as $migration) {
                echo "    " . $migration . "\n";
            }

        }
    }


    /**
     * Запуск задания миграций
     *
     * @return array|array[]|void
     */
    public function runTasks() {
        switch ($this->task) {

            //Показать список миграций
            case "showList":
                return $this->returnMigrationsList();
                break;

            //Выполнить не установленные миграции
            case "migrate":
                return $this->runMigrations();
                break;
            //Откат миграций
            case "rollback":
                return $this->runMigrations(true);
                break;
            case "rollbackShowList":
                return $this->returnMigrationsList();
                break;

            //Откат бд к состоянию 3.7
            case "fresh":
                return $this->fresh();
                break;

            //Откат бд к состоянию 3.7 и запуск миграций
            case "refresh":
                $this->fresh();
                $this->runMigrations();
                break;

            case "check":
                return $this->checkDb();
                break;

            case "dumpMigrations":
                return $this->dumpMigrations();
                break;

            default:
                $this->errors[] = $this->errorCallback("Нет задания");
                break;
        }
        return $this->errors[] = $this->errorCallback("Нет задания");
    }


    /**
     * Запустить миграции
     *
     * @param false $rollback - если true, то выполнятся методы down() у миграций
     *
     * @return array
     */
    private function runMigrations($rollback = false) {

        try {
            MigrationsTable::createTableIfNotExists();
        } catch (Exception $e) {
            die("\n".$e->getMessage()."\n");
        }

        $completedMigrationsBefore = MigrationsTable::getCompleted();

        $success = 0;
        $before = 0;
        $notSuccess = 0;
        foreach ($this->migrations as $version => $migrations) {

            foreach ($migrations as $migration) {
                $migrationName = substr($migration, 0, -4);
                if (isset($completedMigrationsBefore[$version]) && in_array($migrationName, $completedMigrationsBefore[$version]) && in_array($version, array_keys($completedMigrationsBefore)) && $rollback == false) {
                    $before++;
                    continue;
                }

                $result = $this->runMigration($migration, $version, $rollback);
                if ($result) {
                    $success += 1;
                } else {
                    $notSuccess += 1;
                }
            }
        }
        return $this->resultHandlerMigrate($success, $notSuccess, $before);
    }

    /**
     * Запустить экшн миграции
     *
     * @param $migration - имя миграции
     * @param $version - версия(папка где находится)
     * @param bool $rollback - запустить down() или up() метод миграции
     *
     * @return bool
     */
    function runMigration($migration, $version, $rollback) {
        $start = microtime(true);

        //Создание объекта миграции
        require_once $this->migrationDir . $version . "/" . $migration;
        $objectName = substr($migration, 0, -4);
        $object = new $objectName;

        //Запуск действий миграции
        try {
            $rollback ? $object->down() : $object->up(); //Если откат - выполнить метод down()
            $result = true;
        } catch (\PDOException $PDOException) {//Парсим сообщение ошибки, если дубль - то игнорировать

            $pdoError = $PDOException->getMessage();

            $hasError = true;
            if (strpos($pdoError, "Column already exists") !== false) {//Дублированное имя столбца «%s»-
                $hasError = false;
            } elseif (strpos($pdoError, "Duplicate entry") !== false) {//Дублированная запись «%s» для ключа %d
                $hasError = false;
            } elseif (strpos($pdoError, "has duplicated value") !== false) {//Столбец f%sf содержит дублированное значение «%s» в %s
                $hasError = false;
            } elseif (strpos($pdoError, "Duplicate entry") !== false) {
                $hasError = false;
            } else {
                $hasError = true;
            }


            if ($hasError) {
                \Log::file("migrations", "Миграция $migration", ["errorText" => $PDOException->getMessage()], 4);
                $errorText = $PDOException->getMessage();
                $result = false;
            } else {
                $result = true;
            }

        } catch (Exception $e) {
            $errorText = $e->getMessage();
            $result = false;
        }

        $this->migrationResultHandler($version, $objectName, $rollback, $start, $result, $errorText ?? null);

        return $result;
    }

    /**
     * Обработчик результата после выполнения каждой миграции
     * Записывает данные о выполненной миграции
     * Если вызов из консоли - выводит сообщения о результатах выполнения миграции и выводит ошибки
     *
     * @param $version
     * @param $migrationName
     * @param $isRollback
     * @param $startTime
     * @param bool $status
     * @param null $exceptionMess
     *
     * @return array
     */
    private function migrationResultHandler($version, $migrationName, $isRollback, $startTime, $status = true, $exceptionMess = null) {
        $method = $isRollback ? "down()" : "up()";
        $this->migrationsStatuses[$migrationName] = [
            "version" => $version,
            "isRollback" => $isRollback,
            "time" => round(microtime(true) - $startTime, 4),
            "status" => $status,
            "exceptionMess" => $exceptionMess,
            "method" => $method,
        ];

        if ($status) {
            if ($isRollback) {
                MigrationsTable::removeMigrateRow($migrationName, $version);
            } else {
                MigrationsTable::addMigrateRow($migrationName, $version);
            }
        }

        if ($this->callFrom != "cmd") {
            return $this->migrationsStatuses[$migrationName];
        }

        echo colors::colors()->colorize($version, 'gray') . " - ";
        echo colors::colors()->colorize($migrationName."->".$method, 'light_green');
        if (!$status && $exceptionMess) {
            echo " - " . colors::colors()->colorize($exceptionMess, 'red');
        }
        echo colors::colors()->colorize(' (' . round(microtime(true) - $startTime, 4) . 's)', 'green');
        echo "\n";

        return $this->migrationsStatuses[$migrationName];
    }

    /**
     * Обработчик результата после выполнения всех миграций
     * Возвращает кол-во выполненных миграций
     *
     * @param $success
     * @param $notSuccess
     * @param $before
     * @param null $errors
     *
     * @return array
     */
    public function resultHandlerMigrate($success, $notSuccess, $before, $errors = null)
    {
        $result_array = [
            "success" => $success ?? 0,
            "notSuccess" => $notSuccess ?? 0,
            "before" => $before ?? 0,
            "errors" => $errors ?? 0,
            "executedMigrations" => $this->migrationsStatuses,
        ];

        if ($this->callFrom != "cmd") {
            return $result_array;
        }

        echo colors::colors()->colorize("Результат", 'blue') . "\n";
        echo colors::colors()->colorize("   Успешно: " . $result_array['success'], 'light_green') . "\n";
        echo colors::colors()->colorize("   Не успешно: " . $result_array['notSuccess'], 'red') . "\n";
        echo colors::colors()->colorize("   Было выполнено до этого: " . $result_array['before'], 'yellow') . "\n";

        return $result_array;
    }

    /**
     * Откат БД (Только для консоли)
     * Удалить все таблицы из бд, установить чистый дамп. Вернуть часть настроек
     */
    private function fresh() {
        if ($this->callFrom != "cmd") {
            die("Это задание можно выполнить только из консоли!");
        }

        $result = readline("Это действие ОТКАТИТ бд к состоянию 3.7.6 и УДАЛИТ все данные. Продолжить? Y/N? ");
        if ($result != "Y") {
            die();
        }

        //Запоминаем старые настройки бд
        $settings = \System::getSetting();
        $script_url = $settings['script_url'];
        $license = $settings['license_key'];

        //Подготавливаем дамп
        $dump = file_get_contents($this->migrationDir."0.sql");
        $dump = strtr($dump, ["[PREFIX]" => PREFICS ?? die("Нет префикса")]);
        $paramPath = ROOT . '/config/config.php';
        $params = include($paramPath);

        //Получаем список таблиц из бд
        $tables = \Db::getConnection()->query("SHOW TABLES FROM ".$dbname)->fetchAll(\PDO::FETCH_ASSOC);
        $tablesList = [];
        foreach ($tables as $table) {
            $tablesList[] = array_values($table)[0];
        }
        $tablesList = implode(", ", $tablesList);

        //Удаляем все таблицы
        $drop = \Db::getConnection()->prepare("DROP TABLE IF EXISTS ".$tablesList)->execute();
        echo("Таблицы удалены: ".($drop ? 'ok' : "x")."\n");

        //Устанавливаем дамп
        $dumpInstall = \Db::getConnection()->prepare($dump)->execute();
        echo("Дамп установлен: ".($dumpInstall ? 'ok' : "x")."\n");

        //Вернуть настройки
        $settingsReturn = \Db::getConnection()->prepare("UPDATE `".PREFICS."settings` SET `script_url` = '$script_url', `license_key` = '$license' WHERE `setting_id` = 1")->execute();
        echo("Настройки восстановлены: ".($settingsReturn ? 'ok' : "x")."\n");
        echo("\n ДАННЫЕ ВХОДА В АДМИНКУ: admin:admin \n");
    }

    /**
     * Колбек ошибки
     *
     * @param $errortext
     * @param int $type = 0 завершить процесс; 1 - продолжить далее
     */
    private function errorCallback($errortext, $type = 0) {
        $this->errors[] = $errortext;
        if ($this->callFrom == "cmd") {
            echo $errortext;
        }

        if ($type == 0) {
            die();
        }
    }


    /**
     * Проверка версии проекта и версии бд
     *
     * @return array
     * @throws Exception
     */
    function checkDb()
    {
        MigrationsTable::createTableIfNotExists();
        $completedMigrations = MigrationsTable::getCompleted();

        $dbVersion = array_keys($completedMigrations);
        $dbVersion = empty($dbVersion) ? 0 : max($dbVersion);//Версия проекта следуя выполненным миграциям

        $this->migrations = $this->getMigrations("all");

        $versionWithoutMigrations = [];
        foreach ($this->migrations as $key => $migration) {
            if (empty($migration)) {
                $versionWithoutMigrations[] = $key;
            }
        }

        $project_versions = array_keys($this->migrations);

        if (empty($project_versions)) {
            $project_version = 390;
        } else {
            $project_version = max($project_versions);//Версия проекта следуя файлам
        }

        if (defined("CURR_VER")) {//Версия проекта из константы
            $project_version_const = preg_replace('/[^0-9]/', '', CURR_VER);
        }

        if ($project_version_const) {
            $project_version = $project_version_const ?? $this->needVersion != "all" ? $this->needVersion : $project_version;
        }

        $status = false;
        //Если версия бд соответствует файлам, то проверить все ли миграции этой версии выполнены
        if ($dbVersion == $project_version) {

            $mig = $this->getMigrations($project_version);

            $noExecuted = [];
            foreach ($mig as $version => $migrations) {
                foreach ($migrations as $migration) {

                    $migrationName = substr($migration, 0, -4);
                    if (isset($completedMigrations[$version]) && in_array($migrationName, $completedMigrations[$version]) && in_array($version, array_keys($completedMigrations))) {
                        continue;
                    }
                    $noExecuted[] = ["version" => $version, "migration" => $migrationName];

                }
            }

            if (empty($noExecuted)) {
                $result = "Версия бд ($dbVersion) соответствует версии проекта ($project_version)!";
                $status = "ok";

            } else {
                $count = count($noExecuted);
                $status = "needRunMigrations";
                $result = "Версия бд ($dbVersion) отчасти соответствует версии проекта ($project_version)! Не выполнено миграций: $count. Нужно выполнить миграции для обновления бд!";
            }
        }

        if ($dbVersion > $project_version) {
            $status = "upper";
            $result = "Версия бд ($dbVersion) не соответствует версии проекта ($project_version)! В базе данных были выполнены миграции, которых нету в текущей версии проекта";
        }

        if ($dbVersion < $project_version) {

            $dbVersionTmp = $dbVersion + 1;
            $project_versionTmp = $project_version;
            $needRunMigrations = false;

            for (; $dbVersionTmp <= $project_versionTmp; $dbVersionTmp++) {
                if (!in_array($dbVersionTmp, $versionWithoutMigrations)) {
                    $needRunMigrations = true;
                }
            }

            if ($needRunMigrations) {
                $status = "needRunMigrations";
                $result = "Версия бд ($dbVersion) не соответствует версии проекта ($project_version)! Нужно выполнить миграции для обновления бд";
            } else {
                $dbVersion = --$dbVersionTmp;
                $result = "Версия бд - ($dbVersionTmp) соответствует версии проекта ($project_versionTmp)!";
                $status = "ok";
            }
        }

        if ($dbVersion == 0) {
            $status = "needRunMigrations";
            $dbVersion = "???";
            $result = "Необходимо выполнить миграции!";
        }

        $checked = [
            "message" => $result,
            "dbVersion" => $dbVersion,
            "projectVersion" => $project_version,
            "status" => $status,
        ];

        if ($this->callFrom == "cmd") {
            echo $checked['message']."\n";
        }

        return $checked;
    }

    public static function checkDbConvertStatus($status) {
        switch ($status) {
            case "needRunMigrations":
                return "Необходимо выполнить миграции!";
            case "upper":
                return "В базе данных были выполнены миграции, которых нету в текущей версии проекта!";
            case "ok":
                return "База данных актуальна";
                break;
            default:
                return "???";
                break;
        }
    }

    private function dumpMigrations() {

        if (!key_exists($this->needVersion,$this->migrations)) {
            die("Нет такой версии");
        }

        if (!isset($this->migrations[$this->needVersion][0])) {
            die("Нет файлов миграций в этой версии");
        }

        Schema::$dumping = true;
        foreach ($this->migrations[$this->needVersion] as $key => $migration) {

            $migrationName = substr($migration, 0, -4);
            Schema::$migrationName = $migrationName;

            require_once $this->migrationDir . $this->needVersion . "/" . $migration;

            $object = new $migrationName;
            $object->up();
        }

        $dumpArr = Schema::$dump;
        Schema::$dumping = false;

        $file = "";
        foreach ($dumpArr as $migrationSqls) {
            foreach ($migrationSqls as $oneSql) {
                $file .= $oneSql."\n";
            }
            $file .= "\n";
        }

        $fileName = dirname($this->migrationDir)."/".$this->needVersion.".sql";

        file_put_contents($fileName, $file);

        return $fileName;
    }
}