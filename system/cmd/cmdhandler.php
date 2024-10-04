<?php

namespace cmd;

use Migrations\createmigration;
use testSrc\TestHandler;

class cmdHandler
{
    private $colors;
    private $args;

    public function __construct($argv) {
        require_once (ROOT.'/system/cmd/colors.php');
        colors::colors();
        $this->args = $argv;
    }

    public function execute() {
        $arg = $this->args[1];
        switch ($arg) {
            case 'test':
                require_once (ROOT.'/tests/testSrc/TestHandler.php');
                $this->executeTest();
                break;
            case 'migrate':
                \MigrationCmd::execute($this->args);
                break;
            case 'migrate:rollback':
                \MigrationCmd::execute($this->args, true, false);
                break;
            case 'migrate:fresh':
                \MigrationCmd::execute($this->args, true);
                break;
            case "migrate:check":
                \MigrationCmd::execute($this->args, false, false);
                break;
            case "make:migration":
                \MigrationCmd::executeMake($this->args);
                break;
            case "migrate:dump":
                \MigrationCmd::executeDumpMigrations($this->args);
                break;
            default:
                require_once (ROOT.'/tests/testSrc/TestHandler.php');
                $this->executeHelp();
                break;
        }

    }

    /**
     * Запустить тесты
     */
    public function executeTest() {
        //TODO Загрузка файлов теста
        require_once(ROOT.'/tests/testSrc/CreateTest.php');
        require_once(ROOT.'/tests/testSrc/TestCase.php');
        require_once(ROOT.'/tests/testSrc/TestHandler.php');

        //TODO создать объект теста
        $test = new TestHandler($this->args);

        //TODO передать управление ему
        $test->handle();
    }

    /**
     * Вывести справку
     */
    public function executeHelp() {
        echo colors::colors()->colorize('Доступные команды:', 'green')."\n";

        $commands['test'] = TestHandler::getHelp();
        $commands['migrate'] = \MigrationCmd::getHelp();

        foreach ($commands as $key => $commandgroup) {

            echo colors::colors()->colorize($key, 'white')."\n";

            foreach ($commandgroup as $cmd => $commanddesc) {
                echo colors::colors()->colorize('  '.$cmd, 'red');
                echo colors::colors()->colorize(' - '.$commanddesc, 'white')."\n";
            }

        }
    }


}