<?defined('BILLINGMASTER') or die;?>

<div id="content">
    <div id="cart">
        <div class="container-cart">
            <?if($product_in_cart):?>
                <ul class="container-crumbs  ">
                    <li class="first-active"><span>1</span><?=System::Lang('CART');?></li>
                    <li><span>2</span><?=System::Lang('YOUR_DATES');?></li>
                    <li><span>3</span><?=System::Lang('PAYMENT_OPTION');?></li>
                </ul>
            <?endif;?>

            <h2 class="mb-45"><?=System::Lang('CART');?></h2>
            <h3 class="mb-15"><?=System::Lang('ITEM_ORDER');?></h3>

            <?if($product_in_cart):
                $total = $full_price = 0;?>

                <div class="offer main">
                    <?foreach($products as $product):
                        $price = Price::getPriceinCatalog($product['product_id'], false);
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

                                <a class="order_item-delete" href="/cart/del/<?echo $product['product_id'];?>">
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
                                        <strong><?=$full_price;?> <?=$setting['currency'];?></strong>
                                    </div>

                                    <?if($full_price > $total):?>
                                        <div class="payment-right-inner__item">
                                            <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div>
                                            <strong class="color-red "><?=$full_price - $total;?> <?=$setting['currency'];?></strong>
                                        </div>
                                    <?endif;?>

                                    <div class="payment-itogo__total">
                                        <div class="payment-right-inner__subtitle"><?=System::Lang('RESULT');?></div>
                                        <?="{$total} {$setting['currency']}";?>
                                    </div>
                                </div>

                                <form action="" method="POST">
                                    <p class="mt-25"><input type="submit" class="button btn-blue d-block" name="checkout" value="<?=System::Lang('CHECKOUT2');?>"></p>
                                </form>

                                <?$accumulative_discount = Product::getSaleList(1, [5]);
                                if(!$accumulative_discount):?>
                                    <form style="margin-top:15px;" id="promo" action="" method="POST">
                                        <?require_once (__DIR__.'/../common/add_promo_code.php');?>
                                    </form>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            <?else:?>
                <p><?=System::Lang('EMPTY_CART');?></p>
                <p><a href="<?=$setting['script_url'];?>"><?=System::Lang('ON_MAIN');?></a></p>
            <?endif;?>
        </div>
    </div>
</div>