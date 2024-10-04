<?defined('BILLINGMASTER') or die;

class trainingBaseController extends \baseController {

    protected $tr_settings;
    protected $page_settings;
    protected $en_extension;

    protected $title;
    protected $meta_desc;
    protected $meta_keys;
    protected $h1;
    protected $h2;
    protected $is_page;
    protected $view_path;
    protected $use_css;


    /**
     * trainingController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->en_extension = System::CheckExtensension('training', 1);
        if (!$this->en_extension) {
            require_once (ROOT . "/template/{$this->settings['template']}/404.php");
        }

        $this->tr_settings = Training::getSettings();
        $this->extension = 'training';
    }


    /**
     * ЗАДАТЬ ОСНОВНЫЕ НАСТРОЙКИ СТРАНИЦЫ
     * @param $data
     * @param $is_page
     * @param null $view_path
     * @param string $body_class
     * @param string $content_class
     * @param bool $use_css
     */
    public function setPageSettings($data, $is_page, $view_path = null, $body_class = '', $content_class = '',
                                    $use_css = true) {
        $title = $meta_desc = $meta_keys = $h1 = '';

        if ($data) {
            $title = $data['title'];
            $meta_desc = isset($data['meta_desc']) ? $data['meta_desc'] : $data['desc'] ?? "";
            $meta_keys = isset($data['meta_keys']) ? $data['meta_keys'] : $data['keys'] ?? "";
            $h1 = isset($data['h1']) ? $data['h1'] : $data['name'];
        }

        if ($view_path == 'layouts/no_access.php') {
            $title = 'Нет доступа';
        }

        $this->extension = 'training';
        $this->setSEOParams($title, $meta_desc, $meta_keys, $h1);

        $this->setViewParams($is_page, $view_path, false, null, $body_class, '', $use_css);
        $this->view['main_content_class'] = $content_class;
    }
}