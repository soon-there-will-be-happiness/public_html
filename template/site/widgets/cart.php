<?php defined('BILLINGMASTER') or die;
$product_in_cart = Cart::getProducts();
$products_ids = $product_in_cart ? array_keys($product_in_cart) : null;
$products = $products_ids ? Product::getProductsByIds($products_ids) : null;?>

    <div class="widget widget-cart">
        <h3 class="widget-header"><?=System::Lang('CART');?></h3>
        <?php if($product_in_cart):
        $total = 0;
        $totalnotdiscount = 0;
        $discount = 0;?>
        <div class="widget-cart-items">
            <?php foreach($products as $product):
                $price = Price::getPriceinCatalog($product['product_id'], false);
                $discount += $price['price']-$price['real_price'];
                $totalnotdiscount += $price['price'];
                $total += $price['real_price'];?>

                <div class="widget-cart-item">
                    <div class="widget-cart-item__left"><?php if(!empty($product['product_cover'])):?>
                        <img src="/images/product/<?=$product['product_cover'];?>" alt="<?=$product['img_alt'];?>">
                        <?php endif;?></div>
                    <div class="widget-cart-item__right">
                        <div class="widget-cart-item__inner">
                            <h4 class="widget-cart-item__title"><?=$product['product_name'];?></h4>
                            <div class="widget-cart-item__price" data-id="<?=$product['product_id'];?>">
                            <?php if($price['real_price'] < $price['price']):?>
                                <div class="widget-cart-item__price-old"><?="{$price['price']} {$this->settings['currency']}";?></div>
                            <?php endif;?>
                                <div class="widget-cart-item__price-current"><?="{$price['real_price']} {$this->settings['currency']}";?></div>
                            </div>
                        </div>
                        <a class="widget-cart-item__delete" data-id="<?=$product['product_id'];?>" onclick="return deleteproduct(this);">
                            <span class="icon-remove"></span>
                        </a>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
        <div class="widget-cart-all-summ">
            <div class="widget-cart-summ"><span>Сумма заказа:</span> <?="{$totalnotdiscount} {$this->settings['currency']}";?></div>
            <div class="widget-cart-discount"><span>Скидка:</span> <?="{$discount} {$this->settings['currency']}";?></div>
            <div class="widget-cart-total"><span>Итого:</span> <?="{$total} {$this->settings['currency']}";?></div>
        </div>

        <form action="/cart" method="POST" class="widget-cart-add-prod">
            <input type="submit" class="button btn-blue" name="checkout" value="<?=System::Lang('CHECKOUT');?>">
        </form>

        <?php else:?>
            <div class="widget-cart-items">
            </div>
            <p id="empty-cart" class="empty-cart"><?=System::Lang('EMPTY_CART');?></p>
        <?php endif;?>
    </div>

<script>
function deleteproduct(obj){
    var id = $(obj).attr("data-id");
    $.post("/cart/del/"+id, {}, function (data) {
        var cart_data = JSON.parse(data);
        var currency = "<?=$this->settings['currency'];?>";
        document.getElementById("cart-count").innerHTML = cart_data['count'];
        document.querySelector('.widget-cart-summ').innerHTML = `<span>Сумма заказа: </span>`+cart_data['totalnotdiscount']+` `+currency;
        document.querySelector('.widget-cart-discount').innerHTML = `<span>Скидка: </span>`+cart_data['discount']+` `+currency;
        document.querySelector('.widget-cart-total').innerHTML = `<span>Итого: </span>`+cart_data['total']+` `+currency;
        $(obj).closest(".widget-cart-item").remove();
        var items = document.querySelector(".widget-cart-items");
        if (document.querySelector(".widget-cart-item") == null) {
            document.querySelector(".widget-cart-all-summ").remove();
            document.querySelector(".widget-cart-add-prod").remove();
            let empty_cart = document.createElement('p');
            empty_cart.classList.add('empty-cart');
            empty_cart.id = "empty-cart";
            empty_cart.innerText = '<?=System::Lang('EMPTY_CART');?>';
            items.after(empty_cart);
        } else {
            Object.keys(cart_data['id']).forEach((element) => {
                var prod = document.querySelector('.widget-cart-item__price[data-id="'+element+'"]');
                if (prod.querySelector('.widget-cart-item__price-old') !== null ) {
                    if (cart_data['id'][element]['price']>cart_data['id'][element]['real_price']) {
                        prod.querySelector('.widget-cart-item__price-old').textContent = cart_data['id'][element]['price']+` `+currency;
                    } else {
                        prod.querySelector('.widget-cart-item__price-old').remove(); 
                    }
                }
                prod.querySelector('.widget-cart-item__price-current').textContent = cart_data['id'][element]['real_price']+` `+currency;
            });
        }
    });
            return false;
}
</script>
