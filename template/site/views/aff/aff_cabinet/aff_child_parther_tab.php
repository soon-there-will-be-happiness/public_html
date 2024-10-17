<?defined('BILLINGMASTER') or die;
 $setting = System::getSetting();
?>

<div>
    <div class="table-responsive">
        <table class="usertable fz-14">
            <tr>
                <th>Email родителя или ребенка</th>
                <th>Продукты</th>
            </tr>
            <?$status = isset($_GET['all']) ? 'all' : 'pay';
            $child = ToChild::searchByParent($user['email']);
            if($child!=false):
            foreach( $child as $orders):?>
            <tr>
                <td class="text">
                    <?if($orders['child_email']!=null):?>
                    <?=$orders['child_email'] ?>
                    <?else:?>
                        <form class="table-form-input" action="" method="POST">
                            <input type="text" id ="child" name="child" class="link_input" value="">
                            <input style="display:none;" type="hidden" name="id_order" id="id_order" value="<?=$orders['id_order']?>">
                            <button type="submit"  name="addchild">Отправить</button>
                        </form>
                    <?endif;?>
                </td>
                <?$order_items = Order::getOrderItems($orders['id_order']);
                $all_product="";
                foreach($order_items as  $item ) {
                    $product = Product::getProductDataForSendOrder($item['product_id']);
                    if(  $all_product!="")
                    $all_product.=", ".$product['product_name'];
                    else
                    $all_product.=$product['product_name'];

                }?>
                <td class="text">
                    <?=$all_product ?>
                </td>
                <?if($orders['child_email']==null):?>
                <td>
                    Ссылка на регистрацию
                <?=$setting['script_url'].'/lk/registration?o='.$order_id; ?>
                </td>
                <?endif;?>
            </tr>
            <?endforeach;endif;?>
            <?$parent = ToChild::searchByChild($user['email']);
            if($parent!=false):
            foreach($parent as $orders ):?>
            <tr>
                <td class="text">
                    <?=$orders['client_email'] ?>
                </td>
                <?$order_items = Order::getOrderItems( $orders['id_order']);
                $all_product="";
                foreach($order_items as  $item ) {
                    $product = Product::getProductDataForSendOrder($item['product_id']);
                    if(  $all_product!="")
                    $all_product.=", ".$product['product_name'];
                    else
                    $all_product.=$product['product_name'];
                }?>
                <td class="text">
                    <?=$all_product ?>
                </td>
            </tr>
            <?endforeach;endif;?>
        </table>
    </div>
</div>