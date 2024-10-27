<?php
// URL ATOL API
$apiUrl = 'https://croc-sandbox-api-mobile.atolpay.ru/v1/ecom/payments';
$token = 'b96218be9d90a80ab7c0855cb36bb5d1'; // Ваш токен

// Получаем JSON данные из POST-запроса
$data = file_get_contents('php://input');

// Инициализация cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);

// Отправка запроса и получение ответа
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Устанавливаем заголовки для CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Возвращаем ответ
http_response_code($httpCode);
echo $response;
?>