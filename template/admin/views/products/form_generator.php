<?php defined('BILLINGMASTER') or die;
require_once(ROOT . '/template/admin/layouts/admin-head.php'); ?>

<body id="page">
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
                <?php if ($show != 'showhtml') { ?>
                    <li><input type="submit"
                               value="<?= $show == 'showOnlySelectProduct' ? 'Выбрать' : 'Предпросмотр' ?>"
                               class="button save button-white font-bold"
                               name="<?= $show == 'showOnlySelectProduct' ? '' : 'saveform' ?>"
                        >
                    </li>
                    <li class="nav_button__last"><a class="button red-link" href="/admin/products/formlist">Закрыть</a></li>
                <? } ?>
                <?php if ($show == 'showhtml') { ?>
                    <li><input type="submit" name="saveform"
                        value="Сохранить форму"
                        class="button save button-white font-bold">
                    </li>
                    <li class="nav_button__last"><a class="button red-link" href="/admin/products/formlist">Вернуться</a></li>
                    </form>
                <? } ?>
            </ul>
        </div>
        <div class="admin_form formgenerator">
            <div class="row-line">
                <?php if ($show == 'showOnlySelectProduct') {    ?>
                    <div class="col-1-1 mb-0"><h4>Основное</h4></div>
                    <div class="col-1-2">
                        <p class="width-100">
                            <label>Название формы: </label>
                            <input type="text" name="form[name]" placeholder="Форма для сайта" required>
                        </p>
                        <div class="width-100"><label>Выберите продукт или продукты для формы: </label>
                            <div class="select-wrap">
                                <select name="products_id[]" multiple="multiple" class="multiple-select" size="10" required>
                                    <?php foreach ($products as $product) { ?>
                                        <option value="<?= $product['product_id']; ?>"><?= $product['product_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цель Я.Метрики (номер цели): </label>
                            <div class="wrap">
                                <input type="text" placeholder="Укажите номер цели, или идентификатор" name="data[yandex_target_id]">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Купон на скидку: </label>
                            <div class="wrap">
                                <input type="text" placeholder="Введите купон" name="data[discount_coupon]">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Закрепление за партнером: </label>
                            <div class="wrap">
                                <input type="text" placeholder="Введите id партнера" name="data[partner_id]">
                            </div>
                        </div>
                        <div class="width-100" style="display: none;">
                            <span class="custom-checkbox-wrap">
                                <label class="custom-chekbox-wrap">
                                        <input type="checkbox" value="1" name="form[useUtmScript]" checked>
                                        <span class="custom-chekbox"></span>Добавить код для протаскивания utm-меток
                                </label>
                            </span>
                        </div>
                        <div class="width-100"><label>Событие после отправки: </label>
                            <div class="select-wrap">
                                <select name="sendevent[type]">
                                    <option value="0" selected>По умолчанию (направить в SM)</option>
                                    <option value="1" data-show_on="SendEventText">Вывести сообщение</option>
                                </select>
                            </div>
                        </div>
                        <div class="width-100" id="SendEventText">
                            <label>Текст после: </label>
                            <div class="wrap">
                                <textarea type="text" placeholder="Введите текст. Он покажется после отправки заявки" name="sendevent[text]">Спасибо! Ваша заявка отправлена</textarea>
                            </div>
                        </div>
                        <div class="width-100"><label>Язык формы: </label>
                            <div class="select-wrap">
                                <select name="lang">
                                    <option value="ru">Русский</option>
                                    <option value="en">English</option>
                                    <option value="ua">Український</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-1-2">

                    </div>
                <?php } ?>

                <?php if ($show == 'showAll') { ?>
                <div class="col-1-1 mb-0"><h4>Настройка полей</h4></div>
                <div class="col-1-2">
                    <?php //Айди продуктов
                    foreach ($_POST['products_id'] as $id) { ?>
                        <input name="products_id[]" type="hidden" value="<?= $id ?>">
                    <?php } ?>
                        <input type="hidden" name="data[yandex_target_id]" value="<?= $data['yandex_target_id'] ?>">
                        <input type="hidden" name="data[discount_coupon]" value="<?= $data['discount_coupon'] ?>">
                        <input type="hidden" name="data[partner_id]" value="<?= $data['partner_id'] ?>">
                        <input name="form[name]" type="hidden" value="<?= $_POST['form']['name'] ?>">
                        <input name="form[useUtmScript]" type="hidden" value="<?= $_POST['form']['useUtmScript'] ?? '0' ?>">
                        <input name="sendevent[type]" type="hidden" value="<?= $_POST['sendevent']['type'] ?? '0' ?>">
                        <input name="sendevent[text]" type="hidden" value="<?= $_POST['sendevent']['text'] ?? '' ?>">
                        <input name="lang" type="hidden" value="<?= $_POST['lang'] ?>">
                    <!-- TODO генератор полей -->
                    <div class="width-100"><label>Выберите поля для заполнения: </label>
                        <div class="select-wrap">
                            <select name="fields[fill][]" multiple="multiple" class="multiple-select" size="10">
                                <option value="usename" selected>Имя</option>
                                <option value="usesurname">Фамилия</option>
                                <option value="useemail" selected>E-mail</option>
                                <option value="usetg">Telegram</option>
                                <option value="usephone">Телефон</option>
                                <option value="usepolicy" selected>Согласие с политикой</option>
                                <option value="usepromo">Промокод</option>
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
                                    ><span>Радиокнопки</span></label>
                                <label class="custom-radio">
                                    <input name="form[product_kind_of_choice]" type="radio"
                                           value="select"
                                           data-show_off="titleSettings"
                                    ><span>Список</span></label>
                            </span>
                        </p>
                    </div>

                <div class="col-1-1 mb-0"><h4>Внешний вид полей</h4></div>
                <div class="col-1-2">
                    <div class="width-100">
                        <label class="margin-bottom-15">Рамка у полей: </label>
                        <div class="flex">
                            <label class="image-checkbox" for="bordertop"><input id="bordertop"
                                                                                 name="fields[borders][]"
                                                                                 type="checkbox" value="top"
                                                                                 checked><img
                                        src="/template/admin/images/icons/border-top.svg"></label>
                            <label class="image-checkbox" for="borderright"><input id="borderright"
                                                                                   name="fields[borders][]"
                                                                                   type="checkbox" value="right"
                                                                                   checked><img
                                        src="/template/admin/images/icons/border-right.svg"></label>
                            <label class="image-checkbox" for="borderbottom"><input id="borderbottom"
                                                                                    name="fields[borders][]"
                                                                                    type="checkbox" value="bottom"
                                                                                    checked><img
                                        src="/template/admin/images/icons/border-bottom.svg"></label>
                            <label class="image-checkbox" for="borderleft"><input id="borderleft"
                                                                                  name="fields[borders][]"
                                                                                  type="checkbox" value="left"
                                                                                  checked><img
                                        src="/template/admin/images/icons/border-left.svg"></label>
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Радиус скругления<span
                                    class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="5">
                            <input type="number" min="2" max="80" name="fields[border-radius]" value="5">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Ширина рамки<span
                                    class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="1" max="80" oninput="updateRangeInput(this)" value="1">
                            <input type="number" min="1" max="30" name="fields[width_border]" value="1">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Стиль: </label>
                        <div class="select-wrap">
                            <select name="fields[style]">
                                <option value="solid" selected>Solid</option>
                                <option value="dotted">dotted</option>
                                <option value="dashed">Dashed</option>
                                <option value="double">double</option>
                                <option value="groove">groove</option>
                                <option value="ridge">ridge</option>
                                <option value="inset">inset</option>
                                <option value="outset">outset</option>
                            </select>
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет рамки: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="fields[border-color]"
                                   value="#B1B1B1">
                        </div>
                    </div>
                    <div class="width-100">
                        <span class="custom-checkbox-wrap">
                            <label class="custom-chekbox-wrap">
                                    <input type="checkbox" value="1" name="fields[labels]" checked oninput="showblock('labelSettings');">
                                    <span class="custom-chekbox"></span>Выводить лейблы у полей
                            </label>
                        </span>
                    </div>
                    <div id="labelSettings" class="margin-bottom-15">
                        <div class="width-100 px-label-wrap" bis_skin_checked="1">
                            <label>Размер шрифта<span class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="1" max="80" oninput="updateRangeInput(this)" value="14">
                                <input type="number" min="1" max="30" name="fields[label_font_size]" value="14">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цвет текста: </label>
                            <div class="color-input-wrap">
                                <input type="text" data-coloris="" class="coloris" name="fields[label_color]"
                                       value="#373A4C">
                            </div>
                        </div>
                    </div>
                    <div class="width-100">
                            <span class="custom-checkbox-wrap">
                                <label class="custom-chekbox-wrap">
                                        <input type="checkbox" value="1" name="fields[placeholder]" oninput="showblock('placeholderSettings');">
                                        <span class="custom-chekbox"></span>Показывать плейсхолдер
                                </label>
                            </span>
                    </div>
                    <div id="placeholderSettings" class="hidden">
                        <div class="width-100 px-label-wrap" bis_skin_checked="1">
                            <label>Размер шрифта<span class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="1" max="80" oninput="updateRangeInput(this)" value="14">
                                <input type="number" min="1" max="30" name="fields[placeholder_font_size]" value="14">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цвет текста: </label>
                            <div class="color-input-wrap">
                                <input type="text" data-coloris="" class="coloris" name="fields[placeholder_color]"
                                       value="#B1B1B1">
                            </div>
                        </div>
                    </div>



                </div>
                <div class="fields-settings-right-side col-1-2" title="Настройка полей">
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта в полях:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="2" max="32" oninput="updateRangeInput(this)" value="16">
                                <input type="number" min="2" max="32" name="fields[font-size]" value="16">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Высота поля<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="10" max="100" oninput="updateRangeInput(this)" value="40">
                                <input type="number" min="10" max="100" name="fields[height]" value="40">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цвет текста: </label>
                            <div class="color-input-wrap">
                                <input type="text" data-coloris="" class="coloris" name="fields[text-color]"
                                       value="#000000">
                            </div>
                        </div>
                    </div>

                <div class="col-1-1 mb-0"><h4>Поле телефона</h4></div>
                <div class="col-1-2">
                    <div class="width-100">
                        <span class="custom-checkbox-wrap">
                            <label class="custom-chekbox-wrap">
                                    <input type="checkbox" value="1" name="fields[phone][enable_mask]" checked>
                                    <span class="custom-chekbox"></span>Добавить маску для телефона
                            </label>
                        </span>
                    </div>
                </div>

                <div class="col-1-1 mb-0"><h4>Политика обработки персональных данных</h4></div>
                <div class="col-1-1">
                    <div class="width-100">
                        <textarea style="height: 200px;" class="editor" name="fields[policyLink]" rows="55" cols="55" placeholder="Текст политики">Согласен с условиями политики конфиденциальности и условиями договора оферты</textarea>
                    </div>
                </div>

                <div class="col-1-1 mb-0"><h4>Настройка кнопки</h4></div>
                <div class="col-1-2">
                    <div class="width-100">
                        <label>Текст кнопки: </label>
                        <div class="wrap">
                            <input type="text" placeholder="Заказать" name="btn[text]" value="Заказать">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Состояние кнопки: </label>
                        <div class="flex">
                            <label class="image-checkbox" for="btncursor1"><input id="btncursor1" name="btn[cursor]"
                                                                                  type="radio" value="auto" checked>
                                <img src="/template/admin/images/icons/cursor_auto.svg">
                            </label>
                            <label class="image-checkbox" for="btncursor2"><input id="btncursor2" name="btn[cursor]"
                                                                                  type="radio" value="pointer">
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
                                           value="#ffffff">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Фон кнопки: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="btn[btn-background]"
                                           value="#0772A0">
                                </div>
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="8" max="30" oninput="updateRangeInput(this)" value="14">
                                <input type="number" min="8" max="30" name="btn[font-size]" value="14">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Межбуквенный интервал:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="0" max="16" oninput="updateRangeInput(this)" value="0">
                                <input type="number" min="0" max="16" name="btn[letter-spacing]" value="0">
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

                                <input type="hidden" name="btn[format][font_bold]" value="0">
                                <input type="hidden" name="btn[format][font_under_line]" value="0">
                                <input type="hidden" name="btn[format][font_italic]" value="0">
                                <input type="hidden" name="btn[format][font_uppercase]" value="0">
                            </div>
                        </div>
                        <div class="width-100">
                            <div class="margin-bottom-15"><b>Внутренний отступ: </b></div>
                            <div class="flex">
                                <div class="smallinputwrap">
                                    <label>Сверху</label>
                                    <input type="number" name="btn[inner-padding][top]" placeholder="px" value="15">
                                </div>
                                <div class="smallinputwrap">
                                    <label>Справа</label>
                                    <input type="number" name="btn[inner-padding][right]" placeholder="px" value="65">
                                </div>
                                <div class="smallinputwrap">
                                    <label>Снизу</label>
                                    <input type="number" name="btn[inner-padding][bottom]" placeholder="px" value="15">
                                </div>
                                <div class="smallinputwrap">
                                    <label>Слева</label>
                                    <input type="number" name="btn[inner-padding][left]" placeholder="px" value="65">
                                </div>
                            </div>
                        </div>
                        <div class="width-100">
                            <label class="margin-bottom-15">Рамка у кнопки: </label>
                            <div class="flex">
                                <label class="image-checkbox" for="bordertopbtn">
                                    <input id="bordertopbtn" name="btn[borders][]" type="checkbox" value="top" checked>
                                    <img src="/template/admin/images/icons/border-top.svg">
                                </label>
                                <label class="image-checkbox" for="borderrightbtn">
                                    <input id="borderrightbtn" name="btn[borders][]" type="checkbox" value="right" checked>
                                    <img src="/template/admin/images/icons/border-right.svg">
                                </label>
                                <label class="image-checkbox" for="borderbottombtn">
                                    <input id="borderbottombtn" name="btn[borders][]" type="checkbox" value="bottom" checked>
                                    <img src="/template/admin/images/icons/border-bottom.svg">
                                </label>
                                <label class="image-checkbox" for="borderleftbtn">
                                    <input id="borderleftbtn" name="btn[borders][]" type="checkbox" value="left" checked>
                                    <img src="/template/admin/images/icons/border-left.svg">
                                </label>
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Радиус скругления:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="10">
                                <input type="number" min="2" max="80" name="btn[border-radius]" value="10">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Ширина рамки:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="1">
                                <input type="number" min="0" max="80" name="btn[border-width]" value="1">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Стиль: </label>
                            <div class="select-wrap">
                                <select name="btn[border-style]">
                                    <option value="solid" selected>Solid</option>
                                    <option value="dotted">dotted</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="double">double</option>
                                    <option value="groove">groove</option>
                                    <option value="ridge">ridge</option>
                                    <option value="inset">inset</option>
                                    <option value="outset">outset</option>
                                </select>
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цвет: </label>
                            <div class="color-input-wrap">
                                <input type="text" data-coloris="" class="coloris" name="btn[border-color]"
                                       value="#0772A0">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Ширина кнопки: </label>
                            <div class="select-wrap">
                                <select name="btn[btn-width]">
                                    <option value="10%">10%</option>
                                    <option value="20%">20%</option>
                                    <option value="30%">30%</option>
                                    <option value="40%">40%</option>
                                    <option value="50%">50%</option>
                                    <option value="60%">60%</option>
                                    <option value="70%" selected>70%</option>
                                    <option value="80%">80%</option>
                                    <option value="90%">90%</option>
                                    <option value="100%">100%</option>
                                </select>
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Выравнивание кнопки: </label>
                            <div class="flex">
                                <label class="image-checkbox" title="Слева" for="alignLeft"><input id="alignLeft"
                                                                                                   name="btn[align]"
                                                                                                   type="radio"
                                                                                                   value="left"><img
                                            src="/template/admin/images/icons/text-align_left.svg"></label>
                                <label class="image-checkbox" title="По центру" for="alignCenter"><input
                                            id="alignCenter" name="btn[align]" type="radio" value="center" checked><img
                                            src="/template/admin/images/icons/text-align_center.svg"></label>
                                <label class="image-checkbox" title="Справа" for="alignRight"><input id="alignRight"
                                                                                                     name="btn[align]"
                                                                                                     type="radio"
                                                                                                     value="right"><img
                                            src="/template/admin/images/icons/text-align_right.svg"></label>
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
                                           value="#000000">
                                </div>
                            </div>
                            <div class="width-100">
                                <label>Фон кнопки: </label>
                                <div class="color-input-wrap">
                                    <input type="text" data-coloris="" class="coloris" name="btn[hover][btn-background]"
                                           value="#FFFFFF">
                                </div>
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="8" max="30" oninput="updateRangeInput(this)" value="14">
                                <input type="number" min="8" max="30" name="btn[hover][font-size]" value="14">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Межбуквенный интервал:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="0" max="16" oninput="updateRangeInput(this)" value="0">
                                <input type="number" min="0" max="16" name="btn[hover][letter-spacing]" value="0">
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

                                <input type="hidden" name="btn[hover][format][font_bold]" value="0">
                                <input type="hidden" name="btn[hover][format][font_under_line]" value="0">
                                <input type="hidden" name="btn[hover][format][font_italic]" value="0">
                                <input type="hidden" name="btn[hover][format][font_uppercase]" value="0">
                            </div>
                        </div>
                        <div class="width-100">
                            <div class="margin-bottom-15"><b>Внутренний отступ: </b></div>
                            <div class="flex">
                                <div class="smallinputwrap">
                                    <label>Сверху</label>
                                    <input type="number" name="btn[hover][inner-padding][top]" placeholder="px" value="15">
                                </div>
                                <div class="smallinputwrap">
                                    <label>Справа</label>
                                    <input type="number" name="btn[hover][inner-padding][right]" placeholder="px" value="65">
                                </div>
                                <div class="smallinputwrap">
                                    <label>Снизу</label>
                                    <input type="number" name="btn[hover][inner-padding][bottom]" placeholder="px" value="15">
                                </div>
                                <div class="smallinputwrap">
                                    <label>Слева</label>
                                    <input type="number" name="btn[hover][inner-padding][left]" placeholder="px" value="65">
                                </div>
                            </div>
                        </div>
                        <div class="width-100">
                            <label class="margin-bottom-15">Рамка у кнопки: </label>
                            <div class="flex">
                                <label class="image-checkbox" for="bordertopbtnhover">
                                    <input id="bordertopbtnhover" name="btn[hover][borders][]" type="checkbox" value="top" checked>
                                    <img src="/template/admin/images/icons/border-top.svg">
                                </label>
                                <label class="image-checkbox" for="borderrightbtnhover">
                                    <input id="borderrightbtnhover" name="btn[hover][borders][]" type="checkbox" value="right" checked>
                                    <img src="/template/admin/images/icons/border-right.svg">
                                </label>
                                <label class="image-checkbox" for="borderbottombtnhover">
                                    <input id="borderbottombtnhover" name="btn[hover][borders][]" type="checkbox" value="bottom" checked>
                                    <img src="/template/admin/images/icons/border-bottom.svg"
                                </label></label>
                                <label class="image-checkbox" for="borderleftbtnhover">
                                    <input id="borderleftbtnhover" name="btn[hover][borders][]" type="checkbox" value="left" checked>
                                    <img src="/template/admin/images/icons/border-left.svg">
                                </label>
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Радиус скругления:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="10">
                                <input type="number" min="2" max="80" name="btn[hover][border-radius]" value="10">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Ширина рамки:<span
                                        class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="1">
                                <input type="number" min="0" max="80" name="btn[hover][border-width]" value="1">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Стиль: </label>
                            <div class="select-wrap">
                                <select name="btn[hover][border-style]">
                                    <option value="solid" selected>Solid</option>
                                    <option value="dotted">dotted</option>
                                    <option value="dashed">Dashed</option>
                                    <option value="double">double</option>
                                    <option value="groove">groove</option>
                                    <option value="ridge">ridge</option>
                                    <option value="inset">inset</option>
                                    <option value="outset">outset</option>
                                </select>
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цвет: </label>
                            <div class="color-input-wrap">
                                <input type="text" data-coloris="" class="coloris" name="btn[hover][border-color]"
                                       value="#0772A0">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Ширина кнопки: </label>
                            <div class="select-wrap">
                                <select name="btn[hover][btn-width]">
                                    <option value="10%">10%</option>
                                    <option value="20%">20%</option>
                                    <option value="30%">30%</option>
                                    <option value="40%">40%</option>
                                    <option value="50%">50%</option>
                                    <option value="60%">60%</option>
                                    <option value="70%" selected>70%</option>
                                    <option value="80%">80%</option>
                                    <option value="90%">90%</option>
                                    <option value="100%">100%</option>
                                </select>
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Выравнивание кнопки: </label>
                            <div class="flex">
                                <label class="image-checkbox" title="Слева" for="alignLeft">
                                    <input id="alignLeft" name="btn[hover][align]" type="radio" value="left">
                                    <img src="/template/admin/images/icons/text-align_left.svg">
                                </label>
                                <label class="image-checkbox" title="По центру" for="alignCenter">
                                    <input id="alignCenter" name="btn[hover][align]" type="radio" value="center" checked>
                                    <img src="/template/admin/images/icons/text-align_center.svg">
                                </label>
                                <label class="image-checkbox" title="Справа" for="alignRight">
                                    <input id="alignRight" name="btn[hover][align]" type="radio" value="right">
                                    <img src="/template/admin/images/icons/text-align_right.svg">
                                </label>
                            </div>
                        </div>



                    </div>
                </div>
                    <div class="button-settings-right-side col-1-2" title="Настройка кнопки">
                        <div id="nohoverbtnright">
                            <div class="shadow-settings-right-side" title="Настройка тени у кнопки">
                                <h5>Тень</h5>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По горизонтали<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                        <input type="number" min="0" max="80" name="btn[shadow-horiz]" value="0">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По вертикали<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                        <input type="number" min="0" max="80" name="btn[shadow-vertic]" value="0">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размытие<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                        <input type="number" min="0" max="80" name="btn[shadow-blur]" value="0">
                                    </div>
                                </div>
                                <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Spread<span
                                                class="px-label">px</span></label>
                                    <div class="range" bis_skin_checked="1">
                                        <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                        <input type="number" min="0" max="80" name="btn[shadow-spread]" value="0">
                                    </div>
                                </div>
                                <div class="width-100">
                                    <label>Цвет тени: </label>
                                    <div class="color-input-wrap">
                                        <input type="text" data-coloris="" class="coloris" name="btn[shadow-color]"
                                               value="#f1f1f1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="hoverbtnright" style="display: none;">

                            <div id="nohoverbtnright">
                                <div class="shadow-settings-right-side" title="Настройка тени у кнопки">
                                    <h5>Тень</h5>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По горизонтали<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-horiz]" value="0">
                                        </div>
                                    </div>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>По вертикали<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-vertic]" value="0">
                                        </div>
                                    </div>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размытие<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-blur]" value="0">
                                        </div>
                                    </div>
                                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Spread<span
                                                    class="px-label">px</span></label>
                                        <div class="range" bis_skin_checked="1">
                                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                                            <input type="number" min="0" max="80" name="btn[hover][shadow-spread]" value="0">
                                        </div>
                                    </div>
                                    <div class="width-100">
                                        <label>Цвет тени: </label>
                                        <div class="color-input-wrap">
                                            <input type="text" data-coloris="" class="coloris" name="btn[hover][shadow-color]"
                                                   value="#faa0a0">
                                        </div>
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
                                        <input name="product[showtitle]" type="radio" value="1" <?= count($_POST['products_id']) > 1 ? 'checked' : '' ?>
                                            data-show_on="ProductSettings">
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="product[showtitle]" type="radio" value="0" <?= count($_POST['products_id']) == 1 ? 'checked' : '' ?>
                                               data-show_off="ProductSettings">
                                        <span>Нет</span>
                                    </label>
                                </span>
                    </p>
                    <div id="ProductSettings">
                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта в названии
                            продукта:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="14">
                            <input type="number" min="2" max="80" name="product[font-size]" value="14">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1"><label>Размер шрифта в цене
                            продукта:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="14">
                            <input type="number" min="2" max="80" name="product[price_font_size]" value="14">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет текста: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="product[text-color]"
                                   value="#000000">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет цены: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="product[price_color]"
                                   value="#000000">
                        </div>
                    </div>
                    </div>
                </div>

                <div class="col-1-1 mb-0"><h4>Дополнительные элементы</h4></div>
                <div class="col-1-2">
                    <p><label class="margin-bottom-15">Добавить заголовок:</label>
                        <span class="custom-radio-wrap">
                                <label class="custom-radio">
                                    <input name="additional[showtitle]" type="radio"
                                           value="1"
                                           data-show_on="titleSettings"
                                    ><span>Да</span></label>
                                <label class="custom-radio">
                                    <input name="additional[showtitle]" type="radio"
                                           value="0" checked
                                           data-show_off="titleSettings"
                                    ><span>Нет</span></label>
                            </span>
                    </p>
                    <div id="titleSettings">
                    <div class="width-100">
                        <label>Заголовок формы: </label>
                        <div class="wrap">
                            <input type="text" placeholder="Заказать" name="additional[titletext]" value="Оформить заказ">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1">
                        <label>Размер шрифта:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="32">
                            <input type="number" min="2" max="80" name="additional[title-font-size]" value="32">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1">
                        <label>Межбуквенный интервал:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                            <input type="number" min="0" max="80" name="additional[title-letter-spacing]" value="0">
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

                            <input type="hidden" name="additional[titleFormat][font_bold]" value="0">
                            <input type="hidden" name="additional[titleFormat][font_under_line]" value="0">
                            <input type="hidden" name="additional[titleFormat][font_italic]" value="0">
                            <input type="hidden" name="additional[titleFormat][font_uppercase]" value="0">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Выравнивание: </label>
                        <div class="flex">
                            <label class="image-checkbox" title="Слева" for="titleAlignLeft">
                                <input id="titleAlignLeft" name="additional[titleAlign]" type="radio" value="left">
                                <img src="/template/admin/images/icons/text-align_left.svg">
                            </label>
                            <label class="image-checkbox" title="По центру" for="titleAlignCenter">
                                <input id="titleAlignCenter" name="additional[titleAlign]" type="radio" value="center" checked>
                                <img src="/template/admin/images/icons/text-align_center.svg">
                            </label>
                            <label class="image-checkbox" title="Справа" for="titleAlignRight">
                                <input id="titleAlignRight" name="additional[titleAlign]" type="radio" value="right">
                                <img src="/template/admin/images/icons/text-align_right.svg">
                            </label>
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет текста: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="additional[title-color]"
                                   value="#000000">
                        </div>
                    </div>
                    </div>
                    <p><label class="margin-bottom-15">Добавить текст под формой:</label>
                        <span class="custom-radio-wrap">
                                    <label class="custom-radio">
                                        <input name="underform[showtext]" type="radio"
                                               value="1"
                                               data-show_on="underformtextSettings"
                                        >
                                        <span>Да</span>
                                    </label>
                                    <label class="custom-radio">
                                        <input name="underform[showtext]" type="radio" value="0"
                                               checked
                                               data-show_off="underformtextSettings"
                                        >
                                        <span>Нет</span>
                                    </label>
                            </span>
                    </p>
                    <div id="underformtextSettings">
                    <div class="width-100">
                        <label>Текст под формой: </label>
                        <div class="wrap">
                            <input type="text" placeholder="Заказать" name="underform[text]"
                                   value="Ваши данные защищены">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1">
                        <label>Размер шрифта:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="12">
                            <input type="number" min="2" max="80" name="underform[font-size]" value="12">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1">
                        <label>Межбуквенный интервал:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="0" max="80" oninput="updateRangeInput(this)" value="0">
                            <input type="number" min="0" max="80" name="underform[letter-spacing]" value="0">
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

                            <input type="hidden" name="underform[format][font_bold]" value="0">
                            <input type="hidden" name="underform[format][font_under_line]" value="0">
                            <input type="hidden" name="underform[format][font_italic]" value="0">
                            <input type="hidden" name="underform[format][font_uppercase]" value="0">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Выравнивание: </label>
                        <div class="flex">
                            <label class="image-checkbox" title="Слева" for="underformAlignLeft">
                                <input id="underformAlignLeft" name="underform[Align]" type="radio" value="left" checked>
                                <img src="/template/admin/images/icons/text-align_left.svg">
                            </label>
                            <label class="image-checkbox" title="По центру" for="underformAlignCenter">
                                <input id="underformAlignCenter" name="underform[Align]" type="radio" value="center">
                                <img src="/template/admin/images/icons/text-align_center.svg">
                            </label>
                            <label class="image-checkbox" title="Справа" for="underformAlignRight">
                                <input id="underformAlignRight" name="underform[Align]" type="radio" value="right">
                                <img src="/template/admin/images/icons/text-align_right.svg">
                            </label>
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет текста: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="underform[text-color]"
                                   value="#000000">
                        </div>
                    </div>
                </div>
                </div>


                <div class="additional-settings-right-side col-1-2" title="Настройка подзаголовка">
                        <p><label class="margin-bottom-15">Добавить подзаголовок:</label>
                            <span class="custom-radio-wrap">
                                        <label class="custom-radio">
                                            <input name="additional[showSubtitle]" type="radio"
                                                   value="1"
                                                   data-show_on="subtitleSettings"
                                            >
                                            <span>Да</span>
                                        </label>
                                        <label class="custom-radio">
                                            <input name="additional[showSubtitle]" type="radio"
                                                   value="0" checked
                                                   data-show_off="subtitleSettings"
                                            >
                                            <span>Нет</span>
                                        </label>
                                </span>
                        </p>
                    <div id="subtitleSettings">
                        <div class="width-100">
                            <label>Подзаголовок формы: </label>
                            <div class="wrap">
                                <input type="text" placeholder="Заказать" name="additional[Subtitletext]"
                                       value="Выберите комплект и введите данные">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1">
                            <label>Размер шрифта:<span class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="2" max="80" oninput="updateRangeInput(this)" value="16">
                                <input type="number" min="2" max="80" name="additional[subtitle-font-size]" value="16">
                            </div>
                        </div>
                        <div class="width-100 px-label-wrap" bis_skin_checked="1">
                            <label>Межбуквенный интервал:<span class="px-label">px</span></label>
                            <div class="range" bis_skin_checked="1">
                                <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="0">
                                <input type="number" min="0" max="20" name="additional[subtitle-letter-spacing]"
                                       value="0">
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

                                <input type="hidden" name="additional[subtitleFormat][font_bold]" value="0">
                                <input type="hidden" name="additional[subtitleFormat][font_under_line]" value="0">
                                <input type="hidden" name="additional[subtitleFormat][font_italic]" value="0">
                                <input type="hidden" name="additional[subtitleFormat][font_uppercase]" value="0">
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Выравнивание: </label>
                            <div class="flex">
                                <label class="image-checkbox" title="Слева" for="subtitleAlignLeft">
                                    <input id="subtitleAlignLeft" name="additional[subtitleAlign]" type="radio" value="left">
                                    <img src="/template/admin/images/icons/text-align_left.svg">
                                </label>
                                <label class="image-checkbox" title="По центру" for="subtitleAlignCenter">
                                    <input id="subtitleAlignCenter" name="additional[subtitleAlign]" type="radio" value="center" checked>
                                    <img src="/template/admin/images/icons/text-align_center.svg">
                                </label>
                                <label class="image-checkbox" title="Справа" for="subtitleAlignRight">
                                    <input id="subtitleAlignRight" name="additional[subtitleAlign]" type="radio" value="right">
                                    <img src="/template/admin/images/icons/text-align_right.svg">
                                </label>
                            </div>
                        </div>
                        <div class="width-100">
                            <label>Цвет текста: </label>
                            <div class="color-input-wrap">
                                <input type="text" data-coloris="" class="coloris" name="additional[subtitle-color]"
                                       value="#000000">
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
                                       value="30">
                            </div>
                            <div class="smallinputwrap">
                                <label>Справа</label>
                                <input type="number" name="appearance[inner-padding][right]" placeholder="px"
                                       value="30">
                            </div>
                            <div class="smallinputwrap">
                                <label>Снизу</label>
                                <input type="number" name="appearance[inner-padding][bottom]" placeholder="px"
                                       value="30">
                            </div>
                            <div class="smallinputwrap">
                                <label>Слева</label>
                                <input type="number" name="appearance[inner-padding][left]" placeholder="px"
                                       value="30">
                            </div>
                        </div>
                    </div>
                    <div class="width-100">
                        <label class="margin-bottom-15">Рамка</label>
                        <div class="flex">
                            <label class="image-checkbox" for="appearancebordertop"><input id="appearancebordertop"
                                                                                           name="appearance[borders][]"
                                                                                           type="checkbox"
                                                                                           value="top" checked><img
                                        src="/template/admin/images/icons/border-top.svg"></label>
                            <label class="image-checkbox" for="appearanceborderright"><input
                                        id="appearanceborderright" name="appearance[borders][]" type="checkbox"
                                        value="right" checked><img
                                        src="/template/admin/images/icons/border-right.svg"></label>
                            <label class="image-checkbox" for="appearanceborderbottom"><input
                                        id="appearanceborderbottom" name="appearance[borders][]" type="checkbox"
                                        value="bottom" checked><img
                                        src="/template/admin/images/icons/border-bottom.svg"></label>
                            <label class="image-checkbox" for="appearanceborderleft"><input
                                        id="appearanceborderleft" name="appearance[borders][]" type="checkbox"
                                        value="left" checked><img
                                        src="/template/admin/images/icons/border-left.svg"></label>
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1">
                        <label>Радиус скругления:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="10">
                            <input type="number" min="0" max="20" name="appearance[border-radius]" value="10">
                        </div>
                    </div>
                    <div class="width-100 px-label-wrap" bis_skin_checked="1">
                        <label>Ширина рамки:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="0" max="20" oninput="updateRangeInput(this)" value="1">
                            <input type="number" min="0" max="20" name="appearance[border-width]" value="1">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Стиль: </label>
                        <div class="select-wrap">
                            <select name="appearance[style]">
                                <option value="solid" selected>Solid</option>
                                <option value="dotted">dotted</option>
                                <option value="dashed">Dashed</option>
                                <option value="double">double</option>
                                <option value="groove">groove</option>
                                <option value="ridge">ridge</option>
                                <option value="inset">inset</option>
                                <option value="outset">outset</option>
                            </select>
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет рамки: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="appearance[border-color]"
                                   value="#D8DAE7">
                        </div>
                    </div>
                </div>
                <div class="form-appearance-right-side col-1-2" title="Внешний вид формы">
                    <div class="width-100 px-label-wrap" bis_skin_checked="1" title="0 - для 100%">
                        <label>Ширина формы:<span class="px-label">px</span></label>
                        <div class="range" bis_skin_checked="1">
                            <input type="range" min="300" max="2000" oninput="updateRangeInput(this)" value="400">
                            <input type="number" min="300" max="2000" name="appearance[form-width]" value="400">
                        </div>
                    </div>
                    <div class="width-100">
                        <label>Цвет фона формы: </label>
                        <div class="color-input-wrap">
                            <input type="text" data-coloris="" class="coloris" name="form[background-color]"
                                   value="#F7F8FA">
                        </div>
                    </div>

                    <div class="width-100">
                        <label>Выравнивание формы: </label>
                        <div class="flex">
                            <label class="image-checkbox" title="Слева" for="formAlignLeft">
                                <input id="formAlignLeft" name="form[formAlign]" type="radio" value="left">
                                <img src="/template/admin/images/icons/text-align_left.svg">
                            </label>
                            <label class="image-checkbox" title="По центру" for="formAlignCenter">
                                <input id="formAlignCenter" name="form[formAlign]" type="radio" value="center" checked>
                                <img src="/template/admin/images/icons/text-align_center.svg">
                            </label>
                            <label class="image-checkbox" title="Справа" for="formAlignRight">
                                <input id="formAlignRight" name="form[formAlign]" type="radio" value="right">
                                <img src="/template/admin/images/icons/text-align_right.svg">
                            </label>
                        </div>
                    </div>

                </div>
                <?php } ?>

                <?php if ($show == 'showhtml') { ?>
                    <div class="col-1-1 mb-0"><h4>Предпросмотр</h4></div>
                    <div class="wrapperformpreview">
                        <?= $htmlform ?>
                    </div>
                    <div class="col-1-1 mb-0"><h4>Код для вставки</h4></div>
                    <textarea rows="20" cols="40" style="height: 500px;"><?= $htmlform ?></textarea>
                <?php } ?>

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
})
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