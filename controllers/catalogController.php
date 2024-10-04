<?php defined('BILLINGMASTER') or die;

class catalogController extends baseController {


    // ПРОСМОТР КАТАЛОГА
    public function actionCatalog() {
        
        $params = json_decode($this->settings['params'], true);
        $currency_list = false;
        if (isset($params['many_currency']) && $params['many_currency'] == 1) {
            $currency_list = Currency::getCurrencyList();
        }
        
        if ($this->settings['enable_catalog'] == 0) {
            ErrorPage::return404();
        }
        
        if (isset($_GET['cat'])) {
            $category = htmlentities($_GET['cat']);
            $category = Product::getCatDataByAlias($category); // id категории
            if ($category) {
                $category_data = Product::getCatData($category);
                $list_product = Product::getProductInCatalog($category);
                $this->setSEOParams($category_data['cat_title'], $category_data['cat_meta_desc'],
                    $category_data['cat_keys'], $category_data['cat_name']
                );
            } else {
                ErrorPage::return404();
            }
        } else {
            $filter = [
                'id' => isset($_GET['cat_id']) ? System::getSecureData($_GET['cat_id']) : null,
                'type' => isset($_GET['type']) && $_GET['type'] != 'all' ? $_GET['type'] : null,
            ];

            $this->setSEOParams($this->settings['catalog_title'], $this->settings['catalog_desc'],
                $this->settings['catalog_keys'], $this->settings['catalog_h1']
            );

            $list_product = Product::getProductsByFilter($filter);
        }

        $this->setViewParams('catalog', 'product/catalog.php', [['title' => System::Lang('CATALOG')]],
            null, 'invert-page catalog-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    
    // ПРОСМОТР ПРОДУКТА
    public function actionLanding($alias)
    {
        $product = Product::getProductDataByAlias($alias); // Данные продукта, если ничего нет, то переход на стр. 404
        if (!$product || $this->settings['enable_landing'] == 0) {
            ErrorPage::return404();
        }

        if($product['product_access'] == 2) Product::checkProductAvailableToUser($product);

        $params = json_decode($this->settings['params'], true);
        $currency_list = false;
        if (isset($params['many_currency']) && $params['many_currency'] == 1) {
            $currency_list = Currency::getCurrencyList();
        }
        
        $product_id = $product['product_id'];
        $this->setSEOParams($product['product_title'], $product['meta_desc'], $product['meta_keys']);
        $this->setViewParams('viewproduct');

        // Сегментация
        $blog = System::CheckExtensension('blog', 1);
        if ($blog) {
            $user_id = User::isAuth();
            $no_count = array(1, 3);
            if ($user_id && !in_array($user_id, $no_count)) {
                $url = htmlentities($_SERVER["REQUEST_URI"]);
                $url = explode("?", $url);
                $url = $url[0];
                $segment = Blog::Segmentation($user_id, $url);
            }
        }
        
        // СПЛИТ ТЕСТ
        $cookie_split = $this->settings['cookie'].'_split'; // Сформировали имя куки
        
        if (empty($product['product_text2'])) { // Если 2-ого варианта нет
            $var = 1;
            $text_lp = $product['product_text1'];
            
            if (isset($_COOKIE["$cookie_split"])) {
                $cookie_arr = json_decode($_COOKIE["$cookie_split"], true);
                $cookie_arr[$product['product_id']] = $var;
                setcookie("$cookie_split", json_encode($cookie_arr), time()+3600*24*30*12, '/');
            } else {
                $cookie_arr[$product['product_id']] = $var;
                setcookie("$cookie_split", json_encode($cookie_arr), time()+3600*24*30*12, '/');   
            }
        } else { // Если 2-ой вариант есть
            // Проверяем наличие куки
            if (isset($_COOKIE["$cookie_split"])) {
                $cookie_arr = json_decode($_COOKIE["$cookie_split"], true);
                
                if (array_key_exists($product['product_id'], $cookie_arr)) {
                    $var = intval($cookie_arr[$product['product_id']]); // вариант описания
                    $text_lp = $product["product_text$var"];
                } else {
                    $var = rand(1,2); // генерим вариант
                    $cookie_arr[$product['product_id']] = $var; // новое значение для массива
                    $text_lp = $product["product_text$var"];
                    setcookie("$cookie_split", json_encode($cookie_arr), time()+3600*24*30*12, '/');
                    
                    $_SESSION["$cookie_split"] = json_encode($cookie_arr); // дублируем в сессию
                }
            } else { // если куки нет, генерим вариант
                $var = rand(1,2);
                $text_lp = $product["product_text$var"];
                $cookie_arr[$product['product_id']] = $var;
                setcookie("$cookie_split", json_encode($cookie_arr), time()+3600*24*30*12, '/');
                
                $_SESSION["$cookie_split"] = json_encode($cookie_arr); // дублируем в сессию
            }
        }
        
        $variant = "hits_$var";
        $hits = Product::updateHits($product['product_id'], $variant, $product["$variant"] + 1);
        $text_tmpl = 'text'.$var.'_tmpl';
        $text_heading = 'text'.$var.'_heading';

        if (isset($_GET['viewmodal'])) {
            $params['params']['commenthead'] = null;
            $page['in_head'] = '<style>#page {padding:5%}</style>';
            $page['in_body']= null;
            $page['content'] = $text_lp;

            $this->setViewPath('static/static_nostyle.php');
            require_once ($this->view['path']);
            exit;
        }

        if ($product[$text_tmpl] == 0) {
            $this->view['use_css'] = 0;
            $this->view['no_tmpl'] = 1;
            $text_head = "text{$var}_head";
            $text_bottom = "text{$var}_bottom";
            $this->view['in_head'] = $product[$text_head];
            $this->view['in_bottom'] = $product[$text_bottom];
            $this->setViewPath('product/no_view.php');
            require_once ($this->view['path']);
        } else {
            $path = $product[$text_tmpl] == 1 ? 'product/view.php' : 'product/card.php';

            if ($product['in_catalog'] == 1) {
                $this->setViewParams('viewproduct', $path, [
                    ['title' => System::Lang('CATALOG'), 'url' => '/catalog'],
                    ['title' => $product['product_name']]
                ], null, 'invert-page product-card-page'
                );
            } else {
                $this->setViewParams('viewproduct', $path, false,
                    null, 'invert-page product-card-page'
                );
            }

            require_once ("{$this->template_path}/main.php");
        }
        return true;
    }


    // СПИСОК ОТЗЫВОВ
    public function actionReviews() {
        if ($this->settings['enable_reviews'] == 0) {
            ErrorPage::return404();
        }

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total = Product::countReviews(1);
        $reviews_tune = unserialize(base64_decode($this->settings['reviews_tune']));
        $list_reviews = Product::getReviews(1, null, null, $this->settings['show_items'], $page);   
        
        $is_pagination = $total > $this->settings['show_items'] ? true : false;
        $pagination = new Pagination($total, $page, $this->settings['show_items']);

        $this->setSEOParams($reviews_tune['title'], $reviews_tune['meta_desc'],
            $reviews_tune['meta_keys'], $reviews_tune['h1']
        );
        $this->setViewParams('reviews','product/reviews.php', [['title' => System::Lang('REVIEWS')]],
            null,'reviews-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    // ДОБАВИТЬ ОТЗЫВ
    public function actionAddreview()
    {
        if ($this->settings['enable_reviews'] == 0) {
            ErrorPage::return404();
            
        } elseif($this->settings['enable_reviews'] == 2){
            $user_id = User::isAuth();
            if(!$user_id) header("Location:/login");
        }

        $now = time();
        $max_upload = $this->settings['max_upload'] * 1024 * 1024;
        $reviews_tune = unserialize(base64_decode($this->settings['reviews_tune']));

        if (isset($_POST['addreview']) && !empty($_POST['review']) && !empty($_POST['name']) && is_numeric($_POST['time'])) {
            $_SESSION['review'] = 1;
            $time = intval($_POST['time']);
            if (($now - $time) < 4) {
                ErrorPage::returnError('Error undefined');
            }
            
            $name = htmlentities($_POST['name']);
            
            $chars = ['(',')']; // символы для удаления
            $name = str_replace($chars, '', $name); // PHP код
        
        
            $email = isset($_POST['email']) ? htmlentities($_POST['email']) : null;
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ErrorPage::returnError("E-mail адрес '$email' указан неверно.");
            }
            
            $site_url = isset($_POST['site_url']) ? htmlentities($_POST['site_url']) : null;
            $vk_url = isset($_POST['vk_url']) ? htmlentities($_POST['vk_url']) : null;
            $fb_url = isset($_POST['fb_url']) ? htmlentities($_POST['fb_url']) : null;
            $range = isset($_POST['range']) ? intval($_POST['range']) : null;
            $review = htmlentities($_POST['review']);
            $error = false;
            $img = null;

            if (isset($_FILES["photo"]["tmp_name"]) && $_FILES["photo"]["size"] > 0) {
                // тип файла 
                if ($_FILES["photo"]["type"] != "image/gif" && $_FILES["photo"]["type"] != "image/jpeg" && $_FILES["photo"]["type"] != "image/png") {
                    $error = 'Не верный тип файла';
                }

                if ($_FILES["photo"]["size"] > $max_upload) {
                    $error .= 'Файл слишком большой';
                }
                   
                $imageinfo = getimagesize($_FILES["photo"]["tmp_name"]);
                if ($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/png' && $imageinfo['mime'] != 'image/jpeg') {
                    $error .= 'Не верный тип файла';
                }

                if ($error == false) {
                    $mime = explode("/",$imageinfo['mime']);

                    $tmp_name = $_FILES["photo"]["tmp_name"]; // Временное имя картинки на сервере
                    //$img = $_FILES["photo"]["name"]; // Имя картинки при загрузке
                    $img = md5($now).'.'.$mime[1];

                    $folder = ROOT . '/images/reviews/'; // папка для сохранения
                    $path = $folder . $img; // Полный путь с именем файла
                    if (is_uploaded_file($tmp_name)) {
                        move_uploaded_file($tmp_name, $path);
                    }
                }
            }
            
            if ($error == false) {
                $add = Product::addReview($time, $name, $email, $site_url, $vk_url, $fb_url, $review, $range, $img);
                $subject = System::Lang('NEW_REVIEW_SUBJ');
                $text = System::Lang('NEW_REVIEW_TEXT');
                $text .= '<p><a href="'.$this->settings['script_url'].'/admin?key='.$this->settings['security_key'].'">Перейти</a></p>';
                $send = Email::SendMessageToBlank($this->settings['admin_email'], 'BM', $subject, $text);
                System::redirectUrl("/reviews/add?success");
            } else {
                System::redirectUrl("/reviews/add?fail=$error");
            }
        }

        $this->setSEOParams('Добавить отзыв');
        $this->setViewParams('reviews', 'product/review_add.php', false,
            null, 'reviews-add-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }


    public function actionGetProductDataByApi($id) {
        $product = Product::getProductById($id); // Данные продукта

        header('Content-Type: application/json; charset=utf-8;');//хедер для json
        //Хедеры для CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: token, Content-Type');
        header('Access-Control-Max-Age: 1728000');

        $price = Price::getFinalPrice($id);
        //Если продукта не существует или выключен, то вернуть 404
        if (!$product) {
            http_response_code(404);
            echo json_encode([ 'status' => false ]);
            exit();
        }

        //Потоки
        $flows = Flows::getFlowForProduct($id);

        $productFlows = false;
        if ($flows) {
            $productFlows = Flows::getActualFlowByIDs($flows, time());
        }

        if ($productFlows) {
            foreach ($productFlows as $key => $productFlow) {
                $productFlows[$key] = [
                    "flow_id" => $productFlow['flow_id'],
                    "flow_title" => $productFlow['flow_title'],
                ];
            }
        }


        //Формируем ответ
        $response = [
            'status' => true,
            'product_id' => $product['product_id'],
            'price' => $price["real_price"],
            'old_price' => $price["price"],
            'product_title' => html_entity_decode($product['product_name'], ENT_QUOTES || ENT_SUBSTITUTE || ENT_HTML5),
            'product_flows' => $productFlows,
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
}