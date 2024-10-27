<?php defined('BILLINGMASTER') or die;
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$order = Order::getOrderDataByID($order_id,100);
echo($order_id)
;if($order['status']==1):

?>

<html>
<head>
<title>Успешная оплата</title>
</head>
<body style="background:#eee; font-size:1.1rem">
<div style="background:#fff; width:60%; margin:2em auto; text-align:center; padding:1em 2%">
<h1>Спасибо за оплаченный заказ!</h1>
<p>Дальнейшие инструкции высланы на ваш e-mail адрес.<br />На всякий случай проверьте папку СПАМ.</p>
<p>Вернуться на <a href="/">главную страницу</a></a></p>
</div>
</body>
</html>
<?php
else:
?>
<html>
<head>
<title>Отмена платежа</title>
</head>
<body style="background:#eee; font-size:1.1rem">
<div style="background:#fff; width:60%; margin:2em auto; text-align:center; padding:1em 2%">
<p>Если у вас возникли вопросы или сложности, то обязаетльно напишите нам.</p>
<p>Вернуться на <a href="/">главную страницу</a></a></p>
</div>
</body>
</html>

<?php
endif;
?>