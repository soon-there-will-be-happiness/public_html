<?php defined('BILLINGMASTER') or die; 

// Simple

class adminTemplateController extends AdminBase {

    public function actionIndex()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }

        $name = $_SESSION['admin_name'];
        $settings = System::getSetting();

        $main_settings = System::getSettingMainpage();
        $ext = 'new_simple';

        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_main_tunes'])) {
                System::redirectUrl("/admin");
            }

            $status = intval($_POST['status']);
            $save = System::SaveExtensionSetting($ext, '', $status);

            $save2 = TemplateNewSimple::updSystemSettings($settings['logotype'], $_POST['settings']['phone'], $_POST['settings']['phone_link'],
                $settings['fix_head'], $settings['socbut'], $_POST['settings']['counters'], $_POST['settings']['counters_head']
            );
            $save3 = TemplateNewSimple::updSystemMainSettings($main_settings['slogan'], htmlentities($_POST['main_settings']['sidebar']),
                $main_settings['copyright'], $_POST['main_settings']['custom_css']
            );

            System::redirectUrl('/admin/new-simple/settings', $save && $save2 && $save3);
        }
        
        $enable = System::getExtensionStatus($ext);
        $ext_settings = TemplateNewSimple::getSettings($ext);

        require_once(ROOT . '/extensions/new_simple/views/admin/settings/index.php');
    }
    
    
    
    
    public function actionHeader() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }

        $ext = 'new_simple';
        $name = $_SESSION['admin_name'];
        $settings = System::getSetting();
        $ext_settings = TemplateNewSimple::getSettings($ext);
        $main_settings = System::getSettingMainpage();

        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_main_tunes'])) {
                System::redirectUrl("/admin");
            }

            $save = NewSimpleHeader::updSystemSettings($_POST['settings']['logotype'], intval($_POST['settings']['fix_head']));
            $save2 = NewSimpleHeader::updSystemMainSettings($_POST['main_settings']['slogan']);

            System::redirectUrl('/admin/new-simple/settings/header', $save && $save2);
        }

        require_once(ROOT . '/extensions/new_simple/views/admin/settings/header.php');
    }


    public function actionFooter() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_main_tunes'])) {
            System::redirectUrl("/admin");
        }

        $ext = 'new_simple';
        $name = $_SESSION['admin_name'];
        $settings = System::getSetting();
        $main_settings = System::getSettingMainpage();

        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_main_tunes'])) {
                System::redirectUrl("/admin");
            }

            $politics_link = htmlentities($_POST['main_settings']['politics_link']);
            $offer_link = htmlentities($_POST['main_settings']['offer_link']);

            $save = NewSimpleFooter::updSystemSettings(base64_encode(serialize($_POST['settings']['socbut'])));
            $save2 = NewSimpleFooter::updSystemMainSettings($_POST['main_settings']['copyright'], $politics_link,
                $_POST['main_settings']['politics_text'], $offer_link, $_POST['main_settings']['offer_text']
            );
            System::redirectUrl('/admin/new-simple/settings/footer', $save && $save2);
        }

        require_once(ROOT . '/extensions/new_simple/views/admin/settings/footer.php');
    }
    
}