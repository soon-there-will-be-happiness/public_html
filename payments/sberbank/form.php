<?php defined('BILLINGMASTER') or die;

require_once "load.php";

/**
 *  @var array $payment
 *  @var array $order
 *  @var integer $total
 */

$params = unserialize(base64_decode($payment['params']));
if (!$params) {
    return false;
}

//Если уже создана ссылка на оплату - получить ее
$order_info = unserialize(base64_decode($order['order_info']));
$link = "";
if (isset($order_info['sberbankFormUrl'])) {
    $link = $order_info['sberbankFormUrl'];
}

?>
<a class="payment_btn" id="sberbankPayBtn" href="<?= $link ?>">Оплатить</a>
<script>
    let sberBtn;
    window.addEventListener("DOMContentLoaded", function () {
        sberBtn = document.getElementById("sberbankPayBtn");
        sberBtn.addEventListener("click", sberBtnClick);
    });

    async function sberBtnClick(e) {
        if (e.target.href !== window.location.href) {//Если ссылка на оплату уже создана
            return true;
        }

        e.preventDefault();

        let response = await fetch("/payments/sberbank/createorder.php?orderid=" + <?= $order['order_id'] ?>);
        let status = await response.status;
        let result = await response.json();

        switch (status) {
            case 201:
                sberBtn.setAttribute("href", result.data.formUrl)
                location.assign(result.data.formUrl);
                break;
            default:
                console.log(response.status)
                console.log(result);
                break;
        }
    }
</script>