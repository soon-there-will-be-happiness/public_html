<?defined('BILLINGMASTER') or die;?>

<div class="pay-page">
    <? // Вывод промо кода
    require_once (__DIR__ . '/../common/show_promo_code.php');?>

    <? // Вывод уведомления CallPassword
    if (CallPassword::isShowButton($user)):
        require_once (ROOT.'/extensions/callpassword/views/show_notice.php');
    endif;

    // Вывод уведомления Telegram
    Connect::showConnectNotice('telegram', $user['user_id'], true);
?>

    <?if(isset($_GET['success'])):?>
        <div class="success_message"><?=System::Lang('LINKS_SEND_TO_EMAIL');?></div>
    <?endif;

    if(isset($_GET['fail'])):?>
        <div class="warning_message"><?=System::Lang('ERROR_PRODUCT');?></div>
    <?endif;

    if($orders):?>
        <div class="pay_orders">
            <h1 class="mb-30"><?=System::Lang('MY_ORDERS');?></h1>

            <div class="table-responsive">
                <table class="pay-table">
                    <tr>
                        <th class="text-left"><?=System::Lang('COUNT_NUMBER');?></th>
                        <th class="text-left"><?=System::Lang('ITEM');?></th>
                        <th><?=System::Lang('PAYMENT_DATE');?></th>
                        <th><?=System::Lang('STATUS');?></th>
                    </tr>
                    
                    <?foreach($orders as $order):
                        $right = 0;?>
                        <tr>
                            <td class="text-left"><?=$order['order_date'];?></td>
                            <td class="text-left"><?$items = Order::getOrderItems($order['order_id']);
                                if ($items):
                                    foreach($items as $item):
                                        $product_data = Product::getProductName($item['product_id']);?>
                                        <h4 class="order-final-title">
                                            <?if ($product_data['group_id']) {
                                                $prod_groups = explode(",", $product_data['group_id']);
                                                foreach($prod_groups as $group){
                                                    if (is_array($user_groups) && in_array($group, $user_groups)) {
                                                        $right++;
                                                    }
                                                }
                                            }
                                            echo "{$product_data['product_name']}{$product_data['mess']}";?>
                                        </h4>

                                        <?if(!empty($item['pincode'])):?>
                                            <div class="payment-info"><?=System::Lang('KEY');?> <?=$item['pincode'];?></div>
                                        <?endif;
                                    endforeach;
                                endif;?>

                                <?php if($item['flow_id']> 0):
                                $flow_data = Flows::getFlowByID($item['flow_id']);?>
                                <div class="payment-info"><?php if($flow_data) echo $flow_data['flow_title']; else echo '- - -';?></div>
                                
                                <?php endif;?>
                                
                                <?if($this->settings['dwl_in_lk'] == 1 && $right != 0 && !empty($product_data['link'])):?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="order" value="<?=$order['order_date'];?>">
                                        <input type="submit" class="btn getlink btn-red" name="getlink" value="Отправить письмо заказа ещё раз">
                                    </form>
                                <?endif;?>
                            </td>

                            <td><?=date("d.m.Y H:i:s", $order['order_date']);?></td>

                            <td>
                                <span class="status-act"><?=System::Lang('ACTIVE');?></span>
                            </td>
                        </tr>
                    <?endforeach;?>
                </table>
            </div>
        </div>
    <?endif;

    $installment_list = Order::searchInstallmentByEmail($user['email']);
    if($installment_list):?>
        <div class="installments">
            <h2 class="mb-30"><?=System::Lang('MY_INSTALLMENTS_AND_PREPAYMENTS');?></h2>

            <?foreach($installment_list as $installment):
                $pay_actions = !empty($installment['pay_actions']) ? unserialize(base64_decode($installment['pay_actions'])) : false;
                $installment_data = Product::getInstallmentData($installment['installment_id']);?>

                <div class="table-responsive">
                    <table class="pay-table">
                        <tr>
                            <th><?=System::Lang('ID');?></th>
                            <th class="text-left"><?=System::Lang('DESCRIPTION');?></th>
                            <th class="text-right"><?=System::Lang('PAIDED');?></th>
                            <th class="text-right"><?=System::Lang('NEXT_PAYMENT');?></th>
                        </tr>

                        <tr>
                            <td><?=$installment['id'];?></td>

                            <td class="text-left">
                                <p><?=System::Lang('SUMM');?> <?echo $summ = $installment['summ']; echo " {$this->settings['currency']}, кол-во платежей: {$installment['max_periods']}";?></p>

                                <?if($installment['status'] == 9):
                                    $summ = $summ + $installment_data['sanctions'];?>
                                    <p class="max-w-280">
                                        <span style="color:red"><?=System::Lang('PAYMENT_DOWN');?>
                                            <?if ($installment_data['sanctions']!= 0) {
                                                echo " (штраф: {$installment_data['sanctions']} {$this->settings['currency']})";
                                            }?>
                                        </span>
                                    </p>
                                <?endif;

                                $pay_summ = 0;
                                if ($pay_actions) {
                                    foreach ($pay_actions as $action) {
                                        $pay_summ = $pay_summ + $action['summ'];
                                    }
                                }?>

                                <p><a class="btn-green" href="/installahead/<?=$installment['id'];?>">Погасить досрочно <?=$summ - $pay_summ.' '.$this->settings['currency'];?></a></p>
                            </td>

                            <td class="text-right">
                                <?$i = 0;
                                if ($pay_actions):
                                    foreach($pay_actions as $action):?>
                                        <p class="sum-paid-item">
                                            <span class="sum-paid"><?=$action['summ']?> <?=$this->settings['currency'];?></span>
                                            <span class="span-paid"><?=date("d.m.Y H:i", $action['date'])?></span>
                                        </p>

                                        <?$i++;
                                    endforeach;
                                else:
                                    echo '---';
                                endif?>
                            </td>

                            <td class="text-right">
                                <div class="next-pay">
                                    <div class="next-pay-price">
                                        <?$num_next_pays = $installment['max_periods'] - $i;
                                        echo ($num_next_pays > 0 ? round(($summ - $pay_summ) / $num_next_pays) : 0) . $this->settings['currency'];?>
                                    </div>
                                    <?if($installment['next_pay']):?>
                                        <span class="font-12"><?=date("d.m.Y H:i", $installment['next_pay']);?></span>
                                    <?else:
                                        echo '---';
                                    endif;

                                    if($installment['status'] == 9):?>
                                        <span class="paid-expired"><?=System::Lang('EXPIRED');?></span>
                                    <?endif;?>
                                </div>

                                <?if($installment['next_order'] != 0){?>
                                    <div class="pay-link-blue">
                                        <a class="link-blue" target="_blank" href="/pay/<?=$installment['next_order'];?>"><?=System::Lang('TO_PAY');?></a>
                                    </div>
                                <?} else {?>
                                    <div class="pay-link-blue">
                                        <a href="/installament/ahead/<?echo $installment['id'];?>"><?=System::Lang('PAY_EARLY');?></a>
                                    </div>
                                <?}?>
                            </td>
                        </tr>
                    </table>
                </div>
            <?endforeach;?>
        </div>
    <?endif;

    if($orders_nopay):?>
        <div class="nopay_orders">
            <h2 class="mb-30"><?=System::Lang('NOT_COMPLITE_ORDERS');?></h2>

            <div class="table-responsive">
                <table class="pay-table nopay">
                    <tr>
                        <th class="text-left"><?=System::Lang('ORDER_NUM_TAG');?></th>
                        <th class="text-left"><?=System::Lang('ORDER_CONTENT');?></th>
                        <th><?=System::Lang('ORDER_DATE');?></th>
                        <th><?=System::Lang('SUMM');?></th>
                        <th><?=System::Lang('STATUS');?></th>
                    </tr>

                    <?foreach($orders_nopay as $ord_nopay):
                        $total = 0;?>
                        <tr>
                            <td class="text-left"><?=$ord_nopay['order_date'];?></td>

                            <td class="text-left">
                                <?$items = Order::getOrderItems($ord_nopay['order_id']);
                                if ($items):
                                    foreach($items as $item):?>
                                        <h5 class="order-title"><?$product_data = Product::getProductName($item['product_id']);
                                            echo "{$product_data['product_name']}{$product_data['mess']}";?>
                                        </h5>

                                        <?$total = $total + $item['price'];
                                    endforeach;
                                endif;?>

                                <div class="pay-move">
                                    <div class="pay-move__button">
                                        <a class="btn-green" target="_blank" href="/pay/<?=$ord_nopay['order_date'];?>"><?=System::Lang('TO_PAY');?></a>
                                        <?if(isset($params['allow_user_to_delete_orders']) && $params['allow_user_to_delete_orders'] == 1):?>
                                            <a class="link-red" onclick="return confirm('Вы уверены?')" href="/cancelpay/<?=$ord_nopay['order_date'];?>?key=<?=md5($ord_nopay['client_email'].':'.$ord_nopay['order_date']);?>"><?=System::Lang('CANCEL');?></a>
                                        <?endif;?>
                                    </div>
                                </div>
                            </td>

                            <td><?=date("d.m.Y", $ord_nopay['order_date']);?><br><?=date("H:i:s", $ord_nopay['order_date']);?></td>

                            <td><?=$total;?> <?=$this->settings['currency'];?></td>

                            <td><span class="status-noact"><?=System::Lang('NOT_PAID');?></span><br><br>
                                <!--div class="status-remove">Отменён</div-->
                            </td>
                        </tr>
                    <?endforeach;?>
                </table>
            </div>
        </div>
    <?endif;

    if(!$orders && !$installment_list && !$orders_nopay) {
        echo System::Lang('NO_ORDERS');
    }?>
</div>