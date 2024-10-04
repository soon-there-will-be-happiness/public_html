<?defined('BILLINGMASTER') or die;

$tr_settings = Training::getMPSettings();?>

<div class="width-100"><label>Что выводить:</label>
    <div class="select-wrap">
        <select name="training[params][show_list]">
            <option value="all"<?if(isset($tr_settings['show_list']) && $tr_settings['show_list'] == 'all') echo ' selected="selected"';?> data-show_on="show_headers2list">Все с категориями</option>
            <option value="without_categories"<?if(isset($tr_settings['show_list']) && $tr_settings['show_list'] == 'without_categories') echo ' selected="selected"';?>>Все без разбивки на категории</option>
            <option value="content_separate_category" data-show_on="categories_to_content,show_headers2list" <?if(isset($tr_settings['show_list']) && $tr_settings['show_list'] == 'content_separate_category') echo ' selected="selected"';?>>Содержимое отдельной категории</option>
        </select>
    </div>
</div>

<div class="width-100 hidden" id="categories_to_content">
    <label>Выбрать категории</label>
    <select size="7" class="multiple-select" multiple="multiple" name="training[params][categories_to_content][]">
        <?$categories = TrainingCategory::getCatList();
        if($categories):
            foreach($categories as $category):?>
                <option value="<?=$category['cat_id'];?>"<?if(isset($tr_settings['categories_to_content']) && in_array($category['cat_id'], $tr_settings['categories_to_content'])) echo ' selected="selected"';?>>
                    <?=$category['name'];?>
                </option>
            <?endforeach;
        endif;?>
    </select>
</div>

<div class="width-100 hidden" id="show_headers2list"><label>Выводить заголовки перед блоками:</label>
    <span class="custom-radio-wrap">
        <label class="custom-radio"><input name="training[params][show_headers2list]" type="radio" value="1"<?if(!isset($tr_settings['show_headers2list']) || $tr_settings['show_headers2list']) echo ' checked';?>><span>Да</span></label>
        <label class="custom-radio"><input name="training[params][show_headers2list]" type="radio" value="0"<?if(isset($tr_settings['show_headers2list']) && !$tr_settings['show_headers2list']) echo ' checked';?>><span>Нет</span></label>
    </span>
</div>

<div class="width-100">
    <label>Шаблон страницы</label>
    <select name="training[params][template]">
        <option value="2columns"<?if(isset($tr_settings['template']) && $tr_settings['template'] == '2columns') echo ' selected="selected"';?>>В 2 колонки</option>
        <option value="3columns"<?if(isset($tr_settings['template']) && $tr_settings['template'] == '3columns') echo ' selected="selected"';?>>В 3 колонки</option>
    </select>
</div>

<div class="width-100"><label>Фильтр</label>
    <select class="multiple-select" name="training[params][filter][]" multiple="multiple" size="4">
        <option value="access"<?if(isset($tr_settings['filter']) && in_array('access', $tr_settings['filter'])) echo ' selected="selected"';?>>По доступу</option>
        <option value="category"<?if(isset($tr_settings['filter']) && in_array('category', $tr_settings['filter'])) echo ' selected="selected"';?>>По категории</option>
        <option value="author"<?if(isset($tr_settings['filter']) && in_array('author', $tr_settings['filter'])) echo ' selected="selected"';?>>По автору</option>
    </select>
</div>