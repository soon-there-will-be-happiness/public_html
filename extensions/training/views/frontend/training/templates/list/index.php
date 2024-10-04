<?defined('BILLINGMASTER') or die;
$now = time();

$tr_settings = isset($this->tr_settings) ? $this->tr_settings : $widget_params['params'];
$filter_settings = isset($tr_settings['filter']) ? $tr_settings['filter'] : null;
$show_headers2list = !isset($tr_settings['show_headers2list']) || $tr_settings['show_headers2list'] ? true : false;

if($cat_list):
    if($count_trainings_without_filter):
        if($show_headers2list):?>
            <h2 class="training-list-header">Категории</h2>
        <?endif;

        require_once (__DIR__ . "/../../../category/templates/list/{$tr_settings['template']}.php");

        if($show_headers2list):?>
            <hr><h2 class="training-list-header">Тренинги</h2>
        <?endif;

        if($filter_settings && ($cat_key = array_search('category', $filter_settings))):
            unset($filter_settings[$cat_key]);
        endif;
    else:
        require_once (__DIR__ . "/../../../category/templates/list/{$tr_settings['template']}.php");
    endif;
endif;

if ($count_trainings_without_filter) {
    if ($filter_settings && $this->view['is_page'] != 'my_trainings') {
        require_once(__DIR__ . '/../../../filter/filter.php');
        $training_filter_enabled = true;
    }

    require_once (__DIR__ . "/{$tr_settings['template']}.php");
}

