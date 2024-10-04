<?php defined('BILLINGMASTER') or die;?>

<div id="add_action" class="uk-modal">
    <div class="uk-modal-dialog" style="padding:0;">
        <div class="userbox modal-userbox-3">
            <form action="/admin/conditions/add-action" method="POST" enctype="multipart/form-data">
                <div class="admin_top admin_top-flex">
                    <div class="flex">
                        <div class="poll-answers-item__icon-add-question"></div>
                        <h3 class="traning-title">Добавить действие</h3>
                    </div>

                    <ul class="nav_button">
                        <li>
                            <input type="submit" name="add_action" value="Добавить" class="button save button-white font-bold" data-goal="1">
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
                                        <option value="<?=Conditions::ACTION_ADD_GROUP;?>" data-show_on="add_action_add_group"><?=Conditions::getActions(Conditions::ACTION_ADD_GROUP);?></option>
                                        <option value="<?=Conditions::ACTION_DEL_GROUP;?>" data-show_on="add_action_del_group"><?=Conditions::getActions(Conditions::ACTION_DEL_GROUP);?></option>
                                        <option value="<?=Conditions::ACTION_ADD_TO_MEMBERSHIP;?>" data-show_on="add_action_add_to_membership"><?=Conditions::getActions(Conditions::ACTION_ADD_TO_MEMBERSHIP);?></option>
                                        <? /*?>
                                        <option value="<?=Conditions::ACTION_DEL_TO_MEMBERSHIP;?>" data-show_on="add_action_del_to_membership"><?=Conditions::getActions(Conditions::ACTION_DEL_TO_MEMBERSHIP);?></option>
                                        <? */?>
                                        <option value="<?=Conditions::ACTION_SUBSCRIBE_MAILING;?>" data-show_on="add_action_subscribe_mailing"><?=Conditions::getActions(Conditions::ACTION_SUBSCRIBE_MAILING);?></option>
                                        <option value="<?=Conditions::ACTION_UNSUBSCRIBE_MAILING;?>" data-show_on="add_action_unsubscribe_mailing"><?=Conditions::getActions(Conditions::ACTION_UNSUBSCRIBE_MAILING);?></option>
                                        <option value="<?=Conditions::ACTION_SEND_LETTER;?>" data-show_on="add_action_send_letter"><?=Conditions::getActions(Conditions::ACTION_SEND_LETTER);?></option>
                                        <option value="<?=Conditions::ACTION_SEND_SMS;?>" data-show_on="add_action_send_sms"><?=Conditions::getActions(Conditions::ACTION_SEND_SMS);?></option>
                                        <option value="<?=Conditions::ACTION_SEND_WEBHOOK;?>" data-show_on="add_action_send_webhook"><?=Conditions::getActions(Conditions::ACTION_SEND_WEBHOOK);?></option>
                                        <option value="<?=Conditions::ACTION_USER_TO_PARTNER;?>" data-show_on=""><?=Conditions::getActions(Conditions::ACTION_USER_TO_PARTNER);?></option>
                                        <?if($filter_model == SegmentFilter::FILTER_TYPE_USERS):?>
                                            <option value="<?=Conditions::ACTION_DELETE_USER;?>"><?=Conditions::getActions(Conditions::ACTION_DELETE_USER);?></option>
                                        <?endif;?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100 hidden" id="add_action_add_group"><label>Выберите группы:</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="params[add_groups][]">
                                    <?if($group_list):
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>">
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>

                            <div class="width-100 hidden" id="add_action_del_group"><label>Выберите группы:</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="params[del_groups][]">
                                    <?$group_list = User::getUserGroups();
                                    if($group_list):
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>">
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>

                            <div class="width-100 hidden" id="add_action_add_to_membership"><label>Выберите мембершип подписки:</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="params[add_to_membership][]">
                                    <?$planes = Member::getPlanes();
                                    if($planes):
                                        foreach($planes as $plane):?>
                                            <option value="<?=$plane['id'];?>">
                                                <?=$plane['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>

                            <div class="width-100 hidden" id="add_action_del_to_membership"><label>Выберите мембершип подписки:</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="params[del_to_membership][]">
                                    <? if($planes):
                                        foreach($planes as $plane):?>
                                            <option value="<?=$plane['id'];?>">
                                                <?=$plane['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>

                            <div class="width-100 hidden" id="add_action_del_to_dmembership"><label>Выберите подписку</label>
                                <div class="select-wrap">
                                    <select name="params[subscribe_delivery]">
                                        <?if($delivery_list):
                                            foreach($delivery_list as $delivery):?>
                                                <option value="<?=$delivery['delivery_id'];?>">
                                                    <?=$delivery['name'];?>
                                                </option>
                                            <?php endforeach;
                                        endif;?>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100 hidden" id="add_action_unsubscribe_mailing"><label>Выберите рассылку</label>
                                <select class="multiple-select" size="5" multiple="multiple" name="params[unsubscribe_delivery][]">
                                    <?if($delivery_list):
                                        foreach($delivery_list as $delivery):?>
                                            <option value="<?=$delivery['delivery_id'];?>">
                                                <?=$delivery['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                            
                            <div class="width-100 hidden" id="add_action_subscribe_mailing"><label>Выберите рассылку</label>
                                <select class="multiple-select" size="5" multiple="multiple" name="params[subscribe_delivery][]">
                                    <?if($delivery_list):
                                        foreach($delivery_list as $delivery):?>
                                            <option value="<?=$delivery['delivery_id'];?>">
                                                <?=$delivery['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>

                        <div class="col-1-1 hidden" id="add_action_send_letter">
                            <p class="width-100"><label>Тема письма:</label>
                                <input type="text" value="" name="params[subject]">
                            </p>

                            <p class="width-100"><label>Текст письма:</label>
                                <textarea class="editor" name="params[letter]"></textarea>
                            </p>

                            <p class="width-100"><label>Получатель:</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="params[letter_recipient_type]" type="radio" value="1" <?if(!isset($cond_action['params']['letter_recipient_type']) || $cond_action['params']['letter_recipient_type'] == 1) echo ' checked="checked"';?>><span>Из сегмента</span></label>
                                    <label class="custom-radio"><input name="params[letter_recipient_type]" type="radio" value="2" <?if(isset($cond_action['params']['letter_recipient_type']) && $cond_action['params']['letter_recipient_type'] == 2) echo ' checked="checked"';?> data-show_on="add_action_letter_email"><span>Кастомный e-mail</span></label>
                                </span>
                            </p>

                            <p class="width-100 hidden" id="add_action_letter_email" style="width: 50%;"><label>E-mail:</label>
                                <input type="text" name="params[letter_email]" value="<?=isset($cond_action['params']['letter_email']) ? $cond_action['params']['letter_email'] : '';?>">
                            </p>
                            
                            <p><?foreach($vars = Conditions::getFields2Messages($filter_model) as $var => $title):
                                    echo "$var - $title <br />";
                                endforeach;?>
                            </p>
                        </div>

                        <div class="col-1-1 hidden" id="add_action_send_sms">
                            <div class="width-100">
                                <label>Текст SMS сообщения
                                    <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
                                </label>

                                <textarea name="params[message]" data-counting-characters="" data-max_length="1000"></textarea>

                                <div class="counting-characters">
                                    <span class="counting-characters_count">0</span>/<span class="counting-characters_max-length">1000</span>, sms:
                                    <span class="counting-characters_count-sms">0</span>
                                </div>
                            </div>

                            <p><?foreach($vars as $var => $title):
                                echo "$var - $title <br />";
                             endforeach;?>
                            </p>
                        </div>

                        <div class="col-1-1 hidden" id="add_action_send_webhook">
                            <div class="row-line">
                                <div class="col-1-2">
                                    <p class="width-100">
                                        <label>Тип отправки (POST/GET):</label>
                                        <span class="custom-radio-wrap">
                                            <label class="custom-radio"><input name="params[webhook][send_type]" type="radio" value="1" checked><span>POST</span></label>
                                            <label class="custom-radio"><input name="params[webhook][send_type]" type="radio" value="2"><span>GET</span></label>
                                        </span>
                                    </p>
                                    <p class="width-100">
                                        <label class="custom-chekbox-wrap" for="is_send_utm_add">
                                            <input type="checkbox" id="is_send_utm_add" name="params[webhook][is_send_utm]" value="1">
                                            <span class="custom-chekbox"></span>Передавать utm-метки
                                        </label>
                                    </p>
                                </div>
                                
                                <div class="col-1-2">
                                    <p class="width-100"><label title="Адрес сайта, куда будут отправляться данные">Адрес сайта:</label>
                                        <input type="text" name="params[webhook][url]" placeholder="Адрес сайта">
                                    </p>
                                </div>
                            </div>

                            <?if($filter_model == SegmentFilter::FILTER_TYPE_ORDERS):?>
                                <div class="row-line">
                                    <div class="col-1-1 mb-0">
                                        <h4>Имена переменных (заказ)</h4>
                                    </div>

                                    <div class="col-1-2 width-100"><label title="ID заказа">ID заказа:</label>
                                        <input type="text" name="params[webhook][order_id]" placeholder="">
                                    </div>

                                    <div class="col-1-2 width-100"><label title="Дата заказа">Дата заказа:</label>
                                        <input type="text" name="params[webhook][order_date]" placeholder="">
                                    </div>

                                    <div class="col-1-2 width-100"><label title="Статус заказа">Статус заказа:</label>
                                        <input type="text" name="params[webhook][order_status]" placeholder="">
                                    </div>

                                    <div class="col-1-2 width-100"><label title="Состав заказа">Состав заказа:</label>
                                        <input type="text" name="params[webhook][order_products]" placeholder="">
                                    </div>

                                    <div class="col-1-2 width-100"><label title="Сумма заказа">Сумма заказа:</label>
                                        <input type="text" name="params[webhook][summ]" placeholder="">
                                    </div>
                                </div>
                            <?endif;?>

                            <div class="row-line">
                                <div class="col-1-1 mb-0">
                                    <h4>Имена переменных (пользователь)</h4>
                                </div>

                                <div class="col-1-2 width-100"><label title="ID пользователя">ID пользователя:</label>
                                    <input type="text" name="params[webhook][user_id]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Имя клиента">Имя клиента:</label>
                                    <input type="text" name="params[webhook][name]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Имя клиента">Фамилия клиента:</label>
                                    <input type="text" name="params[webhook][surname]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Email клиента">Email клиента:</label>
                                    <input type="text" name="params[webhook][email]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Телефон клиента">Телефон клиента:</label>
                                    <input type="text" name="params[webhook][phone]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Город проживания клиента">Город проживания клиента:</label>
                                    <input type="text" name="params[webhook][city]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Индекс клиента">Индекс клиента:</label>
                                    <input type="text" name="params[webhook][index]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Ссылка на профиль клиента в ВК">Ссылка на профиль клиента в ВК:</label>
                                    <input type="text" name="params[webhook][vk_url]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Инстаграм">Инстаграм:</label>
                                    <input type="text" name="params[webhook][insta]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Телеграм">Телеграм:</label>
                                    <input type="text" name="params[webhook][telegram]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="Секретный ключ API">Секретный ключ API:</label>
                                    <input type="text" name="params[webhook][secret]" placeholder="">
                                </div>

                                <div class="col-1-2 width-100"><label title="ClientID GA">ClientID GA:</label>
                                    <input type="text" name="params[webhook][userId_GA]" placeholder="">
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
                                                <input type="text" name="params[webhook][custom_fields][<?=$custom_field['id'];?>]" placeholder="">
                                            </p>
                                        </div>
                                    <?endforeach;?>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                <input type="hidden" name="condition_id" value="<?=isset($condition) ? $condition['id'] : 0;?>">
                <input type="hidden" name="filter_model" value="<?=$filter_model;?>">
            </form>
        </div>
    </div>
</div>

