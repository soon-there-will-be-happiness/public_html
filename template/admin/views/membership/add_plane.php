<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
<div class="top-wrap">
    <h1>Создать план подписки</h1>
    <div class="logout">
        <a href="/" target="_blank">Перейти на сайт</a>
        <a href="/admin/logout/" class="red">Выход</a>
    </div>
</div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/membersubs/">Подписки</a></li>
        <li>Создать план подписки</li>
    </ul>
    
    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div><img src="/template/admin/images/ext/membership.svg" alt=""></div>
                <div><h3 class="traning-title mb-0">Создать план подписки</h3></div>
            </div>
            
            <ul class="nav_button">
                <li><input type="submit" name="add" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/membersubs/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Доступ</li>
                <li>Письма</li>
            </ul>
        
            <div class="admin_form">
                <!-- ОСНОВНОЕ -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0"><h4>Общие настройки</h4></div>
                        <div class="col-1-2">
                            <p><label>Название плана подписки</label>
                                <input type="text" name="name" placeholder="Название плана" required="required">
                            </p>
                            
                            <p><label>Служебное название</label>
                                <input type="text" name="service_name">
                            </p>
                            
                            <p><label>Статус</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1" checked><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0"><span>Откл</span></label>
                                </span>
                            </p>
                            
                            <p><label>Тип продления</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input data-show_on="recurrent,for_recurrent" name="recurrent_enable" type="radio" value="1"><span>Рекурренты</span></label>
                                    <label class="custom-radio"><input data-show_on="from_sm" name="recurrent_enable" type="radio" value="0" checked><span>School-Master</span></label>
                                </span>
                            </p>
                        </div>

                        <div class="col-1-2">

                            <div class="width-100"><label>Описание</label>
                                <textarea name="subs_desc" cols="35" rows="3"></textarea>
                                <input type="hidden" name="first_time" value="0">
                                <input type="hidden" name="first_time_data" value="">
                            </div>

                        </div>
                    </div>

                    <div class="row-line">
                        <div class="col-1-1 mb-0"><h4>Настройка периодов</h4></div>
                        <div class="col-1-2">

                            <div class="width-100"><label>Тип периода</label>
                                <div class="select-wrap">
                                    <select name="period_type">
                                        <option value="Month">Месяцы</option>
                                        <option value="Week">Недели</option>
                                        <option value="Day">Дни</option>
                                    </select>
                                </div>
                            </div>
                            <p><label title="Здесь указываем кол-во (дней, недель или месяцев)">Период (значение)</label>
                                <input type="text" name="lifetime">
                            </p>
                        </div>
                        <div class="col-1-2 hidden" id="for_recurrent">
                            <p><label title="Сколько раз списывать оплату">Максимальное кол-во периодов</label>
                                <input type="text" name="max_periods">
                            </p>
                            <p class="min-label-wrap"><label title="Это время, когда нужно начинать регулярные платежи (первый платёж был установочный, не регулярный)">Отсрочка 1-го регулярного платежа<span class="min-label">дни</span></label>
                                <input type="text" name="delay">
                            </p>
                        </div>
                    </div>

                    <div class="row-line hidden" id="recurrent">
                        <div class="col-1-1 mb-0"><h4>Настройка платежей</h4></div>
                        <div class="col-1-2">
                        
                            <p><label title="Сумма для регулярного списания, по умолчанию совпадает с суммой первого (установочного) платежа">Сумма регулярного платежа</label>
                                <input type="text" name="amount">
                            </p>

                            <p id="recurrent_label"><label title="Галочка при рекуррентах">Подпись чекбокса при создании регулярных платежей</label>
                                <input type="text" name="recurrent_label">
                            </p>
                            
                            <div class="width-100"><label>При оплате использовать только эти платёжные системы</label>
                                <div>
                                    <select class="multiple-select" name="select_payments[]" multiple="multiple">
                                        <?php $payments = Order::getPayments();
                                        if($payments):
                                        foreach($payments as $payment):?>
                                        <option value="<?=$payment['payment_id'];?>"><?=$payment['title'];?></option>
                                        <?php endforeach;
                                        endif;?>
                                    </select>
                                </div>
                            </div>
        
                            <input type="hidden" name="access_id" value="0">
                            <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                        </div>
                    </div>
                        
                        
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Настройка продления</h4>
                        </div>
                        <div class="col-1-2">
                            <div class="width-100 hidden" id="from_sm">
                                <label>Ссылка в письме продления подписки ведёт на</label>
                                <div class="select-wrap">
                                    <select name="renewal_type">
                                        <option value="0">Не выбрано</option>
                                        <option value="1">Страница заказа продукта</option>
                                        <option value="2">Страница описания продукта</option>
                                        <option data-show_on="the_link" value="3">Свой лендинг</option>
                                    </select>
                                </div>
                            </div>
    
                            <div class="width-100" id="the_product">
                                <label>Выберите продукт для продления</label>
                                <div class="select-wrap">
                                    <select name="renewal_product">
                                        <option value="0">Не выбран</option>
                                        <?php $product_list = Product::getProductListOnlySelect();
                                        foreach($product_list as $product):?>
                                            <option value="<?=$product['product_id'];?>"><?=$product['product_name'];?></option>
                                            <?php if($product['service_name']):?>
                                                <option disabled="disabled" class="service-name">(<?=$product['service_name'];?>)</option>
                                            <?php endif;
                                        endforeach;?>
                                    </select>
                                </div>
                            </div>

                            <div class="width-100 hidden" id="the_link">
                                <label>если ссылка, укажите</label>
                                <input type="text" name="renewal_link" placeholder="http://">
                            </div>
                        </div>
                        <div class="col-1-2">                            
                            <div class="width-100">
                                <label>Продлевать</label>
                                <div class="select-wrap">
                                    <select name="prolong_active">
                                        <option value="0" data-show_on="extension_from_select">Любую, даже законченную подписку</option>
                                        <option value="1" data-show_on="prolong_link">Только активную подписку</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="width-100 hidden" id="prolong_link">
                                <label>Ссылка для продления, если подписка закончилась: </label>
                                <input type="text" name="prolong_link" placeholder="http://">
                            </div>
                            
                            <div class="width-100" id="extension_from_select">
                                <label>Продлевать завершенную подписку:</label>
                                <div class="select-wrap">
                                    <select name="extension_from_type">
                                        <option value="0">От даты завершения</option>
                                        <option value="1">От даты продления</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                
                
                <!-- ДОСТУП  -->
                <div>
    
                    <div class="row-line">
                        <div class="col-1-1 mb-0">
                            <h4>Окончание действия</h4>
                        </div>
        
                        <div class="col-1-2">
                            <?php $group_list = User::getUserGroups();?>
            
                            <div class="width-100"><label>При завершении действия плана удалить группы пользователя</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="del_groups[]">
                                    <?php if($group_list):
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>">
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
        
                        <div class="col-1-2">
                            <div class="width-100"><label>При завершении действия плана добавить группы пользователю</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="add_groups[]">
                                    <?php if($group_list):
                                        foreach($group_list as $user_group):?>
                                            <option value="<?=$user_group['group_id'];?>">
                                                <?=$user_group['group_title'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>

                            <div class="width-100"><label>При завершении действия плана добавить план подписки пользователю</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="add_planes[]">
                                <?php $plane_list = Member::getPlanes();
                                     if($plane_list):
                                        foreach($plane_list as $plane):?>
                                            <option value="<?=$plane['id'];?>">
                                                <?=$plane['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                        
                        <!--  РАСШИРЕНИЕ TELEGRAM-->
                        <?php if (System::CheckExtensension('telegram', 1)) {
                            require_once(ROOT . '/extensions/telegram/views/membership/add_plane.php');
                        };?>
                        
                        <div class="col-1-1 mb-0">
                            <h4>Письмо куратору</h4>
                        </div>
                        
                        <div class="col-1-2">
                            <p class="width-100"><label>Тема письма</label>
                                <input type="text" name="subj_manager">
                            </p>
                        </div>
                        
                        <div class="col-1-2">
                            <p class="width-100" title="Если оставить поле пустым, то письмо НЕ отправится"><label>Email</label>
                                <input type="email" name="email_manager" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$">
                            </p>
                        </div>
                        
                        <div class="col-1-1">
                            <div class="label">Содержание письма:</div>
                            <div class="width-100">
                                <textarea name="letter_manager" class="editor" rows="6"></textarea>
                            </div>
                            
                            <div class="width-100">
                                <p>Переменные для подстановки: <br />
                                [NAME] - имя клиента<br />[SURNAME] - фамилия клиента<br />[EMAIL] - Email клиента<br />
                                [NICK_TG] - ник в Telegram<br />[NICK_IG] - ник в Instagram
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- Уведомления об окончании плана  -->
                <div>
                    <h4>Уведомления об окончании плана</h4>
                    <div class="notification-end-plan">
                        <div class="notification-end-plan__icon"><i class="icon-info"></i></div>
                        <div class="notification-end-plan__text">
                            <p>Уведомления работают как для ручного, так и для автоматического продления подписок.</p>
                            <p><strong>Важно!</strong> Если у вас рекуррентные платежи, то уведомления о предстоящей дате списания может отправлять платежная система. Например, cloudpayments отправляет уведомления. Учтите это при настройке уведомлений.</p>
                        </div>
                    </div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="menu-apsell">
                            <ul>
                                <li>Уведомление 1</li>
                                <li>Уведомление 2</li>
                                <li>Уведомление 3</li>
                            </ul>
                            
                            <div>
                                <div>
                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <p class="min-label-wrap"><label>Отправить за (n часов)<span class="min-label">час.</span></label>
                                                <input type="text" size="3" name="letter_1_time" placeholder="">
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <div class="width-100"><label>Отправлять SMS</label>
                                                <span class="custom-radio-wrap">
                                                    <label class="custom-radio"><input data-show_on="sms1_text" name="sms1_status" type="radio" value="1"><span>Вкл</span></label>
                                                    <label class="custom-radio"><input name="sms1_status" type="radio" value="0" checked="checked"><span>Откл</span></label>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-1-2"></div>

                                        <div class="col-1-2 hidden" id="sms1_text">
                                            <div class="width-100">
                                                <label>Текст SMS сообщения
                                                    <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
                                                </label>
                                                <textarea name="sms1_text" data-counting-characters data-max_length="1000"></textarea>
                                                <div class="counting-characters">
                                                    <span class="counting-characters_count">0</span>/<span class="counting-characters_max-length">1000</span>, sms:
                                                    <span class="counting-characters_count-sms">1</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <div class="width-100"><label>Отправлять e-mail</label>
                                                <span class="custom-radio-wrap">
                                                    <label class="custom-radio"><input data-show_on="letter_1" name="letter_1_status" type="radio" value="1"><span>Вкл</span></label>
                                                    <label class="custom-radio"><input name="letter_1_status" type="radio" value="0" checked="checked"><span>Откл</span></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row-line hidden mt-20" id="letter_1">
                                        <div class="col-1-1">
                                            <p><label>Тема письма</label>
                                                <input type="text" size="45" name="letter_1_subj" placeholder="">
                                            </p>
                                        </div>

                                        <div class="col-1-1">
                                            <label>Текст письма</label>
                                            <textarea class="editor" name="letter_1"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div>
                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <p class="min-label-wrap"><label>Отправить за (n часов)<span class="min-label">час.</span></label>
                                                <input type="text" size="3" name="letter_2_time" placeholder="">
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <div class="width-100"><label>Отправлять SMS</label>
                                                <span class="custom-radio-wrap">
                                                    <label class="custom-radio"><input data-show_on="sms2_text" name="sms2_status" type="radio" value="1"><span>Вкл</span></label>
                                                    <label class="custom-radio"><input name="sms2_status" type="radio" value="0" checked="checked"><span>Откл</span></label>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-1-2"></div>

                                        <div class="col-1-2 hidden" id="sms2_text">
                                            <div class="width-100">
                                                <label>Текст SMS сообщения
                                                    <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
                                                </label>
                                                <textarea name="sms2_text" data-counting-characters data-max_length="1000"></textarea>
                                                <div class="counting-characters">
                                                    <span class="counting-characters_count">0</span>/<span class="counting-characters_max-length">1000</span>, sms:
                                                    <span class="counting-characters_count-sms">1</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <div class="width-100"><label>Отправлять e-mail</label>
                                                <span class="custom-radio-wrap">
                                                    <label class="custom-radio"><input data-show_on="letter_2" name="letter_2_status" type="radio" value="1"><span>Вкл</span></label>
                                                    <label class="custom-radio"><input name="letter_2_status" type="radio" value="0" checked="checked"><span>Откл</span></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row-line hidden mt-20" id="letter_2">
                                        <div class="col-1-1">
                                            <p><label>Тема письма</label>
                                                <input type="text" size="45" name="letter_2_subj" placeholder="">
                                            </p>
                                        </div>

                                        <div class="col-1-1">
                                            <label>Текст письма</label>
                                            <textarea class="editor" name="letter_2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div>
                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <p class="min-label-wrap"><label>Отправить за (n часов)<span class="min-label">час.</span></label>
                                                <input type="text" size="3" name="letter_3_time" placeholder="">
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <div class="width-100"><label>Отправлять SMS</label>
                                                <span class="custom-radio-wrap">
                                                    <label class="custom-radio"><input data-show_on="sms3_text" name="sms3_status" type="radio" value="1"><span>Вкл</span></label>
                                                    <label class="custom-radio"><input name="sms3_status" type="radio" value="0" checked="checked"><span>Откл</span></label>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-1-2"></div>

                                        <div class="col-1-2 hidden" id="sms3_text">
                                            <div class="width-100">
                                                <label>Текст SMS сообщения
                                                    <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span>
                                                </label>
                                                <textarea name="sms3_text" data-counting-characters data-max_length="1000"></textarea>
                                                <div class="counting-characters">
                                                    <span class="counting-characters_count">0</span>/<span class="counting-characters_max-length">1000</span>, sms:
                                                    <span class="counting-characters_count-sms">1</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row-line">
                                        <div class="col-1-2">
                                            <div class="width-100"><label>Отправлять e-mail</label>
                                                <span class="custom-radio-wrap">
                                                    <label class="custom-radio"><input data-show_on="letter_3" name="letter_3_status" type="radio" value="1"><span>Вкл</span></label>
                                                    <label class="custom-radio"><input name="letter_3_status" type="radio" value="0" checked="checked"><span>Откл</span></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row-line hidden mt-20" id="letter_3">
                                        <div class="col-1-1">
                                            <p><label>Тема письма</label>
                                                <input type="text" size="45" name="letter_3_subj" placeholder="">
                                            </p>
                                        </div>

                                        <div class="col-1-1">
                                            <label>Текст письма</label>
                                            <textarea class="editor" name="letter_3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-1-1">
                            <div class="gray-block-2">
                                <p><strong>Переменные для подстановки:</strong></p>
                                <p>[NAME] - имя клиента<br />
                                [LINK] - ссылка на продление доступа.</p>
                            </div>
                        </div>
                        
                        <div class="col-1-1 mb-0">
                            <h4 title="Запускает отправку писем уведомлений">Команда для планировщика 1 раз в 10 минут</h4>
                        </div>
                        
                        <div class="col-1-1">
                            <textarea>php <?=ROOT . '/task/member_cron.php'?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
    </div>
</body>
</html>