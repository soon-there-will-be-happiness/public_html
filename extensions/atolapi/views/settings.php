<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
        <h1>Настройки моего API</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a><a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>

    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/extensions/">Расширения</a></li>
        <li>Настройки API</li>
    </ul>

    <form action="" method="POST">
        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/nastr-tren.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Настройки Atol АПИ</h3>
                </div>
            </div>

            <ul class="nav_button">
                <li><input type="submit" name="save" value="Сохранить" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/extensions/">Закрыть</a></li>
            </ul>
        </div>

        <div class="admin_form">
            <h4 class="h4-border">Основное</h4>
            <div class="row-line">
                <div class="col-2-3">
                    <p>
                    <h5>Атол Вкл/откл</h5>
                    <span class="custom-radio-wrap">
                            <label class="custom-radio"><input name="status" type="radio" value="1" <?php if($enable == 1) echo 'checked';?>><span>Вкл</span></label>
                            <label class="custom-radio"><input name="status" type="radio" value="0" <?php if($enable == 0) echo 'checked';?>><span>Откл</span></label>
                        </span>
                    </p>
                </div>
                <div class="col-2-3">
                    <?php //TODO  сделать фон
                        if(isset($error))
                            echo $error;
                        ?>


                </div>
                <div class="col-1-2">
                    <hr class="row-line">

                    <p>
                    <h3>Настройки организации:</h3>
                        <span>
                            <label for="atol[company][name]">Наименование</label>
                            <input type="text" name="atol[company][name]" value="<?php echo isset($this->company_name) ? $this->company_name : ''?>">
                        </span>
                        <span>
                            <label for="atol[company][inn]">ИНН</label>
                            <input type="text" name="atol[company][inn]" value="<?php echo isset($this->company_inn) ? $this->company_inn : ''?>"
                            placeholder="ИНН - 10 или 12 цифр" >
                        </span>
                        <span>
                            <label for="atol[company][email]">email</label>
                            <input type="text" name="atol[company][email]" value="<?php echo isset($this->company_email) ? $this->company_email : ''?>">
                        </span>
                    <span>
                            <label for="atol[company][phone]">телефон</label>
                            <input type="text" name="atol[company][phone]" value="<?php echo isset($this->company_phone) ? $this->company_phone : ''?>"
                                   placeholder="необязательно" >
                        </span>
                        <span>
                            <label for="atol[company][address]">Адрес</label>
                            <input type="text" name="atol[company][address]" value="<?php echo isset($this->company_address) ? $this->company_address : ''?>">
                        </span>
                        <span>
                            <label for="atol[company][vat]">Система налогообложения</label>
                            <select name="atol[company][sn]">
                                <option value="osn" <?php echo (isset($this->company_sn) && $this->company_sn == 'osn') ? 'selected' : '' ;?>>Общая СН</option>
                                <option value="usn_income" <?php echo (isset($this->company_sn) && $this->company_sn == 'usn_income') ? 'selected' : '' ;?>>упрощенная СН (доходы)</option>
                                <option value="usn_income_outcome" <?php echo (isset($this->company_sn) && $this->company_sn == 'usn_income_outcome') ? 'selected' : '' ;?>>упрощенная СН (доходы минус расходы)</option>
                                <option value="envd" <?php echo (isset($this->company_sn) && $this->company_sn == 'envd') ? 'selected' : '' ;?>>единый налог на вмененный доход</option>
                                <option value="esn" <?php echo (isset($this->company_sn) && $this->company_sn == 'esn') ? 'selected' : '' ;?>>единый сельскохозяйственный налог</option>
                                <option value="patent" <?php echo (isset($this->company_sn) && $this->company_sn == 'patent') ? 'selected' : '' ;?>>патентная СН</option>
                            </select>
                            <label for="atol[company][vat]">Ставка НДС</label>
                            <select name="atol[company][vat]">
                                <option value="none"  <?php echo (isset($this->company_vat) && $this->company_vat == 'none') ? 'selected' : '' ;?>>без НДС</option>
                                <option value="vat0" <?php echo (isset($this->company_vat) && $this->company_vat == 'vat0') ? 'selected' : '' ;?>>НДС по ставке 0%</option>
                                <option value="vat10" <?php echo (isset($this->company_vat) && $this->company_vat == 'vat10') ? 'selected' : '' ;?>>НДС чека по ставке 10%</option>
                                <option value="vat110" <?php echo (isset($this->company_vat) && $this->company_vat == 'vat110') ? 'selected' : '' ;?>>НДС чека по расчетной ставке 10/110</option>
                                <option value="vat20" <?php echo (isset($this->company_vat) && $this->company_vat == 'vat20') ? 'selected' : '' ;?>>НДС чека по ставке 20%</option>
                                <option value="vat120" <?php echo (isset($this->company_vat) && $this->company_vat == 'vat120') ? 'selected' : '' ;?>>НДС чека по расчетной ставке 20/120</option>

                            </select>
                        </span>
                    </p>

            </div>


                <div class="col-1-2">
                    <hr class="row-line">
                    <p>
                    <h3>Настройки входа в Atol online:</h3>
                    <span>
                            <label for="atol[company][url]">URL</label>
                            <input type="text" name="atol[company][url]" value="<?php echo isset($this->company_url) ? $this->company_url : ''?>"
                            placeholder="https://online.atol.ru/" >
                        </span>

                    <span>
                            <label for="atol[company][login]">Login</label>
                            <input type="text" name="atol[company][login]" value="<?php echo isset($this->company_login) ? $this->company_login : ''?>">
                        </span>
                    <span>
                            <label for="atol[company][password]">password</label>
                            <input type="password" name="atol[company][password]" value="<?php echo isset($this->company_password) ? $this->company_password : ''?>">
                    </span>
                    <span>
                            <label for="atol[company][group_code]">Код группы</label>
                            <input type="password" name="atol[company][group_code]" value="<?php echo isset($this->company_code) ? $this->company_code : ''?>">
                    </span>

                    </p>
                    <input type="hidden" name="token" value="<?=$_SESSION['admin_token'];?>">
                    </hr>
                </div>
        </div>
    </form>

    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

</body>
</html>