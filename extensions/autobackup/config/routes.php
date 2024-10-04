<?php defined('BILLINGMASTER') or die;

return array (
    'admin/autobackup' => 'extensions/autobackup/adminBackup/index',
    'admin/autobackup/addtask' => 'extensions/autobackup/adminBackup/addtask',
    'admin/autobackup/edittask/([0-9]+)' => 'extensions/autobackup/adminBackup/edittask/$1',
    
    'admin/autobackup/storage' => 'extensions/autobackup/adminBackup/storage',
    'admin/autobackup/storage/add' => 'extensions/autobackup/adminBackup/addstorage',
    'admin/autobackup/storage/edit/([0-9]+)' => 'extensions/autobackup/adminBackup/editstorage/$1',

    'admin/autobackup/copys' => 'extensions/autobackup/adminBackup/copys',


    'admin/autobackup/copys/restore/([0-9]+)' => 'extensions/autobackup/adminBackup/FileRestore/$1',


    'admin/autobackupsettings' => 'extensions/autobackup/adminBackup/settings',

    'admin/autobackup/storage/remove/([0-9]+)' => 'extensions/autobackup/adminBackup/removestorage/$1',
    'admin/autobackup/removetask/([0-9]+)' => 'extensions/autobackup/adminBackup/removetask/$1',

    'admin/autobackup/api/getbackupsize' => 'extensions/autobackup/adminBackupApi/GetBackupSize',

    # Ручной запуск

    'admin/autobackup/run/([0-9]+)' => 'extensions/autobackup/adminBackup/runTask/$1',

    'admin/autobackup/progress/([0-9]+)' => 'extensions/autobackup/adminBackup/showProgress/$1',
    'admin/autobackup/api/progress/([0-9]+)' => 'extensions/autobackup/adminBackupApi/GetLastBackupTaskProgressData/$1',


    # Восстановление
    'admin/autobackup/api/restore/start' => 'extensions/autobackup/adminBackupApi/StartBackupRestore',

    'admin/autobackup/restore/progress/([a-zA-Z0-9_-]+)' => 'extensions/autobackup/adminBackupApi/RestoreProgress/$1',
    'admin/autobackup/api/restore/progress/([a-zA-Z0-9_-]+)' => 'extensions/autobackup/adminBackupApi/GetLastBackupTaskProgress/$1',

    'admin/autobackup/copys/download' => 'extensions/autobackup/adminBackup/downloadCopy',

    'admin/autobackup/startsmartbackup' => 'extensions/autobackup/adminBackupApi/RunSmartBackup',
);