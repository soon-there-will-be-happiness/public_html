<?php  defined('BILLINGMASTER') or die;

$categories = Product::getAllCatList();
$type_filter = isset($_GET['type']) && $_GET['type'] != 'all' ? $_GET['type'] : null;
$cat_filter = isset($_GET['cat_id']) && is_array($_GET['cat_id']) && array_filter($_GET['cat_id'], 'strlen') ? $_GET['cat_id'] : [];?>

<div class="filters-2">
    <div class="filter-select">
        <div class="filter-select-row">
            <div class="filter-select-col">
                <div>
                    <select class="select2" name="type_filter">
                        <option value="all" data-filter="type"<?=!$type_filter ? ' selected="selected"' : '';?>>Все категории</option>
                        <option value="paid" data-filter="type"<?=$type_filter == 'paid' ? ' selected="selected"' : '';?>>Платные</option>
                        <option value="free" data-filter="type"<?=$type_filter == 'free' ? ' selected="selected"' : '';?>>Бесплатные</option>
                    </select>
                </div>
            </div>

            <?if($categories):?>
                <div class="filter-select-col">
                    <div class="multiple">
                        <select class="select2" name="category_filter" multiple>
                            <option value="" data-filter="cat_id[]"<?=empty($cat_filter) ? ' selected="selected"' : '';?>><?=System::Lang('ALL_CATEGORIES');?></option>
                            <?foreach($categories as $category):?>
                                <option value="<?=$category['cat_id'];?>" data-filter="cat_id[]"<?=in_array($category['cat_id'], $cat_filter) ? ' selected="selected"' : '';?>><?=$category['cat_name'];?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
            <?endif;?>
        </div>
    </div>
</div>