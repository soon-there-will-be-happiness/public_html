<?php defined('BILLINGMASTER') or die;

return array (
    
    'admin/flows/del/([0-9]+)' => 'extensions/flows/adminFlow/del/$1',
    'admin/flows/edit/([0-9]+)' => 'extensions/flows/adminFlow/edit/$1',
    'admin/flows/add' => 'extensions/flows/adminFlow/add',
    'admin/flows' => 'extensions/flows/adminFlow/index',
    'admin/flowsetting' => 'extensions/flows/adminFlow/settings'
    
);