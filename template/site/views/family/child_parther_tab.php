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
                            <form class="table-form-input" action="" method="POST" style="display: flex; align-items: center;">
                                <input type="text" id="child_email" name="child_email" class="link_input" value="">
                                <input type="hidden" name="order_id" value="<?= $child['id_order'] ?>">
                                <button type="submit" class="button link_input" name="addchild">Отправить</button>
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
                    <td class="text middle">
                        <?= $product_list ?>
                    </td>
                    <?php if ($child['child_email'] === null): ?>
                        <td class='middle'>
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
                    <td class="text child">
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
                    <td class="text middle">
                        <?= $product_list ?>
                    </td>
                </tr>
                <?php endforeach; 
            endif; ?>
        </table>
    </div>
</div>

<style>
    [name="addchild"] {
        background: none;
        border: none;
        height: 20px;
        padding: 11px;
        cursor: pointer;
        color: #007BFF;
        font-size: 16px;
        display: flex;
        align-items: center; /* Вертикальное выравнивание */
        justify-content: center;
    }
    input[type="text"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #A29595; /* #736868; */
      border-radius: 10px;
      font-size: 14px;
    }
    .link_input {
        width: 200px !important;  
    }
    .middle {
        padding: 0 10px !important;
        text-align: center !important; 
        vertical-align: middle !important;
    }
    .child {
        padding: 30px 10px !important;
    }
    .text.middle {
        text-align: left !important;
        padding: 0 10px 0 0 !important;
    }
</style>
