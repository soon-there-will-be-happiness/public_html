<?php defined('BILLINGMASTER') or die;


class baseController {

    protected $settings;
    protected $main_settings;
    protected $params;
    protected $template_path;
    protected $layouts_path;
    protected $views_path;
    protected $widgets_path;
    protected $view;
    protected $seo;
    protected $extension;
    protected $user_id;
    protected $user;
    protected $widgets;
    protected $show_top_menu;


    /**
     * baseController constructor.
     */
    public function __construct() {
        $this->settings = System::getSetting(true);
        $this->main_settings = System::getSettingMainpage();
        $this->template_path = ROOT."/template/{$this->settings['template']}";
        $this->layouts_path = Template::getLayoutsPath($this->settings);
        $this->widgets_path = Template::getWidgetsPath($this->settings);
        $this->show_top_menu = true;

        $this->view = [
            'is_page' => '',
            'use_css' => false,
            'in_head' => null,
            'in_bottom' => null,
            'content_class' => '',
            'main_content_class' => '',
            'noindex' => null,
            'body_class' => '',
        ];

        $this->seo = [
            'title' => '',
            'meta_desc' => '',
            'meta_keys' => '',
            'h1' => '',
        ];

        $this->extension = null;
        $this->user_id = (int)User::isAuth();
        $this->user = $this->user_id ? User::getUserById($this->user_id) : null;
        $this->widgets = null;
    }


    /**
     * @param string $is_page
     * @param null $path
     * @param array $breadcrumbs
     * @param array $params
     * @param string $body_class
     * @param string $content_class
     * @param bool $use_css
     * @param null $in_head
     * @param null $in_bottom
     */
    protected function setViewParams($is_page = '', $path = null, $breadcrumbs = [], $params = [], $body_class = '',
                                      $content_class = 'content-wrap', $use_css = true, $in_head = null, $in_bottom = null) {

        $this->view = [
            'is_page' => $is_page,
            'use_css' => $use_css,
            'in_head' => $in_head,
            'in_bottom' => $in_bottom,
            'main_content_class' => $this->view['main_content_class'],
            'noindex' => $this->view['noindex'],
        ];

        if ($path) {
            $this->view['path'] = Template::getPath($path, $this->extension);
            $this->view['breadcrumbs'] = $breadcrumbs;
            $this->view['hero'] = $params && isset($params['hero']) ? $params['hero'] : null;
            $this->view['body_class'] = $body_class;
            $this->view['content_class'] = $content_class;
            $this->params = $params;
        }

        $this->widgets = Widgets::getWidgets($this->view['is_page'], $this->user_id);
    }


    /**
     * @param $path
     */
    public function setViewPath($path) {
        $this->view['path'] = Template::getPath($path, $this->extension);
    }


    /**
     * @param $title
     * @param string $meta_desc
     * @param string $meta_keys
     * @param string $h1
     */
    public function setSEOParams($title, $meta_desc = '', $meta_keys = '', $h1 = '') {
        $this->seo['title'] = $title;
        $this->seo['meta_desc'] = $meta_desc;
        $this->seo['meta_keys'] = $meta_keys;
        $this->seo['h1'] = $h1;
    }
}