<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1><?=System::Lang('EDIT_USER');?> ID : <?=$user['user_id'];?></h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank"><?=System::Lang('GO_SITE');?></a><a href="<?=$setting['script_url'];?>/admin/logout" class="red"><?=System::Lang('QUIT');?></a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/users/">Пользователи</a>
        </li>
        <li>Редактировать пользователя</li>
    </ul>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <?php if(isset($_GET['success'])):?>
            <div class="admin_message">Сохранено!</div>
        <?php endif;?>
        
        <?php if(isset($_GET['dublemail'])):?>
            <div class="admin_warning">Пользователь с таким E-mail уже существует!</div>
        <?php endif;?>

        <div class="traning-top">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/user-edit.svg" alt="">
                </div>
                
                <div>
                    <h3 class="traning-title mb-0">Редактировать пользователя</h3>
                </div>
            </div>
            
            <ul class="nav_button">
                <li><input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?=$setting['script_url'];?>/admin/users"><?=System::Lang('CLOSE');?></a></li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Заказы</li>
                <li>Группы</li>
                <li>Подписки</li>

                <?php if($flows):?>
                <li>Потоки</li>
                <?php endif;?>
                
                <?php if($responder):?>
                    <li>Рассылки</li>
                <?php endif;

                if($en_training && $uniq_trainings || $en_courses && $uniq_courses):?>
                    <li>Тренинги</li>
                <?php endif;?>

                <li>Письма</li>

                <?if($user['is_curator'] && System::CheckExtensension('training', 1)):?>
                    <li>Кураторская</li>
                <?php endif;

                if($user_cerificates):?>
                    <li>Сертификаты</li>
                <?php endif;?>
                <li><i class="icon-chamomile"></i></li>
            </ul>
    
            <div class="admin_form">
                
                <!-- 1 вкладка Основное -->
                <div>
                    <h4 class="h4-border"><?=System::Lang('BASIC');?></h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <p><label>Имя:</label><input type="text" name="name" value="<?=$user['user_name'];?>"></p>
                            
                            <?php if($setting['show_surname'] > 0):?>
                                <p><label>Фамилия:</label>
                                    <input type="text" name="surname" value="<?=$user['surname'];?>">
                                </p>
                            <?php endif;

                            if($setting['show_patronymic'] > 0):?>
                                <p><label>Отчество:</label>
                                    <input type="text" name="patronymic" value="<?=$user['patronymic'];?>">
                                </p>
                            <?php endif;?>
                            
                            <p><label>E-mail:</label>
                                <input type="text" name="email" value="<?=$user['email'];?>">
                                <input type="hidden" name="old_email" value="<?=$user['email'];?>">
                            </p>
                            <p><label>Логин (для админов):</label>
                                <input type="text" value="<?=$user['login']?>" name="login" autocomplete="off">
                            </p>
                            <p><label>Телефон<?if($user['confirm_phone'] == $user['phone']) echo ' (подтвержден)';?></label>
                                <input type="text" name="phone" value="<?=$user['phone'];?>">
                            </p>
        
                            <p><label>Новый пароль: </label><input type="text" name="pass"></p>
        
                            <p><label>Статус: </label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1" <?php if($user['status']== 1) echo 'checked';?>><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0" <?php if($user['status']== 0) echo 'checked';?>><span>Откл</span></label>
                                </span>
                            </p>
                            <div class="width-100"><label>Уровень: </label>
                                <div class="select-wrap">
                                    <select name="role">
                                        <option value="user"<?php if($user['role'] == 'user') echo ' selected="selected"';?>>Пользователь</option>
                                        <option value="manager"<?php if($user['role'] == 'manager') echo ' selected="selected"';?>>Менеджер</option>
                                        <option value="admin"<?php if($user['role'] == 'admin') echo ' selected="selected"';?>>Админ</option>
                                    </select>
                                </div>
                            </div>
                            <? /*
                            <div class="width-100"><label>Состояние / статус: </label>
                                <div class="select-wrap">
                                    <select name="level">
                                        <option value="">Не выбран</option>
                                        <option value="1"<?php if($user['level'] == 1) echo ' selected="selected"';?>>Новичок</option>
                                        <option value="2"<?php if($user['level'] == 2) echo ' selected="selected"';?>>Исследователь</option>
                                        <option value="3"<?php if($user['level'] == 3) echo ' selected="selected"';?>>Доцент</option>
                                        <option value="4"<?php if($user['level'] == 4) echo ' selected="selected"';?>>Магистр</option>
                                        <option value="5"<?php if($user['level'] == 5) echo ' selected="selected"';?>>Ракета</option>
                                    </select>
                                </div>
                            </div>
                        */ ?>
                            <p><label>Добавить заметку</label>
                                <textarea name="note" cols="45" rows="3"><?=$user['note'];?></textarea>
                            </p>
                        </div>
                        
                        <div class="col-1-2">
                            <div class="round-block mb-20">
                                <p class="text-center mb-50 user-avatar">
                                    <img src="<?=User::getAvatarUrl($user, $setting);?>" />
                                </p>
                                <p><b>Уровень:</b> <?=user::getRoleUser($user['role']);?></p>
                                <p class="width-100 main-tooltyp-wrap">
                                    Дата регистрации: <?=date("d.m.Y H:i:s", $user['reg_date']);?>
                                    <span class="main-tooltyp">(через <?php $afterday = ($user['reg_date'] - $user['enter_time'])/ 86400; echo round($afterday, 2)?> дней)</span>
                                </p>
                                <p>Метод регистрации: <?=getRegMethod($user['enter_method']);?></p>
                                <p title="Когда School Master в первый раз запомнил посетителя">Первый вход: <?=date("d.m.Y H:i:s", $user['enter_time']);?></p>
                                <p>Последний вход: <?=$user['last_visit'] != null ? date("d.m.Y H:i:s", $user['last_visit']) : 'Никогда';?></p>

                                <?php if(!empty($user['from_id'])):
                                    $user_data = User::getUserNameByID($user['from_id']);?>
                                    <p>Пришёл от партнёра:
                                        <a target="_blank" href="/admin/users/edit/<?=$user['from_id'];?>"><?=$user_data['user_name'];?></a>
                                        <a data-id="<?=$user['user_id'];?>" data-partner-id="<?=$user['from_id'];?>" title="Отвязать партнера" class="remove-label remove-partner"><span class="icon-remove"></span></a>
                                    </p>
                                    
                                <?php endif;

                                if(!empty($user['channel_id'])):?>
                                    <p>Канал: <?php $channel = Stat::getChannelData($user['channel_id']);
                                        if ($channel && isset($channel['name'])):?>
                                            <a href="/admin/stat/channels/"><?=$channel['name'];?></a>
                                        <?php endif;?>
                                    </p>
                                <?php endif;?>

                                <p>ДР: <?="{$user['bith_day']}.{$user['bith_month']}.{$user['bith_year']}";?></p>
                                
                                <?php $brought_money = Order::getOrderTotalSum2User($user['email']);?>
                                <p><b>Принес денег: <?=$brought_money;?> <?=$setting['currency'];?></b></p>

                                <div class="width-100">
                                    <input form="user_enter" type="hidden" value="<?=$user['user_id'];?>" name="user_id">
                                    <input type="hidden" form="user_enter" value="<?=$user['user_name'];?>" name="user_name">
                                    <input type="hidden" form="user_enter" name="token" value="<?=$_SESSION['admin_token'];?>">
                                </div>
                            </div>

                            <?$utm_keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_referrer'];
                            $first_order = Order::getFirstOrder2User($user['email']);
                            $user_utm = $first_order && $first_order['utm'] ? System::getUtmData($first_order['utm']) : null;?>
                            <div style="border-radius: 10px; margin-bottom: 8px; background-color: #f9f9f9;">
                                <div class="usermetrics-block-btns row-line" style="margin-bottom: 0 !important; margin-left: 0; background-color: #f3f3f3; border-radius: 10px;" onclick="openUserMetrics()">
                                        <a class="no-link-stat" href="javascript:void(0);" style="margin-bottom: 0 !important;">Метки по которым пришел</a>
                                        <span class="icon-arrow-down"></span>
                                </div>

                                <script>
                                    function openUserMetrics() {
                                        document.getElementById("user-stat-metricsblock").classList.toggle('hidden');
                                    }
                                </script>
                                <div id="user-stat-metricsblock" class="hidden" style="background-color: #f9f9f9;">
                                    <ul class="user-stat-metrics">
                                        <?php foreach ($utm_keys as $utm_key):?>
                                            <li class="flex flex-nowrap">
                                                <div class="statistics-tags-item__key"><?=$utm_key;?></div>
                                                <div class="statistics-tags-item__val"><?=$user_utm && isset($user_utm[$utm_key]) ? $user_utm[$utm_key] : '...';?></div>
                                            </li>
                                        <?php endforeach;?>

                                        <li class="flex flex-nowrap">
                                            <div class="statistics-tags-item__key">clientID YM</div>
                                            <div class="statistics-tags-item__val"><?=isset($order_info['userId_YM']) ? $order_info['userId_YM'] : '...';?></div>
                                        </li>

                                        <li class="flex flex-nowrap">
                                            <div class="statistics-tags-item__key">clientID GA</div>
                                            <div class="statistics-tags-item__val"><?=isset($order_info['userId_GA']) ? $order_info['userId_GA'] : '...';?></div>
                                        </li>

                                        <li class="flex flex-nowrap">
                                            <div class="statistics-tags-item__key">roistat_visitor</div>
                                            <div class="statistics-tags-item__val"><?=isset($order_info['roistat_visitor']) ? $order_info['roistat_visitor'] : '...';?></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="block-button" style="margin-top: 32px;">
                                <input type="submit" form="user_enter" value="Войти под пользователем" class="button-green" style="" name="user_enter">
                            </div>
                            <div class="block-button">
                                <a href="/admin/users/resetpass<?="?user_id={$user['user_id']}&user_name={$user['user_name']}&user_email={$user['email']}&token={$_SESSION['admin_token']}";?>">Сбросить и отправить пароль</a>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-30">Социальные сети</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <? $user_connect = Connect::getUserBySMID($user['user_id']); ?>
                            <p>
                                <label>Telegram: 
                                <? if(!empty($user_connect) && ($db_key = Connect::getServiceKey('telegram')) 
                                    && isset($user_connect[$db_key]) && !empty($user_connect[$db_key])
                                ): ?>
                                    <span style="color:#5DCE59">аккаунт привязан</span>

                                <? else: ?>
                                    <span style="color:#E04265">аккаунт не привязан</span>

                                <? endif; ?>

                                </label>
                                <input type="text" name="nick_telegram" value="<?=@ $user['nick_telegram'];?>">
                            </p>
                            <p>
                                <label>ВКонтакте: 
                                <? if(!empty($user_connect) && ($db_key = Connect::getServiceKey('vkontakte')) 
                                    && isset($user_connect[$db_key]) && !empty($user_connect[$db_key])
                                ): ?>
                                    <span style="color:#5DCE59">аккаунт привязан</span>

                                <? else: ?>
                                    <span style="color:#E04265">аккаунт не привязан</span>

                                <? endif; ?>

                                </label>
                                <input type="text" name="vk_url" value="<?=@ $user['vk_url'];?>">
                            </p>
                                <? if(!empty($user_connect)){
                                    System::modalFormGenerate('connect_user_setting', "/admin/connect/ajax/user/{$user['user_id']}", ['token' => $_SESSION['admin_token']], 'connect_set');
                                    ?>
                                    <a href="#connect_user_setting" data-uk-modal="{center:true}" class="button-green" style="width: 100%;">Настройки Connect</a>
                                    <?
                                }
                            ?>

                        </div>
                        <div class="col-1-2">
                            <p>
                                <label>Instagram: </label>
                                <input type="text" name="nick_instagram" value="<?=$user['nick_instagram'];?>">
                            </p>
                            
                            <p>
                                <label>ID в Одноклассниках: </label>
                                <input type="text" name="ok_id" value="<?=$user['ok_id'];?>">
                            </p>
                        </div>
                    </div>

                    <h4 class="mt-30">Личное</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <div class="width-100"><label>Пол: </label>
                                <div class="select-wrap">
                                    <select name="sex">
                                        <option value="">Не указан</option>
                                        <option value="male"<?php if($user['sex'] == 'male') echo ' selected="selected"';?>>Мужской</option>
                                        <option value="female"<?php if($user['sex'] == 'female') echo ' selected="selected"';?>>Женский</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-30 h4-border">Дополнительные поля</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <p><label>Город: </label><input type="text" name="city" value="<?=$user['city'];?>"></p>
                            <p><label>Индекс: </label><input type="text" name="zipcode" value="<?=$user['zipcode'];?>"></p>
                        </div>
                        <div class="col-1-2">
                            <p><label>Адрес: </label><textarea name="address" cols="45" rows="3"><?=$user['address'];?></textarea></p>
                        </div>
                    </div>

                    <?if($custom_fields):?>
                        <h4 class="mt-30 h4-border">Кастомные поля</h4>
                        <div class="row-line custom-fields">
                            <?foreach($custom_fields as $custom_field):?>
                                <div class="col-1-2">
                                    <div class="width-100">
                                        <?=CustomFields::getFieldTag2Admin($custom_field, $user['user_id']);?>
                                    </div>
                                </div>
                            <?endforeach;?>
                        </div>
                    <?endif;?>
                    <h4>Токены пользователя</h4>
                    <div id="tokenwrap" class="col-1-1">
                        <?php $auto_login = json_decode($user['auto_login'], true); ?>
                        <?php if (is_array($auto_login)) { ?>
                            <p><label>Токен: </label><input readonly type="text" value="<?=$auto_login['token']?>"></p>
                            <p><label>Дата создания: </label><input readonly type="text" value="<?= date('Y.m.d H:i:s',$auto_login['create_date']) ?>"></p>
                            <p><label>Дата последнего использования: </label><input readonly type="text" value="<?= is_int($auto_login['last_use']) ? date('Y.m.d H:i:s',$auto_login['last_use']) : 'Еще не использовался' ?>"></p>
                        <?php } else { echo "У пользователя отсутствует токен";} ?>
                    </div>

                    <?php if (System::CheckExtensension('polls', 1)) {
                        require_once(ROOT . '/extensions/polls/views/admin/polls/user_card.php');
                    }?>

                    <h4 class="mt-30 h4-border">Роли</h4>
                    <div class="row-line">
                        <div class="col-1-2">
                            <div class="roly-wrap">
                                <div class="roly-bottom">
                                    <?php $aff = System::CheckExtensension('partnership', 1);
                                    $enable_aff = $aff ? System::getExtensionStatus('partnership') : false;
                                    if($enable_aff != false && $user['is_partner'] == 1):?>
                                        <div class="width-100">
                                            <label class="custom-chekbox-wrap" for="is_partner">
                                                <input type="checkbox" id="is_partner" name="is_partner" value="1"<?php if($user['is_partner'] == 1) echo ' checked="checked"'; ?>>
                                                <span class="custom-chekbox"></span>Партнёр (<a target="_blank" href="/admin/aff/userstat/<?php echo $id;?>">начисления</a>)
                                            </label>
                                        </div>

                                        <div class="width-100">
                                            <div class="ind-komis">
                                                <div class="relative" style="max-width: 100px;">
                                                    <input class="price-input-2" type="text" value="<?=$partner['custom_comiss'];?>" name="custom_comiss" title="Индивидуальная комиссия партнёра">
                                                    <div class="price-input-cur-2">%</div>
                                                </div>
                                                <span>Инд. комиссия</span>
                                            </div>
                                        </div>
                                    <?php endif;?>
                                </div>

                                <div class="roly-row mb-0">
                                    <?php if($user['is_client'] == 1):?>
                                        <div><label class="custom-chekbox-wrap" for="is_client">
                                            <input type="checkbox" id="is_client" name="is_client" value="1" checked="checked" disabled="disabled">
                                            <span class="custom-chekbox"></span>Клиент
                                        </label></div>
                                    <?php endif;?>

                                    <?php if($enable_aff != false):?>
                                        <div><label class="custom-chekbox-wrap" for="is_author">
                                            <input type="checkbox" id="is_author" name="is_author" value="1" <?php if($user['is_author'] == 1) echo ' checked="checked"'; ?>>
                                            <span class="custom-chekbox"></span>Автор
                                        </label></div>
                                    <?php endif;?>

                                    <div><label class="custom-chekbox-wrap" for="is_subsc">
                                        <input type="checkbox" id="is_subsc" name="is_subsc" value="1"<?php if($user['is_subs'] == 1) echo ' checked="checked"'; ?>>
                                        <span class="custom-chekbox"></span>Получает рассылки
                                    </label></div>

                                    <div><label class="custom-chekbox-wrap" for="is_curator">
                                        <input type="checkbox" id="is_curator" name="is_curator" value="1"<?php if($user['is_curator'] == 1) echo ' checked="checked"'; ?>>
                                        <span class="custom-chekbox"></span>Куратор
                                    </label></div>

                                    <?php if(isset($enable_aff) && $enable_aff != false && $user['is_partner'] == 0):?>
                                    <div>
                                        <form action="" method="POST" id="partner" class="custom-chekbox-wrap">
                                            <input type="hidden" name="id" value="<?=$user['user_id'];?>">
                                            <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                                            <span class="custom-chekbox"></span>
                                            <input class="make-partner" type="submit" value="Сделать партнёром" name="make_partner">
                                        </form>
                                    </div>
                                    <?php endif;?>
                                </div>
                            </div>
                            <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                        </div>
                    </div>

                    <!--  Особый режим -->
                    <?php if($enable_aff != false && $user['is_partner'] == 1):?>
                        <h4 class="mt-30 h4-border">Особый режим партнёрки</h4>
                        <div class="row-line">
                            <div class="col-1-1">
                                <p><label>Особый режим: </label>
                                    <span class="custom-radio-wrap">
                                        <label class="custom-radio"><input name="spec_aff" type="radio" value="1" <?php if($user['spec_aff']== 1) echo 'checked';?>><span>Вкл</span></label>
                                        <label class="custom-radio"><input name="spec_aff" type="radio" value="0" <?php if($user['spec_aff']== 0) echo 'checked';?>><span>Откл</span></label>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="gray-block-3 mt-20">
                            <div class="row-line">
                                <div class="col-1-1">
                                    <p><strong>Добавить индивидуальную стратегию партнерки для партнера и продукта</strong></p>
                                </div>

                                <div class="col-1-1" style="max-width: 340px;">
                                    <div><label>Выберите продукт</label>
                                        <div class="select-wrap">
                                            <select form="spec_aff" name="specaff_params[products]">
                                                <?php $product_list = Product::getProductListOnlySelect();
                                                if($product_list):
                                                    foreach ($product_list as $product_item):?>
                                                        <option <?php if($product_item['run_aff']!=1) echo 'style="color: gray;"';?> value="<?=$product_item['product_id'];?>"><?=$product_item['product_name'];?><?php if($product_item['run_aff']!=1) echo ' (выключено начисление)';?></option>
                                                        <?php if ($product_item['service_name']):?>
                                                            <option <?php if($product_item['run_aff']!=1) echo 'style="color: gray;":';?> disabled="disabled" class="service-name">(<?=$product_item['service_name'];?>)</option>
                                                        <?php endif;
                                                    endforeach;
                                                endif;?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-1-1" style="max-width: 340px;">
                                    <div><label>Стратегия работы</label>
                                        <div class="select-wrap">
                                            <select form="spec_aff" name="specaff_params[type]">
                                                <option value="1">Начислять только с 1 заказа</option>
                                                <option value="2">Начислять только со 2-го заказа</option>
                                                <option value="3" data-show_on="floatscheme">Плавающая схема</option>
                                                <option value="4">Начислять для всех заказов</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-1-4" style="max-width: 100px;">
                                    <div><label>Процент:</label>
                                        <div class="relative">
                                            <input type="text" form="spec_aff" name="specaff_params[comiss]">
                                            <div class="price-input-cur-2">%</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-1-1" style="max-width: 220px">
                                    <input type="hidden" form="spec_aff" name="token" value="<?=$_SESSION['admin_token'];?>">
                                    <input type="submit" form="spec_aff" name="add_spec_aff" class="button save button-green-rounding add-prod-but" value="Добавить">
                                </div>

                                <div id="floatscheme" class="col-1-1 hidden">
                                    <p style="max-width: 350px"><label title="Вида № платежа=%комиссии, например: 1=20">Платежи и комиссии для плавающей схемы: </label>
                                        <textarea form="spec_aff" name="specaff_params[float]"></textarea>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?if($aff_params):?>
                            <div class="add-strategy mt-20">
                                <p class="mb-15"><strong>Созданные стратегии</strong></p>
                                <?foreach($aff_params as $item):
                                    $product = Product::getProductById($item['product_id']);?>
                                    <div class="gray-block-3 mt-20">
                                        <form id="edit_spec<?=$item['id'];?>" action="" method="POST">
                                            <p class="width-100 add-strategy-top">
                                                <a class="font-bold" href="/admin/products/edit/<?=$product['product_id'];?>" target="_blank">
                                                    <?=$product['product_name'].($product['service_name'] ? " ({$product['service_name']})" : '');?>
                                                </a>
                                                <button type="submit" onclick="return confirm('Вы уверены?')" title="Удалить" name="del_spec" class="button save button-red-rounding button-lesson"><span class="icon-remove"></span></button>
                                            </p>

                                            <div class="row-line">
                                                <div class="col-1-1" style="max-width: 340px"><label>Стратегия работы</label>
                                                    <div class="select-wrap">
                                                        <select name="specaff_params[type]">
                                                            <option value="1"<?php if($item['type'] == 1) echo ' selected="selected"';?>>Начислять только с 1 заказа</option>
                                                            <option value="2"<?php if($item['type'] == 2) echo ' selected="selected"';?>>Начислять только со 2-го заказа</option>
                                                            <option value="3"<?php if($item['type'] == 3) echo ' selected="selected"';?> data-show_on="floatscheme<?=$item['id'];?>">Плавающая схема</option>
                                                            <option value="4"<?php if($item['type'] == 4) echo ' selected="selected"';?>>Начислять для всех заказов</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-1-4" style="max-width: 100px;">
                                                    <div><label>Процент</label>
                                                        <div class="relative">
                                                            <input  type="text" name="specaff_params[comiss]" value="<?=$item['comiss'];?>">
                                                            <div class="price-input-cur-2">%</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-1-2" style="align-self: flex-end; max-width: 160px;">
                                                    <div class="form-row-submit">
                                                        <input  type="hidden" name="spec_id" value="<?=$item['id'];?>">
                                                        <input type="hidden"  name="token" value="<?=$_SESSION['admin_token'];?>">
                                                        <button type="submit" title="Сохранить" name="save_spec" class="button save add-strategy-but button-green-rounding"><i class="icon-check-thin"></i>Применить</button>
                                                    </div>
                                                </div>

                                                <div id="floatscheme<?=$item['id'];?>" class="col-1-1 hidden"><label>Плавающая схема</label>
                                                    <textarea  name="specaff_params[float]"><?=$item['float_scheme'];?></textarea>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endforeach;?>
                            </div>
                        <?php endif;
                    endif;?>
                </div>


                <!-- 2 вкладка Заказы -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1" style="border-bottom: 1px solid #D8DAE7;">
                            <div class="width-50 mt-5"><b>Заказы пользователя</b></div>
                            <div class="width-50 text-right mb-10">
                                <button form="create_order" type="submit" name="send" class="button-blue-rounding">Добавить заказ</button>
                            </div>
                        </div>
                    </div>

                    <div class="row-line mt-15">
                        <div class="col-1-1">
                            <div class="overflow-container">
                                <?php if($orders):?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Номер</th>
                                                <th class="text-left">Продукт</th>
                                                <th>Сумма, <?=$setting['currency'];?></th>
                                                <th class="text-left">Utm-метки</th>
                                                <th>Статус</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $all_sum = $count_orders = 0;
                                            foreach($orders as $order):
                                                $order_summ = 0;
                                                $count_orders += 1;?>

                                                <tr>
                                                    <td class="text-left">
                                                        <a class="order-link" target="_blank" href="/admin/orders/edit/<?=$order['order_id'];?>">
                                                            <?=$order['order_date'];?>
                                                        </a>
                                                        <br><?=date("d.m.Y", $order['order_date']);?>
                                                    </td>

                                                    <td class="text-left">
                                                        <?php $order_items = Order::getOrderItems($order['order_id']);
                                                        if ($order_items):
                                                            foreach ($order_items as $order_item):
                                                                $order_summ += $order_item['price'];
                                                                $order_product = Product::getProductData($order_item['product_id'], false);?>
                                                                <a target="_blank" href="/admin/products/edit/<?=$order_item['product_id'];?>">
                                                                    <?=$order_product['product_name'].($order_product['service_name'] ? " ({$order_product['service_name']})" : '')?>
                                                                </a><br>
                                                            <?php endforeach;
                                                        endif;?>
                                                    </td>

                                                    <td class="fz-16"><?=$order_summ?></td>

                                                    <td class="text-left">
                                                        <?=str_replace(['?', '&'], ['', '<br>'], $order['utm']);?>
                                                    </td>
                                                    <td><?php if($order['status'] != 1):?><span class="icon-stopwatch"></span><?endif?>
                                                        <?php if($order['status'] == 1):?><span class="checked-status"></span><?endif?>
                                                        <?php if($order['status'] == 9):?><span class="status-return"></span><?endif?>
                                                    </td>
                                                </tr>
                                                <?php if($order['status'] == 1):
                                                    $all_sum += $order_summ;
                                                endif;?>
                                        <?php endforeach;?>
                                        </tbody>
                                    </table>

                                    <hr />
                                    <p class="text-right">Всего заказов: <strong><?=$count_orders;?></strong>, оплачено на сумму: <strong><?="$all_sum {$setting['currency']}";?></strong></p>
                                <?php else:?>
                                    <p>Заказы не найдены</p>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- 3 вкладка Группы -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <?php $user_group_list = User::getUserGroups();
                            if($user_group_list):?>
                            <div class="overflow-container">
                                <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Группа</th>
                                                <th class="text-left">Дата назначения</th>
                                            </tr>
                                        </thead>

                                        <body>
                                            <?php foreach($user_group_list as $key => $user_group):?>
                                                <tr>
                                                    <td class="text-left">
                                                        <label class="custom-chekbox-wrap" for="<?=$user_group['group_name'];?>">
                                                            <input type="checkbox" id="<?=$user_group['group_name'];?>" name="groups[ids][<?=$key;?>]" value="<?=$user_group['group_id'];?>"<?php if($user_groups && in_array($user_group['group_id'], $user_groups)) echo ' checked="checked"';?>>
                                                            <span class="custom-chekbox"></span>
                                                            <a href="/admin/usergroups/edit/<?=$user_group['group_id'];?>" target="_blank"><?=$user_group['group_title'];?></a>
                                                        </label>
                                                    </td>

                                                    <td class="text-left">
                                                        <?php $date = '';
                                                        if($user_groups && in_array($user_group['group_id'], $user_groups)):
                                                            $group = User::getGroupByUserAndGroup($user['user_id'], $user_group['group_id']);
                                                            $date = $group ? date("d.m.Y H:i:s", $group['date']) : '';
                                                        endif;?>
                                                        <input type="text" id="<?=$user_group['group_name'];?>_date" class="datetimepicker" name="groups[dates][<?=$key;?>]" value="<?=$date;?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach;?>
                                        </body>
                                    </table>
                                </div>
                            <?php endif;?>
                        </div>
                    </div>
                </div>


                <!-- Подписки -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1" style="border-bottom: 1px solid #D8DAE7;">
                            <div class="width-50 mt-5"><b>Подписки пользователя</b></div>
                            <div class="width-50 text-right mb-10">
                                <button form="create_subscribe" type="submit" name="send" class="button-blue-rounding">Добавить подписку</button>
                            </div>
                        </div>
                    </div>

                    <div class="row-line mt-15">
                        <div class="col-1-1">
                            <div class="overflow-container">
                                <?php if($user_planes):?>
                                    <table class="table">
                                        <tr>
                                            <th>ID</th>
                                            <th class="text-left">План</th>
                                            <th class="text-left">Дата создания</th>
                                            <th class="text-left">Дата окончания</th>
                                            <th class="td-last">Статус</th>
                                        </tr>
                                        <?foreach($user_planes as $user_plane):
                                            $plane = Member::getPlaneByID($user_plane['subs_id']);?>
                                            <tr>
                                                <td><a href="/admin/memberusers/edit/<?=$user_plane['id'];?>"><?=$user_plane['id'];?></a></td>
                                                <td class="text-left">
                                                    <a href="/admin/membersubs/edit/<?=$user_plane['subs_id'];?>">
                                                        <?=!empty($plane['service_name']) ? $plane['service_name'] : $plane['name'];?>
                                                    </a>
                                                </td>
                                                <td class="text-left"><?=date("d-m-Y H:i:s", $user_plane['create_date']);?></td>
                                                <td class="text-left"><?=date("d-m-Y H:i:s", $user_plane['end']);?></td>
                                                <td class="td-last">
                                                    <?php if($user_plane['status']):?>
                                                        <span class="stat-yes"><i class="icon-stat-yes"></i></span>
                                                    <?php else:?>
                                                        <span class="stat-no"></span>
                                                    <?php endif;?>
                                                </td>
                                            </tr>
                                        <?php endforeach;?>
                                    </table>
                                <?else:?>
                                    <p>Подписки не найдены</p>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?if($flows):?>
                <!-- Потоки -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            
                            <?php $flows_map = Flows::getFlowByUserID($user['user_id']);?>
                            <?php if($flows_map){?>
                            <div class="overflow-container">
                                <table class="table">
                                    <tr>
                                        <th class="text-left">Поток</th>
                                        <th class="text-left">Дата начала</th>
                                        <th class="text-left">Дата завершения</th>
                                        <th class="text-left">Статус</th>
                                        <th class="td-last">Act</th>
                                    </tr>
                                
                                <?php foreach($flows_map as $item):?>
                                    <tr>
                                        <td class="text-left"><a target="_blank" href="/admin/flows/edit/<?=$item['flow_id'];?>"><?=Flows::getFlowName($item['flow_id']);?></a></td>
                                        <td class="text-left">
                                        <input type="text" form="map_<?=$item['map_id'];?>" class="datetimepicker" value="<?=date("d.m.Y H:i", $item['start']);?>" name="start" autocomplete="off"></td>
                                        <td class="text-left">
                                        <input type="text" form="map_<?=$item['map_id'];?>" class="datetimepicker" value="<?=date("d.m.Y H:i", $item['end_date']);?>" name="end_date" autocomplete="off"></td>
                                        <td class="text-left">
                                            <select name="status" form="map_<?=$item['map_id'];?>">
                                                <option value="0"<?php if($item['status'] == 0) echo ' selected="selected"';?>>В ожидании</option>
                                                <option value="1"<?php if($item['status'] == 1) echo ' selected="selected"';?>>Активен</option>
                                                <option value="8"<?php if($item['status'] == 8) echo ' selected="selected"';?>>Завершён</option>
                                            </select>
                                        </td>
                                        <td><input type="hidden" form="map_<?=$item['map_id'];?>" name="reload_map_item" value="<?=$item['map_id'];?>">
                                            <input type="hidden" form="map_<?=$item['map_id'];?>"  name="token" value="<?=$_SESSION['admin_token'];?>">
                                            <input type="image" form="map_<?=$item['map_id'];?>" src="/template/admin/images/reload.png" title="Обновить" name="reload">
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                                </table>
                            </div>
                            <? } else echo 'Нет данных'; ?>
                            
                        </div>
                    </div>
                </div>
                <?php endif;?>


                <!-- Рассылки -->
                <?php if($responder):?>
                    <div>
                        <div class="row-line">
                            <div class="col-1-1">
                                <h4 class="h4-border mb-5">Подписан на рассылки</h4>
                                <table class="table">
                                    <?php $delivery_list = Responder::getUserDelivery($user['email']);
                                    if($delivery_list):?>
                                        <?php foreach($delivery_list as $delivery):
                                                $delivery_data = Responder::getDeliveryData($delivery['delivery_id']);?>
                                    <tr><td class="text-left"><div class="subscrip-item"><i class="icon-subscrip-check"></i><a href="/admin/responder/edit/<?=$delivery['delivery_id'];?>" target="_blank"><?=$delivery_data['name'];?></a></div></td></tr>
                                        <?php endforeach;?>
                                    <?php else:?>
                                    <tr><td><p><strong>Не подписан на рассылки</strong></p></td></tr>
                                    <?php endif;?>
                                </table>
                            </div>

                            <div class="col-1-1">
                                <h4 class="h4-border mb-5">Отписки</h4>
                                <table class="table">
                                    <!-- ToDo программистам: tr > td надо вставить куда-нибудь внутрь цикла, по примеру таблицы выше
                                    И внутрь td такую разметку, чтобы иконка была
                                    <div class="subscrip-item">
                                        <i class="icon-unsubscribe-check"></i><a href="#">School-master</a>
                                    </div>
                                     -->
                                    <tr>
                                        <td class="text-left">
                                            <?php $cancelled = Responder::getReasons($user['email']);
                                if($cancelled){
                                    foreach($cancelled as $cancel):
                                        if($cancel['delivery_id'] != 0) {
                                            $delivery_data = Responder::getDeliveryData($cancel['delivery_id']);
                                            $title = $delivery_data['name'];
                                        } else $title = 'Отписался от всех';?>
                                            <p><strong><?=$title;?></strong> | <?php echo  date("d.m.Y H:i:s", $cancel['time']);?><br /><?=$cancel['reason']?></p><br />
                                            <?php endforeach;
                                }?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


                <!-- Тренинги -->
                <?php if(($en_training && $uniq_trainings) || ($en_courses && $uniq_courses)):?>
                    <div>
                        <?php if ($uniq_trainings) {
                            require_once(__DIR__ . '/edit_trainings.php');
                        }
                        if ($uniq_courses) {
                            require_once(__DIR__ . '/edit_courses.php');
                        };?>
                    </div>
                <?php endif;?>


                <!-- Письма -->
                <div>
                    <h4>Написать письмо</h4>
                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="width-100">
                                <? $services = Connect::getAllServices(); 
                                foreach ($services as $id => $service): ?>
                                <label class="custom-chekbox-wrap">
                                    <input form="sender" type="hidden" name="addit_data[caller]" value="user_edit">
                                    <input form="sender" type="checkbox" 
                                        name="addit_data[mail][<?=$service['name']?>][msg]" value="1" 
                                        <? echo Connect::getUserNotifStatusBySMID($user['user_id'], $service['name']) 
                                            ? 'checked="checked"' 
                                            : 'disabled';
                                        ?>
                                    >
                                    <span class="custom-chekbox"></span>Дублировать в <?=$service['title']?>
                                </label>
                                <? endforeach; ?>
                            </div>
                            <div class="width-100">
                                <label>Имя отправителя:</label>
                                <input form="sender" type="text" name="sender_name" placeholder="Имя отправителя" value="<?=$setting['sender_name']?>">
                            </div>
                            <div class="width-100">
                                <label>Тема письма:</label>
                                <input form="sender" type="text" name="subject" placeholder="Тема письма">
                            </div>

                            <div class="width-100">
                                <textarea form="sender" name="letter" class="editor"></textarea>
                            </div>

                            <div class="width-100">
                                <input form="sender" type="submit" name="send" class="button-green">
                            </div>
                        </div>

                        <div class="col-1-1">
                            <h4>Отправленные письма</h4>

                            <div class="row-line">
                                <div class="col-1-1">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th class="text-left">ID</th>
                                            <th class="text-left">Email</th>
                                            <th class="text-left">Письмо</th>
                                            <th>Время</th>
                                            <!--th class="td-last"></th-->
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <?php if($log_letters):
                                            foreach($log_letters as $log):?>
                                                <tr>
                                                    <td><?=$log['id'];?></td>
                                                    <td class="text-left"><?=$log['email'];?></td>
                                                    <td class="text-left rdr_2"><a target="_blank" href="<?=$setting['script_url'];?>/admin/emailog/edit/<?=$log['id'];?>"><?=$log['type'];?></a></td>

                                                    <td><?=date("d.m.Y H:i:s", $log['datetime']);?></td>
                                                </tr>
                                            <?php endforeach;
                                        else:?>
                                            <tr>
                                                <td><p>No letters</p></td>
                                            </tr>
                                        <?php endif;?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кураторская -->
                <?php if($user['is_curator'] && System::CheckExtensension('training', 1)):?>
                    <div>
                        <h4 class="h4-border">Кураторская</h4>
                        <div class="row-line">
                            <div class="col-1-1">
                                <div class="overflow-container">
                                   Тут список Ваших пользователей (в разработке)
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif;?>


                <!-- Сертификаты -->
                <?php if($user_cerificates):?>
                    <div>
                        <h4 class="h4-border">Сертификаты</h4>
                        <div class="row-line">
                            <div class="col-1-1">
                                <div class="overflow-container">
                                <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Номер</th>
                                                <th class="text-left">Тренинг</th>
                                                <th class="text-right">Дата выдачи</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        
                                     <?php foreach($user_cerificates as $user_cerificate):?>
                                        <tr>
                                        <td class="text-left"><?=$user_cerificate['id'];?></td>
                                        <td class="text-left"><a target="_blank" href="/admin/training/edit/<?=$user_cerificate['training_id'];?>"> <?=Training::getTrainingNameByID($user_cerificate['training_id']);?></a></td>
                                        <td class="text-right"><?=date("d.m.Y H:i:s", $user_cerificate['date']);?></td>
                                        <td><a target="_blank" href="<?=$setting['script_url'];?>/training/showcertificate/<?=$user_cerificate['url'];?>">Посмотреть</a>
                              
                                        </td>
                                        <td>
                                            <a href="/admin/training/updatecertificate/<?=$user['user_id'];?>/<?=$user_cerificate['training_id'];?>/<?=$user_cerificate['url'];?>"><img title="Обновить" src="/template/admin/images/reload.png"></a>
                                        </td>
                                        </tr>
                                    <?php endforeach;?>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif;?>

                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="overflow-container">
                                <?php if($sessions = UserSession::getSessions($user['user_id'])):?>
                                    <table class="table">
                                        <tr>
                                            <th class="text-left">Сессия</th>
                                            <th class="text-left">Устройство</th>
                                            <th class="text-left">Дата входа</th>
                                            <th class="text-right">Действие</th>
                                        </tr>

                                        <?foreach($sessions as $session):?>
                                            <tr>
                                                <td class="text-left pl-0">
                                                    <div class="user-session_id user-session-status--<?=$session['status'];?>"">
                                                        <span><?=$session['id'];?></span>
                                                    </div>
                                                </td>
                                                <td class="text-left">
                                                    <span>IP: <?=$session['ip'];?></span></br>
                                                    <span><?=$session['user_agent'];?></span></br>
                                                </td>
                                                <td class="text-left"><?=date("d.m.Y", $session['auth_date']);?></td>
                                                <td class="text-right">
                                                    <?if($session['status'] == 2):?>
                                                        <a href="/admin/users/sessions/unblock/<?=$session['id'];?>" title="Разблокировать пользователя с данным ip"><i class="icon-unblock"></i></a>
                                                    <?else:?>
                                                        <a href="/admin/users/sessions/block/<?=$session['id'];?>" title="Заблокировать пользователя с данным ip"><i class="icon-block"></i></a>
                                                    <?endif;?>
                                                    <a href="/admin/users/sessions/del/<?=$session['id'];?>"  title="Удалить сессию" class="ml-18"><i class="icon-remove2"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach;?>
                                    </table>
                                <?else:?>
                                    <p>Сессий пока нет</p>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <?if(isset($flows_map) && $flows_map != null):
        foreach($flows_map as $item):?>
            <form action="" method="POST" id="map_<?=$item['map_id'];?>"></form>
        <?php endforeach;
    endif;?>

    <form action="" method="POST" id="sender">
        <input type="hidden" name="email" value="<?=$user['email'];?>">
        <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
    </form>
    
    <form action="" target="_blank" method="POST" id="user_enter"></form>
        <div class="buttons-under-form">
            <p class="button-delete">
                <a onclick="return confirm('Вы уверены?')" href="<?=$setting['script_url'];?>/admin/users/del/<?=$user['user_id'];?>?token=<?=$_SESSION['admin_token'];?>" title="Удалить">
                    <i class="icon-remove"></i>Удалить юзера
                </a>
            </p>
            
            <div class="blacklist-but">
                <input type="hidden" form="black_list" name="email" value="<?=$user['email']?>">
                <input type="hidden" form="black_list" name="token" value="<?=$_SESSION['admin_token'];?>">
                <?php $check = User::searchEmailinBL($user['email']);
                if($check == 0):?>
                    <input type="hidden" form="black_list" name="act" value="add">
                    <input class="button-black-rounding" form="black_list" type="submit" value="В чёрный список" name="blacklist">
                <?php else:?>
                    <input type="hidden" form="black_list" name="act" value="delete">
                    <input class="button-green-rounding" form="black_list" type="submit" value="Убрать из чёрного списка" name="blacklist" style="background:#efd943; color:#444; padding:0.3em 10px; border:none; cursor:pointer">
                <?php endif;?>
            </div>
            <div class="reference-button">
                <a href="https://lk.school-master.ru/rdr/46" target="_blank"><i class="icon-answer-2"></i>Справка</a>
            </div>
        </div>

    <form action="" id="black_list" method="POST"></form>
	<form action="" id="spec_aff" method="POST"></form>
    <form action="/admin/orders/add" id="create_order" method="GET">
        <input type="hidden" name="email" value="<?=$user['email'];?>">
        <input type="hidden" name="name" value="<?=$user['user_name'];?>">
        <input type="hidden" name="phone" value="<?=$user['phone'];?>">
        <input type="hidden" name="city" value="<?=$user['city'];?>">
        <input type="hidden" name="address" value="<?=$user['address'];?>">
        <input type="hidden" name="index" value="<?=$user['zipcode'];?>">
    </form>
    <form action="/admin/memberusers/add" id="create_subscribe" method="GET">
        <input type="hidden" name="user_id" value="<?=$user['user_id'];?>">
    </form>

    <div id="ModalCurator" class="uk-modal">
    <div class="uk-modal-dialog">
        <div class="userbox  modal-userbox-2">
            <a href="#close" title="Закрыть" class="uk-modal-close uk-close modal-close"><span class="icon-close"></span></a>
            <div>
                <h3 class="modal-head">Выберите куратора</h3>
                <div class="">
                 <form action="" id="changecurator_id" method="POST" class="select-curator-row">
                    <div class="select-wrap">
                    <select class="select" name="newcurator">
                        <!-- TODO тут надо фильтрованый список кураторов конкретного раздела и тренинга -->
                        <?php $curators = User::getCurators();
                        if($curators):
                        foreach($curators as $curator):?>
                            <option value="<?php echo $curator['user_id']?>"><?php echo $curator['user_name'] .' '. $curator['surname']?></option>
                        <?php endforeach;
                        endif;?>
                    </select>
                    </div>
                    <div class="">
                        <input type="hidden" name="user_id" value="<?= $user['user_id']?>">
                        <input type="hidden"  name="token" value="<?=$_SESSION['admin_token'];?>">
                        <div class="group-button-modal">
                            <button type="submit" name="changecurator" class="button button-green">Назначить</button>
                            <div><button type="submit" name="deletecurator" class="button btn-red-link">Сбросить</button></div>
                        </div>
                    </div>     
                 </form>
                </div>
            </div>
        </div>
    </div>

    <?php function getRegMethod($metod) {
        switch($metod){
            case 'paid':
            return 'покупка продукта';
            break;
            
            case 'free':
            return 'скачивание';
            break;
            
            case 'handmade':
            return 'админом вручную';
            break;

            case 'api':
            return 'добавлен через API';
            break;

            default:
            return 'наверное, это админ)';
            break;
        }
    }
    
    
    function getLessonTask($task) {
        switch($task){
            case 0:
            return 'Без задания';
            break;
            
            case 1:
            return 'Без проверки';
            break;
            
            case 2:
            return 'Автопроверка';
            break;
            
            case 3:
            return 'Ручная проверка';
            break;
        }
    }?>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
  function ChangeCurator(elm){
    
    var addform = document.getElementById('changecurator_id');
    
    var trainingid = document.createElement('input');
    trainingid.type = 'hidden';
    trainingid.name = 'training_id';
    trainingid.value = elm.dataset.setTrainingId;
    addform.appendChild(trainingid);
    
    var sectionid = document.createElement('input');
    sectionid.type = 'hidden';
    sectionid.name = 'section_id';
    sectionid.value = elm.dataset.setSectionId;
    addform.appendChild(sectionid);
    
    var curatorid = document.createElement('input');
    curatorid.type = 'hidden';
    curatorid.name = 'curator_id';
    curatorid.value = elm.dataset.setCuratorId;
    addform.appendChild(curatorid);
    
  };
  jQuery('.datetimepicker').datetimepicker({
    format:'d.m.Y H:i:s',
    lang:'ru'
  });

$(document).ready(function() {
  $('.remove-partner').on('click', function (resp) {
    if (confirm("Отвязать партнера от пользователя ?") == true) {
    let el = $(this);
    let id = el.data('id');
    let partnerid = el.data('partner-id');
    $.post('/admin/users/delpartner/'+id+'/'+partnerid, {del_partner: true}, function (resp) {
      if (resp.status) {
        el.closest('p').remove();
      }
    });
    }
  });
});

</script>
</body>
</html>