<?defined('BILLINGMASTER') or die;
$filter_model = isset($filer_model) ? $filer_model : SegmentFilter::getFilterModel();
$condition_titles = isset($condition_titles) ? $condition_titles : OrderFilter::getFilterTitles();
$logic_text = $group['logic_type'] == 'and' ? 'и' : 'или';
$cond_change = isset($cond_change) ? $cond_change : true;?>

<div class="conditions-group" data-logic_type="<?=$group['logic_type'];?>" data-index="<?=$group['index'];?>" data-invert="<?=$group['invert'];?>">
    <a class="logic-button-change" href="javascript:void(0)"<?if(!$cond_change) echo ' disabled="disabled"';?>><?=$logic_text;?></a>
    <div class="logic-buttons-wrap">
        <a href="javascript:void(0);" class="logic-button" data-logic_type="and"<?if(!$cond_change) echo ' disabled="disabled"';?>>и</a>
        <a href="javascript:void(0);" class="logic-button" data-logic_type="or"<?if(!$cond_change) echo ' disabled="disabled"';?>>или</a>
        <a href="javascript:void(0);" class="logic-button" data-logic_type="not"<?if(!$cond_change) echo ' disabled="disabled"';?>>не</a>
        <a href="javascript:void(0);" class="logic-button" data-logic_type="del"<?if(!$cond_change) echo ' disabled="disabled"';?>>&nbsp;</a>
    </div>

    <?if($group['groups']):
        foreach ($group['groups'] as $inner_group_index):
            $index = array_search($inner_group_index, array_column($groups, 'index'));
            $filter_model::getFiltersHtmlItem($filter_model, $condition_titles, $groups, $groups[$index], $skip, $segment_data, $cond_change);
            $skip[] = $inner_group_index;
        endforeach;
    endif;

    if ($group['conditions']) {
        foreach ($group['conditions'] as $cond_index) {
            if (isset($segment_data['condition_type'][$cond_index])) {
                require(__DIR__.'/condition.php');
            }
        }
    };?>
</div>