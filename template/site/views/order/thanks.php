<?php defined('BILLINGMASTER') or die;?>

<div id="order_form">
    <div class="container-cart">
        <div class="maincol-inner-white">
            <h1><?=System::Lang('THANKS');?></h1>

            <div class="order_data">
                <?=System::Lang('INSTRUCTIONS_ON_EMAIL');?>
                <?php if (isset($_COOKIE['cl_eml'])): ?>
                  <?= $_COOKIE['cl_eml'] ?>
                <?php endif; ?>
                <?=System::Lang('INSTRUCTIONS_ON_EMAIL_HINT');?>
            </div>
        </div>
    </div>
</div>