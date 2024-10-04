<?php


trait checksettings {

    public static $settings;

    private static $successMessage = "Все в порядке)";

    private static function checkAdminEmail() {

        $email = self::$settings['admin_email'];
        //$email = 11;
        $result = System::isEmail($email);

        return [
            "name" => "Наличие почты админа",
            "status" => $result,
            "message" => $result ? self::$successMessage : "Не правильно указан E-mail админа",
        ];
    }

    private static function checkCronWork() {
        $crons = [];
        $crons['order_cron'] = System::getCronLog('order_cron');
        $crons['cond_cron'] = System::getCronLog('cond_cron');
        $crons['installment_cron'] = System::getCronLog('installment_cron');
        $crons['course_cron'] = System::getCronLog('course_cron');
        $crons['training_cron'] = System::getCronLog('training_cron');
        $crons['flow_cron'] = System::getCronLog('flow_cron');
        $crons['email_cron'] = System::getCronLog('email_cron');
        $checktime = time() - 60*60*25;

        $message = "";
        $status = true;
        foreach ($crons as $key => $cron) {
            if ($cron['last_run'] < $checktime) {
                $status = false;
                $message .= "$key давно не запускался<br>";
            }
        }

        return [
            "name" => "Статус работ крона",
            "status" => $status,
            "message" => $status ? self::$successMessage : $message,
        ];
    }

    private static function checkMailSettings() {
        $status = false;
        $message = "";
    
        if (self::$settings['use_smtp'] == 0) $message .= 'Используется устаревший PHP Mail, переключите на SwiftMailer<br>';
        if (empty(self::$settings['support_email'])) $message .= 'Не указан email техподдержки<br>';
        if (empty(self::$settings['sender_email'])) $message .= 'Не указан email отправителя<br>';
        if (empty(self::$settings['sender_name'])) $message .= 'Не указано имя отправителя<br>';
        if (empty(self::$settings['smtp_host'])) $message .= 'Не указан smtp хост<br>';
        if (empty(self::$settings['smtp_port'])) $message .= 'Не указан smtp порт<br>';
        if (empty(self::$settings['smtp_user'])) $message .= 'Не указан smtp пользователь<br>';

        if ($message == "") {
            $status = true;
        }

        return [
            "name" => "Настройки почты",
            "status" => $status,
            "message" => $status ? self::$successMessage : $message,
        ];
    }

    private static function httpResponseCode() {
        $curl = System::curl(self::$settings['script_url']);

        $status = false;
        $message = "Код HTTP ответа сервера должен быть 200";
        if ($curl) {
            if ($curl['info']['http_code'] == 200) {
                $status = true;
            }
        }


        return [
            "name" => "Ответ сервера",
            "status" => $status,
            "message" => $status ? self::$successMessage : $message,
        ];
    }

    private static function maxFileUpload() {
        $size = ini_get('upload_max_filesize');

        return [
            "name" => "Макс.объем загружаемого файла",
            "status" => true,
            "message" => "Значение: $size",
        ];
    }

    private static function executionTime() {
        $time = ini_get('max_execution_time');

        return [
            "name" => "Время выполнения скрипта",
            "status" => true,
            "message" => "Значение: $time",
        ];
    }

    private static function checkPerms() {

        $dirs_to_check = [
            "/tmp", "/load", "/images"
        ];

        $dirs = [];
        foreach ($dirs_to_check as $checkDir) {//Получаем вложенные папки
            $finded = System::get_dir_files(ROOT.$checkDir, true, true, true);
            foreach ($finded as $find) {
                $dirs[] = $find;
            }

        }

        $perms = [];
        foreach ($dirs as $dir) {//Проверяем права для каждой папки
            $path = $dir;
            $perm = substr(sprintf('%o', fileperms($path)), -4);
            if ($perm != "0755" || $perm != "755") {
                $key = str_replace(ROOT, '', $path);
                $perms[$key] = $perm;
            }
        }

        $message = "";
        $status = true;
        if (!empty($perms)) {
            $message = "Папки, у которых права не 755: ";
            $implode = implode("<br>", array_keys($perms));
            $message .= $implode;
            $status = false;
        }

        return [
            "name" => "Права на папки",
            "status" => $status,
            "message" => $status ? self::$successMessage : $message,
        ];

    }

    private static function checkFreeSpace() {
        $dir = dirname(ROOT, 30);

        $freeSpace = disk_free_space($dir) ?? 0;//получить значение в байтах

        $freeSpace = round($freeSpace / 1024 / 1024); // в мб

        if ($freeSpace > 1000) {
            $status = true;
            $mess = "Свободного места достаточно: $freeSpace мб";
        } else {
            $status = false;
            $mess = "Свободного места недостаточно: $freeSpace мб";
        }

        return [
            "name" => "Свободное место",
            "status" => $status,
            "message" => $mess,
        ];
    }

    private static function checkInstallmentReminders() {
        $instalment_list = Product::getInstalments();
        if (!$instalment_list) {
            return [
                "name" => "Напоминания в рассрочках",
                "status" => true,
                "message" => "Нет рассрочек",
            ];
        }
        $message = "";
        $status = true;
        foreach ($instalment_list as $instalment) {
            $notif = unserialize(base64_decode($instalment['notif']));
            if (is_string($notif)) {
                $status = false;
                $message .= "У рассрочки ".$instalment['id']." не включены напоминания!<br>";
            } elseif ($notif['send_1_time'] == "" || $notif['send_1_email'] == 0) {
                $status = false;
                $message .= "У рассрочки ".$instalment['id']." не включены напоминания!<br>";
            }
        }

        if ($status) {
            $message = self::$successMessage;
        }

        return [
            "name" => "Напоминания в рассрочках",
            "status" => $status,
            "message" => $message,
        ];
    }


    private static function checkSubsNotif() {
        $subs = Member::getPlanes();
        $message = "";
        $status = true;

        foreach ($subs as $sub) {
            if ($sub['letter_1_time'] == 0 || $sub['letter_1'] == "") {
                $status = false;
                $message .= "У плана ".$sub['id']." не включены напоминания!<br>";
            }
        }

        if ($status) {
            $message = self::$successMessage;
        }

        return [
            "name" => "Уведомления в планах подписки",
            "status" => $status,
            "message" => $message,
        ];
    }

    private static function checkSubsPeriod() {
        $subs = Member::getPlanes();
        $message = "";
        $status = true;

        foreach ($subs as $sub) {
            if ($sub['lifetime'] == 0) {
                $status = false;
                $message .= "У плана ".$sub['id']." период 0!<br>";
            }
        }

        if ($status) {
            $message = self::$successMessage;
        }

        return [
            "name" => "Периоды в планах подписки",
            "status" => $status,
            "message" => $message,
        ];
    }

    private static function checkTrainingEndEvent() {
        $trainings = Training::getTrainingList();
        $message = "";
        $status = true;

        foreach ($trainings as $training) {
            if ($training['finish_type'] != 6 && $training['finish_lessons'] == null) {
                $status = false;
                $message .= "У тренинга ".$training['training_id']." не указан финишный урок!<br>";
            }
        }

        if ($status) {
            $message = self::$successMessage;
        }

        return [
            "name" => "Проверка событий в тренинге",
            "status" => $status,
            "message" => $message,
        ];
    }

    private static function checkHttpToHttpsRedirect() {
        
        $setting = System::getSetting();
        $status = true;
        $current = $_SERVER['REQUEST_SCHEME'];
        if($current == 'http'){   
            $script = explode(":", $setting['script_url']);
            if($script[0] == 'https'){
                $message = "Нет перенаправления на https!<br /><a target='_blank' href='https://support.school-master.ru/knowledge_base/item/289559'>Что значит?</a>";
                $status = false;
            } else {
                $message = "Рекомендуется включить https для сайта";
                $status = false;
            }
        }
        
        return [
            "name" => "Работа по https",
            "status" => $status,
            "message" => $status ? self::$successMessage : $message,
        ];
        
    }

    private static function checkMassResponder() {
        $time = time();

        $straggler = Responder::searchStragglerTask($time);
        $cron = System::getCronLog('email_cron');

        $status = true;
        $message = "";

        if ($cron['jobs_error']) {
            $status = false;
            $message = "Есть ошибки в работе крона рассылок<br>";
        }

        return [
            "name" => "Массовые рассылки",
            "status" => $status,
            "message" => $status ? self::$successMessage : $message,
        ];
    }

    private static function checkAffCookie() {
        $enable = System::getExtensionStatus('partnership');
        $status = true;
        $message = self::$successMessage;
        if ($enable) {
            $params = unserialize(System::getExtensionSetting('partnership'));

            if ($params['params']['aff_life'] == "") {
                $status = false;
                $message = 'Поле "Время учёта партнёра и жизни куки, дни" пустое';
            }
        } else {
            $message = "Расширение партнерки выключено";
        }

        return [
            "name" => "Партнерка",
            "status" => $status,
            "message" => $message,
        ];
    }

    private static function checkIntlextInPhp() {
        $status = extension_loaded("intl");
        return [
            "name" => "Расширение Intl",
            "status" => $status,
            "message" => $status ? self::$successMessage : "Расширение php Intl не установлено",
        ];
    }


    private static function checkExecFunction() {
        $status = function_exists("exec");
        return [
            "name" => "Функция exec",
            "status" => $status,
            "message" => $status ? self::$successMessage : "Функция exec() отключена",
        ];
    }
}