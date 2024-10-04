<?php defined('BILLINGMASTER') or die; 


class adminProductFormController extends AdminBase {

    public $path = '/load/forms/';
    public $uniqueid;
    public $ids;

    public function __construct(){
        $this->uniqueid = uniqid();
    }

    public function actionIndex() {
        $acl = self::checkAdmin();
        if(!isset($acl['show_conditions'])) {
            header("Location: /admin");
        }
        $name = $_SESSION['admin_name'];

        //TODO разобрать по действиям
        //$_post пустой - показывать только выбор продукта
        //$_post только айди продуктов - показывать форму полностью
        //$_post полный - сформировать форму
        //$_POST['saveform'] - сохранить форму
        if (isset($_POST['saveform'])) {
            $formdata = $_POST;
            $generated = $this->generateFullForm($formdata);
            $formdata['embedCode'] = $generated['embedCode'];
            $formdata['embedCode'] = $generated['embedCode'];
            $formdata['filename'] = $generated['filename'];
            return $this->saveForm($formdata);
        }
        if (!empty($_POST)) {
            //если было отправлена форма полностью, то сформировать результат
            if (count($_POST) > 8) {
                $formdata = $_POST;
                $htmlform = $this->processForm($formdata);
                $show = 'showhtml';
            } else {
                $show = 'showAll';
                $data = $_POST['data'];
                $products = Product::getProductsByIds($_POST['products_id']);
            }
        } else {//показывать только выбор продукта
            $show = 'showOnlySelectProduct';
            $products = Product::getProductListOnlySelect();
        }
        require_once (ROOT . '/template/admin/views/products/form_generator.php');
        return true;
    }


    public function addAtribute($html, $atribute, $value) {
        $html .= $atribute.':'.$value.';';
        return $html;
    }
    public function addBorder($html, $side, $width, $style, $color) {
        $html .= 'border-'.$side.':'.$width.'px '.$style.' '.$color.';';
        return $html;
    }
    public function addPadding($html, $side, $width) {
        $html .= 'padding-'.$side.':'.$width.'px;';
        return $html;
    }
    public function addFormat($format) {
        $style = '';
        if (isset($format)) {
            if (!empty($format["font_bold"])) {
                $style .= 'font-weight:bold;';
            }
            if (!empty($format["font_under_line"])) {
                $style .= 'text-decoration:underline;';
            }
            if (!empty($format["font_italic"])) {
                $style .= 'font-style: italic;';
            }
            if (!empty($format["font_uppercase"])) {
                $style .= 'text-transform: uppercase;';
            }
        }
        return $style;
    }

    public function processForm($formdata, $id = null) {

        //Генерируем стили
        $html = '<style>.productformwrapper * {box-sizing: border-box;} .productformwrapper{background-color:'.$formdata["form"]["background-color"].';font-family:Sans-Serif;box-sizing:border-box;display:flex;justify-content:center;flex-direction:column;width:'.$formdata["appearance"]["form-width"].'px;font-size:14px !important;}
        #productswrapper select{width:100%;padding:0 21px !important;}
        .productform{margin-top:16px;padding:0 8px;}.radiowrapper{display:flex;align-items:center; margin-bottom: 15px; justify-content: space-between;}.radiowrapper input{width:5%;margin:0 10px 0 0;}.radiowrapper .prodname{width:75%; font-size:'.$formdata["product"]["font-size"].'px; color: '.$formdata["product"]["text-color"].';}.radiowrapper .price{width:20%; text-align: right; font-size: '.$formdata["product"]["price_font_size"].'px;color:'.$formdata["product"]["price_color"].';}
        .inputwrapper{display:flex;flex-direction:column;margin-top:20px;align-items:center;justify-content:space-between}.oldpricetext{text-decoration:line-through;}
        .inputwrapper .checkbox{display:flex;align-items: center;}.inputwrapper input[type=checkbox]{display:inline;width:auto;margin-right:8px;margin-bottom: 0;}.productformwrapper a {color: #000;}.productformwrapper h3,.productformwrapper h5 {margin:0; padding:0; margin-bottom:10px;}.productformwrapper h5 {font-weight:normal;margin-bottom:35px;}
        ';
        //Контейнер
        $html .= '.wrapperform{display:flex;width:100%;}';
        switch ($formdata['form']['formAlign']) {
            case 'left':
                $html .= '.wrapperform{justify-content: flex-start;}';
                break;
            case 'right':
                $html .= '.wrapperform{justify-content: flex-end;}';
                break;
            default:
                $html .= '.wrapperform{justify-content: center}';
                break;
        }

        //Лейблы
        if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
            $html .= '.inputwrapper label{width:100%; font-size:' . $formdata["fields"]["label_font_size"] . 'px; color:'.$formdata["fields"]["label_color"].';margin-bottom:5px !important;}';
        } else {
            $html .= '.inputwrapper label{}';
        }

        //Плейсхолдер
        if (isset($formdata["fields"]["placeholder"]) && $formdata["fields"]["placeholder"] == 1) {
            $html .= '.inputwrapper input::placeholder{font-size:' . $formdata["fields"]["placeholder_font_size"] . 'px; color:'.$formdata["fields"]["placeholder_color"].';}';
        } else {
            $html .= '.inputwrapper input::placeholder{visibility:hidden;}';
        }

        //главный контейнер
        $html .= '.productformwrapper{padding: '.$formdata["appearance"]["inner-padding"]["top"].'px '.$formdata["appearance"]["inner-padding"]["right"].'px '.$formdata["appearance"]["inner-padding"]["bottom"].'px '.$formdata["appearance"]["inner-padding"]["left"].'px;';
        if (isset($formdata["appearance"]["borders"])) {
            if (in_array('top', $formdata["appearance"]["borders"])) {
                $html = $this->addBorder($html, 'top', $formdata["appearance"]["border-width"], $formdata["appearance"]["style"], $formdata["appearance"]["border-color"]);
            }
            if (in_array('bottom', $formdata["appearance"]["borders"])) {
                $html = $this->addBorder($html, 'bottom', $formdata["appearance"]["border-width"], $formdata["appearance"]["style"], $formdata["appearance"]["border-color"]);
            }
            if (in_array('left', $formdata["appearance"]["borders"])) {
                $html = $this->addBorder($html, 'left', $formdata["appearance"]["border-width"], $formdata["appearance"]["style"], $formdata["appearance"]["border-color"]);
            }
            if (in_array('right', $formdata["appearance"]["borders"])) {
                $html = $this->addBorder($html, 'right', $formdata["appearance"]["border-width"], $formdata["appearance"]["style"], $formdata["appearance"]["border-color"]);
            }
        }
        $html .= "border-radius: 10px;}";
        /** СТИЛИ ИНТУТОВ и select */

        $html .= '.inputwrapper input, select{border-radius:'.$formdata["fields"]["border-radius"].'px;background-color:#fff;';
            //Рамка
        if (isset($formdata["fields"]["borders"])) {
            if (in_array('top', $formdata["fields"]["borders"])) {
                $html = $this->addBorder($html, 'top', $formdata["fields"]["width_border"], $formdata["fields"]["style"], $formdata["fields"]["border-color"]);
            }
            if (in_array('bottom', $formdata["fields"]["borders"])) {
                $html = $this->addBorder($html, 'bottom', $formdata["fields"]["width_border"], $formdata["fields"]["style"], $formdata["fields"]["border-color"]);
            }
            if (in_array('left', $formdata["fields"]["borders"])) {
                $html = $this->addBorder($html, 'left', $formdata["fields"]["width_border"], $formdata["fields"]["style"], $formdata["fields"]["border-color"]);
            }
            if (in_array('right', $formdata["fields"]["borders"])) {
                $html = $this->addBorder($html, 'right', $formdata["fields"]["width_border"], $formdata["fields"]["style"], $formdata["fields"]["border-color"]);
            }
        }
        $html .= '
        height:'.$formdata["fields"]["height"].'px;
        padding:16px;width:100%;display:block;
        font-size:'.$formdata["fields"]["font-size"].'px;
        margin-bottom:20px}';
        $html .= '.inputwrapper input:focus, select:focus{outline:none;border: 1px solid #BABDD1;}';



        /** Кнопка submit:normal */
        $html .= '.inputwrapper input[type="submit"]{margin-top:20px;transition: 0.2s;';
        if (isset($formdata['btn'])) {
            isset($formdata['btn']['btn-background']) ? $html .= 'background-color:' . $formdata['btn']['btn-background'] . ';' : $html .= 'background-color:#e73131;';
            isset($formdata['btn']['text-color']) ? $html .= 'color:' . $formdata['btn']['text-color'] . ';'                    : $html .= 'color:#000;';
            isset($formdata['btn']['border-radius']) ? $html .= 'border-radius:' . $formdata['btn']['border-radius'] . 'px;'    : $html .= 'border-radius:8px;';
            $html .= 'cursor: pointer;';
        }
        //Borders submit
        if (isset($formdata["btn"]["borders"])) {
            if (in_array('top', $formdata["btn"]["borders"])) {
                $html = $this->addBorder($html, 'top', $formdata["btn"]["border-width"], $formdata["btn"]["border-style"], $formdata["btn"]["border-color"]);
            }
            if (in_array('bottom', $formdata["btn"]["borders"])) {
                $html = $this->addBorder($html, 'bottom', $formdata["btn"]["border-width"], $formdata["btn"]["border-style"], $formdata["btn"]["border-color"]);
            }
            if (in_array('left', $formdata["btn"]["borders"])) {
                $html = $this->addBorder($html, 'left', $formdata["btn"]["border-width"], $formdata["btn"]["border-style"], $formdata["btn"]["border-color"]);
            }
            if (in_array('right', $formdata["btn"]["borders"])) {
                $html = $this->addBorder($html, 'right', $formdata["btn"]["border-width"], $formdata["btn"]["border-style"], $formdata["btn"]["border-color"]);
            }
        }
        //format submit
        $html .= $this->addFormat($formdata["btn"]["format"]);
        //padding submit
        if (isset($formdata["btn"]["inner-padding"])) {
            if (isset($formdata["btn"]["inner-padding"]["top"])) {
                $html = $this->addPadding($html, 'top', $formdata["btn"]["inner-padding"]["top"]);
            }
            if (isset($formdata["btn"]["inner-padding"]["right"])) {
                $html = $this->addPadding($html, 'right', $formdata["btn"]["inner-padding"]["right"]);
            }
            if (isset($formdata["btn"]["inner-padding"]["bottom"])) {
                $html = $this->addPadding($html, 'bottom', $formdata["btn"]["inner-padding"]["bottom"]);
            }
            if (isset($formdata["btn"]["inner-padding"]["left"])) {
                $html = $this->addPadding($html, 'left', $formdata["btn"]["inner-padding"]["left"]);
            }
        }
        $html .= 'width:'.$formdata["btn"]["btn-width"].';font-size:'.$formdata["btn"]["font-size"].'px;
        letter-spacing:'.$formdata["btn"]["letter-spacing"].'px;';
        $html .= 'box-shadow: '.$formdata["btn"]["shadow-horiz"].'px '.$formdata["btn"]["shadow-vertic"].'px '.$formdata["btn"]["shadow-blur"].'px '.$formdata["btn"]["shadow-spread"].'px '.$formdata["btn"]["shadow-color"].';';
        $html .= 'text-align:'.$formdata["btn"]["align"].';height:auto;}';


        /** Кнопка submit:hover */
        $html .= '.inputwrapper input[type="submit"]:hover{';
        if (isset($formdata['btn']['hover'])) {
            isset($formdata['btn']['hover']['btn-background']) ? $html .= 'background-color:' . $formdata['btn']['hover']['btn-background'] . ';' : $html .= 'background-color:#e73131;';
            isset($formdata['btn']['hover']['text-color']) ? $html .= 'color:' . $formdata['btn']['hover']['text-color'] . ';'                    : $html .= 'color:#000;';
            isset($formdata['btn']['hover']['border-radius']) ? $html .= 'border-radius:' . $formdata['btn']['hover']['border-radius'] . 'px;'    : $html .= 'border-radius:8px;';
            $html .= 'cursor: pointer;';
        }
        //Borders submit
        if (isset($formdata["btn"]['hover']["borders"])) {
            if (in_array('top', $formdata["btn"]['hover']["borders"])) {
                $html = $this->addBorder($html, 'top', $formdata["btn"]['hover']["border-width"], $formdata["btn"]['hover']["border-style"], $formdata["btn"]['hover']["border-color"]);
            }
            if (in_array('bottom', $formdata["btn"]['hover']["borders"])) {
                $html = $this->addBorder($html, 'bottom', $formdata["btn"]['hover']["border-width"], $formdata["btn"]['hover']["border-style"], $formdata["btn"]['hover']["border-color"]);
            }
            if (in_array('left', $formdata["btn"]['hover']["borders"])) {
                $html = $this->addBorder($html, 'left', $formdata["btn"]['hover']["border-width"], $formdata["btn"]['hover']["border-style"], $formdata["btn"]['hover']["border-color"]);
            }
            if (in_array('right', $formdata["btn"]['hover']["borders"])) {
                $html = $this->addBorder($html, 'right', $formdata["btn"]['hover']["border-width"], $formdata["btn"]['hover']["border-style"], $formdata["btn"]['hover']["border-color"]);
            }
        }
        //format submit
        $html .= $this->addFormat($formdata["btn"]['hover']["format"]);
        //padding submit
        if (isset($formdata["btn"]["inner-padding"])) {
            if (isset($formdata["btn"]['hover']["inner-padding"]["top"])) {
                $html = $this->addPadding($html, 'top', $formdata["btn"]['hover']["inner-padding"]["top"]);
            }
            if (isset($formdata["btn"]['hover']["inner-padding"]["right"])) {
                $html = $this->addPadding($html, 'right', $formdata["btn"]['hover']["inner-padding"]["right"]);
            }
            if (isset($formdata["btn"]['hover']["inner-padding"]["bottom"])) {
                $html = $this->addPadding($html, 'bottom', $formdata["btn"]['hover']["inner-padding"]["bottom"]);
            }
            if (isset($formdata["btn"]['hover']["inner-padding"]["left"])) {
                $html = $this->addPadding($html, 'left', $formdata["btn"]['hover']["inner-padding"]["left"]);
            }
        }
        $html .= 'width:'.$formdata["btn"]['hover']["btn-width"].';font-size:'.$formdata["btn"]['hover']["font-size"].'px;
        letter-spacing:'.$formdata["btn"]['hover']["letter-spacing"].'px;';
        $html .= 'box-shadow: '.$formdata["btn"]['hover']["shadow-horiz"].'px '.$formdata["btn"]['hover']["shadow-vertic"].'px '.$formdata["btn"]['hover']["shadow-blur"].'px '.$formdata["btn"]['hover']["shadow-spread"].'px '.$formdata["btn"]['hover']["shadow-color"].';';
        $html .= 'text-align:'.$formdata["btn"]['hover']["align"].'}';
        $html .= '@media (max-width: 800px) { .inputwrapper input[type="submit"] { padding: 8px 0; } }';
        $html .= '@media (max-width: 400px) { .inputwrapper input[type="submit"] { padding: 8px 0; } .inputwrapper .checkbox { align-items: flex-start; } .productformwrapper { padding: 15px; } body { margin: 0; padding: 0; }';

        // Заголовок
        $title = '';
        if ($formdata['additional']['showtitle'] == 1) {
            $title = '<h3 class="h3form">' . $formdata['additional']['titletext'] . '</h3>';
            $html .= '.h3form{font-size:'.$formdata['additional']['title-font-size'].'px;letter-spacing:'.$formdata['additional']['title-letter-spacing'].'px;';
            $html .= $this->addFormat($formdata["additional"]["titleFormat"]);
            $html .= 'text-align:'.$formdata["additional"]["titleAlign"].';';
            $html .= 'color:'.$formdata["additional"]["title-color"].';}';
        }
        //Подзаголовок
        $subtitle = '';
        if ($formdata['additional']['showSubtitle'] == 1) {
            $subtitle = '<h5 class="h5form">' . $formdata['additional']['Subtitletext'] . '</h5>';
            $html .= '.h5form{font-size:'.$formdata['additional']['subtitle-font-size'].'px;letter-spacing:'.$formdata['additional']['subtitle-letter-spacing'].'px;';
            $html .= $this->addFormat($formdata["additional"]["subtitleFormat"]);
            $html .= 'text-align:'.$formdata["additional"]["subtitleAlign"].';';
            $html .= 'color:'.$formdata["additional"]["subtitle-color"].';}';
        }
        $underformtext = '';
        if ($formdata["underform"]["showtext"] == 1) {
            $underformtext = "<div>".$formdata['underform']['text']."</div>";
        }
        //Я.Метрика
        //TODO получение номера из бд
        $yaAnalytic = '';
        $settings = System::getSetting(true);
        if (!empty($formdata["data"]["yandex_target_id"])) {
            $yaAnalytic_id = $settings['yacounter'];
            $yaAnalytic = 'onSubmit="';
            $targId = $formdata['data']['yandex_target_id'];
            $yaAnalytic .= "ym($yaAnalytic_id, reachGoal, $targId);";
            $yaAnalytic .= '"';
        }
        $settingsParams = json_decode($settings["params"], true);
        $html .= '</style>';
        $protocol = isset($_SERVER["HTTPS"]) ? "https://" : "http://";
        $yandexHtml = "";
        if (!empty($targId)) {
            $yandexHtml = '<script>const reachGoal = "reachGoal"</script>
                            <script>const '.$targId.' = "'.$targId.'"</script>';
        }


        $html .= '<div class="wrapperform">
<div class="productformwrapper" id="productformwrapper">
    '.$yandexHtml.'
    <form class="productform" id="productformm" method="POST" target="_blank" action="'. $protocol . $_SERVER["HTTP_HOST"] .'/buy/'.$formdata['products_id'][0].'" '.$yaAnalytic.'>
        '.$title.$subtitle.'
        <div id="productswrapper"></div>
        <div class="inputwrapper">
            ';
        if (isset($formdata["fields"]["fill"])) {
            if (in_array('usename', $formdata["fields"]["fill"])) {
                if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
                    $html .= '<label for="yourname">[YOUR_NAME]</label>';
                }
                $html .= '<input id="yourname" type="text" name="name" placeholder=" [YOUR_NAME]" required="required">';
            } else {
                $html .= '<input type="hidden" name="name" value="'.@$settingsParams["not_exist_name"].'">';
            }
            if (in_array('usesurname', $formdata["fields"]["fill"])) {
                if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
                    $html .= '<label for="surname">[YOUR_SURNAME]</label>';
                }
                $html .= '<input id="surname" type="text" name="surname" placeholder=" [YOUR_SURNAME]">';
            }

            if (in_array('useemail', $formdata["fields"]["fill"])) {
                if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
                    $html .= '<label for="email">[YOUR_EMAIL]</label>';
                }
                $html .= '<input id="email" type="email" name="email" placeholder=" E-mail" required="required">';
            }

            if (in_array('usetg', $formdata["fields"]["fill"])) {
                if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
                    $html .= '<label for="tg">[YOUR_TELEGRAM]</label>';
                }
                $html .= '<input id="tg" type="text" name="email" placeholder=" @nickname">';
            }
            if (in_array('usephone', $formdata["fields"]["fill"])) {
                if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
                    $html .= '<label for="phone">[YOUR_PHONE]</label>';
                }
                $html .= '<input id="phone" type="text" name="phone" placeholder=" +7 (___)___-__-__" onfocus="if(this.value==""){this.value="+7"}">';
            }
            if (in_array("usepromo", $formdata['fields']['fill'])) {
                if (isset($formdata["fields"]["labels"]) && $formdata["fields"]["labels"] == 1) {
                    $html .= '<label for="phone">[PROMO_CODE]</label>';
                }
                $html .= '<input id="promocode" placeholder="[PROMO_CODE]" type="text" name="promo" value="'.$formdata["data"]["discount_coupon"].'">';
            } else {
                $html .= '<input type="hidden" name="promo" value="'.$formdata["data"]["discount_coupon"].'">';
            }
            if (in_array('usepolicy', $formdata["fields"]["fill"])) {
                if (isset($formdata['fields']['policyLink']) && strlen($formdata['fields']['policyLink']) >= 1) {
                    $html .= '<div class="checkbox">'.'<input id="confirm" type="checkbox" required name="politika">'.
                    '<label for="confirm">'.$formdata['fields']['policyLink'].'</label>'
                        .'</div>';
                } else {
                    $html .= '<div class="checkbox">'.'<input id="confirm" type="checkbox" required name="politika">'.
                    '<label for="confirm">[YOUR_AGREEMENT]</label>'
                        .'</div>';
                }
            }
            if (isset($formdata["data"]["yandex_target_id"])){
                $html .= '<input type="hidden" name="yandex_target_id" value="'.$formdata["data"]["yandex_target_id"].'">';
            }
            if (isset($formdata["data"]["partner_id"]) && $formdata["data"]["partner_id"] != ""){
                $html .= '<input type="hidden" name="pid" value="'.$formdata["data"]["partner_id"].'">';
            }
        }
        $html .= '<input type="submit" value="'.$formdata["btn"]["text"].'" name="buy" id="formSubmitBtn"></div>'.$underformtext.'</form></div></div>';

        if (isset($formdata['sendevent']['type']) && $formdata['sendevent']['type'] == 1) {
            $html .= '<style>.onediv {text-align: center; padding-bottom: 16px;}</style><script>
    document.addEventListener("DOMContentLoaded", async function () {
        let productformm = document.getElementById("productformm");
        productformm.addEventListener("submit", async function (event) {
            event.preventDefault();
            let response1 = await fetch(productformm.getAttribute("action"), {method: "post", credentials: "same-origin", redirect: "follow", body: new FormData(productformm)});
            let res = await response1.text();           
            productformm.innerHTML = "<div class=onediv >'.$formdata['sendevent']['text'].'</div>";
        })
    })
</script>';
        }

        if (!isset($formdata['form']['product_kind_of_choice'])) {
            $formdata['form']['product_kind_of_choice'] = 'radio';
        }
        //Генерируем скрипт
        $currency = $settings['currency'] ?? "руб.";
        $html .= '
<script>
let productsFlowsData = [];
document.addEventListener("DOMContentLoaded", async function () {
    //АЙДИ ПРОДУКТОВ->разделяем строку
    let products_ids = "'. implode('/', $formdata['products_id']) .'";products_ids = products_ids.split("/");let error = 0;let wrapper = document.getElementById("productswrapper");
    let showtitleproduct = '.$formdata["product"]["showtitle"].';
    let selectOrRadio = "'.$formdata['form']['product_kind_of_choice'].'";
    let select = document.createElement("select");
    if (selectOrRadio == "select") {    
        select.addEventListener("input", updateformurl);
        document.querySelector("#productswrapper").insertAdjacentElement("afterBegin", select);
    }
    
    
    //ПОДГРУЖАЕМ ДАННЫЕ
                    let MD5=function(d){d=unescape(encodeURIComponent(d));result=M(V(Y(X(d),8*d.length)));return result.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}    
    for (product_id in products_ids) {
        let response = await fetch("'.$protocol. $_SERVER["HTTP_HOST"] .'/api/catalog/" + products_ids[product_id]);
        let status = await response.status;
        switch (status) {
            case 200:
                let result = await response.json();//ответ json
                //Формируем список товаров
                if (selectOrRadio == "radio") {
                    let div = document.createElement("div");//контейнер продукта
                    //Создание объектов
                    //один продукт
                    div.className = "radiowrapper";
                    wrapper.append(div);
                    //Чекбокс или ничего              
                    if (products_ids.length == 1) {
                        document.getElementById("productformm").action = "'.$protocol. $_SERVER["HTTP_HOST"] . '/buy/" + products_ids[product_id];
                        if (result.product_flows) {
                            showFlowSelect(products_ids[product_id], result.product_flows)
                        }
                    } else {
                        let input = document.createElement("input");
                        
                       
                        if (result.product_flows) {
                            input.setAttribute("flowData", JSON.stringify(result.product_flows));
                        } else {
                            input.setAttribute("flowData", "false");
                        }
    
                        input.setAttribute("type", "radio");
                        input.setAttribute("name", "productid");
                        input.setAttribute("required", true);
                        input.value = result.product_id;
                        input.setAttribute("id", "form_product" + products_ids[product_id]);
                        div.insertAdjacentElement("beforeend", input);
                        input.addEventListener("input", updateformurl);
                    }                
                    //Название
                    let prodname = document.createElement("label");
                    prodname.className = "prodname";                
                    prodname.innerHTML = result.product_title;
                    prodname.setAttribute("for", "form_product" + products_ids[product_id]);  
                       
                    //Цена
                    let pricewrapper = document.createElement("div");
                    pricewrapper.className = "price";
                    let price = document.createElement("div");
                    let oldprice = document.createElement("div");
                    price.className = "pricetext";
                    oldprice.className = "oldpricetext";
                    if (result.price != result.old_price) {
                        price.innerHTML = result.price + " '.$currency.'";
                        oldprice.innerHTML = result.old_price + " '.$currency.'"; 
                    } else {
                        price.innerHTML = result.price + " '.$currency.'";
                        oldprice.innerHTML = "";
                    }
                    //Время
                    let timeinp = document.createElement("input");
                    timeinp.setAttribute("type", "hidden");
                    timeinp.setAttribute("name", "time");
                    timeinp.value = parseInt(Date.now() / 1000) - 2;
                    //Токен
                    let token = document.createElement("input");
                    token.setAttribute("type", "hidden");
                    token.setAttribute("name", "token");
                    token.value = MD5(products_ids[product_id] + "s+m" + timeinp.value);
                    //Вставляем объекты в html
                    if (showtitleproduct == 1 || products_ids.length > 1) {
                        div.insertAdjacentElement("beforeend", prodname);
                        div.insertAdjacentElement("beforeend", pricewrapper);
                        pricewrapper.insertAdjacentElement("beforeend", oldprice);
                        pricewrapper.insertAdjacentElement("beforeend", price);
                    }
                    div.insertAdjacentElement("beforeend", timeinp);
                    div.insertAdjacentElement("beforeend", token);
                } else {                    
                    let option = document.createElement("option");
                    option.value = products_ids[product_id];
                    option.text = result.product_title + " - " + result.price + " '.$currency.'";
                    
                    productsFlowsData[products_ids[product_id]] = result.product_flows;
                    
                    if (result.product_flows) {
                        option.setAttribute("flowData", JSON.stringify(result.product_flows));
                    } else {
                        option.setAttribute("flowData", "false");
                    }
                    select.appendChild(option);
                }                                
                break;
            default:
                error += 1;
                break;
        }
    }
    if (error >= products_ids.length) {//Удаление формы,если все товары не найдены
        document.getElementById("productformwrapper").remove();
    }
    let sended = false;
    document.getElementById("productformm").addEventListener("submit", function (e) {
        console.log(sended);
        if (sended == true) {
            e.preventDefault();
        } else {
            sended = true
            setTimeout(function () {
                sended = false;
            }, 5000); 
        } 
    });
    
    
    function updateformurl(event) {       
        let hasFlow = getFlowDataFromEvent(event);
        if (hasFlow) {
            showFlowSelect(event.target.value, JSON.parse(hasFlow));
        } else {
            deleteOldFlowSelect();
        }
        document.getElementById("productformm").action = "'. $protocol . $_SERVER["HTTP_HOST"] .'/buy/" + event.target.value;
        addUtmToForm(document.getElementById("productformm"));
    }
    
    function getFlowDataFromEvent(event) {   
        let result = false;
        if (selectOrRadio == "radio") {
            result = event.target.getAttribute("flowdata");
            console.log(result)
        } else {
            const select = event.target;
            const selectedOption = select.options[select.selectedIndex];
            result = selectedOption.getAttribute("flowdata");
        }
        
        if (result == "false" || result === false || result === null) {
            result = false;
        }
        
        return result;
    }
    
    function showFlowSelect(prod_id, flowdata) {
        deleteOldFlowSelect();
        
        let flowSelect = document.createElement("select");
        flowSelect.setAttribute("required", "true");
        flowSelect.setAttribute("name", "flows");
        flowSelect.setAttribute("id", "productFlowSelect");
        document.querySelector("#productswrapper").insertAdjacentElement("beforeend", flowSelect);
        
        if (selectOrRadio != "radio") {
            flowdata = productsFlowsData[prod_id];            
        }
        console.log("d", flowdata);
        for (let flow in flowdata) {
            console.log("e", flowdata[flow]);
            let option = document.createElement("option");
            option.value = flowdata[flow].flow_id;
            option.text = flowdata[flow].flow_title;
            flowSelect.appendChild(option);
        }
    }
    
    function deleteOldFlowSelect() {//Удалить старый селект
        let oldElem = document.getElementById("productFlowSelect");       
        if (oldElem) {            
            oldElem.remove();
        }
    }
    
    addUtmToForm(document.getElementById("productformm"));
    //сохранение get параметров внутри iframe
    function getutm() {
        let url = document.baseURI;
        let result = url.split("?") ?? "";
        let getparams = "";
        if (result[1]) {
            getparams = result[1];
        }
        return "?" + getparams;
    }
    function addUtmToForm(form) {
        let utm = getutm();
        formlink = form.action;
        form.action = formlink + utm;
        return form;
    }    
});</script>
        ';

        //Маска телефона
        if (isset($formdata['fields']['phone']['enable_mask']) && $formdata['fields']['phone']['enable_mask'] == 1 && in_array('usephone', $formdata["fields"]["fill"])) {
            $html .= '
<script>
let selector = "#phone";
let phoneInput = document.querySelector(selector);
phoneInput.addEventListener("keydown", function(event) {
   if( !(event.key == "ArrowLeft" || event.key == "ArrowRight" || event.key == "Backspace" || event.key == "Tab")) { event.preventDefault() }
    let mask = "+7 (111) 111-11-11"; 
    if (/[0-9\+\ \-\(\)]/.test(event.key)) {
        // Здесь начинаем сравнивать this.value и mask
        let currentString = this.value;
        let currentLength = currentString.length;
        if (/[0-9]/.test(event.key)) {
            if (mask[currentLength] == "1") {
                this.value = currentString + event.key;
            } else {
                for (let i=currentLength; i<mask.length; i++) {
                if (mask[i] == "1") {
                    this.value = currentString + event.key;
                    break;
                }
                currentString += mask[i];
                }
            }
        }
    } 
});</script>';
        }

        if (isset($formdata["data"]["yandex_target_id"]) && !empty($formdata["data"]["yandex_target_id"])) {
            $html .= '<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym("'.$yaAnalytic_id.'", "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/89633414" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->';
        }


        if (isset($formdata['form']['useUtmScript']) && $formdata['form']['useUtmScript'] == 1) {
            $html .= '<script>
    document.addEventListener("DOMContentLoaded", function () {
        let utm = document.location.search;
        let links =  document.querySelectorAll("a");
        links.forEach(function (currentValue, index, array) {
            let link = array[index].getAttribute("href");
            array[index].setAttribute("href", link + utm);
        });
        let forms =  document.querySelectorAll("form");
        forms.forEach(function (currentValue, index, array) {
            array[index] = addUtm2Form(array[index]);
        });
    });
    function addUtm2Form(form) {
        let utm = document.location.search;
        formlink = form.action;
        form.action = formlink + utm;
        return form;
    }
</script>';
        }

        $this->ids['productformiframe'] = 'productformiframe';
        $html1 = "<iframe id='productformiframe".$id."' style='width:100%;border:none;overflow:visible;display:block;min-height:455px;' scrolling='no' srcdoc='";
        $html1 .= $html;
        $html1 .= "'></iframe>";

        return $html1;
    }

    public function generateFullForm($formdata, $id = null) {
        $db = Db::getConnection();

        if ($id == null) {
            $result = $db->query('SELECT MAX(`id`) as "maxid" FROM `' . PREFICS . 'created_product_forms`');
            $result = $result->fetch();
            $id = $result['maxid'] + 1;
        }
        //Генерируем css, js, html для формы
        $html = $this->processForm($formdata, $id);

        $html = $this->changeLang($html, $formdata['lang'] ?? "ru");

        //Генерируем скрипт на вставку и авторазмеры
        $script = $this->getScript($html, $formdata, $id);

        //Сохраняем скрипт как файл js
        $filename = 'form' . $id . '.js';


        $path = $_SERVER["DOCUMENT_ROOT"].$this->path;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents($path.$filename, $script);

        //Возвращаем код для вставки в файл
        $embedCode = $this->getEmbedCode($filename, $id);
        return ['embedCode'=>$embedCode, 'filename'=>$filename];
    }

    public function changeLang($html, $lang = "ru") {
        $replace_lang = include(ROOT."/lang/form_generator/$lang.php") ?? [];

        return strtr($html, $replace_lang);
    }

    public function getScript($html, $formdata, $id) {
        $script = 'document.addEventListener("DOMContentLoaded", function () {';
        $script .= 'let formhtml = `'.$html.'`;
               //Прикрепляем все
               let wrapper = document.getElementById("formwrapper" + '.$id.');
               wrapper.innerHTML = formhtml;
            
               //Авто размер фрейма
               let iframe = document.getElementById("productformiframe" + '.$id.');
            
               iframe.addEventListener("load", function () {//Первичное придание размеров
                  let scrollHeight = iframe.contentDocument.body.scrollHeight;
                  iframe.style.height = scrollHeight + '.count($formdata["products_id"]).' * 47 + "px";
                  window.setInterval(maxheight, 200);
               });
               async function maxheight() {//Обновление размера
                  let scrollHeight = iframe.contentDocument.body.scrollHeight;
                  iframe.style.height = scrollHeight + 20 + "px";
               }
            });';
        return $script;
    }

    public function getEmbedCode($filename, $id) {
        $protocol = isset($_SERVER["HTTPS"]) ? "https://" : "http://";
        $link = $protocol.$_SERVER['HTTP_HOST'].$this->path.$filename;
        $code = '<div id="formwrapper'.$id.'" style="width: 100%;"><script src="'.$link.'"></script></div>';
        return $code;
    }

    public function actionList() {
        $acl = self::checkAdmin();
        if(!isset($acl['show_conditions'])) {
            header("Location: /admin");
        }
        $name = $_SESSION['admin_name'];

        $forms = createdproductforms::getFormsForList();

        require_once (ROOT . '/template/admin/views/products/form_list.php');
        return true;
    }

    public function actionEdit($id) {
        $acl = self::checkAdmin();
        if(!isset($acl['show_conditions'])) {
            header("Location: /admin");
        }
        $name = $_SESSION['admin_name'];

        if (isset($_POST['updateForm'])) {
            $formdata = $_POST;
            $result = $this->editAndUpdateForm($id, $formdata);

            return system::redirectUrl("/admin/products/form/edit/$id", $result);
        }

        $form = createdproductforms::getFormById($id);
        if (!$form) {
            require_once (ROOT . "/template/404.php");
            exit(404);
        }

        $selected_products_ids = json_decode($form['products'], true);
        $products = Product::getProductListOnlySelect();
        $generated_form = $form['form'];

        $formdata = json_decode($form['data'], true);
        /*echo "<pre>";
        var_dump($formdata);
        echo "</pre>";*/

        require_once (ROOT . '/template/admin/views/products/form_edit.php');
        return true;
    }

    /**
     * Сохранить форму
     *
     * @param $formdata
     * @return void
     */
    public function saveForm($formdata) {
        //все данные формы
        $generated_form = $formdata['embedCode'];//сгенерированная форма
        $jsonproducts = json_encode($formdata['products_id']);
        $formname = $formdata['form']['name'];
        unset($formdata['embedCode']);
        $jsonformdata = json_encode($formdata);
        $result = createdproductforms::saveForm($formname, $jsonproducts, $jsonformdata, $generated_form);
        System::setNotif(true, "Форма успешно сохранена.");
        return system::redirectUrl('/admin/products/form/edit/'.$result, true);
    }


    public function editAndUpdateForm($id, $formdata) {

        $generated = $this->generateFullForm($formdata, $id);

        //Сохраняем все значения
        $jsonproducts = json_encode($formdata['products_id']);
        $formname = $formdata['form']['name'];
        $formdata['filename'] = $generated['filename'];
        $jsonformdata = json_encode($formdata, JSON_UNESCAPED_UNICODE);

        $result = createdproductforms::updateForm($id, $formname, $jsonproducts, $jsonformdata, $generated['embedCode']);
        System::setNotif(true, "Форма успешно обновлена.");
        return $result;
    }

    public function actionDelete($id) {

        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];

        $result = createdproductforms::deleteForm($id);
        $file = $_SERVER["DOCUMENT_ROOT"].$this->path.'form' . $id . '.js';

        if (is_file($file)) {
            unlink($file);
        }


        if ($result) {
            System::setNotif(true, "Форма успешно удалена.");
        } else {
            System::setNotif(true, "Ошибка. Форма не была удалена!");
        }

        System::redirectUrl("/admin/products/formlist");
    }

}