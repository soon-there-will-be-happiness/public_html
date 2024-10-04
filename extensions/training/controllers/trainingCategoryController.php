<?php defined('BILLINGMASTER') or die;

class trainingCategoryController extends trainingBaseController {


    /**
     * КАТЕГОРИЯ ТРЕНИНГОВ
     * @param $cat_alias
     */
    public function actionCategory($cat_alias)
    {
        if (!$this->en_extension) {
            require_once (ROOT . '/template/'.$this->settings['template'].'/404.php');
        }

        $category = TrainingCategory::getCategoryByAlias(htmlentities($cat_alias));
        if (!$category) {
            require_once (ROOT . '/template/'.$this->settings['template'].'/404.php');
        }

        $filter  = [
            'access' => isset($_GET['acc']) && $_GET['acc'] != 'all' ? $_GET['acc'] : false,
            'author' => isset($_GET['aut']) && is_array($_GET['aut'])  ? $_GET['aut'] : false,
            'category' => isset($_GET['cat']) && is_array($_GET['cat'])  ? $_GET['cat'] : false,
        ];

        $subcategory_list = TrainingCategory::getSubCategories($category['cat_id']);
        $training_list = null;
        if (!$subcategory_list) {
            $training_list = Training::getTrainingList($category['cat_id'], null, $filter);
        }
        
        $canonical = $this->settings['script_url'].'/training/category/'.$cat_alias;

        $user_id = intval(User::isAuth());
        $user_groups = $user_id ? User::getGroupByUser($user_id) : false;
        $user_planes = $user_id ? Member::getPlanesByUser($user_id, 1, true) : false;

        $this->setPageSettings($category, 'training_category', 'category/index.php',
            'training-category-page', $training_list ? 'training-list-container' : ''
        );

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * ПОДКАТЕГОРИИ ТРЕНИНГОВ
     * @param $cat_alias
     * @param $sub_cat_alias
     */
    public function actionSubcategory($cat_alias, $sub_cat_alias)
    {
        $category = TrainingCategory::getCategoryByAlias(htmlentities($cat_alias));
        $sub_category = TrainingCategory::getCategoryByAlias(htmlentities($sub_cat_alias));

        if (!$this->en_extension || !$category || !$sub_category) {
            require_once (ROOT . '/template/'.$this->settings['template'].'/404.php');
        }

        $filter  = [
            'access' => isset($_GET['acc']) && $_GET['acc'] != 'all' ? $_GET['acc'] : false,
            'author' => isset($_GET['aut']) && is_array($_GET['aut'])  ? $_GET['aut'] : false,
            'category' => isset($_GET['cat']) && is_array($_GET['cat'])  ? $_GET['cat'] : false,
        ];
        $training_list = Training::getTrainingList($sub_category['cat_id'], 1, $filter);

        $user_id = intval(User::isAuth());
        $user_groups = $user_id ? User::getGroupByUser($user_id) : false;
        $user_planes = $user_id ? Member::getPlanesByUser($user_id, 1, true) : false;

        $this->setPageSettings($sub_category, 'training_subcategory', 'category/subcategory/index.php',
            'training-subcategory-page', 'training-list-container');

        require_once ("{$this->template_path}/main3.php");
    }
}