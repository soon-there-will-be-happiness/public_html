<?defined('BILLINGMASTER') or die;?>

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
                <td class="text-right">
                    <?=$orders['child_email'] ?>
                </td>
                <?$order_items = Order::getOrderItems($orders['id_order']);
                $all_product="";
                foreach($order_items as  $item ) {
                    $product = Product::getProductDataForSendOrder($item['product_id']);
                    $all_product.=", "+$product['product_name'];
                }?>
                <td class="text-right">
                    <?=$all_product ?>
                </td>
            </tr>
            <?endforeach;endif;?>
            <?$parent = ToChild::searchByChild($user['email']);
            if($parent!=false):
            foreach($parent as $orders ):?>
            <tr>
                <td class="text-right">
                    <?=$orders['client_email'] ?>
                </td>
                <?$order_items = Order::getOrderItems( $orders['id_order']);
                $all_product="";
                foreach($order_items as  $item ) {
                    $product = Product::getProductDataForSendOrder($item['product_id']);
                    $all_product.=", "+$product['product_name'];
                }?>
                <td class="text-right">
                    <?=$all_product ?>
                </td>
            </tr>
            <?endforeach;endif;?>
        </table>
    </div>
</div>