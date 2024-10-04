<?php defined('BILLINGMASTER') or die;
$is_prepayment = $installment_data['max_periods'] == 2;
$full_price = $total_nds = 0;?>

<div id="order_form">
    <div class="container-cart">
        <ul class="container-crumbs<?=$related_products && $this->settings['use_cart'] == 0 ? '' : ' container-crumbs-two-steps'?>">
            <li class="crumbs-no-active crumbs-order"><span>1</span><?=System::Lang('YOUR_DATES');?></li>
            <li class="three-active"><span><?=$related_products && $this->settings['use_cart'] == 0 ? '3' : '2'?></span><?= $is_prepayment ? System::Lang('PREPAYMENT') : System::Lang('INSTALLMENT_APPLICATION');?></li>
        </ul>

        <h2><?= $is_prepayment ? System::Lang('PREPAYMENT') : System::Lang('REQUEST');?></h2>

        <div class="order_data">
            <h3><?=System::Lang('ORDER_DATES');?> <?=$order_date;?></h3>

            <div class="offer main offer-mb-35">
                <?foreach($order_items as $item):?>
                    <div class="order_item">
                        <?php $product = Product::getMinProductById($item['product_id']);
                        if ($product['installment'] > 0) {
                            $installment = $product['installment'];
                        }

                        if($product['product_cover']!= null):?>
                            <div class="order_item-left">
                                <img src="/images/product/<?=$product['product_cover'];?>" alt="">
                            </div>
                        <?endif;?>

                        <div class="order_item-desc">
                            <h4 class="product_name"><?=$item['product_name'];?></h4>
                            <?if($product['product_desc'] != null):?>
                                <div class="product_desc"><?=nl2br($product['product_desc']);?></div>
                            <?endif;?>
                        </div>

                        <div class="order_item-price_box-right">
                            <?if($product['price'] > $item['price']):?>
                                <span class="old_price product-price"><?=$product['price'];?> <?=$this->settings['currency'];?></span>
                                <div class="font-bold product-price red-price"><?=$item['price'];?> <?=$this->settings['currency'];?></div>
                            <?else:?>
                                <div class="font-bold product-price"><?=$item['price'];?> <?=$this->settings['currency'];?></div>
                            <?endif;?>
                        </div>
                    </div>

                    <?$full_price += Price::getOnlyNDSPrice($product['price']);
                    $total_nds += $item['nds'];
                endforeach; ?>

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

                            if($tax != 0):?>
                                <div><?=System::Lang('DELIVERY');?> <?=$tax;?> <?=$this->settings['currency'];?></div>
                            <?endif;?>
                        </div>

                        <div class="payment-right">
                            <div class="payment-right-inner">
                                <div class="payment-right-inner__item order-sum">
                                    <div class="payment-right-inner__subtitle"><?=System::Lang('ORDER_SUMM_TAG');?>:</div>
                                    <strong><?="{$full_price} {$this->settings['currency']}";?></strong>
                                </div>

                                <?if($full_price > $total):?>
                                    <div class="payment-right-inner__item order-discount">
                                        <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div>
                                        <strong class="color-red"><?=$full_price - $total;?> <?=$this->settings['currency'];?></strong>
                                    </div>
                                <?endif;?>
                            </div>

                            <div class="payment-itogo__total">
                                <div class="payment-right-inner__subtitle"><?=System::Lang('RESULT');?></div>
                                <?="$total {$this->settings['currency']}";?>
                            </div>

                            <?if($this->settings['nds_enable'] > 0){?>
                                <div>в т.ч. НДС <?="{$total_nds} {$this->settings['currency']}";?></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="installment_pay mt-35">
                <h3><?= $is_prepayment ? System::Lang('PREPAYMENT') : System::Lang('INSTALLMENTS_PAYMENT');?></h3>
                <?if(!$installment_data) {
                    exit('Error Installment');
                }

                $first_pay = round(($total / 100) * $installment_data['first_pay']);
                $other_pay = round(($total / 100) * $installment_data['other_pay']);

                $p = 2;
                $m = 1;?>

                <div class="offer main">
                    <div class="install_item">
                        <h4 class="tabs-payment-subtitle"><?=$installment_data['title'];?></h4>
                        <?php $increase_pay = $installment_data['increase'] > 0 ? $installment_data['increase'] / $installment_data['max_periods'] : 0; ?>

                        <table class="install_item-table">
                            <tr>
                                <th><?=System::Lang('PAYMENT_NUMBER');?></th>
                                <th><?=System::Lang('PAYMENT_DATE');?></th>
                                <th class="install_item-table__last"><?=System::Lang('SUMMCLEAN');?></th>
                            </tr>

                            <tr>
                                <td>1</td>
                                <td><?=System::Lang('TODAY');?></td>
                                <td class="install_item-table__last"><?=round($first_pay + $increase_pay);?> <?=$this->settings['currency'];?></td>
                            </tr>

                            <?php while($installment_data['max_periods'] >= $p):
                                $pay_date = Installment::getNextPayDate($installment_data, $now, $installment_data['date_second_payment'], $m++);?>
                                <tr>
                                    <td><?=$p++?></td>
                                    <td><?=date("d.m.Y", $pay_date);?></td>
                                    <td class="install_item-table__last"><?=round($other_pay + $increase_pay);?> <?=$this->settings['currency'];?></td>
                                </tr>
                            <?php endwhile;?>
                        </table>

                        <p class="install_item__last-block"><?= $is_prepayment ? System::Lang('PREPAYMENT_COAST') : System::Lang('INSTALLMENT_COAST');?> <?=$installment_data['increase'];?> <?=$this->settings['currency'];?></p>
                        <p class="install_item__last-block"><strong><?=$is_prepayment ? System::Lang('RESULT') : System::Lang('INSTALLMENT_COAST_SUMM');?> <?=$installment_total;?> <?=$this->settings['currency'];?></strong></p>

                        <div class="short_rules">
                            <?=$installment_data['installment_desc'];?>
                        </div>
                    </div>

                    <?php $fields = unserialize(base64_decode($installment_data['fields']));?>
                    <div class="user_data">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <?php if (!$is_prepayment) { ?>
                                <p>
                                    <input type="text" name="name" placeholder="<?= System::Lang('YOUR_NAME') ?>" required="required" value="<?= $order['client_name'] ?? "" ?>">
                                </p>

                                <?php if (!isset($fields['surname']) ||  @$fields['surname'] != 0) { ?>
                                    <p>
                                        <input type="text" name="soname" placeholder="<?= System::Lang('YOUR_SURNAME') ?>" <?= @$fields['surname'] == 2 ? 'required="required"' : ""?> value="<?= $order['surname'] ?? "" ?>">
                                    </p>
                                <?php } ?>

                                <?php if (!isset($fields['patronymic']) ||  @$fields['patronymic'] != 0) { ?>
                                    <p>
                                        <input type="text" name="otname" placeholder="<?= System::Lang('YOUR_PATRONYMIC') ?>" <?= @$fields['patronymic'] == 2 ? 'required="required"' : ""?> value="<?= $order['patronymic'] ?? "" ?>">
                                    </p>
                                <?php } ?>

                                <?if($fields['passport'] > 0):?>
                                    <p>
                                        <input type="text" name="passport" placeholder="<?= System::Lang('PASSPORT_NUM') ?>" <?= $fields['passport'] == 2 ? 'required="required"' : ""?>>
                                    </p>
                                <?endif;?>

                                <p>
                                    <input type="email" name="email" disabled="disabled" placeholder="Email" value="<?= $order['client_email'] ?>">
                                </p>

                                <?php if (!isset($fields['phone']) ||  @$fields['phone'] != 0) { ?>
                                    <p>
                                        <input type="text" name="phone" placeholder="<?= !$this->settings['countries_list'] ? System::Lang('CLIENT_PHONE') : '' ?>" <?= @$fields['phone'] == 2 ? 'required="required"' : ""?> value=" <?= $order['client_phone'] ?>">
                                    </p>
                                <?php } ?>

                                <?if($fields['address'] > 0):?>
                                    <p>
                                        <input type="text" name="city" <?= $fields['address'] == 2 ?'required="required"' : ""?> placeholder="<?= System::Lang('CITY') ?>">
                                    </p>
                                    <p>
                                        <textarea name="address" <?= $fields['address'] == 2 ? 'required="required"' : "" ?> placeholder="<?= System::Lang('ADDRESS') ?>"></textarea>
                                    </p>
                                <?endif;?>

                                <?if($fields['skan1'] > 0):?>
                                    <p>
                                        <span class="scan-text"><?=System::Lang('PASSPORT_SCAN');?></span>
                                        <input type="file" name="skan" <?if($fields['skan1'] == 2) echo 'required="required"';?>>
                                    </p>
                                <?endif;?>

                                <?if($fields['skan2'] > 0):?>
                                    <p>
                                        <span class="scan-text"><?=System::Lang('PASSPORT_SCAN_REG');?></span>
                                        <input type="file" name="skan2" <?if($fields['skan2'] == 2) echo 'required="required"';?>>
                                    </p>
                                <?endif;?>

                                <?php if (!$is_prepayment) { ?>
                                    <div>
                                        <label class="check_label">
                                            <input checked type="checkbox" required="">
                                            <span class="installment-agree-terms"><a href="/installment/rules/<?=$installment_id;?>" target="_blank"><?=System::Lang('LINK_AGREE');?></a> <?=System::Lang('LINK_AGREE2');?></span>
                                        </label>
                                    </div>
                            <? } } else { ?>
                                <?php $info = unserialize(base64_decode($order['order_info']));?>
                                <style>
                                    .intl-tel-input {
                                        display: none !important;
                                    }
                                </style>
                                <input type="hidden" name="name" value="<?=$order['client_name'];?>">
                                <input type="hidden" name="soname" value="<?=$info['surname'];?>">
                                <input type="hidden" name="otname" value="<?=$info['patronymic']?>">
                                <input type="hidden" name="email" value="<?=$order['client_email'];?>">
                                <input type="hidden" name="city" value="<?=$order['client_city'];?>">
                                <input type="hidden" name="address" value="<?=$order['client_address'];?>">
                            <?php } ?>

                            <input type="hidden" name="order_date" value="<?=$order['order_date'];?>">
                            <input type="hidden" name="install_id" value="<?=$installment_id;?>">
                            <input type="hidden" name="install_title" value="<?=$installment_data['title'];?>">

                            <div class="payment-submir-wrap">
                                <button type="submit" name="go_installment" class="btn-green-small"><?= $is_prepayment ? System::Lang('PREPAYMENT_PLAN') :System::Lang('SEND_REQUEST');?></button>
                            </div>
                            <?php if (!$is_prepayment) { ?>
                                <p><span class="small"><?=System::Lang('DEDLINE');?></span></p>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>