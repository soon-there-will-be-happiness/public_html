<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php');?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php');?>

<div class="main">
    <div class="top-wrap">
        <h1>Кастомные поля</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a>
            <a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/users">Пользователи</a></li>
        <li>Кастомные поля</li>
    </ul>

    <div class="nav_gorizontal">
        <ul class="nav_gorizontal__ul flex-right">
            <li class="nav_gorizontal__parent-wrap">
                <a class="button-red-rounding" href="#add_field" data-uk-modal>Добавить поле</a>
            </li>
        </ul>
    </div>

    <?php if(isset($_GET['success'])):?>
        <div class="admin_message">Успешно!</div>
    <?php endif;
    if (isset($_GET['fail'])):?>
        <div class="admin_warning">Ошибка при сохранении данных!</div>
    <?php endif;
    if(CustomFields::hasError()) CustomFields::showError();?>

    <div class="admin_form">
        <div class="row-line">
            <div class="col-2-3">
                <?php $custom_fields = CustomFields::getDataFields(null);
                if($custom_fields):?>
                    <table class="table custom-fields">
                        <thead>
                            <tr>
                                <th class="text-left" valign="top">Название поля</th>
                                <th class="text-left" valign="top">Системное</th>
                                <th class="text-left" valign="top">Тип поля</th>
                                <th class="text-left" valign="top">Видимость</th>
                                <th class="text-left" valign="top">Api</th>
                                <th valign="top">Act</th>
                            </tr>
                        </thead>

                        <body>
                            <?php foreach($custom_fields as $key => $custom_field):?>
                                <tr>
                                    <td class="text-left">
                                        <div class="status-info-wrap" style="width: 240px">
                                            <div class="status-info mr-20">
                                                <i class="status-<?=$custom_field['status'] ? 'on' :'off';?>"></i>
                                            </div>
                                            <i class="visible-<?=$custom_field['is_show_in_profile'] ? 'on' :'off';?>"></i>
                                            <a href="#edit_field" data-uk-modal data-field_id="<?=$custom_field['id'];?>"><?=$custom_field['field_name'];?></a>
                                        </div>
                                    </td>

                                    <td class="text-left">
                                        <?=$custom_field['column_name'];?>
                                    </td>

                                    <td class="text-left">
                                        <span><?=CustomFields::getFieldType($custom_field['field_type']);?></span>
                                    </td>

                                    <td class="text-left">
                                        <?=$custom_field['is_show_in_profile'] ? 'Да' : 'Нет';?>
                                    </td>

                                    <td class="text-left">
                                        <?=$custom_field['is_parse_in_api'] ? 'Да' : 'Нет';?>
                                    </td>

                                    <td>
                                        <a onclick="return confirm('Вы уверены?')" href="/admin/users/custom-field/del/<?="{$custom_field['id']}?token={$_SESSION['admin_token']}";?>" title="Удалить">
                                            <i class="fas fa-times color-red" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </body>
                    </table>
                <?php else:
                    echo 'Полей пока нет';
                endif;?>
            </div>
        </div>
    </div>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<div id="add_field" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-dialog-3">
        <div class="userbox modal-userbox-3">
            <form action="" method="POST" id="add_custom_fields_form">
                <div class="admin_top admin_top-flex">
                    <h3 class="traning-title">Добавить поле</h3>
                    <ul class="nav_button">
                        <li>
                            <input type="submit" name="add_field" value="Сохранить" class="button save button-white font-bold">
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
                                <input type="text" name="field_name" placeholder="" required="required">
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Пользователь видит поле в профиле?</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="is_show_in_profile" type="radio" value="1"><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="is_show_in_profile" type="radio" value="0" checked><span>Выкл</span></label>
                                </span>
                            </div>

                            <div class="width-100"><label>Показывать при регистрации?</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="is_show2registration" type="radio" value="1"><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="is_show2registration" type="radio" value="0" checked><span>Выкл</span></label>
                                </span>
                            </div>

                            <div class="width-100"><label>Показывать при оформлении заказа?</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="is_show2order" type="radio" value="1"><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="is_show2order" type="radio" value="0" checked><span>Выкл</span></label>
                                </span>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Тип поля</label>
                                <div class="select-wrap">
                                    <select name="field_type">
                                        <?php $types = CustomFields::getFieldType();
                                        foreach($types as $type => $title):?>
                                            <option value="<?=$type?>" id="add_field_type_<?=$type;?>" <?if(!in_array($type, [CustomFields::FIELD_TYPE_TEXT, CustomFields::FIELD_TYPE_TEXTAREA])) echo ' data-show_on="add_list_headers"';?>><?=$title?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Пользователь может редактировать?</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="is_editable" type="radio" value="1"><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="is_editable" type="radio" value="0" checked><span>Выкл</span></label>
                                </span>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Тип данных поля</label>
                                <div class="select-wrap">
                                    <select name="field_data_type">
                                        <?php $types = CustomFields::getFieldDataType();
                                        foreach($types as $data_type => $title):?>
                                            <option value="<?=$data_type?>"><?=$title?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100"><label>Разрешить парсинг поля в Api</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="is_parse_in_api" type="radio" value="1"><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="is_parse_in_api" type="radio" value="0" checked><span>Выкл</span></label>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row-line mt-20">
                        <div class="col-1-2" id="add_field_default_value">
                            <div class="width-100" title="Введите значение для поля (для полей выбора из списка - номер значения)"><label>Значение поля по умолчанию</label>
                                <input type="text" name="field_default_value" placeholder="">
                            </div>
                        </div>

                        <div class="col-1-2 hidden" id="add_list_headers">
                            <div class="width-100" title="Введите заголовки списка"><label>Заголовки списка (через запятую)</label>
                                <input type="text" name="list_headers" placeholder="" value="">
                            </div>
                        </div>


                        <div class="col-1-2">
                            <div class="width-100" title="Введите значение для сортировки"><label>Сортировка</label>
                                <input type="text" name="field_sort" placeholder="" value="<?=CustomFields::getCountFields(null, null)+1;?>">
                            </div>
                        </div>
                    </div>

                    <div class="row-line mt-20">
                        <div class="col-1-2">
                            <div class="width-100"><label>Статус</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1" checked><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0"><span>Выкл</span></label>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit_field" class="uk-modal">
    <div class="uk-modal-dialog uk-modal-dialog-3">
        <div class="userbox modal-userbox-3"></div>
    </div>
</div>

<script>
    $(function() {
        $('a[href="#edit_field"]').click(function() {
            $.post('/admin/users/custom-fields', {field_id: $(this).data('field_id')}, function(html) {
                if (html) {
                    $('#edit_field .userbox').html(html);
                }
            });
        });

        $(document).on('change', '#add_custom_fields_form [name="field_data_type"]', function (e) {
            let $el = $("option:selected", this);
            if ($el.val() == <?=CustomFields::FIELD_DATA_TYPE_INT;?>) {
                let $select = $('#add_custom_fields_form [name="field_type"]');
                let select_value = $select.val();
                $('#add_field_type_1, #add_field_type_4').attr('disabled', 'disabled');
                if (select_value == <?=CustomFields::FIELD_TYPE_CHECKBOX;?> || select_value == <?=CustomFields::FIELD_TYPE_MULTI_SELECT;?>) {
                    setTimeout(function(){$select.children('option:nth-child(2)').prop('selected', true);},500);
                }
            } else {
                $('#add_field_type_1, #add_field_type_4').removeAttr('disabled');
            }
        });
    });
</script>
</body>
</html>