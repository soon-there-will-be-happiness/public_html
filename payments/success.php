<?defined('BILLINGMASTER') or die;

if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}
if(isset($_SESSION['sale_id'])) {
    unset($_SESSION['sale_id']);
}

$title = isset($title) ? $title : 'Успешная оплата';
$h2 = isset($h2) ? $h2 : 'Спасибо за оплаченный заказ!';
$html = isset($html) ? $html : <<<LABEL
<p>Дальнейшие инструкции высланы на ваш e-mail адрес.<br />На всякий случай проверьте папку СПАМ.</p>
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
    require_once ("{$this->layouts_path}/tech-footer.php");

    if (System::CheckExtensension('super_simple', 1)) {
        echo TemplateSuperSimple::getCss();
    }?>
</body>
</html>