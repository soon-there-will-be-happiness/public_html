<?defined('BILLINGMASTER') or die;?>

<div id="order_form">
    <div class="container-cart">
        <h1><?=System::Lang('THANK_YOU_FOR_PAID_ORDER');?></h1>

        <div class="order_data">
            <h3><?=System::Lang('YOUR_ORDER_COMPLECTION');?></h3>

            <table>
                <?foreach($items as $item):?>
                    <tr>
                        <td>
                            <form action="" id="<?=$item['product_id'];?>" method="POST">
                                <strong><?$product_data = Product::getProductName($item['product_id']);
                                    echo $product_data['product_name'].$product_data['mess'];?></strong>
                        </td>

                        <td>
                            <?if($product_data['dwl'] == 1):?>
                                <input type="hidden" name="item" value="<?=$item['product_id'];?>">
                                <?if($item['dwl_count'] < $dwl_count){?>
                                    <input type="submit" class="button" name="download" value="<?=System::Lang('DOWNLOAD');?>">
                                <?} else {
                                    echo System::Lang('LINK_TIME_EXPIRED');
                                }?>
                            <?endif; ?>
                            </form>
                        </td>
                    </tr>
                <?endforeach;?>
            </table>
        </div>
    </div>
</div>