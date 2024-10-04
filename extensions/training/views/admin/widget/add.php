<?php defined('BILLINGMASTER') or die;?>

<div class="row-line">
    <div class="col-1-1 mb-0">
        <h4>Содержимое</h4>
    </div>

    <div class="col-1-2">
        <div class="width-100"><label>Что выводить:</label>
            <div class="select-wrap">
                <select name="widget[params][show_list]">
                    <option value="all" data-show_on="show_headers2list">Все с категориями</option>
                    <option value="without_categories">Все без разбивки на категории</option>
                    <option value="content_separate_category" data-show_on="categories_to_content,show_headers2list">Содержимое отдельной категории</option>
                </select>
            </div>
        </div>

        <div class="width-100 hidden" id="categories_to_content">
            <label>Выбрать категории</label>
            <select size="7" class="multiple-select" multiple="multiple" name="widget[params][categories_to_content][]">
                <?php $categories = TrainingCategory::getCatList();
                if($categories):
                    foreach($categories as $category):?>
                        <option value="<?=$category['cat_id'];?>"><?=$category['name'];?></option>
                    <?php endforeach;
                endif;?>
            </select>
        </div>

        <div class="width-100 hidden" id="show_headers2list"><label>Выводить заголовки перед блоками:</label>
            <span class="custom-radio-wrap">
                <label class="custom-radio"><input name="widget[params][show_headers2list]" type="radio" value="1"><span>Да</span></label>
                <label class="custom-radio"><input name="widget[params][show_headers2list]" type="radio" value="0"><span>Нет</span></label>
            </span>
        </div>

        <div class="width-100">
            <label>Шаблон страницы</label>
            <select name="widget[params][template]">
                <option value="2columns">В 2 колонки</option>
                <option value="3columns">В 3 колонки</option>
            </select>
        </div>
    </div>

    <div class="col-1-2">
        <div class="width-100"><label>Фильтр</label>
            <select class="multiple-select" name="widget[params][filter][]" multiple="multiple" size="4">
                <option value="access">По доступу</option>
                <option value="category">По категории</option>
                <option value="author">По автору</option>
            </select>
        </div>
    </div>
</div>