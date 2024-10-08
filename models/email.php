<?php defined('BILLINGMASTER') or die;

class Email {

    public static $msg_cases = [
        'sendLetterAboutExpireSubscription' => '[Клиенту] Скоро закончится подписка',
        'SendExpirationMessageByClient'     => '[Клиенту] Подписка закончилась',
        'SendClientNotifAboutOrder'         => '[Клиенту] Напоминания об оплате заказа',
        'SendClientNotifAboutInstallment'   => '[Клиенту] Напоминание о рассрочке',
        'SendConfirmDelivery'               => '[Клиенту] Подтверждение доставки',
        'SendUserNotifAboutTaskAnswer'      => '[Клиенту] Новое сообщение от куратора',
        'SendEmailFromCuratorToUser'        => '[Клиенту] Новый ответ на задание',

        'SendAnswerFromUserToCurator'       => '[Куратору] Комментарий, ответ на ДЗ',

        'SendCheckTaskToAdmin'              => '[Автору] Проверено задание урока',

        'SendPernerLetter'                  => '[Партнеру] Регистрация нового партнера',

        'SendOrder'                         => '[Админу] Новый оплаченный заказ',
        'SendOrderToAdmin'                  => '[Админу] о новом еще заказе',
        'sendMessageAccountStatement'       => '[Админу] О выписке счета',
        'AdminCompanyOrder'                 => '[Админу] Выставление счёта юр.лицу',
        'SendNotifAboutPartnerToAdmin'      => '[Админу] Регистрация нового партнера',
        'AdminDeliveryConfirm'              => '[Админу] Нужно подтвердить доставку',
        'AdminPincodeNotification'          => '[Админу] Заканчиваются пин коды',
        'AdminNotification'                 => '[Админу] Апселл не найден',
        'SendPartnerTransaction'            => '[Партнеру] Новый заказ',
    ];

    public static function getMsgCases() {
        $cases = self::$msg_cases;

        $pomopatorFile = ROOT."/lib/aff_curator.php";
        if (is_file($pomopatorFile)) {
            $cases["sendMessageToPartnerFromPomogator"] = '[Партнеру] Новый заказ в скрипте помогаторе';
            $cases["sendMessageToPartnerFromPomogatorProlong"] = '[Партнеру] О продлении';
            $cases["sendMessageToPartnerFromPomogatorExpire"] = '[Партнеру] Об окончании подписки';
        }

        return $cases;
    }

    // ШАБЛОН ЧИСТЫЙ
    public static function SendMessageToBlank($email, $name, $subject, $text, $sender_name = null, $is_testLetter = false, $reply_to = false, $addit_data = [])
    {
        $setting = System::getSetting();
        
        if(!$sender_name || empty(trim($sender_name)) || !is_string($sender_name) || $sender_name == "none")
            $sender_name = $setting['sender_name'];
            $sender_name = html_entity_decode($sender_name);

        $send = self::sender($email, $subject, $text, $setting, $sender_name, $setting['sender_email'], $is_testLetter, $reply_to, $addit_data);

        return $send ? true : false;
    }


    // ОТПРАВКА ПИЬСМА О ЗАВЕРШЕНИИ ПОДПИСКИ
    public static function sendLetterAboutExpireSubscription($email, $subj_manager, $letter, $user_id)
    {
        $setting = System::getSetting();
        $user = User::getUserById($user_id);

        $replace = array(
            '[NAME]' => $user['user_name'],
            '[CLIENT_NAME]' => $user['user_name'],
            '[SURNAME]' => $user['surname'],
            '[EMAIL]' => $user['email'],
            '[NICK_TG]' => $user['nick_telegram'],
            '[NICK_IG]' => $user['nick_instagram'],
        );

        $text = strtr($letter, $replace);

        return self::sender($email, $subj_manager, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }
    
    
    // СПИСОК ID предстоящих массовых рассылок - 24 часа
    public static function getActiveMassMailIDs($time)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT delivery_id FROM ".PREFICS."email_delivery WHERE type = 1 AND send_time > $time");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
        	$data[] = $row['delivery_id'];
        }
        
        return !empty($data) ? $data : false;
    }



    // ОТПРАВКА КАСТОМНОГО ПИСЬМА МЕНЕДЖЕРУ
    public static function sendCustomLetterForManager($email, $subj_manager, $letter, $order)
    {
        $setting = System::getSetting();
        $surname = null;
        $nick_telegram = null;
        $nick_instagram = null;

        if ($order['order_info'] != null) {
            $order_info = unserialize(base64_decode($order['order_info']));
            if (isset($order_info['surname'])) {
                $surname = $order_info['surname'];
            }

            if (isset($order_info['nick_telegram'])) {
                $nick_telegram = $order_info['nick_telegram'];
            }

            if (isset($order_info['nick_instagram'])) {
                $nick_instagram = $order_info['nick_instagram'];
            }
        }

        $replace = array(
            '[ORDER]' => $order['order_date'],
            '[DATE]' => date("d-m-Y H:i:s", $order['order_date']),
            '[NAME]' => $order['client_name'],
            '[CLIENT_NAME]' => $order['client_name'],
            '[SURNAME]' => $surname,
            '[EMAIL]' => $order['client_email'],
            '[SUMM]' => $order['summ'],
            '[NICK_TG]' => $nick_telegram,
            '[NICK_IG]' => $nick_instagram,
            '[CLIENT_PHONE]' => $order['client_phone'],
        );

        $text = strtr($letter, $replace);
        return self::sender($email, $subj_manager, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }



    // ОТПРАВКА ДОКУМЕНТА СТРОГОЙ ОТЧЁТНОСТИ
    public static function SendStrictReport($client_name, $client_email, $order_date, $payment_date, $summ, $setting, $order_items, $surname = null)
    {
        $ticket = unserialize(base64_decode($setting['org_data']));
        $admin_email = $setting['admin_email'];
        $subject = System::Lang('STRICT_REPORT_SUBJ');
        $order_items = '<table style="width:100%;max-width:100%;border-collapse:collapse;border-spacing:0;font-size:12px;text-align:center">
        <tr style="color: #737581;">
          <td style="padding: 8px 8px 8px 0; text-align: left; line-height: 1.42857143; vertical-align: top;">Наименование услуг</td>
          <td style="padding: 8px; line-height: 1.42857143; vertical-align: top;">Количество</td>
          <td style="padding: 8px 0 8px 8px; line-height: 1.42857143; vertical-align: top; text-align: right;">Стоимость (руб.)</td>
          <td style="padding: 8px 0 8px 8px; line-height: 1.42857143; vertical-align: top; text-align: right;">в т.ч. НДС</td>
        </tr>'.$order_items.'</table>';

        $letter = $ticket['text'];
        $date = date("d-m-Y H:i:s", $payment_date);

        $replace = array(
            '[DATE]' => $date,
            '[ORDER]' => $order_date,
            '[CLIENT_EMAIL]' => $client_email,
            '[EMAIL]' => $admin_email,
            '[SITE]' => $setting['script_url'],
            '[SUMM]' => $summ . $setting['currency'],
            '[FULL_NAME]' => $client_name.' '.$surname,
            '[NAME]' => $client_name,
            '[CLIENT_NAME]' => $client_name,
            '[ORG_NAME]' => $ticket['org_name'],
            '[INN]' => $ticket['inn'],
            '[YR_ADDRESS]' => $ticket['address'],
            '[OGRN]' => $ticket['ogrn'],
            '[PHONE]' => $ticket['phone'],
            '[ORDER_ITEMS]' => $order_items,
        );

        $text = strtr($letter, $replace);

        return self::sender($client_email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ОТПРАВКА ПИСЕМ НАПОМИНАНИЯ (ДОжимающие )
    public static function SendClientNotifAboutOrder($email, $name, $order_date, $subject, $letter, $link = null)
    {
        $setting = System::getSetting();

        if ($link == null) {
            $link = $setting['script_url'].'/pay/'.$order_date;
        }

        $replace = array(
            '[NAME]' => $name,
            '[CLIENT_NAME]' => $name,
            '[ORDER]' => $order_date,
            '[LINK]' => $link,
        );

        $text = strtr($letter, $replace);
        $subject = strtr($subject, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }



    // ОТПРАВКА ПИСЕМ НАПОМИНАНИЯ О ПЛАТЕЖЕ (РАССРОЧКА )
    public static function SendClientNotifAboutInstallment($email, $name, $order_date, $subject, $letter, $link = null)
    {
        $setting = System::getSetting();

        if ($link == null) {
            $link = '<a href="'.$setting['script_url'].'/pay/'.$order_date.'">Завершить заказ</a>';
        }

        $replace = array(
            '[CLIENT_NAME]' => $name,
            '[NAME]' => $name,
            '[ORDER]' => $order_date,
            '[LINK]' => $link,
        );

        $text = strtr($letter, $replace);
        $subject = strtr($subject, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ОТПРАВКА ПОДТВЕРЖДЕНИЯ ПОДПИСКИ
    public static function sendConfirmSubs($delivery_id, $email, $name, $subs_key)
    {
        $setting = System::getSetting();
        $delivery = Responder::getDeliveryData($delivery_id);
        $delivery_name = $delivery['name'];
        $subject = $delivery['confirm_subject'];
        $letter = $delivery['confirm_body'];
        $confirm_link = $setting['script_url'] . "/responder/confirm/$delivery_id?email=$email&key=$subs_key";

        // Реплейсим письмо
        $replace = array(
            '[NAME]' => $name,
            '[CLIENT_NAME]' => $name,
            '[DELIVERY]' => $delivery_name,
            '[EMAIL]' => $email,
            '[CONFIRM_LINK]' => $confirm_link,
        );

        $text = strtr($letter, $replace);
        $subject = strtr($subject, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    /**
     *     ФОРУМСКИЕ
     */


    /**
     * ОПОВЕЩЕНИЕ ТОПИКСТАРТЕРА О НОВОМ СООБЩЕНИИ В ТЕМЕ + ОПОВЕЩЕНИЕ ПОДПИСАННЫХ ЮЗЕРОВ
     * Принимает данные + параметр to. Если 1 - значит оповещение топикстартера, если 0 - то подписанных юзеров.
     * @param $email
     * @param $user_name
     * @param $user
     * @param $message
     * @param $alias
     * @param $topic_id
     * @param $to
     * @return bool
     */
    public static function SendEmailTopicstarterAboutNewMessage($email, $user_name, $user, $message, $alias, $topic_id, $to)
    {
        $setting = System::getSetting();
        $key = md5($setting['secret_key']);
        $user = User::getUserNameByID($user);
        $user = $user['user_name'];

        $topic = Forum::getTopicDataByID($topic_id);
        $link = $setting['script_url']."/forum/$alias/topic-$topic_id#answer";
        $unsub_link = $setting['script_url']."/forum/$alias/topic-$topic_id/unsubscribe?email=$email&key=$key";

        if ($to == 1) {
            $subject = System::Lang('TOPICSTARTER_NEW_MESS_SUBJ');
            $letter = System::Lang('TOPICSTARTER_NEW_MESS_MESS');
        } else {
            $subject = System::Lang('SUBS_USER_NEW_MESS_SUBJ');
            $letter = System::Lang('SUBS_USER_NEW_MESS_MESS');
        }

        // Реплейсим письмо
        $replace = array(
            '[LINK]' => $link,
            '[TOPICSTARTER]' => $user_name,
            '[TOPIC]' => $topic['topic_title'],
            '[USER]' => $user,
            '[UNSUBSCRIBE]' => $unsub_link,
        );

        $text = strtr($letter, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ОПОВЕЩЕНИЕ АДМИНА / ТЕХПОДДЕРЖКИ О НОВОМ СООБЩЕНИИ В ТЕМЕ
    public static function SendEmailAdminAboutNewMess($email, $user, $message, $alias, $topic_id, $mess_id, $status)
    {
        $setting = System::getSetting();
        $user = User::getUserNameByID($user);
        $user = $user['user_name'];

        $topic = Forum::getTopicDataByID($topic_id);

        if ($status == 0) {
            $link = $setting['script_url']."/forum/$alias/topic-$topic_id/mess-$mess_id/confirm?public=1&key=".md5($setting['secret_key']);
        } else {
            $link = $setting['script_url']."/forum/$alias/topic-$topic_id/#mess$mess_id";
        }

        $del_link = $setting['script_url']."/forum/$alias/topic-$topic_id/mess-$mess_id/confirm?public=0&key=".md5($setting['secret_key']);

        $subject = System::Lang('ADMIN_NEW_MESS_SUBJ');
        $letter = $status == 1 ? System::Lang('ADMIN_NEW_MESS_MESS') : System::Lang('ADMIN_NEW_MESS_MESS_LINK');

        // Реплейсим письмо
        $replace = array(
            '[LINK]' => $link,
            '[DEL_LINK]' => $del_link,
            '[TOPIC]' => $topic['topic_title'],
            '[USER]' => $user,
            '[TEXT]' => $message,
        );

        $text = strtr($letter, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ОПОВЕЩЕНИЕ АДМИНА / ТЕХПОДДЕРЖКИ О НОВОЙ ТЕМЕ НА ФОРУМЕ
    // Ghkexftn email куда отправить, название темы, алиас категории, статус и id юзера
    public static function SendEmailAboutNewTopic($email, $name, $cat, $status, $user, $topic_message, $topic_id)
    {
        $setting = System::getSetting();
        $user = User::getUserNameByID($user);
        $user = $user['user_name'];
        $link = $setting['script_url']."/forum/$cat/topic-$topic_id/confirm?public=1&key=".md5($setting['secret_key']);
        $del_link = $setting['script_url']."/forum/$cat/topic-$topic_id/confirm?public=0&key=".md5($setting['secret_key']);

        $subject = System::Lang('ADMIN_NEW_TOPIC_SUBJ');
        $letter = $status == 1 ? System::Lang('ADMIN_NEW_TOPIC_MESS') : System::Lang('ADMIN_NEW_TOPIC_MESS_LINK');

        // Реплейсим письмо
        $replace = array(
            '[LINK]' => $link,
            '[DEL_LINK]' => $del_link,
            '[USER]' => $user,
            '[TEXT]' => $topic_message,
        );

        $text = strtr($letter, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    /**
     *     МЕМБЕРШИП
     */


    /**
     * ПИСЬМО КЛИЕНТУ ОБ ОКОНЧАНИИ ПЛАНА ПОДПИСКИ
     * @param $email
     * @param $name
     * @param $subject
     * @param $letter
     * @param $link
     * @return bool
     */
    public static function SendExpirationMessageByClient($email, $name, $subject, $letter, $link)
    {
        $setting = System::getSetting();
        // Реплейсим письмо
        $replace = array(
            '[NAME]' => $name,
            '[CLIENT_NAME]' => $name,
            '[LINK]' => $link
        );

        $text = strtr($letter, $replace);

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }



    /**
     *   ОНЛАЙН УРОКИ
     */



    // ПИСЬМО УЧЕНИКУ О НОВОМ СООБЩЕНИИ К ЗАДАНИЮ ОТ КУРАТОРА/АДМИНА
    public static function SendUserNotifAboutTaskAnswer($email, $name, $course, $lesson)
    {
        // Получаем настройки
        $setting = System::getSetting();
        $link = $setting['script_url'].'/courses/'.$course .'/'.$lesson;

        // Реплейсим письмо
        $replace = array(
            '[NAME]' => $name,
            '[CLIENT_NAME]' => $name,
            '[LINK]' => $link
        );

        $letter = System::Lang('MESS_NOTIF_ABOUT_TASK_ANSWER');
        $text = strtr($letter, $replace);
        $subject = System::Lang('SUBJ_NOTIF_ABOUT_TASK_ANSWER');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    /**
     * ПИСЬМО АДМИНУ / АВТОРУ О ПРОВЕРКЕ ЗАДАНИЯ УРОКА
     * @param $user
     * @param $lesson_id
     * @param $answer
     * @param $curators
     * @return bool
     */
    public static function SendCheckTaskToAdmin($user, $lesson_id, $answer, $curators)
    {
        // Получаем настройки
        $setting = System::getSetting();
        $lesson = Course::getLessonDataByID($lesson_id);
        $course = Course::getCourseByID($lesson['course_id']);
        $course_name = $course['name'];
        $link = $setting['script_url'].'/courses/'.$course['alias'].'/'.$lesson['alias'];
        $link_admin = '<a href="'.$setting['script_url'].'/admin/answers?get=check">Проверить</a>';
        $user = User::getUserById($user);
        $user = $user['user_name'] .'('.$user['email'].')';

        $from = $setting['admin_email'];
        $from_name = $setting['sender_name'];

        $letter = System::Lang('ADMIN_LETTER_ABOUT_CHECK_TASK');
        $subject = System::Lang('ADMIN_SUBJ_ABOUT_CHECK_TASK');
        $send = false;

        // ЕСЛИ кураторы есть.
        if (!empty($curators)) {
            // Получить емейлы кураторов
            $arr = unserialize($curators);
            $cur_true = false;

            // Реплейсим письмо
            $replace = array(
                '[COURSE]' => $course_name,
                '[LESSON]' => $lesson['name'],
                '[USER]' => $user,
                '[LINK]' => $link
            );

            $text = strtr($letter, $replace);

            foreach($arr as $curator) {
                $user_curator = User::getUserById($curator);
                if ($user_curator) {
                    $cur_true = true;
                    $send = self::sender($user_curator['email'], $subject, $text, $setting, $from_name, $from);
                }
            }

            if (!$cur_true) { // Если ни одного куратора не существует, отправляем админу.
                // Реплейсим письмо
                $replace = array(
                    '[COURSE]' => $course_name,
                    '[LESSON]' => $lesson['name'],
                    '[USER]' => $user,
                    '[LINK]' => $link_admin
                );

                $text = strtr($letter, $replace);

                return self::sender($setting['admin_email'], $subject, $text, $setting, $from_name, $from);
            }
        } else { // ЕСЛИ кураторов нет, отправляем админу
            // Реплейсим письмо
            $replace = array(
                '[COURSE]' => $course_name,
                '[LESSON]' => $lesson['name'],
                '[USER]' => $user,
                '[LINK]' => $link_admin
            );

            $text = strtr($letter, $replace);
            $send = self::sender($setting['admin_email'], $subject, $text, $setting, $from_name, $from);
        }

        return $send;
    }


    /**
     * ПИСЬМО КУРАТОРУ или кураторам или АДМИНУ от ученика об ответе на ДЗ или комментарий
     * @param $user_id
     * @param $answer_homework_id
     * @param $answer
     * @param $lesson
     * @param $training
     * @return bool
     */
    public static function SendAnswerFromUserToCurator($user_id, $answer_homework_id, $answer, $lesson, $training) {
        
        $setting = System::getSetting();
        $user = User::getUserById($user_id);
        if (empty($answer_homework_id)) {
            $answer_homework = TrainingLesson::getHomeWork($user_id, $lesson['lesson_id']);
            $answer_homework_id = $answer_homework['homework_id'];
        }

        $training_name = $training['name'];
        $from = $setting['sender_email'];
        $from_name = $setting['sender_name'];
        $link_admin = $setting['script_url'].'/lk/curator/answers/'.$answer_homework_id.'/'.$user_id.'/'.$lesson['lesson_id'];
        $subject = $training['subject_letter_to_curator'];
        $letter = $training['letter_to_curator'];
        $curators_for_send = [];

        $assign_curator = Training::getCuratorToUserByLessonId($lesson['lesson_id'], $user_id);
        if (isset($assign_curator['curator_id'])) {
            $curators_for_send[] = $assign_curator['curator_id'];
        }

        // Реплейсим письмо
        $replace = array(
            '[LINK]' => $link_admin,
            '[LESSON]' => $lesson['name'],
            '[NAME]' => $user['user_name'],
            '[CLIENT_NAME]' => $user['user_name'],
            '[SURNAME]' => $user['surname'],
            '[EMAIL]' => $user['email'],
            '[TRAINING]' => $training_name,
        );
        $text = strtr($letter, $replace);

        if (!$training['send_email_to_all_curators']) {
            $text1 = str_replace('[CURATOR]', $assign_curator['user_name'] ?? "куратор", $text);
            if (strpos($text1, '[AUTH_LINK]') !== false) {
                $admin = User::getUserDataByEmail($setting['admin_email']);
                $auth_link = User::generateAutoLoginLink($admin); //Ссылка автологин без редиректа
                $text1 = str_replace('[AUTH_LINK]', $auth_link, $text1);
            }
            self::sender($setting['admin_email'], $subject, $text1, $setting, $from_name, $from);
        }

        if (!$curators_for_send && $curators = Training::getCuratorsTraining($training['training_id'])) {
            $curators_for_send = array_unique(array_merge($curators['datamaster'], $curators['datacurators']));
        }

        if ($curators_for_send) {
            foreach ($curators_for_send as $curator) {
                $user_curator = User::getUserById($curator);
                if ($user_curator) {
                    $text2 = str_replace('[CURATOR]', $user_curator['user_name'] ?? "куратор", $text);
                    if (strpos($text2, '[AUTH_LINK]') !== false) {
                        $auth_link = User::generateAutoLoginLink($user_curator); //Ссылка автологин без редиректа
                        $text2 = str_replace('[AUTH_LINK]', $auth_link, $text2);
                    }

                    $send = self::sender($user_curator['email'], $subject, $text2, $setting, $from_name, $from);
                }
            }
        }

        return true;
    }


    /**
     * ПИСЬМО КЛИЕНТУ ОБ ОТВЕТЕ на его задание/тест и т.д.
     * @param $user
     * @param $lesson_id
     * @param $text_message
     * @param $type_message
     * @param $status
     * @param $curator
     * @return bool
     */
    public static function SendEmailFromCuratorToUser($user, $lesson_id, $text_message, $type_message, $status, $curator)
    {

        // Получаем настройки
        $setting = System::getSetting();
        $lesson = TrainingLesson::getLesson($lesson_id);
        $training_id = Training::getTrainingIdByLessonId($lesson_id);
        $training = Training::getTraining($training_id);
        $subject = $training['subject_letter_to_user'];
        $letter = $training['letter_to_user'];
        $link = $setting['script_url']."/training/view/{$training['alias']}/lesson/{$lesson['alias']}";
        $str = TrainingLesson::getLessonStatusText($status);
        
        $message = isset($type_message) ? html_entity_decode(base64_decode($type_message)) : '';
        $curator_name = isset($curator['user_name']) ? $curator['user_name'] : 'Куратор';
        // Реплейсим письмо
        $replace = array(
            '[LINK]' => $link,
            '[TRAINING]' => $training['name'],
            '[LESSON]' => $lesson['name'],
            '[NAME]' => $user['user_name'],
            '[CLIENT_NAME]' => $user['user_name'],
            '[SURNAME]' => $user['surname'],
            '[CURATOR]' => $curator_name,
            '[MESSAGE]' => $message,
            '[STATUS]' => $str
        );

        if (strpos($letter, '[AUTH_LINK]') !== false) {
            $auth_link = User::generateAutoLoginLink($user); //Ссылка автологин без редиректа
            $replace = array_merge($replace, [
                '[AUTH_LINK]' => $auth_link,
            ]);
        }

        $text = strtr($letter, $replace);

        return self::sender($user['email'], $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }




    /**
     *    ПРОДУКТЫ , ЗАКАЗЫ
     */


    /**
     * ПИСЬМО КЛИЕНТУ С ПОДТВЕРЖДЕНИЕМ ДОСТАВКИ
     * @param $order_date
     * @param $name
     * @param $email
     * @param $items
     * @param $total
     * @param $metod_name
     * @return bool
     */
    public static function SendConfirmDelivery($order_date, $name, $email, $items, $total, $metod_name)
    {
        // Получаем настройки
        $setting = System::getSetting();

        $link = $setting['script_url'].'/delivery/confirm/'.$order_date.'?key='.md5($email);
        $space = '<br />';
        $items = implode($space, $items);

        // Реплейсим письмо
        $replace = array(
            '[ORDER]' => $order_date,
            '[CLIENT_NAME]' => $name,
            '[NAME]' => $name,
            '[ITEMS]' => $items,
            '[SUMM]' => $total,
            '[METHOD]' => $metod_name,
            '[CURRENCY]' => $setting['currency'],
            '[LINK]' => $link
        );

        $letter = System::Lang('CONFIRM_DELIVERY_LETTER');

        $text = strtr($letter, $replace);
        $subject = System::Lang('CONFIRM_DELIVERY_SUBJECT');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }

    // ПИСЬМО КЛИЕНТУ О ЗАКАЗЕ
    // ПРИНИМАЕТ ТЕКСТ ПИСЬМА, ИМЯ КЛИЕНТА, НОМЕР ЗАКАЗА
    public static function SendOrder($order_date, $letter, $product, $name, $email, $summ, $pincode, $addsubject = null, $surname = false, $patronymic = false,$to_child=false,$order_id=null)
    {
        $setting = System::getSetting();
        $link = $setting['script_url'].'/download/'. $order_date.'?key='.md5($email);
        $pin = !empty($pincode) ? System::Lang('YOUR_PINCODE').$pincode : '';

        $userdata = User::getUserDataByEmail($email);

        $prelink = User::generateAutoLoginLink($userdata);//Ссылка автологин без редиректа

        // реплейсим письмо
        $replace = array(
            '[CLIENT_NAME]' => $name,
            '[FULL_NAME]' => $name.' '.$surname,
            '[NAME]' => $name ?? " ",
            '[ORDER]' => $order_date,
            '[PRODUCT_NAME]' => $product,
            '[LINK]' => $link,
            '[SUMM]' => $summ,
            '[DWL_TIME]' => $setting['dwl_time'],
            '[SUPPORT]' => $setting['support_email'],
            '[PINCODE]' => $pin,
            '[EMAIL]' => $email,
            '[AUTH_LINK]' => $prelink,
        );

        if (preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $letter)) {
            $letter = CustomFields::replaceContent($letter, $email);
        }

        $text = strtr($letter, $replace);
        $text = User::replaceAuthLinkInText($text, $prelink);//Ссылка автологин с редиректом

        if ($addsubject != null) {
            $subject = $addsubject;
        } else {
            $subject = $setting['client_letter_subj'] != null ? $setting['client_letter_subj'] : System::Lang('SUBJECT_EMAIL_ORDER');
        }

        $subject = strtr($subject, $replace);
        if($to_child==true)
        $text.=$setting['script_url'].'/lk/registration?o='.$order_id;
        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }



    // ПИСЬМО КЛИЕНТУ О РЕГИСТРАЦИИ
    public static function SendLogin($name, $email, $pass, $letter)
    {
        // Получаем настройки
        $setting = System::getSetting();

        $link = $setting['script_url'].'/lk';

        $userdata = User::getUserDataByEmail($email);
        $prelink = User::generateAutoLoginLink($userdata);//Ссылка автологин без редиректа

        // реплейсим письмо
        $replace = array(
            '[CLIENT_NAME]' => $name,
            '[NAME]' => $name,
            '[EMAIL]' => $email,
            '[LINK]' => $link,
            '[SUPPORT]' => $setting['support_email'],
            '[PASS]' => $pass,
            '[AUTH_LINK]' => $prelink,
        );

        $text = strtr($letter, $replace);
        $text = User::replaceAuthLinkInText($text, $prelink); //Ссылка автологин с редиректом

        $subject = System::Lang('SUBJECT_EMAIL_REGISTER');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ПИСЬМО КЛИЕНТУ ДЛЯ ПОДТВЕРЖДЕНИЯ РЕГИСТРАЦИИ
    public static function SendRegConfirm($name, $email, $req_key, $pass, $letter)
    {
        // Получаем настройки
        $setting = System::getSetting();

        $link = "{$setting['script_url']}/lk/registration/$req_key";
        $link2 = "{$setting['script_url']}/lk";

        // реплейсим письмо
        $replace = array(
            '[CLIENT_NAME]' => $name,
            '[NAME]' => $name,
            '[EMAIL]' => $email,
            '[LINK]' => $link,
            '[LINK2]' => $link2,
            '[SUPPORT]' => $setting['support_email'],
            '[PASS]' => $pass,
        );

        $text = strtr($letter, $replace);
        $subject = System::Lang('SUBJECT_EMAIL_REGISTER');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ПИСЬМО ПАРТНЁРУ О РЕГИСТРАЦИИ
    public static function SendPernerLetter($name, $email, $reg_key)
    {
        $setting = System::getSetting($name, $email);
        $link = $setting['script_url'].'/aff/confirm?key='.$reg_key;
        $letter = System::Lang('LETTER_PARTNER_REGISTER');

        // реплейсим письмо
        $replace = array(
            '[NAME]' => $name,
            '[CLIENT_NAME]' => $name,
            '[LINK]' => $link,
            '[SUPPORT]' => $setting['support_email']
        );

        $text = strtr($letter, $replace);
        $subject = System::Lang('SUBJECT_PARTNER_REGISTER');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    /**
     * ПИСЬМО АДМИНУ о ЗАКАЗЕ
     * @param $order_date
     * @param $name
     * @param $email
     * @param $sum
     * @param $partner
     * @param null $order_id
     * @param null $payment_id
     * @param null $surname
     * @param $clientPhone
     * @return bool
     */
    public static function SendOrderToAdmin ($order_date, $name, $email, $sum, $partner_id, $order_id = null,
                                             $payment_id = null, $surname = null, $clientPhone)
    {
        // Получаем настройки
        $setting = System::getSetting();
        $letter = System::Lang('ADMIN_LETTER_ORDER');
        $subject = System::Lang('SUBJECT_EMAIL_ADMIN_ORDER');
        // +KEMSTAT+8
        $subject = $subject;// . ' '.$setting['site_name'];
        // +KEMSTAT-8
        $contents = '';
        
        // +KEMSTAT-8
        $partner_subj = !empty($partner_id)?User::getUserById($partner_id):null;
        // -KEMSTAT-8
        if (!empty($partner_subj)) {
            $partner_name = $partner_subj['user_name'];
            $partner_surname = $partner_subj['surname'];
            $partner_mail = $partner_subj['email'];
        } else {
            $partner_name=$partner_mail='Нет данных';
            $partner_surname='';
        }
        
        if ($payment_id != null) {
            $payment_data = Order::getPaymentDataForAdmin($payment_id);
            $payment = $payment_data['title'];
        } else {
            $payment = 'Free';
        }

        if (!empty($order_id)) {
            $items = Order::getOrderItems($order_id);
            foreach($items as $item) {
                $product_data = Product::getProductName($item['product_id']);
                $contents .= $product_data['product_name'].$product_data['mess'].'<br />';
            }
        }

        $replace = array(
            '[CLIENT_NAME]' => $name.' '.$surname,
            '[NAME]' => $name.' '.$surname,
            '[ORDER]' => $order_date,
            '[PAYMENT]' => $payment,
            '[CLIENT_EMAIL]' => $email,
            '[SUMM]' => $sum,
            // +KEMSTAT-8
            '[PARTNER]' => !empty($partner_id)?$partner_id:'Нет данных',
            '[PARTNER_NAME]' => $partner_name,
            '[PARTNER_SURNAME]' => $partner_surname,
            '[PARTNER_MAIL]' => $partner_mail,
            // -KEMSTAT-8
            '[CONTENTS]' => $contents,
            '[CLIENT_PHONE]' => $clientPhone
        );

        $text = strtr($letter, $replace);


        if (!isset(json_decode($setting['params'])->order_notice_all_admins) || json_decode($setting['params'])->order_notice_all_admins == 1) {
            return self::sender($setting['admin_email'], $subject, $text, $setting, $setting['sender_name'], $setting['admin_email']);
        } else {
            $admins = User::getUsersByRoles('admin');
            if ($admins) {
                $result = true;
                foreach ($admins as $admin) {
                    $result = self::sender($admin['email'], $subject, $text, $setting,
                            $setting['sender_name'], $setting['admin_email']
                    ) && $result;
                }

                return $result;
            }

            return false;
        }
    }


    /**
     * ПИСЬМО АДМИНУ О РЕГИСТРАЦИИ НОВОГО ПАРТНЁРА
     * @param $name
     * @param $email
     * @return bool
     */
    public static function SendNotifAboutPartnerToAdmin($name, $email)
    {
        // Получаем настройки
        $setting = System::getSetting();
        $letter = System::Lang('ADMIN_LETTER_ABOUT_PARTNER');

        $replace = array (
            '[NAME]' => $name,
            '[CLIENT_NAME]' => $name,
            '[EMAIL]' => $email
        );

        $text = strtr($letter, $replace);
        $subject = System::Lang('ADMIN_SUBJECT_ABOUT_PARTNER');

        return self::sender($setting['admin_email'], $subject, $text, $setting, $setting['sender_name'], $setting['admin_email']);
    }


    /**
     * ПИСЬМО ЮЗЕРУ ДЛЯ ПОДТВЕРЖДЕНИЯ СМЕНЫ ПАРОЛЯ
     * @param $email
     * @param $letter
     * @param $key
     * @return bool
     */
    public static function LostYourPass($email, $letter, $key)
    {
        // Получаем настройки
        $setting = System::getSetting();

        $link = $setting['script_url'].'/lostpass?email='.$email.'&key='.$key;

        // реплейсим письмо
        $replace = array(
            '[LINK]' => $link,
            '[SITE]' => $setting['script_url']
        );

        $text = strtr($letter, $replace);
        $subject = System::Lang('LETTER_LOSTPASS_SUBJECT_RESPONSE');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    // ПИСЬМО ЮЗЕРУ С НОВЫМ ПАРОЛЕМ
    public static function ChangePassOk($email, $pass, $letter)
    {
        // Получаем настройки
        $setting = System::getSetting();
        $link = $setting['script_url'].'/lk';

        // реплейсим письмо
        $replace = array(
            '[PASS]' => $pass,
            '[LINK]' => $link
        );

        $text = strtr($letter, $replace);
        $subject = System::Lang('LETTER_LOSTPASS_SUBJECT');

        return self::sender($email, $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }


    /**
     * УВЕДОМЛЕНИЕ АДМИНУ О ПОДТВЕРЖДЕНИИ ДОСТАВКИ ЗАКАЗА
     * @param $order_date
     * @param $email
     * @param $name
     * @return bool
     */
    public static function AdminDeliveryConfirm($order_date, $email, $name)
    {
        $setting = System::getSetting();
        // Реплейсим письмо
        $replace = array(
            '[ORDER]' => $order_date,
            '[CLIENT_NAME]' => $name,
            '[NAME]' => $name,
            '[EMAIL]' => $email
        );

        $letter = System::Lang('CONFIRM_DELIVERY_ADMIN_LETTER');
        $text = strtr($letter, $replace);
        $subject = System::Lang('CONFIRM_DELIVERY_ADMIN_SUBJECT');

        return self::sender($setting['admin_email'], $subject, $text, $setting, $setting['sender_name'], $setting['sender_email']);
    }



    // УВЕДОМЛЕНИЕ О РУЧНОЙ ОПЛАТЕ
    public static function AdminCustomOrder($order, $secret, $email, $client_email, $payment, $purse, $summ, $client, $phone, $script_url, $order_id)
    {
        $setting = System::getSetting();
        $key = md5($order_id.$secret);

        $subject = 'Ручной перевод';
        $message = "<p>Оплата заказа № $order ручным способом.</p>
        <p>Система: $payment<br />
        Кошелёк: $purse<br />
        Сумма: $summ <br />
        Клиент: $client<br />
        E-mail: $client_email<br />
        Телефон: $phone</p>
        <p><a href=\"$script_url/confirmcustom?key=$key&date=$order\">Подтвердить заказ</p>";

        return self::sender($email, $subject, $message, $setting, 'BillingMaster', $email);
    }




    // УВЕДОМЛЕНИЕ О ВЫСТАВЛЕНИИ СЧЁТА КОМПАНИИ
    // УВЕДОМЛЕНИЕ О РУЧНОЙ ОПЛАТЕ
    public static function AdminCompanyOrder($order, $secret, $email, $client_email, $summ, $client, $phone, $script_url,
        $order_id, $organization, $inn, $bik, $rs, $country, $city, $address)
    {
        $setting = System::getSetting();
        $key = md5($order_id.$secret);

        $subject = "Выставление счёта юр.лицу";
        $message = "<p>Выставлен счёт на заказ № $order, проверьте поступление на ваш р/с</p>
        <p>Сумма: $summ <br />
        Клиент: $client<br />
        E-mail: $client_email<br />
        Телефон: $phone</p>
        <p><strong>Реквизиты:</strong></p>
        <p>Организация: $organization<br />
        ИНН/КПП: $inn<br />
        БИК:$bik<br />
        Р/с:$rs</p>
        <p>$address</p>
        <p>Когда счёт будет оплачен, вы можете <a href=\"$script_url/confirmcustom?key=$key&date=$order\">подвтердить оплату</p>";

        // Письмо админу
        return self::sender($email, $subject, $message, $setting, 'BillingMaster', $email);
    }



    /**
     *  СИСТЕМНЫЕ УВЕДОМЛЕНИЯ ДЛЯ АДМИНА
     */


    /**
     * СИСТЕМНОЕ УВЕДОМЛЕНИЕ ДЛЯ АДМИНА ОБ ОКОНЧАНИИ ПИНКОДОВ ДЛЯ ПРОДУКТА
     * ПРИНИМАЕТ ЕМЕЙЛ АДМИНА И ID продукта
     * @param $email
     * @param $id
     * @param $pin_count
     * @return bool
     */
    public static function AdminPincodeNotification($email, $id, $pin_count)
    {
        $setting = System::getSetting();
        $product_name = Product::getProductName($id);

        $subject = "School-Master - заканчиваются пин коды";
        $message = "<p>Заканчиваются пинкоды для продукта: " . $product_name['product_name']."<br />Осталось всего $pin_count. <br />
        Проверьте и добавьте новых.</p>
        <p>Это системное сообщение скрипта School-Master, отвечать на него не нужно.</p>";

        // Письмо админу
        return self::sender($email, $subject, $message, $setting, 'BillingMaster', $email);
    }



    // СИСТЕМНОЕ УВЕДОМЛЕНИЕ ДЛЯ АДМИНА О НЕРАБОЧЕМ АПСЕЛЛЕ
    // ПРИНИМАЕТ ЕМЕЙЛ АДМИНА И НОМЕР ЗАКАЗА
    public static function AdminNotification($email, $id)
    {
        $setting = System::getSetting();
        $subject = "School-Master - Апселл не найден";
        $message = "<p>Товар для апселла не найден (может вы его удалили?).<br />Номер заказа $id<br />Проверьте настройки для продукта</p>
        <p>Это системное сообщение скрипта School-Master, отвечать на него не нужно.</p>";

        // Письмо админу
        return self::sender($email, $subject, $message, $setting, 'BillingMaster', $email);
    }


    /**
     * SMTP ОТПРАВЩИК ОДИНОЧНЫХ писем
     * @param $email
     * @param $subject
     * @param $text
     * @param $setting
     * @param null $sender_name
     * @param bool $is_testLetter - тестовое ли письмо. Для тестового письма нет обработки исключений
     * @return bool
     */
    public static function SMTPSingleSender($email, $subject, $text, $setting, $sender_name = null, $is_testLetter = false, $reply_to = false)
    {
        require_once (dirname(__FILE__) . '/../vendor/autoload.php');

        // if (System::CheckExtensension('autopilot', 1)) { // расширение autopilot
        //     $autopilot = Autopilot::getSettings();
        //     if (isset($autopilot['vk_club']['notify']) && $autopilot['vk_club']['notify'] == 1) {
        //         Autopilot::sendMessToVKbyEmail($email, $text);
        //     }

        //     if (stripos($email, '@vk.com')) {
        //         return true; // do not send to not valid emails!
        //     }
        // }

        // if (System::CheckExtensension('telegram', 1)) { // расширение telegram
        //     $telegram = Telegram::getSettings();
        //     $params = unserialize($telegram);
        //     if (isset($params['params']['notify']) && $params['params']['notify'] == 1) {
        //         Telegram::sendNotifyMessage($email, $text);
        //     }
        // }

        $time = time();

        if(!$sender_name)
            $sender_name = $setting['sender_name'];

        $sender_email = $setting['sender_email'];

        // Инициализировать объект Мейлера
        if ($setting['smtp_ssl'] > 0) {
            if ($setting['smtp_ssl'] == 1) {
                $auth = 'ssl';
            }

            if ($setting['smtp_ssl'] == 2) {
                $auth = 'tls';
            }

            $transport = (new Swift_SmtpTransport($setting['smtp_host'], $setting['smtp_port'], $auth))
                ->setUsername($setting['smtp_user'])
                ->setPassword($setting['smtp_pass']);
        } else {
            $transport = (new Swift_SmtpTransport($setting['smtp_host'], $setting['smtp_port']))
                ->setUsername($setting['smtp_user'])
                ->setPassword($setting['smtp_pass']);
        }

        $mailer = new Swift_Mailer($transport);

        if (!empty($setting['smtp_private_key'])) {
            $signer = new Swift_Signers_DKIMSigner($setting['smtp_private_key'], $setting['smtp_domain'], $setting['smtp_selector']);
        }

        $message = new Swift_Message();
        if (!empty($setting['smtp_private_key'])) {
            $message->attachSigner($signer);
        }

        $message->setFrom([$sender_email => $sender_name]);
        if ($setting['return_path'] != null) {
            $message->setSender($setting['return_path']);
        }

        if($reply_to) $message->AddReplyTo($reply_to[1], $reply_to[0]);
        else $message->AddReplyTo($setting['return_path'], $sender_name);

        // Message-ID
        $header_name = md5($email.$time);
        $chars = ['http://','https://']; // символы для удаления
        $domain = str_replace($chars, '', $setting['script_url']); // PHP код
        $message->getHeaders()->addIdHeader($header_name, $header_name.'@'.$domain);

        // Письмо
        $message->setBody($text, 'text/html', 'utf-8');
        $message->addPart(strip_tags($text), 'text/plain');
        $message->setSubject(html_entity_decode($subject));
        $message->setTo($email);

        if ($is_testLetter) {//Если тестовое сообщение - не ловить исключения(обработчик исключений находиться выше по структуре вызовов)

            $res = $mailer->send($message);

        } else { //если сообщение не тестовое - поймать исключение и добавить сообщение об ошибке админу
            try {
                $res = $mailer->send($message);
            } catch (Exception $e) {
                AdminNotice::addNotice('Ошибка отправки сообщения. '.'<br>Протестируйте отправку сообщений в настройках(раздел почта -> тестировать отправку)', '/admin/settings/');
            }
        }

        if (isset($res) && $res) {
            $descript = null;
            $subject = htmlentities($subject);
            $log = Email::WriteLog($email, $sender_name, $text, $time, $subject, $descript);
            if ($log) {
                return true;
            }
        }

        return false;
    }


    // Тестовое уведоление для отладки
    public static function TestEmail($str)
    {
        $setting = System::getSetting();
        $email = 'report@kasyanov.info';
        $message = 'Для отладки<br />'.$str;

        return self::sender($email, 'School-Master - отладка', $message, $setting, 'BillingMaster', $email);
    }


    /**
     * ОТПРАВИТЬ ПИСЬМО
     * @param $email
     * @param $subject
     * @param $text
     * @param $setting
     * @param $from_name
     * @param $from
     * @return bool
     */
    public static function sender($email, $subject, $text, $setting, $from_name, $from, $is_testLetter = false, $reply_to = false, array $addit_data = []) {
        $caller = System::get_caller(__FUNCTION__);
        $res = Connect::sendMessagesByEmail($email, $subject . "\n\n" . $text, [
            'caller' => $caller,
            'email' => $email,
            'subject' => $subject,
            'text' => $text,
            'setting' => $setting,
            'form' => $from,
            'form_name' => $from_name,
            'addit_data' => $addit_data
        ]);

        if ($setting['use_smtp'] == 1) { // Отправляем через SMTP
            $send = self::SMTPSingleSender($email, $subject, $text, $setting, $from_name, $is_testLetter, $reply_to);

        } else { // Отправляем через Mail()
            $send = self::mailSender($email, $subject, $text, $from_name, $from);
        }

        return $send ? true: false;
    }


    /**
     * ОТПРАВИТЬ ПИСЬМО ЧЕРЕЗ СТАНДАРТНУЮ ФУНКЦИЮ mail
     * @param $email
     * @param $subject
     * @param $text
     * @param $from_name
     * @param $from
     * @return bool
     */
    public static function mailSender($email, $subject, $text, $from_name, $from) {
        $headers= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html;charset=utf-8 \r\n";
        $headers .= "From: $from_name <$from>\r\n";
        $headers .= "Reply-To: $from \r\n";

        $subject = html_entity_decode($subject);

        return mail($email, $subject, $text, $headers);
    }


    /**
     * ЗАПИСЬ ОТПРАВЛЕННЫХ ЕМЕЙЛ СООБЩЕНИЙ В ЛОГ
     * @param $email
     * @param $sender_name
     * @param $letter
     * @param null $time
     * @param null $type
     * @param null $descript
     * @return bool
     */
    public static function WriteLog($email, $sender_name, $letter, $time = null, $type = null, $descript = null) {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'log_email_send 
            (email, sender_name, letter, datetime, type, description ) 
        VALUES 
            (:email, :sender_name, :letter, :datetime, :type, :descript)';

        $result = $db->prepare($sql);
        if (function_exists('gzdeflate')) {
            $deflated = gzdeflate($letter, 9);
            if ($deflated) {
                $letter = utf8_encode($deflated);
            }
        }

        $result->bindParam(':letter', $letter, PDO::PARAM_STR);
        $result->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
        $result->bindParam(':datetime', $time, PDO::PARAM_INT);

        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_STR);
        $result->bindParam(':descript', $descript, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ИЗ ЛОГА Email сообщений
     * @param int $page
     * @param null $show_items
     * @param bool $pagination
     * @param bool $email
     * @param bool $start
     * @param bool $finish
     * @param bool $subject
     * @return bool
     */
    public static function getLog($page = 1, $show_items = null, $pagination = false, $email = false, $start = false, $finish = false, $subject = false, $nolimit = false) {
        $clauses = array();
        if ($start) {
            $clauses[] =  "datetime > $start";
        }
        if ($finish) {
            $clauses[]  = "datetime < $finish";
        }
        if ($email !== false) {// Поиск по email
            $clauses[] = "email LIKE '%$email%'";
        }
        if ($subject != false) {// Поиск по теме
            $clauses[] = "type LIKE '%$subject%'";
        }

        $where = !empty($clauses) ? (' WHERE ' . implode(' AND ', $clauses)) : '';
        $limit = $pagination ? "LIMIT $show_items OFFSET " . ($page - 1) * $show_items : 'LIMIT 3000';
        $limit = $nolimit ? '' : $limit;
        $sql = "SELECT id, email, datetime, type FROM ".PREFICS."log_email_send $where ORDER BY id DESC $limit";

        $db = Db::getConnection();
        $result = $db->query($sql);
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ПИСЬМА ОТПАРВЛЕННЫЕ ЮЗЕРУ
     * @param $email
     * @return array|bool
     */
    public static function getLogByUser($email) {
        $db = Db::getConnection();
        $result = $db->query("SELECT id, email, type, datetime FROM ".PREFICS."log_email_send WHERE email = '$email' ORDER BY datetime DESC LIMIT 150");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ЗАПИСИ ЛОГА
     * @param $id
     * @return bool|mixed
     */
    public static function getLogData($id) {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."log_email_send WHERE id = $id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (empty($data)) {
            return false;
        }

        if (function_exists('gzinflate')) {
            $inflated = @gzinflate(utf8_decode($data['letter']));//@ - если убрать, при не удачном разжатии будет отображаться ошибка, помимо возращенного false

            if ($inflated) {//Если разжалось
                $result = $inflated;
            } else {//Если не разжалось, то возможно письмо и не сжималось вовсе
                $result = $data['letter'];
            }
        }
        $data['letter'] = $result ? $result : $data['letter'];

        return !empty($data) ? $data : false;
    }

    public static function updateLogLetter($id, $letter) {
        $db = Db::getConnection();

        $result = $db->prepare("UPDATE `".PREFICS."log_email_send` SET `letter` = :letter WHERE `id` = :id");
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        $result->bindParam(':letter', $letter, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ПОДСЧЁТ КОЛ-ВА ЗАПИСЕЙ ЛОГА ЕМЕЙЛ
     * @return mixed
     */
    public static function countLogs()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(id) FROM ".PREFICS."log_email_send");
        $count = $result->fetch();

        return $count[0];
    }


    /**
     * УДАЛЕНИЕ СТАРЫХ ЛОГОВ
     * @param $date
     * @return bool
     */
    public static function delOldLogs($date) {
        $db = Db::getConnection();
        $result = $db->prepare('DELETE FROM '.PREFICS.'log_email_send WHERE datetime < :datetime');
        $result->bindParam(':datetime', $date, PDO::PARAM_INT);

        return $result->execute();
    }

   /* public static function sendMessageAccountStatement($email, $order_id, $client_name, $client_surmane, $product_id, $product_name, $client_email, $client_phone, $order_date, $price) {
        $setting = System::getSetting();
        $letter = System::Lang('ACCOUNT_STATEMENT_NOTIFY_EMAIL');
        $replace = array (
            '[ORDER_ID]' => $order_id,
            '[PRODUCT_ID]' => $product_id,
            '[PRODUCT_NAME]' => $product_name,
            '[CLIENT_EMAIL]' => $client_email,
            '[CLIENT_PHONE]' => $client_phone,
            '[SUMM]' => $price,
            '[CLIENT_NAME]' => $client_name,
            '[CLIENT_SURNAME]' => $client_surmane,
            '[LINK]' => $setting['script_url'].'/pay/'.$order_date,
        );
        $text = strtr($letter, $replace);
        $subject = System::Lang('Сформирован заказ');

        return self::sender($email, $subject, $text, $setting, $setting['sender_email'], $email);
    }*/
    public static function sendMessageAccountStatement($email, $order_id, $client_name, $client_surmane, $product_id, $product_name, $client_email, $client_phone, $order_date, $price,$to_child=false) {
        $setting = System::getSetting();
        $letter = System::Lang('ACCOUNT_STATEMENT_NOTIFY_EMAIL');
        $replace = array (
            '[ORDER_ID]' => $order_id,
            '[PRODUCT_ID]' => $product_id,
            '[PRODUCT_NAME]' => $product_name,
            '[CLIENT_EMAIL]' => $client_email,
            '[CLIENT_PHONE]' => $client_phone,
            '[SUMM]' => $price,
            '[CLIENT_NAME]' => $client_name,
            '[CLIENT_SURNAME]' => $client_surmane,
            '[LINK]' => $setting['script_url'].'/pay/'.$order_date,
        );
        $text = strtr($letter, $replace);
        $subject = System::Lang('Сформирован заказ');
        if($to_child==true)
        $text.=$setting['script_url'].'/lk/registration?o='.$order_id;
        return self::sender($email, $subject, $text, $setting, $setting['sender_email'], $email);
    }
}