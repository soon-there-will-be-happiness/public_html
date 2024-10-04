<?php
namespace Connect\Vkontakte\api;

defined('CONNECT_VK_BOT') or die;

foreach (glob(__DIR__ . '/src/*.php') as $file)
    require_once $file;

class Methods{

    public $bot;
    public $bot_id;
    public $data;
    public $callback_id = false;

    public $vk;

    private $vk_anw = false;
    private $next_msg;

    private $msgs;
    private $kbds;
    private $msgs_file = __DIR__ . '/../../BOT/VK/config/messages.json';

    private $service_data;

    /**
     * Methods constructor.
     * @param array  $data
     * @param false  $callback_id
     * @param string $bot
     */
    public function __construct(array $data, $callback_id = false, $bot = 'main') {
        $this->data = $data;
        $this->bot = $bot;
        $this->callback_id = $callback_id;

        $this->service_data = \Connect::getServiceByName('vkontakte');

        if (@$this->service_data['status'] != 1 || empty($this->service_data['service_params']['chat_token']))
            $this->vk = false;
        else
            $this->vk = new src\main($data, $this->service_data['service_params']['chat_token']);

        $this->bot_id = $this->service_data['service_params']['group_id'];
        unset($this->service_data['service_params']);
    }

    /**
     * @param false $code
     * @return bool
     */
    public function __invoke($code = false) {
        if ($this->vk === false)
            return false;

        if (is_string($code))
            return @ $this->vk->$code;

        return true;
    }

    /**
     * @param string $key
     * @return bool|null
     */
    public function getService(string $key = '') {
        if ($this->vk === false)
            return null;

        if (empty($key))
            return $this->service_data;

        return @$this->service_data[$key];
    }

    /**
     * @param string $text
     * @param false  $keyboard
     * @return false|mixed
     */
    public function sendMessage(string $text, $keyboard = false) {
        return $this->vk
            ->_addKeyboard($keyboard)->MessagesSend($text);
    }

    public function sendPreMessage(string $text, $keyboard = false) {
        return $this->sendMessage($text, $keyboard);
    }

}