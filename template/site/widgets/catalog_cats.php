<?php defined('BILLINGMASTER') or die;
$cat_list = Product::getAllCatList();
if($cat_list):?>
    <ul class="rubrics_list">
        <?php foreach($cat_list as $cat):?>
            <li><a href="<?php echo $this->settings['script_url'];?>/catalog?cat=<?=$cat['cat_alias']?>"><?=$cat['cat_name']?></a></li>
        <?php endforeach;?>
    </ul>
<?php endif;?>