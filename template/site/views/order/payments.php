<?php defined('BILLINGMASTER') or die;?>

<div id="order_form">
    <div class="container-cart">
        <ul class="container-crumbs <?if($related_products && $this->settings['use_cart'] == 0) echo ''; else echo 'container-crumbs-two-steps'?> ">
            <li class="crumbs-no-active crumbs-order"><span>1</span><?=System::Lang('YOUR_DATES');?></li>
            <?if($related_products && $this->settings['use_cart'] == 0):?>
                <li class="crumbs-no-active crumbs-order"><span>2</span>
                    <a href="/related/<?=$order_date;?>">Корзина</a>
                </li>
            <?endif;?>

            <li class="three-active">
                <span><?=$related_products && $this->settings['use_cart'] == 0 ? '3' :'2';?></span><?=System::Lang('PAYMENT_OPTION');?>
            </li>
        </ul>

        <h2><?=System::Lang('REPAYMENT');?></h2>


        <div class="order_data">
            <h3><?=System::Lang('ORDER_DATES');?> <?=$order_date;?></h3>

            <div class="offer main mb-45">
                <?$full_price = $total_nds = 0;
                $is_show_timer = 1;

                foreach($order_items as $item):
                    $product = Product::getMinProductById($item['product_id']);
                    $full_price += $product['price'];
                    $total_nds += $item['nds'];
                    $is_show_timer = $is_show_timer * (int) $product['show_timer'];?>

                    <div class="order_item">
                        <?if($product['product_cover']!= null):?>
                            <div class="order_item-left">
                                <img src="/images/product/<?=$product['product_cover'];?>" alt="">
                                <?php if($product['show_amt'] == 1):?>
                                    <p style="text-align: center">Осталось: <?=$product['product_amt'];?></p>
                                <?php endif;?>
                            </div>
                        <?endif;?>

                        <div class="order_item-desc">
                            <h4 class="cart-item-name"><?=$item['product_name'];?></h4>
                            <?php if($flows && $item['flow_id'] > 0):?>
                            <p><?=$flows_params['order_title'];?> <?php $flow_data = Flows::getFlowByID($item['flow_id']);
                            echo $flow_data['flow_title'];?> <?php if($flow_data['show_period'] == 1){
                                echo date("d.m.Y", $flow_data['start_flow']); echo ' - '.date("d.m.Y", $flow_data['end_flow']);
                            }?></p>
                            <?endif;?>
                            
                            <?if($product['product_desc'] != null):?>
                                <div class="cart-item-desc"><?=nl2br($product['product_desc']);?></div>
                            <?endif;?>
                        </div>

                        <div class="order_item-price_box-right">
                            <?if(isset($item['old_price']) && $order['installment_map_id']):?>
                                <?if($product['price'] > $item['old_price']):?>
                                    <span class="old_price product-price"><?=$product['price'];?> <?=$this->settings['currency'];?></span>
                                <?endif;?>
                                <div class="font-bold product-price<?=$product['price'] > $item['old_price'] ? ' red-price' : '';?>"><?=$item['old_price'];?> <?=$this->settings['currency'];?></div>
                            <?else:
                                if($product['price'] > $item['price']):?>
                                    <span class="old_price product-price"><?=$product['price'];?> <?=$this->settings['currency'];?></span>
                                <?endif;?>
                                <div class="font-bold product-price<?=$product['price'] > $item['price'] ? ' red-price' : '';?>"><?=$item['price'];?> <?=$this->settings['currency'];?></div>
                            <?endif;?>
                        </div>
                    </div>
                <?endforeach;

                if ($order['installment_map_id']) {
                    $order_items = Order::getOrderItems($order['order_id']);
                }?>

                <div class="payment-wrap">
                    <div class="payment-row">
                        <div class="payment-left">
                            <div class="blue-color"><?=System::Lang('ORDER_NUMBER');?> <?=$order_date;?></div>
                            <?if(!isset($hide_cl_email) || !$hide_cl_email):
                                $order_info = unserialize(base64_decode($order['order_info']));?>
                                <div><?=$order['client_name']; if(!empty($order_info['surname'])) echo ' '.$order_info['surname'];?> (<?=$order['client_email'];?>)</div>
                                <?if($order['client_phone']):?>
                                    <div>Телефон: <?=$order['client_phone'];?></div>
                                <?endif;
                            endif;

                            if(isset($is_show_timer) && $is_show_timer > 0):?>
                                <div>
                                    <span><?=System::Lang('ORDER_STILL_BUY');?> </span>&nbsp;
                                    <strong id="clockdiv">
                                        <span class="days"></span>д.
                                        <span class="hours"></span>ч.
                                        <span class="minutes"></span>м.
                                        <span class="seconds"></span>с.
                                    </strong>
                                </div>
                            <?endif;?>
                        </div>

                        <div class="payment-right">
                            <div class="payment-right-inner">
                                <div class="payment-right-inner__item order-sum">
                                    <div class="payment-right-inner__subtitle"><?=System::Lang('ORDER_SUMM_TAG');?>:</div>
                                    <strong><?="{$full_price} {$this->settings['currency']}";?></strong>
                                </div>
                                <?php if ($tax != 0): ?>
                                    <div class="payment-right-inner__item">
                                        <div class="payment-right-inner__subtitle"><?= System::Lang('DELIVERY'); ?></div>
                                        <strong><?= "{$tax} {$this->settings['currency']}"; ?></strong>
                                    </div>
                                <? endif; ?>


                                <?if((!$order['installment_map_id'] && $full_price > $total) || ($order['installment_map_id'] && $order_sum && $full_price > $order_sum)):
                                    if(!$order['installment_map_id']):?>
                                        <div class="payment-right-inner__item order-discount">
                                            <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div>
                                            <strong class="color-red"><?=$full_price - $total;?> <?=$this->settings['currency'];?></strong>
                                        </div>
                                    <?elseif($order_sum && $full_price > $order_sum):?>
                                        <div class="payment-right-inner__item order-discount">
                                            <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div>
                                            <strong class="color-red"><?=$full_price - $order_sum;?> <?=$this->settings['currency'];?></strong>
                                        </div>
                                    <?endif;
                                endif;?>
                            </div>

                            <div class="payment-itogo__total">
                                <div class="payment-right-inner__subtitle"><?=System::Lang('RESULT');?></div>
                                <?=($order['installment_map_id'] && $order_sum ? $order_sum : $total)." {$this->settings['currency']}";?>
                                <?if($currency_list){
                                    foreach($currency_list as $currency){?>
                                        <span> | <?=$total * $currency['tax'];?> <?=$currency['simbol'];?></span>
                                    <?php }
                                };?>
                            </div>

                            <?if($this->settings['nds_enable'] > 0){?>
                                <div>в т.ч. НДС <?="{$total_nds} {$this->settings['currency']}";?></div>
                            <?php } ?>
                            <?if(isset($order['deposit']))://Если это заказ с внесенной предоплатой(SM-1933)
                                $deposits = json_decode($order['deposit'], true);
                                $depositsSum = 0;
                                //Если предоплата в рассрочке
                                $isInstallment = false;
                                if ($order['installment_map_id']) {
                                    $number_pay = Installment::getCountPaysByMapId($order['installment_map_id']) + 1;
                                    $isInstallment = true;
                                }
                            ?>

                                <div style="padding: 32px 0 0 0;">
                                    <div style="padding: 16px 16px; background: rgba(55, 58, 76, 0.03); border-radius: 10px;">
                                        <?php if ($isInstallment) { ?>
                                            <div class="margin-bottom-8"><strong>К оплате</strong></div>
                                            <div class="mt-10 margin-bottom-8">Сумма <?=System::getTextNumber($number_pay);?> платежа: <?="{$total} {$this->settings['currency']}";?></div>
                                        <?php } ?>
                                        <?if($deposits):
                                            foreach ($deposits as $deposit):
                                                $depositsSum += $deposit['sum'];?>
                                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">Внесена предоплата <?=date('j.m.Y', $deposit['time'])?>: <span><?="{$deposit['sum']} {$this->settings['currency']}"?></span></div>
                                            <?endforeach;
                                        endif;?>
                                        <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 12px;">Осталось: <span id="prepaymentRemainingAmount"><?=($remainingSum = $total - $depositsSum)." {$this->settings['currency']}"?></span></div>
                                    </div>
                                </div>
                            <?elseif($order['installment_map_id']):
                                $number_pay = Installment::getCountPaysByMapId($order['installment_map_id']) + 1; ?>
                                <div class="installment_pay-info">
                                    <strong>К оплате</strong>
                                    <div class="mt-10">Сумма <?=System::getTextNumber($number_pay);?> платежа: <?="{$total} {$this->settings['currency']}";?></div>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mt-45"><?=System::Lang('CHOOSE_PAYMENT');?></h3>
            <div class="tabs tabs-payment">
                <?if($order['installment_map_id'] == 0):?>
                    <ul class="tabs-payment-ul">
                        <?if($installment_list || $prepayment_list):?>
                            <li><?=System::Lang('PAYMENT_IMMEDIATELY');?></li>
                        <?endif;

                        if($installment_list):?>
                            <li class="tabs-payment-small-pad"><?=System::Lang('INSTALLMENTS_PAYMENT');?></li>
                        <?endif;

                        if ($prepayment_list):?>
                            <li class="tabs-payment-small-pad"><?=System::Lang('PREPAYMENT');?></li>
                        <?endif;?>
                    </ul>
                <?endif;?>

                <div class="tabs-payment-div">
                    <div>
                        <div class="tabs-payments_list">
                            <div class="payment_item">
                                <div class="offer main">
                                    <?if(isset($_COOKIE['payment_error']) && ($payment_error = json_decode($_COOKIE['payment_error'], true)));
                                    foreach($payments as $payment):
                                        if ($order['installment_map_id'] && $payment['name'] == 'tinkoffinstallments') {
                                            continue;
                                        }
                                        # если платежка вернула ошибку (при backend-backend)  // -> setCookie() -> Location: '{order_payment_link}#*' //
                                        # скрывем метод
                                        if(@ $payment_error && in_array($payment['payment_id'], $payment_error))
                                            continue;

                                        if (isset($plane) && $plane['select_payments'] != null) {
                                            $selected = unserialize(base64_decode($plane['select_payments']));
                                            if(!in_array($payment['payment_id'], $selected)) {
                                                continue;
                                            }
                                        }

                                        // проверить платёжки для организации
                                        if($org_separation) {
                                            $payments_org = json_decode($org_separation['payments'], true);

                                            if($payments_org['cloud']['payment_id'] == $payment['payment_id']){
                                                if($payments_org['cloud']['enable'] != 1) continue;
                                            }

                                            if($payments_org['yookassa']['payment_id'] == $payment['payment_id']){
                                                if($payments_org['yookassa']['enable'] != 1) continue;
                                            }
                                        }
                                        if (isset($order['deposit'])) {//Если это заказ с предоплатой(SM-1933)
                                            $order['summ'] = $total = $remainingSum ?? $total;
                                        }?>

                                        <div class="order_item">
                                            <div class="order_item-left">
                                                <img src="<?=$this->settings['script_url'];?>/payments/<?=$payment['name']?>/<?=$payment['name']?>.png" alt="">
                                            </div>

                                            <div class="order_item-desc">
                                                <?if($payment['public_title'] != null) echo '<h4>'.$payment['public_title'].'</h4>';?>
                                                <?=$payment['payment_desc'];?>
                                            </div>

                                            <div class="payment_button">
                                                <?php require_once (ROOT.'/payments/'.$payment['name'].'/form.php');?>
                                            </div>
                                        </div>
                                    <?endforeach;?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?if($list = $installment_list):?>
                        <div>
                            <div class="installment_pay offer main">
                                <form class="install-form" id="install" action="/installment" method="POST">
                                    <?php require (__DIR__ .'/installment_list.php');?>
                                </form>
                            </div>
                        </div>
                    <?endif;

                    if($list = $prepayment_list):
                        $is_prepaymentList = true;
                        ?>
                        <div>
                            <div class="installment_pay offer main">
                                <form class="install-form" id="prepayment" action="/installment" method="POST">
                                    <?php require (__DIR__ .'/installment_list.php');?>
                                </form>
                            </div>
                        </div>
                    <?endif;?>
                </div>
            </div>
        </div>
    </div>
</div>