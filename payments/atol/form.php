<?php defined('BILLINGMASTER') or die;

$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('CUSTOM_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'custom_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? $metriks : '';

// URL для регистрации платежа через API АТОЛ Pay
$atol_url = 'https://new-api-mobile.atolpay.ru/v1/ecom/payments';

// Параметры для регистрации платежа
$payment_data = [
    'amount' => $total * 100, // сумма в копейках
    'orderId' => uniqid(), // уникальный идентификатор заказа
    'sessionType' => 'oneStep', // одностадийный платеж
    'additionalProps' => [
        'returnUrl' => 'https://ваш_сайт/успех_оплаты',
        'notificationUrl' => 'https://ваш_сайт/callback',
    ],
    'receipt' => [
        'buyer' => [
            'email' => $user_email,
        ],
        'positions' => [
            [
                'name' => 'Товар', 
                'price' => $total * 100, 
                'quantity' => 1000, 
                'measure' => 0, 
                'paymentMethod' => 0, 
                'paymentSubject' => 0, 
                'tax' => 0
            ]
        ]
    ]
];

// Инициализация платежа через API
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $atol_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payment_data));
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ваш_токен' // добавьте токен авторизации
]);

$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response, true);
$payment_url = $result['paymentUrl'] ?? '';

?>

<a class="payment_btn payment_btn-link" href="#ModalCompanyPay" data-uk-modal><?=System::Lang('ISSUE_ORDER');?></a>

<div id="ModalCompanyPay" class="uk-modal">
  <div class="uk-modal-dialog uk-modal-dialog-3">
    <div class="userbox modal-userbox-3">
      <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
      <form enctype="application/x-www-form-urlencoded" action="<?=$payment_url;?>" method="POST"<?=$form_parameters;?>>
        <h3 class="modal-head-2">Итого к оплате: <?php echo $total; ?> <?php echo $this->settings['currency'];?></h3>
        <?php $params = unserialize(base64_decode($payment['params'])); echo $params['instruct'];?>
        <hr>
        <p class="pay-total">К оплате: <strong><?php echo $total; ?> <?php echo $this->settings['currency'];?></strong></p>
        <div>
          <p><label><?=System::Lang('NAME_COMPANY');?>: <input type="text" name="organization" required="required"></label></p>
          <div class="modal-form-line"><label>ИНН/КПП:</label><input type="text" name="inn" required="required"></div>
          <div class="modal-form-line"><label>БИК:</label><input type="text" name="bik"></div>
          <div class="modal-form-line"><label>Р/счёт:</label><input type="text" name="rs" required="required"></div>
          <div class="modal-form-line"><label>Адрес для отправки закрывающих документов:</label>
            <textarea name="address" cols="35" rows="3"></textarea></div>
          <input type="hidden" name="payment" value="<?php echo $payment['payment_id'];?>">
          <input type="hidden" name="summ" value="<?php echo $total;?>">
          <div class="modal-form-submit mb-0"><input type="submit" name="company_pay" class="order_button btn-green-small" value="<?=System::Lang('ISSUE_ORDER');?>"></div>
        </div>
      </form>
    </div>
  </div>
</div>
