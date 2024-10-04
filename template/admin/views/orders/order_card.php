<?php defined('BILLINGMASTER') or die; ?>

<?php
if (!function_exists("OrderStatus") && isset($createfunc)) {
    function OrderStatus($status)
    {
        switch ($status) {
            case 2 :
                $class = ' conf" title="Ручной перевод - нажмите на иконку чтобы подтвердить оплату"';
                break;

            case 0 :
                $class = ' off" title="Не оплачен"';
                break;

            case 7 :
                $class = ' send" title="Подтверждён клиентом"';
                break;

            case 9 :
                $class = ' refund" title="Возврат"';
                break;

            default :
                $class = '"';
        }

        return $class;
    }
}
?>
<tr class=" <?= OrderStatus($order['status']); ?>">
    <td class="text-left" style="width: 100px;">
        <a title="Просмотр заказа" href="/admin/orders/edit/<?= $order['order_id']; ?>"><?= $order['order_date']; ?></a><br/>
        <?= date("d.m.Y H:i", $order['order_date']); ?><br/>
        <?php if (OrderTask::checkErrors2Order($order['order_id'])): ?>
            <span title='Есть ошибки по крон-событиям'
                  style="font-size:80%;color:#ff0000"><?= $order['order_id']; ?></span>
        <?php else: ?>
            <span style="font-size:80%;color:#888"><?= $order['order_id']; ?></span>
        <?php endif; ?>
    </td>
    <td class="text-left" style="word-break: break-all; width: 150px;"><?php $link = User::getUserIDatEmail($order['client_email']);
        $order_info = unserialize(base64_decode(($order['order_info'])));
        if ($link):?>
            <a target="_blank"
               href="/admin/users/edit/<?= $link; ?>"><?= urldecode($order['client_name']); ?>&nbsp;<?php if (isset($order_info['surname'])) echo $order_info['surname']; ?></a>
        <?php else:
            echo $order['client_name'];?>&nbsp;<?php if (isset($order_info['surname'])) echo $order_info['surname']; ?>
        <?php endif; ?>
        <br/><span class="small link-inherit"><?= $order['client_email']; ?></span><br/><span
                class="small link-inherit"><?= $order['client_phone']; ?></span>
    </td>
    <td class="text-left" style="word-break: break-word; width: 120px;"><?php $items = Order::getOrderItems($order['order_id']);
        $total = 0;
        if ($items):
            foreach ($items as $item):
                $product_data = Product::getProductName($item['product_id']);
                $total = $total + $item['price'];
                echo $product_data['product_name'] . $product_data['mess'];
                if ($item['type_id'] == 2):?>
                    <div class="delivery_icon" title="<?= System::Lang('HAVE_DELIVERY'); ?>"></div>
                <?php endif; ?><br/>
            <?php endforeach;
        endif;

        if (!empty($order['admin_comment'])):?>
            <div class="admin_comment_in_order" title="<?= System::Lang('ADMIN_COMMENT'); ?>">
                <i class="fas fa-comment-dots"></i>
            </div>
        <?php endif; ?>
    </td>
    <td class="" style="word-break: break-word; width: 60px;"><?= $total; ?> <?= $setting['currency']; ?></td>
    <td style="word-break: break-word; width: 60px;">
        <?php if ($order['installment_map_id'] != 0): ?>
            <?php $inst = Order::getInstallmentMapData($order['installment_map_id']);
            if ($inst['max_periods'] == 2) { ?>
                <img src="/template/admin/images/icons/installment.png" title="Предоплата">
            <?php } else { ?>
                <img src="/template/admin/images/icons/installment.png" title="Рассрочка">
            <?php } endif; ?>

        <?php if ($order['status'] == 1) echo '<span class="checked-status" title="Оплачен"></span>'; ?>
        <?php if ($order['status'] == 0) echo '<a style="text-decoration:none; color:#E04265" onclick="return confirm(\'Вы уверены что хотите удалить этот заказ?\')" href="/admin/orders/del/' . $order['order_id'] . '?token=' . $_SESSION['admin_token'] . '"><span class="icon-stopwatch"></span></a>'; ?>
        <?php if ($order['status'] == 2) echo '<a style="text-decoration:none" target="_blank" onclick="return confirm(\'Вы уверены что хотите подтвердить оплату этого заказа?\')" href="/confirmcustom?key=' . md5($order['order_id'] . $setting['secret_key']) . '&date=' . $order['order_date'] . '"><span class="status-close"></span></a>'; ?>
        <?php if ($order['status'] == 9) echo '<span class="status-return"></span>'; ?>
        <?php if ($order['status'] == 97) echo '<span class="status-return wait" title="Ожидает возврата"></span>'; ?>
        <?php if ($order['status'] == 7) echo '<span class="status-time"></span>'; ?>
        <?php if ($order['status'] == 99) echo '<span class="status-cancel" title="Отменён"></span>'; ?>
    </td>
</tr>

