<?php defined('BILLINGMASTER') or die;

return array (
    'admin/connect' => 'extensions/connect/adminConnect/settings/$1',
    'admin/connect/ajax' => 'extensions/connect/adminConnect/ajax/one',
    'admin/connect/ajax/submit_form/([0-9])+/([a-z0-9_-]+)' => 'extensions/connect/adminConnect/ajaxSubmit/$1/$2',
    'admin/connect/ajax/([a-z0-9_-]+)' => 'extensions/connect/adminConnect/ajaxForm/$1',
    'admin/connect/ajax/bot/([a-z0-9_-]+)' => 'extensions/connect/adminConnect/ajaxForm/$1/1',

    'admin/connect/ajax/user/([0-9]+)' => 'extensions/connect/adminConnect/userSettingAjaxForm/$1',

    'connect/webhook/([a-z0-9_-]+)' => 'extensions/connect/connect/webhook/$1',

    'connect/check_auth' => 'extensions/connect/connect/isAuth',
    'connect/auth/([a-z0-9_-]+)' => 'extensions/connect/connect/authLink/$1',
    'connect/authLoad/([a-z0-9_-]+)' => 'extensions/connect/connect/authLoad/$1',
    'connect/authProcess/([a-z0-9_-]+)' => 'extensions/connect/connect/process/$1',

    'connect/ajax/lk/([a-z0-9_-]+)' => 'extensions/connect/connect/ajaxLk/$1/1',
    'connect/jquery/([a-z0-9_-]+)' => 'extensions/connect/connect/ajax/$1',
    'connect/attach/([a-z0-9_-]+)' => 'extensions/connect/connect/process/$1/attach',
    'connect/unlink/([a-z0-9_-]+)' => 'extensions/connect/connect/process/$1/unlink'
);