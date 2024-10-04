<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));


//Продублировать в form.php -> $selected_installments
$allowed_installments = [
    "installment_0_0_4_5" => "Рассрочка на 4 месяца",
    "installment_0_0_6_6" => "Рассрочка на 6 месяцев",
    "installment_0_0_10_10" => "Рассрочка на 10 месяцев",
    "installment_0_0_12_11" => "Рассрочка на 12 месяцев"
];


$custom = [];
$customList = [];
if (isset($params['custom_codes'])) { foreach ($params['custom_codes'] as $codeId => $code) {
    if(!trim($code["name"]) || !trim($code["code"]) ) { continue; }
    $custom[$code['code']] = $code['name'];
    $customList[] = $params['custom_codes'][$codeId];
}}
$allowed_installments = array_merge($allowed_installments, $custom);



?>
<div class="row-line">
    <div class="col-1-1">
        <h4 class="h4-border">Параметры</h4>
    </div>

    <div class="col-1-2 mb-40">
        <p><label>Режим работы</label>
            <span class="custom-radio-wrap">
                <label class="custom-radio"><input name="params[test_mode]" type="radio" value="0"<?if(!isset($params['test_mode']) || !$params['test_mode']) echo ' checked'; ?>><span>Рабочий</span></label>
                <label class="custom-radio"><input name="params[test_mode]" type="radio" data-show_on="tinkoff_demo_flow"  value="1"<?if(isset($params['test_mode']) &&  $params['test_mode']) echo ' checked'; ?>><span>Тестовый</span></label>
            </span>
        </p>

        <div class="width-100 hidden mb-20" id="tinkoff_demo_flow"><label>Режим теста (DemoFlow)</label>
            <div class="select-wrap">
                <select name="params[demo_flow]" required="required">
                    <option value="sms"<?if(isset($params['demo_flow']) && $params['demo_flow'] == 'sms') echo ' selected="selected"';?>>Подписание документов через смс</option>
                    <option value="appointment"<?if(isset($params['demo_flow']) && $params['demo_flow'] == 'appointment') echo ' selected="selected"';?>>Подписание документов при встрече</option>
                    <option value="reject"<?if(isset($params['demo_flow']) && $params['demo_flow'] == 'reject') echo ' selected="selected"';?>>Отказ</option>
                </select>
            </div>
        </div>

        <p><label>Идентификатор компании (ShopId)</label>
            <input type="text" name="params[shop_id]" value="<?=$params['shop_id'];?>">
        </p>

        <p><label>Идентификатор сайта (ShowcaseId)</label>
            <input type="text" name="params[showcase_id]" value="<?=$params['showcase_id'];?>">
        </p>

        <p><label>Секретный ключ</label>
            <input type="text" name="params[password]" value="<?=$params['password'];?>">
        </p>


        <div class="width-100"><label><b>Кредит или рассрочка</b>: </label>
            <div class="mult select-wrap">
                <select name="params[selected_codes][]" multiple="multiple" class="multiple-select" size="10">
                    <?foreach ($allowed_installments as $id => $installment) { ?>
                        <option value="<?=$id?>" <?= isset($params['selected_codes']) && in_array($id, $params['selected_codes']) ? " selected" : "" ?>><?=$installment?></option>
                    <?} ?>
                </select>
            </div>
        </div>
        <h4>Кастомные рассрочки</h4>
        <?php $lastId = 0 ?>
        <?php if (!empty($customList)) { foreach ($customList as $codeId => $code) { ?>
            <div style="margin-bottom: 8px;">
                <input type="text" name="params[custom_codes][<?=$codeId?>][name]" value="<?=$code['name']?>" placeholder="Пустое значение удалит этот код рассрочки" style="margin-bottom: 4px;">
                <input type="text" name="params[custom_codes][<?=$codeId?>][code]" value="<?=$code['code']?>" placeholder="Пустое значение удалит этот код рассрочки" style="margin-bottom: 4px;">
            </div>
        <?php $lastId = $codeId + 1; }} ?>
        <p><label>Новый код рассрочки
                <span class="result-item-icon" data-uk-tooltip="" title="Поле имя - то что отображается клиенту при выборе;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Поле код - то, что передается в платежку. Берется из личного кабинета"><i class="icon-answer"></i></span>
            </label>
            <input type="text" name="params[custom_codes][<?=$lastId?>][name]" value="" placeholder="Имя: Рассрочка на 12 месяцев" style="margin-bottom: 4px;">
            <input type="text" name="params[custom_codes][<?=$lastId?>][code]" value="" placeholder="Код: installment_0_0_12_11">
            <input type="submit" style="padding: 8px; margin-top: 8px;" name="savepayments" value="Добавить" class="button save button-green-border-rounding font-bold">
        </p>
    </div>

    <!--<div class="col-1-1">
        <div class="round-block mb-20">
            <p class="width-100 mb-20"><strong>Возможные значения для рассрочки, уточняйте их в личном кабинете Тинькофф</strong></p>

            <p class="width-100">installment_0_0_4_5 — Рассрочка на 4 месяца без первоначального взноса</p>
            <p class="width-100">installment_0_0_6_6 — Рассрочка на 6 месяцев без первоначального взноса</p>
            <p class="width-100">installment_0_0_10_10 — Рассрочка на 10 месяцев без первоначального взноса</p>
            <p class="width-100">installment_0_0_12_11 — Рассрочка на 12 месяцев без первоначального взноса</p>
        </div>
    </div>-->
</div>

<div class="reference-link">
    <a class="button-blue-rounding" target="_blank" href="https://lk.school-master.ru/rdr/47"><i class="icon-info"></i>Справка по расширению</a>
</div>