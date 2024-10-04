<?defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));
$payment_method = isset($params['payment_method']) ? $params['payment_method'] :'full_prepayment';
$payment_object = isset($params['payment_object']) ? $params['payment_object'] : 'commodity';
$tax = isset($params['tax']) ? $params['tax'] : 'none';
$sno = isset($params['sno']) ? $params['sno'] : 'osn';
$pay_object_delivery = isset($params['pay_object_delivery']) ? $params['pay_object_delivery'] : 'commodity';

$currencies = Currency::getCurrencyList();

$prodamusCurrencies = [
    'rub' => ['service_name'=>'rub', 'name'=>'Рубль'],
    'usd' => ['service_name'=>'usd', 'name'=>'Доллар'],
    'eur' => ['service_name'=>'eur', 'name'=>'Евро'],
];
$walletMethods = [
    'AC' => 'Оплата картой, выпущенной в РФ',
    'ACkz' => 'Оплата картой Казахстана',
    'ACkztjp' => 'Оплата картой всех стран мира, кроме РФ',
    'ACf' => 'Оплата картами стран СНГ, кроме РФ',
    'ACeuruk' => 'Оплата в EUR картой всех стран, кроме РФ и РБ',
    'ACusduk' => 'Оплата в USD картой всех стран, кроме РФ и РБ',
    'ACEURNMBX' => 'Оплата EUR картой всех стран, кроме РФ и РБ',
    'ACUSDNMBX' => 'Оплата в USD картой всех стран, кроме РФ и РБ',
    'ACusdxp' => 'Оплата в USD картой всех стран мира, кроме РФ',

    'SBP' => 'Быстрый платёж, без ввода данных карты. Для карт РФ',
    'PC' => 'Юmoney',

    'QW' => 'Qiwi Wallet',
    'WM' => 'Webmoney',
    'GP' => 'Платежный терминал',
    'sbol' => 'Сбербанк онлайн',
    'invoice' => 'Оплата по счету',

    'installment' => 'Частями от Продамус',
    'installment_5_21' => 'Частями от Продамус на 3 месяца',
    'installment_6_28' => 'Частями от Продамус на 6 месяцев',
    'installment_10_28' => 'Частями от Продамус на 10 месяцев',
    'installment_12_28' => 'Частями от Продамус на 12 месяцев',

    'installment_0_0_3' => 'Рассрочка от Тинькофф на 3 месяца',
    'installment_0_0_6' => 'Рассрочка от Тинькофф на 6 месяцев',
    'installment_0_0_10' => 'Рассрочка от Тинькофф на 10 месяцев',
    'installment_0_0_12' => 'Рассрочка от Тинькофф на 12 месяцев',
    'installment_0_0_24' => 'Рассрочка от Тинькофф на 24 месяца',
    'installment_0_0_36' => 'Рассрочка от Тинькофф на 36 месяцев',

    'credit' => 'Кредит от Тинькофф',

    'vsegdada_installment_0_0_4' => 'Рассрочка Всегда.Да на 4 месяца без переплаты!',
    'vsegdada_installment_0_0_6' => 'Рассрочка от ВсегдаДа на 6 месяцев без переплаты!',
    'vsegdada_installment_0_0_10' => 'Рассрочка от ВсегдаДа на 10 месяцев без переплаты!',
    'vsegdada_installment_0_0_12' => 'Рассрочка от ВсегдаДа на 12 месяцев без переплаты!',
    'vsegdada_installment_0_0_24' => 'Рассрочка от ВсегдаДа на 24 месяца без переплаты!',
];
?>

<div class="row-line">
    <div class="col-1-2">
        <h4 class="h4-border">Параметры</h4>
        <p><label>Адрес платежной страницы</label>
            <input type="text" name="params[prodamus_site_name]" value="<?=$params['prodamus_site_name'];?>">
        </p>
        
        <p><label>Секретный ключ</label>
            <input type="text" name="params[prodamus_secret_key]" value="<?=$params['prodamus_secret_key'];?>">
        </p>
        
        <h4 class="h4-border">Параметры</h4>
        
        <div class="width-100"><label>Разрешить оплату в валютах:</label>
            <!-- TODO выбор валюты из бд -->
            <?if(isset($prodamusCurrencies) && count($prodamusCurrencies) > 0):
                if(is_array($currencies)):
                    foreach($prodamusCurrencies as $key => $currency):?>
                        <div style="margin-bottom: 15px;">
                            <label class="custom-chekbox-wrap">
                                <input type="checkbox" name="params[enable_<?=$currency['service_name']?>]" value="1"<?=isset($params["enable_{$currency['service_name']}"]) && key_exists("enable_{$currency['service_name']}", $params) ? ' checked' : ''?>>
                                <span class="custom-chekbox"></span><?=$currency['name']?>
                            </label>

                            <?if($currency['service_name'] != 'rub'):?>
                                <label>Прикрепить к:</label>
                                <span class="select-wrap">
                                    <select name="params[<?=$currency['service_name'] ?>ToCurrencyId]">
                                        <?foreach ($currencies as $stdCurrency) { ?>
                                            <option value="<?=$stdCurrency['id'] ?>" <?=isset($params[$currency['service_name'] . 'ToCurrencyId']) && $params[$currency['service_name'] . 'ToCurrencyId'] == $stdCurrency['id'] ? ' selected' : '' ?>>
                                                <?=$stdCurrency['name'] ?>
                                            </option>
                                        <?} ?>
                                    </select>
                                </span>
                            <?endif;?>
                        </div>
                    <?endforeach;
                else:
                    echo "У вас нет доп.валют";
                endif;
            endif;?>
        </div>
    </div>

    <div class="col-1-2">
        <div style="margin-bottom: 215px;">&nbsp;</div>
        <div class="width-100"><label><b>Основная валюта</b>: </label>
            <div class="mult select-wrap">
                <select name="params[available_payment_methods][]" multiple="multiple" class="multiple-select" size="10">
                    <?foreach ($walletMethods as $payMethod => $desc) { ?>
                        <option value="<?=$payMethod?>" <?=isset($params['available_payment_methods']) &&  in_array($payMethod, $params['available_payment_methods']) ? ' selected' : "" ?>><?=$desc?></option>
                    <?} ?>
                </select>
            </div>
        </div>

        <div class="width-100"><label><b>Доллар</b>: </label>
            <div class="mult select-wrap">
                <select name="params[available_currency_payment_methods][usd][]" multiple="multiple" class="multiple-select" size="10">
                    <?foreach ($walletMethods as $payMethod => $desc) { ?>
                        <option value="<?=$payMethod?>" <?=isset($params['available_currency_payment_methods']['usd']) && is_array($params['available_currency_payment_methods']['usd']) &&  in_array($payMethod, $params['available_currency_payment_methods']['usd']) ? ' selected' : "" ?> ><?=$desc?></option>
                    <?} ?>
                </select>
            </div>
        </div>

        <div class="width-100"><label><b>Евро</b>: </label>
            <div class="mult select-wrap">
                <select name="params[available_currency_payment_methods][eur][]" multiple="multiple" class="multiple-select" size="10">
                    <?foreach ($walletMethods as $payMethod => $desc) { ?>
                        <option value="<?=$payMethod?>" <?=isset($params['available_currency_payment_methods']['eur']) && is_array($params['available_currency_payment_methods']['eur']) && in_array($payMethod, $params['available_currency_payment_methods']['eur']) ? ' selected' : "" ?> ><?=$desc?></option>
                    <?} ?>
                </select>
            </div>
        </div>
    </div>
</div>
<div class="reference-link">
    <a class="button-blue-rounding" target="_blank" href="https://support.school-master.ru/knowledge_base/item/232107"><i class="icon-info"></i>Справка по расширению</a>
</div>
<style>
    .select-wrap::before {
        top: 8px;
    }
    .select-wrap::after {
        top: 0;
    }
    .select-wrap .select2-selection__rendered:before, .mult.select-wrap:after, .select-wrap .select2-selection__rendered:after {
        content: none !important;
    }
</style>