<?php defined('BILLINGMASTER') or die;

class siteController extends baseController {

    /**
     * MAIN PAGE
     * @return bool
     */
    public function actionIndex() {
        // Проверка авторизации
        $userId = User::isAuth();
        if ($userId) {
            $user = User::getUserById($userId);
        }

        $this->setSEOParams($this->main_settings['main_page_title'], $this->main_settings['main_page_desc'],
            $this->main_settings['main_page_keys']
        );
        $this->setViewParams('main', '', null, null, '',
            '', true, $this->settings['in_head'], $this->settings['in_body']
        );

        if ($this->main_settings['main_page_content'] == 1) { // ОБЫЧНЫЙ МАКЕТ
            if ($this->main_settings['main_page_tmpl'] == 1) {
                $this->setViewParams('main', 'index.php', false,
                    null, 'main-page'
                );

                require_once ("{$this->template_path}/main.php");
            } else {
                $this->view['use_css'] = 0;
                $this->view['no_tmpl'] = 1;
                require_once ("{$this->layouts_path}/head.php");
                echo "<body id=\"page\">{$this->main_settings['main_page_text']}{$this->view['in_bottom']}</body></html>";
            }
        } elseif (in_array($this->main_settings['main_page_content'], [2, 4])) { // Макет онлайн курсов
            if (!System::CheckExtensension('courses', 1)) {
                ErrorPage::return404();
            }

            $params = unserialize(base64_decode(Course::getCourseSetting()));
            $user = intval(User::isAuth());
            $cat_name = $cats = false;

            if (!isset($_GET['category'])) { // Если в URL нет параметров для категории
                $this->setSEOParams($params['params']['title'], $params['params']['desc'],
                    $params['params']['keys'], $params['params']['h1']
                );

                if ($this->main_settings['main_page_content'] == 4) {
                    $courses = Course::getAllCourseList(1); // Получить все опубликованные курсы вообще
                } else {
                    $cats = Course::getCourseCatFromList(1);
                    $courses = Course::getCourseList(1, 0); // Получить курсы без категории
                }
            } else {
                $alias = htmlentities($_GET['category']);
                $cat_data = Course::getCatDataByAlias($alias); // Получить данные категории по алиасу

                if ($cat_data) {
                    $cat_name = $cat_data['name'];
                    $this->setSEOParams($cat_data['title'], $cat_data['meta_desc'], $cat_data['meta_keys']);

                    $courses = Course::getCourseList(1, $cat_data['cat_id']); // Получить курсы данной категории
                } else {
                    ErrorPage::return404();
                }
            }

            $this->setViewParams('main', 'course/index.php', false, $params['params'], '',
                'content-courses', true, $this->settings['in_head'], $this->settings['in_body']
            );

            require_once ("{$this->template_path}/main.php");
        } elseif ($this->main_settings['main_page_content'] == 3) { // СТРАНИЦА ВХОДА
            if (!$userId) {
                $all_widgets = Widgets::getWidgets($this->view['is_page'], $userId);
                $sidebar = Widgets::RenderWidget($all_widgets, 'sidebar');
                $this->view['main_content_class'] = !$sidebar ? 'content-lk' : '';
                $content_class = $sidebar ? 'content-wrap' : '';

                $this->setViewParams('main', 'users/login.php', false, null,
                    'authorization-page', $content_class, true, $this->settings['in_head'],
                    $this->settings['in_body']
                );

                require_once ("{$this->template_path}/main.php");
            } else {
                $url = User::redirectFromEnter($this->settings['login_redirect'], false);
                System::redirectUrl($url);
            }
        } elseif ($this->main_settings['main_page_content'] == 5) { // ЗАГРУЗКА ВНЕШНЕГО URL
            $ch = curl_init($this->main_settings['external_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $html = curl_exec($ch);
            curl_close($ch);

            echo $html;
        } elseif ($this->main_settings['main_page_content'] == 6) { // БЛОГ
            $en_blog = System::CheckExtensension('blog', 1);
            if (!$en_blog) {
                ErrorPage::return404();
            }

            $now = time();
            $params = unserialize(System::getExtensionSetting('blog'));
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

            $show_items = $params['params']['postcount'];
    		$sort = isset($params['params']['sort']) ? $params['params']['sort'] : 'post_id';
            $post_list = Blog::getPostPublicList($now, 0, $page, $show_items, $sort);
            $total_post = Blog::countAllPost(0, 1);
            $is_pagination = false;

            if ($total_post > $show_items) {
                $is_pagination = true;
                $pagination = new Pagination($total_post, $page, $show_items);
            }

            $this->setViewParams('main', 'blog/index.php', [
                    ['title' => System::Lang('BLOG'), "url" => '/blog'],
                ], $params['params'], 'blog-page', 'content-wrap', true,
                $this->settings['in_head'], $this->settings['in_body']
            );

            require_once ("{$this->template_path}/main.php");
        } elseif ($this->main_settings['main_page_content'] == 7 && System::CheckExtensension('training', 1)) {
            require_once (ROOT . "/extensions/training/views/frontend/main_page/training.php");
        }
		
        return true;
    }
    
    
    // СТРАНИЦА ПОЛИТИКИ
    public function actionPolitika()
    {
        $this->setSEOParams($this->main_settings['politika_link']);

        $params['params']['commenthead'] = null;
        $params['params']['commenthead'] = null;

        $page['in_head'] = '<style>#page {padding:5%}</style>';
        $page['in_body']= null;
        $page['content'] = $this->main_settings['politika_text'];

        $this->setViewPath('static/static_nostyle.php');
        require_once($this->view['path']);
        return true;
    }
    
    
    // СТРАНИЦА ОФЕРТЫ
   /* public function actionOferta()
   {
       $params['params']['commenthead'] = null;
       $page['in_head'] = '<style>#page {padding:5%}</style>';
       $page['in_body']= null;
       $page['content'] = $this->main_settings['oferta_text'];
   
       $this->setSEOParams($this->main_settings['oferta_link']);
   
       $this->setViewPath('static/static_nostyle.php');
       require_once($this->view['path']);
       return true;
   } */
    
    
    // +KEMSTAT-8
    public function actionOferta()
    {
        $partner_id = null;

        // Проверяем GET-параметр
        if (isset($_GET['partner_id']) && ctype_digit($_GET['partner_id'])) {
            $partner_id = intval($_GET['partner_id']);
        }

        // Проверяем cookie, если GET-параметр отсутствует
        elseif (isset($_COOKIE['aff_billingmaster']) && ctype_digit($_COOKIE['aff_billingmaster'])) {
            $partner_id = intval($_COOKIE['aff_billingmaster']);
        }

        $partner_data = Aff::getPartnerReq($partner_id);
        $params['params']['commenthead'] = null;
        $page['in_head'] = '<style>#page {padding:5%}</style>';
        $page['in_body']= null;
        if($partner_data)
        {
            $setting_main = System::getSettingMainpageBySecondId();
            $partner_req = $partner_data['requsits'];
            $data = unserialize($partner_req);
            // Извлечение fio и inn
            $fio = $data['rs']['fio'];
            $inn = $data['rs']['inn'];
            
            $user = User::getUserDataForAdmin($partner_id);
            $email = $user['email'];
            $phone = $user['phone'];
            $page['content'] =$setting_main['oferta_text'];
            $replace = array(
                        '[FIO]' => !empty($fio)?$fio:"Ivanov",
                        '[INN]' => !empty($inn)?$inn:"000000000",
                        '[EMAIL]' => !empty($email)?$email:"example@exp.com",
                        '[PHONE]' => !empty($phone)?$phone:"+789999999999",
                    );
            $page['content'] = strtr($page['content'], $replace);
        } else{
            $page['content'] = $this->main_settings['oferta_text'];
        }
        
        $this->setSEOParams($this->main_settings['oferta_link']);
        $this->setViewPath('static/static_nostyle.php');
        require_once($this->view['path']);
        return true;
    }
    // -KEMSTAT-8
    
    
    // СТАТЧИНАЯ СТРАНИЦА
    public function actionPage($alias)
    {
        $alias = htmlentities($alias);
        $page = System::getPageDataByAlias($alias, 1);
        $userId = User::isAuth();
        $access = true;
        if ($page['access_type'] > 0) {
            $access = $userId ? Access::getAccesstoUser($userId, $page['access_type'], $page['groups'], $page['planes']) : false;
        }
        
        // Проверка авторизации
        if ($userId) {           
            $user = User::getUserById($userId);
        }
        
        if ($page) {
			$curl = $page['curl'];

            $this->setSEOParams($page['title'], $page['meta_desc'], $page['meta_keys']);
            $this->setViewParams('static');

            if ($access) {
                if (!empty($page['curl'])) {
                    $this->setViewPath('static/static_curl.php');
                    require_once($this->view['path']);
                } elseif ($page['tmpl'] == 1) {
                    $this->setViewParams('static', 'static/static.php', [['title' => $page['name']]],
                        null, 'invert-page static-page', 'content-wrap'
                    );

                    require_once ("{$this->template_path}/main.php");
                } else {
                    $this->setViewPath('static/static_nostyle.php');
                    require_once($this->view['path']);
                }
                return true;   
            } else {
                $this->setViewPath('static/static_noaccess.php');
                require_once($this->view['path']);
            }
        } else {
            ErrorPage::return404();
        }
        return true;
    }
    
    
    public function actionAmbassador(){
        require_once (ROOT.'/st/ambassador/page.php');
        return true;
    }
    
    public function actionPartner(){
        require_once (ROOT.'/st/partner/page.php');
        return true;
    }

    public function actionKemstat7_11(){
        require_once (ROOT.'/st/kemstat7_11/page.php');
        return true;
    }


    public function actionKemstat12_17(){
        require_once (ROOT.'/st/kemstat12_17/page.php');
        return true;
    }
    
    public function actionFree(){
        require_once (ROOT.'/st/free/page.php');
        return true;
    }
    
    public function actionKemstat()
    {  
        require_once (ROOT.'/st/kemstat/page.php');
        return true;
    }    
    
    
    // СТРАНИЦА АКЦИЙ КРАСНАЯ ЦЕНА
    public function actionSales()
    {
        $page = Product::getSalesPage();
        $params = !empty($page['param']) ? unserialize(base64_decode($page['param'])) : null;
        $params['params']['heroheader'] = $params['h1'];
        
        // Проверка авторизации
        $userId = User::isAuth();
        if ($userId) {
            $user = User::getUserById($userId);
        }

        $accumulative_discount = Product::getSaleList(1, [5]);
        $list_product = !($accumulative_discount) ? Product::getSaleProduct() : null;
        
        if ($params != null && $params['enable_page'] != 1) {
            ErrorPage::return404();
        }

        if ($params != null) {
            $this->setSEOParams($params['title'], $params['meta_desc'], $params['meta_desc'], $params['h1']);
        } else {
            $this->setSEOParams('Скидки', 'Товары со скидкой', $params['meta_desc'], 'Скидки');
        }

        $this->setViewParams('sale', 'product/sales.php', [
                ['title' => System::Lang('DISCOUNTS')],
            ], $params['params'], 'invert-page sale-page', 'content-wrap'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    // ОБРАТНАЯ СВЯЗЬ
    public function actionFeedback()
    {
        if ($this->settings['enable_feedback'] == 0) {
            ErrorPage::return404();
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $form = System::getFormDataByDefault($id);

        if ($form) {
            $params = unserialize(base64_decode($form['params']));

            $this->setSEOParams($params['params']['title'], $params['params']['meta_desc'], $params['params']['meta_keys']);
            $this->setViewParams('feedback');

            $site_name = $this->settings['site_name'];
            $now = time();
        
            if (isset($_POST['feedback']) && is_numeric($_POST['time']) && isset($_SESSION['feedback'])  && isset($_POST['token_sm'])) {
                if (($now - intval($_POST['time'])) < $params['params']['min_time']) {
                    ErrorPage::returnError('Ошибка отправки сообщения');
                }

                //Если включена капча, то проверка
                $reCaptcha = json_decode($this->settings['reCaptcha'], true);
                if (isset($reCaptcha['enable']) && $reCaptcha['enable'] == 1) {
                    $reCaptcha = new recaptcha($_POST['g-recaptcha-response'], $reCaptcha['reCaptchaSecret'], $reCaptcha['minimalScoreVerifyValue']);
                    if (!$reCaptcha->checkCaptcha()) {
                        ErrorPage::returnError('you are robot!');
                    }
                }
                $name = null;
                // Здесь в форме если авторизован подставляем email пользователя, хотя в дальнейшем он может быть и переопределен
                // если есть такое поле.
                $userId = User::isAuth();
                if ($userId) {
                    $user = User::getUserById($userId);
                }
                $email = isset($user) ? $user['email'] : null;
                $phone = $text = $field1 = $field2 = null;
                $field1_name = $params['params']['field1_name'];
                $field2_name = $params['params']['field2_name'];

                if (!empty($params['params']['redirect'])) {
                    $url = $params['params']['redirect'];
                }  else {
                    $url = isset($_GET['id']) ? "/feedback?success&id=$id" : "/feedback?success";
                }

                if (isset($_POST['name'])) $name = htmlentities($_POST['name']);
                if (isset($_POST['email'])) $email = htmlentities($_POST['email']);
                if (isset($_POST['text'])) $text = htmlentities($_POST['text']);
                if (isset($_POST['phone'])) $phone = htmlentities($_POST['phone']);
                
                $reply_to = array($name, $email);

                if (isset($_POST['field1'])) {
                    $field1 = is_array($_POST['field1']) ? implode(",", $_POST['field1']) : htmlentities($_POST['field1']);
                }
                if (isset($_POST['field2'])) {
                    $field2 = is_array($_POST['field2']) ? implode(",", $_POST['field2']) : htmlentities($_POST['field2']);
                }

                if (!empty($params['params']['letter'])) {
                    // Отправляем письмо на указанный емейл
                    $subj = !empty($params['params']['letter_subj']) ? $params['params']['letter_subj'] : "# Сообщение с сайта $site_name";
                    $letter = $params['params']['letter'];

                    $replace = array(
                        '[NAME]' => $name,
                        '[CLIENT_NAME]' => $name,
                        '[EMAIL]' => $email,
                        '[PHONE]' => $phone,
                        '[TEXT]' => $text,
                    );

                    $user_text = strtr($letter, $replace);
                    $user_text = str_replace(array("\r\n", "\r", "\n"), '<br>', $user_text);
                    $user_send = Email::SendMessageToBlank($email, $name, $subj, $user_text);
                }
                if ($phone) {
                    $phone = "Телефон: ".$phone;
                }

                $text_to_admin = "<p>$name отправил(-а) вам сообщение<br />Email: $email<br />$phone<br /></p>
                <p>$field1_name $field1<br />$field2_name $field2</p>".$text."
                <p><a href='mailto:$email?subject=Re: Ответ на сообщение с сайта $site_name'>Написать ответ</p>";


                if (isset($params['params']['recipient']) && !empty($params['params']['recipient'])) {
                    $admin_recipient = $params['params']['recipient'];
                } else {
                    $admin_recipient = $this->settings['admin_email'];
                }

                $text_to_admin = str_replace(array("\r\n", "\r", "\n"), '<br>', $text_to_admin);

                $lastid = System::getLastFeedbackId();
                if (is_array($lastid)) {
                    $lastid = $lastid['maxid'];
                }
                $lastid = $lastid ?? 1 + 1;
                $str = date("Y-m-d");
                $send = Email::SendMessageToBlank($admin_recipient, $name, "# Сообщение с сайта $site_name #$lastid-$str", $text_to_admin, null, false, $reply_to);

                if ($send) {
                    if ($this->settings['write_feedback'] == 1) {
                        $write = System::writeFeedback($name, $email, $phone, $field1, $field2, $text, $form['form_id']);
                        if ($write) {
                            System::redirectUrl($url);
                        } else {
                            ErrorPage::returnError('Error');
                        }
                    } else {
                        System::redirectUrl($url);
                    }
                }
            }

            $this->setViewParams('feedback', 'feedback.php', false, $params['params'],
                'invert-page feedback-page', 'content-wrap'
            );

            require_once ("{$this->template_path}/main.php");
        } else {
            echo 'Не назначено формы по-умолчанию';
        }

        return true;
    }
    
    
    public function actionSitemap()
    {
        $menu_items = System::getMenuItemsForSiteMap();
        
        require_once ("{$this->template_path}/xml_sitemap.php");
        return true;
    }
}

