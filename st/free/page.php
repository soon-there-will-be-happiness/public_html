<?php require_once ("{$this->layouts_path}/head.php");?>
<!DOCTYPE html>
<html>
<?php  
defined('BILLINGMASTER') or die; 
//require_once ("{$this->layouts_path}/head.php");
$id=31;
$promo= null;
if(isset( $_GET['partner'])) $promo= $_GET['partner'];
$product = Product::getProductById($id);
$price = Price::getFinalPrice($id);
$setting = System::getSetting();
$metriks = !empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1 ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : null;
$date = time();
$name = $email = $phone = $surname = $patronymic = null;
$partner_id = !empty($_COOKIE['aff_billingmaster'])?$_COOKIE['aff_billingmaster']:null;
?>
<head>
<meta charset="utf-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <meta name="yandex-verification" content="3130095486568572" />
 --><!--metatextblock-->
<title>Пробный урок по профориентации программы "Кем стать?"</title>
<meta property="og:url" content="https://youngschool.ru/testlesson" />
<meta property="og:title" content="Пробный урок по профориентации программы &quot;Кем стать?&quot;" />
<meta property="og:description" content="" />
<meta property="og:type" content="website" />
<meta property="og:image" content="images/tild6331-6163-4462-b931-633838313032__-__resize__504x__heidi-sandstrom-1203.jpg" />
<!-- Подключаем UIkit CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/css/uikit.min.css" />
<!-- Подключаем UIkit JS -->
<script src="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.6.16/dist/js/uikit-icons.min.js"></script>
<link rel="canonical" href="https://youngschool.ru/testlesson">
<!--/metatextblock-->
<meta name="format-detection" content="telephone=no" />
<meta http-equiv="x-dns-prefetch-control" content="on">
<link rel="dns-prefetch" href="https://ws.tildacdn.com">
<link rel="dns-prefetch" href="https://fonts.tildacdn.com">
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link rel="apple-touch-icon" href="images/tild6237-3933-4330-b164-373830623535______.png">
<link rel="apple-touch-icon" sizes="76x76" href="images/tild6237-3933-4330-b164-373830623535______.png">
<link rel="apple-touch-icon" sizes="152x152" href="images/tild6237-3933-4330-b164-373830623535______.png">
<link rel="apple-touch-startup-image" href="images/tild6237-3933-4330-b164-373830623535______.png">
<meta name="msapplication-TileColor" content="#000000">
<meta name="msapplication-TileImage" content="images/tild3130-6564-4163-b634-643264396330______.png">
<!-- Assets -->
<script src="https://neo.tildacdn.com/js/tilda-fallback-1.0.min.js" async charset="utf-8"></script>
<link rel="stylesheet" href="css/tilda-grid-3.0.min.css" type="text/css" media="all" onerror="this.loaderr='y';"/>
<link rel="stylesheet" href="css/tilda-blocks-page33844683.min.css?t=1725305517" type="text/css" media="all" onerror="this.loaderr='y';" />
<link rel="stylesheet" href="css/tilda-animation-2.0.min.css" type="text/css" media="all" onerror="this.loaderr='y';" />

<link rel="stylesheet" href="css/tilda-cover-1.0.min.css" type="text/css" media="all" onerror="this.loaderr='y';" />
<script type="text/javascript">TildaFonts = ["427","429","433","435"];</script>
<script type="text/javascript" src="js/tilda-fonts.min.js" charset="utf-8" onerror="this.loaderr='y';">
</script>
<script nomodule src="js/tilda-polyfill-1.0.min.js" charset="utf-8">
</script>
<script type="text/javascript">function t_onReady(func) {
            if (document.readyState != 'loading') {
                func();
            } else {
                document.addEventListener('DOMContentLoaded', func);
            }
        }
        function t_onFuncLoad(funcName, okFunc, time) {
            if (typeof window[funcName] === 'function') {
                okFunc();
            } else {
                setTimeout(function() {
                    t_onFuncLoad(funcName, okFunc, time);
                },(time || 100));
            }
        }</script>
<script src="js/jquery-1.10.2.min.js" charset="utf-8" onerror="this.loaderr='y';">

</script> <script src="js/tilda-scripts-3.0.min.js" charset="utf-8" defer onerror="this.loaderr='y';"></script>
<script src="js/tilda-blocks-page33844683.min.js?t=1725305517" charset="utf-8" async onerror="this.loaderr='y';"></script>
<script src="js/lazyload-1.3.min.export.js" charset="utf-8" async onerror="this.loaderr='y';"></script>
<script src="js/tilda-animation-2.0.min.js" charset="utf-8" async onerror="this.loaderr='y';"></script>
<script src="js/tilda-cover-1.0.min.js" charset="utf-8" async onerror="this.loaderr='y';"></script>
<script src="js/tilda-events-1.0.min.js" charset="utf-8" async onerror="this.loaderr='y';"></script>

    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '663488660829171');
        fbq('track', 'PageView');
    </script>
            <style>
        /* Установим наивысший z-index для lightbox */
        .uk-open .uk-responsive-width data-uk-lightbox {
            position: fixed !important;
            z-index: 2147483647 !important; /* Убедитесь, что это наивысший z-index на странице */
        }
        
        body .uk-modal {
            position: fixed !important;
            z-index: 2147483647 !important; /* Самый высокий z-index */
        }


        /* Установим наивысший z-index для бэкдропа (фон lightbox) */
        .uk-lightbox {
            position: fixed !important;
            z-index: 2147483646 !important;
        }
        .t228__maincontainer {
            position: relative;
            z-index: 1;
        }
    </style>

    <noscript>
<img height="1" width="1" style="display:none"
                   src="https://www.facebook.com/tr?id=663488660829171&ev=PageView&noscript=1"
        />
</noscript>
    <!-- End Facebook Pixel Code -->



<!--    <style>
    .sber-a {
        background: #42cd42;
        color: #fff !important;
        padding: 10px 20px;
        border-radius: 10px;
        clear: both;
        display: inline-block;
        margin: 10px;
        font-family: 'FuturaPT',Arial,sans-serif;
        white-space: nowrap;
        vertical-align: middle;
        font-weight: 700;
        font-size: 15px;
    }
</style> -->
    <script>
        /*jQuery(document).ready(function($) {



        }

        if($('#rec618297354 .t599__btn').length > 0) {

            $('#rec618297354 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec498427511 .t599__btn').length > 0) {

            $('#rec498427511 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec490578302 .t599__btn').length > 0) {

            $('#rec490578302 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec169624609 .t599__btn').length > 0) {

            $('#rec169624609 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec381795431 .t599__btn').length > 0) {

            $('#rec381795431 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec388201744 .t599__btn').length > 0) {

            $('#rec388201744 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec388201108 .t599__btn').length > 0) {

            $('#rec388201108 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec388200720 .t599__btn').length > 0) {

            $('#rec388200720 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec388232035 .t599__btn').length > 0) {

            $('#rec388232035 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec388227723 .t599__btn').length > 0) {

            $('#rec388227723 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec338977359 .t599__btn').length > 0) {

            $('#rec338977359 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }

        if($('#rec338974718 .t599__btn').length > 0) {

            $('#rec338974718 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');


        }
        // бой https://youngschool.ru/nastavn
        if($('#rec342104316 .t599__btn').length > 0) {

            $('#rec342104316 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // тест https://youngschool.ru/ps2
        //if($('#rec312112271 .t599__btn').length > 0) {

        //$('#rec312112271 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку</a>'); 

        //}
        // тест https://youngschool.ru/tg_blog  



        // тест https://youngschool.ru/ps4_new
        if($('#rec339037728 .t599__btn').length > 0) {

            $('#rec339037728 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/ps4
        if($('#rec331071192 .t599__btn').length > 0) {

            $('#rec331071192 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/programm_nastav
        //     if($('#rec341421276 .t599__btn').length > 0) {

        //      $('#rec341421276 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>'); 

        //    }
        // бой https://youngschool.ru/pr_dg2
        if($('#rec338955178 .t599__btn').length > 0) {

            $('#rec338955178 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/pr_dg1
        if($('#rec346302121 .t599__btn').length > 0) {

            $('#rec346302121 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/start
        if($('#rec342514618 .t599__content').length > 0) {

            $('#rec342514618 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/mo
        if($('#rec342518861 .t599__content').length > 0) {

            $('#rec342518861 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/py
        if($('#rec342519988 .t599__content').length > 0) {

            $('#rec342519988 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/mo2
        if($('#rec346006481 .t599__content').length > 0) {

            $('#rec346006481 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/mo3
        if($('#rec346017085 .t599__content').length > 0) {

            $('#rec346017085 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/mo4
        if($('#rec346017587 .t599__content').length > 0) {

            $('#rec346017587 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/mo5
        if($('#rec346018828 .t599__content').length > 0) {

            $('#rec346018828 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/p_mo
        if($('#rec360353661 .t599__content').length > 0) {

            $('#rec360353661 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        // бой https://youngschool.ru/p_mo
        if($('#rec361171472 .t599__content').length > 0) {

            $('#rec361171472 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

        }
        https://youngschool.ru/dgb5
            if($('#rec362356233 .t599__content').length > 0) {

                $('#rec362356233 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку от Сбер</a>');

            }
        https://youngschool.ru/pr_ps
            if($('#rec381553218 .t599__btn').length > 0) {

                $('#rec381553218 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

            }
        https://youngschool.ru/psycholog
            if($('#rec303476956 .t599__btn').length > 0) {

                $('#rec303476956 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

            }
        // https://youngschool.ru/mo_blackfriday
        if($('#rec382719522 .t599__content').length > 0) {

            $('#rec382719522 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

        }
        // https://youngschool.ru/start_after1000
        if($('#rec390416467 .t599__content').length > 0) {

            $('#rec390416467 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

        }
        // https://youngschool.ru/py_after1000
        if($('#rec390419227 .t599__content').length > 0) {

            $('#rec390419227 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

        }
        // https://youngschool.ru/prof_skidka
        if($('#rec396082060 .t599__content').length > 0) {

            $('#rec396082060 .t599__content').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

        }
        // https://youngschool.ru/ny_2022
        if($('#rec396897921 .t599__btn').length > 0) {

            $('#rec396897921 .t599__btn').after('<a href="javascript://" class="sber-a sberbank_button">Купить в рассрочку Сбер</a>');

        }*/


        // глобальные переменные для запомининия выбранного товара длярасрочки
        var sber_name = '';
        var sber_price = '';

        //if(window.location.href.indexOf('test') !== -1) {

        function public_form() {

            var style = '<style>.sber-popup .t702__wrapper {padding: 50px;}' +
                '.sber-popup .t-input-block {margin: 10px 0}' +
                '.sber-popup .t-form__submit {margin: 10px 0}' +
                '.sber-popup .t-form .t-submit {position: relative; float: right; margin: 0 0 30px 0;}' +
                '.sber-popup input.error {border: 1px solid red !important;}' +
                '.sber-popup .t-popup__close-icon {background: #000;    border-radius: 50%;    padding: 3px;}' +
                '</style>';

            var html_form = '<div class="sber-popup t-popup t-popup_show" data-tooltip-hook="#popup:neuhodi" style="display: block;">' +
                '<div class="t-popup__container t-width t-width_6 t-popup__container-animated">' +
                '<div class="t702__wrapper">' +
                '  <div class="t-popup__close">' +
                '    <div class="t-popup__close-wrapper">' +
                '<svg class="t-popup__close-icon" width="23px" height="23px" viewBox="0 0 23 23" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' +
                '<g stroke="none" stroke-width="1" fill="#fff" fill-rule="evenodd">' +
                '          <rect transform="translate(11.313708, 11.313708) rotate(-45.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30">
</rect>' +
                '          <rect transform="translate(11.313708, 11.313708) rotate(-315.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30">
</rect>' +
                '        </g>' +
                '      </svg>' +
                '    </div>' +
                '  </div>' +
                '      <div class="t702__text-wrapper t-align_center">' +
                '        <div class="t702__title t-title t-title_xxs" style="">Купить в рассрочку от Сбер</div>' +
                '        <div class="t702__descr t-descr t-descr_xs" style="">Оставьте свои контакты для покупки</div>' +
                '      </div>' +
                '      <form action="" method="POST" class="t-form js-form-proccess t-form_inputs-total_3 ">' +
                '        <div class="js-successbox t-form__successbox t-text t-text_md" style="display:none;">
</div>' +
                '        <div class="t-form__inputsbox">' +
                '          <div class="t-input-group t-input-group_nm" data-input-lid="1495810354468">' +
                '            <div class="t-input-block">' +
                '              <input type="text" autocomplete="name" name="Name" class="t-input js-tilda-rule " value="" placeholder="Имя" data-tilda-req="1" data-tilda-rule="name" style="color:#000000; border:1px solid #c9c9c9; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">' +
                '              <div class="t-input-error">
</div>' +
                '            </div>' +
                '          </div>' +
                '          <div class="t-input-group t-input-group_ph" data-input-lid="1495810359387">' +
                '            <div class="t-input-block">' +
                '              <input type="tel" autocomplete="tel" name="Phone" class="t-input js-tilda-rule js-tilda-mask " value="" placeholder="Телефон" data-tilda-req="1" data-tilda-rule="phone" pattern="[0-9]*" data-tilda-mask="+7 (999) 999 99 99" style="color:#000000; border:1px solid #c9c9c9; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">' +
                '              <div class="t-input-error">
</div>' +
                '            </div>' +
                '          </div>' +
                '          <div class="t-input-group t-input-group_cb" data-input-lid="1549980493619">' +
                '            <div class="t-input-block">' +
                '              <label class="t-checkbox__control t-text t-text_xs" style="">' +
                '                <input type="checkbox" name="Checkbox" value="yes" class="t-checkbox js-tilda-rule" checked="" data-tilda-req="1">' +
                '                <div class="t-checkbox__indicator">
</div>Я согласен с политикой конфиденциальности сайта</label>' +
                '              <div class="t-input-error">
</div>' +
                '            </div>' +
                '          </div>' +
                '          <div class="t-form__submit">' +
                '            <button type="button" class="t-submit" style="color:#ffffff;background-color:#ff9b05;border-radius:2px; -moz-border-radius:2px; -webkit-border-radius:2px;">Купить</button>' +
                '          </div>' +
                '        </div>' +
                '      </form>' +
                '    </div>' +
                '  </div>' +
                '</div>';

            $('body').append(style);
            $('body').append(html_form);
        }
        //}


        /*$(document).on('click', '.sber-popup .t-popup__close-icon', function(e) {

            e.stopPropagation();

            $('.sber-popup').remove();

        });*/


        function ValidPhone(myPhone) {
            var re = /^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/;
            var valid = re.test(myPhone);
            if (valid) return true; else return false;
        }

       /* $(document).on('click', '.sber-popup .t-submit', function() {

            var phone = $('.sber-popup [name="Phone"]').val();
            var username = $('.sber-popup [name="Name"]').val();

            $('.sber-popup [name="Phone"], .sber-popup [name="Name"]').removeClass('error')

            if(phone.length == 0 || !ValidPhone(phone)) {
                $('.sber-popup [name="Phone"]').addClass('error')
            }
            if(username.length == 0) {
                $('.sber-popup [name="Name"]').addClass('error')
            }

            if(username.length == 0 || phone.length == 0 || !ValidPhone(phone)) {

                return false;
            }

            var returnUrl = window.location.href; // возврат после оформления на страницу товара

            var orderBundle = {cartItems: {items: [{positionId: 1, name: sber_name, itemDetails: {}, quantity: { "value": 1, "measure": ""}, itemAmount: sber_price, itemPrice: sber_price}]}, installments: {productType: 'CREDIT', productID: 10}
            };


            window.location.href = "http://sber.youngschool.ru/curl_GET.php?price=" + sber_price + "&title=" + sber_name + "&phone=" + phone + "&username=" + username + "&returnUrl=" + returnUrl;
        });*/

        /*$(document).on('click', '.sberbank_button', function(e) {

            e.stopPropagation();
            e.preventDefault();

            var date = new Date();
            var idOrder = (date.getTime() / 100).toFixed(0); // ID заказа, временная метка
            var returnUrl = window.location.href; // возврат после оформления на страницу товара

            var pp = $(this).parent().find('.t599__price').clone();
            $('span, s', pp).remove();
            var price = $(pp).text().replace(/[^\d]+/g,"") * 100; // стоимость товара
            var name = $(this).parent().find('.t599__title').text().trim();

            var orderBundle = {cartItems: {items: [{positionId: 1, name: name, itemDetails: {}, quantity: { "value": 1, "measure": ""}, itemAmount: price, itemPrice: price}]}, installments: {productType: 'CREDIT', productID: 10}
            };

            sber_price = price;
            sber_name = name;
            public_form();*/

            //window.location.href = "http://sber.youngschool.ru/curl_GET.php?price=" + price + "&title=" + name + "&returnUrl=" + returnUrl;


            /*$.ajax({
              url: "http://sber.youngschool.ru/curl.php?v1",
                      data: 'amount=' + price + '&currency=643&language=ru&orderNumber=' + idOrder + '&returnUrl=' + returnUrl + 
                '&jsonParams={"phone":""}&sessionTimeoutSecs=86400' + 
                '&orderBundle=' + JSON.stringify(orderBundle),
              type: 'post',
                      dataType: 'json',
                      headers: { 'Access-Control-Request-Headers' : 'x-requested-with'},
                      success: function(response){ // Получаем ответ содержащий ссылку на форму заявки
                  if(typeof response.formUrl !== 'undefined') {
                      window.location.href = response.formUrl; 
                  }
                  else {
                          alert('Сервис временно недоступен.');
                  }
    
              },
              error: function(response){ // Получаем ответ содержащий ссылку на форму заявки
                  
                          alert('Сервис временно недоступен.');
                  
              }
            });*/
        });


        });
    </script>
    
</script>
    <script type="text/javascript">window.dataLayer = window.dataLayer || [];</script>
<!-- Google Tag Manager -->
<script type="text/javascript">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-N5MWNPV');</script>
<!-- End Google Tag Manager -->
<!-- Facebook Pixel Code -->
<script type="text/javascript" data-tilda-cookie-type="advertising">setTimeout(function(){!function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.agent='pltilda';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '402237957503335');
            fbq('track', 'PageView');
        }, 2000);</script>
<!-- End Facebook Pixel Code -->
<!-- VK Pixel Code -->
<!-- <script type="text/javascript" data-tilda-cookie-type="advertising">setTimeout(function(){!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src="https://vk.com/js/api/openapi.js?161",t.onload=function(){VK.Retargeting.Init("VK-RTRG-974419-2ZkvK"),VK.Retargeting.Hit()},document.head.appendChild(t)}();
        }, 2000);</script> -->
<!-- End VK Pixel Code
<script type="text/javascript">(function () {
            if((/bot|google|yandex|baidu|bing|msn|duckduckbot|teoma|slurp|crawler|spider|robot|crawling|facebook/i.test(navigator.userAgent))===false && typeof(sessionStorage)!='undefined' && sessionStorage.getItem('visited')!=='y' && document.visibilityState){
                var style=document.createElement('style');
                style.type='text/css';
                style.innerHTML='@media screen and (min-width: 980px) {.t-records {opacity: 0;}.t-records_animated {-webkit-transition: opacity ease-in-out .2s;-moz-transition: opacity ease-in-out .2s;-o-transition: opacity ease-in-out .2s;transition: opacity ease-in-out .2s;}.t-records.t-records_visible {opacity: 1;}}';
                document.getElementsByTagName('head')[0].appendChild(style);
                function t_setvisRecs(){
                    var alr=document.querySelectorAll('.t-records');
                    Array.prototype.forEach.call(alr, function(el) {
                        el.classList.add("t-records_animated");
                    });
                    setTimeout(function () {
                        Array.prototype.forEach.call(alr, function(el) {
                            el.classList.add("t-records_visible");
                        });
                        sessionStorage.setItem("visited", "y");
                    }, 400);
                }
                document.addEventListener('DOMContentLoaded', t_setvisRecs);
            }
        })();</script> -->
        <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/st/free/css/style.css'); ?>">
</head>
<body class="t-body" style="margin:0;">
    
<!--allrecords-->
<div id="allrecords" data-tilda-export="yes" class="t-records" data-hook="blocks-collection-content-node" data-tilda-project-id="227222" data-tilda-page-id="33844683" data-tilda-page-alias="testlesson" data-tilda-formskey="563b98246626f60e23e5fa77537148c9" data-tilda-lazy="yes" data-tilda-root-zone="com" data-tilda-project-headcode="yes" data-tilda-ts="y">
<div id="rec546920578" class="r t-rec" style=" " data-animationappear="off" data-record-type="204" >
        <!-- cover -->
<div class="t-cover" id="recorddiv546920578"bgimgfield="img"style="height:100vh;background-image:url('images/tild6331-6163-4462-b931-633838313032__-__resize__20x__heidi-sandstrom-1203.jpg');">
<div class="t-cover__carrier" id="coverCarry546920578"data-content-cover-id="546920578"data-content-cover-bg="images/tild6331-6163-4462-b931-633838313032__heidi-sandstrom-1203.jpg"data-display-changed="true"data-content-cover-height="100vh"data-content-cover-parallax=""style="height:100vh;background-attachment:scroll; "itemscope itemtype="http://schema.org/ImageObject">
<meta itemprop="image" content="images/tild6331-6163-4462-b931-633838313032__heidi-sandstrom-1203.jpg">
</div>
<div class="t-cover__filter" style="height:100vh;background-image: -moz-linear-gradient(top, rgba(0,0,0,0.0), rgba(0,0,0,0.0));background-image: -webkit-linear-gradient(top, rgba(0,0,0,0.0), rgba(0,0,0,0.0));background-image: -o-linear-gradient(top, rgba(0,0,0,0.0), rgba(0,0,0,0.0));background-image: -ms-linear-gradient(top, rgba(0,0,0,0.0), rgba(0,0,0,0.0));background-image: linear-gradient(top, rgba(0,0,0,0.0), rgba(0,0,0,0.0));filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#fe000000', endColorstr='#fe000000');">
</div>
<div class="t-container">
<div class="t-col t-col_8">
<div class="t-cover__wrapper t-valign_middle" style="height:100vh; position: relative;z-index: 1;">
<div class="t181">
                            <div data-hook-content="covercontent">
<div class="t181__wrapper">
<div class="t181__title t-title t-title_md" field="title">
<div class="t_title" style="font-size: 62px; line-height: 76px;" data-customstyle="yes">
<p style="text-align: left;">
<strong class='title' style="font-family: Arial; font-size: 62px;">Попробуй на практике бесплатно урок по профориентации программы "Кем Стать?"</strong>
<strong style="font-family: Arial;"> </strong>
</p>
</div>
</div>
<div class="t181__descr t-descr t-descr_lg " field="descr">
<span style="color: rgb(0, 0, 0); font-weight: 400;">Профориентационный онлайн-курс «Кем стать?»</span>
<br />
<span style="color: rgb(0, 0, 0); font-weight: 400;">для школьников от 12 до 18 лет</span>
</div>
<div class="t181__button-wrapper" style="margin-top:30px;">
<div class="t-btnwrapper">
<button class="buy-btn" style="width: 200px;">З А П И С А Т Ь С Я</button>
<div id="popup" class="popup hidden">
    <div class="popup-content">
        <span class="close-btn">&times;</span>

        <h4 class="pop_up_title">Форма регистрации на пробный урок на платформе. Для получения доступа к уроку заполните данные о себе.</h4>
        <p class="pop_up_subtitle">Продукт: <?=$product['product_name'];?>
    </br>
        Стоимость: <s><?=$price['real_price']?> ₽</s> бесценно </p>
        <form class="form" action="<?=$setting['script_url']?>/buy/<?=$id?>" method="POST" <?=$metriks;?> id="form_order_buy">
                                    <label for="first_name" id="label_first_name">Имя<span style="color: red;">*</span></label>
                                    <input class="input-field" type="text" id="first_name" name="name" value="<?= isset($name) ? $name : ''; ?>"
                                    placeholder="Введите ваше имя" required>
                                    <label for="email" id="label_email">Электронная почта<span style="color: red;">*</span></label>
                                    <?php if($this->settings['email_protection']):?>
                                    <script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCI="));</script>
                                    <input class="input-field" type="email" id="email" name="email" value="<?=$user_email ?? $email?>" placeholder="Введите вашу почту" required>
                                    <?php else:?>
                                    <input class="input-field" type="email" id="email" name="email" value="" placeholder="Введите вашу почту" required>
                                    <?php endif;?>
                                    <label for="phone" id="label_phone">Телефон<span style="color: red;">*</span></label>
                                    <input class="input-field" type="tel" id="phone_inp" name="phone" maxlength="12" placeholder="912 333-33-33" required>
                                    <span class="text-hint" onclick="toggleFields()">Вы также можете указать никнейм телеграм для оперативной связи</span>
                                    <label for="telegram" id="label_telegram" style="display: none;">Телеграм через @</label>
                                    <input class="input-field" type="text" id="telegram" name="telegram" placeholder="@ваш_ник" style="display: none;">
                                    <label>
                                        <input type="checkbox" id="agreement" name="agreement" required> 
                                        <!--politika-->
                                        <?php if(!isset($_SESSION['org'])):?>
                                                <span class="politics"><?=System::Lang('LINK_CONFIRMED');?></span>
                                        <?php endif;?>
                                    </label>
                                    <input type="hidden" name="time" value="<?=$date;?>">
                                    <input type="hidden" name="token" value="<?=md5($id.'s+m'.$date);?>">
                                    <input type="hidden" name="vk_id" value="<?=@$_REQUEST['vk_id'] ?>">
                                    <input type="hidden" name="promo" value="<?=$promo;?>">
                                    <?php if (isset($_REQUEST['pid'])): ?>
                                        <input type="hidden" name="pid" value="<?=$_REQUEST['pid'] ?? "" ?>">
                                    <?php endif; ?>
                                    <button id ="buy" class="pay" name="buy" type="submit">З А П И С А Т Ь С Я</button>
        </form>
    </div>
</div>
<script>
document.getElementById('buy').addEventListener('click', function() {
    const form = document.getElementById('form_order_buy');
    if (form.checkValidity()) {
        <?php $telegram = TelegramProduct::searchByProductId($partner_id,$id);
        if($telegram != false): ?>
        window.open('<?php echo $telegram['telegram']?>', '_blank');
        <?php endif; ?>
    } else {
        //alert('Пожалуйста, заполните все обязательные поля.');
        pass;
    }
});
</script> 


 
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<script src="../kemstat/js/script.js"></script>
<!-- <script>
    // Выполнение функции updateLabels после загрузки внешнего скрипта
    window.onload = function() {
        // Set default labels based on the initial selection (Родитель by default)
        updateLabels('parent');
    };
</script> -->

<style>#rec546920578 .t-btn[data-btneffects-first],
            #rec546920578 .t-btn[data-btneffects-second],
            #rec546920578 .t-btn[data-btneffects-third],
            #rec546920578 .t-submit[data-btneffects-first],
            #rec546920578 .t-submit[data-btneffects-second],
            #rec546920578 .t-submit[data-btneffects-third] {
                position: relative;
                overflow: hidden;
                isolation: isolate;
            }
            #rec546920578 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects,
            #rec546920578 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                -webkit-transform: translateX(-85px);
                -ms-transform: translateX(-85px);
                transform: translateX(-85px);
                -webkit-animation-name: flash;
                animation-name: flash;
                -webkit-animation-duration: 3s;
                animation-duration: 3s;
                -webkit-animation-timing-function: linear;
                animation-timing-function: linear;
                -webkit-animation-iteration-count: infinite;
                animation-iteration-count: infinite;
            }
            #rec546920578 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_md,
            #rec546920578 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_md {
                -webkit-animation-name: flash-md;
                animation-name: flash-md;
            }
            #rec546920578 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_lg,
            #rec546920578 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_wrap-effects_lg {
                -webkit-animation-name: flash-lg;
                animation-name: flash-lg;
            }
            #rec546920578 .t-btn[data-btneffects-first="btneffects-flash"] .t-btn_effects,
            #rec546920578 .t-submit[data-btneffects-first="btneffects-flash"] .t-btn_effects {
                background: -webkit-gradient(linear, left top, right top, from(rgba(255, 255, 255, .1)), to(rgba(255, 255, 255, .4)));
                background: -webkit-linear-gradient(left, rgba(255, 255, 255, .1), rgba(255, 255, 255, .4));
                background: -o-linear-gradient(left, rgba(255, 255, 255, .1), rgba(255, 255, 255, .4));
                background: linear-gradient(90deg, rgba(255, 255, 255, .1), rgba(255, 255, 255, .4));
                width: 45px;
                height: 100%;
                position: absolute;
                top: 0;
                left: 30px;
                -webkit-transform: skewX(-45deg);
                -ms-transform: skewX(-45deg);
                transform: skewX(-45deg);
            }
            @-webkit-keyframes flash {
                20% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }
            @keyframes flash {
                20% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }
            @-webkit-keyframes flash-md {
                30% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }
            @keyframes flash-md {
                30% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }
            @-webkit-keyframes flash-lg {
                40% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }
            @keyframes flash-lg {
                40% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
                100% {
                    -webkit-transform: translateX(100%);
                    transform: translateX(100%);
                }
            }</style>
<script>
    t_onReady(function() {
                var rec = document.getElementById('rec546920578');
                if (!rec) return;
                var firstButton = rec.querySelectorAll('.t-btn[data-btneffects-first], .t-submit[data-btneffects-first]');
                Array.prototype.forEach.call(firstButton, function (button) {
                    button.insertAdjacentHTML('beforeend', '<div class="t-btn_wrap-effects">
                    <div class="t-btn_effects">
                    </div>
                    </div>');
                    var buttonEffect = button.querySelector('.t-btn_wrap-effects');
                    if (button.offsetWidth > 230) {
                        buttonEffect.classList.add('t-btn_wrap-effects_md');
                    }
                    if (button.offsetWidth > 750) {
                        buttonEffect.classList.remove('t-btn_wrap-effects_md');
                        buttonEffect.classList.add('t-btn_wrap-effects_lg');
                    }
                });
            });
</script>
<style>@media (hover: hover), (min-width: 0\0) {#rec546920578 .t-btn:not(.t-animate_no-hover):hover {background-color: #e68a00 !important;}#rec546920578 .t-btn:not(.t-animate_no-hover):focus-visible {background-color: #e68a00 !important;}#rec546920578 .t-btn:not(.t-animate_no-hover) {transition-property: background-color, color, border-color, box-shadow;transition-duration: 0.2s;transition-timing-function: ease-in-out;}}</style> <style> #rec546920578 .t181__title { color: #000000; } #rec546920578 .t181__descr { opacity: 1; }</style>
</div>
</div>

<!-- Stat -->
<!-- Google Tag Manager (noscript) -->
<noscript>
<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N5MWNPV" height="0" width="0" style="display:none;visibility:hidden">
</iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->
<!-- FB Pixel code (noscript) -->
<noscript>
<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=402237957503335&ev=PageView&agent=pltilda&noscript=1"/>
</noscript>
<!-- End FB Pixel code (noscript) -->
<!-- VK Pixel code (noscript) -->
<noscript>
<img src="https://vk.com/rtrg?p=VK-RTRG-974419-2ZkvK" style="position:fixed; left:-999px;" alt=""/>
</noscript>
<!-- End VK Pixel code (noscript) -->
</body>
<?require_once ("{$this->layouts_path}/tech-footer.php");?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#pay') {
            document.getElementById('popup').classList.add('show');
        }
    });
</script>
</html>