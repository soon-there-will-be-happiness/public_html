<?php defined('BILLINGMASTER') or die; 


class attachmentsController extends baseController {

    /**
     * Скачивание продуктов из заказа
     * @param $order_date
     */
    public function actionDownload($order_date)
    {
        if (isset($_GET['key'])) {
            // Получить данные заказа по order_date
            
            $dwl = 0; // Флаг для скачивания
            $dwl_time = $this->settings['dwl_time'] != 0 ?  $this->settings['dwl_time'] * 3600 : 100000000;
            $dwl_count = $this->settings['dwl_count'];
            $now = time(); // Текущее время
            
            $order = Order::getOrderData($order_date, 1); // Получить данные заказа
            if (!$order) {
                require_once ("{$this->template_path}/404.php");
            }

            // Проверка ключа
            if (md5($order['client_email']) != $_GET['key']) {
                exit('Неверные параметры ключа');
            }
            
            // Проверка ограничения по времени
            if ($order['dwl_time'] == null) {
                if (($order['payment_date'] + $dwl_time) > $now ) {
                    $dwl = 1;
                }
                $upd = Order::UpdateOrderDwl($order_date, $now); // Обновляем дату начала скачивания
            } else {
                if (($order['dwl_time'] + $dwl_time) > $now) {
                    $dwl = 1;
                }
            }
            
            if ($dwl == 1) {
               $_SESSION["dwl_order_$order_date"] = 1;
                
               if (isset($_POST['download'])) {
                    if (isset($_SESSION["dwl_order_$order_date"]) && $_SESSION["dwl_order_$order_date"] == 1) {
                        
                        $item = intval($_POST['item']);
                        
                        // Проверка существования данного продукта в заказе
                        $item_data = Order::ExistProductInOrder($order['order_id'], $item);
                        if ($item_data) {
                            $product = Product::getProductDataForSendOrder($item);
                            header("Location: ".$product['link']);
                            
                            Order::UpdateOrderDwlCount( $item_data['order_item_id'], $item_data['dwl_count'] + 1);   
                        } else {
                            exit('Ошибка. Данного продукта нет в заказе');
                        }
                    }
               }
               
               $items = Order::getOrderItems($order['order_id']);

                $this->setViewParams('Скачать', 'order/paid_load.php', false,
                    null, 'cart-page'
                );

                require_once ("{$this->template_path}/main2.php");
            } else {
                exit('Время скачивания истекло');
            }
        }
    }


    /**
     * СТРАНИЦА ЗАГРУЗКИ ИЗОБРАЖЕНИЙ НА СЕРВЕР
     */
    public function actionUploadImage() {
        if (isset($_FILES['upload']) && $_FILES["upload"]["size"] > 0) {
            $tmp_name = $_FILES['upload']['tmp_name'];
            $path_info = pathinfo($_FILES['upload']['name']);
            $img_name = $path_info['filename'];
            $img_ext =$path_info['extension'];
            $unique_name = md5(microtime(true).mt_rand(100,999).$img_name);
            $relative_path = "/load/images/$unique_name.$img_ext";

            if (is_uploaded_file($tmp_name) && move_uploaded_file($tmp_name, ROOT.$relative_path)) {
                
                $response = [
                    'fileName' => $img_name,
                    'uploaded' => true,
                    'error' => '',
                    'url' => "{$this->settings['script_url']}/$relative_path",
                ];
            } else {
                $response = [
                    'error' => [
                        'message' => 'Не удалось загрузить изображение'
                    ]
                ];
            }

            echo json_encode($response);
        }
    }

    /**
     * ЗАГРУЗКА АВТАРКИ ПОЛЬЗОВАТЕЛЯ
     */
    public function actionUploadAvatar() {

        /// TODO Avatars здесь нужно добавить проверку на токен и юзер ИД тут как-то получить!!!
        $user_id = intval(User::checkLogged());

        if (isset($_POST['image']) && $user_id) {

            
            $folderPath = ROOT."/load/avatars/";
            $user = User::getUserNameByID($user_id);
            $old_file = !empty($user['photo_url']) ? basename($user['photo_url']) : false;
            if ($old_file) {
                if (file_exists($folderPath.$old_file)) {
                    unlink($folderPath.$old_file);
                }
            }

            $image_parts = explode(";base64,", $_POST['image']);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $filename = uniqid() . '.png';
            $file = $folderPath . $filename;
            $tmpfile = "tmp/".$filename;
            $pathtodb = $this->settings['script_url'].'/load/avatars/'.$filename;

            file_put_contents($tmpfile, $image_base64);
            $img = @imagecreatefrompng($tmpfile);
            imagepng($img,$file,9);

            $db = Db::getConnection();
            $sql = 'UPDATE '.PREFICS.'users SET photo_url = :photo_url WHERE user_id = :id';
            $result = $db->prepare($sql);
            $result->bindParam(':photo_url', $pathtodb, PDO::PARAM_STR);
            $result->bindParam(':id', $user_id, PDO::PARAM_STR);
            $result->execute();

            echo json_encode($pathtodb);

        }
    }
}