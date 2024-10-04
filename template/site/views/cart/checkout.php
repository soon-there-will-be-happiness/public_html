<?defined('BILLINGMASTER') or die;
$name = $email = $phone = null;

if (isset($_COOKIE['emnam'])) {
    $emnam = explode("=", htmlentities($_COOKIE['emnam']));
    if (isset($emnam[0])) {
        $email = $emnam[0];
    }

    if (isset($emnam[1])) {
        $name = $emnam[1];
    }
}

if (isset($is_auth) && $is_auth != false) {
    $user = User::getUserById($is_auth);
    $name = $user['user_name'];
    $email = $user['email'];
    $phone = $user['phone'];
}?>

<div id="content">
    <div class="" id="checkout">
        <div class="container-cart" id="order_form">
            <ul class="container-crumbs">
                <li class="crumbs-no-active crumbs-width-210"><em><span>1</span>
                    <a href="/cart"><?=System::Lang('CART');?></a></em>
                </li>
                <li class="two-active"><span>2</span><?=System::Lang('YOUR_DATES');?></li>
                <li><em><span>3</span><?=System::Lang('PAYMENT_OPTION');?></em></li>
            </ul>

            <h2 class="mb-45"><?=System::Lang('ORDER_REGISTRATION');?></h2>

            <div class="order_data">
                <h3 class="mb-15"><?=System::Lang('ITEM_ORDER');?>:</h3>

                <div class="offer main">
                    <?$total = $full_price = 0;
                    $delivery = 0;
                    foreach($products as $product):
                        if ($product['type_id'] == 2) {
                            $delivery = 2;
                        }

                        $price = Price::getPriceinCatalog($product['product_id']);
                        $full_price += $price['price'];
                        $total += $price['real_price'];?>

                        <div class="order_item">
                            <?if($product['product_cover']!= null):?>
                                <div class="order_item-left">
                                    <img src="/images/product/<?=$product['product_cover'];?>" alt="">
                                </div>
                            <?endif;?>

                            <div class="order_item-desc">
                                <h4 class="product_name"><?=$product['product_name'];?></h4>
                                <?if($product['product_desc'] != null):?>
                                    <div class="product_desc"><?=nl2br($product['product_desc']);?></div>
                                <?endif;?>

                                <a class="order_item-delete" href="<?=$setting['script_url'];?>/cart/del/<?=$product['product_id'];?>">
                                    <span class="icon-remove"></span>Удалить
                                </a>
                            </div>

                            <div class="order_item-price_box-right">
                                <?if($price['price'] > $price['real_price']):?>
                                    <span class="old_price product-price"><?=$price['price'];?> <?=$setting['currency'];?></span>
                                <?endif;?>
                                <div class="font-bold product-price<?=$price['price'] > $price['real_price'] ? ' red-price' : '';?>"><?="{$price['real_price']} {$setting['currency']}";?></div>
                            </div>
                        </div>
                    <?endforeach;?>

                    <div class="payment-wrap">
                        <div class="payment-row">
                            <div class="payment-left"></div>

                            <div class="payment-right">
                                <div class="payment-right-inner">
                                    <div class="payment-right-inner__item order-sum">
                                        <div class="payment-right-inner__subtitle"><?=System::Lang('SUMM_ORDER');?></div>
                                        <?=$full_price;?> <?=$setting['currency'];?>
                                    </div>

                                    <?if($full_price > $total):?>
                                        <div class="payment-right-inner__item">
                                            <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div>
                                            <strong class="color-red"><?=$full_price - $total;?> <?=$setting['currency'];?></strong>
                                        </div>
                                    <?endif;?>

                                    <div class="payment-itogo__total">
                                        <div class="payment-right-inner__subtitle"><?=System::Lang('RESULT');?></div>
                                        <?="{$total} {$setting['currency']}";?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="payments_list">
                    <h3 class="mb-15"><?=System::Lang('YOUR_DATES');?>:</h3>

                    <form class="cart-form" action="" method="POST">
                        <ul class="cart-form-field">
                            <li class="cart-form-input-2">
                                <label><?=System::Lang('YOUR_NAME');?></label>
                                <input type="text" value="<?=$name?>" name="name" required="required">
                            </li>
                            
                            <?if($this->settings['show_surname'] == 2 || ($this->settings['show_surname'] == 1 && $price['real_price'] > 0)):?>
                            <li class="cart-form-input"><label><?=System::Lang('YOUR_SURNAME');?></label>
                                <input type="text" name="surname" value="<?=$surname;?>" required="required">
                            </li>
                            <?endif;?>

                            <li class="cart-form-input-2">
                                <label><?=System::Lang('YOUR_EMAIL');?></label>
                                <script>document.write(window.atob("PGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCI="));</script> value="<?=$email?>" required="required" pattern="^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,})+$">
                            </li>

                            <?if($setting['request_phone'] == 1):?>
                                <li class="cart-form-input-2">
                                    <label><?=System::Lang('CLIENT_PHONE');?></label>
                                    <input type="text" name="phone" value="<?=$phone;?>" required="required">
                                </li>
                            <?endif;

                            if($delivery == 2):?>
                                <li class="cart-form-input-2"><label><?=System::Lang('POSTCODE');?></label>
                                    <input type="text" name="index">
                                </li>

                                <li class="cart-form-input-2"><label><?=System::Lang('CITY');?></label>
                                    <input type="text" name="city" required="required">
                                </li>

                                <li class="cart-form-input-2"><label><?=System::Lang('ADDRESS');?></label>
                                    <input type="text" name="address" required="required">
                                </li>
                            <?endif;?>

                            <li class="cart-form-input-2">
                                <label><?=System::Lang('NOTE');?></label>
                                <textarea name="comment" rows="3" cols="49"></textarea>
                            </li>

                            <li>
                                <label class="check_label">
                                    <input type="checkbox" name="politika" required="required">
                                    <span><?=System::Lang('LINK_CONFIRMED');?></span>
                                </label>

                                <input type="hidden" name="type_id" value="<?=$delivery;?>">
                            </li>

                            <li><input type="submit" class="order_button btn-blue" name="buy" value="<?=$total == 0 ? 'Скачать' : 'Заказать';?>"></li>
                        </ul>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>