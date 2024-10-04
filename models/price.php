<?php defined('BILLINGMASTER') or die;

class Price {
    
    // РАСЧИТЫВАЕТ СТОИОМОСТЬ ПРОДУКТА с Учётом всех акций
    public static function getPriceinCatalog($id)
    {
        $db = Db::getConnection();
        $data = array();
        
        // Получаем цену продукта
        $result = $db->query("SELECT price, red_price, price_minmax FROM ".PREFICS."products WHERE product_id = $id");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($data)) {
            if ($data['price'] == 0 && !empty($data['price_minmax'])) {
                $price_min = explode(":", $data['price_minmax'])[0];
                $data['real_price'] = $price_min;
            } else {
                $data['real_price'] = $data['price'];
            }        
            
            $accumulative_discount = Product::getSaleList(1, [5]); // Получить список акций с типом Скидка в корзине(акция с 5-ым статусом)
           
            if ($accumulative_discount) { // 
                $params = json_decode($accumulative_discount[0]['params'], true);
                $products = unserialize($accumulative_discount[0]['products']);
                if (isset($_SESSION['cart'])) {
                    if (array_key_exists($id, $_SESSION['cart']) &&  in_array($id, $products)) {
                        foreach ($params['count_or_summ'] as $key => $level){
                            if ($params['level_type'] == 'count_prod'){ // тут расчет скидки от кол-ва продуктов в корзине 
                                if (count($_SESSION['cart'])>=$level) {
                                    $_SESSION['sale_id'] = $accumulative_discount[0]['id'];
                                    $data['real_price'] = $params['discount_type'] == 'summ' ? $data['price'] - $params["size_discount"][$key]
                                    : round($data['price'] - ($data['price'] / 100) * $params["size_discount"][$key]);
                                }
                            } else { // тут от суммы товаров в корзине 
                                $totalcart = Cart::getTotalProductsInCartNotDscount();
                                if ($totalcart>=$level) {
                                    $_SESSION['sale_id'] = $accumulative_discount[0]['id'];
                                    if ($params['discount_type'] == 'summ') {
                                        $data['real_price'] =  $data['price'] > $params["size_discount"][$key] ? $data['price'] - $params["size_discount"][$key] : 0;
                                    } else {
                                        $data['real_price'] = round($data['price'] - ($data['price'] / 100) * $params["size_discount"][$key]);
                                    }
                                }
                            }
                        }
                    }
                }


            } else {

                if (!empty($data['red_price'])) {

                    $product_category = $db->query("SELECT cat_id FROM ".PREFICS."products WHERE product_id = $id");
                    $product_category = $product_category->fetch(PDO::FETCH_ASSOC);
                    $product_category = $product_category['cat_id'];

                    $red_sales = Product::getSaleList(1, [1]); // Получить список акций с типом Красная цена

                    if ($red_sales) {
                        foreach ($red_sales as $sale) {
                            $products = unserialize($sale['products']);
                            $categories = unserialize($sale['categories']);
                            if ((!empty($products) && in_array($id, $products) || (!empty($categories) && in_array($product_category, $categories)))) {
                                $data['real_price'] = $data['red_price'];
                                $data['sale_id'] = $sale['id'];
                                break;
                            }
                        }
                    }
                }

                if (isset($_SESSION['promo_code'])) {
                    $promo_sales = Product::getSaleList(1, [2,9]); // Получить список акций с типом Промо код

                    if ($promo_sales) {
                        foreach($promo_sales as $sale) {
                            $products = unserialize($sale['products']);

                            $categories = unserialize($sale['categories']);
                            $product_category = $db->query("SELECT cat_id FROM ".PREFICS."products WHERE product_id = $id");
                            $product_category = $product_category->fetch(PDO::FETCH_ASSOC);
                            $product_category = $product_category['cat_id'];
                            
                            if (!empty($products) && in_array($id, $products) && $_SESSION['promo_code'] == $sale['promo_code']) {
                                $price_to_calc = $sale['promo_calc_discount'] == 2 && $data['red_price'] ? $data['red_price'] : $data['price'];
                                
                                $data['real_price'] = $sale['discount_type'] == 'summ' ? $price_to_calc - $sale['discount']
                                    : round($price_to_calc - ($price_to_calc / 100) * $sale['discount']);
        
                                return $data;
                            } elseif (!empty($categories) && in_array($product_category, $categories)) {

                                if (strtoupper($_SESSION['promo_code']) == strtoupper($sale['promo_code'])) {
                                    $price_to_calc = $sale['promo_calc_discount'] == 2 && $data['red_price'] ? $data['red_price'] : $data['price'];

                                    $data['real_price'] = $sale['discount_type'] == 'summ' ? $price_to_calc - $sale['discount']
                                        : round($price_to_calc - ($price_to_calc / 100) * $sale['discount']);

                                    return $data;
                                }
                            }

                        }
                    }
                }

            }
            
            return $data;
        }
    
        return false;
    }
    
    
        // ПОЛУЧАЕТ КОНЕЧНУЮ СТОИМОСТЬ ПРОДУКТА
    public static function getFinalPrice($id, $get_partner_id = 1)
    {
        $db = Db::getConnection();
        $data = array();
        
        $setting = System::getSetting();
        $cookie = $setting['cookie'];
        
        // Получаем цену продукта
        $result = $db->query("SELECT price, red_price, price_minmax FROM ".PREFICS."products WHERE product_id = $id");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($data)) {
            if ($data['price'] == 0 && !empty($data['price_minmax'])) {
                $price_min = explode(":", $data['price_minmax'])[0];
                $data['real_price'] = $price_min;
            } else {
                $data['real_price'] = $data['price'];
            }

            $data['sale_id'] = $data['partner_id'] = null;
            $accumulative_discount = Product::getSaleList(1, [5]); // Получить список акций с типом Скидка в корзине(акция с 5-ым статусом)
           
            if ($accumulative_discount) {
                $params = json_decode($accumulative_discount[0]['params'], true);
                $products = unserialize($accumulative_discount[0]['products']);

                if (isset($_SESSION['cart'])) {
                    if (array_key_exists($id, $_SESSION['cart']) &&  in_array($id, $products)) {
                        foreach ($params['count_or_summ'] as $key => $level){
                            if ($params['level_type'] == 'count_prod'){ // тут расчет скидки от кол-ва продуктов в корзине
                                if (count($_SESSION['cart']) >= $level) {
                                    $_SESSION['sale_id'] = $accumulative_discount[0]['id'];
                                    $data['real_price'] = $params['discount_type'] == 'summ' ? $data['price'] - $params["size_discount"][$key]
                                    : round($data['price'] - ($data['price'] / 100) * $params["size_discount"][$key]);
                                }
                            } else { // тут от общей суммы товаров в корзине
                                $totalcart = Cart::getTotalProductsInCartNotDscount();

                                if ($totalcart >= $level) {
                                    $_SESSION['sale_id'] = $accumulative_discount[0]['id'];
                                    if ($params['discount_type'] == 'summ') {
                                        $data['real_price'] =  $data['price'] > $params["size_discount"][$key] ? $data['price'] - $params["size_discount"][$key] : 0;
                                    } else {
                                        $data['real_price'] = round($data['price'] - ($data['price'] / 100) * $params["size_discount"][$key]);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $product_category = $db->query("SELECT cat_id FROM ".PREFICS."products WHERE product_id = $id");
                $product_category = $product_category->fetch(PDO::FETCH_ASSOC);
                $product_category = $product_category['cat_id'];

                if (!empty($data['red_price'])) {
                    $red_sales = Product::getSaleList(1, [1]); // Получить список акций с типом Красная цена

                    if ($red_sales) {
                        foreach ($red_sales as $sale) {
                            $products = unserialize($sale['products']);
                            $categories = unserialize($sale['categories']);
                            if ((!empty($products) && in_array($id, $products) || (!empty($categories) && in_array($product_category, $categories)))) {
                                $data['real_price'] = $data['red_price'];
                                $data['sale_id'] = $sale['id'];
                                break;
                            }
                        }
                    }
                }

                if (isset($_SESSION['promo_code']) && $sale = Product::getSaleByPromoCode($_SESSION['promo_code'])) {
                    $products = $sale['products'] ? unserialize($sale['products']) : [];
                    $categories = $sale['categories'] ? unserialize($sale['categories']) : [];
                    if ($categories) {
                        $cat_products = Product::getProductIdsToCategories(implode(',', $categories));
                        $products = $cat_products ? array_merge($products, $cat_products) : $products;
                    }

                    if (!empty($products) && in_array($id, $products)) {
                        $data['sale_id'] = $sale['id'];
                        if ($sale['partner_id'] != 0) {
                            $saleparams = json_decode($sale['params'], true);

                            if (!isset($saleparams['usepartnersaccrue']) || $saleparams['usepartnersaccrue']) {
                                $_SESSION["real_aff_$cookie"] = $sale['partner_id'];
                                $data['partner_id'] = $sale['partner_id'];
                            } else {
                                $data['usepartner'] = false;
                            }
                        }

                        $price_to_calc = $sale['promo_calc_discount'] == 2 && $data['red_price'] ? $data['red_price'] : $data['price'];
                        $data['real_price'] = $sale['discount_type'] == 'summ' ? $price_to_calc - $sale['discount'] : round($price_to_calc - ($price_to_calc / 100) * $sale['discount']);

                        return $data;
                    }
                }
            }
        
            return $data;
        }
        
        return false;
    }
    
    
    
    // РАСЧЁТ ЦЕНЫ и НДС
    public static function getNDSPrice($price)
    {
        $setting = System::getSetting();
        
        if($setting['nds_enable'] == 1){ // добавить НДС к цене
            $nds = $price / 100 * $setting['nds_value'];
            $price = $price + $nds;
        } elseif ($setting['nds_enable'] == 2) {// вычесть НДС из цены
            
            $mod = 100 + $setting['nds_value'];
            $nds = round(($price * $setting['nds_value'] / $mod), 2);
            
        } else {
            $nds = 0;
        }
        
        $data['price'] = $price;
        $data['nds'] = round($nds, 2);
        
        return $data;
    }
    
    
    
    // ВЫЧЛЕНИТЬ НДС ИЗ СУММЫ с ндс
    public static function isolateNDS($price)
    {
        $setting = System::getSetting();
        
        if($setting['nds_enable'] > 0){
            $mod = 100 + $setting['nds_value'];
            $nds = $price * $setting['nds_value'] / $mod;    
        } else {
            $nds = 0;
        }
        
        
        return round($nds, 2);
    }
    
    
    
    // Расчёт только цены
    public static function getOnlyNDSPrice($price)
    {
        $setting = System::getSetting();
        
        if($setting['nds_enable'] == 1){ // добавить НДС к цене
            $nds = $price / 100 * $setting['nds_value'];
            $price = $price + $nds;
        } elseif ($setting['nds_enable'] == 2) {// вычесть НДС из цены
            $nds = $price / 100 * $setting['nds_value'];
            
        } else {
            $nds = 0;
        }
        
        return $price;
    }

    /**
     * Подсчет цены продуктов заказа с уже внесенной предоплатой
     *
     * @param array $order
     * @param array $order_items
     *
     * @return array
     */
    public static function changeOrderItemsPriceWithDeposits(array $order, array $order_items) {

        if(!isset($order['deposit'])) {
            return $order_items;
        }
        $deposits = json_decode($order['deposit'], true);
        if ($deposits) {

            $depositSum = 0;

            foreach ($deposits as $deposit) {
                $depositSum += $deposit['sum'];
            }

            $items_count = count($order_items);
            $priceMinus = $depositSum / $items_count;

            $order_items_for_payments = [];

            foreach ($order_items as $key => $orderItem) {
                $order_items_for_payments[$key] = $orderItem;
                $order_items_for_payments[$key]['price'] = $orderItem['price'] - $priceMinus;
            }

            return $order_items_for_payments;

        }

        return $order_items;
    }
}