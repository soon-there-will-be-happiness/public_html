<?php defined('BILLINGMASTER') or die;

$extension = System::CheckExtensension('training', 1);
if ($extension) {
    $setting = System::getSetting();
    $tr_settings = Training::getSettings();
    $widget_params = unserialize($widget['params']);

    if (isset($widget_params['params']['filter'])) {
        $tr_settings['filter'] = $widget_params['params']['filter'];
    }

    $user_groups = $user_planes = false;
    $user_id = intval(User::isAuth());
    if ($user_id) {
        $user_groups = User::getGroupByUser($user_id);
        $user_planes = Member::getPlanesByUser($user_id, 1, true);
    }

    $filter = [
        'access' => isset($_GET['acc']) && $_GET['acc'] != 'all' ? $_GET['acc'] : false,
        'author' => isset($_GET['aut']) ? $_GET['aut'] : false,
        'category' => isset($_GET['cat']) ? $_GET['cat'] : false,
    ];

    $cat_list = $training_list = $count_trainings_without_filter = false;

    if ($widget_params['params']['show_list'] == 'all') {
        $cat_list = TrainingCategory::getCatList(false, TrainingCategory::STATUS_CATEGORY_ON);
        $count_trainings_without_filter = Training::getCountTrainings([0], 1);
        $training_list = $count_trainings_without_filter ? Training::getTrainingList(0, null, $filter) : false;
    } elseif($widget_params['params']['show_list'] == 'without_categories') {
        $count_trainings_without_filter = Training::getCountTrainings(null, 1);
        $training_list = Training::getTrainingList(null, null, $filter);
    } elseif ($widget_params['params']['show_list'] == 'content_separate_category' && $widget_params['params']['categories_to_content']) {
        $cat_list = TrainingCategory::getSubCategoriesByParentsIds($widget_params['params']['categories_to_content']);
        $count_trainings_without_filter = Training::getCountTrainings($widget_params['params']['categories_to_content'], 1);
        $training_list = $count_trainings_without_filter ? Training::getTrainingList($widget_params['params']['categories_to_content'], null, $filter) : false;
    }

    require_once (__DIR__ . "/view.php");
}