<?php defined('BILLINGMASTER') or die;?>

<div id="order_form">
    <div class="container-cart">
        <h1><?=System::Lang('THANKS');?></h1>

        <div class="order_data">
            <p><?$product = Product::getMinProductById($order['product_id']); echo $product['product_name'];?></p>
            <p><a href="<?=$product['link'];?>" class="order_button"><?=System::Lang('DOWNLOAD');?></a></p>
        </div>
    </div>
</div>