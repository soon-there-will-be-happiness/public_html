<?php defined('BILLINGMASTER') or die;

$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter" . $this->settings['yacounter'] . ".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="' . $ya_goal . $ga_goal . ' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' ' . $metriks : '';

$params = unserialize(base64_decode($payment['params']));


$inv_id = $order['order_id'];
$inv_desc = 'Оплата заказа №' . $order['order_date'];
$out_summ = $total . '.00';
$shp_item = "2";
$in_curr = "";
$culture = "ru";
$payment_method = isset($params['payment_method']) ? $params['payment_method'] : 'full_prepayment';
$payment_object = isset($params['payment_object']) ? $params['payment_object'] : 'commodity';
$tax = isset($params['tax']) ? $params['tax'] : 'none';
$sno = isset($params['sno']) ? $params['sno'] : 'osn';
$pay_object_delivery = isset($params['pay_object_delivery']) ? $params['pay_object_delivery'] : 'commodity';

$items = array();
$order_items = Order::getOrderItems($inv_id);

foreach ($order_items as $item) {
    $items[] = [
        'name' => $item['product_name'],
        'quantity' => 1,
        'sum' => $item['price'] . '.00',
        'tax' => $tax,
        'payment_method' => $payment_method,
        'payment_object' => $payment_object
    ];
}

$ship_method = !empty($order['ship_method_id']) ? System::getShipMethod($order['ship_method_id']) : null;
if ($ship_method) {
    $items[] = [
        'name' => $ship_method['title'],
        'quantity' => 1,
        'sum' => $ship_method['tax'] . '.00',
        'tax' => $tax,
        'payment_method' => $payment_method,
        'payment_object' => $pay_object_delivery
    ];
}

$receipt = json_encode(
    array(
        'sno' => $sno,
        'items' => $items
    ),
JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT
);

if ($recurrent_enable) {
    $crc  = md5("$mrh_login:$out_summ:$inv_id:$receipt:$mrh_pass1:Shp_item=$shp_item:Shp_recurrent=$inv_id");
} else {
    $crc  = md5("$mrh_login:$out_summ:$inv_id:$receipt:$mrh_pass1:Shp_item=$shp_item");
}

// Здесь необходимо указать ссылку для отправки формы на АТОЛ, полученную в ответе API
$payment_url = "https://new-api-mobile.atolpay.ru/v1/ecom/payments"; // замените на действительный URL от АТОЛ

?>
  
    <form action="<?= $payment_url ?>" method="POST" <?= $form_parameters ?>>
        <input type="hidden" name="amount" value="<?php echo $total * 100; ?>"> <!-- Сумма в копейках -->
        <input type="hidden" name="orderId" value="<?php echo $inv_id; ?>">
        <input type="hidden" name="sessionType" value="oneStep">
        <input type="hidden" name="receipt" value="<?php echo urlencode($receipt); ?>">
        <input type="hidden" name="paymentMethods[0][paymentType]" value="card">
        <input type="hidden" name="paymentMethods[0][bankId]" value="100">
        <input type="hidden" name="additionalProps[returnUrl]" value="https://yoursite.com/atol_return">
        <input type="hidden" name="additionalProps[notificationUrl]" value="https://yoursite.com/atol_callback">
        <input type="hidden" name="Authorization" value="Bearer TOKEN"> <!-- Токен для авторизации -->
    <input type="submit" class="payment_btn" value="<?= System::Lang('TO_PAY'); ?>">
    </form>

    <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата через АТОЛ</title>
    <script>
        function sendPaymentRequest() {
            const paymentData = {
                amount: 100000, // сумма в копейках (1000.00 рублей)
                orderId: "b0d564188d7e652602206e8ed2c835cf9", // уникальный идентификатор заказа
                sessionType: "oneStep", // одностадийный платеж
                additionalProps: {
                    returnUrl: "https://yoursite.com/atol_return",
                    notificationUrl: "https://yoursite.com/atol_callback"
                },
                receipt: {
                    buyer: { email: "client@example.com" },
                    positions: [
                        {
                            name: "Товар 1",
                            price: 100000, // цена в копейках (1000.00 рублей)
                            quantity: 1000, // количество (1.000)
                            tax: 0
                        }
                    ],
                    sno: "osn"
                },
                paymentMethods: [
                    {
                        paymentType: "card",
                        bankId: 100
                    }
                ]
            };

            fetch("https://new-api-mobile.atolpay.ru/v1/ecom/payments", {
                method: "POST",
                headers: {
                    "Authorization": "Bearer TOKEN",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(paymentData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.paymentUrl) {
                    window.location.href = data.paymentUrl;
                } else {
                    alert("Ошибка: " + data.errorMessage);
                }
            })
            .catch(error => {
                console.error("Ошибка при отправке запроса:", error);
                alert("Произошла ошибка при регистрации платежа.");
            });
        }
    </script>
</head>
<body>
    <button onclick="sendPaymentRequest()">Отправить</button>
</body>
</html>
