<?php defined('BILLINGMASTER') or die;

class trainingSectionController extends trainingBaseController {


    /**
     * ПРОСМОТР РАЗДЕЛА ТРЕНИНГА
     * @param $tr_alias
     * @param $section_alias
     * @throws Exception
     */
    public function actionSection($tr_alias, $section_alias)
    {
        $training = Training::getTrainingByAlias(htmlentities($tr_alias));
        if (!$training || !$training['status']) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $section = TrainingSection::getSectionByAlias($training['training_id'], htmlentities($section_alias));
        if (!$section) {
            require_once(ROOT . "/template/{$this->settings['template']}/404.php");
        }

		require_once (ROOT ."/lib/mobile_detect/Mobile_Detect.php");
        $detect = new Mobile_Detect;

        $user_id = intval(User::isAuth());
        $user_groups = $user_id ? User::getGroupByUser($user_id) : false;
        $user_planes = $user_id ? Member::getPlanesByUser($user_id, 1, true) : false;
        $user_is_curator = Training::isCuratorInTrainingInSection($user_id, $training['training_id'], $section['section_id']);

        $access = Training::getAccessData($user_groups, $user_planes, $training, $section);
        if (!Training::checkUserAccess($access)) {
            $this->setPageSettings($section, 'section', 'layouts/no_access.php', 'no-access-page inner-training-page');
            require_once ("{$this->template_path}/main3.php");
            exit;
        }

        if ($user_id && !isset($_SESSION['training_save_uv']['section'][$section['section_id']])) {
            TrainingUserVisits::saveVisit($user_id, $training['training_id'], $section['section_id']);
            $_SESSION['training_save_uv']['section'][$section['section_id']] = true;
        }


        $sub_category = $category = false;
        if ($training['cat_id'] != 0) {
            $category = TrainingCategory::getCategory($training['cat_id']);
            if ($category && $category['parent_cat'] != 0) {
                $sub_category = $category;
                $category = TrainingCategory::getCategory($category['parent_cat']);
            }
        }

        $block_list = TrainingBlock::getBlocks($training['training_id'], $section['section_id']); // получаем обычные блоки для секции
        $lesson_list = TrainingLesson::getLessons($training['training_id'], $section['section_id']); // получаем уроки без блоков

        //Сертификат
        $user_id = intval(User::isAuth());
        $sertificate = json_decode($training['sertificate'], true);
        $have_certificate = Training::getUrlHashCertificate2User($user_id, $training['training_id']);

        $this->setPageSettings($section, 'section', 'section/index.php', 'section-page inner-training-page');

        require_once ("{$this->template_path}/main3.php");
    }


    /**
     * СТРАНИЦА ВЫБОРА ТАРИФА в разделах (Продуктов)
     * @param $section_id
     */
    public function actionOptions($section_id)
    {
        $section_id = intval($section_id);
        $section_id = $section = TrainingSection::getSection($section_id);

        if (!$section_id) {
            require_once (ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $user_id = intval(User::isAuth());
        $h1 = $section_id['name'];
        $training = Training::getTraining($section_id['training_id']);

        $lesson = null;
        $sub_category = $category = false;

        if ($training['cat_id'] != 0) {
            $category = TrainingCategory::getCategory($training['cat_id']);
            if ($category && $category['parent_cat'] != 0) {
                $sub_category = $category;
                $category = TrainingCategory::getCategory($sub_category['parent_cat']);
            }
        }

        $big_button = json_decode($section_id['by_button'], true);
        $list_product = $big_button['rate'];

        $this->setSEOParams('Выберите вариант');
        $this->setViewParams('', 'layouts/rates.php', null, null, 'training-options-page inner-training-page');

        require_once ("{$this->template_path}/main3.php");
    }
}