<?php 
namespace Connect;
defined('BILLINGMASTER') or die;

interface Service{
    
    public static function updSetting(int $id, array $data);

    public static function updUserServiceID(int $sm_user_id, int $tg_id, string $service_username = '', bool $cu_req = false);

    public static function authProcess();

    public static function sendMessage(int $user_id, array $data);
}