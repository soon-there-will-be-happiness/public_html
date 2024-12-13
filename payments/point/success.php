<?php defined('BILLINGMASTER') or die;
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = Order::getOrder($order_id);

if($order_id!=null&&$order['status']==1):

?>

<html>
<head>
<title>Успешная оплата</title>
</head>


<body class="order-pay-status-page" id="page" style="background:#eee; font-size:1.1rem">
<?require_once ("{$this->layouts_path}/head.php");?>
    <?require_once ("{$this->layouts_path}/header.php");?>

    <div style="height: 82%;">
    <div style="background:#fff; width:60%; margin:2em auto; text-align:center; padding:1em 2%">
<h1>Спасибо за оплаченный заказ!</h1>
<p>Дальнейшие инструкции высланы на ваш e-mail адрес.<br />На всякий случай проверьте папку СПАМ.</p>
<p>Вернуться на <a href="/">главную страницу</a></a></p>
</div>
</div>

    <?php require_once ("{$this->layouts_path}/footer.php");
    require_once ("{$this->layouts_path}/tech-footer.php")?>


</body>
</html>
<?php
else:
?>
<html>
<title>Отмена платежа</title>
</head>


<body class="order-pay-status-page" id="page" style="background:#eee; font-size:1.1rem">
<?require_once ("{$this->layouts_path}/head.php");?>
    <?require_once ("{$this->layouts_path}/header.php");?>

    <div style="height: 82%;">
    <div style="background:#fff; width:60%; margin:2em auto; text-align:center; padding:1em 2%">
<p>Если у вас возникли вопросы или сложности, то обязательно напишите нам.</p>
<p>Вернуться на <a href="/">главную страницу</a></a></p>
</div>
</div>

    <?php require_once ("{$this->layouts_path}/footer.php");
    require_once ("{$this->layouts_path}/tech-footer.php")?>


</body>
</html>
<?php
endif;
?>
