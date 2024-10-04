<?php defined('BILLINGMASTER') or die;

class cartController extends baseController {
    
    // ДОБАВЛЕНИЕ В КОРЗИНУ
    public function actionAdd($id)
    {
        $id = intval($id);
        
        // Проверка наличия продукта по ID 
        if(Product::getMinProductById($id)){
            $data = Cart::AddProduct($id);
            echo json_encode($data);
        }
        return true;
    }
    
    
    
    // УДАЛЕНИЕ ИЗ КОРЗИНЫ
    public static function actionDel($id)
    {
        $id = intval($id);
        $setting = System::getSetting();
        unset($_SESSION['sale_id']);
        unset($_SESSION['cart'][$id]);

        if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            echo json_encode(Cart::countItems());
        } else {
            header("Location: /cart");
        }
        return true;
    }
    
    
    
    
    // ПРОСМОТР КОРЗИНЫ
    public function actionIndex()
    {
        $setting = System::getSetting();
        $cookie = $setting['cookie'];
        if ($setting['use_cart'] != 1) {
            require_once ("{$this->template_path}/404.php");
        }

        // Промо код
        if (isset($_POST['apply_promo']) && isset($_POST['promo']) && !empty($_POST['promo'])) {
            $promo_code = htmlentities(trim($_POST['promo']));

            if (!isset($_SESSION['promo_code']) || $_SESSION['promo_code'] != $promo_code) {
                $sale = Product::getSaleByPromoCode($promo_code);
                if ($sale && ($sale['count_uses'] === null || $sale['count_uses'] > 0)) {
                    if ($sale['count_uses'] > 0) {
                        Product::updSaleCountUses($sale['id'], $sale['count_uses'] - 1);
                    }
                    $_SESSION['promo_code'] = $promo_code;
                }
            }
        }

        $product_in_cart = Cart::getProducts();
        $products_ids = $product_in_cart ? array_keys($product_in_cart) : null;
        $products = $products_ids ? Product::getProductsByIds($products_ids) : null;
        
        if (isset($_POST['buy']) && $product_in_cart == true) {
            $date = time();
            $name = htmlentities($_POST['name']);
            $surname = isset($_POST['surname']) ? htmlentities($_POST['surname']) : false;
            if (strpbrk($name, "'()$%&!")) {
                exit('Do not use special characters!'); 
            }
            
            if (strpbrk($surname, "'()$%&!")) {
                exit('Do not use special characters!'); 
            }

            $email = htmlentities(trim(strtolower($_POST['email'])));
            $email = System::checkemaildomain($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                exit("E-mail адрес '$email' указан неверно.\n");
            }

            $phone = isset($_POST['phone']) ? htmlentities($_POST['phone']) : null;
            $index = isset($_POST['index']) ? htmlspecialchars($_POST['index']) : null;
            $city = isset($_POST['city']) ? htmlentities($_POST['city']) : null;
            $address = isset($_POST['address']) ? htmlentities($_POST['address']) : null;
            $comment = htmlentities($_POST['comment']);
            $type_id = intval($_POST['type_id']);
            $param = isset($_COOKIE["$cookie"]) ? htmlentities($_COOKIE["$cookie"]) : htmlentities($_SESSION["$cookie"]);
            $client  = @$_SERVER['HTTP_CLIENT_IP'];
            $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
            $remote  = @$_SERVER['REMOTE_ADDR'];
             
            if (filter_var($client, FILTER_VALIDATE_IP)) {
                $ip = $client;
            } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
                $ip = $forward;
            } else {
                $ip = htmlentities($remote);
            }
            
			$partner_id = null;
            
            // ПАРТНЁРКА 
            $partnership = System::CheckExtensension('partnership', 1);
            if ($partnership) {
                if (isset($_SESSION['promo_code'])) {
                    $sale = Product::getSaleByPromoCode(htmlentities($_SESSION['promo_code']));
                    if ($sale && $sale['partner_id'] && $verify = Aff::PartnerVerify($sale['partner_id'])) {
                        $partner_id = $verify['email'] != $email ? $sale['partner_id'] : null;
                    }
                } else {
                    if (isset($_SESSION["real_aff_$cookie"])) {
                        $verify = Aff::PartnerVerify(intval($_SESSION["real_aff_$cookie"]));
                        if ($verify && $verify['email'] != $email) { // Проверка на самозаказ
                            $partner_id = intval($_SESSION["real_aff_$cookie"]);
                        }
                    } else {
                        if (isset($_COOKIE["aff_$cookie"])) {
                            $verify = Aff::PartnerVerify(intval($_COOKIE["aff_$cookie"])); // Проверка партнёра на существование и самозаказ
                            if($verify && $verify['email'] != $email) {
                                $partner_id = intval($_COOKIE["aff_$cookie"]);
                            } else {
                                $partner_id = null;
                            }
                        } elseif (isset($_SESSION["aff_$cookie"])) { // Проверка партнёра на существование
                            $verify = Aff::PartnerVerify(intval($_SESSION["aff_$cookie"]));
                            if ($verify && $verify['email'] != $email) {
                                $partner_id = intval($_SESSION["aff_$cookie"]);
                            } else {
                                $partner_id = null;
                            }
                        } else {
                            $partner_id = null;
                        }
                    }
                }
            }

            //Проверка, нуждается ли продукт в доставке
            $need_delivery = false;
            $sum = 0;

            foreach ($products as $product) {
                if ($product['type_id'] == 2) {
                    $need_delivery = true;
                }

                $price = Price::getPriceinCatalog($product['product_id']);
                $sum += $price['real_price'];
            }


            $utm = System::getUtm();
            $sale_id = isset($_SESSION['sale_id']) ? $_SESSION['sale_id'] : null;
            $expire = $this->settings['order_life_time'] * 86400 + $date;

            $add_order = Cart::addCartOrder($name, $surname, $email, $phone, $index, $city, $address, $comment, $param,
                $partner_id, $date, $sale_id, 0, $type_id, $ip, $products, $utm, $expire, $sum
            );
            
            if ($add_order) {
                if (isset($_SESSION['promo_code'])) {
                    unset($_SESSION['promo_code']);
                }

                if (!isset($_COOKIE['emnam'])) {
                    $emnam = $email . '='.$name . '='.$phone;
                    setcookie('emnam', $emnam, time()+3600*24*30*3, '/');
                }

                OrderTask::addTask($add_order['order_id'], OrderTask::STAGE_ACC_STAT); // добавление задач для крона по заказу

                if ($need_delivery) {
                    $_SESSION["delivery_$date"] = 1;
                    System::redirectUrl("/delivery/$date");
                } else {
                    System::redirectUrl("/pay/$date");
                }
            }
        }

        $path = isset($_POST['checkout']) && isset($_SESSION['cart']) ? 'cart/checkout.php' : 'cart/index.php';

        $this->setSEOParams('Корзина');
        $this->setViewParams('cart', $path, false, null, 'cart-page');

        require_once ("{$this->template_path}/main2.php");
        return true;
    }
}