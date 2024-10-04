<?php defined('BILLINGMASTER') or die; 

class galleryController extends baseController {
    
    
    // ГЛАВНАЯ СТРАНИЦА ГАЛЕРЕИ
    public function actionIndex()
    {
        $setting = System::getSetting(); 
        $gallery = System::CheckExtensension('gallery', 1);
        if (!$gallery) {
            ErrorPage::return404();
        }

        $params = unserialize(System::getExtensionSetting('gallery'));
        $cat_list = Gallery::getCatList(1);

        $this->setSEOParams($params['params']['title'], $params['params']['desc'], $params['params']['keys']);
        $this->setViewParams('gallery', 'gallery/index.php', [['title' => System::Lang('GALLERY')]]);

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    
    public function actionCats($alias)
    {
        $setting = System::getSetting(); 
        $gallery = System::CheckExtensension('gallery', 1);
        if (!$gallery) {
            ErrorPage::return404();
        }
        
        $params = unserialize(System::getExtensionSetting('gallery'));
        $alias = htmlentities($alias);
        $cat = Gallery::getCatDataByAlias($alias);
        if (!$cat) {
            ErrorPage::return404();
        }

        define('BM_GALLERY', $params['params']['style']);
        
        $subcat_list = Gallery::getSubCatList($cat['cat_id']);
        $img_list = Gallery::getImagesByCat($cat['cat_id']);

        $this->setSEOParams($cat['cat_title'], $cat['meta_desc'], $cat['meta_keys']);
        $this->setViewParams('galleryPage', 'gallery/cat.php', [
            ['title' => System::Lang('GALLERY'), 'url' => '/gallery'],
            ['title' => $cat['cat_name']],
        ]);

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    public function actionSubcats($alias, $sub_alias)
    {
        $setting = System::getSetting(); 
        $gallery = System::CheckExtensension('gallery', 1);
        if (!$gallery) {
            ErrorPage::return404();
        }
        
        $params = unserialize(System::getExtensionSetting('gallery'));
        $alias = htmlentities($alias);
        $sub_alias = htmlentities($sub_alias);
        
        $cat = Gallery::getCatDataByAlias($alias);
        $sub_cat = Gallery::getCatDataByAlias($sub_alias);
        
        if (!$cat || !$sub_cat) {
            ErrorPage::return404();
        }

        define('BM_GALLERY', $params['params']['style']);
        
        $img_list = Gallery::getImagesByCat($sub_cat['cat_id']);

        $this->setSEOParams($sub_cat['cat_title'], $sub_cat['meta_desc'], $sub_cat['meta_keys']);
        $this->setViewParams('gallery', 'gallery/subcat.php', [
            ['title' => System::Lang('GALLERY'), 'url' => '/gallery', 'url' => $cat['alias']],
            ['title' => $sub_cat['cat_name']],
        ]);

        require_once ("{$this->template_path}/main.php");
        return true;
    }
}