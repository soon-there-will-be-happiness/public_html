<?defined('BILLINGMASTER') or die;?>
<!-- 4 Партнёрские заказы -->
<div>
    <div class="table-responsive">
        <p><?=System::Lang('SHOW');?> <a href="/lk/aff"<?if(!isset($_GET['all'])) echo ' style="font-weight:bold"'?>><?=System::Lang('ONLY_PAID');?></a> | <a href="/lk/aff?all"<?if(isset($_GET['all'])) echo ' style="font-weight:bold"'?>><?=System::Lang('SHOW_ALL');?></a></p>
        <table class="usertable fz-14">
            <tr>
                <th><?=System::Lang('NUMBER');?></th>
                <th><?=System::Lang('PRODUCT');?></th>
                <th><?=System::Lang('CREATION');?></th>
                <th><?=System::Lang('CLIENT_NAME');?></th>
                <th><?=System::Lang('CLIENT_EMAIL');?></th>
                <th><?=System::Lang('SUMM_INVOICE');?></th>
                <th><?=System::Lang('PROMO');?></th>
                <th><?=System::Lang('PAID_OUT');?></th>
                <th><?=System::Lang('EARNED');?></th>
            </tr>

           <?
            $status = isset($_GET['all']) ? 'all' : 'pay';
            $orders = Aff::getPartnersOrders($userId, $status, $paid);
            if($orders):
                foreach($orders as $nopay):?>
                    <tr>
                        <td class="nowrap"><?=$nopay['order_date'];?><br />
                            <span class="small"><?=System::Lang('ID');?>: <?=$nopay['order_id'];?></span>
                        </td>

                        <td style="white-space: break-spaces"><?php
                            $product_name = Product::getProductName($nopay['product_id']);
                            echo $product_name['product_name'];?>
                        </td>

                        <td><?if($nopay['order_date'] != null) echo date("d.m.Y H:i:s", $nopay['order_date']);?></td>
                        <!-- +KEMSTAT-18 -->
                        <td>
                            <?php
                                $user = User::getUserDataByEmail($nopay['client_email']);
                                if(!empty($user)) {
                                    echo $user['user_name'] . ' ' . $user['surname'];
                                } else {
                                    echo 'Не найден';
                                }
                            ?>
                        </td>
                        <!-- -KEMSTAT-18 -->
                        <td><?=$params['params']['hidden_email'] == 1 ? System::hideEmail($nopay['client_email']) : $nopay['client_email'];?>
                            <?if($nopay['partner_id'] != $userId) echo '<br> 2 или 3 уровень';?>
                        </td>

                        <td><?=$nopay['summ']>0 ? $nopay['summ'].' '.$this->settings['currency'] : 'Бесплатно';?></td>

                        <td><?if(!empty($nopay['sale_id'])) {
                                $sale = Product::getSaleData($nopay['sale_id']);
                                if ($sale['type'] == 2) {
                                    echo $sale['promo_code'];
                                }
                            }?>
                        </td>

                        <td class="nowrap">
                            <?if($nopay['status'] == 1 && $nopay['summ']>0):?>
                                <div class="partner-pay" title="Оплачено">
                                    <i class="icon-dollar-green"></i>
                                    <span><?=date("d.m.Y", $nopay['payment_date']);?><br><?=date("H:i:s", $nopay['payment_date']);?></span>
                                </div>
                            <?elseif($nopay['status'] == 1 && $nopay['summ'] == 0):?>
                                <span style="color:green">Получен</span>
                            <?elseif($nopay['status'] == 9):?>
                                <div class="partner-pay" title="Возврат">
                                    <i class="icon-dollar-red"></i>
                                    <span><?=date("d.m.Y", $nopay['payment_date']);?><br><?=date("H:i:s", $nopay['payment_date']);?></span>
                                </div>
                            <?else:?>
                                <span style="color:orange">Не оплачен</span>
                           <?endif;?>
                        </td>

                        <td class="text-right">
                            <?=$nopay['trans_summ'] > 0 ? "{$nopay['trans_summ']} {$this->settings['currency']}" : '---';?>
                        </td>
                    </tr>
                   <?endforeach;
            endif;?>

           <?$total_summ_orders = 0;
            if($orders):
                foreach($orders as $transact):
                    if ($transact['product_id']!=33):?>
                        <?$total_summ_orders = $total_summ_orders + $transact['summ'];
                    endif;
                endforeach;
            endif;?>
        </table>

        <p class="text-right"><?='Оборот: '.$total_summ_orders;?> <?=$this->settings['currency'];?></p>
    </div>
</div>