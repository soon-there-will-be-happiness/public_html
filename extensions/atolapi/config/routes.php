<?php defined('BILLINGMASTER') or die;

return array (

    'admin/atolapi-settings' => 'extensions/atolapi/adminAtolapi/settings',
    
    'atolapi/([a-zA-Z0-9_-]+)' => 'extensions/atolapi/atolapi/api/$1',
);