<?php defined('BILLINGMASTER') or die;

require_once __DIR__ . '/Hmac.php';

if (!isset($_REQUEST['currency'])) {
    $ya_goal = !empty($this->settings['yacounter']) ? "yaCounter" . $this->settings['yacounter'] . ".reachGoal('GO_PAY');" : '';
    $ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
    $metriks = $ya_goal || $ga_goal ? ' onsubmit="' . $ya_goal . $ga_goal . ' return true;"' : '';
    $form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' target="_blank"' . $metriks : '';
}

$params = unserialize(base64_decode($payment['params']));
$linktoform = trim($params['prodamus_site_name']);
$secret_key = trim($params['prodamus_secret_key']);// Секретный ключ. Можно найти на странице настроек, в личном кабинете платежной формы.

$tax = isset($params['tax']) ? $params['tax'] : 'none';
$payment_method = isset($params['payment_method']) ? $params['payment_method'] :'full_prepayment';
$payment_object = isset($params['payment_object']) ? $params['payment_object'] : 'commodity';

$available_payment_methods = isset($params['available_payment_methods']) ? implode('|',$params['available_payment_methods']) : '';

$items_prod = [];

if (!isset($selected_currency)) {
    if (isset($params['enable_rub']) && $params['enable_rub'] == 1) {
        $selected_currency = 'rub';
    } elseif (isset($params['enable_usd']) && $params['enable_usd'] == 1) {
        $selected_currency = 'usd';
    } else {
        $selected_currency = 'eur';
    }
}

if (($selected_currency == 'eur' && !isset($params['enable_eur'])) || ($selected_currency == 'eur' && isset($params['enable_eur']) && $params['enable_eur'] == 0 )) {
    $selected_currency = 'rub';
}

$currencyId = null;
if (isset($selected_currency) && $selected_currency != 'rub') {
    switch ($selected_currency) {
        case 'usd':
            $currencyId = $params['usdToCurrencyId'] ?? null;
            $available_payment_methods = isset($params['available_currency_payment_methods']['usd']) ? implode(',',$params['available_currency_payment_methods']['usd']) : '';
            break;
        case 'eur':
            $currencyId = $params['eurToCurrencyId'] ?? null;
            $available_payment_methods = isset($params['available_currency_payment_methods']['eur']) ? implode(',',$params['available_currency_payment_methods']['eur']) : '';
            break;
        default:
            exit('Ошибка, нет такой валюты');
            break;
    }

    $currencydata = [];
    if ($currencyId) {
        $currencydata = Currency::getCurrencyData($currencyId);
    }
}

foreach($order_items_for_payments as $item){
    $items_prod[] = [
        'name' => trim(str_replace(["'", '"', "!", "@", "%", "&", "*", "«", "»"],'', html_entity_decode($item['product_name']))),
        'quantity' => 1,
        'price' => $selected_currency == 'rub' ? $item['price'] . '.00' : $item['price'] * $currencydata['tax'],
        'tax' => $tax,
        'paymentMethod' => $payment_method,
        'paymentObject' => $payment_object
    ];
}

$ship_method = !empty($order['ship_method_id']) ? System::getShipMethod($order['ship_method_id']) : null;
if ($ship_method) {
    $items_prod[] = [
        'name' => $ship_method['title'],
        'quantity' => 1,
        'price' => $selected_currency == 'rub' ? $ship_method['tax'] . '.00' : $ship_method['tax'] * $currencydata['tax'],
        'tax' => $tax,
        'paymentMethod' => $payment_method,
        'paymentObject' => $pay_object_delivery
    ];
}

$protocol = isset($_SERVER["HTTPS"]) ? "https://" : "http://";
$host = $protocol.$_SERVER['HTTP_HOST'];

$data = [
    // хххх - номер заказ в системе интернет-магазина
    'order_id' => $order['order_id'],

    // +7хххххххххх - мобильный телефон клиента
    'customer_phone' => !empty($order['client_phone']) ? $order['client_phone'] : '',

    // ИМЯ@prodamus.ru - e-mail адрес клиента
    'customer_email' => $order['client_email'],

    // перечень товаров заказа
    'products' => $items_prod,

    // для интернет-магазинов доступно только действие "Оплата"
    'do' => 'pay',

    // url-адрес для возврата пользователя без оплаты
    //           (при необходимости прописать свой адрес)
    'urlReturn' => $host . '/payments/prodamus/fail.php',

    // url-адрес для возврата пользователя при успешной оплате
    //           (при необходимости прописать свой адрес)
    'urlSuccess' => $host . '/payments/prodamus/success.php',

    // служебный url-адрес для уведомления интернет-магазина
    //           о поступлении оплаты по заказу
    // 	         пока реализован только для Advantshop,
    //           формат данных настроен под систему интернет-магазина
    //           (при необходимости прописать свой адрес)
    'urlNotification' => '',

    // код системы интернет-магазина, запросить у поддержки,
    //     для самописных систем можно оставлять пустым полем
    //     (при необходимости прописать свой код)
    'sys' => 'schoolmaster',

    // метод оплаты, выбранный клиентом
    // 	     если есть возможность выбора на стороне интернет-магазина,
    // 	     иначе клиент выбирает метод оплаты на стороне платежной формы
    //       варианты (при необходимости прописать значение):
    // 	AC - банковская карта
    // 	PC - Яндекс.Деньги
    // 	QW - Qiwi Wallet
    // 	WM - Webmoney
    // 	GP - платежный терминал
    'available_payment_methods' => $available_payment_methods,

    // сумма скидки на заказ
    // 	     указывается только в том случае, если скидка
    //       не прменена к товарным позициям на стороне интернет-магазина
    // 	     алгоритм распределения скидки по товарам
    //       настраивается на стороне пейформы
    'discount_value' => 0.00,
    // Валюта (rub/usd/eur)
    'currency' => $selected_currency ?? 'rub',
];

$data['signature'] = Hmac::create($data, $secret_key);

$prodamusCurrencies = [
    'usd' => ['service_name'=>'usd', 'name'=>'Доллар'],
    'eur' => ['service_name'=>'eur', 'name'=>'Евро'],
];


if(!isset($_REQUEST['currency'])) {
    $currencies = Currency::getCurrencyList();

    if(isset($params['enable_usd']) || isset($params['enable_eur'])):?>
        <label class="select-wrap" style="text-align: center;">Оплатить в:<br>
            <select class="select" id="prodamusCurrencySelect">
                <?if((isset($params['enable_rub']) && $params['enable_rub'] == 1) || !$currencies):?>
                    <option value="rub">Рубль</option>
                <?endif;

                foreach($prodamusCurrencies as $currency):?>
                    <?if(isset($params['enable_' . $currency['service_name']]) && $params['enable_' . $currency['service_name']] == 1):?>
                        <option value="<?=$currency['service_name'] ?>"><?= $currency['name'] ?></option>
                    <?endif;
                endforeach;?>
            </select><br>
        </label>
    <?endif;?>


    <form action="<?=$linktoform?>" method="post" id="prodamusForm">
        <?php foreach ($data as $key => $field) { ?>
            <?php if (!is_array($field)) { ?>
                <input type="hidden" name="<?=$key?>" value="<?=$field?>">
            <?php } ?>
        <?php } ?>
        <?php foreach ($data['products'] as $key2 => $product) { ?>
            <?php foreach ($product as $key3 => $field3) { ?>
                <input type="hidden" name="products[<?=$key2?>][<?=$key3?>]" value="<?=$field3?>">
            <?php } ?>
        <?php } ?>
        <input type="submit" class="payment_btn" value="<?=System::Lang('TO_PAY');?>">
    </form>

    <script>
        let order_date = '<?=$order_date;?>';
        let prodamusCurrencySelect = document.getElementById('prodamusCurrencySelect');
        prodamusCurrencySelect.addEventListener('input', async function (e) {
            let currency = e.target.value;
            let response = await fetch('/payments/prodamus/ajax.php' + '?' + 'currency=' + currency + '&order_date=' + order_date);
            let result = await response.json();
            let status = await response.status;

            switch (status) {
                case 201:
                    //меняем ссылку
                    document.getElementById("prodamusForm").innerHTML = result.link;
                    break;
                default:
                    alert('Ошибка продамус!' + result.message);
                    break;
            }
        });
    </script>

    <style>
        .select-wrap::before {
            top: 35px;
        }
        .select-wrap::after {
            top: 28px;
        }
    </style>
<? } else { ?>
    <?php foreach ($data as $key => $field) { ?>
        <?php if (!is_array($field)) { ?>
            <input type="hidden" name="<?=$key?>" value="<?=$field?>">
        <?php } ?>
    <?php } ?>
    <?php foreach ($data['products'] as $key2 => $product) { ?>
        <?php foreach ($product as $key3 => $field3) { ?>
            <input type="hidden" name="products[<?=$key2?>][<?=$key3?>]" value="<?=$field3?>">
        <?php } ?>
    <?php } ?>
    <input type="submit" class="payment_btn" value="<?=System::Lang('TO_PAY');?>">
<?php } ?>
