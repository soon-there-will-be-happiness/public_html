<?php defined('BILLINGMASTER') or die;

class adminProductController extends AdminBase {
    
    
    public function actionIndex()
    {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        $start_time = strtotime(date('Y-m-d 00:00:00'));
        $order_list = Order::OrderToday($start_time);
        $title = 'Продукты - главная';
        require_once (ROOT . '/template/admin/index.php');
        return true;
    }

    /**
     * СОЗДАТЬ ПРОДУКТ
     */
    public function actionAddproduct()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        if (isset($_POST['addproduct']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }

            $prod_name = !empty($_POST['name']) ? htmlentities($_POST['name']) : 'Продукт №';
            $service_name = htmlentities($_POST['service_name']);
            $cat_id = intval($_POST['cat_id']);
            $price = intval($_POST['price']);
            
            // свободная цена продукта
            if (isset($_POST['show_custom_price']) && $_POST['show_custom_price'] == 1) {
                $price_minmax = "{$_POST['min_price']}:{$_POST['max_price']}";
            } else {
                $price_minmax = null;
            }

            $red_price = intval($_POST['red_price']);
            $product_type = intval($_POST['product_type']);
            $amt = intval($_POST['amt']);
            $show_amt = isset($_POST['show_amt']) && $_POST['show_amt'] == 1 ? 1 : 0;
            $link = htmlentities($_POST['link']);
            $subscription = isset($_POST['subscription']) ? intval($_POST['subscription']) : null;

            $product_comiss = isset($_POST['product_comiss']) && $_POST['product_comiss'] > 0 ? intval($_POST['product_comiss']) : 0;
            $send_pass = intval($_POST['send_pass']);
            $redirect_after = htmlentities($_POST['redirect_after']);

            $desc = htmlentities($_POST['desc']);
            $in_catalog = intval($_POST['in_catalog']);

            $in_partner = isset($_POST['in_partner']) ? intval($_POST['in_partner']) : 0;
            $status = intval($_POST['status']);
            $alias = !empty($_POST['alias']) ? $_POST['alias'] : System::Translit($_POST['name']);

            $title = !empty($_POST['title']) ? $_POST['title'] : $prod_name;
            $meta_desc = htmlentities($_POST['meta_desc']);
            $meta_keys = htmlentities($_POST['meta_keys']);

            $button_text = htmlentities($_POST['button_text']);
            $show_price_box = isset($_POST['show_price_box']) ? intval($_POST['show_price_box']) : 0;
            $code_price_box = $_POST['code_price_box'];
            $show_reviews = intval($_POST['show_reviews']);
            $sell_once = intval($_POST['sell_once']);
            $hidden_price = isset($_POST['hidden_price']) ? intval($_POST['hidden_price']) : 0;

            $delivery = isset($_POST['delivery']) ? serialize($_POST['delivery']) : null;
            $delivery_unsub = isset($_POST['delivery_unsub']) ? serialize($_POST['delivery_unsub']) : null;

            $add_group = isset($_POST['add_group']) ? implode(",", $_POST['add_group']) : 0;
            $del_group = isset($_POST['del_group']) ? implode(",", $_POST['del_group']) : 0;

            $letter = $_POST['letter'];
            $subject_letter = $_POST['subject_letter'] ? $_POST['subject_letter'] : 'Ваш заказ.';

            $text1 = isset($_POST['text1']) ? $_POST['text1'] : null;
            $text2 = isset($_POST['text2']) ? $_POST['text2'] : null;

            $text1_tmpl = intval($_POST['text1_tmpl']);
            $text2_tmpl = isset($_POST['text2_tmpl']) ? intval($_POST['text2_tmpl']) : 0;
            $text1_head = $_POST['text1_head'];
            $text2_head = isset($_POST['text2_head']) ? $_POST['text2_head'] : null;

            $text1_heading = intval($_POST['text1_heading']);
            $text2_heading = isset($_POST['text2_heading']) ? intval($_POST['text2_heading']) : null;

            $text1_bottom = $_POST['text1_bottom'];
            $text2_bottom = isset($_POST['text2_bottom']) ? $_POST['text2_bottom'] : null;

            $notif_url = htmlentities($_POST['notif_url']);

            $author1 = isset($_POST['author1']) && $_POST['author1'] != 0 ? "{$_POST['author1']};{$_POST['comiss1']};{$_POST['val1']}" : null;
            $author2 = isset($_POST['author2']) && $_POST['author2'] != 0 ? "{$_POST['author2']};{$_POST['comiss2']};{$_POST['val2']}" : null;
            $author3 = isset($_POST['author3']) && $_POST['author3'] != 0 ? "{$_POST['author3']};{$_POST['comiss3']};{$_POST['val3']}" : null;

            $pincodes = $_POST['pincodes'];

            $upsell_1 = $_POST['upsell_1'];
            $upsell_2 = $_POST['upsell_2'];
            $upsell_3 = $_POST['upsell_3'];

            $upsell_1_price = !empty($_POST['upsell_1_price']) ? intval($_POST['upsell_1_price']) : null;
            $upsell_2_price = !empty($_POST['upsell_2_price']) ? intval($_POST['upsell_2_price']) : null;
            $upsell_3_price = !empty($_POST['upsell_3_price']) ? intval($_POST['upsell_3_price']) : null;


            $upsell_1_desc = $_POST['upsell_1_desc'];
            $upsell_2_desc = $_POST['upsell_2_desc'];
            $upsell_3_desc = $_POST['upsell_3_desc'];

            $upsell_1_text = $_POST['upsell_1_text'];
            $upsell_2_text = $_POST['upsell_2_text'];
            $upsell_3_text = $_POST['upsell_3_text'];

            $external_landing = intval($_POST['external_landing']);
            $external_url = htmlentities($_POST['external_url']);

            $base_id = intval($_POST['base_id']);
            $complect_params = base64_encode(serialize("{$_POST['complect_name']}|{$_POST['complect_list']}|{$_POST['complect_highlight']}"));
            $price_layout = intval($_POST['price_layout']);
            $complect_sort = intval($_POST['complect_sort']);
            $run_aff = isset($_POST['run_aff']) ? intval($_POST['run_aff']) : 0;
            $to_resale = isset($_POST['to_resale']) ? $_POST['to_resale'] : 0;

            $manager_letter = array();
            $manager_letter['subj_manager'] = !empty($_POST['subj_manager']) ? htmlentities($_POST['subj_manager']) : null;
            $manager_letter['email_manager'] = !empty($_POST['email_manager']) ? htmlentities($_POST['email_manager']) : null;
            $manager_letter['letter_manager'] = !empty($_POST['letter_manager']) ? $_POST['letter_manager'] : null;

            $select_payments = isset($_POST['select_payments_on']) && $_POST['select_payments_on'] == 1 ? serialize($_POST['select_payments']) : null;

            if ($manager_letter['subj_manager'] != null || $manager_letter['email_manager'] != null || $manager_letter['letter_manager'] != null) {
                $manager_letter = base64_encode(serialize($manager_letter));
            } else {
                $manager_letter = null;
            }

            $img_alt = htmlentities($_POST['img_alt']);

            $product_access = intval($_POST['product_access']);

            $access_planes = $_POST['access']['planes'] ?? [];
            $access_groups = $_POST['access']['groups'] ?? [];



            if (isset($_FILES['product_cover'])) {
                $tmp_name = $_FILES["product_cover"]["tmp_name"]; // Временное имя картинки на сервере
                $img = $_FILES["product_cover"]["name"]; // Имя картинки при загрузке

                $folder = ROOT . '/images/product/'; // папка для сохранения
                $path = $folder . $img; // Полный путь с именем файла

                if (is_uploaded_file($tmp_name)) {
                    if (file_exists($path)) {
                        $pathinfoimage = pathinfo($path);
                        $newname = $pathinfoimage['filename'].'-copy.'.$pathinfoimage['extension'];
                        $img = $newname;
                        $path = $folder . $newname;
                    }
                    move_uploaded_file($tmp_name, $path);
                }
            }

            $add = Product::AddProduct($prod_name, $service_name, $title, $cat_id, $price, $red_price, $product_type, $amt, $show_amt, $link,
                $subscription, $desc, $in_catalog, $in_partner, $status, $alias, $meta_desc, $meta_keys, $delivery, $delivery_unsub, $add_group,
                $del_group, $letter, $subject_letter, $text1, $text2, $notif_url, $author1, $author2, $author3, $base_id, $img_alt, $img,
                $text1_tmpl, $text2_tmpl,  $pincodes, $upsell_1, $upsell_2, $upsell_3, $upsell_1_desc, $upsell_2_desc, $upsell_3_desc,
                $upsell_1_text, $upsell_2_text, $upsell_3_text, $upsell_1_price, $upsell_2_price, $upsell_3_price, $text1_head, $text2_head,
                $text1_bottom, $text2_bottom, $text1_heading, $text2_heading, $button_text, $show_price_box, $code_price_box, $complect_params,
                $price_layout, $complect_sort, $show_reviews, $sell_once, $external_landing, $external_url, $run_aff, $product_comiss, $send_pass,
                $redirect_after, $manager_letter, $hidden_price, $price_minmax, $to_resale, $select_payments, $product_access
            );

            if ($add) {
                $log = ActionLog::writeLog('products', 'add', 'product', 0, time(), $_SESSION['admin_user'], json_encode($_POST));
                $id = intval($add);

                Product::createProductAccessData($id, $product_access, $access_groups, $access_planes);

                System::setNotif(true);
                System::redirectUrl("/admin/products");
            }
        }

        $partnership = System::CheckExtensension('partnership', 1);
        $title = 'Продукты -добавить продукт';
        require_once (ROOT . '/template/admin/views/products/add.php');
        return true;
    }


    /**
     * РЕДАКТИРОВАТЬ ПРОДУКТ
     * @param $id
     */
    public function actionEditproduct($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        $promo_gen = Product::getAutoPromoByID($id);

        // УДАЛЕНИЕ СОПУТ ПРОДУКТА
        if (isset($_POST['del_related']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }

            $related_id = intval($_POST['related_id']);
            $del = Product::deleteRelatedProduct($related_id);
            if ($del) {
                System::setNotif(true);
                System::redirectUrl("/admin/products/edit/$id");
            }
        }


        // БЫСТРОЕ СОХРАНЕНИЕ СОПУТСТВУЮЩЕГО
        if (isset($_POST['save_related']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }

            $price = intval($_POST['price']);
            $sort = intval($_POST['sort']);
            $show_complects = intval($_POST['show_complects']);
            $status = intval($_POST['status']);
            $related_id = intval($_POST['related_id']);

            $quick_save = Product::saveRelatedProduct($related_id, $price, $sort, $show_complects, $status);
            if ($quick_save) {
                System::setNotif(true);
                System::redirectUrl("/admin/products/edit/$id");
            }
        }

        // Добавление сопутствующего в корзину
        if (isset($_POST['add_related']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {

            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }

            $product_related = intval($_POST['product_related']);
            $price = intval($_POST['price']);
            $show_complects = intval($_POST['show_complects']);
            $status = intval($_POST['status']);
            $related_desc = $_POST['related_desc'];
            $sort = intval($_POST['sort']);

            $add = Product::addRelatedProduct($id, $product_related, $price, $show_complects, $status, $related_desc, $sort);
            if ($add) {
                System::setNotif(true);
                System::redirectUrl("/admin/products/edit/$id");
            }

        }

        $access_data = Product::getProductAccessData($id);

        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }

            $prod_name = !empty($_POST['name']) ? htmlentities($_POST['name']) : 'Продукт №';
            $service_name = htmlentities($_POST['service_name']);
            $cat_id = intval($_POST['cat_id']);
            $price = intval($_POST['price']);
            // свободная цена продукта
            if (isset($_POST['show_custom_price']) && $_POST['show_custom_price'] == 1) {
                $price_minmax = "{$_POST['min_price']}:{$_POST['max_price']}";
            } else {
                $price_minmax = null;
            }

            $red_price = intval($_POST['red_price']);
            $product_type = intval($_POST['product_type']);
            $amt = intval($_POST['amt']);
            $show_amt = isset($_POST['show_amt']) && $_POST['show_amt'] == 1 ? 1 : 0;
            $link = htmlentities($_POST['link']);
            $subscription = isset($_POST['subscription']) ? intval($_POST['subscription']) : null;

            $product_comiss = isset($_POST['product_comiss']) ? intval($_POST['product_comiss']) : 0;

            $desc = htmlentities($_POST['desc']);
            $in_catalog = intval($_POST['in_catalog']);

            $in_partner = isset($_POST['in_partner']) && $_POST['in_partner'] == 1 ? 1 : 0;
            $status = intval($_POST['status']);
            $alias = !empty($_POST['alias']) ? $_POST['alias'] : System::Translit($_POST['name']);

            $title = !empty($_POST['title']) ? $_POST['title'] : $prod_name;
            $meta_desc = htmlentities($_POST['meta_desc']);
            $meta_keys = htmlentities($_POST['meta_keys']);
            $button_text = htmlentities($_POST['button_text']);
            $custom_code = $_POST['custom_code'];

            $manager_letter = array();
            $manager_letter['subj_manager'] = !empty($_POST['subj_manager']) ? htmlentities($_POST['subj_manager']) : null;
            $manager_letter['email_manager'] = !empty($_POST['email_manager']) ? htmlentities($_POST['email_manager']) : null;
            $manager_letter['letter_manager'] = !empty($_POST['letter_manager']) ? $_POST['letter_manager'] : null;

            if ($manager_letter['subj_manager'] != null || $manager_letter['email_manager'] != null || $manager_letter['letter_manager'] != null) {
                $manager_letter = base64_encode(serialize($manager_letter));
            } else {
                $manager_letter = null;
            }

            $installment = intval($_POST['installment']);
            $installments = isset($_POST['installments']) ? $_POST['installments'] : [];
            Installment::saveInstallments2Product($id, $installments);
            if (isset($_POST['installment_action']) && !empty($_POST['installment_action'])) {
                $installment_action = base64_encode(serialize($_POST['installment_action']));
            } else {
                $installment_action = null;
            }
            $installment_addgroups = isset($_POST['installment_addgroups']) ? intval($_POST['installment_addgroups']) : 0;

            $delivery = isset($_POST['delivery']) ? serialize($_POST['delivery']) : null;
            $delivery_unsub = isset($_POST['delivery_unsub']) ? serialize($_POST['delivery_unsub']) : null;

            $add_group = isset($_POST['add_group']) ? implode(",", $_POST['add_group']) : 0;
            $del_group = isset($_POST['del_group']) ? implode(",", $_POST['del_group']) : 0;

            $letter = isset($_POST['letter']) ? $_POST['letter'] : null;
            $subject_letter = $_POST['subject_letter'] ? $_POST['subject_letter'] : 'Ваш заказ.';

            $text1 = isset($_POST['text1']) ? $_POST['text1'] : null;
            $text2 = isset($_POST['text2']) ? $_POST['text2'] : null;

            $text1_tmpl = intval($_POST['text1_tmpl']);
            $text2_tmpl = isset($_POST['text2_tmpl']) ? intval($_POST['text2_tmpl']) : 0;
            $text1_head = $_POST['text1_head'];
            $text2_head = isset($_POST['text2_head']) ? $_POST['text2_head'] : null;

            $text1_heading = intval($_POST['text1_heading']);
            $text2_heading = isset($_POST['text2_heading']) ? intval($_POST['text2_heading']) : 0;

            $text1_bottom = $_POST['text1_bottom'];
            $text2_bottom = isset($_POST['text2_bottom']) ? $_POST['text2_bottom'] : null;

            $notif_url = null;//htmlentities($_POST['notif_url']);
            $send_pass = intval($_POST['send_pass']);
            $redirect_after = htmlentities($_POST['redirect_after']);

            $acymailing = !empty($_POST['acymailing']) ? base64_encode(serialize($_POST['acymailing'])) : null;
            $auto_add = !empty($_POST['auto_add']) ? base64_encode(serialize($_POST['auto_add'])) : null;

            $select_payments = isset($_POST['select_payments_on']) && $_POST['select_payments_on'] == 1
                && isset($_POST['select_payments']) ? serialize($_POST['select_payments']) : null;

            if (isset($_POST['author1']) && $_POST['author1'] != 0) {
                $author1 = intval($_POST['author1']);
                $type_comiss1 = $_POST['comiss1'];
                $comiss1 = intval($_POST['val1']);
            } else {
                $type_comiss1 = null;
                $comiss1 = null;
                $author1 = null;
            }

            if (isset($_POST['author2']) && $_POST['author2'] != 0) {
                $author2 = intval($_POST['author2']);
                $type_comiss2 = $_POST['comiss2'];
                $comiss2 = intval($_POST['val2']);
            } else {
                $author2 = null;
                $type_comiss2 = null;
                $comiss2 = null;
            }

            if (isset($_POST['author3']) && $_POST['author3'] != 0) {
                $author3 = intval($_POST['author3']);
                $type_comiss3 = $_POST['comiss3'];
                $comiss3 = intval($_POST['val3']);
            } else {
                $author3 = null;
                $type_comiss3 = null;
                $comiss3 = null;
            }

            $pincodes = $_POST['pincodes'];

            $upsell_1 = $_POST['upsell_1'];
            $upsell_2 = $_POST['upsell_2'];
            $upsell_3 = $_POST['upsell_3'];

            $upsell_1_desc = $_POST['upsell_1_desc'];
            $upsell_2_desc = $_POST['upsell_2_desc'];
            $upsell_3_desc = $_POST['upsell_3_desc'];

            $upsell_1_text = $_POST['upsell_1_text'];
            $upsell_2_text = $_POST['upsell_2_text'];
            $upsell_3_text = $_POST['upsell_3_text'];

            $upsell_1_price = !empty($_POST['upsell_1_price']) ? intval($_POST['upsell_1_price']) : null;
            $upsell_2_price = !empty($_POST['upsell_2_price']) ? intval($_POST['upsell_2_price']) : null;
            $upsell_3_price = !empty($_POST['upsell_3_price']) ? intval($_POST['upsell_3_price']) : null;

            $base_id = intval($_POST['base_id']);
            $complect_params = base64_encode(serialize($_POST['complect_name'].'|'.$_POST['complect_list'].'|'.$_POST['complect_highlight']));
            $price_layout = intval($_POST['price_layout']);
            $complect_sort = intval($_POST['complect_sort']);

            $show_price_box = intval($_POST['show_price_box']);
            $code_price_box = $_POST['code_price_box'];
            $show_reviews = intval($_POST['show_reviews']);
            $sell_once = intval($_POST['sell_once']);
            $note = htmlentities($_POST['note']);

            $external_landing = intval($_POST['external_landing']);
            $external_url = htmlentities($_POST['external_url']);
            $run_aff = isset($_POST['run_aff']) ? intval($_POST['run_aff']) : 0;
            $hidden_price = intval($_POST['hidden_price']);
            $to_resale = isset($_POST['to_resale']) ? $_POST['to_resale'] : 0;

            $img_alt = htmlentities($_POST['img_alt']);



            $product_access = intval($_POST['product_access']);

            $access_planes = $_POST['access']['planes'] ?? [];
            $access_groups = $_POST['access']['groups'] ?? [];

            if (!$access_data) {
                Product::createProductAccessData($id, $product_access, $access_groups, $access_planes);
            } else {
                Product::updateProductAccessData($id, $product_access, $access_groups, $access_planes);
            }

            if (isset($_FILES["product_cover"]["tmp_name"]) && $_FILES["product_cover"]["size"] != 0) {
                $tmp_name = $_FILES["product_cover"]["tmp_name"]; // Временное имя картинки на сервере
                $img = $_FILES["product_cover"]["name"]; // Имя картинки при загрузке

                $folder = ROOT . '/images/product/'; // папка для сохранения
                $path = $folder . $img; // Полный путь с именем файла
                if (is_uploaded_file($tmp_name)) {
                    if (file_exists($path)) {
                        $pathinfoimage = pathinfo($path);
                        $newname = $pathinfoimage['filename'].'-copy.'.$pathinfoimage['extension'];
                        $img = $newname;
                        $path = $folder . $newname;
                    }
                    move_uploaded_file($tmp_name, $path);
                }
            } else {
                $img = $_POST['current_img'];
            }

            if (isset($_FILES["ads"]["tmp_name"]) && $_FILES["ads"]["size"] != 0) {
                $tmp_name = $_FILES["ads"]["tmp_name"];
                $zip = $_FILES["ads"]["name"];
                $folder = ROOT . '/load/ads/'; // папка для сохранения
                $path = $folder . $zip; // Полный путь с именем файла

                if (is_uploaded_file($tmp_name)) {
                    if (file_exists($path)) {
                        $pathinfoimage = pathinfo($path);
                        $newname = $pathinfoimage['filename'].'-copy.'.$pathinfoimage['extension'];
                        $zip = $newname;
                        $path = $folder . $newname;
                    }
                    move_uploaded_file($tmp_name, $path);
                }
            } elseif (isset($_POST['current_ads'])) {
                $zip = $_POST['current_ads'];
            } else {
                $zip = null;
            }

            // Действия при рассрочках
            if (isset($_POST['group_after_install']) && !empty($_POST['group_after_install'])) {
                $group_after_install = base64_encode(serialize($_POST['group_after_install']));
                $add_action = Product::writeGroupAfterInstallPay($id, $group_after_install);
            }

            // ГЕНЕРАЦИЯ ПРОМО КОДА
            if ($_POST['promo_gen'] == 1 || $_POST['promo_enable'] == 1) {
                $promo_enable = $_POST['promo_enable'];
                $duration = intval($_POST['duration']);
                $promo_word = htmlentities($_POST['promo_word']);
                $type_discount = htmlentities($_POST['type_discount']);
                $discount = intval($_POST['discount']);
                $promo_products = isset($_POST['promo_products']) ? serialize($_POST['promo_products']) : null;
                $promo_desc = htmlentities($_POST['promo_desc']);
                $count_uses = $_POST['count_uses'] != '' ? intval($_POST['count_uses']) : null;

                $add = Product::addPromoGen($id, $promo_enable, $duration, $promo_word, $type_discount,
                    $discount, $promo_products, $promo_gen, $promo_desc, $count_uses
                );
            }
            $promo_hide = (int)$_POST['promo_hide'];
            $not_request_phone = (int)$_POST['not_request_phone'];
            $request_timer= (int)$_POST['show_timer']; // показ таймера оплаты заказа 0-нет, 1 - да
			if(isset($_POST['organization_id'])){
                $add = Organization::addOrgFromProduct($id, intval($_POST['organization_id']));
            }

            $params = json_encode($_POST['params']);

            $save = Product::EditProduct($id, $prod_name, $service_name, $title, $cat_id, $price, $red_price, $product_type, $amt, $show_amt,
                $link, $subscription, $desc, $in_catalog, $in_partner, $status, $alias, $meta_desc, $meta_keys, $delivery, $delivery_unsub,
                $add_group, $del_group, $letter, $subject_letter, $text1, $text2, $notif_url, $author1, $author2, $author3, $type_comiss1,
                $type_comiss2, $type_comiss3, $comiss1, $comiss2, $comiss3, $base_id, $img_alt, $img, $text1_tmpl, $text2_tmpl, $pincodes,
                $upsell_1, $upsell_2, $upsell_3, $upsell_1_desc, $upsell_2_desc, $upsell_3_desc, $upsell_1_text, $upsell_2_text, $upsell_3_text,
                $upsell_1_price, $upsell_2_price, $upsell_3_price, $zip, $text1_head, $text2_head, $text1_bottom, $text2_bottom, $text1_heading,
                $text2_heading, $button_text, $show_price_box, $code_price_box, $custom_code, $complect_params, $price_layout, $complect_sort,
                $show_reviews, $sell_once, $external_landing, $external_url, $run_aff, $acymailing, $auto_add, $product_comiss, $send_pass,
                $redirect_after, $manager_letter, $note, $installment, $installment_action, $installment_addgroups, $hidden_price, $price_minmax,
                $to_resale, $select_payments, $promo_hide, $not_request_phone, $request_timer, $product_access, $params
            );

            // РАСШИРЕНИЕ ExpertSender
            if (System::CheckExtensension('expertsender', 1)) {
                ExpertSender::saveDataToProduct($id, $_POST);
            }

            if ($save) {
                $log = ActionLog::writeLog('products', 'edit', 'product', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::setNotif(true);
                System::redirectUrl("/admin/products/edit/$id?type=$product_type");

            }else
                System::setNotif(false);

        }

        $product = Product::getProductById($id);
        $slit_test = Order::getSplitTestData($id);
        $partnership = System::CheckExtensension('partnership', 1);
        $related_products = Product::getRelatedProductsByID($id);
        $product['select_payments_on'] = 1;

        $get_group_actions = Product::getGroupAfterInstallPay($id);
        $group_actions = $get_group_actions ? unserialize(base64_decode($get_group_actions[0]['actions'])) : null;
        $installment_action = $product['installment_action'] != null ? unserialize(base64_decode($product['installment_action'])) : null;


        // РАСШИРЕНИЕ ExpertSender
        if (System::CheckExtensension('expertsender', 1)) {
            $expsndr = ExpertSender::getDataToProduct($id);
        }
        $title = 'Продукты - редактировать продукт';
        require_once (ROOT . '/template/admin/views/products/edit.php');
        return true;
    }


    /**
     * РАССРОЧКА
     */
    public function actionInstallment()
    {

        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $instalment_list = Product::getInstalments();
        $title = 'Продукты - рассрочка';
        require_once (ROOT . '/template/admin/views/products/installments.php');
        return true;
    }
	
	
	
	// УДАЛИТЬ ДОСРОЧНОЕ ПОГАШЕНИЕ
    public function actionDelahead($map_id, $order_id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Order::deleteOrder($order_id, null);
            
            $del2 = Order::deleteAheadInMap($map_id);
            
            if ($del && $del2){
                System::setNotif(true);
                System::redirectUrl("/admin/installment/map/$map_id");
            } 
        }
        
    }
    
    
    // УДАЛИТЬ ДОСРОЧНЫЙ ПЛАТЁЖ по рассрочке
    public function actionDelnextorder($map_id, $order_date)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $order_data = Order::getOrderData($order_date);
            
            if($order_data) $del = Order::deleteOrder($order_data['order_id'], null);
            $del2 = Order::deleteNextOrderInMap($map_id);
            
            
            if ($del2){
                System::setNotif(true);
                System::redirectUrl("/admin/installment/map/$map_id");
            }
        }
    }
    
    
    // УДАЛИТЬ ДОГОВОР РАССРОЧКИ
    public function actionDelinstallmap($map_id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Order::deleteInstallMap($map_id);
            
            if ($del){
                if (!isset($acl['change_products'])) {
                    System::redirectUrl('/admin/products/');
                    exit();
                }
                $log = ActionLog::writeLog('installments', 'delete', 'map', $map_id, time(), $_SESSION['admin_user'], json_encode($_GET));
                System::setNotif(true);
                System::redirectUrl("/admin/installment/map");   
            }
        }
    }


    /**
     * ДОБАВИТЬ РАССРОЧКУ
     */
    public function actionAddinstallment()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $name = trim($_POST['name']);
            $status = intval($_POST['status']);
            $replace = array(',' => '.');
            $first_pay = floatval(strtr($_POST['first_pay'], $replace));
            $max_periods = intval($_POST['max_periods']);
			$period_freq = intval($_POST['period_freq']);
            $sort = intval($_POST['sort']);
            $installment_rules = $_POST['installment_rules'];
            $installment_desc = $_POST['installment_desc'];
            $increase = intval($_POST['increase']);
            
            $approve = intval($_POST['approve']);
            $letters = base64_encode(serialize($_POST['letters']));
            $sms = base64_encode(serialize($_POST['sms']));
            
            $notif = base64_encode(serialize($_POST['notif']));
            $expired = intval($_POST['expired']);
            
            $sanctions = intval($_POST['sanctions']);
            $minimal = intval($_POST['minimal']);
            
            $other_pay = (100 - $first_pay)/ ($max_periods - 1);
            $fields = base64_encode(serialize($_POST['fields']));
            $prepayment = isset($_POST['prepayment']) ? intval($_POST['prepayment']) : 0;
            $date_second_payment = $_POST['date_second_payment'] ? strtotime($_POST['date_second_payment']) : null;

            $add = Installment::addInstalment($name, $status, $first_pay, $other_pay, $max_periods, $period_freq,
                $installment_rules, $sort, $approve, $letters, $sms, $notif, $expired, $installment_desc, $sanctions,
                $minimal, $increase, $fields, $prepayment, $date_second_payment
            );

            if ($add) {
                $log = ActionLog::writeLog('installments', 'add', 'installment', 0, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::redirectUrl("/admin/installment/edit/$add", $add);
            }
        }
        $title = 'Продукты - добавить рассрочку';
        require_once (ROOT . '/template/admin/views/products/installment_add.php');
        return true;
    }


    /**
     * ИЗМЕНИТЬ РАССРОЧКУ
     * @param $id
     */
    public function actionEditinstallment($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }
            
            $name = trim($_POST['name']);
            $status = intval($_POST['status']);
            $replace = array(',' => '.');
            $first_pay = floatval(strtr($_POST['first_pay'], $replace));
            $max_periods = intval($_POST['max_periods']);
			$period_freq = intval($_POST['period_freq']);
            $sort = intval($_POST['sort']);
            $installment_rules = $_POST['installment_rules'];
            $installment_desc = $_POST['installment_desc'];
            
            $approve = intval($_POST['approve']);
            $letters = base64_encode(serialize($_POST['letters']));
            $sms = base64_encode(serialize($_POST['sms']));
            
            $notif = base64_encode(serialize($_POST['notif']));
            $expired = intval($_POST['expired']);
            
            $sanctions = intval($_POST['sanctions']);
            $minimal = intval($_POST['minimal']);
            $other_pay = (100 - $first_pay)/ ($max_periods - 1);
            $increase = intval($_POST['increase']);
            $fields = base64_encode(serialize($_POST['fields']));
            $prepayment = isset($_POST['prepayment']) ? intval($_POST['prepayment']) : 0;
            $date_second_payment = $_POST['date_second_payment'] ? strtotime($_POST['date_second_payment']) : null;

            $edit = Installment::editInstalment($id, $name, $status, $first_pay, $other_pay, $installment_rules,
                $sort, $approve, $letters, $sms, $notif, $expired, $installment_desc, $sanctions, $minimal,
                $increase, $fields, $period_freq, $prepayment, $date_second_payment
            );

			if ($edit) {
                $log = ActionLog::writeLog('installments', 'edit', 'installment', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::redirectUrl("/admin/installment/edit/$id", $edit);
            }
        }
        
        $installment = Product::getInstallmentData($id);
        $letters = unserialize(base64_decode($installment['letters']));
        $sms = unserialize(base64_decode($installment['sms']));
        $notif = unserialize(base64_decode($installment['notif']));
		$fields = unserialize(base64_decode($installment['fields']));
        $title = 'Продукты - изменить рассрочку';
        require_once (ROOT . '/template/admin/views/products/installment_edit.php');
        return true;
    }


    /**
     * УДАЛИТЬ РАССРОЧКУ
     * @param $id
     */
    public static function actionDelinstallment($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        if (!isset($acl['del_products'])) {
            System::redirectUrl("/admin/products", false);
        }

        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Product::delInstallment($id);
            if ($del) {
                $log = ActionLog::writeLog('installments', 'delete', 'installment', $id, time(), $_SESSION['admin_user'], json_encode($_GET));
                System::redirectUrl("/admin/installment", true);
            } else System::redirectUrl("/admin/installment", false);
        }
    }
    
    
    // КАРТА РАССРОЧЕК
    public function actionInstallmaps()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();

        $filter = [
            'type' => isset($_GET['type']) && $_GET['type'] ? intval($_GET['type']) : null,
            'status' => isset($_GET['status']) && $_GET['status'] != '' ? intval($_GET['status']) : null,
            'email' =>  isset($_GET['email']) && $_GET['email'] ? htmlentities($_GET['email']) : null,
        ];
        $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;

        $total_items = Installment::getCountInstalmentsMap($filter);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $pagination = !isset($_GET['load_csv']) ? new Pagination($total_items, $page, $setting['show_items']) : false;
        $instalment_map = Product::getInstalmentsMap($filter, $pagination, $page, $setting['show_items']);

        if (isset($_GET['load_csv']) && $instalment_map) {
            $time = time();
            $fields = [
                'id', 'order_id', 'installment_id',
                'start_summ', 'summ', 'status', 'max_periods',
                'email', 'next_pay', 'create_date', 'second_pay'
            ];
            $count_fields = count($fields);
            $csv = implode(';', $fields) . PHP_EOL;

            foreach ($instalment_map as $key => $instalment_map_item) {
                foreach ($fields as $_key => $field) {
                    $value = $instalment_map_item[$field] && in_array($field, ['next_pay', 'create_date', 'second_pay']) ? date("d.m.Y H:i:s", $instalment_map_item[$field]) : $instalment_map_item[$field];
                    $csv .= $value . ($_key < $count_fields - 1 ? ';' : '');
                }
                $csv .= PHP_EOL;
            }

            $write = file_put_contents(ROOT . "/tmp/instalment_map_$time.csv", $csv);
            if ($write) {
                System::redirectUrl("/tmp/instalment_map_$time.csv");
            }
        }
        $title = 'Продукты - карта рассрочек';
        require_once (ROOT . '/template/admin/views/products/installment_map.php');
        return true;
    }
    
    
    // УДАЛИТЬ ПЛАТЁЖ ИЗ РАССРОЧКИ
    public function actionDelpayinstall($map_id, $num)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        if (!isset($acl['del_products'])) {
            System::setNotif(false);
            System::redirectUrl("/admin/products");
        }
        $name = $_SESSION['admin_name'];
        $map_id = intval($map_id);
        $num = intval($num);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            
            $map_item = Order::getInstallmentMapData($map_id);
            $pay_actions = unserialize(base64_decode($map_item['pay_actions']));
            
            unset($pay_actions["$num"]);
            
            $pay_str = base64_encode(serialize($pay_actions));
            $del = Installment::updateIntallmentMapPayActions($map_id, $pay_str);
            if ($del) {
                $log = ActionLog::writeLog('installments', 'delete', 'pay', $map_id, time(), $_SESSION['admin_user'], $num);
                System::setNotif(true);
                System::redirectUrl("/admin/installment/map/$map_id");   
            }
        }
    }
    
    
    
    // ПОСМОТРЕТЬ ДОГОВОР РАССРОЧКИ
    public function actionViewinstall($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        // ДОБАВЛЕНИЕ ПЛАТЕЖА В РАССРОЧКУ
        if(isset($_POST['new_pay_add']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']){
            
            $sum = intval($_POST['sum']);
            $type = intval($_POST['new_pay_type']);
            $pay_action = array();
            if($_POST['pay_action'] != null){
            
                $pay_action = unserialize(base64_decode($_POST['pay_action']));
                $count = count($pay_action) + 1;   
                
            } else $count = 1;
            
            if($type == 1){
                // новый платёж
                $new_pay = array();
                $new_pay["$count"]['summ'] = $sum;
                $new_pay["$count"]['date'] = time();
                
                $pay_action = $pay_action + $new_pay;
                
            }  else {
                // дополнить один из платежей
                $pay_action[1]['summ'] = $pay_action[1]['summ'] + $sum;
            }
            
            $pay_str = base64_encode(serialize($pay_action));
            
            $upd = Installment::updateIntallmentMapPayActions($id, $pay_str);
            if($upd){
                $log = ActionLog::writeLog('installments', 'add', 'pay', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::setNotif(true);
                System::redirectUrl("/admin/installment/map/$id");   
            }
        }
        
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            $comment = htmlentities($_POST['comment']);
            $next_pay = intval($_POST['next_pay']);
            $freeze = intval($_POST['freeze']);
			$summ = intval($_POST['summ']);
			$change_email = htmlentities($_POST['change_email']);
            
            $next_pay = $next_pay + $freeze * 86400;
			
			$status = intval($_POST['status']);
            
            $upd = Order::updateInstalMapItem($id, $comment, $next_pay, $status, $summ, $change_email);
            if ($upd) {
                $log = ActionLog::writeLog('installments', 'edit', 'map', $id, time(), $_SESSION['admin_user'], json_encode($_POST));
                System::setNotif(true);
                System::redirectUrl("/admin/installment/map/$id");   
            }
            
        }
        
        $install_map_item = Order::getInstallmentMapData($id);
        $title = 'Продукты - договор рассрочки';
        if ($install_map_item){
            require_once (ROOT . '/template/admin/views/products/installment_map_view.php');
        } else {
            exit('Такой страницы не существует');
        }
        return true;
    }

    
    /**
     * ОТЗЫВЫ 
     */
    
    // СПИСОК ОТЗЫВОВ
    public function actionReviews()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total = Product::countReviews();
        
        
        if (isset($_POST['filter'])) {
            $cat = intval($_POST['cat_id']);
            if (is_numeric($_POST['status'])) $status = intval($_POST['status']);
            else $status = null;
            $list_reviews = Product::getReviews($status, $cat, $tag = null, $setting['show_items'], $page);
            $is_pagination = false;
            
        } else {
            $list_reviews = Product::getReviews($status = null, $cat = null, $tag = null, $setting['show_items'], $page);   
            $is_pagination = true;
        }
        
        $pagination = new Pagination($total, $page, $setting['show_items']);
        $title = 'Продукты - отзывы';
        require_once (ROOT . '/template/admin/views/products/reviews_list.php');
        return true;
    }
    
    
    // РЕДАКТИРВОАТЬ ОТЗЫВ
    public function actionEditreview($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        $now = time();
        
        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }
            $name = htmlentities($_POST['name']);
            $email = htmlentities($_POST['email']);
            $cat_id = intval($_POST['cat_id']);
            $text = $_POST['text'];
            $rate = intval($_POST['rate']);
            $status = intval($_POST['status']);
            if (!empty($_POST['product_id'])) $product_id = intval($_POST['product_id']);
            else $product_id = null;
            if (isset($_POST['labels'])) {
                $label_map = Product::labelWriteMap($id, $_POST['labels']);
            }
            
            $site_url = htmlentities($_POST['site_url']);
            $vk_url = htmlentities($_POST['vk_url']);
            $fb_url = htmlentities($_POST['fb_url']);
            
            if (isset($_FILES["photo"]["tmp_name"]) && $_FILES["photo"]["size"] != 0) {
                
                $tmp_name = $_FILES["photo"]["tmp_name"]; // Временное имя картинки на сервере
                $img = $_FILES["photo"]["name"]; // Имя картинки при загрузке 
                $img = strtolower($now.'-'.$img);
                
                $folder = ROOT . '/images/reviews/'; // папка для сохранения
                $path = $folder . $img; // Полный путь с именем файла
                if (is_uploaded_file($tmp_name)) {      
                    move_uploaded_file($tmp_name, $path);
                }
            } elseif (isset($_POST['current_img'])) $img = $_POST['current_img'];
            else $img = null;
            
            $edit = Product::editReview($id, $name, $email, $cat_id, $text, $site_url, $vk_url, $fb_url, $rate, $status, $img, $product_id);
        }
        
        $review = Product::getReviewByID($id);
        $label_map = Product::getLabelMap($id);
        //print_r($label_map);
        //exit;
        $title = 'Продукты - редактировать отзыв';
        require_once (ROOT . '/template/admin/views/products/review.php');
        return true;
    }
    
    
    // СПИСОК КАТЕГОРИЙ
    public function actionReviewscats()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $list_cats = Product::getReviewsCats();
        $title = 'Продукты - список категорий';
        require_once (ROOT . '/template/admin/views/products/reviews_cats.php');
        return true;
    }
    
    
    
    // СПИСОК МЕТОК
    public function actionLabels()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $list_labels = Product::getReviewsLabels();
        $title = 'Продукты - список меток';
        require_once (ROOT . '/template/admin/views/products/reviews_labels.php');
        return true;
    }
    
    
    // СОЗДАТЬ МЕТКУ
    public function actionAddlabel()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['addlabel']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin');
            }
            $name = htmlentities($_POST['name']);
            if (!empty($_POST['alias'])) $alias = htmlentities($_POST['alias']);
            else $alias = System::Translit($name);
            $status = intval($_POST['status']);
            
            $add = Product::addLabelReview($name, $alias, $status);
            if ($add) {
                System::setNotif(true);
                System::redirectUrl("/admin/reviews/labels");
            }
        }
        $title = 'Продукты - создать метку';
        require_once (ROOT . '/template/admin/views/products/reviews_addlabel.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ МЕТКУ
    public function actionEditlabel($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        
        if (isset($_POST['editlabel']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) 
                System::redirectUrl('/admin');

            $name = htmlentities($_POST['name']);
            if (!empty($_POST['alias'])) $alias = htmlentities($_POST['alias']);
            else $alias = System::Translit($name);
            $status = intval($_POST['status']);
            
            $edit = Product::editLabelReview($id, $name, $alias, $status);
            if ($edit){
                System::setNotif(true);
                System::redirectUrl("/admin/reviews/editlabel/$id");
            
            }else{
                System::setNotif(false);
                System::redirectUrl("/admin/reviews/editlabel/$id");
            }
        }
        
        $label = Product::getReviewsLabelByID($id);
        $title = 'Продукты - изменить метку';
        require_once (ROOT . '/template/admin/views/products/reviews_editlabel.php');
        return true;
    }
    
    
    // УДАЛИТЬ МЕТКУ
    public function actionDellabel($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');

        if (!isset($acl['del_products'])) 
            System::redirectUrl("/admin/products");
        
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Product::deleteLabel($id);
            
            if ($del){
                System::setNotif(true);
                System::redirectUrl("/admin/reviews/labels");
            }
        }
    }
    
    
    // ДОБАВИТЬ КАТЕГОРИЮ ОТЗЫВОВ 
    public function actionAddreviewscat()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['addcat']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin');
            }
            $name = htmlentities($_POST['name']);
            $status = intval($_POST['status']);
            if (!empty($_POST['alias'])) $alias = htmlentities($_POST['alias']);
            else $alias = System::Translit($name);
            
            $add = Product::addReviewCat($name, $status, $alias);
            if ($add){
                System::setNotif(true);
                System::redirectUrl("/admin/reviewscat");
            }
        }
        $title = 'Продукты - добавить категорию отзывов';
        require_once (ROOT . '/template/admin/views/products/reviews_addcat.php');
        return true;
    }
    
    
    // ИЗМЕНИТЬ КАТЕГОРИЮ ОТЗЫВОВ
    public function actionEditreviewscat($id)
    {
        $acl = self::checkAdmin();
        $id = intval($id);
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin');
            }
            $name = htmlentities($_POST['name']);
            $status = intval($_POST['status']);
            if (!empty($_POST['alias'])) $alias = htmlentities($_POST['alias']);
            else $alias = System::Translit($name);
            $edit = Product::editReviewCat($id, $name, $status, $alias);
            if ($edit){
                System::setNotif(true);
                System::redirectUrl("/admin/reviewscat/edit/$id");
            }
        }
        
        $category = Product::getReviewCatByID($id);
        $title = 'Продукты - изменить категорию отзывов';
        require_once (ROOT . '/template/admin/views/products/reviews_editcat.php');
        return true;
    }
    
    
    // УДАЛИТЬ ОТЗЫВ
    public function actionDelreview($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        if (!isset($acl['del_products'])) {
            System::setNotif(false);
            System::redirectUrl("/admin/products");
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Product::deleteReview($id);
            
            if ($del) 
                System::setNotif(true, "Удалено!");
            else 
                System::setNotif(false, "Произошла ошибка!");

            System::redirectUrl("/admin/reviews");
        }
    }
    
    
    // УДАЛИТЬ КАТЕГОРИЮ ОТЗЫВА
    public function actionDelreviewscat($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        if (!isset($acl['del_products'])) {
            System::setNotif(false);
            System::redirectUrl("/admin/products");
            exit();   
        }
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Product::deleteReviewCat($id);
            
            if ($del) 
                System::setNotif(true, "Удалено!");
            else 
                System::setNotif(false, "Удалить невозможно. Категория содержит отзывы!");

            System::redirectUrl("/admin/reviewscat");
        }
    }
    
    /**
     *  ИНФО ПРОДУКТЫ
     */


    /**
     * ПРОДУКТЫ СПИСОК
     */
    public function actionProduct()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $category = $type = $status = null;

        if (isset($_POST['filter'])) {
            $category = $_POST['cat_id'] !== '' ? intval($_POST['cat_id']) : null;
            $type = $_POST['type'] !== '' ? intval($_POST['type']) : null;
            $status = $_POST['status'] !== '' ? intval($_POST['status']) : null;
        }
        $list_products = Product::getAdminProductList($category, $type, $status);
        $title = 'Продукты - список';
        require_once (ROOT . '/template/admin/views/products/index.php');
        return true;
    }
    
    
    // РЕДАКТИРОВАТЬ СВЯЗАННЫЙ ПРОДУКТ ДЛЯ КОРЗИНЫ
    public function actionRelated($base_id, $item_id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $base_id = intval($base_id);
        $item_id = intval($item_id);
        $setting = System::getSetting();
        
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }
            $price = intval($_POST['price']);
            $sort = intval($_POST['sort']);
            $show_complects = intval($_POST['show_complects']);
            $status = intval($_POST['status']);
            $related_desc = $_POST['related_desc'];
            
            $save = Product::saveRelatedProduct($item_id, $price, $sort, $show_complects, $status, $related_desc);
            if ($save){
                System::setNotif(true);
                System::redirectUrl("/admin/related/edit/$base_id/$item_id");
            }
            
        }
        
        $related = Product::getRelatedItemByID($item_id);
        $title = 'Продукты - редактирование связанного продукта';
        require_once (ROOT . '/template/admin/views/products/related_edit.php');
        return true;
        
    }
    
    // КОПРИРОВАТЬ ПРОДУКТ
    public function actionCopyProduct($product_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) 
            System::redirectUrl('/admin');

        if (isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token'] && isset($_POST['copy'])) {
            $copy = Product::copyProduct($product_id);
            if ($copy) {
                System::setNotif(true);
                System::redirectUrl("/admin/products/edit/$copy");
            }
        }
    }
    
    // УДАЛИТЬ ПРОДУКТ
    public function actionDelproduct($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) 
            System::redirectUrl('/admin');

        if (!isset($acl['del_products']))
            System::redirectUrl("/admin/products");

        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Product::deleteProduct($id);
            if ($del) {
                ActionLog::writeLog("products", 'delete', 'product', $id, time(), $_SESSION['admin_user'], ["request" => $_REQUEST]);
                Installment::delInstallments2Product($id);
                System::redirectUrl("/admin/products");
            }
        }
    }
    
    
    
    // ОБНУЛИТЬ СПЛИТ ТЕСТ
    public function actionReset($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        $id = intval($id);
        if (isset($_GET['type'])) $type = intval($_GET['type']);
        else $type = 1;
        
        $reset = Product::resetSplit($id);
        if ($reset) System::redirectUrl("/admin/products/edit/$id?type=$type");
    }
    

    //ОБНОВИТЬ СОРТИРОВКУ ДЛЯ ПРОДУКТОВ
    public function actionUpdSortProducts() {
        if (!empty($_POST['sort'])) {
            $resp = array('status' => true, 'error' => '');
            foreach ($_POST['sort'] as $sort => $prod_id) {
                $result = Product::UpdateSortProduct(intval($prod_id), intval($sort)+1);
                if (!$result) {
                    $resp['status'] = false;
                    $resp['error'] = 'Не удалось сохранить sort для урока с id = ' . $prod_id;
                    break;
                };
            }
            echo json_encode($resp);
        }
    }


    // КАТЕГОРИИ СПИСОК
    public function actionCategory()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        $cat_list = Product::getAllCatList();
        $title = 'Продукты - список категорий';
        require_once (ROOT . '/template/admin/views/products/category.php');
        return true;
    }
    
    
    // СОЗДАТЬ КАТЕГОРИЮ
    public function actionAddcategory()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['addcat']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin');
            }

            $cat_name = htmlentities($_POST['cat_name']);
            $cat_desc = htmlentities($_POST['cat_desc']);
            $cat_keys = htmlentities($_POST['cat_keys']);
            $cat_meta = htmlentities($_POST['cat_meta_desc']);
            if (!empty($_POST['alias']))$alias = htmlentities($_POST['alias']);
            else $alias = System::Translit($_POST['cat_name']);
            if (!empty($_POST['title'])) $title = htmlentities($_POST['title']);
            else $title = $cat_name;
            
            $add = Product::AddCategory($cat_name, $cat_desc, $alias, $title, $cat_keys, $cat_meta);
            if ($add){
                System::setNotif(true);
                System::redirectUrl("/admin/category");
            }
        }
        $title = 'Продукты - создать категорию';
        require_once (ROOT . '/template/admin/views/products/addcat.php');
        return true;
    }
    
    
    // РЕДАКТИРОВАТЬ КАТЕГОРИЮ
    public function actionEditcategory($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin');
            }

            $cat_name = htmlentities($_POST['cat_name']);
            $cat_desc = htmlentities($_POST['cat_desc']);
            $cat_keys = htmlentities($_POST['cat_keys']);
            $cat_meta = htmlentities($_POST['cat_meta_desc']);
            if (!empty($_POST['alias']))$alias = htmlentities($_POST['alias']);
            else $alias = System::Translit($_POST['cat_name']);
            if (!empty($_POST['title'])) $title = htmlentities($_POST['title']);
            else $title = $cat_name;
            
            $edit = Product::EditCategory($id, $cat_name, $cat_desc, $alias, $title, $cat_keys, $cat_meta);
            if ($edit){
                System::setNotif(true, "Категория сохранена.");
                System::redirectUrl("/admin/category");
            }
        }
        
        $cat = Product::getCatData($id);
        $title = 'Продукты - редактирование категории списка';
        require_once (ROOT . '/template/admin/views/products/editcat.php');
        return true;
    }
    
    
    // УДАЛИТЬ КАТЕГОРИЮ
    public static function actionDelcategory($id) {

        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];

        if (!isset($acl['show_products'])) 
            System::redirectUrl('/admin');

        if (!isset($acl['del_products'])) 
            System::redirectUrl("/admin/products");
        
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
        
        // Удаление
        $del = Product::deleteCategory($id);   
        if ($del) 
            System::setNotif(true, "Категория успешно удалена.");

        else
            System::setNotif(true, "В категории есть продукты, удалить её не удалось.");

        System::redirectUrl("/admin/category");

        } else {
            exit('Invalid token');
        }
    }
    
    
    
    // СТРАНИЦА АКЦИЙ НАСТРОЙКА
    public function actionTunepage()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        $name = $_SESSION['admin_name'];
    
        $setting = System::getSetting();
        
        $page = Product::getSalesPage();
        $param = null;
        if (!empty($page['param'])) $param = unserialize(base64_decode($page['param']));
        
        if (isset($_POST['save_page']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin');
            }

            $str = base64_encode(serialize($_POST['sale_page']));
            $content = $_POST['content'];
            $code = $_POST['code'];
            
            $save = Product::saveSalePage($str, $content, $code);
            if ($save) {
                System::setNotif(true);
                System::redirectUrl("/admin/sales/page");

            }else{
                System::setNotif(false);
                System::redirectUrl("/admin/sales/page");
            }
            
        }
        $title = 'Продукты - настройка акций';
        require_once (ROOT . '/template/admin/views/products/sale_page.php');
        return true;
    }


    /**
     * СПИСОК АКЦИЙ
     */
    public function actionSales() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $category = isset($_GET['category']) ? htmlentities($_GET['category']) : (!isset($_GET['reset']) ? 1 : '');
        $filter = [
            'name' => isset($_GET['name']) && $_GET['name'] !== '' ? htmlentities($_GET['name']) : null,
            'type' => isset($_GET['type']) && $_GET['type'] !== '' ? htmlentities($_GET['type']) : null,
            'category' => $category !== '' ? $category : null,
        ];
        $filter['is_filter'] = array_filter($filter, 'strlen') ? true : false;

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $total_items = Product::getCountSales($filter);
        $setting = System::getSetting();
        $pagination = new Pagination($total_items, $page, $setting['show_items']);
        $sales = Product::getSales2Admin($filter, $page, $setting['show_items']);
        $title = 'Продукты - список акций';
        require_once (ROOT . '/template/admin/views/products/sales.php');
        return true;
    }
    
    
    // СОЗДАТЬ АКЦИЮ
    public function actionAddsale()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['addsale']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) 
                System::redirectUrl('/admin/products/');
            
            $name = htmlentities($_POST['name']);
            $type = intval($_POST['type']);
            $desc = htmlentities($_POST['desc']);
            $start = !empty($_POST['start']) ? strtotime($_POST['start']) : time();
            
            $finish = !empty($_POST['finish']) ? strtotime($_POST['finish']) : time() + 84600;
            $status = intval($_POST['status']);

            $product = isset($_POST['product']) ? serialize($_POST['product']) : null;
            $categories = !empty($_POST['categories']) ? serialize($_POST['categories']) : null;
            
            $discount = intval($_POST['discount']);
            $discount_type = isset($_POST['discount_type']) ? htmlentities($_POST['discount_type']) : '';
            $promo = htmlentities(trim($_POST['promo']));
            $promo_calc_discount = intval($_POST['promo_calc_discount']);
            $partner_id = isset($_POST['partner_id']) ? intval($_POST['partner_id']) : null;
            $duration = null;
            $params = isset($_POST['params']) ? json_encode($_POST['params'], true) : null;
            $count_uses = $_POST['count_uses'] !== '' && $_POST['count_uses'] >= 0 ? (int)$_POST['count_uses'] : null;
            $count_uses = $count_uses <= 8388607 ? $count_uses : 8388607;

            $add = Product::addSale($name, $type, $desc, $start, $finish, $status, $discount, $discount_type, $promo,
                $promo_calc_discount, $partner_id, $duration, $product, $params, $categories, null, $count_uses
            );

            if ($add) {
                System::setNotif(true, "Акция ``{$name}`` создана!");
                // exit();
                System::redirectUrl("/admin/sales");
            }
        }
        $title = 'Продукты - создание акции';
        require_once (ROOT . '/template/admin/views/products/add_sale.php');
        return true;
    }
    
    
    // РЕДАКТИРОВАТЬ АКЦИЮ
    public function actionEditsale($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }
        
        $name = $_SESSION['admin_name'];
        $setting = System::getSetting();
        
        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            if (!isset($acl['change_products'])) {
                System::redirectUrl('/admin/products/');
                exit();
            }
            
            $name = htmlentities($_POST['name']);
            $type = intval($_POST['type']);
            $desc = htmlentities($_POST['desc']);
            
            $start = !empty($_POST['start']) ? strtotime($_POST['start']) : time();
            $finish = !empty($_POST['finish']) ? strtotime($_POST['finish']) : time() + 84600;
            $status = intval($_POST['status']);


            $product = !empty($_POST['product']) ? serialize($_POST['product']) : null;
            $categories = !empty($_POST['categories']) ? serialize($_POST['categories']) : null;

            $discount = intval($_POST['discount']);
            $discount_type = htmlentities($_POST['discount_type']);
            $promo = htmlentities(trim($_POST['promo']));
            $promo_calc_discount = intval($_POST['promo_calc_discount']);
            
            $partner_id = isset($_POST['partner_id']) ? intval($_POST['partner_id']) : null;
            $duration = null;

            $params = isset($_POST['params']) ? json_encode($_POST['params'], true) : null;
            $count_uses = $_POST['count_uses'] !== '' && $_POST['count_uses'] >= 0 ? (int)$_POST['count_uses'] : null;
            $count_uses = $count_uses <= 8388607 ? $count_uses : 8388607;

            $edit = Product::editSale($id, $name, $type, $desc, $start, $finish, $status, $discount,
                $discount_type, $promo, $promo_calc_discount, $partner_id, $duration, $product, $params,
                $categories, null, $count_uses
            );
            if ($edit) {
                System::setNotif(true);
                System::redirectUrl("/admin/sale/edit/$id");
            }
        }
        
        $sale = Product::getSaleData($id);
        if (!$sale) {
            require_once (ROOT . '/template/'.$setting['template'].'/404.php');
            exit;
        }
        $params = isset($sale['params']) ? json_decode($sale['params'], true) : null;
        $title = 'Продукты - редактировать акцию';

        require_once (ROOT . '/template/admin/views/products/edit_sale.php');
        return true;
    }
    
    // УДАЛИТЬ АКЦИЮ
    public function actionDelsale($id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) System::redirectUrl('/admin');
        if (!isset($acl['del_products']))
            System::redirectUrl("/admin/products");
       
        $name = $_SESSION['admin_name'];
        $id = intval($id);
        $setting = System::getSetting();
        
        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = Product::deleteSale($id);
            
            if ($del) 
                System::setNotif(true, "Акция удалена!");

            System::redirectUrl("/admin/sales");
        }
    }


    /**
     * ДОБАВИТЬ HTTP УВЕДОМЛЕНИЕ
     */
    public function actionAddHttpNotice() {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        if (isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $name = trim($_POST['name']);
            $url = trim($_POST['url']);
            $send_type = intval($_POST['send_type']);
            $send_time_type = intval($_POST['send_time_type']);
            $product_id = intval($_POST['product_id']);
            $vars = !empty($_POST['vars']) ? json_encode($_POST['vars']) : '';
            $is_send_utm = isset($_POST['is_send_utm']) ? 1 : 0;
            $res = ProductHttpNotice::addNotice($product_id, $name, $url, $send_type, $send_time_type, $vars, $is_send_utm);

            if($res)
                System::setNotif(true);

            System::redirectUrl("/admin/products/edit/$product_id?type={$_POST['product_type']}");
        }
    }


    /**
     * РЕДАКТИРОВАТЬ HTTP УВЕДОМЛЕНИЕ
     * @param $notice_id
     */
    public function actionEditHttpNotice($notice_id) {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }
    
        if (isset($_POST['edit']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $name = trim($_POST['name']);
            $url = trim($_POST['url']);
            $send_type = intval($_POST['send_type']);
            $send_time_type = intval($_POST['send_time_type']);
            $product_id = intval($_POST['product_id']);
            $vars = !empty($_POST['vars']) ? json_encode($_POST['vars']) : '';
            $is_send_utm = isset($_POST['is_send_utm']) ? 1 : 0;

            $res = ProductHttpNotice::editNotice($notice_id, $name, $url, $send_type, $send_time_type, $vars, $is_send_utm);
           
            if($res)
                System::setNotif(true);

            System::redirectUrl("/admin/products/edit/$product_id?type={$_POST['product_type']}");
        }
        
        if (!isset($_GET['prod_id']) || !isset($_GET['prod_type'])) {
            exit;
        }
    
        $notice = ProductHttpNotice::getNotice(intval($notice_id));
        
        if (!$notice) {
            $setting = System::getSetting();
            require_once (ROOT . '/template/'.$setting['template'].'/404.php');
            exit;
        }
        
        $notice_vars =  json_decode($notice['vars'], true);
        $product = [
            'product_id' => $_GET['prod_id'],
            'type_id' => $_GET['prod_type'],
        ];
        $title = 'Продукты - редактирование уведомления';
        require_once (ROOT . '/template/admin/views/products/httpnotice/edit.php');
        return true;
    }


    /**
     * УДАЛИТЬ HTTP УВЕДОМЛЕНИЕ
     * @param $id
     */
    public function actionDelHttpNotice($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products']) || !isset($acl['del_products'])) {
            System::redirectUrl('/admin');
        }

        if (isset($_GET['token']) && $_GET['token'] == $_SESSION['admin_token']) {
            $del = ProductHttpNotice::delNotice(intval($id));
            
            if($res)
                System::setNotif(true);

            System::redirectUrl("/admin/products/edit/{$_GET['prod_id']}?type={$_GET['prod_type']}");
        }
    }


    /**
     * ДОБАВИТЬ ПЕРСОНАЛЬНОЕ УВЕДОМЛЕНИЕ
     */
    public function actionAddReminder()
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        if (isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $product_id = intval($_POST['product_id']);
            $status = (int)$_POST['status'];

            $remind_letter1 = base64_encode(serialize($_POST['remind_letter1']));
            $remind_letter2 = base64_encode(serialize($_POST['remind_letter2']));
            $remind_letter3 = base64_encode(serialize($_POST['remind_letter3']));
            $remind_sms1 = base64_encode(serialize($_POST['remind_sms1']));
            $remind_sms2 = base64_encode(serialize($_POST['remind_sms2']));
            
            $res = ProductReminder::addReminder($product_id, $status, $remind_letter1, $remind_letter2, $remind_letter3, $remind_sms1, $remind_sms2);

            if($res)
                System::setNotif(true);

            System::redirectUrl("/admin/products/edit/$product_id?type={$_POST['product_type']}");
        }
    }


    /**
     * РЕДАКТИРОВАТЬ ПЕРСОНАЛЬНОЕ УВЕДОМЛЕНИЕ
     * @param $id
     */
    public function actionEditReminder($id)
    {
        $acl = self::checkAdmin();
        if (!isset($acl['show_products'])) {
            System::redirectUrl('/admin');
        }

        if (isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {
            $product_id = intval($_POST['product_id']);
            $status = (int)$_POST['status'];

            $remind_letter1 = base64_encode(serialize($_POST['remind_letter1']));
            $remind_letter2 = base64_encode(serialize($_POST['remind_letter2']));
            $remind_letter3 = base64_encode(serialize($_POST['remind_letter3']));
            $remind_sms1 = base64_encode(serialize($_POST['remind_sms1']));
            $remind_sms2 = base64_encode(serialize($_POST['remind_sms2']));
            
            $res = ProductReminder::editReminder(intval($id), $status, $remind_letter1, $remind_letter2, $remind_letter3, $remind_sms1, $remind_sms2);

            if($res)
                System::setNotif(true);

            System::redirectUrl("/admin/products/edit/$product_id?type={$_POST['product_type']}");
        }
    }


    public function actionLogout()
    {
        $acl = self::checkAdmin();
        $setting = System::getSetting();
        unset($_SESSION['admin_user']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_token']);
        session_destroy();
        System::redirectUrl("/admin");
        exit();
    }
    
}