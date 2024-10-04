<?php defined('BILLINGMASTER') or die;
$params = unserialize(base64_decode($payment['params']));?>

<p><label>Public_ID</label><br />
    <input type="text" name="params[public_id]" value="<?php echo $params['public_id'];?>">
</p>

<p><label>Пароль для API:</label><br />
    <input type="text" name="params[pass_api]" value="<?php echo $params['pass_api'];?>">
</p>

<p><label>Валюта (RUB):</label><br />
    <input type="text" name="params[currency]" value="<?php echo $params['currency'];?>">
</p>

<p><label>Показывать чек бокс для автоплатежей:</label>
    <select name="params[checkbox]">
        <option value="1"<?php if($params['checkbox'] == 1) echo ' selected="selected"';?>>Показать</option>
        <option value="0"<?php if($params['checkbox'] == 0) echo ' selected="selected"';?>>Не показывать</option>
    </select>
</p>

<p>----------------</p>
<p><label>Онлайн касса:</label>
    <select name="params[online_kassa]">
        <option value="0"<?php if($params['online_kassa'] == 0) echo ' selected="selected"';?>>Отключена</option>
        <option value="1"<?php if($params['online_kassa'] == 1) echo ' selected="selected"';?>>Подключена</option>
    </select>
</p>

<p><label>Наименование организации (ФИО ИП):</label><br />
    <input type="text" name="params[full_name]" value="<?php echo $params['full_name'];?>">
</p>

<p><label>ИНН:</label><br />
    <input type="text" name="params[inn]" value="<?php echo $params['inn'];?>">
</p>

<p><label>Система налогообложения:</label>
    <select name="params[taxationsystem]">
        <option value="0"<?php if(isset($params['taxationsystem']) && $params['taxationsystem'] == 0) echo ' selected="selected"';?>>ОСН</option>
        <option value="1"<?php if(isset($params['taxationsystem']) && $params['taxationsystem'] == 1) echo ' selected="selected"';?>>УСН (Доход)</option>
        <option value="2"<?php if(isset($params['taxationsystem']) && $params['taxationsystem'] == 2) echo ' selected="selected"';?>>УСН (Доход - Расход)</option>
        <option value="3"<?php if(isset($params['taxationsystem']) && $params['taxationsystem'] == 3) echo ' selected="selected"';?>>ЕНВД</option>
        <option value="5"<?php if(isset($params['taxationsystem']) && $params['taxationsystem'] == 5) echo ' selected="selected"';?>>Патент</option>
    </select>
</p>

<p><label>Предмет расчёта:</label>
    <select name="params[object]">
        <option value="1"<?php if(isset($params['object']) && $params['object'] == 1) echo ' selected="selected"';?>>Товар</option>
        <option value="4"<?php if(isset($params['object']) && $params['object'] == 4) echo ' selected="selected"';?>>Услуга</option>
        <option value="13"<?php if(isset($params['object']) && $params['object'] == 13) echo ' selected="selected"';?>>Иное</option>
    </select>
</p>

<p><label>Ставка НДС:</label>
    <select name="params[vat_code]">
        <option value=""<?php if($params['vat_code'] === '') echo ' selected="selected"';?>>НДС не облагается</option>
        <option value="0"<?php if($params['vat_code'] === 0) echo ' selected="selected"';?>>НДС по ставке 0%</option>
        <option value="10"<?php if($params['vat_code'] == 10) echo ' selected="selected"';?>>НДС по ставке 10%</option>
        <option value="20"<?php if($params['vat_code'] == 20) echo ' selected="selected"';?>>НДС чека по ставке 20%</option>
        <option value="110"<?php if($params['vat_code'] == 110) echo ' selected="selected"';?>>НДС чека по расчетной ставке 10/110</option>
        <option value="120"<?php if($params['vat_code'] == 120) echo ' selected="selected"';?>>НДС чека по расчетной ставке 20/120</option>
    </select>
</p>

<p><br /></p>
<h4>Рекурренты</h4>
<p><label>Включить синхронизацию реккурентов: </label>
    <span class="custom-radio-wrap">
        <label class="custom-radio"><input name="params[sync]" type="radio" value="1" <?php if(isset($params['sync']) && $params['sync']== 1) echo 'checked';?>><span>Вкл</span></label>
        <label class="custom-radio"><input name="params[sync]" type="radio" value="0" <?php if(!isset($params['sync']) || $params['sync']== 0) echo 'checked';?>><span>Откл</span></label>
    </span>
</p>

<p><label>Отправлять СМС если карта истекла:</label>
    <span class="custom-radio-wrap">
        <label class="custom-radio"><input data-show_on="expiredsms" name="params[expiredCard]" type="radio" value="1" <?php if(isset($params['expiredCard']) && $params['expiredCard']== 1) echo 'checked';?>><span>Вкл</span></label>
        <label class="custom-radio"><input name="params[expiredCard]" type="radio" value="0" <?php if(!isset($params['expiredCard']) || $params['expiredCard']== 0) echo 'checked';?>><span>Откл</span></label>
    </span>
</p>

<div id="expiredsms" class="hidden">
    <label>Текст SMS при истёкшей карте
        <span class="result-item-icon" data-uk-tooltip="" title="SMSC может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
    </label>
    <textarea name="params[expiredsms]" data-counting-characters data-max_length="1000"><?php if(isset($params['expiredsms'])) echo $params['expiredsms'];?></textarea>
    <div class="counting-characters">
        <span class="counting-characters_count"><?=isset($params['expiredsms']) ? strlen($params['expiredsms']) : 0;?></span>/<span class="counting-characters_max-length">1000</span>, sms:
        <span class="counting-characters_count-sms"><?=isset($params['expiredsms']) ? System::getCountSMS($params['expiredsms']) : 0;?></span>
    </div>
    <span class="small">[LINK] - подставляет ссылку на перепривязку карты<br />[CARD] - последние 4 цифры карты</span>
</div>

<p><label>Отправлять СМС если на карте нет денег:</label>
    <span class="custom-radio-wrap">
        <label class="custom-radio"><input data-show_on="nomoneysms" name="params[nomoney]" type="radio" value="1" <?php if(isset($params['nomoney']) && $params['nomoney']== 1) echo 'checked';?>><span>Вкл</span></label>
        <label class="custom-radio"><input name="params[nomoney]" type="radio" value="0" <?php if(!isset($params['nomoney']) || $params['nomoney']== 0) echo 'checked';?>><span>Откл</span></label>
    </span>
</p>

<div id="nomoneysms" class="hidden">
    <label>Текст SMS при недостатке средств на карте
        <span class="result-item-icon" data-uk-tooltip="" title="SMSC может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
    </label>
    <textarea name="params[nomoneysms]" data-counting-characters data-max_length="1000"><?php if(isset($params['nomoneysms'])) echo $params['nomoneysms'];?></textarea>
    <div class="counting-characters">
        <span class="counting-characters_count"><?=isset($params['nomoneysms']) ? strlen($params['nomoneysms']) : 0;?></span>/<span class="counting-characters_max-length">1000</span>, sms:
        <span class="counting-characters_count-sms"><?=isset($params['nomoneysms']) ? System::getCountSMS($params['nomoneysms']) : 0;?></span>
    </div>
    <span class="small">[CARD] - последние 4 цифры карты</span>
</div>

<div class="reference-link">
    <a class="button-blue-rounding" target="_blank" href="https://support.school-master.ru/knowledge_base/item/232534"><i class="icon-info"></i>Справка по расширению</a>
</div>