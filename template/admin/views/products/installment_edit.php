<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Изменить рассрочку</h1>
        <div class="logout">
            <a href="<?=$setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?=$setting['script_url'];?>/admin/logout" class="red">Выход</a>
        </div>
    </div>
    
    <ul class="breadcrumb">
        <li><a href="/admin">Дашбоард</a></li>
        <li><a href="/admin/installment/">Рассрочки</a></li>
        <li>Изменить рассрочку</li>
    </ul>
    
    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/installment.svg" alt="">
                </div>

                <div>
                    <h3 class="traning-title mb-0">Изменить рассрочку</h3>
                </div>
            </div>
            
            <ul class="nav_button">
                <li><input type="submit" name="edit" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/installment/">Закрыть</a></li>
            </ul>
        </div>

        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Одобрение</li>
                <li>Напоминания</li>
                <li>Письма просрочки</li>
                <li>Письма о погашении</li>
            </ul>
            
            <div class="admin_form">
                <div>
                    <div class="row-line">
                        <div class="col-1-2">
                            <h4>Основное</h4>
                            <p class="width-100"><label>Название</label>
                                <input type="text" maxlength="127" name="name" value="<?=$installment['title'];?>" placeholder="Название" required="required">
                            </p>

                            <p class="width-100"><label class="number-payments">Кол-во платежей: <?=$installment['max_periods'];?></label>
                                <input type="hidden" value="<?=$installment['max_periods'];?>" name="max_periods">
                            </p>

                            <p class="min-label-wrap"><label>Периодичность платежей<span class="min-label">дней</span></label>
                                <input type="text" value="<?=$installment['period_freq'];?>" name="period_freq" placeholder="Время периода">
                            </p>

                            <div class="width-100"><label>Дата второго платежа</label>
                                <div class="datetimepicker-wrap">
                                    <input class="datetimepicker" type="text" autocomplete="off" name="date_second_payment" value="<?=isset($installment['date_second_payment']) ? date('d.m.Y', $installment['date_second_payment']) : '';?>">
                                </div>
                            </div>

                            <div class="width-100"><label>Статус</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="status" type="radio" value="1" <?php if($installment['enable'] == 1) echo 'checked=""'?>><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="status" type="radio" value="0" <?php if($installment['enable'] == 0) echo 'checked=""'?>><span>Откл</span></label>
                                </span>
                                <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                            </div>
							
							<div class="width-100" title="Это когда платёж состоит из 2-х частей"><label>Предоплата <span class="result-item-icon" data-toggle="popover" data-content="Рассрочка превращается в предоплату. Будет выведена в отдельной вкладке «Предоплата» на странице оплаты." data-original-title="" title=""><i class="icon-answer"></i></span></label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="prepayment" type="radio" value="1"<?php if($installment['prepayment'] == 1) echo ' checked=""'?>><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="prepayment" type="radio" value="0"<?php if($installment['prepayment'] == 0) echo ' checked=""'?>><span>Откл</span></label>
                                </span>
                            </div>
                        </div>


                        <div class="col-1-2">
                            <h4>Дополнительно</h4>
                            <p class="width-100"><label>Мин. сумма для рассрочки</label>
                                <input type="text" value="<?=$installment['minimal'];?>" name="minimal">
                            </p>

                            <p class="width-100"><label>Увеличить сумму рассрочки</label>
                                <input type="text" value="<?=$installment['increase'];?>" name="increase">
                            </p>

                            <p class="width-100"><label>Сортировка</label>
                                <input type="text" value="<?=$installment['sort'];?>" name="sort">
                            </p>
                        </div>

                        <div class="col-1-1 mb-0">
                            <h4>Платежи</h4>
                        </div>

                        <div class="col-1-2">
                            <p class="px-label-wrap"><label>Первый платёж<span class="px-label">%</span></label>
                                <input type="text" value="<?=$installment['first_pay']?>" name="first_pay" placeholder="Первый платёж, %">
                            </p>

                            <p class="px-label-wrap"><label>Остальные платежи<span class="px-label">%</span></label>
                                <input type="text" name="other_pay" value="<?=$installment['other_pay'];?>" placeholder="Другие платежи, %">
                            </p>
                        </div>

                        <div class="col-1-2">
                            <p class="min-label-wrap"><label>Считать просроченным через<span class="min-label">дней</span></label>
                                <input type="text" name="expired" value="<?=$installment['expired']?>">
                            </p>

                            <p class="width-100"><label>При просрочке увеличить сумму на</label>
                                <input type="text" name="sanctions" value="<?=$installment['sanctions']?>">
                            </p>
                        </div>

                        <div class="col-1-1">
                            <h4>Рассрочка (описание, условия)</h4>
                            <p class="width-100" title="Выводится под графиком платежей"><label>Краткое описание условий рассрочки <span class="result-item-icon" data-toggle="popover" data-content="Выводится на странице оформления рассрочки" data-original-title="" title=""><i class="icon-answer"></i></span></label>
                                <textarea class="editor" name="installment_desc"><?=$installment['installment_desc'];?></textarea>
                            </p>

                            <p class="width-100"><label>Текст условий договора рассрочки <span class="result-item-icon" data-toggle="popover" data-content="Выводится в отдельном окне при клике на ссылку" data-original-title="" title=""><i class="icon-answer"></i></span></label>
                                <textarea class="editor" name="installment_rules"><?=$installment['installment_rules'];?></textarea>
                            </p>
                        </div>
                    </div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0"><h4>Поля для заполнения</h4></div>
                        <div class="col-1-2">

                            <p class="width-100"><label>Фамилия</label>
                                <select name="fields[surname]">
                                    <option value="0"<?php if(@$fields['surname'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if(@$fields['surname'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if(@$fields['surname'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>

                            <p class="width-100"><label>Отчество</label>
                                <select name="fields[patronymic]">
                                    <option value="0"<?php if(@$fields['patronymic'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if(@$fields['patronymic'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if(@$fields['patronymic'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>
                        </div>
                        <div class="col-1-2">

                            <p class="width-100"><label>Серия и номер паспорта</label>
                                <select name="fields[passport]">
                                    <option value="0"<?php if($fields['passport'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if($fields['passport'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if($fields['passport'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>

                            <p class="width-100"><label>Город и адрес</label>
                                <select name="fields[address]">
                                    <option value="0"<?php if($fields['address'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if($fields['address'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if($fields['address'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>
                        </div>
                        <div class="col-1-2">
                            <p class="width-100"><label>Скан 1</label>
                                <select name="fields[skan1]">
                                    <option value="0"<?php if($fields['skan1'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if($fields['skan1'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if($fields['skan1'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>

                            <p class="width-100"><label>Скан 2</label>
                                <select name="fields[skan2]">
                                    <option value="0"<?php if($fields['skan2'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if($fields['skan2'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if($fields['skan2'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>
                        </div>
                        <div class="col-1-2">
                            <p class="width-100"><label>Номер телефона</label>
                                <select name="fields[phone]">
                                    <option value="0"<?php if(@$fields['phone'] == 0) echo ' selected="selected"';?>>Не показывать</option>
                                    <option value="1"<?php if(@$fields['phone'] == 1) echo ' selected="selected"';?>>Показывать</option>
                                    <option value="2"<?php if(@$fields['phone'] == 2) echo ' selected="selected"';?>>Показывать + обязательное</option>
                                </select>
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <h4>Автоматическое одобрение</h4>
                                <div class="width-100">

                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input name="approve" type="radio" value="1" <?php if($installment['approve'] == 1) echo 'checked=""'?>><span>Вкл</span></label>
                                    <label class="custom-radio"><input name="approve" type="radio" value="0" <?php if($installment['approve'] == 0) echo 'checked=""'?>><span>Откл</span></label>
                                </span>
                            </div>
                        </div>


                        <div class="col-1-1">
                            <h4>Сообщение на сайте после отправки заявки</h4>
                            <p class="width-100">
                                <textarea class="editor" name="letters[waiting]"><?=@$letters['waiting'];?></textarea>
                            </p>
                        </div>

                        <div class="col-1-1 mt-10 mb-0">
                            <h4>SMS-уведомления</h4>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100 mb-0">
                                <label>Отправлять SMS при одобрении</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input data-show_on="text_good" name="sms[send_good]" type="radio" value="1" <?php if(@$sms['send_good'] == 1) echo 'checked=""';?>><span>Отправить</span></label>
                                    <label class="custom-radio"><input name="sms[send_good]" type="radio" value="0" <?php if(@$sms['send_good'] == 0) echo 'checked=""';?>><span>Откл</span></label>
                                </span>
                            </div>

                            <div class="width-100 mt-20 hidden" id="text_good">
                                <label>SMS сообщение <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span></label>
                                <textarea data-counting-characters data-max_length="1000" cols="50" rows="6" name="sms[text_good]"><?=@$sms['text_good'];?></textarea>
                                <div class="counting-characters">
                                    <span class="counting-characters_count"><?=@strlen($sms['text_good']);?></span>/<span class="counting-characters_max-length">1000</span>, sms:
                                    <span class="counting-characters_count-sms"><?=@System::getCountSMS($sms['text_good']);?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-2">
                            <div class="width-100 mb-0">
                                <label>Отправлять SMS при отказе</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio"><input data-show_on="text_bad" name="sms[send_bad]" type="radio" value="1" <?php if(@$sms['send_bad'] == 1) echo 'checked=""';?>><span>Отправить</span></label>
                                    <label class="custom-radio"><input name="sms[send_bad]" type="radio" value="0" <?php if(@$sms['send_bad'] == 0) echo 'checked=""';?>><span>Откл</span></label>
                                </span>
                            </div>

                            <div class="width-100 mt-20 hidden" id="text_bad">
                                <label>SMS сообщение <span class="result-item-icon" data-toggle="popover" data-content="SMS может отправить сообщение до 1000 символов. Одно сообщение 67 символов кириллицы и 160 символов латиницы."><i class="icon-answer"></i></span></label>
                                <textarea data-counting-characters data-max_length="1000" cols="50" rows="6" name="sms[text_bad]"><?=@$sms['text_bad'];?></textarea>
                                <div class="counting-characters">
                                    <span class="counting-characters_count"><?=@strlen($sms['text_bad']);?></span>/<span class="counting-characters_max-length">1000</span>, sms:
                                    <span class="counting-characters_count-sms"><?=@System::getCountSMS($sms['text_bad']);?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-1-1">
                            <h4>Письма (email)</h4>
                            <p class="width-100"><label>Тема письма одобрения</label>
                                <input type="text" name="letters[subject_good]" value="<?=@$letters['subject_good'];?>">
                            </p>

                            <p class="width-100"><label>Письмо одобрения</label>
                                <textarea class="editor" name="letters[letter_good]"><?=@$letters['letter_good'];?></textarea>
                            </p>

                            <p class="width-100"><hr /></p>

                            <p class="width-100"><label>Тема письма отказа</label>
                                <input type="text" name="letters[subject_bad]" value="<?=@$letters['subject_bad'];?>">
                            </p>

                            <p class="width-100"><label>Письмо отказа</label>
                                <textarea class="editor" name="letters[letter_bad]"><?=@$letters['letter_bad'];?></textarea>
                            </p>
                        </div>


                        <div class="col-1-1 mt-10">
                            <div class="gray-block-2">
                                <p><strong>Переменные для подстановки в сообщения:</strong></p>
                                <p>
                                   [CLIENT_NAME] - имя клиента<br/>
                                   [EMAIL] - email клиента<br/>
                                   [ORDER] - номер заказа<br/>
                                   [LINK] - ссылка на заказ<br/>
                                   [SUMM] - сумма заказа<br/>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <h4>Напоминания о платежах</h4>
                        </div>
                    </div>

                    <div class="menu-apsell">
                        <ul>
                            <li>Напоминание №1</li>
                            <li>Напоминание №2</li>
                            <li>Напоминание №3</li>
                        </ul>

                        <div>
                            <div>
                                <div class="row-line">
                                    <div class="col-1-2">
                                        <p class="min-label-wrap"><label>Отправить за (n часов)<span class="min-label">час.</span></label>
                                            <input type="text" name="notif[send_1_time]" value="<?=@$notif['send_1_time']?>">
                                        </p>

                                        <div class="width-100 mb-0">
                                            <label>Отправлять SMS</label>
                                            <span class="custom-radio-wrap">
                                                <label class="custom-radio">
                                                    <input data-show_on="send_1_smstext" name="notif[send_1_sms]" type="radio" value="1"<?php if(@$notif['send_1_sms'] == 1) echo ' checked=""'?>><span>Отправить</span>
                                                </label>

                                                <label class="custom-radio">
                                                    <input name="notif[send_1_sms]" type="radio" value="0"<?php if(@$notif['send_1_sms'] == 0) echo ' checked=""'?>><span>Откл</span>
                                                </label>
                                            </span>
                                        </div>
                                        <div class="width-100 hidden mt-20" id="send_1_smstext">
                                            <label>Текст SMS сообщения</label>
                                            <textarea name="notif[send_1_smstext]" data-counting-characters data-max_length="1000"><?=@$notif['send_1_smstext']?></textarea>
                                            <div class="counting-characters">
                                                <span class="counting-characters_count"><?=@strlen($notif['send_1_smstext']);?></span>/<span class="counting-characters_max-length">1000</span>, sms:
                                                <span class="counting-characters_count-sms"><?=@System::getCountSMS($notif['send_1_smstext']);?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-1-1">
                                        <div class="width-100 mb-0">
                                            <label>Отправлять Email</label>
                                            <span class="custom-radio-wrap">
                                                <label class="custom-radio"><input data-show_on="send_1_text" name="notif[send_1_email]" type="radio" value="1"<?php if(@$notif['send_1_email'] == 1) echo ' checked=""'?>><span>Отправить</span></label>
                                                <label class="custom-radio"><input name="notif[send_1_email]" type="radio" value="0"<?php if(@$notif['send_1_email'] == 0) echo ' checked=""'?>><span>Откл</span></label>
                                            </span>
                                        </div>
                                        <div class="width-100 hidden mt-20" id="send_1_text">
                                            <p><label>Тема для Email сообщения</label>
                                                <input type="text" name="notif[send_1_subject]" value="<?=@$notif['send_1_subject']?>">
                                            </p>
                                            <div>
                                                <label>Текст для Email сообщения</label>
                                                <textarea class="editor" name="notif[send_1_text]"><?=@$notif['send_1_text']?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="row-line">
                                    <div class="col-1-2">
                                        <p class="min-label-wrap"><label>Отправить за (n часов)<span class="min-label">час.</span></label>
                                            <input type="text" name="notif[send_2_time]" value="<?=@$notif['send_2_time']?>">
                                        </p>
                                        <div class="width-100 mb-0">
                                            <label>Отправлять SMS</label>
                                            <span class="custom-radio-wrap">
                                                <label class="custom-radio"><input data-show_on="send_2_smstext" name="notif[send_2_sms]" type="radio" value="1"<?php if(@$notif['send_2_sms'] == 1) echo ' checked=""'?>><span>Отправить</span></label>
                                                <label class="custom-radio"><input name="notif[send_2_sms]" type="radio" value="0"<?php if(@$notif['send_2_sms'] == 0) echo ' checked=""'?>><span>Откл</span></label>
                                            </span>
                                        </div>
                                        <div class="width-100 hidden mt-20" id="send_2_smstext">
                                            <label>Текст SMS сообщения</label>
                                            <textarea data-counting-characters data-max_length="1000" name="notif[send_2_smstext]"><?=@$notif['send_2_smstext']?></textarea>
                                            <div class="counting-characters">
                                                <span class="counting-characters_count"><?=@strlen($notif['send_2_smstext']);?></span>/<span class="counting-characters_max-length">1000</span>, sms:
                                                <span class="counting-characters_count-sms"><?=@System::getCountSMS($notif['send_2_smstext']);?></span>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-1-1">
                                        <div class="width-100 mb-0">
                                            <label>Отправлять Email</label>
                                            <span class="custom-radio-wrap">
                                                <label class="custom-radio"><input data-show_on="send_2_text" name="notif[send_2_email]" type="radio" value="1"<?php if(@$notif['send_2_email'] == 1) echo ' checked=""'?>><span>Отправить</span></label>
                                                <label class="custom-radio"><input name="notif[send_2_email]" type="radio" value="0"<?php if(@$notif['send_2_email'] == 0) echo ' checked=""'?>><span>Откл</span></label>
                                            </span>
                                        </div>
                                        <div class="width-100 hidden mt-20" id="send_2_text">
                                            <p><label>Тема для Email сообщения</label>
                                                <input type="text" name="notif[send_2_subject]" value="<?=@$notif['send_2_subject']?>">
                                            </p>

                                            <div><label>Текст для Email сообщения</label>
                                                <textarea class="editor" name="notif[send_2_text]"><?=@$notif['send_2_text']?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="row-line">
                                    <div class="col-1-2">
                                        <p class="min-label-wrap"><label>Отправить за (n часов)<span class="min-label">час.</span></label>
                                            <input type="text" name="notif[send_3_time]" value="<?=@$notif['send_3_time']?>">
                                        </p>

                                        <div class="width-100 mb-0">
                                            <label>Отправлять SMS</label>
                                            <span class="custom-radio-wrap">
                                                <label class="custom-radio"><input data-show_on="send_3_smstext" name="notif[send_3_sms]" type="radio" value="1"<?php if(@$notif['send_3_sms'] == 1) echo ' checked=""'?>><span>Отправить</span></label>
                                                <label class="custom-radio"><input name="notif[send_3_sms]" type="radio" value="0"<?php if(@$notif['send_3_sms'] == 0) echo ' checked=""'?>><span>Откл</span></label>
                                            </span>
                                        </div>

                                        <div class="width-100 hidden mt-20" id="send_3_smstext">
                                            <label>Текст SMS сообщения</label>
                                            <textarea data-counting-characters data-max_length="1000" name="notif[send_3_smstext]"><?=@$notif['send_3_smstext']?></textarea>
                                            <div class="counting-characters">
                                                <span class="counting-characters_count"><?=@strlen($notif['send_3_smstext']);?></span>/<span class="counting-characters_max-length">1000</span>, sms:
                                                <span class="counting-characters_count-sms"><?=@System::getCountSMS($notif['send_3_smstext']);?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-1-1">
                                        <div class="width-100 mb-0">
                                            <label>Отправлять Email</label>
                                            <span class="custom-radio-wrap">
                                                <label class="custom-radio"><input data-show_on="send_3_text" name="notif[send_3_email]" type="radio" value="1"<?php if(@$notif['send_3_email'] == 1) echo ' checked=""'?>><span>Отправить</span></label>
                                                <label class="custom-radio"><input name="notif[send_3_email]" type="radio" value="0"<?php if(@$notif['send_3_email'] == 0) echo ' checked=""'?>><span>Откл</span></label>
                                            </span>
                                        </div>
                                        <div class="width-100 hidden mt-20" id="send_3_text">
                                            <p><label>Тема для Email сообщения</label>
                                                <input type="text" name="notif[send_3_subject]" value="<?=@$notif['send_3_subject']?>">
                                            </p>
                                            <p><label>Текст для Email сообщения</label>
                                                <textarea class="editor" name="notif[send_3_text]"><?=@$notif['send_3_text']?></textarea>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="gray-block-2">
                                <p><strong>Переменные для подстановки в сообщения:</strong></p>
                                <p>
                                    [CLIENT_NAME] - имя клиента<br/>
                                    [EMAIL] - email клиента<br/>
                                    [ORDER] - номер заказа<br/>
                                    [LINK] - ссылка на заказ<br/>
                                    [SUMM] - сумма заказа<br/>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <h4>Письма после просрочки платежа</h4>
                        </div>
                    </div>

                    <div class="menu-apsell">
                        <ul>
                            <li>1 письмо</li>
                            <li>2 письмо</li>
                        </ul>
                        <div>
                            <div class="row-line">
                                <div class="col-1-2">
                                    <p class="min-label-wrap"><label>Отправить через (n часов)<span class="min-label">час.</span></label>
                                        <input type="text" name="notif[time_1_after]" value="<?=@$notif['time_1_after']?>">
                                    </p>
                                </div>

                                <div class="col-1-1">
                                    <p class="width-100"><label>Тема для Email сообщения</label><input type="text" name="notif[subject_1_after]" value="<?=@$notif['subject_1_after']?>"></p>
                                    <p class="width-100"><label>Текст для Email сообщения</label><textarea class="editor" name="notif[text_1_after]"><?=@$notif['text_1_after']?></textarea></p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="row-line">
                                <div class="col-1-2">
                                    <p class="min-label-wrap"><label>Отправить через (n часов)<span class="min-label">час.</span></label>
                                        <input type="text" name="notif[time_2_after]" value="<?=@$notif['time_2_after']?>">
                                    </p>
                                </div>

                                <div class="col-1-1">
                                    <p class="width-100"><label>Тема для Email сообщения</label>
                                        <input type="text" name="notif[subject_2_after]" value="<?=@$notif['subject_2_after']?>">
                                    </p>

                                    <p class="width-100"><label>Текст для Email сообщения</label>
                                        <textarea class="editor" name="notif[text_2_after]"><?=@$notif['text_2_after']?></textarea>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <h4>Письма о погашении рассрочки</h4>
                        </div>
                    </div>
                    <div class="menu-apsell">
                        <ul>
                            <li>После оплаты очередного платежа</li>
                            <li>После погашения целиком</li>
                            <li>Дополнительное письмо</li>
                        </ul>
                        <div>
                            <div>
                                <p class="width-100"><label>Тема письма клиенту после оплаты очередного платежа</label>
                                    <input type="text" name="letters[subject_pay]" value="<?php if(isset($letters['subject_pay'])) echo $letters['subject_pay'];?>">
                                </p>

                                <p class="width-100"><label>Письмо клиенту после оплаты очередного платежа</label>
                                    <textarea class="editor" name="letters[letter_pay]"><?=@$letters['letter_pay'];?></textarea>
                                </p>

                            </div>
                            <div>
                                <p class="width-100"><label>Тема письма клиенту после погашения рассрочки</label>
                                    <input type="text" name="letters[subject_client_end]" value="<?=isset($letters['subject_client_end']) ? $letters['subject_client_end'] : '';?>">
                                </p>

                                <p class="width-100"><label>Письмо клиенту после погашения рассрочки</label>
                                    <textarea class="editor" name="letters[letter_client_end]"><?=isset($letters['letter_client_end']) ? $letters['letter_client_end'] : '';?></textarea>
                                </p>
                            </div>
                            <div>
                                <p class="width-100"><label>Тема дополнительного письма после погашения рассрочки</label>
                                    <input type="text" name="letters[subject_end]" value="<?=isset($letters['subject_end']) ? $letters['subject_end']: '';?>">
                                </p>

                                <p class="width-100"><label>Email куда отправить доп.письмо</label>
                                    <input type="text" name="letters[email_end]" value="<?=isset($letters['email_end']) ? $letters['email_end'] : '';?>">
                                </p>

                                <p class="width-100"><label>Текст доп.письма после после погашения рассрочки</label>
                                    <textarea class="editor" name="letters[letter_end]"><?=isset($letters['letter_end']) ? $letters['letter_end'] : '';?></textarea>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row-line">
                        <div class="col-1-1">
                            <div class="gray-block-2">
                                <p><strong>Переменные для подстановки в письма и SMS:</strong></p>
                                <p>
                                    [CLIENT_NAME] - имя клиента<br/>
                                    [EMAIL] - email клиента<br/>
                                    [ORDER] - номер заказа<br/>
                                    [LINK] - ссылка на заказ<br/>
                                    [SUMM] - сумма заказа<br/>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="buttons-under-form">
        <p class="button-delete">
            <a onclick="return confirm('Вы уверены?')" href="#"><i class="icon-remove"></i>Удалить</a>
        </p>
        <div class="reference-button">
            <a href="https://lk.school-master.ru/rdr/48" target="_blank"><i class="icon-answer-2"></i>Справка</a>
        </div>
    </div>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
  jQuery('.datetimepicker').datetimepicker({
    format:'d.m.Y',
    lang:'ru'
  });
</script>
</body>
</html>