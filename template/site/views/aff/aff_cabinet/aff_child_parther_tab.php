<?php defined('BILLINGMASTER') or die;
$settings = System::getSetting();
?>

<div>
    <div class="table-responsive">
        <table class="usertable fz-14">
            <tr>
                <th>Email родителя или ребенка</th>
                <th>Продукты</th>
            </tr>
            <?php 
            $status = isset($_GET['all']) ? 'all' : 'pay';
            $children = ToChild::searchByParent($user['email']);
            if ($children !== false):
                foreach ($children as $child): ?>
                <tr>
                    <td class="text">
                        <?php if ($child['child_email'] !== null): ?>
                            <?= $child['child_email'] ?>
                        <?php else: ?>
                            <form class="table-form-input" action="" method="POST">
                                <input type="text" id="child_email" name="child_email" class="link_input" value="">
                                <input type="hidden" name="order_id" value="<?= $child['id_order'] ?>">
                                <button type="submit" name="addchild">Отправить</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <?php 
                    $order_items = Order::getOrderItems($child['id_order']);
                    $product_list = "";
                    foreach ($order_items as $order_item) {
                        $product = Product::getProductDataForSendOrder($order_item['product_id']);
                        $product_list .= ($product_list !== "" ? ", " : "") . $product['product_name'];
                    }
                    ?>
                    <td class="text">
                        <?= $product_list ?>
                    </td>
                    <?php if ($child['child_email'] === null): ?>
                        <td>
                            Ссылка на регистрацию
                            <?= $settings['script_url'] . '/lk/registration?o=' . $child['id_order']; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; 
            endif; ?>

            <?php 
            $parents = ToChild::searchByChild($user['email']);
            if ($parents !== false):
                foreach ($parents as $parent): ?>
                <tr>
                    <td class="text">
                        <?= $parent['client_email'] ?>
                    </td>
                    <?php 
                    $order_items = Order::getOrderItems($parent['id_order']);
                    $product_list = "";
                    foreach ($order_items as $order_item) {
                        $product = Product::getProductDataForSendOrder($order_item['product_id']);
                        $product_list .= ($product_list !== "" ? ", " : "") . $product['product_name'];
                    }
                    ?>
                    <td class="text">
                        <?= $product_list ?>
                    </td>
                </tr>
                <?php endforeach; 
            endif; ?>
        </table>
    </div>
</div>
