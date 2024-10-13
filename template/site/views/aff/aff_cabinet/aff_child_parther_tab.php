<?defined('BILLINGMASTER') or die;?>
<!-- 4 Партнёрские заказы -->
<div>
    <div class="table-responsive">
        <p><?=System::Lang('SHOW');?> <a href="/lk/aff"<?if(!isset($_GET['all'])) echo ' style="font-weight:bold"'?>><?=System::Lang('ONLY_PAID');?></a> | <a href="/lk/aff?all"<?if(isset($_GET['all'])) echo ' style="font-weight:bold"'?>><?=System::Lang('SHOW_ALL');?></a></p>
        <table class="usertable fz-14">
            <tr>
                <th>Email родителя или ребенка</th>
                <th>Продукты</th>         
        
            </tr>

           <?
            $status = isset($_GET['all']) ? 'all' : 'pay';

            $child = ToChild::searchByParent($user['email']);
            if($child):
                foreach($orders as $child):
                ?>
                    <tr>
                        <td class="text-right">
                            <?=$orders['child_email'] ?>
                        </td>
                        <?
                            $order_items = self::getOrderItems($order['id_order']);
                            $all_product="";
                            foreach($order_items as $item) {
                                $product = Product::getProductDataForSendOrder($item['product_id']);
                                $all_product.=", "+$product['product_name'];
                            }

                        ?>
                        <td class="text-right">
                            <?=$all_product ?>
                        </td>
                    </tr>
                <?endforeach;
            endif;?>
       <?     $parent = ToChild::searchByChild($user['email']);
            if($parent):
                foreach($orders as $parent):
                ?>
                    <tr>
                      
                        <td class="text-right">
                            <?=$orders['client_email'] ?>
                        </td>
                      
                        <?
                            $order_items = self::getOrderItems($order['id_order']);
                            $all_product="";
                            
                            foreach($order_items as $item) {
                                $product = Product::getProductDataForSendOrder($item['product_id']);
                                $all_product.=", "+$product['product_name'];
                            }

                        ?>
                        <td class="text-right">
                            <?=$all_product ?>
                        </td>
                    </tr>
                   <?endforeach;
            endif;?>
        </table>

        <p class="text-right"><?='Итого: '.$total_summ_orders;?> <?=$this->settings['currency'];?></p>
    </div>
</div>