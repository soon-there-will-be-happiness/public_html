<?php
require_once (dirname(__FILE__) . 'cyclopsApi.php');
$api = CyclopsApi::getInstance();
$response = $api->listPayments(1,50,['identify' => false]);
