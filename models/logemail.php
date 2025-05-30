<?php defined('BILLINGMASTER') or die;


class LogEmail{
    public static function PaymentError($error_message,$place, $error_type){
        $setting = System::getSetting();

        Email::SendEmailAdminAboutError(
            $setting['admin_email'],
            $error_type ,
            $error_message);
        Log::add(5,$error_message,
        "$place $error_type",
        "payment");
        return true;
    }
    public static function PaymentLogForUpdate($message,$place, $type){
        $setting = System::getSetting();

        /*Email::SendEmailAdminAboutError("
            $setting['admin_email'],
             $error_type ,
             $error_message);*/
        Log::add(3,$message,["place"=>$place,"type"=>$type], "payment");
        return true;
    }
    public static function NetworkError($error_message,$place, $error_type){
        $setting = System::getSetting();

        Email::SendEmailAdminAboutError(
            $setting['admin_email'],
             $error_type ,
             $error_message);
        Log::add(5,$error_message,
        ["place"=>$place,
        "type"=>$error_type]
        , "network");
        return true;
    }
    public static function TokenForUpdate($message,$place, $type){
        $setting = System::getSetting();

        /*Email::SendEmailAdminAboutError(
            $setting['admin_email'],
             $error_type ,
             $error_message);*/
        Log::add(2,$message,["place"=>$place,"type"=>$type], "payment");
        return true;
    }

}
?>