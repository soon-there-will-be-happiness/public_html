<?php defined('BILLINGMASTER') or die;
require_once (ROOT."/payments/sberbank/sberbank.php");

function jresponse($message, $status = 422, $data = null) {
    http_response_code($status);
    if (!isset($data)) {
        return die(json_encode(["message" => $message], JSON_UNESCAPED_UNICODE));
    } else {
        return die(json_encode(["message" => $message, "data" => $data], JSON_UNESCAPED_UNICODE));
    }
}