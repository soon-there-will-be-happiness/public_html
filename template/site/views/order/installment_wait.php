<?php defined('BILLINGMASTER') or die;
// Страница спасибо для рассрочки?>

<div id="order_form">
    <div class="container-cart">
        <div class="maincol-inner-white">
            <h1><?=System::Lang('APPLICATION_SENDED');?></h1>

            <div class="order_data">
                <?=$letters ? $letters['waiting'] : '';?>
            </div>
        </div>
    </div>
</div>