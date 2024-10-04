<?defined('BILLINGMASTER') or die;?>

<div id="order_form">
    <div class="container-cart">
        <ul class="container-crumbs container-crumbs-two-steps ">
            <li class="first-active"><span>1</span><?=System::Lang('YOUR_DATES');?></li>
            <li><span>2</span><?=System::Lang('PAYMENT_OPTION');?></li>
        </ul>

        <h2 class="mb-45"><?=System::Lang('VARIANT_OF_DELIVERY_AND_PAYMENT');?></h2>

        <div class="order_data">
            <?if(isset($message)){?>
                <div class="success_message success_message-alert"><?=$message;?></div>
            <?}?>
            <h3><?=System::Lang('ORDER_DATES');?> <?=$order_date;?></h3>

            <div class="offer main">
                <?$total = $total_nds = $full_price = 0;
                foreach($order_items as $item):
                    $product = Product::getProductById($item['product_id']);
                    $price = Price::getPriceinCatalog($item['product_id']);
                    $full_price += $price['price'];
                    $total += $item['price'];
                    $total_nds += $item['nds'];?>

                    <div class="order_item">
                        <div class="order_item-desc">
                            <h4 class="product_name"><?=$item['product_name'];?></h4>
                            <?if($product && $product['product_desc'] != null):?>
                                <div class="cart-item-desc"><?=nl2br($product['product_desc']);?></div>
                            <?endif;?>
                        </div>

                        <div class="order_item-price_box-right">
                            <?if($price['price'] > $price['real_price']):?>
                                <span class="old_price"><?=$price['price'];?> <?=$this->settings['currency'];?></span>
                            <?endif;?>
                            <div class="font-bold"><?="{$price['real_price']} {$this->settings['currency']}";?></div>
                        </div>
                    </div>
                <?endforeach;?>

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
                            endif;?>
                        </div>

                        <div class="payment-right">
                            <div class="payment-right-inner">
                                <div class="payment-right-inner__item order-sum">
                                    <div class="payment-right-inner__subtitle"><?=System::Lang('SUMM_ORDER');?></div>
                                    <strong><?=$full_price;?> <?=$this->settings['currency'];?></strong>
                                </div>

                                <?if($full_price > $total):?>
                                    <div class="payment-right-inner__item order-discount">
                                        <div class="payment-right-inner__subtitle"><?=System::Lang('DISCOUNT');?>:</div>
                                        <strong class="color-red"><?=$full_price - $total;?> <?=$this->settings['currency'];?></strong>
                                    </div>
                                <?endif;?>

                                <div class="payment-itogo__total">
                                    <div class="payment-right-inner__subtitle"><?=System::Lang('RESULT');?></div>
                                    <?="{$total} {$this->settings['currency']}";?>
                                </div>

                                <?if($this->settings['nds_enable'] > 0){?>
                                    <div style="text-align: right;">в т.ч. НДС <?="{$total_nds} {$this->settings['currency']}";?></div>
                                <?}?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="" method="POST">
            <div class="payments_list">
                <h3><?=System::Lang('DELIVERY_VARIANTS');?></h3>

                <div class="offer main">
                    <?if($delivery_methods):
                        foreach($delivery_methods as $method):
                            $data_input = 'onchange="delivery_when_pay(' . @ $method['when_pay'] . ')"';?>

                            <div class="payment_item">
                                <div class="delivery_radio">
                                    <label class="custom-radio" for="payment_<?=$method['method_id'];?>">
                                        <input type="radio" id="payment_<?=$method['method_id'];?>" 
                                        name="method" value="<?=$method['method_id'];?>"  <?=$data_input?>>
                                        <span><?=$method['title'];?></span>
                                    </label>
                                </div>

                                <div class="payment_img font-bold">
                                    <?if($method['tax'] != 0):?>+ <?=$method['tax'];?> <?=$this->settings['currency'];?><?endif;?>
                                </div>

                                <div class="payment_desc">
                                    <?=$method['ship_desc'];?>
                                </div>
                            </div>
                        <?endforeach;
                    endif;?>
                </div>
            </div>

            <div class="payments_list">
                <h3><?=System::Lang('PAYMENT_OPTION');?></h3>
                <div class="offer main">
                    <p><label><?=System::Lang('CHOOSE_PAYMENT');?></label></p>

                    <div class="payments_list-row">
                        <div>
                            <div class="select-wrap">
                                <select name="pay" id="pay_select-_" style="width: 236px;">
                                    <option value="1" id="pay_value-1"><?=System::Lang('PAY_NOW');?></option>
                                    <option value="0" id="pay_value-0"><?=System::Lang('PAY_WHEN_RESIVING');?></option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <input type="hidden" name="total" value="<?=$total;?>">
                            <input class="btn-blue-small" type="submit" name="delivery_ok" value="<?=System::Lang('CONTINUE');?>">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    function delivery_when_pay(type) {
        if(type == 1){
            $('#pay_select-_').val("1");
            $('#pay_value-').addClass('hidden');
            $('#pay_value-1').removeClass('hidden');
            $('#pay_value-0').addClass('hidden');
        }else
        if(type == 2){
            $('#pay_select-_').val("0");
            $('#pay_value-').addClass('hidden');
            $('#pay_value-1').addClass('hidden');
            $('#pay_value-0').removeClass('hidden');
        }else{
            $('#pay_value-').removeClass('hidden');
            $('#pay_value-1').removeClass('hidden');
            $('#pay_value-0').removeClass('hidden');
        }
    }
</script>

<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function () {
      $('.success_message').fadeOut('fast')
    }, 4000);
  });
</script>