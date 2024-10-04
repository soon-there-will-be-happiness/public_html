<?php defined('BILLINGMASTER') or die;

$old_v = defined('CURR_VER') 
    ? version_compare(CURR_VER, '3.7.0', '<')
    : false;

$params = unserialize(base64_decode($payment['params']));


// Создаем форму вне этой формы как окно
?>
<script type="text/javascript">
    jQuery('<div>', {
        id: 'refund_form_modal',
        class: 'uk-modal'
    }).appendTo('body');
    $.ajax({
        type: "POST",
        url: '/payments/dolyame/lib/ajax.php?method=refund',
        data: {
            token: '<?=$_SESSION["admin_token"]?>',
            show: 'form'
        },
        success: function(data) {
            $('#refund_form_modal').html(data);
        }
    });
</script>

<h4 class="h4-border">Параметры 
    <a style="font-size: 12px;" href="#refund_form_modal" data-uk-modal="{center:true}">Возврат средств покупателю</a>
</h4>
<div class="row-line">
    <div class="col-1-2">
        <p>
            <label>Логин:</label>
            <input type="text" name="params[login]" value="<?=$params['login'];?>">
        </p>

        <p>
            <label>Пароль:</label>
            <input type="text" name="params[password]" value="<?=$params['password'];?>">
        </p>

        <input type="hidden" name="file_dir" value="<?=__DIR__ . '\files'?>">

        <? if($old_v): ?>
            <p style="font-size: 12px; color: #00000070;">Загрузите сертификат и ключ на сервер, в папку "/payments/dolyame/files/"<br>Укажите название файла ключа и сертификата ниже</p>
        <? endif; ?>

        <div class="width-100" id="file-cert-upload">
            <label>Сертификат:</label>
            <? if($old_v): ?>
                <input type="text" name="params[cert_path]" placeholder="Укажите название файла-сертификата" <?= !isset($params['cert_path']) ?: "value=\"{$params['cert_path']}\""?>>
            <? else: ?>
                <input type="file" name="cert" accept=".pem">
            <? endif; ?>
        </div>
        <? if(!$old_v && isset($params['cert_path']) && !empty($params['cert_path'])): ?>
            <input type="hidden" name="params[cert_path]" value="<?=$params['cert_path']?>">
            <script type="text/javascript">
                setTimeout(function(){
                    document.getElementById("file-cert-upload").getElementsByClassName("jq-file__name")[0].innerHTML = "<?=$params['cert_path']?>";
                }, 300 * 2);
            </script>
        <? endif; ?>

        <div class="width-100" id="file-key-upload">
            <label>Ключ:</label>
            <? if($old_v): ?>
                <input type="text" name="params[key_path]" placeholder="Укажите название файла-ключа" <?= !isset($params['key_path']) ?: "value=\"{$params['key_path']}\""?>>
            <? else: ?>
                <input type="file" name="key" accept=".key">
            <? endif; ?>
        </div>
        <? if(!$old_v && isset($params['key_path']) && !empty($params['key_path'])): ?>
            <input type="hidden" name="params[key_path]" value="<?=$params['key_path']?>">
            <script type="text/javascript">
                setTimeout(function(){
                    document.getElementById("file-key-upload").getElementsByClassName("jq-file__name")[0].innerHTML = "<?=$params['key_path']?>";
                }, 300 * 2);
            </script>
        <? endif; ?>


    </div>
    <div class="col-1-2">
        <p>
            <label>Ставка НДС:</label>
            <select name="params[tax_type]">
                <option value="none"<?php if(!isset($params['tax_type']) || $params['tax_type'] == 'none') echo ' selected="selected"';?>
                >Без НДС</option>

                <option value="vat0"<?php if(@ $params['tax_type'] == 'vat0') echo ' selected="selected"';?>
                >НДС по ставке 0%</option>

                <option value="vat10"<?php if(@ $params['tax_type'] == 'vat10') echo ' selected="selected"';?>
                >НДС по ставке 10%</option>

                <option value="vat20"<?php if(@ $params['tax_type'] == 'vat20') echo ' selected="selected"';?>
                >НДС по ставке 20%</option>
            </select>
        </p>

        <p>
            <label>Тип рассчёта:</label>
            <select name="params[payment_method]">
                <option value="full_payment"<?php if(!isset($params['payment_method']) || $params['payment_method'] == 'full_payment') echo ' selected="selected"';?>
                >Полный расчёт</option>

                <option value="full_prepayment"<?php if(@ $params['payment_method'] == 'full_prepayment') echo ' selected="selected"';?>
                >Предоплата 100%</option>

                <option value="prepayment"<?php if(@ $params['payment_method'] == 'prepayment') echo ' selected="selected"';?>
                >Предоплата</option>

                <option value="credit"<?php if(@ $params['payment_method'] == 'credit') echo ' selected="selected"';?>
                >Кредит</option>

            </select>
        </p>
        
        <p>
            <label>Тип товара:</label>
            <select name="params[payment_object]">
                <option value=""<?php if(@ $params['payment_object'] == "") echo ' selected="selected"';?>
                >Не указан</option>

                <option value="commodity"<?php if(@ $params['payment_object'] == 'commodity') echo ' selected="selected"';?>
                >Товар</option>

                <option value="service"<?php if(!isset($params['payment_object']) || $params['payment_object'] == 'service') echo ' selected="selected"';?>
                >Услуга</option>

                <option value="payment"<?php if(@ $params['payment_object'] == 'payment') echo ' selected="selected"';?>
                >Платёж</option>

                <option value="another"<?php if(@ $params['payment_object'] == 'another') echo ' selected="selected"';?>
                >Другое</option>

            </select>
        </p>
    </div>
</div>

<div class="reference-link">
    <a class="button-blue-rounding" target="_blank" href="https://support.school-master.ru/knowledge_base/item/272577"><i class="icon-info"></i>Справка по расширению</a>
</div>