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
                            <input type="text" name="req[<?=$req_key;?>]" value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key] : '';?>">
                        </div>
                    <?php else:?>
                    <div class="container">
                        <div class="form-section form-section_two">
                            <h2>
                                Данные партнёра
                            </h2>
                            <label for="fio">
                                ФИО
                            </label>
                            <input type="text" id="fio" placeholder="Введите ФИО как в документе" pattern="[А-Яа-яЁёA-Za-z\s]+" title="ФИО должно содержать только буквы"
                            name="req[<?=$req_key;?>][fio]"
                            value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['fio'] : '';?>"/>
                            <label for="inn">
                                ИНН
                            </label>
                            <input type="text" id="inn" placeholder="Введите ИНН" minlength="10" maxlength="12" pattern="\d{10}|\d{12}" title="ИНН юр. лиц должен содержать 10 цифр, ИП и самозанятые — 12 цифр" name="req[<?=$req_key;?>][inn]" value="<?=!empty($req) && isset($req[$req_key]['inn']) && array_key_exists($req_key, $req) ? $req[$req_key]['inn'] : '';?>"/>
                            <label for="org-name">
                                Название организации
                            </label>
                            <small class="important-info">
                            *Важная информация! Если вы оформлены как ИП или ООО, то поле
                            "Название организации" оформляется как: - ИП Иванов Иван Иванович -
                            ООО "Ромашка". Для самозанятых ФИО полностью.
                            </small>
                            <input type="text" id="org-name"   placeholder="Введите название организации" pattern="[А-Яа-яЁёA-Za-z\s]+" title="Название должно содержать только буквы" name="req[<?=$req_key;?>][off_name]" value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['off_name'] : '';?>"/>
                        </div>
                        <div class="form-section">
                            <h2>Данные для выплат</h2>
                            <label for="account-number">
                                Номер счёта
                            </label>
                            <input type="text" id="account-number" placeholder="0000 0000 0000 0000 0000" pattern="\d{20}" title="Расчетный счет должен содержать 20 цифр" name="req[rs][rs]" value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['rs'] : ''?>"/>
                            
                            <label for="bank-bik">
                                БИК банка
                            </label>
                            <input type="text" id="bank-bik" placeholder="000 000 000" pattern="\d{9}" title="БИК должен содержать 9 цифр" name="req[<?=$req_key;?>][bik]" value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['bik'] : '';?>"/>
                            
                            <label for="payment-org-name">
                                Название организации
                            </label>
                            <input type="text" id="payment-org-name" placeholder="Введите название организации" name="req[<?=$req_key;?>][name]" value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['name'] : '';?>"/>
                            
                            <label for="corr-account">
                                Корреспондентский счет
                            </label>
                            <input  type="text" id="corr-account" placeholder="0000 0000 0000 0000 0000 0000" pattern="\d{20}" title="Корреспондентский счет должен содержать 20 цифр" name="req[rs][rs2]" value="<?=!empty($req) && array_key_exists($req_key, $req) ? $req[$req_key]['rs2'] : ''?>"/>
                            
                            <button type="submit" class="submit-btn button" value="Сохранить" name="save_req">Сохранить</button>
                        </div>
                    </div>
            <?php endif;
        endforeach;?>
    </form>
<?php endif;?>
    
<style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      /* padding: 20px; */
    }
    .container {
      display: flex;
      justify-content: space-between;
      max-width: 1200px;
      margin: 0 auto;
      gap: 20px;
    }
    .form-section {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      width: 48%;
    }
    .form-section_two input[type="text"] {
      margin-bottom: 32px;
    }
    h2 {
      margin-bottom: 20px;
      font-size: 22px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-size: 16px;
      font-weight: bold;
    }
    input[type="text"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #A29595; /* #736868; */
      border-radius: 10px;
      font-size: 14px;
    }
    small {
      display: block;
      font-size: 12px;
      color: #666;
      margin-bottom: 5px;
    }
    .important-info {
      margin-top: 10px;
      color: red;
      margin-bottom: 32px;
      font-size: 12px;
      line-height: 19px;
    }
    .file-upload {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    button {
      background-color: transparent;
      border: 1px solid #111111;
      padding: 10px 15px;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
    }
    button svg {
      margin-right: 5px;
    }
    button:hover {
      background-color: #ddd;
    }
    #passport-file-name,
    #org-file-name {
      margin-left: 10px;
      font-size: 14px;
      color: #666;
    }
    .submit-btn {
      background: #3250ea;
      width: 100%;
      font-size: 16px;
      padding: 10px 0;
      border-radius: 10px;
      cursor: pointer;
      border: none;
      color: white;
      justify-content: center;
    }
    .submit-btn:hover {
      background-color: #2955b9;
    }
    @media (max-width: 899px) {
      .important-info {
        margin-bottom: 15px;
      }
    }
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }
      .form-section {
        width: 100%;
      }
      .form-section_two input[type="text"] {
        margin-bottom: 15px;
      }
      .important-info {
        margin-top: 7px;
        margin-bottom: 12px;
        line-height: 18px;
      }
    }
    @media (max-width: 350px) {
      .form-section {
        padding: 10px;
      }
      button {
        padding: 10px 10px;
        font-size: 12px;
      }
    }
  </style>