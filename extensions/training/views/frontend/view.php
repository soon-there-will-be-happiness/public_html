<?php defined('BILLINGMASTER') or die;

$template_path = isset($this->view['path']) ? $this->view['path'] : $this->view_path;
if (!file_exists($template_path)) {
    $template_path = ROOT . "/extensions/training/views/frontend/$template_path";
}
require_once ($template_path);