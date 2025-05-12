<?defined('BILLINGMASTER') or die;


$ya_goal = !empty($this->settings['yacounter']) ? "yaCounter".$this->settings['yacounter'].".reachGoal('CREATE_ORDER');" : null;
$ga_goal = $this->settings['ga_target'] == 1 ? "ga ('send', 'event', 'create_order', 'submit');" : null;
$metriks = !empty($this->settings['yacounter']) || $this->settings['ga_target'] == 1 ? ' onsubmit="'.$ya_goal.$ga_goal.' return true;"' : null;

$name = $email = $phone = $surname = $patronymic = null;

if (isset($_COOKIE['emnam'])) {
    $emnam = explode("=", htmlentities(urldecode($_COOKIE['emnam'])));
    if(isset($emnam[0])) $email = $emnam[0];
    if(isset($emnam[1])) $name = $emnam[1];
    if(isset($emnam[2])) $phone = $emnam[2];
}

$name =  isset($_REQUEST['name']) ? trim(htmlentities(mb_substr($_REQUEST['name'], 0, 255))) : '';
$phone = isset($_REQUEST['phone']) ? htmlentities(mb_substr($_REQUEST['phone'],0,25)) : null;

if ($is_auth) {
    $user = User::getUserById($is_auth);
    $name = $user['user_name'];
	$surname = $user['surname'];
    $email = $user['email'];
    $phone = $user['phone'];
    $patronymic = $user['patronymic'];
}
?>

<? /* (?) скрыть фото на моб. версии */ 

if( isset($this->settings['params']['order_img_mob']) && $this->settings['params']['order_img_mob'] == 0):?>
<style type="text/css">
    @media screen and (max-width: 640px),
    only screen and (max-device-width:640px) {
        .cart-item-left, .review_img{
            display: none;
        }
    }
</style>
<? endif; ?>

<div id="order_form">
    <div class="container-cart">
        <form action="" method="POST"<?=$metriks;?> id="form_order_buy">
            <?if($price['real_price'] > 0):?>
                <ul class="container-crumbs <?if($related_products) echo ''; else echo 'container-crumbs-two-steps'?> ">
                    <li class="first-active"><span>1</span><?=System::Lang('YOUR_DATES');?></li>
                    <?if($related_products) echo '<li><span>2</span>Корзина</li>';?>
                    <li><span><?if($related_products) echo '3'; else echo '2';?></span><?=System::Lang('PAYMENT_OPTION');?></li>
                </ul>

                <h2 class="mb-45"><?=System::Lang('ORDER_REGISTRATION');?></h2>
                <h3 class="mb-15"><?=System::Lang('ITEM_ORDER');?></h3>

                <div class="cart-item">
                    <?if(!empty($product['product_cover'])):?>
                        <div class="cart-item-left">
                            <img src="/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
							<?if($product['show_amt'] == 1):?>
                                <p style="text-align: center">Осталось: <?=$product['product_amt'];?></p>
                            <?endif;?>
						</div>
                    <?endif;?>

                    <div class="cart-item-right">
                        <h4 class="cart-item-name"><?=$product['product_name'];?></h4>
                        <?if($product['product_desc'] != null):?>
                            <div class="cart-item-desc"><?=nl2br($product['product_desc']);?></div>
                        <?endif;?>

                        <div class="cart-item-price">
                            <?if(empty($product['price_minmax'])):?>
                                <span><?=System::Lang('COAST');?>
                                    <?if($price['real_price'] < $price['price']):
                                        $new_price = Price::getNDSPrice($price['price']);?>
                                        <span class="old_price product-price"><?="{$new_price['price']} {$this->settings['currency']}";?></span>&nbsp;&nbsp;
                                    <?endif;?>
                                    <span class="font-bold product-price<?=$price['real_price'] < $price['price'] ? ' red-price' : '';?>"><?="{$nds_price['price']} {$this->settings['currency']}";?></span>

                                    <?if($currency_list):
                                        foreach($currency_list as $currency):?>
                                            <span class="product-price"> | <?=$nds_price['price'] * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                        <?endforeach;
                                    endif;

                                    if($this->settings['nds_enable'] > 0 && isset($nds_price)):?>
                                        <br /><span>В т.ч. НДС: <?=$nds_price['nds'];?> <?=$this->settings['currency'];?></span>
                                    <?endif;?>
                                </span>
                            <?else: // Тут свободная цена
                                $price_mas = explode(":", $product['price_minmax']);?>
                                <span style="margin-right: 20px;"><?=System::Lang('COAST');?></span>
                                <input style="max-width: 350px;" type="number" name="user_price" min="<?=$price_mas[0];?>" max="<?=$price_mas[1];?>"
                                   placeholder="Укажите вашу цену от <?=$price_mas[0];?> до <?=$price_mas[1];?>" value="<?if(isset($_GET['price'])) echo htmlentities($_GET['price']);?>"
                                >
                            <?endif;?>
                        </div>
                    </div>
                </div>
            <?endif;?>

            <?if($price['real_price'] > 0):?>
                <h3 class="mb-15 mt-45"><?=System::Lang('YOUR_DATES');?></h3>
            <?endif;?>

            <div class="cart-form">
                <?if($price['real_price'] == 0 && !empty($product['product_cover'])):?>
                    <div class="cart-item-left" style="align-self: flex-start;">
                        <img src="<?=$this->settings['script_url'];?>/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
                    </div>
                <?endif;?>

                <div class="cart-item-right">
                    <?if($price['real_price'] == 0):?>
                        <h4 class="cart-item-name"><?=$product['product_name'];?></h4>
                        <?if($product['product_desc'] != null):?>
                            <div class="cart-item-desc mb-30"><?=nl2br($product['product_desc']);?></div>
                        <?endif;?>
                    <?endif;?>

                    <ul class="cart-form-field">
                        
                        <?php // Потоки
                        if($flows_ext) require_once (ROOT . '/extensions/flows/views/frontend/order_buy.php');?>
                        
                        <li class="cart-form-input"><label><?=System::Lang('YOUR_NAME');?></label>
                            <?if($this->settings['only_name2name'] && ($this->settings['show_surname'] == 2 || ($this->settings['show_surname'] == 1 && $price['real_price'] > 0))):?>
                                <input type="text" value="<?=$name?>" name="name" required="required" pattern="^[ ]?[A-Za-zА-Яа-яёЁЇїІіЄєҐґ0-9\-]+[ ]?$">
                            <?else:?>
                                <input type="text" value="<?=$name?>" name="name" required="required">
                            <?endif;?>
                        </li>

                        <?if($this->settings['show_surname'] == 2 || ($this->settings['show_surname'] == 1 && $price['real_price'] > 0)):?>
                            <li class="cart-form-input"><label><?=System::Lang('YOUR_SURNAME');?></label>
                                <input type="text" name="surname" value="<?=$surname;?>" required="required">
                            </li>
                        <?endif;

                        if($this->settings['show_patronymic']):?>
                            <li class="cart-form-input"><label><?=System::Lang('YOUR_PATRONYMIC');?></label>
                                <input type="text" name="patronymic" value="<?=$patronymic;?>" required="required">
                            </li>
                        <?endif;?>

                        <li class="cart-form-input"><label><?=System::Lang('YOUR_EMAIL');?></label>
                            <?if($this->settings['email_protection']):?>
                                <script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCI="));</script>value="<?=$user_email ?? $email?>" required="required" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$">
                            <?else:?>
                            <input type="email" name="email" value="<?=$user_email?>" required="required">
                            <?endif;?>
                        </li>

                        <?if($this->settings['request_phone'] == 1 && !$product['not_request_phone']):?>
                            <li class="cart-form-input"><label><?=System::Lang('YOUR_PHONE');?></label>
                                <input type="text" autocomplete="off" name="phone" value="<?=$phone;?>" required="required">
                            </li>
                        <?endif; ?>

                        <?if(Product::isRequestTelegram($product, $price, $this->settings)):?>
                            <li class="cart-form-input"><label><?=System::Lang('TELEGRAM');?></label>
                                <input type="text" name="nick_telegram" <?if($this->settings['show_telegram_nick'] == 3) echo 'required="required"';?>>
                            </li>
                        <?endif;?>

                        <?if(Product::isRequestInstagram($product, $price, $this->settings)):?>
                            <li class="cart-form-input"><label><?=System::Lang('INSTAGRAM_NIK');?></label>
                                <input type="text" name="nick_instagram" <?if($this->settings['show_instagram_nick'] == 3) echo 'required="required"';?>>
                            </li>
                        <?endif;?>

                        <?if(Product::isRequestVk($product, $price, $this->settings)):?>
                            <li class="cart-form-input"><label><?=System::Lang('ADRESS_VK');?></label>
                                <input type="text" name="vk_page" <?if($this->settings['show_vk_page'] == 3) echo 'required="required"';?> pattern="(https{0,1}:\/\/)?(www\.)?(vk.com\/)(id\d|[a-zA-z][a-zA-Z0-9_.]{2,})" title="Формат https://vk.com/your_page или vk.com/your_page или vk.com/id111111"
                                   value="<?= isset($_GET['vk_id']) ? "vk.com/id".$_GET['vk_id'] : "" ?>">
                            </li>
                        <?endif;?>

                        <?if(Product::isShowCustomFields($product, $price, $this->settings)):
                            if ($custom_fields) {
                                array_walk($custom_fields, function (& $item) {
                                    $item['sort'] = $item['field_sort'];
                                });

                                foreach (Helpers::arraySort($custom_fields) as $custom_field):?>
                                    <li class="cart-form-input<?=$custom_field['field_type'] == CustomFields::FIELD_TYPE_MULTI_SELECT ? ' select2-form-input' : '';?>">
                                        <label><?=$custom_field['field_name'];?></label>
                                        <?=CustomFields::getFieldTag2Order($custom_field, $user_id);?>
                                    </li>
                                <?endforeach;
                            }
                        endif;?>

                        <?if($product['type_id'] == 2):?>
                            <li class="cart-form-input"><label><?=System::Lang('POSTCODE');?></label>
                                <input type="text" name="index">
                            </li>

                            <li class="cart-form-input"><label><?=System::Lang('CITY');?></label>
                                <input type="text" name="city" required="required">
                            </li>

                            <li class="cart-form-input"><label><?=System::Lang('ADDRESS');?></label>
                                <input type="text" name="address" required="required">
                            </li>
                        <?endif;?>

                        <?if($this->settings['show_order_note'] == 1):?>
                            <li class="cart-form-input"><label><?=System::Lang('NOTE');?></label>
                                <textarea name="comment" rows="3" cols="49"></textarea>
                            </li>
                        <?endif;?>

                        <li>
                            <?if($product['price']> 0 && !$product['promo_hide']):
                                require_once (__DIR__.'/../common/add_promo_code.php');
                            endif;?>
                        </li>

                        <li>
                            <label class="check_label">
                                <input type="checkbox" name="politika" required="required">
                                <?if(!isset($_SESSION['org'])):?>
                                    <?if(stripos($product['group_id'], "23") === false):?>
                                        <span class="politics"><?=System::Lang('LINK_CONFIRMED_P');?><?=$_GET['pr'] ?? null;?><?=System::Lang('LINK_CONFIRMED_O');?></span>
                                    <?else:?>
                                        <span class="politics"><?=System::Lang('LINK_CONFIRMED');?></span>
                                    <?endif;?>
                                <?else:?>
                                    <span class="politics"><?=System::Lang('LINK_CONFIRMED_2');?></span>
                                <?endif;?>
                            </label>
                        </li>

                        <li>
                            <input type="hidden" name="time" value="<?=$date;?>">
                            <input type="hidden" name="token" value="<?=md5($id.'s+m'.$date);?>">
                            <input type="hidden" name="vk_id" value="<?=@$_REQUEST['vk_id'] ?>">
                            <?php if (isset($_REQUEST['pid'])) { ?>
                                <input type="hidden" name="pid" value="<?=$_REQUEST['pid'] ?? "" ?>">
                            <?php } ?>
                            <input type="submit" class="order_button btn-blue" name="buy" value="<?=System::Lang('CONTINUE');?>">
                        </li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    form_order_buy_sended = false;
    document.getElementById("form_order_buy").addEventListener("submit", function (e) {
        if (form_order_buy_sended === true) {
            e.preventDefault();
        } else {
            form_order_buy_sended = true
            setTimeout(function () {
                form_order_buy_sended = false;
            }, 5000);
        }
    });
</script>


<?if(isset($_SESSION['org'])): // Разделение финпотоков?>
    <div id="custom_doc" class="uk-modal">
        <div class="uk-modal-dialog uk-modal-dialog-lightbox uk-slidenav-position" style="padding:20px">
            <a href="#" class="uk-modal-close uk-close uk-close-alt"></a>
            <?=$_SESSION['org']['oferta'];?>
        </div>
    </div>
<?endif;?>
</body>
</html>