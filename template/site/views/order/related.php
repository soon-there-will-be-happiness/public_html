<?php defined('BILLINGMASTER') or die;
require_once ("{$this->layouts_path}/head.php");?>

<div id="order_form">
    <div class="container-cart">
        <ul class="container-crumbs">
            <li class="crumbs-no-active"><?=System::Lang('FIRST_YOUR_DATAS');?></li>
            <li class="two-active"><?=System::Lang('SECOND_CART');?></li>
            <li><?=System::Lang('THIRD_PAYMENT_VARIANT');?></li>
        </ul>

        <h2 class="mb-45"><?=System::Lang('CART');?></h2>

        <div class="order_data">
            <div class="offer main">
                <?$total = 0 + $tax;
                $full_price = $total_nds = 0;
                $i = 0;
                foreach($order_items as $item):
                    $product = Product::getProductById($item['product_id']);?>

                    <div class="order_item">
                        <?if($product['product_cover'] != null):?>
                            <div class="order_item-left">
                                <img src="/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
								<?if($product['show_amt'] == 1):?>
								    <p style="text-align: center">Осталось: <?=$product['product_amt'];?></p>
								<?endif;?>
                            </div>
                        <?endif;?>

                        <div class="order_item-desc">
                            <h4 class="product_name"><?=$product['product_name'];?></h4>
                            <?if($product['product_desc'] != null):?>
                                <div class="cart-item-desc"><?=nl2br($product['product_desc']);?></div>
                            <?endif;
                            if($i > 0):?>
                                <a class="order_item-delete" href="?delete_item=1&item_id=<?=$item['order_item_id'];?>">
                                    <span class="icon-remove"></span>Удалить
                                </a>
                            <?endif;?>
                        </div>

                        <div class="order_item-price_box-right">
                            <?if($product['price'] > $item['price']):?>
                                <span class="old_price product-price"><?=$product['price'];?> <?=$this->settings['currency'];?></span>
                            <?endif;?>
                            <div class="font-bold product-price<?=$product['price'] > $item['price'] ? ' red-price' : '';?>"><?=$item['price'];?> <?=$this->settings['currency'];?></div>
                        </div>
                    </div>

                    <?$total += $item['price'];
                    $full_price += $product['price'];
                    $total_nds += $item['nds'];
                    $added_array[] = $item['product_id'];
                    $i++;
                endforeach;?>

                <div class="payment-wrap">
                    <div class="payment-row">
                        <div class="payment-left">
                            <div class="blue-color"><?=System::Lang('ORDER_NUMBER');?> <?=$order_date;?></div>
                            <?if(!isset($hide_cl_email) || !$hide_cl_email):
                                $order_info = unserialize(base64_decode($order['order_info']));?>
                                <div><?=$order['client_name']; if(!empty($order_info['surname'])) echo ' '.$order_info['surname'];?> (<?=$order['client_email'];?>)</div>
                                <?if($order['client_phone']):?>
                                    <div>Телефон: <?=$order['client_phone'];?></div>
                                <?endif;?>
                            <?endif;?>
                        </div>

                        <div class="payment-right">
                            <div class="payment-right-inner">
                                <div class="payment-right-inner__item order-sum">
                                    <div class="payment-right-inner__subtitle"><?=System::Lang('SUMM_ORDER');?></div><strong><?=$full_price; ?> <?=$this->settings['currency'];?></strong>
                                </div>
                                <?if($full_price > $total):?>
                                    <div class="payment-right-inner__item order-discount">
                                        <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div><strong class="color-red"><?=$full_price - $total;?> <?=$this->settings['currency'];?></strong>
                                    </div>
                                <?endif;?>
                            </div>

                            <div class="payment-itogo__total"><div class="payment-right-inner__subtitle"><?=System::Lang('RESULT');?></div><?=$total; ?> <?=$this->settings['currency'];?>
                                <?if ($currency_list) {
                                    foreach ($currency_list as $currency) {?>
                                        <span> | <?=$total * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                    <?}
                                };?>
                            </div>

                            <?if($this->settings['nds_enable'] > 0):?>
                                <div style="text-align: right;">в т.ч. НДС <?="{$total_nds} {$this->settings['currency']}";?></div>
                            <?endif;

                            if($need_delivery):?>
                                <p class="mt-25">
                                    <a class="btn-blue d-block text-center ok_continue" href="/delivery/<?=$order_date;?>"><?=System::Lang('OK_CONTINUE');?></a>
                                </p>
                            <?php else:?>
                                <p class="mt-25">
                                    <a class="btn-blue d-block text-center ok_continue" href="/pay/<?=$order_date;?>"><?=System::Lang('OK_CONTINUE');?></a>
                                </p>
                            <?endif;?>
                        </div>
                    </div>
                </div>
            </div>

            <?foreach ($related_products as $related) {
                $rel_array[] = $related['product_id'];
                $res = array_diff($rel_array, $added_array);
            }

            if(!empty($res)):?>
                <h3>Добавить со скидкой в корзину</h3>
            <?endif;?>

            <?php foreach($related_products as $related):
                if(in_array($related['product_id'], $added_array)) {
                    continue;
                }

                $product = Product::getProductById($related['product_id']);
                $product_link = $product['external_landing'] == 1 ? $product['external_url'] : "/catalog/{$product['product_alias']}";?>

                <div class="related-offer">
                    <div class="order_item">
                        <?if($product['product_cover'] != null):?>
                            <div class="order_item-left">
                                <img src="/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
                            </div>
                        <?endif;?>

                        <div class="order_item-desc">
                            <h4 class="product_name"><?=$product['product_name'];?></h4>
                            <div class="cart-item-desc">
                                <?=$related['offer_desc'] != null ? $related['offer_desc'] : $product['product_desc']?>
                            </div>

                            <div class="order_item-price_box">
                                <?if(Price::getOnlyNDSPrice($product['price']) > Price::getOnlyNDSPrice($related['price'])):?>
                                    <p><?=System::Lang('COAST');?> <span class="old_price product-price"><?=Price::getOnlyNDSPrice($product['price']);?> <?=$this->settings['currency'];?></p></span>
                                    <p><strong><?=System::Lang('SET_ORDER');?> <span class="color-red product-price"><?=Price::getOnlyNDSPrice($related['price']);?> <?=$this->settings['currency'];?></span></strong>
                                        <?if($currency_list) {
                                            foreach($currency_list as $currency){?>
                                                <span> | <?=round($total * $currency['tax']);?> <?=$currency['simbol'];?></span>
                                            <?}
                                        };?>
                                    </p>
                                <?else:?>
                                    <p><?=System::Lang('SET_COAST_PAYMENT');?> <?=Price::getOnlyNDSPrice($related['price']);?> <?=$this->settings['currency'];?>
                                        <?if($currency_list) {
                                            foreach ($currency_list as $currency) {?>
                                                <span> | <?=round($total * $currency['tax']);?> <?=$currency['simbol'];?></span>
                                            <?}
                                        }?>
                                    </p>
                                <?endif;?>
                            </div>
                            <style>
                            @media screen and (max-width: 640px), only screen and (max-device-width:640px) {
                                .add_offer-form {flex-direction: column}
                                .order_item-readmore {padding-top:10px}
                            }
                            </style>
                            <form class="add_offer-form" action="" method="POST">
                                <? /* Комплектация - HTML блок
                                    <div class="complect">
                                        <h5 class="complect-name">Комплектация - HTML блок</h5>
                                        <div class="complect-row">
                                            <label class="custom-radio">
                                                <input name="complect" type="radio">
                                                <span>VIP (7900 р. <strong>5450 р.</strong>)</span>
                                            </label>
                                            <label class="custom-radio">
                                                <input name="complect" type="radio">
                                                <span>Стандарт (7900 р. <strong>5450 р.</strong>)</span>
                                            </label>
                                        </div>
                                    </div>
                                    */ ?>
                                <input type="hidden" name="offer_id" value="<?=$related['id'];?>">
                                <input class="btn-green" type="submit" name="add_offer" value="Добавить к заказу">
                                <a href="<?=$product_link;?>" target="_blank" class="order_item-readmore"><?=System::Lang('MORE');?></a>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>