<?defined('BILLINGMASTER') or die;
$filter_model = isset($filer_model) ? $filer_model : SegmentFilter::getFilterModel();
$condition_titles = isset($condition_titles) ? $condition_titles : $filter_model::getFilterTitles();
if (!isset($cond_index)) {
    $cond_index = isset($segment_data['condition_type']) ? array_keys($segment_data['condition_type'])[0] : 0;
}
$cond_value = isset($segment_data['condition_type'][$cond_index]) ? $segment_data['condition_type'][$cond_index] : null;
$invert = isset($segment_data['invert'][$cond_index]) ? $segment_data['invert'][$cond_index] : 0;
$cond_change = isset($cond_change) ? $cond_change : true;?>

<div class="condition-wrap" data-condition_index="<?=$cond_index;?>" data-invert="<?=$invert;?>">
    <div class="logic-buttons-wrap">
        <a href="javascript:void(0);" class="logic-button" data-logic_type="and"<?if(!$cond_change) echo ' disabled="disabled"';?>>и</a>
        <a href="javascript:void(0);" class="logic-button" data-logic_type="or"<?if(!$cond_change) echo ' disabled="disabled"';?>>или</a>
        <a href="javascript:void(0);" class="logic-button" data-logic_type="not"<?if(!$cond_change) echo ' disabled="disabled"';?>>не</a>
        <a href="javascript:void(0);" class="logic-button" data-logic_type="del"<?if(!$cond_change) echo ' disabled="disabled"';?>>&nbsp;</a>
    </div>

    <div class="select-wrap">
        <select class="select2 condition_type" name="condition_type[<?=$cond_index;?>]"<?if(!$cond_change) echo ' disabled="disabled"';?>>
            <option value="">Добавить условие</option>
            <?foreach($condition_titles as $value => $title):
                $selected = $cond_value !== null && $cond_value == $value ? ' selected="selected"' : '';?>
                <option value="<?=$value;?>"<?=$selected;?>><?=$title;?></option>
            <?endforeach;?>
        </select>
    </div>

    <div class="condition mt-20<?=!isset($segment_data['filter']) ? ' hidden' : '';?>">
        <?if(isset($segment_data['groups_data']) && $segment_data['groups_data']) {
            echo $filter_model::getFilterHtml($segment_data, $cond_value, $cond_index, $cond_change);
        } elseif($cond_value) {
            echo $filter_model::getFilterHtml($segment_data, null, null, $cond_change);
        }?>
    </div>

    <input type="hidden" value="" name="logic_type[<?=$cond_index;?>]">
    <input type="hidden" value="" name="group_index[<?=$cond_index;?>]">
    <input type="hidden" value="" name="invert[<?=$cond_index;?>]">
</div>