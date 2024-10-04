<?php defined('BILLINGMASTER') or die;

class Template {


    /**
     * ПОЛУЧИТЬ НАСТРОЙКИ
     * @param $ext
     * @return bool|mixed
     */
    public static function getSettings($ext)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT params FROM ".PREFICS."extensions WHERE name = '$ext'");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? json_decode($data['params'], true) : false;
    }


    /**
     * @param $logotype
     * @param $phone
     * @param $phone_link
     * @param $fix_head
     * @param $soc_but
     * @param $counters
     * @param $counters_head
     * @return bool
     */
    public static function updSystemSettings($logotype, $phone, $phone_link, $fix_head, $soc_but, $counters, $counters_head) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'settings SET logotype = :logotype, phone = :phone, phone_link = :phone_link, fix_head = :fix_head,
                socbut = :soc_but, counters = :counters, counters_head = :counters_head WHERE setting_id = 1';
        $result = $db->prepare($sql);

        $result->bindParam(':logotype', $logotype, PDO::PARAM_STR);
        $result->bindParam(':phone', $phone, PDO::PARAM_STR);
        $result->bindParam(':phone_link', $phone_link, PDO::PARAM_STR);
        $result->bindParam(':fix_head', $fix_head, PDO::PARAM_INT);
        $result->bindParam(':soc_but', $soc_but, PDO::PARAM_STR);
        $result->bindParam(':counters', $counters, PDO::PARAM_STR);
        $result->bindParam(':counters_head', $counters_head, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @param $slogan
     * @param $sidebar
     * @param $copyright
     * @param $custom_css
     * @return bool
     */
    public static function updSystemMainSettings($slogan, $sidebar, $copyright, $custom_css) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'settings_main_page SET slogan = :slogan, sidebar = :sidebar,
                copyright = :copyright, custom_css = :custom_css WHERE settings_main_page_id = 1';
        $result = $db->prepare($sql);

        $result->bindParam(':slogan', $slogan, PDO::PARAM_STR);
        $result->bindParam(':sidebar', $sidebar, PDO::PARAM_STR);
        $result->bindParam(':copyright', $copyright, PDO::PARAM_STR);
        $result->bindParam(':custom_css', $custom_css, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * Проверка на переопределение макеты
     * @param $path
     * @param bool $extension
     * @return string
     */
    public static function getPath($path, $extension = false)
    {
        $setting = System::getSetting();
        
        if ($extension) {
            $override = ROOT . "/template/{$setting['template']}/extensions/{$extension}/views/$path";
            if (!file_exists($override)) {
                $override = ROOT."/extensions/{$extension}/views/frontend/$path";
            }
        } else {
            $override = ROOT."/template/{$setting['template']}/html/$path";
        }

        if (file_exists($override)) {
            return $override;
        }

        if (file_exists(ROOT."/template/{$setting['template']}/views") ) {
            return ROOT."/template/{$setting['template']}/views/$path";
        }

        return ROOT."/template/site/views/$path";
    }


    /**
     * @param $settings
     * @return string
     */
    public static function getLayoutsPath($settings) {
        if (file_exists(ROOT."/template/{$settings['template']}/layouts")) {
            return ROOT."/template/{$settings['template']}/layouts";
        }

        return ROOT."/template/site/layouts";
    }


    /**
     * @param $settings
     * @return string
     */
    public static function getWidgetsPath($settings) {
        if (file_exists(ROOT."/template/{$settings['template']}/widgets")) {
            return ROOT."/template/{$settings['template']}/widgets";
        }

        return ROOT."/template/site/widgets";
    }


    /**
     * Получить CSS для блока hero
     * @param $hero
     * @param $params
     */
    public static function showHeroCss($hero, $params)
    {
        if ($hero) {
            $setting = System::getSetting();
            require_once (self::getLayoutsPath($setting).'/hero_css.php');
        }
    }


    /**
     * Вывести блок hero
     * @param $hero
     * @param $params
     */
    public static function showHero($hero, $params)
    {
        if ($hero) {
            $setting = System::getSetting();
            require_once (self::getLayoutsPath($setting).'/hero.php');
        }
    }


    /**
     * Вывести хлебные крошки
     * @param $breadcrumbs
     * @return string
     */
    public static function showBreadcrumbs($breadcrumbs) {
        $breadcrumbs_html = '';
        if ($breadcrumbs) {
            foreach ($breadcrumbs as $item) {
                if(isset($item['url'])) {
                    $breadcrumbs_html .= "<li><a href=\"{$item['url']}\">{$item['title']}</a></li>";
                } else {
                    $breadcrumbs_html .= "<li>{$item['title']}</li>";
                }
            }
        }

        return $breadcrumbs !== false ? '<ul class="breadcrumbs"><li><a href="/">'.System::Lang('MAIN')."</a></li>$breadcrumbs_html</ul>" : '';
    }
}