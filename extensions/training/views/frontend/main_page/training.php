<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/extensions/training/controllers/trainingbasecontroller.php');
require_once (ROOT . '/extensions/training/controllers/trainingController.php');

$controller = new trainingController();
$controller->actionIndex();