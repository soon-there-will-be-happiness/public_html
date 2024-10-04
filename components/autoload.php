<?php defined('BILLINGMASTER') or die;

set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/components');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/controllers');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/models');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/cache');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/validator');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/cmd');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/log');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/migrations');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/migrations/src');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/system/migrations/src/interfaces');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/lib');
set_include_path(get_include_path(). PATH_SEPARATOR . ROOT . '/payments/poscredit/models');
$extensions_list = scandir(ROOT . '/extensions');
foreach ($extensions_list as $extension_dir) {
    if ($extension_dir != '.' && $extension_dir != '..') {
        $include_path = ROOT . "/extensions/$extension_dir/models";
        if (file_exists($include_path)) {
            set_include_path(get_include_path(). PATH_SEPARATOR . $include_path);
        }
    }
}
spl_autoload_extensions(".php");
spl_autoload_register();
