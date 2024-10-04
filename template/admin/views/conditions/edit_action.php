<?php defined('BILLINGMASTER') or die;

$group_list = User::getUserGroups();
$planes = Member::getPlanes();
$add_groups = isset($cond_action['params']['add_groups']) ? $cond_action['params']['add_groups'] : null;
$del_groups = isset($cond_action['params']['del_groups']) ? $cond_action['params']['del_groups'] : null;
$add_to_membership = isset($cond_action['params']['add_to_membership']) ? $cond_action['params']['add_to_membership'] : null;
$del_to_membership = isset($cond_action['params']['del_to_membership']) ? $cond_action['params']['del_to_membership'] : null;
$delivery_list = Responder::getDeliveryList(2,1,100);
$subscribe = isset($cond_action['params']['subscribe_delivery']) ? $cond_action['params']['subscribe_delivery'] : null;
$unsubscribe = isset($cond_action['params']['unsubscribe_delivery']) ? $cond_action['params']['unsubscribe_delivery'] : null;?>

<form action="/admin/conditions/edit-action/<?=isset($cond_action['action_id']) ? $cond_action['action_id'] : 0;?>?filter_model=<?=$filter_model;?>" method="POST">
    <div class="admin_top admin_top-flex">
        <div class="flex">
            <div class="poll-answers-item__icon-add-question"></div>
            <h3 class="traning-title"><?=Conditions::getActions($cond_action['action']);?></h3>
        </div>

        <ul class="nav_button">
            <li>
                <input type="submit" name="save_action" value="Сохранить" class="button save button-white font-bold">
            </li>
            <li class="nav_button__last">
                <a class="button red-link uk-modal-close uk-close" href="#close">Закрыть</a>
            </li>
        </ul>
    </div>

    <div class="admin_form">
        <div class="row-line">
            <div class="col-1-1 mb-0">
                <h4>Основное</h4>
            </div>

            <div class="col-1-2">
                <div class="width-100"><label>Действие:</label>
                    <div class="select-wrap">
                        <select name="action">
                            <option value="<?=Conditions::ACTION_ADD_GROUP;?>" data-show_on="edit_action_add_group"<?if($cond_action['action'] == Conditions::ACTION_ADD_GROUP) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_ADD_GROUP);?></option>

                            <option value="<?=Conditions::ACTION_DEL_GROUP;?>" data-show_on="edit_action_del_group"<?if($cond_action['action'] == Conditions::ACTION_DEL_GROUP) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_DEL_GROUP);?></option>

                            <option value="<?=Conditions::ACTION_ADD_TO_MEMBERSHIP;?>" data-show_on="edit_action_add_to_membership"<?if($cond_action['action'] == Conditions::ACTION_ADD_TO_MEMBERSHIP) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_ADD_TO_MEMBERSHIP);?></option>

                            <? /*?>
                            <option value="<?=Conditions::ACTION_DEL_TO_MEMBERSHIP;?>" data-show_on="edit_action_del_to_membership"<?if($cond_action['action'] == Conditions::ACTION_DEL_TO_MEMBERSHIP) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_DEL_TO_MEMBERSHIP);?></option>
                            <? */?>

                            <option value="<?=Conditions::ACTION_SUBSCRIBE_MAILING;?>" data-show_on="edit_action_subscribe_mailing"<?if($cond_action['action'] == Conditions::ACTION_SUBSCRIBE_MAILING) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_SUBSCRIBE_MAILING);?></option>

                            <option value="<?=Conditions::ACTION_UNSUBSCRIBE_MAILING;?>" data-show_on="edit_action_unsubscribe_mailing"<?if($cond_action['action'] == Conditions::ACTION_UNSUBSCRIBE_MAILING) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_UNSUBSCRIBE_MAILING);?></option>

                            <option value="<?=Conditions::ACTION_SEND_LETTER;?>" data-show_on="edit_action_send_letter"<?if($cond_action['action'] == Conditions::ACTION_SEND_LETTER) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_SEND_LETTER);?></option>

                            <option value="<?=Conditions::ACTION_SEND_SMS;?>" data-show_on="edit_action_send_sms"<?if($cond_action['action'] == Conditions::ACTION_SEND_SMS) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_SEND_SMS);?></option>

                            <option value="<?=Conditions::ACTION_SEND_WEBHOOK;?>" data-show_on="edit_action_send_webhook"<?if($cond_action['action'] == Conditions::ACTION_SEND_WEBHOOK) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_SEND_WEBHOOK);?></option>

                            <?if($filter_model == SegmentFilter::FILTER_TYPE_USERS):?>
                                <option value="<?=Conditions::ACTION_DELETE_USER;?>" <?if($cond_action['action'] == Conditions::ACTION_DELETE_USER) echo ' selected="selected"';?>>
                                    <?=Conditions::getActions(Conditions::ACTION_DELETE_USER);?></option>
                            <?endif;?>
                            <option value="<?=Conditions::ACTION_USER_TO_PARTNER;?>" <?if($cond_action['action'] == Conditions::ACTION_USER_TO_PARTNER) echo ' selected="selected"';?>>
                                <?=Conditions::getActions(Conditions::ACTION_USER_TO_PARTNER);?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-1-2">
                <div class="width-100 hidden" id="edit_action_add_group"><label>Выберите группы:</label>
                    <select class="multiple-select" size="7" multiple="multiple" name="params[add_groups][]">
                        <?if($group_list):
                            foreach($group_list as $user_group):?>
                                <option value="<?=$user_group['group_id'];?>"<?if($add_groups && in_array($user_group['group_id'], $add_groups)) echo ' selected="selected"';?>>
                                    <?=$user_group['group_title'];?>
                                </option>
                            <?php endforeach;
                        endif;?>
                    </select>
                </div>

                <div class="width-100 hidden" id="edit_action_del_group"><label>Выберите группы:</label>
                    <select class="multiple-select" size="7" multiple="multiple" name="params[del_groups][]">
                        <?$group_list = User::getUserGroups();
                        if($group_list):
                            foreach($group_list as $user_group):?>
                                <option value="<?=$user_group['group_id'];?>"<?if($del_groups && in_array($user_group['group_id'], $del_groups)) echo ' selected="selected"';?>>
                                    <?=$user_group['group_title'];?>
                                </option>
                            <?php endforeach;
                        endif;?>
                    </select>
                </div>

                <div class="width-100 hidden" id="edit_action_add_to_membership"><label>Выберите мембершип подписки:</label>
                    <select class="multiple-select" size="7" multiple="multiple" name="params[add_to_membership][]">
                        <?if($planes):
                            foreach($planes as $plane):?>
                                <option value="<?=$plane['id'];?>"<?if($add_to_membership && in_array($plane['id'], @$add_to_membership)) echo ' selected="selected"';?>>
                                    <?=$plane['name'];?>
                                </option>
                            <?php endforeach;
                        endif;?>
                    </select>
                </div>

                <div class="width-100 hidden" id="edit_action_del_to_membership"><label>Выберите мембершип подписки:</label>
                    <select class="multiple-select" size="7" multiple="multiple" name="params[del_to_membership][]">
                        <? if($planes):
                            foreach($planes as $plane):?>
                                <option value="<?=$plane['id'];?>"<?if($del_to_membership && in_array($plane['id'], $del_to_membership)) echo ' selected="selected"';?>>
                                    <?=$plane['name'];?>
                                </option>
                            <?php endforeach;
                        endif;?>
                    </select>
                </div>

                <div class="width-100 hidden" id="edit_action_subscribe_mailing"><label>Выберите рассылку</label>
                    <div class="select-wrap">
                        <select name="params[subscribe_delivery]">
                            <?if($delivery_list):
                                foreach($delivery_list as $delivery):?>
                                    <option value="<?=$delivery['delivery_id'];?>"<?if($subscribe && $subscribe == $delivery['delivery_id']) echo ' selected="selected"';?>>
                                        <?=$delivery['name'];?>
                                    </option>
                                <?php endforeach;
                            endif;?>
                        </select>
                    </div>
                </div>

                <div class="width-100 hidden" id="edit_action_unsubscribe_mailing"><label>Выберите рассылку</label>
                    <select class="multiple-select" size="5" multiple="multiple" name="params[unsubscribe_delivery][]">
                        <?if($delivery_list):
                            foreach($delivery_list as $delivery):?>
                                <option value="<?=$delivery['delivery_id'];?>"<?if($unsubscribe && in_array($delivery['delivery_id'], $unsubscribe)) echo ' selected="selected"';?>>
                                    <?=$delivery['name'];?>
                                </option>
                            <?php endforeach;
                        endif;?>
                    </select>
                </div>
            </div>

            <div class="col-1-1 hidden" id="edit_action_send_letter">
                <p class="width-100"><label>Тема письма:</label>
                    <input type="text" name="params[subject]" value="<?=isset($cond_action['params']['subject']) ? $cond_action['params']['subject'] : '';?>">
                </p>

                <p class="width-100"><label>Текст письма:</label>
                    <textarea class="editor" name="params[letter]"><?=isset($cond_action['params']['letter']) ? $cond_action['params']['letter'] : '';?></textarea>
                </p>

                <p class="width-100"><label>Получатель:</label>
                    <span class="custom-radio-wrap">
                        <label class="custom-radio"><input name="params[letter_recipient_type]" type="radio" value="1" <?if(!isset($cond_action['params']['letter_recipient_type']) || $cond_action['params']['letter_recipient_type'] == 1) echo ' checked="checked"';?>><span>Из сегмента</span></label>
                        <label class="custom-radio"><input name="params[letter_recipient_type]" type="radio" value="2" <?if(isset($cond_action['params']['letter_recipient_type']) && $cond_action['params']['letter_recipient_type'] == 2) echo ' checked="checked"';?> data-show_on="edit_action_letter_email"><span>Кастомный e-mail</span></label>
                    </span>
                </p>

                <p class="width-100 hidden" id="edit_action_letter_email" style="width: 50%;"><label title="Если нужно отправить нескольким отправителям, перечислить через запятую">E-mail:</label>
                    <input type="text" name="params[letter_email]" value="<?=isset($cond_action['params']['letter_email']) ? $cond_action['params']['letter_email'] : '';?>">
                </p>

                <p><?if($filter_model):
                        foreach($vars = Conditions::getFields2Messages($filter_model) as $var => $title):
                            echo "$var - $title <br />";
                        endforeach;
                    endif;?>
                </p>
            </div>

            <div class="col-1-1 hidden" id="edit_action_send_sms">
                <div class="width-100">
                    <label>Текст SMS сообщения
                        <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
                    </label>
                    <textarea name="params[message]" data-counting-characters="" data-max_length="1000"><?=$sms_text = isset($cond_action['params']['message']) ? $cond_action['params']['message'] : '';?></textarea>
                    <div class="counting-characters">
                        <span class="counting-characters_count"><?=strlen($sms_text);?></span>/<span class="counting-characters_max-length">1000</span>, sms:
                        <span class="counting-characters_count-sms"><?=System::getCountSMS($sms_text);?></span>
                    </div>
                </div>

                <p><?if($filter_model):
                    foreach($vars as $var => $title):
                            echo "$var - $title <br />";
                        endforeach;
                    endif;?>
                </p>
            </div>

            <div class="col-1-1 hidden" id="edit_action_send_webhook">
                <h4 class="mt-30">Основное</h4>
                <div class="row-line">
                    <div class="col-1-2">
                        <p class="width-100">
                            <label>Тип отправки (POST/GET):</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio"><input name="params[webhook][send_type]" type="radio" value="1" <?if(isset($cond_action['params']['webhook']['send_type']) && $cond_action['params']['webhook']['send_type'] == 1) echo ' checked="checked"';?>><span>POST</span></label>
                                <label class="custom-radio"><input name="params[webhook][send_type]" type="radio" value="2" <?if(isset($cond_action['params']['webhook']['send_type']) && $cond_action['params']['webhook']['send_type'] == 2) echo ' checked="checked"';?>><span>GET</span></label>
                            </span>
                        </p>
                        <p class="width-100">
                            <label class="custom-chekbox-wrap" for="is_send_utm_edit">
                                <input type="checkbox" id="is_send_utm_edit" name="params[webhook][is_send_utm]" value="1" <?if(isset($cond_action['params']['webhook']['is_send_utm']) && $cond_action['params']['webhook']['is_send_utm'] == 1) echo ' checked="checked"'; ?>>
                                <span class="custom-chekbox"></span>Передавать utm-метки
                            </label>
                        </p>
                    </div>

                    <div class="col-1-2">
                        <p class="width-100"><label title="Адрес сайта, куда будут отправляться данные">Адрес сайта:</label>
                            <input type="text" name="params[webhook][url]" value="<?=isset($cond_action['params']['webhook']['url']) ? $cond_action['params']['webhook']['url'] : '';?>" placeholder="Адрес сайта" required="required">
                        </p>
                    </div>
                </div>

                <?if($filter_model == SegmentFilter::FILTER_TYPE_ORDERS):?>
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Имена переменных (заказ)</h4>
                        </div>
                        
                        <div class="col-1-2 width-100"><label title="ID заказа">ID заказа:</label>
                            <input type="text" name="params[webhook][order_id]" value="<?=isset($cond_action['params']['webhook']['order_id']) ? $cond_action['params']['webhook']['order_id'] : '';?>" placeholder="">
                        </div>

                        <div class="col-1-2 width-100"><label title="Дата заказа">Дата заказа:</label>
                            <input type="text" name="params[webhook][order_date]" value="<?=isset($cond_action['params']['webhook']['order_date']) ? $cond_action['params']['webhook']['order_date'] : '';?>" placeholder="">
                        </div>

                        <div class="col-1-2 width-100"><label title="Статус заказа">Статус заказа:</label>
                            <input type="text" name="params[webhook][order_status]" value="<?=isset($cond_action['params']['webhook']['order_status']) ? $cond_action['params']['webhook']['order_status'] : '';?>" placeholder="">
                        </div>

                        <div class="col-1-2 width-100"><label title="Состав заказа, название продукта или продуктов через запятую">Состав заказа:</label>
                            <input type="text" name="params[webhook][order_products]" value="<?=isset($cond_action['params']['webhook']['order_products']) ? $cond_action['params']['webhook']['order_products'] : '';?>" placeholder="">
                        </div>

                        <div class="col-1-2 width-100"><label title="Сумма заказа">Сумма заказа:</label>
                            <input type="text" name="params[webhook][summ]" value="<?=isset($cond_action['params']['webhook']['summ']) ? $cond_action['params']['webhook']['summ'] : '';?>" placeholder="">
                        </div>

                        <div class="col-1-2 width-100"><label title="Roistat visit">Roistat visit:</label>
                            <input type="text" name="params[webhook][roistat_visitor]" value="<?=isset($cond_action['params']['webhook']['roistat_visitor']) ? $cond_action['params']['webhook']['roistat_visitor'] : '';?>" placeholder="">
                        </div>
                    </div>
                <?endif;?>

                <div class="row-line">
                    <div class="col-1-1 mb-0">
                        <h4>Имена переменных (пользователь)</h4>
                    </div>

                    <div class="col-1-2 width-100"><label title="ID пользователя">ID пользователя:</label>
                        <input type="text" name="params[webhook][user_id]" value="<?=isset($cond_action['params']['webhook']['user_id']) ? $cond_action['params']['webhook']['user_id'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Имя клиента">Имя клиента:</label>
                        <input type="text" name="params[webhook][name]" value="<?=isset($cond_action['params']['webhook']['name']) ? $cond_action['params']['webhook']['name'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Имя клиента">Фамилия клиента:</label>
                        <input type="text" name="params[webhook][surname]" value="<?=isset($cond_action['params']['webhook']['surname']) ? $cond_action['params']['webhook']['surname'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Email клиента">Email клиента:</label>
                        <input type="text" name="params[webhook][email]" value="<?=isset($cond_action['params']['webhook']['email']) ? $cond_action['params']['webhook']['email'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Телефон клиента">Телефон клиента:</label>
                        <input type="text" name="params[webhook][phone]" value="<?=isset($cond_action['params']['webhook']['phone']) ? $cond_action['params']['webhook']['phone'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Город проживания клиента">Город проживания клиента:</label>
                        <input type="text" name="params[webhook][city]" value="<?=isset($cond_action['params']['webhook']['city']) ? $cond_action['params']['webhook']['city'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Индекс клиента">Индекс клиента:</label>
                        <input type="text" name="params[webhook][index]" value="<?=isset($cond_action['params']['webhook']['index']) ? $cond_action['params']['webhook']['index'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Ссылка на профиль клиента в ВК">Ссылка на профиль клиента в ВК:</label>
                        <input type="text" name="params[webhook][vk_url]" value="<?=isset($cond_action['params']['webhook']['vk_url']) ? $cond_action['params']['webhook']['vk_url'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Инстаграм">Инстаграм:</label>
                        <input type="text" name="params[webhook][insta]" value="<?=isset($cond_action['params']['webhook']['insta']) ? $cond_action['params']['webhook']['insta'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Телеграм">Телеграм:</label>
                        <input type="text" name="params[webhook][telegram]" value="<?=isset($cond_action['params']['webhook']['telegram']) ? $cond_action['params']['webhook']['telegram'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="Секретный ключ API">Секретный ключ API:</label>
                        <input type="text" name="params[webhook][secret]" value="<?=isset($cond_action['params']['webhook']['secret']) ? $cond_action['params']['webhook']['secret'] : '';?>" placeholder="">
                    </div>

                    <div class="col-1-2 width-100"><label title="ClientID GA">ClientID GA:</label>
                        <input type="text" name="params[webhook][userId_GA]" value="<?=isset($cond_action['params']['webhook']['userId_GA']) ? $cond_action['params']['webhook']['userId_GA'] : '';?>" placeholder="">
                    </div>
                </div>

                <?$custom_fields = CustomFields::getFields();
                if ($custom_fields):?>
                    <div class="row-line">
                        <div class="col-1-1">
                            <h4>Кастомные поля</h4>
                        </div>
                        <?foreach($custom_fields as $custom_field):?>
                            <div class="col-1-2">
                                <p class="width-100"><label title=""><?=$custom_field['field_name'];?>:</label>
                                    <input type="text" name="params[webhook][custom_fields][<?=$custom_field['id'];?>]" placeholder="" value="<?=isset($cond_action['params']['webhook']['custom_fields'][$custom_field['id']]) ? $cond_action['params']['webhook']['custom_fields'][$custom_field['id']] : null;?>">
                                </p>
                            </div>
                        <?endforeach;?>
                    </div>
                <?endif;?>
            </div>
        </div>
    </div>

    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
    <input type="hidden" name="action_key" value="<?=isset($action_key) ? $action_key : 0;?>">
    <input type="hidden" name="action_id" value="<?=isset($cond_action['action_id']) ? $cond_action['action_id'] : 0;?>">
</form>