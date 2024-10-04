<?defined('BILLINGMASTER') or die;
$filter_model = SegmentFilter::getFilterModel();
$segment_data = null;
$cond_change = false;
if (isset($_GET['filter']) && isset($_GET['segment'])) {
    $segment_data = $_GET['segment'] == 'segment' || isset($_GET['condition_type']) ? $_GET : SegmentFilter::getSegmentData($filter_model, (int)$_GET['segment']);
}?>

<form action="" method="GET" data-token="<?=$_SESSION['admin_token'];?>">
    <div class="segment-filter segment-filter_conditions">
        <div class="segment-filter-left">
            <div class="row-line">
                <div class="col-1-2">
                    <div class="select-wrap">
                        <select class="select2" name="segment">
                            <option value="">Выбрать сегмент</option>
                            <?if($segments = $filter_model::getSegments()):
                                foreach($segments as $segment):
                                    $selected = isset($_GET['segment']) && $_GET['segment'] == $segment['segment_id'] ? ' selected="selected"' : '';?>
                                    <option value="<?=$segment['segment_id'];?>"<?=$selected;?>><?=$segment['segment_name'];?></option>
                                <?endforeach;
                            endif;?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="add_conditions" class="<?=!isset($_GET['filter']) ? ' hidden' : '';?>">
                <?if ((isset($segment_data['groups_data']) && $segment_data['groups_data'])) {
                    echo $filter_model::getFiltersHtml($segment_data, false);
                } else {
                    require_once(__DIR__.'/condition.php');
                }?>
            </div>

            <div class="mt-20 submit-wrap<?=!isset($_GET['filter']) ? ' hidden' : '';?>">
                <button class="button-blue-rounding" type="button" name="show_segment" value="1">Посмотреть выборку</button>
            </div>
        </div>
    </div>

    <input type="hidden" name="groups_data" value="">
</form>


<link rel="stylesheet" href="/template/admin/css/segment-filter.css?v=<?=CURR_VER;?>">
<script src="/template/admin/js/segment-filter.js?v=<?=CURR_VER;?>"></script>