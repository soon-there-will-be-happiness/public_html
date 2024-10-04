<?php defined('BILLINGMASTER') or die;
$field_list = !in_array($custom_field['field_type'], [CustomFields::FIELD_TYPE_TEXT, CustomFields::FIELD_TYPE_TEXTAREA]) && $custom_field['params'] ? json_decode($custom_field['params'], true) : null;
$field_headers_list = $field_list ? implode(',', $field_list) : null;?>

<form action="" method="POST" id="edit_custom_fields_form">
    <div class="admin_top admin_top-flex">
        <h3 class="traning-title">Редактировать поле</h3>
        <ul class="nav_button">
            <li>
                <input type="submit" name="save_field" value="Сохранить" class="button save button-white font-bold">
            </li>
            <li class="nav_button__last">
                <a class="button red-link uk-modal-close uk-close" href="#close">Закрыть</a>
            </li>
        </ul>
    </div>

    <div class="admin_form">
        <div class="row-line">
            <div class="col-1-2">
                <div class="width-100" title="Введите название поля"><label>Название поля</label>
                    <input type="text" name="field_name" placeholder="" required="required" value="<?=$custom_field['field_name'];?>">
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Пользователь видит поле в профиле?</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="is_show_in_profile" type="radio" value="1"<?if($custom_field['is_show_in_profile']) echo ' checked="checked"';?>><span>Вкл</span></label>
                        <label class="custom-radio"><input name="is_show_in_profile" type="radio" value="0"<?if(!$custom_field['is_show_in_profile']) echo ' checked="checked"';?>><span>Выкл</span></label>
                    </span>
                </div>

                <div class="width-100"><label>Показывать при регистрации?</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="is_show2registration" type="radio" value="1"<?if($custom_field['is_show2registration']) echo ' checked="checked"';?>><span>Вкл</span></label>
                        <label class="custom-radio"><input name="is_show2registration" type="radio" value="0"<?if(!$custom_field['is_show2registration']) echo ' checked="checked"';?>><span>Выкл</span></label>
                    </span>
                </div>

                <div class="width-100"><label>Показывать при оформлении заказа??</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="is_show2order" type="radio" value="1"<?if($custom_field['is_show2order']) echo ' checked="checked"';?>><span>Вкл</span></label>
                        <label class="custom-radio"><input name="is_show2order" type="radio" value="0"<?if(!$custom_field['is_show2order']) echo ' checked="checked"';?>><span>Выкл</span></label>
                    </span>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Тип поля</label>
                    <div class="select-wrap">
                        <select name="field_type">
                            <?php $types = CustomFields::getFieldType();
                            foreach($types as $type => $title):?>
                                <option value="<?=$type?>" id="edit_field_type_<?=$type;?>"<?if($custom_field['field_type'] == $type) echo ' selected="selected"';if(!in_array($type, [CustomFields::FIELD_TYPE_TEXT, CustomFields::FIELD_TYPE_TEXTAREA])) echo ' data-show_on="edit_list_headers"';?>><?=$title?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Пользователь может редактировать?</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="is_editable" type="radio" value="1"<?if($custom_field['is_editable']) echo ' checked="checked"';?>><span>Вкл</span></label>
                        <label class="custom-radio"><input name="is_editable" type="radio" value="0"<?if(!$custom_field['is_editable']) echo ' checked="checked"';?>><span>Выкл</span></label>
                    </span>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Тип данных поля</label>
                    <div class="select-wrap">
                        <select name="field_data_type">
                            <?php $types = CustomFields::getFieldDataType();
                            foreach($types as $data_type => $title):?>
                                <option value="<?=$data_type?>" value="<?=$custom_field['field_data_type'];?>"<?=($custom_field['field_data_type'] == $data_type ? ' selected="selected"' : '');?>><?=$title?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Разрешить парсинг поля в Api</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="is_parse_in_api" type="radio" value="1"<?if($custom_field['is_parse_in_api']) echo ' checked="checked"';?>><span>Вкл</span></label>
                        <label class="custom-radio"><input name="is_parse_in_api" type="radio" value="0"<?if(!$custom_field['is_parse_in_api']) echo ' checked="checked"';?>><span>Выкл</span></label>
                    </span>
                </div>
            </div>

            <div class="col-1-2" id="edit_field_default_value">
                <div class="width-100" title="Введите значение для поля (для полей выбора из списка - номер значения)"><label>Значение поля по умолчанию</label>
                    <input type="text" name="field_default_value" value="<?=$custom_field['default_value'];?>" placeholder="">
                </div>
            </div>

            <div class="col-1-2 hidden" id="edit_list_headers">
                <div class="width-100" title="Введите заголовки списка"><label>Заголовки списка (через запятую)</label>
                    <input type="text" name="list_headers" placeholder="" value="<?=$field_headers_list;?>">
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100" title="Введите значение для сортировки"><label>Сортировка</label>
                    <input type="text" name="field_sort" value="<?=$custom_field['field_sort'];?>" placeholder="">
                </div>
            </div>
        </div>

        <div class="row-line mt-20">
            <div class="col-1-2">
                <div class="width-100"><label>Статус</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="status" type="radio" value="1"<?if($custom_field['status']) echo ' checked="checked"';?>><span>Вкл</span></label>
                        <label class="custom-radio"><input name="status" type="radio" value="0"<?if(!$custom_field['status']) echo ' checked="checked"';?>><span>Выкл</span></label>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="field_id" value="<?=$custom_field['id'];?>">
</form>

<script>
    dependent_blocks();
    $(document).on('change', '#edit_custom_fields_form [name="field_data_type"]', function (e) {
        let $el = $("option:selected", this);
        if ($el.val() == <?=CustomFields::FIELD_DATA_TYPE_INT;?>) {
            let $select = $('#edit_custom_fields_form [name="field_type"]');
            let select_value = $select.val();
            $('#edit_field_type_1, #edit_field_type_4').attr('disabled', 'disabled');
            if (select_value == <?=CustomFields::FIELD_TYPE_CHECKBOX;?> || select_value == <?=CustomFields::FIELD_TYPE_MULTI_SELECT;?>) {
                setTimeout(function(){$select.children('option:nth-child(2)').prop('selected', true);},500);
            }
        } else {
            $('#edit_field_type_1, #edit_field_type_4').removeAttr('disabled');
        }
    });
</script>