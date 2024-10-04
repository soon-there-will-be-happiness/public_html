<?php defined('BILLINGMASTER') or die;

$title = isset($title) ? $title : 'Отмена платежа';
$h2 = isset($h2) ? $h2 : 'Вы отказались от оплаты.';
$html = isset($html) ? $html : <<<LABEL
<p>Если у вас возникли вопросы или сложности, то обязаетльно напишите нам.</p>
<p>Вернуться на <a href="/">главную страницу</a></a></p>
LABEL;

$this->setSEOParams($title);
$this->setViewParams();
require_once ("{$this->layouts_path}/head.php");?>

<body class="order-pay-status-page" id="page">
    <?require_once ("{$this->layouts_path}/header.php");?>

    <div id="order_form">
        <div class="container-cart">
            <?if(isset($h1) && $h1):?>
                <h1><?=$h1;?></h1>
            <?endif;?>

            <div class="order_data">
                <div class="offer main">
                    <h2><?=$h2;?></h2>
                    <?=$html;?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once ("{$this->layouts_path}/footer.php");
    require_once ("{$this->layouts_path}/tech-footer.php")?>
</body>
</html>