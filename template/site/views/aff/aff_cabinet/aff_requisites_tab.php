<?php defined('BILLINGMASTER') or die;?>
<!-- 6 Реквизиты -->
<div class="container requisites">
    <?php $req = unserialize($req['requsits']);
    $req_data = explode("\r\n", $params['params']['req']);
    if($req_data && array_filter($req_data, 'strlen')):?>
        <form action="" method="POST">
            <?php foreach($req_data as $req_item):
                if (strpos($req_item, '=') === false) {
                    continue;
                }
                list($req_key, $req_val) = explode("=", $req_item);
                if($req_key != 'rs'):?>
                    <div class="h4 requisites__subtitle"><?=$req_val;?></div>
                    <div class="modal-form-line">
                        <input
                                type="text"
                                name="req[<?=$req_key;?>]"
                                value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key] : '';?>"
                        />
                    </div>
                <?php else:?>
                    <div class="container">
                        <div class="form-section form-section_two">
                            <h2>Данные партнёра</h2>
                            <label for="fio"> ФИО </label>
                            <input
                                    type="text"
                                    id="fio"
                                    placeholder="Введите ФИО как в документе"
                                    pattern="[А-Яа-яЁёA-Za-z\s]+"
                                    title="ФИО должно содержать только буквы"
                                    name="req[<?=$req_key;?>][fio]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['fio'] : '';?>"
                            />

                            <label for="inn"> Дата рождения </label>
                            <input
                                    type="date"
                                    id="birthday"
                                    placeholder="Введите дату рождения 1990-01-24"
                                    minlength="8"
                                    maxlength="10"
                                    min="1950-01-01"
                                    max="2099-12-31"
                                    title="Не верная дата"
                                    name="req[<?=$req_key;?>][birthday]"
                                    value="<?=!empty($req) && isset($req[$req_key]['birthday']) && array_key_exists($req_key, $req) ? $req[$req_key]['birthday'] : '';?>"
                            />

                            <label for="org-name"> Название организации </label>

                            <small class="important-info">
                                *Важная информация! Если вы оформлены как ИП или ООО, то поле
                                "Название организации" оформляется как: - ИП Иванов Иван Иванович -
                                ООО "Ромашка". Для самозанятых ФИО полностью.
                            </small>
                            <input
                                    type="text"
                                    id="org-name"
                                    placeholder="Введите название организации"
                                    pattern="[А-Яа-яЁёA-Za-z\s]+"
                                    title="Название должно содержать только буквы"
                                    name="req[<?=$req_key;?>][off_name]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['off_name'] : '';?>"
                            />

                            <label for="inn"> ИНН </label>
                            <input
                                    type="text"
                                    id="inn"
                                    placeholder="Введите ИНН"
                                    minlength="10"
                                    maxlength="12"
                                    pattern="\d{10}|\d{12}"
                                    title="ИНН юр. лиц должен содержать 10 цифр, ИП и самозанятые — 12 цифр"
                                    name="req[<?=$req_key;?>][inn]"
                                    value="<?=!empty($req) && isset($req[$req_key]['inn']) && array_key_exists($req_key, $req) ? $req[$req_key]['inn'] : '';?>"
                            />
                        </div>
                        <div class="form-section form-section_second">
                            <h2>Данные для выплат</h2>
                            <label for="account-number"> Номер счёта </label>
                            <input
                                    type="text"
                                    id="account-number"
                                    placeholder="0000 0000 0000 0000 0000"
                                    pattern="\d{20}"
                                    title="Расчетный счет должен содержать 20 цифр"
                                    name="req[rs][rs]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['rs'] : ''?>"
                            />

                            <label for="bank-bik"> БИК банка </label>
                            <input
                                    type="text"
                                    id="bank-bik"
                                    placeholder="000 000 000"
                                    pattern="\d{9}"
                                    title="БИК должен содержать 9 цифр"
                                    name="req[<?=$req_key;?>][bik]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['bik'] : '';?>"
                            />

                            <label for="payment-org-name"> Название банка </label>
                            <input
                                    type="text"
                                    id="payment-org-name"
                                    placeholder="Введите название организации"
                                    name="req[<?=$req_key;?>][name]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['name'] : '';?>"
                            />

                            <label for="corr-account"> Корреспондентский счет </label>
                            <input
                                    type="text"
                                    id="corr-account"
                                    placeholder="0000 0000 0000 0000 0000 0000"
                                    pattern="\d{20}"
                                    title="Корреспондентский счет должен содержать 20 цифр"
                                    name="req[rs][rs2]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['rs2'] : ''?>"
                            />
                        </div>
                        <div class="form-section form-section_three">
                            <h2>Паспортные данные</h2>
                            <label for="account-number"> Серия и номер паспорта </label>
                            <input
                                    type="text"
                                    id="passport"
                                    pattern="\d{4}\s\d{6}"
                                    minlength="11"
                                    maxlength="11"
                                    placeholder="1234 123456"
                                    title="Введите через пробел: серия - 4 цифры, номер - 6 цифр"
                                    name="req[<?=$req_key;?>][passport]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['passport'] : ''?>"
                            />

                            <label for="bank-bik"> Место рождения </label>
                            <input
                                    type="text"
                                    id="birth-place"
                                    pattern="[А-Яа-яЁё\s\.\-]+"
                                    minlength="3"
                                    maxlength="100"
                                    placeholder="г. Свердловск"
                                    title="Введите место рождения"
                                    name="req[<?=$req_key;?>][birth-place]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['birth-place'] : '';?>"
                            />

                            <label for="payment-org-name"> Дата выдачи паспорта </label>
                            <input
                                    type="date"
                                    id="passport-date"
                                    min="1950-01-01"
                                    max="2099-12-31"
                                    placeholder="Введите дату выдачи 1990-01-24"
                                    name="req[<?=$req_key;?>][passport-date]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['passport-date'] : '';?>"
                            />

                            <label for="corr-account"> Адрес регистрации </label>
                            <small class="important-info">
                                *Важная информация! <br />
                                Для ФЛ адрес прописки как в паспорте <br />
                                Для ООО - юридический адрес
                            </small>
                            <input
                                    type="text"
                                    id="passport-address"
                                    minlength="10"
                                    maxlength="200"
                                    placeholder="683031, г. Петропавловск-Камчатский, пр-кт. Карла Маркса, д. 21/1, офис 305"
                                    title="Введите адрес регистрации"
                                    name="req[<?=$req_key;?>][passport-address]"
                                    value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['passport-address'] : ''?>"
                            />

                            <button
                                    type="submit"
                                    class="submit-btn button"
                                    value="Сохранить"
                                    name="save_req"
                            >
                                Сохранить
                            </button>
                        </div>
                    </div>
                <?php endif;
            endforeach;?>
        </form>
    <?php endif;?>
</div>
