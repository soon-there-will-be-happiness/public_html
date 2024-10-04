<?php defined('BILLINGMASTER') or die;

class adminSiteController extends AdminBase {


    // СПИСОК ФОРМ
    public function actionFeedbackforms()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_feedback'])) {
            header("Location: /admin");
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        $forms = System::getFeedBackFormList();

        $title = 'Обратная связь - список форм';
        require_once (ROOT . '/template/admin/views/feedback/forms.php');
        return true;
    }



    // СОЗДАТЬ ФОРМУ
    public function actionAddfeedbackform()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_feedback'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        if (isset($_POST['addform']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_feedback'])) {
                System::redirectUrl('/admin');
            }

            $name = htmlentities($_POST['name']);
            $form_desc = htmlentities($_POST['form_desc']);
            $status = intval($_POST['status']);
            $default_form = intval($_POST['default_form']);
            $params = base64_encode(serialize($_POST['form']));

            $add = System::AddForm($name, $form_desc, $status, $default_form, $params);
            System::redirectUrl('/admin/feedback/forms', $add);
        }
        $title = 'Обратная связь - создание формы';
        require_once (ROOT . '/template/admin/views/feedback/add_form.php');
        return true;
    }


    // ИЗМЕНИТЬ ФОРМУ
    public function actionEditfeedbackform($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_feedback'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);

        if (isset($_POST['editform']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_feedback'])) {
                System::redirectUrl('/admin');
            }

            $name = htmlentities($_POST['name']);
            $form_desc = htmlentities($_POST['form_desc']);
            $status = intval($_POST['status']);
            $default_form = intval($_POST['default_form']);
            $params = base64_encode(serialize($_POST['form']));

            $edit = System::editForm($id, $name, $form_desc, $status, $default_form, $params);
            if ($edit) {
                header("Location: /admin/feedback/editform/$id?success");
            }
        }

        $form = System::getFormDataByID($id);
        $params = unserialize(base64_decode($form['params']));
        $title = 'Обратная связь - изменение формы';
        require_once (ROOT . '/template/admin/views/feedback/edit_form.php');
        return true;
    }


    // УДАЛИТЬ ФОРМУ
    public function actionDelfeedbackform($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_feedback'])) {
            header("Location: /admin/feedback");
            exit;
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = System::deleteFeedbackForm($id);

            if ($del) header("Location: ".$setting['script_url']."/admin/feedback/forms?success");
            else header("Location:".$setting['script_url']."/admin/feedback/forms?fail");
        }

    }


    // СПИСОК СООБЩЕНИЙ
    public function actionFeedback()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_feedback'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $messages = System::getFeedBackList();
        if($messages){ 
			foreach ($messages as $el){
				$name_form[$el['id']]=System::getFormDataByID($el['form_id']);
			}        
		}


        $title = 'Обратная связь';
        require_once (ROOT . '/template/admin/views/feedback/index.php');
        return true;
    }



    // ПРОСМОТР СООБЩЕНИЯ
    public function actionViewmessage($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_feedback'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        $id = intval($id);
        $message = System::getFeedbackMessage($id);
        $form = System::getFormDataByID($message['form_id']);
        $params = unserialize(base64_decode($form['params']));

        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_feedback'])) {
                System::redirectUrl('/admin');
            }
            $status = intval($_POST['status']);
            $comment = $_POST['comment'];

            $save = System::saveMessage($id, $status, $comment);
            if ($save) header("Location: /admin/feedback");
        }
        $title = 'Сообщения - просмотр';
        require_once (ROOT . '/template/admin/views/feedback/view.php');
        return true;
    }



    // УДАЛИТЬ СООБЩЕНИЕ
    public function actionDelfeedback($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_feedback'])) {
            header("Location: /admin/feedback");
            exit;
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = System::deleteFeedback($id);

            if ($del) header("Location: ".$setting['script_url']."/admin/feedback");
        }
    }



    // СТАТИЧНЫЕ СТРАНИЦЫ
    public function actionPages()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_pages'])) header("Location: /admin");
        $name = $_SESSION['admin_name'];

        $pages = System::getStaticPages();
        $title = 'Статичные страницы';
        require_once (ROOT . '/template/admin/views/pages/index.php');
        return true;
    }



    // СОЗДАТЬ СТАТИЧНУЮ СТРАНИЦУ
    public function actionAddpage()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_pages'])) {
            System::redirectUrl('/admin');
        }
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        if (isset($_POST['addpage']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {

            if (!isset($acl['change_pages'])) {
                header("Location: /admin");
                exit;
            }
            $name = htmlentities($_POST['name']);
            $status = intval($_POST['status']);
            if (empty($_POST['alias'])) $alias = System::Translit($_POST['name']);
            else $alias = $_POST['alias'];

            if (empty($_POST['title'])) $title = $_POST['name'];
            else $title = $_POST['title'];

            $meta_desc = htmlentities($_POST['meta_desc']);
            $meta_keys = htmlentities($_POST['meta_keys']);

            $curl = htmlentities($_POST['curl']);

            $content = $_POST['content'];
            $in_head = $_POST['in_head'];
            $in_body = $_POST['in_body'];
            $tmpl = intval($_POST['tmpl']);

            if (isset($_POST['custom_code'])) $custom_code = $_POST['custom_code'];
            else $custom_code = null;

            $add = System::addStaticPage($name, $status, $alias, $title, $meta_desc, $meta_keys, $content, $tmpl, $in_head, $in_body,
                $custom_code, $curl);
            if ($add) header("Location: ".$setting['script_url']."/admin/statpages?success");
        }
        $title = 'Статичные страницы - создание';
        require_once (ROOT . '/template/admin/views/pages/add.php');
        return true;
    }



    // ИЗМЕНИТЬ СТРАНИЦУ
    public function actionEditpage($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_pages'])) {
            System::redirectUrl('/admin');
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();

        if (isset($_POST['editpage']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_pages'])) {
                System::redirectUrl('/admin');
            }
            $name = htmlentities($_POST['name']);
            $status = intval($_POST['status']);
            if (empty($_POST['alias'])) $alias = System::Translit($_POST['name']);
            else $alias = $_POST['alias'];

            if (empty($_POST['title'])) $title = $_POST['name'];
            else $title = htmlentities($_POST['title']);

            $meta_desc = htmlentities($_POST['meta_desc']);
            $meta_keys = htmlentities($_POST['meta_keys']);

            $curl = htmlentities($_POST['curl']);

            $content = $_POST['content'];
            $in_head = $_POST['in_head'];
            $in_body = $_POST['in_body'];
            $tmpl = intval($_POST['tmpl']);
            $custom_code = $_POST['custom_code'];
            
            $access_type = intval($_POST['access_type']);
            $groups = !empty($_POST['groups']) ? json_encode($_POST['groups']) : null;
            $planes = !empty($_POST['planes']) ? json_encode($_POST['planes']) : null;

            $edit = System::editPage($id, $name, $status, $alias, $title, $meta_desc, $meta_keys, $content, $tmpl, $in_head,
                $in_body, $custom_code, $curl, $access_type, $groups, $planes);
            if ($edit) header("Location: ".$setting['script_url']."/admin/statpages/edit/$id?success");
        }

        $page = System::getPageData($id);
        $title = 'Статичные страницы - изменение';
        require_once (ROOT . '/template/admin/views/pages/edit.php');
        return true;
    }



    // УДАЛИТЬ СТАТИЧЕСКУЮ СТРАНИЦУ
    public function actionDelpage($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_pages'])) {
            System::redirectUrl('/admin');
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = System::deletePage($id);

            if ($del) header("Location: ".$setting['script_url']."/admin/statpages?success");
        }
    }


    /**
     *  ВИДЖЕТЫ
     */



    public function actionWidgets()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_widgets'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $widgets = Widgets::getAllWidgets();
        $title = 'Виджеты';
        require_once (ROOT . '/template/admin/views/widgets/index.php');
        return true;
    }


    public function actionAddwidget()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_widgets'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $type = isset($_GET['type']) ? htmlentities($_GET['type']) : 'html';

        if (isset($_POST['addwidget']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_widgets'])) {
                System::redirectUrl('/admin');
            }

            $type = $_POST['type'];
            $title = htmlentities($_POST['title']);
            $position = htmlentities(($_POST['position']));
            $page = $_POST['page'];
            $desc = htmlentities($_POST['desc']);
            $affix = 0;
            $params = isset($_POST['widget']) ? serialize($_POST['widget']) : null;
            $sort = intval($_POST['sort']);
            $status = intval($_POST['status']);
            $date = time();
            $private = $_POST['private'];
            $width = intval($_POST['width']);

            $show_header = intval($_POST['show_header']);
            $header = htmlentities($_POST['header']);
            $show_subheader = intval($_POST['show_subheader']);
            $subheader = htmlentities($_POST['subheader']);
            $show_right_button = intval($_POST['show_right_button']);
            $right_button_name = htmlentities($_POST['right_button_name']);
            $right_button_link = htmlentities($_POST['right_button_link']);

            $suffix = htmlentities($_POST['suffix']);
            $show_for_course = isset($_POST['show_for_course']) ? base64_encode(serialize($_POST['show_for_course'])) : null;
            $show_for_training = isset($_POST['show_for_training']) ? json_encode($_POST['show_for_training']) : null;
            $showByGroup = intval($_POST['showByGroup']);
            $showGroups = isset($_POST['showGroups']) ? json_encode($_POST['showGroups']) : null;

            $add = Widgets::addWidget($type, $title, $position, $page, $desc, $affix, $params, $sort, $status, $date, $private,
                $show_header, $header, $show_subheader, $subheader, $show_right_button, $right_button_name, $right_button_link,
                $suffix, $show_for_course, $show_for_training, $width, $showByGroup, $showGroups);

            if ($add) {
                header("Location: ".$setting['script_url']."/admin/widgets");
            }
        }
        $title = 'Виджеты - добавление';
        require_once (ROOT . '/template/admin/views/widgets/add.php');
        return true;
    }



    public function actionEditwidget($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_widgets'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();

        if (isset($_POST['editwidget']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_widgets'])) {
                System::redirectUrl('/admin');
            }

            $title = htmlentities($_POST['title']);
            $position = htmlentities(($_POST['position']));
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $desc = htmlentities($_POST['desc']);
            $affix = 0;
            $suffix = htmlentities($_POST['suffix']);
            $sort = intval($_POST['sort']);
            $status = intval($_POST['status']);
            $show_header = intval($_POST['show_header']);
            $header = htmlentities($_POST['header']);
            $show_subheader = intval($_POST['show_subheader']);
            $subheader = htmlentities($_POST['subheader']);
            $show_right_button = intval($_POST['show_right_button']);
            $right_button_name = htmlentities($_POST['right_button_name']);
            $right_button_link = htmlentities($_POST['right_button_link']);
            $width = intval($_POST['width']);
            $showByGroup = intval($_POST['showByGroup']);
            $showGroups = isset($_POST['showGroups']) ? json_encode($_POST['showGroups']) : null;

            $params = isset($_POST['widget']) ? serialize($_POST['widget']) : null;
            $private = $_POST['private'];
            $show_for_course = isset($_POST['show_for_course']) ? base64_encode(serialize($_POST['show_for_course'])) : null;
            $show_for_training = isset($_POST['show_for_training']) ? json_encode($_POST['show_for_training']) : null;

            $add = Widgets::editWidget($id, $title, $position, $page, $desc, $affix, $params, $sort, $status, $private,
                $show_header, $header, $show_subheader, $subheader, $show_right_button, $right_button_name, $right_button_link,
                $suffix, $show_for_course, $show_for_training, $width, $showByGroup, $showGroups
            );
            if ($add) {
                header("Location: ".$setting['script_url']."/admin/widgets/edit/$id?success");
            }
        }

        $widget = Widgets::getWidgetData($id);
        $pages = Widgets::getWidgetsPage($id);
        $params = unserialize($widget['params']);
        $show = $widget['show_for_course'] != null ? unserialize(base64_decode($widget['show_for_course'])) : [];
        $show_training = $widget['show_for_training'] != null ? json_decode($widget['show_for_training'], true) : [];

        $title = 'Виджеты - редактирование';
        require_once (ROOT . '/template/admin/views/widgets/edit.php');
        return true;
    }


    public function actionDelwidget($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_widgets'])) {
            System::redirectUrl('/admin');
        }
        $setting = System::getSetting();
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Widgets::deleteWidget($id);

            if ($del) {
                header("Location: /admin/widgets");
            }
        }
    }

    public function actionChangeWidgetStatus($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['del_widgets'])) {
            System::redirectUrl('/admin');
        }

        $setting = System::getSetting();
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $status = intval($_REQUEST['status']);

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $res = Widgets::setWidgetStatus($id, $status);

            if ($res) {
                header("Location: /admin/widgets");
            }
        }
    }


    /**
     * @param $id
     */
    public function actionDelAdminNotices($id) {
        if (isset($_POST['del_notices'])) {
            $res = AdminNotice::delNotices((int)$id);
            header("Content-type: application/json; charset=utf-8");
            echo json_encode(['status' => $res]);
        }
    }


    /**
     *
     */
    public function actionReadAdminNotices() {
        if (isset($_POST['read_notices'])) {
            $res = AdminNotice::updStatusNotices(0);
            header("Content-type: application/json; charset=utf-8");
            echo json_encode(['status' => $res]);
        }
    }
}