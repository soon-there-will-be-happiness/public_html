<?php

namespace Connect\Telegram\bot\src;


class CommandHandler {

    private $cmd;
    private $args;
    private $is_btn = false;
    private $is_inlineQuery = false;
    private $q_id;

    private $user;
    private $data;


    public function __construct($cmd, $user, $data) {
        $this->cmd  = $cmd[0];
        $this->args = $this->parseArgs($cmd);
        $this->user = $user;
        $this->data = $data;
    }

    // КОМАНДЫ
    // Имена методов обработки команд должны начинаться с "cmd". Другие методы обрабатываются в globalCmd()

    # Команда /start

    private function cmdstart() {
        $service = Telegram::$api->getService();
        $cmd = $this->args;

        if(!isset($cmd[1])){
            return $this->startCmdWithoutArgs($service);
        }

        $code = substr($cmd[1], 0, 4);
        $hash = substr($cmd[1], 4);

        if($code == "cct1"){
            return $this->startCmdWithCodeCct1($service, $hash);
        }

        if ($code == "cct2"){
            return $this->startCmdWithCodeCct2($service, $hash);
        }
    }

    private function startCmdWithoutArgs($service) {
        if(isset($this->user['sm_user_id']) && $this->user['sm_user_id'] > 0) {
            return Telegram::$api->sendPreMessage('start-is_connect');
        } elseif ($service['status'] == 1 && isset($service['types']['lk_conn'], $service['params']['auth_link']) && $service['params']['lk_conn'] == 1) {
            return Telegram::$api->sendPreMessage('start-not_connect');
        }

        return null;
    }

    private function startCmdWithCodeCct1($service, $hash) {
        if ($service['status'] != 1 || !isset($service['types']['auth'], $service['params']['auth_link']) || @ $service['params']['auth'] != 1)
            return Telegram::$api->sendPreMessage('auth-off');

        $res = \Connect::authUser('telegram', $hash, (Telegram::$api)('from_id'), $this->user);

        if($res == 'success') {
            return Telegram::$api->sendPreMessage('auth-suc');
        }
        elseif ($res == 'authorization off') {
            return Telegram::$api->sendPreMessage('auth-u-off');
        }
        elseif ($res == 'success-register') {
            return Telegram::$api->sendPreMessage('reg-suc');
        }

        return Telegram::$api->sendPreMessage('auth-no_conn');
    }

    private function startCmdWithCodeCct2($service, $hash) {
        if ($service['status'] != 1 || !isset($service['types']['lk_conn'], $service['params']['auth_link']) || @ $service['params']['lk_conn'] != 1)
            return Telegram::$api->sendPreMessage('conn-off');

        $username = isset($this->data['from']['username']) && $this->data['from']['username'] ? '@' . $this->data['from']['username'] : $this->user['first_name'] . ' ' . $this->user['last_name'] ?? '';

        $res = \Connect::addUserByHash($hash, 'telegram', (Telegram::$api)('from_id'), $username);
        if ($res) {
            Telegram::saveUser($res['user_id'], $res['tg_id'], (Telegram::$api)('username'), $hash);
            return Telegram::$api->sendPreMessage('conn-suc');
        }

        return Telegram::$api->sendPreMessage('conn-err');
    }

    # Команда /getid

    private function cmdgetid() {
        if((Telegram::$api)('chat_id') < 0) {
            return Telegram::$api->sendMessage('This chat id: <code>' . (Telegram::$api)('chat_id') . '</code>');
        }

        return null;
    }

    # Команда /notification и ее сокращения

    private function cmdNoti() {
        return $this->cmdNotification();
    }

    private function cmdNotif() {
        return $this->cmdNotification();
    }

    private function cmdNotify() {
        return $this->cmdNotification();
    }

    private function cmdNotification() {
        $cmd = $this->args;
        if(!isset($cmd[1])){
            return Telegram::$api->sendPreMessage('noti');
        }

        if($cmd[1] == 'off' && \Connect::setNotifStatus($this->user['user_id'], 'telegram', false)) {
            return Telegram::$api->sendPreMessage('noti-off');
        }
        elseif ($cmd[1] == 'on' && \Connect::setNotifStatus($this->user['user_id'], 'telegram', true)) {
            return Telegram::$api->sendPreMessage('noti-on');
        }

        return Telegram::$api->sendPreMessage('noti');
    }

    # Дефолтный обработчик команд

    private function cmdDefault() {
        return Telegram::$api->sendPreMessage($this->args[0]);
    }

    private function globalCmd() {
        $cmd = $this->args;

        if(isset($cmd[1]) && $cmd[(count($cmd)-1)] == 'delete_this_message') {
            array_pop($cmd);
            Telegram::$api->deleteMsg();
        }

        if(isset($cmd[2]) && $cmd[(count($cmd)-2)] == 'send_inline:') {
            $answer = array_pop($cmd);
            array_pop($cmd);
        }

        if(isset($cmd[2]) && $cmd[(count($cmd)-2)] == 'next_msg:') {
            Telegram::$api->nextPreMessage(array_pop($cmd));
            array_pop($cmd);
        }

        if ($this->is_btn) {
            Telegram::$api->sendPreMessage($cmd[0]);
        }
    }

    # inline кнопки. Имя метода с inq...

    private function inqAuthProcess() {
        $cmd = $this->args;

        if (!isset($cmd[1])) {
            return Telegram::$api->sendPreMessage('m_auth-false', ['[TEXT]', @ $this->data['message']['text']], true);
        }
        $service = Telegram::$api->getService();

        if ($service['status'] != 1 || !isset($service['types']['auth'], $service['params']['auth_link']) || @ $service['params']['auth'] != 1) {
            return Telegram::$api->sendPreMessage('auth-off');
        }

        $hash = substr($cmd[1], 8, 16) == substr($this->user['auth_hash'], 0, 8);
        $hash = $hash ? substr($cmd[1], 0, 8) . $this->user['auth_hash'] : null;

        $res = $hash ? \Connect::authUser('telegram', $hash, (Telegram::$api)('from_id')) : false;

        if ($res == 'success') {
            Telegram::$api->sendPreMessage('auth-suc', ['!before', "✅ Вход был подтвержден.\n\n"], true);
            $answer = '✅ Success';
        } else {
            Telegram::$api->sendPreMessage('auth-no_conn', ['!before', "✅ Вход был подтвержден.\n\n"], true);
            $answer = '❌ Error';
        }

        return Telegram::$api->answer($this->q_id, $answer);
    }





    // ВСПОМОГАТЕЛЬНЫЕ

    /**
     * Запустить команду
     * @return mixed
     */
    public function execCmd() {
        $method = $this->getCommandPrefix().$this->cmd;
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            $this->globalCmd();
        }
    }

    private function parseArgs($args) {
        return $args;
    }

    # Сетеры и геттеры

    public function setIsBtn(bool $is_btn) {
        $this->is_btn = $is_btn;
    }

    public function setIsInlineQuery(bool $is_inlineQuery) {
        $this->is_inlineQuery = $is_inlineQuery;
    }

    public function setQ_id($q_id) {
        $this->q_id = $q_id;
    }

    /**
     * Получить префикс для имени метода задания
     * @return string
     */
    public function getCommandPrefix() {
        return $this->is_inlineQuery ? "inq" : "cmd";
    }
}