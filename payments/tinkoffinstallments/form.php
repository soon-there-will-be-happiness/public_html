<?php defined('BILLINGMASTER') or die;
$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('GO_PAY');" : '';
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'go_pay', 'submit');" : '';
$metriks = $ya_goal || $ga_goal ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : '';
$form_parameters = strpos($_SERVER['HTTP_USER_AGENT'], 'Instagram') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'vkShare') === false ? ' '.$metriks : '';

$inv_desc = 'Оплата заказа №'.$order['order_date'];
$payment_name = 'tinkoffinstallments';
$tinkoffinstallments = Order::getPaymentSetting($payment_name);
$params = unserialize(base64_decode($tinkoffinstallments['params']));

$order_items = Order::getOrderItems($order['order_id']);
$receipt_items = [];
$sum = 0;
if ($order_items) {
    foreach ($order_items as $order_item) {
        $receipt_items[] = [
            'name' => $order_item['product_name'],
            'price' => $order_item['price'],
            'quantity' => 1,
        ];
        $sum += $order_item['price'];
    }
};

if (isset($params['selected_codes'])) {
    $selected_installments = [
        "installment_0_0_4_5" => "Рассрочка на 4 месяца",
        "installment_0_0_6_6" => "Рассрочка на 6 месяцев",
        "installment_0_0_10_10" => "Рассрочка на 10 месяцев",
        "installment_0_0_12_11" => "Рассрочка на 12 месяцев",
    ];

    $custom = [];
    if (isset($params['custom_codes'])) {
        foreach ($params['custom_codes'] as $codeId => $code) {
            if (!trim($code["name"]) || !trim($code["code"])) {
                continue;
            }
            $custom[$code['code']] = $code['name'];
        }
    }

    $final_codes = [];

    foreach ($params['selected_codes'] as $selectedCode) {

        if (key_exists($selectedCode, $selected_installments)) {

            $final_codes[$selectedCode] = $selected_installments[$selectedCode];

        } elseif (key_exists($selectedCode, $custom)) {

            $final_codes[$selectedCode] = $custom[$selectedCode];

        }
    }
} elseif (isset($params['promo_code']) && !empty($params['promo_code'])) {
    $final_codes[$params['promo_code']] = "По умолчанию";
} else {
    $final_codes['default'] = "По-умолчанию";
}

?>

<script src="https://forma.tinkoff.ru/static/onlineScript.js"></script>
<script>
    function tinkoff_installments () {
        let values = {
            contact: {
                mobilePhone: '<?=$order['client_phone'];?>',
                email: '<?=$order['client_email'];?>'
            }
        };
        console.log(SelectedInstallment);

        tinkoff.create<?if($params['test_mode']):?>Demo<?endif;?>({
            shopId: '<?=$params['shop_id'];?>',
            showcaseId: '<?=$params['showcase_id'];?>',
            items: <?=json_encode($receipt_items, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);?>,
            promoCode: SelectedInstallment,
            orderNumber: "<?=$order['order_id'];?>",
            webhookURL: '<?=$this->settings['script_url']."/payments/$payment_name/result.php";?>',
            successURL: '<?=$this->settings['script_url']."/payments/$payment_name/success.php";?>',
            failURL: '<?=$this->settings['script_url']."/payments/$payment_name/fail.php";?>',
            sum: <?=$sum;?>,
            values: values<?if($params['test_mode'] && $params['demo_flow']):?>,
            demoFlow: <?="'{$params['demo_flow']}'";endif;?>
        });
    };
</script>
<div style="text-align: right">
    <label class="select-wrap" style="text-align: center;">Выбор рассрочки: <br>
        <select class="select" id="tinkoffInstallmentSelect">
            <?php foreach ($final_codes as $key => $installment) { ?>
                <option value="<?=$key?>"><?=$installment?></option>
            <?php } ?>
        </select><br>
    </label>
    <input type="button" class="payment_btn" value="Оформить рассрочку" onclick="tinkoff_installments()"/>
</div>
<style>
    .select-wrap::before {
        top: 35px;
    }
    .select-wrap::after {
        top: 28px;
    }
</style>
<script>
    let SelectedInstallment;
    function updateTinkoffSelect(e) {
        SelectedInstallment = e.target.value;
    }
    document.getElementById("tinkoffInstallmentSelect").addEventListener("input", updateTinkoffSelect);//При смене
    SelectedInstallment = document.getElementById("tinkoffInstallmentSelect").value;//При инициализации
</script>