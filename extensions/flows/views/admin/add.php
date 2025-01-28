<?php defined('BILLINGMASTER') or die;
require_once (ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
<?php require_once (ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>

<div class="main">
    <div class="top-wrap">
    <h1>Создать поток</h1>
    <div class="logout">
        <a href="<?php echo $setting['script_url'];?>" target="_blank">Перейти на сайт</a><a href="<?php echo $setting['script_url'];?>/admin/logout" class="red">Выход</a>
    </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li>
            <a href="/admin/flows/">Потоки</a>
        </li>
        <li>Создать поток</li>
    </ul>

    <span id="notification_block"></span>
    
    <form action="" method="POST" enctype="multipart/form-data">

        <div class="admin_top admin_top-flex">
            <div class="admin_top-inner">
                <div>
                    <img src="/template/admin/images/icons/new-categ.svg" alt="">
                </div>
                <div>
                    <h3 class="traning-title mb-0">Создать поток</h3>
                </div>
            </div>
            <ul class="nav_button">
                <li><input type="submit" name="add_flow" value="Создать" class="button save button-white font-bold"></li>
                <li class="nav_button__last"><a class="button red-link" href="<?php echo $setting['script_url'];?>/admin/flows/">Закрыть</a></li>
            </ul>
        </div>
        
        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>События</li>
            </ul>
        
            <div class="admin_form">
                <div>
                <h4 class="h4-border">Основные настройки</h4>
                <div class="row-line">
                    <div class="col-1-2">
<p class="width-100">
    <label>Название</label>
    <input type="text" id="flow_name" name="flow_name" placeholder="Название потока" required="required">
</p>
<p class="width-100">
    <label>Название для учеников: </label>
    <input type="text" id="flow_title" name="flow_title" placeholder="Название потока" required="required">
</p>
                        <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token'];?>">
                        
                        <div class="width-100"><label>Статус</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio"><input name="status" type="radio" value="1" checked=""><span>Вкл</span></label>
                                <label class="custom-radio"><input name="status" type="radio" value="0"><span>Откл</span></label>
                            </span>
                        </div>
                        
                        <div><label class="custom-chekbox-wrap" for="is_default">
                            <input type="checkbox" id="is_default" name="is_default" value="1">
                                <span class="custom-chekbox"></span>Поток по-умолчанию
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-1-2">
                        <div class="width-100">
                            <label>Действует на товары</label>
                            <select class="multiple-select" name="products[]" id="product_select"  multiple="multiple" size="10" required="required">
                                <?$product_list = Product::getProductListOnlySelect();
                                foreach ($product_list as $product):?>
                                    <option value="<?=$product['product_id'];?>"><?=$product['product_name'];?></option>
                                    <?if($product['service_name']):?>
                                        <option disabled="disabled" class="service-name">(<?=$product['service_name'];?>)</option>
                                    <?endif;
                                endforeach?>
                            </select>
                        </div>
                        <p class="width-100" title="-1 без лимита"><label>Лимит пользователей: </label><input type="text" name="limit" placeholder="Лимит пользователей"></p>
                    </div>
                </div>
                
                
                <h4 class="h4-border">Период</h4>
                <div class="row-line">
                <div class="col-1-2">
    <p>
        <label>Дата начала потока</label>
        <input type="text" id="start_flow" class="datetimepicker" name="start_flow" autocomplete="off" placeholder="От" onblur="forDateTimePickerAction()" change="forDateTimePickerAction()" >
    </p>
</div>
<div class="col-1-2">
    <p>
        <label>Дата завершения потока</label>
        <input type="text" id="end_flow" class="datetimepicker" name="end_flow" autocomplete="off" placeholder="До" required="required">
    </p>
</div>
<input name="show_period" type="radio" value="0" class="hidden" checked="checked">
                    <!--
                    <div class="col-1-1">
                        <div class="width-100"><label>Показывать даты потока</label>
                            <span class="custom-radio-wrap">
                                <label class="custom-radio"><input name="show_period" type="radio" value="1"><span>Показать</span></label>
                                <label class="custom-radio"><input name="show_period" type="radio" value="0" checked="checked"><span>Скрыть</span></label>
                            </span>
                        </div>
                    </div>
                                    -->
                    <div class="col-1-2">
    <p>
        <label>Дата начала продаж</label>
        <input type="text" id="public_start" class="datetimepicker" name="public_start" autocomplete="off" placeholder="От" >
    </p>
</div>
<div class="col-1-2">
    <p>
        <label>Дата завершения продаж</label>
        <input type="text" id="public_end" class="datetimepicker" name="public_end" autocomplete="off" placeholder="До" required="required">
    </p>
</div>
                </div>
                </div>
                    
                
                <div>
                    <h4 class="h4-border">Письмо при покупке потока (админу, куратору)</h4>
                    <div class="row-line">
                        
                        <div class="col-1-1">
                            <p class="label"><input type="text" name="letter[sell_emails]" value="" title="Список email через запятую" placeholder="Список email через запятую"></p>
                            <p class="label"><input type="text" name="letter[sell_subject]" value="" title="Тема письма" placeholder="Тема письма"></p>
                            <p><textarea name="letter[sell_text]" class="editor" rows="6" style="width:100%"></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                            </p>
                        </div>
                            
                            
                        <div class="col-1-1" style="margin: 20px 0 0 30px">
                            <h4>События при старте потока</h4>
                            
                        </div>
                        <div class="col-1-2">
                            <div class="width-100"><label>Добавить группы</label>
                                <select size="7" class="multiple-select" multiple="multiple" name="add_groups[]">
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
                        &nbsp;
                        </div>
                        
                        <div class="col-1-2">
                            <div class="width-100"><label>Добавить планы подписок</label>
                                <select class="multiple-select" size="7" multiple="multiple" name="add_planes[]">
                                <?php if($plane_list):
                                        foreach($plane_list as $plane):?>
                                            <option value="<?=$plane['id'];?>">
                                                <?=$plane['name'];?>
                                            </option>
                                        <?php endforeach;
                                    endif;?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-1-1">
                            <h5 class="h4-border" style="font-weight:normal">Письмо клиенту</h5>
                            
                            <p class="label"><input type="text" name="letter[subject]" value="" placeholder="Тема письма"></p>
                            <p><textarea name="letter[text]" class="editor" rows="6" style="width:100%"></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                                [AUTH_LINK] - Ссылка с автоматическим входом<br>
                                [AUTH_LINK='/хвостссылки'] - Ссылка с автоматическим входом и редиректом на какую-либо страницу. Пример: [AUTH_LINK='/blog'] - после авторизации переход в блог<br>
                            </p>
                        </div>
                        
                        
                        <div class="col-1-1" style="margin: 20px 0 0 30px">
                            <h4>События при завершении потока</h4>
                        </div>
                        
                        <div class="col-1-2">
                            <div class="width-100"><label>Удалить группы</label>
                                <select size="7" class="multiple-select" multiple="multiple" name="del_groups[]">
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
                        
                        <div class="col-1-1">
                            <h5 class="h4-border" style="font-weight:normal">Письмо клиенту</h5>
                            <p class="label"><input type="text" name="letter[subject_after]" placeholder="Тема письма"></p>
                            <p><textarea name="letter[text_after]" class="editor" rows="6" style="width:100%"></textarea></p>
        
                            <p class="small">[CLIENT_NAME] - имя клиента<br />
                                [FULL_NAME] - имя и фамилия клиента<br />
                                [SUPPORT] - емейл службы поддержки<br />
                                [EMAIL] - Email клиента<br />
                                [CLIENT_PHONE] - Телефон клиента<br />
                                [AUTH_LINK] - Ссылка с автоматическим входом<br>
                                [AUTH_LINK='/хвостссылки'] - Ссылка с автоматическим входом и редиректом на какую-либо страницу. Пример: [AUTH_LINK='/blog'] - после авторизации переход в блог<br>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php require_once(ROOT . '/template/admin/layouts/admin-footer.php');?>
</div>

<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
jQuery('.datetimepicker').datetimepicker({
format:'d.m.Y H:i',
lang:'ru'
});
</script>
<script>
    // Функция для форматирования даты в формат DD.MM.YYYY HH:mm
    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы начинаются с 0
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}.${month}.${year} ${hours}:${minutes}`;
    }

    // Функция для парсинга даты из строки DD.MM.YYYY HH:mm
    function parseDate(dateStr) {
        const [datePart, timePart] = dateStr.split(' '); // Разделяем дату и время
        const [day, month, year] = datePart.split('.'); // Разделяем день, месяц и год
        const [hour, minute] = timePart.split(':'); // Разделяем часы и минуты

        // Конструируем строку в формате, который понимает JavaScript: YYYY-MM-DDTHH:MM
        const formattedDateStr = `${year}-${month}-${day}T${hour}:${minute}:00`;
        return new Date(formattedDateStr);
    }

    // Функция для расчета дат начала и завершения
    function calculateDates() {
        const startFlowField = document.getElementById('start_flow');
        const publicStartField = document.getElementById('public_start');
        const publicEndField = document.getElementById('public_end');
        const endFlowField = document.getElementById('end_flow');

        const startDate = parseDate(startFlowField.value); // Дата начала потока

        if (!isNaN(startDate)) {
            // Рассчитать дату начала продаж (5 дней до начала потока)
            const publicStartDate = new Date(startDate);
            publicStartDate.setDate(publicStartDate.getDate() - 5);
            publicStartDate.setHours(0, 0, 0, 0); // Установить время в 00:00
            publicStartField.value = formatDate(publicStartDate); // Формат DD.MM.YYYY HH:mm

            // Рассчитать дату завершения продаж (день начала потока, 23:59)
            const publicEndDate = new Date(startDate);
            publicEndDate.setHours(23, 59, 59, 999); // Установить время в 23:59
            publicEndField.value = formatDate(publicEndDate); // Формат DD.MM.YYYY HH:mm

            // Рассчитать дату завершения потока (+4 недели от начала потока)
            const endFlowDate = new Date(startDate);
            endFlowDate.setDate(endFlowDate.getDate() + 28); // Добавить 4 недели (28 дней)
            endFlowDate.setHours(23, 59, 59, 999); // Установить время в 23:59
            endFlowField.value = formatDate(endFlowDate); // Формат DD.MM.YYYY HH:mm
        }
    }
    $(function() {
  $('#datepicker').datepicker({
    onSelect: date => {
      
        updateFlowName();
        
        calculateDates();
    }
  });
});
    // Функция для обновления названия потока
    function updateFlowName() {
        const productSelect = document.getElementById('product_select');
        const selectedOptions = Array.from(productSelect.selectedOptions);
        const startFlowField = document.getElementById('start_flow');
        const startDate = startFlowField.value.trim();
        const flowNameField = document.getElementById('flow_name');
        const flowTitleField = document.getElementById('flow_title');

        if (selectedOptions.length === 1) { // Если выбран один продукт
            const productName = selectedOptions[0].textContent.trim(); // Имя продукта
            const formattedDate = startDate.split(' ')[0]; // Дата без времени

            // Формирование значений
            if (startDate) {
                flowNameField.value = `${productName} с ${formattedDate}`;
            } else {
                flowNameField.value = productName;
            }
            flowTitleField.value = productName; // Название для учеников совпадает
        }
    }

    // Функция, которая срабатывает при изменении даты начала
    function forDateTimePickerAction() {
        updateFlowName();
        calculateDates();
    }

    // Инициализация события для выбора продукта
    document.getElementById('product_select').addEventListener('change', () => {
        updateFlowName();
        calculateDates();
    });

    // Обновление названия и дат при изменении даты начала потока
    document.getElementById('start_flow').addEventListener('input', () => {
        updateFlowName();
        calculateDates();
    });

    // Обновление названия и дат при изменении даты начала потока (по событию change)
    document.getElementById('start_flow').addEventListener('change', () => {
        updateFlowName();
        calculateDates();
    });



</script>


</body>
</body>
</html>



