<?php defined('BILLINGMASTER') or die;
require_once(ROOT . '/template/admin/layouts/admin-head.php'); ?>
<script>
    let loc = window.location.pathname
    let tab = 'tab-' + loc.slice(1) +'-index-0';
    localStorage.setItem(tab, 2);
</script>
<body id="page" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<?php require_once(ROOT . '/template/admin/layouts/admin-sidebar.php'); ?>
<div class="main">
    <div class="top-wrap">
        <h1>Создать форму для заказа продукта</h1>
        <div class="logout">
            <a href="/" target="_blank">Перейти на сайт</a><a href="/admin/logout" class="red">Выход</a>
        </div>
    </div>
    <ul class="breadcrumb">
        <li>
            <a href="/admin">Дашбоард</a>
        </li>
        <li><a href="/admin/products/">Продукты</a></li>
        <li>Создать форму для заказа продукта</li>
    </ul>

    <span id="notification_block"></span>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="admin_top admin_top-flex">
            <h3 class="traning-title mb-0">Форма для заказа продукта</h3>
            <ul class="nav_button">
                <li><input type="submit" name="updateForm" value="Сохранить" class="button save button-white font-bold">
                </li>
                <li class="nav_button__last"><a class="button red-link" href="/admin/products/formlist">Закрыть</a></li>
            </ul>
        </div>
        <div class="tabs">
            <ul>
                <li>Основное</li>
                <li>Внешний вид</li>
                <li>Готовая форма</li>
            </ul>
        
            <div class="admin_form formgenerator">

                <!-- 1 Вкладка основное -->
                <div>
                    <div class="row-line">
                        <div class="col-1-1 mb-0"><h4>Основное</h4></div>
                        <div class="col-1-2">
                            <p class="width-100">
                                <label>Название формы: </label>
                                <input type="text" name="form[name]" placeholder="Форма для сайта" required value="<?= $form['name'] ?>">
                            </p>
                            <!--TODO: сделать выбранные продукты-->
                            <div class="width-100"><label>Выберите продукт или продукты для формы: </label>
                                <div class="select-wrap">
                                    <select name="products_id[]" multiple="multiple" class="multiple-select" size="10" required>
                                        <?php foreach ($products as $product) { ?>
                                            <option value="<?= $product['product_id']; ?>" <?= in_array($product['product_id'], $selected_products_ids)? 'selected' : '' ?>>
                                                <?= $product['product_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цель Я.Метрики (номер цели): </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Укажите номер цели, или идентификатор" name="data[yandex_target_id]" value="<?= $formdata["data"]['yandex_target_id'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Купон на скидку: </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Введите купон" name="data[discount_coupon]" value="<?= $formdata["data"]['discount_coupon'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Закрепление за партнером: </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Введите id партнера" name="data[partner_id]" value="<?= $formdata["data"]['partner_id'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                            <span class="custom-checkbox-wrap" style="display: none;">
                                <label class="custom-chekbox-wrap">
                                        <input type="checkbox" value="1" name="form[useUtmScript]" checked>
                                        <span class="custom-chekbox"></span>Добавить код для протаскивания utm-меток
                                </label>
                            </span>
                            </div>
                            <div class="width-100"><label>Событие после отправки: </label>
                                <div class="select-wrap">
                                    <select name="sendevent[type]">
                                        <option value="0" <?= isset($formdata['sendevent']['type']) && $formdata['sendevent']['type'] == 0 ? 'selected="selected"' : '' ?>>По умолчанию (направить в SM)</option>
                                        <option value="1" data-show_on="SendEventText" <?= isset($formdata['sendevent']['type']) && $formdata['sendevent']['type'] == 1 ? 'selected="selected"' : '' ?>>Вывести сообщение</option>
                                    </select>
                                </div>
                            </div>
                            <div class="width-100" id="SendEventText">
                                <label>Текст после: </label>
                                <div class="wrap">
                                    <textarea type="text" placeholder="Введите текст. Он покажется после отправки заявки" name="sendevent[text]" ><?= $formdata['sendevent']['text'] ?? "Спасибо! Ваша заявка отправлена<"?></textarea>
                                </div>
                            </div>
                            <div class="width-100"><label>Язык формы: </label>
                                <div class="select-wrap">
                                    <select name="lang">
                                        <option value="ru" <?= isset($formdata['lang']) && $formdata['lang'] == "ru" ? 'selected="selected"' : '' ?>>Русский</option>
                                        <option value="en" <?= isset($formdata['lang']) && $formdata['lang'] == "en" ? 'selected="selected"' : '' ?>>English</option>
                                        <option value="ua" <?= isset($formdata['lang']) && $formdata['lang'] == "ua" ? 'selected="selected"' : '' ?>>Український</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="col-1-2"></div>
                    </div>
                </div>

                <!-- 2 Вкладка Внешний вид -->
                <div>
                    <div class="row-line">


                        <div class="col-1-1 mb-0"><h4>Настройка полей</h4></div>
                        <div class="col-1-2">
                            <div class="width-100"><label>Выберите поля для заполнения: </label>
                                <div class="select-wrap">
                                    <select name="fields[fill][]" multiple="multiple" class="multiple-select" size="10">
                                        <option value="usename" <?=in_array('usename', $formdata['fields']['fill']) ? 'selected' : ''?>>Имя</option>
                                        <option value="usesurname" <?=in_array('usesurname', $formdata['fields']['fill']) ? 'selected' : ''?>>Фамилия</option>
                                        <option value="useemail" <?=in_array('useemail', $formdata['fields']['fill']) ? 'selected' : ''?>>E-mail</option>
                                        <option value="usetg" <?=in_array('usetg', $formdata['fields']['fill']) ? 'selected' : ''?>>Telegram</option>
                                        <option value="usephone" <?=in_array('usephone', $formdata['fields']['fill']) ? 'selected' : ''?>>Телефон</option>
                                        <option value="usepolicy" <?=in_array('usepolicy', $formdata['fields']['fill']) ? 'selected' : ''?>>Согласие с политикой</option>
                                        <option value="usepromo" <?=in_array('usepromo', $formdata['fields']['fill']) ? 'selected' : ''?>>Промокод</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-1-2">
                            <p><label class="margin-bottom-15">Вид выбора:</label>
                                <span class="custom-radio-wrap">
                                <label class="custom-radio">
                                    <input name="form[product_kind_of_choice]" type="radio"
                                           value="radio" checked
                                           data-show_on="titleSettings"
                                           <?= isset($formdata['form']['product_kind_of_choice']) && $formdata['form']['product_kind_of_choice'] == 'radio' ? ' checked' : '' ?>
                                    ><span>Радиокнопки</span></label>
                                <label class="custom-radio">
                                    <input name="form[product_kind_of_choice]" type="radio"
                                           value="select"
                                           data-show_off="titleSettings"
                                           <?= isset($formdata['form']['product_kind_of_choice']) && $formdata['form']['product_kind_of_choice'] == 'select' ? ' checked' : '' ?>
                                    ><span>Список</span></label>
                            </span>
                            </p>
                        </div>

                        <div class="col-1-1 mb-0"><h4>Внешний вид полей</h4></div>
                        <div class="col-1-2">
                            <div class="width-100">
                                <label class="margin-bottom-15">Рамка у полей: </label>
                                <div class="flex">

                                    <label class="image-checkbox" for="bordertop">
                                        <input id="bordertop"
                                               name="fields[borders][]"
                                               type="checkbox"
                                               value="top"
                                               <?= isset($formdata['fields']['borders']) && in_array('top', $formdata['fields']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-top.svg">
                                    </label>
                                    <label class="image-checkbox" for="borderright">
                                        <input id="borderright"
                                               name="fields[borders][]"
                                               type="checkbox" value="right"
                                               <?= isset($formdata['fields']['borders']) && in_array('right', $formdata['fields']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-right.svg">
                                    </label>
                                    <label class="image-checkbox" for="borderbottom">
                                        <input id="borderbottom"
                                               name="fields[borders][]"
                                               type="checkbox"
                                               value="bottom"
                                               <?= isset($formdata['fields']['borders']) && in_array('bottom', $formdata['fields']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-bottom.svg">
                                    </label>
                                    <label class="image-checkbox" for="borderleft">
                                        <input id="borderleft"
                                               name="fields[borders][]"
                                               type="checkbox" value="left"
                                               <?= isset($formdata['fields']['borders']) && in_array('left', $formdata['fields']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-left.svg">
                                    </label>
                                </div>
                            </div>

                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Радиус скругления<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata["fields"]['border-radius'] ?>">
                                    <input type="number" min="2" max="80" name="fields[border-radius]" value="<?= $formdata["fields"]['border-radius'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Ширина рамки<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="1" max="80" oninput="updateRangeInput(this)" value="<?= $formdata["fields"]['width_border'] ?>">
                                        <input type="number" min="1" max="30" name="fields[width_border]" value="<?= $formdata["fields"]['width_border'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Стиль: </label>
                                <div class="select-wrap">
                                    <select name="fields[style]">
                                        <option value="solid" <?= $formdata['fields']['style'] == "solid" ? 'selected' : ''?>>Solid</option>
                                        <option value="dotted" <?= $formdata['fields']['style'] == "dotted" ? 'selected' : ''?>>dotted</option>
                                        <option value="dashed" <?= $formdata['fields']['style'] == "dashed" ? 'selected' : ''?>>Dashed</option>
                                        <option value="double" <?= $formdata['fields']['style'] == "double" ? 'selected' : ''?>>double</option>
                                        <option value="groove" <?= $formdata['fields']['style'] == "groove" ? 'selected' : ''?>>groove</option>
                                        <option value="ridge" <?= $formdata['fields']['style'] == "ridge" ? 'selected' : ''?>>ridge</option>
                                        <option value="inset" <?= $formdata['fields']['style'] == "inset" ? 'selected' : ''?>>inset</option>
                                        <option value="outset" <?= $formdata['fields']['style'] == "outset" ? 'selected' : ''?>>outset</option>
                                    </select>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет рамки: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="fields[border-color]"
                                           value="<?= $formdata['fields']['border-color'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <span class="custom-checkbox-wrap">
                                    <label class="custom-chekbox-wrap">
                                            <input type="checkbox" value="1" name="fields[labels]"
                                                <?= isset($formdata['fields']['labels']) && $formdata['fields']['labels'] == 1 ? 'checked' : '' ?>
                                                oninput="showblock('labelSettings');"
                                            >
                                            <span class="custom-chekbox" value="1"></span>Выводить лейблы у полей
                                    </label>
                                </span>
                            </div>
                            <div id="labelSettings" class="margin-bottom-15 <?= isset($formdata['fields']['labels']) && $formdata['fields']['labels'] == 1 ? '' : 'hidden' ?>">
                                <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                    <label>Размер шрифта<span class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="1" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['fields']['label_font_size'] ?>">
                                        <input type="number" min="1" max="30" name="fields[label_font_size]" value="<?= $formdata['fields']['label_font_size'] ?>">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Цвет текста: </label>
                                    <div class="color-input-wrap">
                                        <input type="text" data-coloris="" class="coloris" name="fields[label_color]" value="<?= $formdata['fields']['label_color'] ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="width-100">
                            <span class="custom-checkbox-wrap">
                                <label class="custom-chekbox-wrap">
                                        <input type="checkbox"
                                               value="1"
                                               name="fields[placeholder]"
                                               <?= isset($formdata['fields']['placeholder']) && $formdata['fields']['placeholder'] == 1 ? 'checked' : '' ?>
                                               oninput="showblock('placeholderSettings');">
                                        <span class="custom-chekbox" value="1"></span>Показывать плейсхолдер
                                </label>
                            </span>
                            </div>
                            <div id="placeholderSettings" class="<?= isset($formdata['fields']['placeholder']) && $formdata['fields']['placeholder'] == 1 ? '' : 'hidden'  ?>">
                                <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                    <label>Размер шрифта<span class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="1" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['fields']['placeholder_font_size'] ?>">
                                        <input type="number" min="1" max="30" name="fields[placeholder_font_size]" value="<?= $formdata['fields']['placeholder_font_size'] ?>">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Цвет текста</label>
                                    <div class="color-input-wrap">
                                        <input type="text" data-coloris="" class="coloris" name="fields[placeholder_color]" value="<?= $formdata['fields']['placeholder_color'] ?>">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="fields-settings-right-side col-1-2" title="Настройка полей">
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта в полях:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="32" oninput="updateRangeInput(this)" value="<?= $formdata['fields']['font-size'] ?>">
                                    <input type="number" min="2" max="32" name="fields[font-size]" value="<?= $formdata['fields']['font-size'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Высота поля<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="10" max="100" oninput="updateRangeInput(this)" value="<?= $formdata['fields']['height'] ?>">
                                    <input type="number" min="10" max="100" name="fields[height]" value="<?= $formdata['fields']['height'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет текста: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="fields[text-color]" value="<?= $formdata['fields']['text-color'] ?>">
                                </div>
                            </div>

                        </div>

                        <div class="col-1-1 mb-0"><h4>Поле телефона</h4></div>
                        <div class="col-1-2">
                            <div class="width-100">
                        <span class="custom-checkbox-wrap">
                            <label class="custom-chekbox-wrap">
                                    <input type="checkbox" value="1" name="fields[phone][enable_mask]" <?= isset($formdata['fields']['phone']['enable_mask']) && $formdata['fields']['phone']['enable_mask'] == 1 ? 'checked' : '' ?>>
                                    <span class="custom-chekbox" value="1"></span>Добавить маску для телефона
                            </label>
                        </span>
                            </div>
                        </div>

                        <div class="col-1-1 mb-0"><h4>Политика обработки персональных данных</h4></div>
                        <div class="col-1-1">
                            <div class="width-100">
                                <textarea style="height: 200px;" class="editor margin-bottom-30" name="fields[policyLink]" rows="55" cols="55" placeholder="Текст политики">
                                    <?= $formdata['fields']['policyLink'] ?>
                                </textarea>
                            </div>
                        </div>

                        <div class="col-1-1 mb-0"><h4>Настройка кнопки</h4></div>
                        <div class="col-1-2">
                            <div class="width-100">
                                <label>Текст кнопки: </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Заказать" name="btn[text]" value="<?= $formdata['btn']['text'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Состояние кнопки: </label>
                                <div class="flex">
                                    <label class="image-checkbox" for="btncursor1">
                                        <input id="btncursor1" name="btn[cursor]" type="radio" value="auto" checked>
                                        <img src="/template/admin/images/icons/cursor_auto.svg">
                                    </label>
                                    <label class="image-checkbox" for="btncursor2">
                                        <input id="btncursor2" name="btn[cursor]" type="radio" value="pointer">
                                        <img src="/template/admin/images/icons/cursor_pointer.svg">
                                    </label>
                                </div>
                            </div>

                            <!--TODO настройки normal левая-->
                            <div id="nohoverbtnleft">
                                <div class="row-line margin-bottom-15">
                                    <div class="width-100">
                                        <label>Цвет текста: </label>
                                        <div class="color-input-wrap">
                                            <input type="text" data-coloris="" class="coloris" name="btn[text-color]"
                                                   value="<?= $formdata['btn']['text-color'] ?>">
                                        </div>
                                    </div>
                                    <div class="width-100">
                                        <label>Фон кнопки: </label>
                                        <div class="color-input-wrap">
                                            <input type="text" data-coloris="" class="coloris" name="btn[btn-background]"
                                                   value="<?= $formdata['btn']['btn-background'] ?>">
                                        </div>
                                    </div>
                                </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта:<span
                                            class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="8" max="30" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['font-size'] ?>">
                                    <input type="number" min="8" max="30" name="btn[font-size]" value="<?= $formdata['btn']['font-size'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Межбуквенный интервал:<span
                                            class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="16" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['letter-spacing'] ?>">
                                    <input type="number" min="0" max="16" name="btn[letter-spacing]" value="<?= $formdata['btn']['letter-spacing'] ?>">
                                </div>
                            </div>
                            <div class="width-100" bis_skin_checked="1"><label>Форматирование</label>
                                <div class="fonts flex" bis_skin_checked="1">
                                    <div class="font font-bold" data-font_value="font_bold" bis_skin_checked="1"></div>
                                    <div class="font font-under_line" data-font_value="font_under_line" bis_skin_checked="1"></div>
                                    <div class="font font-italic" data-font_value="font_italic" bis_skin_checked="1"></div>
                                    <div class="font font-uppercase" data-font_value="font_uppercase" bis_skin_checked="1"></div>

                                    <input type="hidden" name="btn[format][font_bold]" value="<?= $formdata['btn']['format']['font_bold'] ?>">
                                    <input type="hidden" name="btn[format][font_under_line]" value="<?= $formdata['btn']['format']['font_under_line'] ?>">
                                    <input type="hidden" name="btn[format][font_italic]" value="<?= $formdata['btn']['format']['font_italic'] ?>">
                                    <input type="hidden" name="btn[format][font_uppercase]" value="<?= $formdata['btn']['format']['font_uppercase'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <div class="margin-bottom-15"><b>Внутренний отступ: </b></div>
                                <div class="flex">
                                    <div class="smallinputwrap">
                                        <label>Сверху</label>
                                        <input type="number" name="btn[inner-padding][top]" placeholder="px" value="<?= $formdata['btn']['inner-padding']['top'] ?>">
                                    </div>
                                    <div class="smallinputwrap">
                                        <label>Справа</label>
                                        <input type="number" name="btn[inner-padding][right]" placeholder="px"
                                               value="<?= $formdata['btn']['inner-padding']['right'] ?>">
                                    </div>
                                    <div class="smallinputwrap">
                                        <label>Снизу</label>
                                        <input type="number" name="btn[inner-padding][bottom]" placeholder="px"
                                               value="<?= $formdata['btn']['inner-padding']['bottom'] ?>">
                                    </div>
                                    <div class="smallinputwrap">
                                        <label>Слева</label>
                                        <input type="number" name="btn[inner-padding][left]" placeholder="px" value="<?= $formdata['btn']['inner-padding']['left'] ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="width-100">
                                <label class="margin-bottom-15">Рамка у кнопки: </label>
                                <div class="flex">
                                    <label class="image-checkbox" for="bordertopbtn">
                                        <input id="bordertopbtn"
                                               name="btn[borders][]"
                                               type="checkbox"
                                               value="top"
                                            <?= isset($formdata['btn']['borders']) &&  in_array('top', $formdata['btn']['borders'])? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/border-top.svg">
                                    </label>
                                    <label class="image-checkbox" for="borderrightbtn">
                                        <input id="borderrightbtn"
                                               name="btn[borders][]"
                                               type="checkbox"
                                               value="right"
                                            <?= isset($formdata['btn']['borders']) &&  in_array('right', $formdata['btn']['borders'])? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/border-right.svg">
                                    </label>
                                    <label class="image-checkbox" for="borderbottombtn">
                                        <input id="borderbottombtn"
                                               name="btn[borders][]"
                                               type="checkbox"
                                               value="bottom"
                                            <?= isset($formdata['btn']['borders']) &&  in_array('bottom', $formdata['btn']['borders'])? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/border-bottom.svg">
                                    </label>
                                    <label class="image-checkbox" for="borderleftbtn">
                                        <input id="borderleftbtn"
                                               name="btn[borders][]"
                                               type="checkbox"
                                               value="left"
                                            <?= isset($formdata['btn']['borders']) && in_array('left', $formdata['btn']['borders'])? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/border-left.svg">
                                    </label>
                                </div>
                            </div>


                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Радиус скругления:<span
                                            class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['border-radius'] ?>">
                                    <input type="number" min="2" max="80" name="btn[border-radius]" value="<?= $formdata['btn']['border-radius'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Ширина рамки:<span
                                            class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['border-width'] ?>">
                                    <input type="number" min="0" max="80" name="btn[border-width]" value="<?= $formdata['btn']['border-width'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Стиль: </label>
                                <div class="select-wrap">
                                    <select name="btn[border-style]">
                                        <option value="solid" <?= $formdata['btn']['border-style'] == "solid" ? 'selected' : ''?>>Solid</option>
                                        <option value="dotted" <?= $formdata['btn']['border-style'] == "dotted" ? 'selected' : ''?>>dotted</option>
                                        <option value="dashed" <?= $formdata['btn']['border-style'] == "dashed" ? 'selected' : ''?>>Dashed</option>
                                        <option value="double" <?= $formdata['btn']['border-style'] == "double" ? 'selected' : ''?>>double</option>
                                        <option value="groove" <?= $formdata['btn']['border-style'] == "groove" ? 'selected' : ''?>>groove</option>
                                        <option value="ridge" <?= $formdata['btn']['border-style'] == "ridge" ? 'selected' : ''?>>ridge</option>
                                        <option value="inset" <?= $formdata['btn']['border-style'] == "inset" ? 'selected' : ''?>>inset</option>
                                        <option value="outset" <?= $formdata['btn']['border-style'] == "outset" ? 'selected' : ''?>>outset</option>
                                    </select>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="btn[border-color]"
                                           value="<?= $formdata['btn']['border-color'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Ширина кнопки: </label>
                                <div class="select-wrap">
                                    <select name="btn[btn-width]">
                                        <option value="10%" <?= $formdata['btn']['btn-width'] == "10%" ? 'selected' : ''?>>10%</option>
                                        <option value="20%" <?= $formdata['btn']['btn-width'] == "20%" ? 'selected' : ''?>>20%</option>
                                        <option value="30%" <?= $formdata['btn']['btn-width'] == "30%" ? 'selected' : ''?>>30%</option>
                                        <option value="40%" <?= $formdata['btn']['btn-width'] == "40%" ? 'selected' : ''?>>40%</option>
                                        <option value="50%" <?= $formdata['btn']['btn-width'] == "50%" ? 'selected' : ''?>>50%</option>
                                        <option value="60%" <?= $formdata['btn']['btn-width'] == "60%" ? 'selected' : ''?>>60%</option>
                                        <option value="70%" <?= $formdata['btn']['btn-width'] == "70%" ? 'selected' : ''?>>70%</option>
                                        <option value="80%" <?= $formdata['btn']['btn-width'] == "80%" ? 'selected' : ''?>>80%</option>
                                        <option value="90%" <?= $formdata['btn']['btn-width'] == "90%" ? 'selected' : ''?>>90%</option>
                                        <option value="100%" <?= $formdata['btn']['btn-width'] == "100%" ? 'selected' : ''?>>100%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Выравнивание кнопки: </label>
                                <div class="flex">
                                    <label class="image-checkbox" title="Слева" for="alignLeft">
                                        <input id="alignLeft"
                                               name="btn[align]"
                                               type="radio"
                                               value="left"
                                            <?= $formdata['btn']['align'] == 'left' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_left.svg">
                                    </label>
                                    <label class="image-checkbox" title="По центру" for="alignCenter">
                                        <input id="alignCenter"
                                               name="btn[align]"
                                               type="radio"
                                               value="center"
                                            <?= $formdata['btn']['align'] == 'center' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_center.svg">
                                    </label>
                                    <label class="image-checkbox" title="Справа" for="alignRight">
                                        <input id="alignRight"
                                               name="btn[align]"
                                               type="radio"
                                               value="right"
                                            <?= $formdata['btn']['align'] == 'right' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_right.svg">
                                    </label>
                                </div>
                            </div>
                            </div>

                            <!--TODO настройки hover левая-->
                            <div id="hoverbtnleft" style="display: none">
                                <div class="row-line margin-bottom-15">
                                    <div class="width-100">
                                        <label>Цвет текста: </label>
                                        <div class="color-input-wrap">
                                            <input type="text" data-coloris="" class="coloris" name="btn[hover][text-color]"
                                                   value="<?= $formdata['btn']['hover']['text-color'] ?>">
                                        </div>
                                    </div>
                                    <div class="width-100">
                                        <label>Фон кнопки: </label>
                                        <div class="color-input-wrap">
                                            <input type="text" data-coloris="" class="coloris" name="btn[hover][btn-background]"
                                                   value="<?= $formdata['btn']['hover']['btn-background'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта:<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="8" max="30" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['hover']['font-size'] ?>">
                                        <input type="number" min="8" max="30" name="btn[hover][font-size]" value="<?= $formdata['btn']['hover']['font-size'] ?>">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Межбуквенный интервал:<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="16" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['hover']['letter-spacing'] ?>">
                                        <input type="number" min="0" max="16" name="btn[hover][letter-spacing]" value="<?= $formdata['btn']['hover']['letter-spacing'] ?>">
                                    </div>
                                </div>
                                <div class="width-100" bis_skin_checked="1"><label>Форматирование</label>
                                    <div class="fonts flex" bis_skin_checked="1">
                                        <div class="font font-bold" data-font_value="font_bold" bis_skin_checked="1"></div>
                                        <div class="font font-under_line" data-font_value="font_under_line"
                                             bis_skin_checked="1"></div>
                                        <div class="font font-italic" data-font_value="font_italic" bis_skin_checked="1"></div>
                                        <div class="font font-uppercase" data-font_value="font_uppercase"
                                             bis_skin_checked="1"></div>

                                        <input type="hidden" name="btn[hover][format][font_bold]" value="<?= $formdata['btn']['hover']['format']['font_bold'] ?>">
                                        <input type="hidden" name="btn[hover][format][font_under_line]" value="<?= $formdata['btn']['hover']['format']['font_under_line'] ?>">
                                        <input type="hidden" name="btn[hover][format][font_italic]" value="<?= $formdata['btn']['hover']['format']['font_italic'] ?>">
                                        <input type="hidden" name="btn[hover][format][font_uppercase]" value="<?= $formdata['btn']['hover']['format']['font_uppercase'] ?>">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <div class="margin-bottom-15"><b>Внутренний отступ: </b></div>
                                    <div class="flex">
                                        <div class="smallinputwrap">
                                            <label>Сверху</label>
                                            <input type="number" name="btn[hover][inner-padding][top]" placeholder="px" value="<?= $formdata['btn']['hover']['inner-padding']['top'] ?>">
                                        </div>
                                        <div class="smallinputwrap">
                                            <label>Справа</label>
                                            <input type="number" name="btn[hover][inner-padding][right]" placeholder="px" value="<?= $formdata['btn']['hover']['inner-padding']['right'] ?>">
                                        </div>
                                        <div class="smallinputwrap">
                                            <label>Снизу</label>
                                            <input type="number" name="btn[hover][inner-padding][bottom]" placeholder="px" value="<?= $formdata['btn']['hover']['inner-padding']['bottom'] ?>">
                                        </div>
                                        <div class="smallinputwrap">
                                            <label>Слева</label>
                                            <input type="number" name="btn[hover][inner-padding][left]" placeholder="px" value="<?= $formdata['btn']['hover']['inner-padding']['left'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label class="margin-bottom-15">Рамка у кнопки: </label>
                                    <div class="flex">
                                        <label class="image-checkbox" for="bordertopbtnhover"><input
                                                    id="bordertopbtnhover"
                                                    name="btn[hover][borders][]"
                                                    type="checkbox" value="top"
                                                <?= isset($formdata['btn']['hover']['borders']) && in_array('top', $formdata['btn']['hover']['borders']) ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/border-top.svg">
                                        </label>
                                        <label class="image-checkbox" for="borderrightbtnhover"><input
                                                    id="borderrightbtnhover"
                                                    name="btn[hover][borders][]"
                                                    type="checkbox" value="right"
                                                <?= isset($formdata['btn']['hover']['borders']) &&  in_array('right', $formdata['btn']['hover']['borders']) ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/border-right.svg"></label>
                                        <label class="image-checkbox" for="borderbottombtnhover"><input
                                                    id="borderbottombtnhover"
                                                    name="btn[hover][borders][]"
                                                    type="checkbox"
                                                    value="bottom"
                                                <?= isset($formdata['btn']['hover']['borders']) &&  in_array('bottom', $formdata['btn']['hover']['borders']) ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/border-bottom.svg">
                                        </label>
                                        <label class="image-checkbox" for="borderleftbtnhover"><input
                                                    id="borderleftbtnhover"
                                                    name="btn[hover][borders][]"
                                                    type="checkbox"
                                                    value="left"
                                                <?= isset($formdata['btn']['hover']['borders']) &&  in_array('left', $formdata['btn']['hover']['borders']) ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/border-left.svg"></label>
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Радиус скругления:<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['hover']['border-radius'] ?>">
                                        <input type="number" min="2" max="80" name="btn[hover][border-radius]" value="<?= $formdata['btn']['hover']['border-radius'] ?>">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Ширина рамки:<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['hover']['border-width'] ?>">
                                        <input type="number" min="0" max="80" name="btn[hover][border-width]" value="<?= $formdata['btn']['hover']['border-width'] ?>">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Стиль: </label>
                                    <div class="select-wrap">
                                        <select name="btn[hover][border-style]">
                                            <option value="solid" <?= $formdata['btn']['hover']['border-style'] == "solid" ? 'selected' : ''?>>Solid</option>
                                            <option value="dotted" <?= $formdata['btn']['hover']['border-style'] == "dotted" ? 'selected' : ''?>>dotted</option>
                                            <option value="dashed" <?= $formdata['btn']['hover']['border-style'] == "dashed" ? 'selected' : ''?>>Dashed</option>
                                            <option value="double" <?= $formdata['btn']['hover']['border-style'] == "double" ? 'selected' : ''?>>double</option>
                                            <option value="groove" <?= $formdata['btn']['hover']['border-style'] == "groove" ? 'selected' : ''?>>groove</option>
                                            <option value="ridge" <?= $formdata['btn']['hover']['border-style'] == "ridge" ? 'selected' : ''?>>ridge</option>
                                            <option value="inset" <?= $formdata['btn']['hover']['border-style'] == "inset" ? 'selected' : ''?>>inset</option>
                                            <option value="outset" <?= $formdata['btn']['hover']['border-style'] == "outset" ? 'selected' : ''?>>outset</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Цвет: </label>
                                    <div class="color-input-wrap">
                                        <input type="text" data-coloris="" class="coloris" name="btn[hover][border-color]"
                                               value="<?= $formdata['btn']['hover']['border-color'] ?>">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Ширина кнопки: </label>
                                    <div class="select-wrap">
                                        <select name="btn[hover][btn-width]">
                                            <option value="10%" <?= $formdata['btn']['hover']['btn-width'] == "10%" ? 'selected' : ''?>>10%</option>
                                            <option value="20%" <?= $formdata['btn']['hover']['btn-width'] == "20%" ? 'selected' : ''?>>20%</option>
                                            <option value="30%" <?= $formdata['btn']['hover']['btn-width'] == "30%" ? 'selected' : ''?>>30%</option>
                                            <option value="40%" <?= $formdata['btn']['hover']['btn-width'] == "40%" ? 'selected' : ''?>>40%</option>
                                            <option value="50%" <?= $formdata['btn']['hover']['btn-width'] == "50%" ? 'selected' : ''?>>50%</option>
                                            <option value="60%" <?= $formdata['btn']['hover']['btn-width'] == "60%" ? 'selected' : ''?>>60%</option>
                                            <option value="70%" <?= $formdata['btn']['hover']['btn-width'] == "70%" ? 'selected' : ''?>>70%</option>
                                            <option value="80%" <?= $formdata['btn']['hover']['btn-width'] == "80%" ? 'selected' : ''?>>80%</option>
                                            <option value="90%" <?= $formdata['btn']['hover']['btn-width'] == "90%" ? 'selected' : ''?>>90%</option>
                                            <option value="100%" <?= $formdata['btn']['hover']['btn-width'] == "100%" ? 'selected' : ''?>>100%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Выравнивание кнопки: </label>
                                    <div class="flex">
                                        <label class="image-checkbox" title="Слева" for="alignLeft">
                                            <input id="alignLeft"
                                                   name="btn[hover][align]"
                                                   type="radio"
                                                   value="left"
                                                <?= $formdata['btn']['hover']['align'] == 'left' ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/text-align_left.svg">
                                        </label>
                                        <label class="image-checkbox" title="По центру" for="alignCenter">
                                            <input
                                                    id="alignCenter" name="btn[hover][align]" type="radio"
                                                    value="center"
                                                <?= $formdata['btn']['hover']['align'] == 'center' ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/text-align_center.svg">
                                        </label>
                                        <label class="image-checkbox" title="Справа" for="alignRight">
                                            <input id="alignRight"
                                                   name="btn[hover][align]"
                                                   type="radio"
                                                   value="right"
                                                <?= $formdata['btn']['hover']['align'] == 'right' ? 'checked' : '' ?>
                                            >
                                            <img src="/template/admin/images/icons/text-align_right.svg"></label>
                                    </div>
                                </div>



                            </div>
                        </div>
                        <div class="button-settings-right-side col-1-2" title="Настройка кнопки">
                            <!--TODO настройки normal правая-->
                            <div id="nohoverbtnright">
                            <div class="shadow-settings-right-side" title="Настройка тени у кнопки">
                                <h5>Тень</h5>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                    <label>По горизонтали<span class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['shadow-horiz'] ?>">
                                        <input type="number" min="0" max="80" name="btn[shadow-horiz]" value="<?= $formdata['btn']['shadow-horiz'] ?>">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По вертикали<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['shadow-vertic'] ?>">
                                        <input type="number" min="0" max="80" name="btn[shadow-vertic]" value="<?= $formdata['btn']['shadow-vertic'] ?>">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размытие<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['shadow-blur'] ?>">
                                        <input type="number" min="0" max="80" name="btn[shadow-blur]" value="<?= $formdata['btn']['shadow-blur'] ?>">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Spread<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['btn']['shadow-spread'] ?>">
                                        <input type="number" min="0" max="80" name="btn[shadow-spread]" value="<?= $formdata['btn']['shadow-spread'] ?>">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Цвет тени: </label>
                                    <div class="color-input-wrap">
                                        <input type="text" data-coloris="" class="coloris" name="btn[shadow-color]"
                                               value="<?= $formdata['btn']['shadow-color'] ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                            <!--TODO настройки hover правая-->
                            <div id="hoverbtnright" style="display: none;">

                                <div class="shadow-settings-right-side" title="Настройка тени у кнопки">
                                    <h5>Тень</h5>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По горизонтали<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)"
                                                   value="<?= $formdata['btn']['hover']['shadow-horiz'] ?>">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-horiz]"
                                                   value="<?= $formdata['btn']['hover']['shadow-horiz'] ?>">
                                        </div>
                                    </div>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По вертикали<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)"
                                                   value="<?= $formdata['btn']['hover']['shadow-vertic'] ?>">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-vertic]"
                                                   value="<?= $formdata['btn']['hover']['shadow-vertic'] ?>">
                                        </div>
                                    </div>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размытие<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)"
                                                   value="<?= $formdata['btn']['hover']['shadow-blur'] ?>">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-blur]"
                                                   value="<?= $formdata['btn']['hover']['shadow-blur'] ?>">
                                        </div>
                                    </div>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Spread<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)"
                                                   value="<?= $formdata['btn']['hover']['shadow-spread'] ?>">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-spread]"
                                                   value="<?= $formdata['btn']['hover']['shadow-spread'] ?>">
                                        </div>
                                    </div>
                                    <div class="width-100">
                                        <label>Цвет тени: </label>
                                        <div class="color-input-wrap">
                                            <input type="text" data-coloris="" class="coloris"
                                                   name="btn[hover][shadow-color]"
                                                   value="<?= $formdata['btn']['hover']['shadow-color'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-1-1 mb-0"><h4>Название продукта</h4></div>
                        <div class="col-1-2">
                            <p><label>Показывать названия продуктов:</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="product[showtitle]" type="radio" value="1" data-show_on="productsSetting"
                                            <?= $formdata["product"]["showtitle"] == 1 ? 'checked' : '' ?>
                                        ><span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="product[showtitle]" type="radio" value="0" data-show_off="productsSetting"
                                            <?= $formdata["product"]["showtitle"] == 0 ? 'checked' : '' ?>
                                        ><span>Нет</span></label>
                                </span>
                            </p>
                            <div id="productsSetting">
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта в названии
                                    продукта:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['product']['font-size'] ?>">
                                    <input type="number" min="2" max="80" name="product[font-size]" value="<?= $formdata['product']['font-size'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта в цене
                                    продукта:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['product']['price_font_size'] ?>">
                                    <input type="number" min="2" max="80" name="product[price_font_size]" value="<?= $formdata['product']['price_font_size'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет текста: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="product[text-color]"
                                           value="<?= $formdata['product']['text-color'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет цены: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="product[price_color]"
                                           value="<?= $formdata['product']['price_color'] ?>">
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="col-1-1 mb-0"><h4>Дополнительные элементы</h4></div>
                        <div class="col-1-2">
                            <p><label class="margin-bottom-15">Добавить заголовок:</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="additional[showtitle]" type="radio" value="1" data-show_on="titleSetting"
                                            <?= $formdata['additional']['showtitle'] == 1 ? 'checked' : '' ?>
                                        >
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="additional[showtitle]" type="radio" value="0" data-show_off="titleSetting"
                                            <?= $formdata['additional']['showtitle'] != 1 ? 'checked' : '' ?>>
                                        <span>Нет</span>
                                    </label>
                            </span>
                            </p>
                            <div id="titleSetting">
                            <div class="width-100">
                                <label>Заголовок формы: </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Заказать" name="additional[titletext]"
                                           value="<?= $formdata['additional']['titletext'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Размер шрифта:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['additional']['title-font-size'] ?>">
                                    <input type="number" min="2" max="80" name="additional[title-font-size]" value="<?= $formdata['additional']['title-font-size'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Межбуквенный интервал:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['additional']['title-letter-spacing'] ?>">
                                    <input type="number" min="0" max="80" name="additional[title-letter-spacing]"
                                           value="<?= $formdata['additional']['title-letter-spacing'] ?>">
                                </div>
                            </div>
                            <div class="width-100" bis_skin_checked="1"><label>Форматирование</label>
                                <div class="fonts flex" bis_skin_checked="1">
                                    <div class="font font-bold" data-font_value="font_bold" bis_skin_checked="1"></div>
                                    <div class="font font-under_line" data-font_value="font_under_line"
                                         bis_skin_checked="1"></div>
                                    <div class="font font-italic" data-font_value="font_italic"
                                         bis_skin_checked="1"></div>
                                    <div class="font font-uppercase" data-font_value="font_uppercase"
                                         bis_skin_checked="1"></div>

                                    <input type="hidden" name="additional[titleFormat][font_bold]" value="<?= $formdata['additional']['titleFormat']['font_bold'] ?>">
                                    <input type="hidden" name="additional[titleFormat][font_under_line]" value="<?= $formdata['additional']['titleFormat']['font_under_line'] ?>">
                                    <input type="hidden" name="additional[titleFormat][font_italic]" value="<?= $formdata['additional']['titleFormat']['font_italic'] ?>">
                                    <input type="hidden" name="additional[titleFormat][font_uppercase]" value="<?= $formdata['additional']['titleFormat']['font_uppercase'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Выравнивание: </label>
                                <div class="flex">
                                    <label class="image-checkbox" title="Слева" for="titleAlignLeft">
                                        <input id="titleAlignLeft" name="additional[titleAlign]" type="radio"
                                               value="left"
                                            <?= $formdata['additional']['titleAlign'] == 'left' ? 'checked' : '' ?>>
                                        <img src="/template/admin/images/icons/text-align_left.svg">
                                    </label>
                                    <label class="image-checkbox" title="По центру" for="titleAlignCenter">
                                        <input id="titleAlignCenter" name="additional[titleAlign]" type="radio"
                                               value="center"
                                            <?= $formdata['additional']['titleAlign'] == 'center' ? 'checked' : '' ?>>
                                        <img src="/template/admin/images/icons/text-align_center.svg">
                                    </label>
                                    <label class="image-checkbox" title="Справа" for="titleAlignRight">
                                        <input id="titleAlignRight" name="additional[titleAlign]" type="radio"
                                               value="right"
                                            <?= $formdata['additional']['titleAlign'] == 'right' ? 'checked' : '' ?>>
                                        <img src="/template/admin/images/icons/text-align_right.svg">
                                    </label>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет текста: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="additional[title-color]"
                                           value="<?= $formdata['additional']['title-color'] ?>">
                                </div>
                            </div>
                            </div>

                            <p><label class="margin-bottom-15">Добавить текст под формой:</label>
                                <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="underform[showtext]" type="radio" value="1" data-show_on="underformtextSetting"
                                            <?= $formdata['underform']['showtext'] == 1 ? 'checked': '' ?>>
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="underform[showtext]" type="radio" value="0" data-show_off="underformtextSetting"
                                            <?= $formdata['underform']['showtext'] == 0 ? 'checked': '' ?>>
                                        <span>Нет</span>
                                    </label>
                            </span>
                            </p>
                            <div id="underformtextSetting">
                            <div class="width-100">
                                <label>Текст под формой: </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Заказать" name="underform[text]"
                                           value="<?= $formdata['underform']['text'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Размер шрифта:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['underform']['font-size'] ?>">
                                    <input type="number" min="2" max="80" name="underform[font-size]" value="<?= $formdata['underform']['font-size'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Межбуквенный интервал:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['underform']['letter-spacing'] ?>">
                                    <input type="number" min="0" max="80" name="underform[letter-spacing]" value="<?= $formdata['underform']['letter-spacing'] ?>">
                                </div>
                            </div>
                            <div class="width-100" bis_skin_checked="1"><label>Форматирование</label>
                                <div class="fonts flex" bis_skin_checked="1">
                                    <div class="font font-bold" data-font_value="font_bold" bis_skin_checked="1"></div>
                                    <div class="font font-under_line" data-font_value="font_under_line"
                                         bis_skin_checked="1"></div>
                                    <div class="font font-italic" data-font_value="font_italic"
                                         bis_skin_checked="1"></div>
                                    <div class="font font-uppercase" data-font_value="font_uppercase"
                                         bis_skin_checked="1"></div>

                                    <input type="hidden" name="underform[format][font_bold]" value="<?= $formdata['underform']['format']['font_bold'] ?>>">
                                    <input type="hidden" name="underform[format][font_under_line]" value="<?= $formdata['underform']['format']['font_under_line'] ?>">
                                    <input type="hidden" name="underform[format][font_italic]" value="<?= $formdata['underform']['format']['font_italic'] ?>">
                                    <input type="hidden" name="underform[format][font_uppercase]" value="<?= $formdata['underform']['format']['font_uppercase'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Выравнивание: </label>
                                <div class="flex">
                                    <label class="image-checkbox" title="Слева" for="underformAlignLeft">
                                        <input id="underformAlignLeft" name="underform[Align]" type="radio"
                                                value="left"
                                               <?= $formdata['underform']['Align'] == 'left' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_left.svg">
                                    </label>
                                    <label class="image-checkbox" title="По центру" for="underformAlignCenter">
                                        <input
                                                id="underformAlignCenter" name="underform[Align]" type="radio"
                                                value="center"
                                            <?= $formdata['underform']['Align'] == 'center' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_center.svg">
                                    </label>
                                    <label class="image-checkbox" title="Справа" for="underformAlignRight">
                                        <input
                                                id="underformAlignRight" name="underform[Align]" type="radio"
                                                value="right"
                                            <?= $formdata['underform']['Align'] == 'right' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_right.svg"></label>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет текста: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="underform[text-color]"
                                           value="<?= $formdata['underform']['text-color'] ?>">
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="additional-settings-right-side col-1-2" title="Настройка подзаголовка">
                            <p><label class="margin-bottom-15">Добавить подзаголовок:</label>
                                <span class="custom-radio-wrap">
                                        <label class="custom-radio">
                                            <input name="additional[showSubtitle]" type="radio" value="1" data-show_on="subtitleSetting"
                                                <?= $formdata['additional']['showSubtitle'] == 1 ? 'checked': '' ?>>
                                            <span>Да</span>
                                        </label>
                                        <label class="custom-radio">
                                            <input name="additional[showSubtitle]" type="radio" value="0" data-show_off="subtitleSetting"
                                                <?= $formdata['additional']['showSubtitle'] == 0 ? 'checked': '' ?>>
                                            <span>Нет</span>
                                        </label>
                                </span>
                            </p>
                            <div id="subtitleSetting">
                            <div class="width-100">
                                <label>Подаголовок формы: </label>
                                <div class="wrap">
                                    <input type="text" placeholder="Заказать" name="additional[Subtitletext]"
                                           value="<?= $formdata['additional']['Subtitletext'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Размер шрифта:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="<?= $formdata['additional']['subtitle-font-size'] ?>">
                                    <input type="number" min="2" max="80" name="additional[subtitle-font-size]"
                                           value="<?= $formdata['additional']['subtitle-font-size'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Межбуквенный интервал:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="<?= $formdata['additional']['subtitle-letter-spacing'] ?>">
                                    <input type="number" min="0" max="20" name="additional[subtitle-letter-spacing]"
                                           value="<?= $formdata['additional']['subtitle-letter-spacing'] ?>">
                                </div>
                            </div>
                            <div class="width-100" bis_skin_checked="1"><label>Форматирование</label>
                                <div class="fonts flex" bis_skin_checked="1">
                                    <div class="font font-bold" data-font_value="font_bold" bis_skin_checked="1"></div>
                                    <div class="font font-under_line" data-font_value="font_under_line"
                                         bis_skin_checked="1"></div>
                                    <div class="font font-italic" data-font_value="font_italic"
                                         bis_skin_checked="1"></div>
                                    <div class="font font-uppercase" data-font_value="font_uppercase"
                                         bis_skin_checked="1"></div>

                                    <input type="hidden" name="additional[subtitleFormat][font_bold]" value="<?= $formdata['additional']['subtitleFormat']['font_bold'] ?>">
                                    <input type="hidden" name="additional[subtitleFormat][font_under_line]" value="<?= $formdata['additional']['subtitleFormat']['font_under_line'] ?>">
                                    <input type="hidden" name="additional[subtitleFormat][font_italic]" value="<?= $formdata['additional']['subtitleFormat']['font_italic'] ?>">
                                    <input type="hidden" name="additional[subtitleFormat][font_uppercase]" value="<?= $formdata['additional']['subtitleFormat']['font_uppercase'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Выравнивание: </label>
                                <div class="flex">
                                    <label class="image-checkbox" title="Слева" for="subtitleAlignLeft"><input
                                                id="subtitleAlignLeft" name="additional[subtitleAlign]" type="radio"
                                                value="left"
                                            <?= $formdata['additional']['subtitleAlign'] == 'left' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_left.svg">
                                    </label>
                                    <label class="image-checkbox" title="По центру" for="subtitleAlignCenter"><input
                                                id="subtitleAlignCenter" name="additional[subtitleAlign]" type="radio"
                                                value="center"
                                            <?= $formdata['additional']['subtitleAlign'] == 'center' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_center.svg">
                                    </label>
                                    <label class="image-checkbox" title="Справа" for="subtitleAlignRight"><input
                                                id="subtitleAlignRight" name="additional[subtitleAlign]" type="radio"
                                                value="right"
                                            <?= $formdata['additional']['subtitleAlign'] == 'right' ? 'checked' : '' ?>
                                        >
                                        <img src="/template/admin/images/icons/text-align_right.svg">
                                    </label>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет текста: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="additional[subtitle-color]"
                                           value="<?= $formdata['additional']['subtitle-color'] ?>">
                                </div>
                            </div>
                        </div>
                        </div>

                        <div class="col-1-1 mb-0"><h4>Внешний вид формы</h4></div>
                        <div class="col-1-2">
                            <div class="width-100">
                                <div class="margin-bottom-15">Внутренний отступ:</div>
                                <div class="flex">
                                    <div class="smallinputwrap">
                                        <label>Сверху</label>
                                        <input type="number" name="appearance[inner-padding][top]" placeholder="px"
                                               value="<?= $formdata['appearance']['inner-padding']['top'] ?>">
                                    </div>
                                    <div class="smallinputwrap">
                                        <label>Справа</label>
                                        <input type="number" name="appearance[inner-padding][right]" placeholder="px"
                                               value="<?= $formdata['appearance']['inner-padding']['right'] ?>">
                                    </div>
                                    <div class="smallinputwrap">
                                        <label>Снизу</label>
                                        <input type="number" name="appearance[inner-padding][bottom]" placeholder="px"
                                               value="<?= $formdata['appearance']['inner-padding']['bottom'] ?>">
                                    </div>
                                    <div class="smallinputwrap">
                                        <label>Слева</label>
                                        <input type="number" name="appearance[inner-padding][left]" placeholder="px"
                                               value="<?= $formdata['appearance']['inner-padding']['left'] ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="width-100">
                                <label class="margin-bottom-15">Рамка у полей: </label>
                                <div class="flex">
                                    <label class="image-checkbox" for="appearancebordertop">
                                        <input id="appearancebordertop"
                                                name="appearance[borders][]"
                                                type="checkbox"
                                                value="top"
                                            <?= isset($formdata['appearance']['borders']) && in_array('top', $formdata['appearance']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-top.svg">
                                    </label>
                                    <label class="image-checkbox" for="appearanceborderright">
                                        <input id="appearanceborderright" name="appearance[borders][]" type="checkbox"
                                               value="right"
                                            <?=isset($formdata['appearance']['borders']) && in_array('top', $formdata['appearance']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-right.svg">
                                    </label>
                                    <label class="image-checkbox" for="appearanceborderbottom">
                                        <input id="appearanceborderbottom" name="appearance[borders][]" type="checkbox"
                                                value="bottom"
                                            <?=isset($formdata['appearance']['borders']) && in_array('top', $formdata['appearance']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-bottom.svg">
                                    </label>
                                    <label class="image-checkbox" for="appearanceborderleft">
                                        <input id="appearanceborderleft" name="appearance[borders][]" type="checkbox"
                                                value="left"
                                            <?=isset($formdata['appearance']['borders']) && in_array('top', $formdata['appearance']['borders']) ? 'checked' : ''?>
                                        >
                                        <img src="/template/admin/images/icons/border-left.svg">
                                    </label>
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Радиус скругления:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="<?= $formdata['appearance']['border-radius'] ?>">
                                    <input type="number" min="0" max="20" name="appearance[border-radius]" value="<?= $formdata['appearance']['border-radius'] ?>">
                                </div>
                            </div>
                            <div class="width-100 px-label-wrap" bis_skin_checked="1">
                                <label>Ширина рамки:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="<?= $formdata['appearance']['border-width'] ?>">
                                    <input type="number" min="0" max="20" name="appearance[border-width]" value="<?= $formdata['appearance']['border-width'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Стиль: </label>
                                <div class="select-wrap">
                                    <select name="appearance[style]">
                                        <option value="solid" <?= $formdata['appearance']['style'] == "solid" ? 'selected' : ''?>>Solid</option>
                                        <option value="dotted" <?= $formdata['appearance']['style'] == "dotted" ? 'selected' : ''?>>dotted</option>
                                        <option value="dashed" <?= $formdata['appearance']['style'] == "dashed" ? 'selected' : ''?>>Dashed</option>
                                        <option value="double" <?= $formdata['appearance']['style'] == "double" ? 'selected' : ''?>>double</option>
                                        <option value="groove" <?= $formdata['appearance']['style'] == "groove" ? 'selected' : ''?>>groove</option>
                                        <option value="ridge" <?= $formdata['appearance']['style'] == "ridge" ? 'selected' : ''?>>ridge</option>
                                        <option value="inset" <?= $formdata['appearance']['style'] == "inset" ? 'selected' : ''?>>inset</option>
                                        <option value="outset" <?= $formdata['appearance']['style'] == "outset" ? 'selected' : ''?>>outset</option>
                                    </select>
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет рамки: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="appearance[border-color]"
                                           value="<?= $formdata['appearance']['border-color'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-appearance-right-side col-1-2" title="Внешний вид формы">
                            <div class="width-100 px-label-wrap" bis_skin_checked="1" title="0 - для 100%">
                                <label>Ширина формы:<span class="px-label">px</span></label>
                                <div class="range" bis_skin_checked="1">
                                    <input type="range" min="300" max="2000" oninput="updateRangeInput(this)"
                                           value="<?= $formdata['appearance']['form-width'] ?>">
                                    <input type="number" min="300" max="2000" name="appearance[form-width]" value="<?= $formdata['appearance']['form-width'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Цвет фона формы: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="form[background-color]"
                                           value="<?= $formdata['form']['background-color'] ?>">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Выравнивание формы: </label>
                                <div class="flex">
                                    <label class="image-checkbox" title="Слева" for="formAlignLeft">
                                        <input id="formAlignLeft" name="form[formAlign]" type="radio" value="left" <?= $formdata['form']['formAlign'] == 'left' ? 'checked' : ''?>>
                                        <img src="/template/admin/images/icons/text-align_left.svg">
                                    </label>
                                    <label class="image-checkbox" title="По центру" for="formAlignCenter">
                                        <input id="formAlignCenter" name="form[formAlign]" type="radio" value="center" <?= $formdata['form']['formAlign'] == 'center' ? 'checked' : ''?>>
                                        <img src="/template/admin/images/icons/text-align_center.svg">
                                    </label>
                                    <label class="image-checkbox" title="Справа" for="formAlignRight">
                                        <input id="formAlignRight" name="form[formAlign]" type="radio" value="right" <?= $formdata['form']['formAlign'] == 'right' ? 'checked' : ''?>>
                                        <img src="/template/admin/images/icons/text-align_right.svg">
                                    </label>
                                </div>
                            </div>



                        </div>
                        </div>
                    </div>


                <!-- 3 Вкладка Предпросмотр -->
                <div>
                </form>
                    <div class="row-line">
                        <div class="col-1-1 mb-0"><h4>Предпросмотр</h4></div>
                        <div class="col-1-1 mb-0"><div class="margin-15-bottom">Если параметры формы были изменены, нажмите на клавишу сохранить, перед тем как увидеть результат. Если вы поменяли какую-либо настройку, но изменения не отобразились, нажмите ctrl+f5</div></div>
                        <div class="wrapperformpreview">
                            <?= $generated_form ?>
                        </div>
                        <div class="col-1-1 mb-0"><h4>Код для вставки</h4></div>
                        <div class="col-1-1 mb-0">
                            <textarea id="textarearesult" class="margin-bottom-15" rows="10" cols="20" style="height: 200px;"><?= $generated_form ?></textarea>
                            <div class="button-green-rounding" onclick="let text = document.getElementById('textarearesult'); text.select();document.execCommand('copy');window.getSelection().removeAllRanges();">Копировать код</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
</div>
</div>
</form>
<?php require_once(ROOT . '/template/admin/layouts/admin-footer.php'); ?>
</div>
<link rel="stylesheet" type="text/css" href="/template/admin/css/jquery.datetimepicker.min.css">
<script src="/template/admin/js/jquery.datetimepicker.full.min.js"></script>
<script>
    jQuery('.datetimepicker').datetimepicker({
        format: 'd.m.Y H:i',
        lang: 'ru'
    });
</script>
<script src="/template/admin/js/main.js" type="text/javascript"></script>
<script src="/lib/coloris/dist/coloris.min.js"></script>
<link rel="stylesheet" href="/lib/coloris/dist/coloris.min.css">
<!-- Переключение состояния для настроек кнопки-->
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        let cursorAutoBtn = document.getElementById('btncursor1');
        let settingsAutoBtnLeft = document.getElementById('nohoverbtnleft');
        let settingsAutoBtnRight = document.getElementById('nohoverbtnright');
        let cursorPointerBtn = document.getElementById('btncursor2');
        let settingsPointerBtnLeft = document.getElementById('hoverbtnleft');
        let settingsPointerBtnRight = document.getElementById('hoverbtnright');

        cursorAutoBtn.addEventListener('click', function () {
            settingsPointerBtnLeft.style.display = 'none';
            settingsPointerBtnRight.style.display = 'none';
            settingsAutoBtnLeft.style.display = 'block';
            settingsAutoBtnRight.style.display = 'block';
        });

        cursorPointerBtn.addEventListener('click', function () {
            settingsPointerBtnLeft.style.display = 'block';
            settingsPointerBtnRight.style.display = 'block';
            settingsAutoBtnLeft.style.display = 'none';
            settingsAutoBtnRight.style.display = 'none';
        });
    });
</script>
<!--ПЕРЕКЛЮЧЕНИЕ ЧЕКБОКСОВ-->
<script>
    function showblock(blockid) {
        let block = document.getElementById(blockid);
        block.classList.toggle('hidden');
    }
</script>
</body>
</html>