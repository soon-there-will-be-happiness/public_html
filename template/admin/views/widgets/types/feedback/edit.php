<?php defined('BILLINGMASTER') or die; ?>

<div class="row-line">
    <div class="col-1-1 mb-0">
        <h4>Содержимое</h4>
    </div>

    <div class="col-1-2">
        <p><label>ID формы</label>
            <select name="widget[params][form_id]">
                <?php $form_list = System::getFeedBackFormList();
                if($form_list):
                    foreach($form_list as $form):?>
                        <option value="<?=$form['form_id'];?>"<?php if($params['params']['form_id'] == $form['form_id']) echo ' selected="selected"';?>><?=$form['name'];?></option>
                    <?php endforeach;
                endif;?>
            </select>
        </p>
    </div>
</div>