<?php defined('BILLINGMASTER') or die;

class System {

    use ResultMessage;

    static $settings;
    static $main_settings;

    static $acl;

    /**
     * ПОЛУЧИТЬ ПРОМО КОДЫ ПО емейл
     * @param $email
     * @param $time
     * @return bool|mixed
     */
    public static function getPromoByEmail($email, $time)
    {
        $db = Db::getConnection();
        $time = $time + 900; // + 15 мин
        $result = $db->query(" SELECT MAX(id) FROM ".PREFICS."sales WHERE type = 9 AND finish > $time AND client_email = '$email' LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (isset($data) && !empty($data['MAX(id)'])) {
            $id = $data['MAX(id)'];
            $result = $db->query(" SELECT * FROM ".PREFICS."sales WHERE id = $id LIMIT 1");
            $data = $result->fetch(PDO::FETCH_ASSOC);

            return !empty($data) ? $data : false;
        }

        return false;
    }

    /**
     * Создаем рандомную строку из цифр и лат. букв двух регистров
     * 
     * @param  int|integer $length длина (16)
     * @param  string      $prefix префикс (null)
     * @return string
     */
    public static function generateRandomString(int $length = 16, string $prefix = ''): string{
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $random_string = '';

        for($i = 0; $i < $length; $i++) 
            $random_string .=  $chars[mt_rand(0, strlen($chars) - 1)];

        if(!empty($prefix))
            $random_string = $prefix . $random_string;

        return $random_string;  
    }

    /**
     * ПРОВЕРКА ПРАВ АДМИНА С ПЕРЕНАПРАВЛЕНИЕМ
     * 
     * @param string $perm_name
     * @param string ...
     * @return null
     */
    public static function checkPermission(... $perms){
        if(!self::$acl)
            self::$acl = AdminBase::checkAdmin();

        foreach($perms as $perm){
            if(!isset(self::$acl[$perm])){
                if(in_array($perm, ['change_main_tunes']))
                    self::redirectUrl("/admin/settings");

                else
                    self::redirectUrl("/admin");
            }
        }

        return;
    }

    /**
     * ПРОВЕРКА ПРАВ АДМИНА с RETURN
     * 
     * @param string $perm_name
     * @param string ...
     * @return bool
     */
    public static function issetPermission(... $perms){
        if(!self::$acl)
            self::$acl = AdminBase::checkAdmin();

        foreach($perms as $perm){
            if(!isset(self::$acl[$perm])){
                return false;
            }
        }

        return true;
    }

    /**
     * ПРОВЕРКА ТОКЕНА _POST[token] и _SESSION[token]
     * 
     * @param null|string $post_token
     * @param null|string $session_token
     * @return bool
     */
    public static function checkToken($post_token = null, $session_token = null): bool{

        $is_perm = false;

        if(empty($post_token) && isset($_POST['token']))
            $post_token = $_POST['token'];

        if(empty($session_token) && isset($_SESSION['admin_token']))
            $session_token = $_SESSION['admin_token'];

        return !empty($post_token) && !empty($session_token) && $post_token == $session_token;
    }

    /**
     * ПОДКЛЮЧЕНИЕ ФАЙЛОВ
     * 
     * @param string $file
     * @param string ...
     * @return :void
     */ // НЕ ИСПОЛЬЗОВАТЬ! ДАННЫЙ СПОСОБ НЕ ПЕРЕДАЕТ ПЕРЕМЕННЫЕ В ПОДКЛЮЧАЕМЫЙ ФАЙЛ
    public static function require_once(... $files){
        foreach($files as $file){
            if(file_exists($file))
                return require_once $file;

            else
                print("<pre>\nFILE \"{$file}\" NOT FOUND!\n</pre>");
        }
    }

    /**
     * СОХРАНЕНИЕ ОСТАТОЧНЫХ ДАННЫХ
     *
     *  Данны сохраняются в Cookie и выводяться по запросу 
     * или на странице где данные были добавлены сюда. 
     * 
     * @param string $reason причина по которой данные не сохранены
     * @param array $data данные (по умолчанию записываются POST и GET запросы)
     *
     * @return void
     */
    public static function addRemainedData(string $reason, array $data = [], int $time = 1209600){
        $req_url = substr($_SERVER['REQUEST_URI'], -1) == '/'
            ? substr($_SERVER['REQUEST_URI'], 0, -1)
            : $_SERVER['REQUEST_URI'];

        $data['reason'] = $reason;
        $data['time'] = time() + 
            (isset($_SESSION, $_SESSION['difference_time']) 
                ? $_SESSION['difference_time'] * 3600 
                : ( isset($_COOKIE["difference_time"]) && is_numeric($_COOKIE['difference_time']) 
                    ? $_COOKIE['difference_time'] * 3600 
                    : 0
                )
            );


        if(isset($data['POST']))
            $data['POST'] = array_merge($_POST, $data['POST']);
        else
            $data['POST'] = $_POST;

        if(isset($data['GET']))
            $data['GET'] = array_merge($_GET, $data['GET']);
        else
            $data['GET'] = $_GET;

        if(isset($data['POST'], $data['POST']['token']))
            unset($data['POST']['token']);

        if(isset($data['GET'], $data['GET']['token']))
            unset($data['GET']['token']);

        if(($post = self::getFormUpdates($data['POST'])) && !empty($post)){
            $data['POST'] = $post;
        }
        elseif(($get = self::getFormUpdates($data['GET'])) && !empty($get)){
            $data['GET'] = $get;
        }
        unset($post, $get);

        $data = [
            $req_url => $data
        ];

        return System::addCookie('remained_data', $data, $time);
    }

    private static function getFormUpdates(array $data = [], array $old_data = []){
        if(empty($data) && !empty($_POST))
            $data = $_POST;
        else
        if(empty($data) && !empty($_GET))
            $data = $_GET;

        $updates = [];

        if(empty($old_data)){
            foreach ($data as $key => $value) {
                if(strlen($key) >= 16 && substr($key, 0, 16) == 'all_start_elmnts'){
                    parse_str($value, $old_data);
                    unset($data[$key]);

                    if(isset($old_data[$key])) 
                        unset($old_data[$key]);
                    break;
                }
            }
        }

        if(is_array($data) && !empty($data) 
            && is_array($old_data) && !empty($old_data)
        )
            return array_diff_assoc($data, $old_data);

        return [];
    }



    /**
     * ВЫВОД СООБЩЕНИЯ В JSON -> exit;
     * 
     * @param bool $status true/false /.status/ = success/error
     * @param ? string $comment /.comment/
     * @param ? array $data /.result.$data 
     * @return void
     */

    public static function jsonAnswer(bool $status, string $comment = '', array $data = []): void{
        $result = [
            'status' => $status ? 'success' : 'error'
        ];

        if(!empty($comment))
            $result['comment'] = $comment;

        if(!empty($data))
            $result['result'] = $data;


        header('Content-Type: application/json');

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        exit();
    }


    /**
     * СОЗДАТЬ ПОПУП ОКНО (загрузка ч/з ajax)
     *
     * для вызова окна на странице:  href="#{$id}"
     * 
     * @param  string $id
     * @param  string $url
     * @param  array $data POST data
     * @return void
     */
    public static function modalFormGenerate(string $id, string $url, array $data = [], string $class = ''): void{
        if(empty($class))
            $class = $id;
        
        $res = "
        <script type=\"text/javascript\">
        window.addEventListener(\"load\", function() {
            jQuery('<div>', {
                id: '{$id}',
                class: 'uk-modal {$class}'
            }).appendTo('body');
            \$.ajax({
                type: \"POST\",
                url: '{$url}',
                data: { 
        ";

        $params_a = [];
        foreach($data as $key => $value)
            $params_a[] = "{$key}: '{$value}'";
            
        $res .= "            " . implode(",
                    ", $params_a);
        
        $res .= "
                },
                success: function(data) {
                    \$('#{$id}').html('<div class=\"uk-modal-dialog\"><div class=\"userbox modal-userbox-3\">' + data + '</div></div>');
                },
                error: function(){
                    \$('#{$id}').html('<div class=\"uk-modal-dialog\"><div class=\"userbox modal-userbox-3\">Connect ERROR</div></div>');
                }
            });
        });
        </script>
        ";

        print($res);
    }



    /**
     * СОЗДАТЬ СПОСОБ ДОСТАВКИ
     * @param $name
     * @param $ship_desc
     * @param $status
     * @param $tax
     * @return bool
     */
    public static function addDeliveryMethod($name, $ship_desc, $status, $tax, $when_pay)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'ship_methods 
            (title, ship_desc, tax, status, when_pay) 
        VALUES 
            (:name, :ship_desc, :tax, :status, :when_pay)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':ship_desc', $ship_desc, PDO::PARAM_STR);
        $result->bindParam(':tax', $tax, PDO::PARAM_INT);
        $result->bindParam(':when_pay', $when_pay, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    // ПОЛУЧИТЬ ДАННЫЕ СПОСОБА ДОСТАВКИ
    public static function getShipMethod($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."ship_methods WHERE method_id = $id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }
    
    
    // ИЗМЕНИТЬ СПОСОБ ДОСТАВКИ
    public static function editDeliveryMethod($id, $name, $ship_desc, $status, $tax, $when_pay)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'ship_methods 
        SET 
            title = :name, 
            ship_desc = :ship_desc, 
            tax = :tax, 
            when_pay = :when_pay,
            status = :status
        WHERE method_id = '.$id;
        
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':ship_desc', $ship_desc, PDO::PARAM_STR);
        $result->bindParam(':tax', $tax, PDO::PARAM_INT);
        $result->bindParam(':when_pay', $when_pay, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    // УДАЛИТЬ СПОСОБ ДОСТАВКИ
    public static function deleteShipMethod($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'ship_methods WHERE method_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }
    
    
    
    public static function replaceNameEmail($string, $user_id)
    {
        
        if($user_id){
            
            $user = User::getUserById($user_id);
            $replace = array(
                '[NAME]' => urlencode($user['user_name']),
                '[CLIENT_NAME]' => urlencode($user['user_name']),
                '[EMAIL]' => $user['email'],
            ); 
            
            return strtr($string, $replace);  
        } else return $string;
    }


    // ЗАГРУЗКА ВИДЖЕТА В ТЕКСТ
    public static function renderContent($text)
    {
        $setting = System::getSetting();
        // Ищем шорт теги
        preg_match_all('/{LOADWIDGET_([0-9]+)}/', $text, $matches);

        if (!empty($matches)) {
            $replace = array();
            $widgets_path = file_exists(ROOT."/template/{$setting['template']}/widgets") ?
                ROOT."/template/{$setting['template']}/widgets" : ROOT."/template/site/widgets";

            foreach ($matches[1] as $id) {// Перебираем массив совпадений
                $widget = Widgets::getWidgetData($id); // Получить виджет с $id

                if ($widget) {
                    $widget_params = unserialize($widget['params']);
                    $data = ob_start();

                    if (!$widget['private'] || $is_auth = User::isAuth()) {
                        require_once ("$widgets_path/{$widget['widget_type']}.php");
                    }

                    $key = "{LOADWIDGET_$id}";
                    $replace[$key] = ob_get_contents();
                    ob_end_clean();

                    return strtr($text, $replace);
                }
            }
        }

        return $text;
    }

    // ПОЛУЧИТЬ УРОВНИ ДОСТУПА
    public static function getACLlist()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT acl_id, user_id, permissions FROM ".PREFICS."acl ORDER BY acl_id ASC");

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    // ДАННЫЕ УРОВНЯ ДОСТУПА ПО ID
    public static function getACLbyID($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."acl WHERE acl_id = $id ");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    // ДАННЫЕ УРОВНЯ ДОСТУПА ПО USER_ID
    public static function getACLbyUserID($user_id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."acl WHERE user_id = $user_id ");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    // СОЗДАТЬ УРОВЕНЬ ДОСТУПА
    public static function AddPermiss($user_id, $perm)
    {
        $db = Db::getConnection();
        $result = $db->prepare('INSERT INTO '.PREFICS.'acl (user_id, permissions) VALUES (:user_id, :perm)');
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->bindParam(':perm', $perm, PDO::PARAM_STR);

        return $result->execute();
    }


    // ИЗМЕНИТЬ УРОВЕНЬ ДОСТУПА
    public static function UpdPermiss($acl_id, $perm)
    {
        $db = Db::getConnection();
        $result = $db->prepare('UPDATE '.PREFICS.'acl SET permissions = :permissions WHERE acl_id = '.$acl_id);
        $result->bindParam(':permissions', $perm, PDO::PARAM_STR);

        return $result->execute();
    }


    // УДАЛИТЬ УРОВЕНЬ ДОСТУПА
    public static function delACL($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'acl WHERE acl_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * СОЗДАТЬ ПУНКТ МЕНЮ
     * @param $name
     * @param $url
     * @param $sort
     * @param $status
     * @param $type
     * @param $menu_id
     * @param $title
     * @param $new_window
     * @param $parent_id
     * @param $sitemap
     * @param $changefreq
     * @param $visible
     * @param $priority
     * @return bool
     */
    public static function addMenuItem($name, $url, $sort, $status, $type, $menu_id, $title, $new_window, $parent_id,
                                       $sitemap, $changefreq, $visible, $priority)
    {
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'menu_items (type, link, name, sort, title, menu_id, status, new_window, parent_id, sitemap, changefreq, visible, priority ) 
                VALUES (:type, :link, :name, :sort, :title, :menu_id, :status, :new_window, :parent_id, :sitemap, :changefreq, :visible, :priority )';

        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':type', $type, PDO::PARAM_STR);
        $result->bindParam(':link', $url, PDO::PARAM_STR);
        $result->bindParam(':sort', $sort, PDO::PARAM_INT);
        $result->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
        $result->bindParam(':new_window', $new_window, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $result->bindParam(':sitemap', $sitemap, PDO::PARAM_INT);
        $result->bindParam(':changefreq', $changefreq, PDO::PARAM_STR);
        $result->bindParam(':visible', $visible, PDO::PARAM_INT);
        $result->bindParam(':priority', $priority, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ИЗМЕНИТЬ ПУНКТ МЕНЮ
     * @param $id
     * @param $name
     * @param $url
     * @param $sort
     * @param $status
     * @param $menu_id
     * @param $title
     * @param $new_window
     * @param $parent_id
     * @param $sitemap
     * @param $changefreq
     * @param $visible
     * @param $priority
     * @return bool
     */
    public static function editMenuItem($id, $name, $url, $sort, $status, $menu_id, $title, $new_window, $parent_id, $sitemap, 
                                        $changefreq, $visible, $priority, $show_in_order_pages, $showByGroup, $showGroups)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'menu_items SET link = :link, sort = :sort, title = :title, menu_id = :menu_id, status = :status, 
        name = :name, new_window = :new_window, parent_id = :parent_id, sitemap = :sitemap, changefreq = :changefreq, visible = :visible,
        priority = :priority, show_in_order_pages = :show_in_order_pages, showByGroup = :showByGroup, showGroups = :showGroups WHERE item_id = '.$id;
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':link', $url, PDO::PARAM_STR);
        $result->bindParam(':sort', $sort, PDO::PARAM_INT);
        $result->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
        $result->bindParam(':new_window', $new_window, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $result->bindParam(':sitemap', $sitemap, PDO::PARAM_INT);
        $result->bindParam(':changefreq', $changefreq, PDO::PARAM_STR);
        $result->bindParam(':visible', $visible, PDO::PARAM_INT);
        $result->bindParam(':priority', $priority, PDO::PARAM_STR);
        $result->bindParam(':show_in_order_pages', $show_in_order_pages, PDO::PARAM_INT);
        $result->bindParam(':showByGroup', $showByGroup, PDO::PARAM_INT);
        $result->bindParam(':showGroups', $showGroups, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ СПИСОК ПУНКТОВ МЕНЮ
     * @param int $status
     * @param null $parent
     * @return array|bool
     */
    public static function getMenuItems($status = 0, $parent = null)
    {
        $db = Db::getConnection();
        $result = false;
        if ($status == 0) {
            $result = $db->query("SELECT * FROM ".PREFICS."menu_items ORDER BY sort ASC");
        } elseif ($status != 0 AND $parent == null) {
            $result = $db->query("SELECT * FROM ".PREFICS."menu_items WHERE status = 1 AND parent_id = 0 ORDER BY sort ASC");
        } elseif ($status != 0 AND $parent != null) {
            $result = $db->query("SELECT * FROM ".PREFICS."menu_items WHERE status = 1 AND parent_id = $parent ORDER BY sort ASC");
        }

        if ($result) {
            $data = [];
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }

            return !empty($data) ? $data : false;
        }

        return false;
    }
    
    // ПОЛУЧИТЬ СПИСОК ПУНКТОВ ДЛЯ КАРТЫ САЙТА
    public static function getMenuItemsForSiteMap()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."menu_items WHERE status = 1 AND sitemap = 1 ORDER BY sort ASC");
        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
        
        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПУНКТА МЕНЮ
     * @param $id
     * @return bool|mixed
     */
    public static function getMenuItem($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."menu_items WHERE item_id = $id ");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПУНКТА МЕНЮ
     * @param $type
     * @return bool|mixed
     */
    public static function getMenuItemByType($type) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."menu_items WHERE type = '$type'");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * УДАЛИТЬ ПУНКТ МЕНЮ
     * @param $id
     * @return bool
     */
    public static function delMenuItem($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'menu_items WHERE item_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ПУНКТ МЕНЮ
     * @param $type
     * @return bool
     */
    public static function delMenuItemByType($type) {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'menu_items WHERE type = :type';
        $result = $db->prepare($sql);
        $result->bindParam(':type', $type, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * СОЗДАТЬ СТАТИЧНУЮ СТРАНИЦУ
     * @param $name
     * @param $status
     * @param $alias
     * @param $title
     * @param $meta_desc
     * @param $meta_keys
     * @param $content
     * @param $tmpl
     * @param $in_head
     * @param $in_body
     * @param $custom_code
     * @param $curl
     * @return bool
     */
    public static function addStaticPage($name, $status, $alias, $title, $meta_desc, $meta_keys, $content, $tmpl, $in_head,
                                         $in_body, $custom_code, $curl)
    {
        $db = Db::getConnection();
        $hits = 0;
        $sql = 'INSERT INTO '.PREFICS.'pages (name, title, meta_desc, meta_keys, alias, content, status, hits, tmpl, in_head, in_body, custom_code, curl ) 
                VALUES (:name, :title, :meta_desc, :meta_keys, :alias, :content, :status, :hits, :tmpl, :in_head, :in_body, :custom_code, :curl)';

        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':meta_desc', $meta_desc, PDO::PARAM_STR);
        $result->bindParam(':meta_keys', $meta_keys, PDO::PARAM_STR);
        $result->bindParam(':alias', $alias, PDO::PARAM_STR);
        $result->bindParam(':content', $content, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':hits', $hits, PDO::PARAM_INT);
        $result->bindParam(':tmpl', $tmpl, PDO::PARAM_INT);
        $result->bindParam(':in_head', $in_head, PDO::PARAM_STR);
        $result->bindParam(':in_body', $in_body, PDO::PARAM_STR);
        $result->bindParam(':custom_code', $custom_code, PDO::PARAM_STR);
        $result->bindParam(':curl', $curl, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * ИЗМЕНИТЬ СТАТИЧНУЮ СТРАНИЦУ
     * @param $id
     * @param $name
     * @param $status
     * @param $alias
     * @param $title
     * @param $meta_desc
     * @param $meta_keys
     * @param $content
     * @param $tmpl
     * @param $in_head
     * @param $in_body
     * @param $custom_code
     * @param $curl
     * @return bool
     */
    public static function editPage($id, $name, $status, $alias, $title, $meta_desc, $meta_keys, $content, $tmpl, $in_head,
                                    $in_body, $custom_code, $curl, $access_type, $groups, $planes)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'pages SET name = :name, title = :title, meta_desc = :meta_desc, meta_keys = :meta_keys, alias = :alias, 
                content = :content, status = :status, hits = :hits, tmpl = :tmpl, in_head = :in_head, in_body = :in_body, 
                custom_code = :custom_code, curl = :curl, access_type = :access_type, groups = :groups, planes = :planes WHERE id = '.$id;
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':title', $title, PDO::PARAM_STR);
        $result->bindParam(':meta_desc', $meta_desc, PDO::PARAM_STR);
        $result->bindParam(':meta_keys', $meta_keys, PDO::PARAM_STR);
        $result->bindParam(':alias', $alias, PDO::PARAM_STR);
        $result->bindParam(':content', $content, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $result->bindParam(':hits', $hits, PDO::PARAM_INT);
        $result->bindParam(':tmpl', $tmpl, PDO::PARAM_INT);
        $result->bindParam(':in_head', $in_head, PDO::PARAM_STR);
        $result->bindParam(':in_body', $in_body, PDO::PARAM_STR);
        $result->bindParam(':custom_code', $custom_code, PDO::PARAM_STR);
        $result->bindParam(':curl', $curl, PDO::PARAM_STR);
        $result->bindParam(':access_type', $access_type, PDO::PARAM_INT);
        $result->bindParam(':groups', $groups, PDO::PARAM_STR);
        $result->bindParam(':planes', $planes, PDO::PARAM_STR);

        return $result->execute();
    }


    // ПОЛУЧИТЬ СПИСОК СТАТИЧНЫХ СТРАНИЦ
    public static function getStaticPages()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."pages ORDER BY id ASC");

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }


    // ПОЛУЧИТЬ ДАННЫЕ СТРАНИЦЫ по ID
    public static function getPageData($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."pages WHERE id = $id ");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    // ПОЛУЧИТЬ ДАННЫЕ СТРАНИЦЫ по alias
    public static function getPageDataByAlias($alias, $status)
    {
        $db = Db::getConnection();
        $status = intval($status);
        $result = $db->query(" SELECT * FROM ".PREFICS."pages WHERE alias = '$alias' AND status = $status LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (isset($data) && !empty($data)) {
            $hits = $data['hits'] + 1;
            $sql = 'UPDATE '.PREFICS.'pages SET hits = :hits WHERE id = '.$data['id'];
            $result = $db->prepare($sql);
            $result->bindParam(':hits', $hits, PDO::PARAM_INT);
            $result->execute();

            return $data;
        } else {
            return false;
        }
    }


    /**
     * УДАЛИТЬ СТРАНИЦУ
     * @param $id
     * @return bool
     */
    public static function deletePage($id)
    {
        $db = Db::getConnection();
        $result = $db->prepare('DELETE FROM '.PREFICS.'pages WHERE id = :id');
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * СПИСОК ФОРМ ОБРАТНОЙ СВЯЗИ
     * @return array|bool
     */
    public static function getFeedBackFormList()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."feedback_forms ORDER BY form_id DESC");

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }
    
    
    
    // ДОБАВИТЬ ФОРМУ ОБРАТНОЙ СВЯЗИ
    public static function AddForm($name, $form_desc, $status, $default_form, $params)
    {
        $hits = 0;
        $db = Db::getConnection();
        $sql = 'INSERT INTO '.PREFICS.'feedback_forms (name, form_desc, params, hits, status ) 
                VALUES (:name, :form_desc, :params, :hits, :status)';

        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':form_desc', $form_desc, PDO::PARAM_STR);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':hits', $hits, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    // ИЗМЕНИТЬ ФОРМУ ОБРАТНОЙ СВЗЯИ
    public static function editForm($id, $name, $form_desc, $status, $default_form, $params)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'feedback_forms SET name = :name, form_desc = :form_desc, params = :params, default_form = :default_form, status = :status WHERE form_id = '.$id;
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':form_desc', $form_desc, PDO::PARAM_STR);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':default_form', $default_form, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        $res = $result->execute();
        
        if ($default_form == 1) { 
            $sql = 'UPDATE '.PREFICS.'feedback_forms SET default_form = 0 WHERE form_id != :id';
            $result = $db->prepare($sql);
            $result->bindParam(':id', $id, PDO::PARAM_INT);
            $result->execute();
        }

        return $res;
    }


    // УДАЛИТЬ ФОРМУ
    public static function deleteFeedbackForm($id)
    {
        $db = Db::getConnection();

        $result = $db->query(" SELECT * FROM ".PREFICS."feedback_forms WHERE form_id = $id LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if (isset($data) && !empty($data) && $data['default_form'] == 1) {
            return false;
        }

        $sql = 'DELETE FROM '.PREFICS.'feedback_forms WHERE form_id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);

        return $result->execute();
    }


    // ПОЛУЧИТЬ ДАННЫЕ ФОРМЫ
    public static function getFormDataByID($id)
    {
        if($id != null) {
            $db = Db::getConnection();
            $result = $db->query(" SELECT * FROM " .
                                 PREFICS .
                                 "feedback_forms WHERE form_id = $id LIMIT 1");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if (isset($data) &&
                !empty($data)) return $data;
            else return false;
        }
    }


    // ПОЛУЧИТЬ ФОРМУ ПО УМОЛЧАНИЮ
    public static function getFormDataByDefault($id = null)
    {
        $db = Db::getConnection();
        if ($id == null) $result = $db->query(" SELECT * FROM ".PREFICS."feedback_forms WHERE default_form = 1 AND status = 1 LIMIT 1");
        else $result = $db->query(" SELECT * FROM ".PREFICS."feedback_forms WHERE form_id = $id AND status = 1 LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if (isset($data) && !empty($data)) {

            $i = $data['hits'] + 1;
            $sql = 'UPDATE '.PREFICS.'feedback_forms SET hits = :hits WHERE form_id = '.$data['form_id'];
            $result = $db->prepare($sql);
            $result->bindParam(':hits', $i, PDO::PARAM_INT);
            $result->execute();

            return $data;
        } else {
            return false;
        }
    }


    // ПРОСМОТР СООБЩЕНИЯ
    public static function getFeedbackMessage($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT * FROM ".PREFICS."feedback WHERE id = $id LIMIT 1 ");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if (isset($data)) {

            if ($data['status'] == 0) {
                $status = 1;
                $sql = 'UPDATE '.PREFICS.'feedback SET status = :status WHERE id = '.$id;
                $result = $db->prepare($sql);
                $result->bindParam(':status', $status, PDO::PARAM_INT);
                $result->execute();

                $data['status'] = 1;
            }

            return $data;

        }
        else return false;
    }


    // СОХРАНИТЬ СООБЩЕНИЕ
    public static function saveMessage($id, $status, $comment)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'feedback SET comment = :comment, status = :status WHERE id = '.$id;
        $result = $db->prepare($sql);
        $result->bindParam(':comment', $comment, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        return $result->execute();
    }
    
    
    // ЗАПИСАТЬ ФИДБЭК В БАЗУ
    public static function writeFeedback($name, $email, $phone, $field1, $field2, $text, $form)
    {
        $db = Db::getConnection();
        $status = 0;
        $date = time();
        $sql = 'INSERT INTO '.PREFICS.'feedback (name, email, phone, text, field1, field2, create_date, status, form_id ) 
                VALUES (:name, :email, :phone, :text, :field1, :field2, :create_date, :status, :form_id)';
        
        $result = $db->prepare($sql);
        $result->bindParam(':name', $name, PDO::PARAM_STR);
        $result->bindParam(':email', $email, PDO::PARAM_STR);
        $result->bindParam(':text', $text, PDO::PARAM_STR);
        $result->bindParam(':phone', $phone, PDO::PARAM_STR);
        $result->bindParam(':field1', $field1, PDO::PARAM_STR);
        $result->bindParam(':field2', $field2, PDO::PARAM_STR);
        $result->bindParam(':form_id', $form, PDO::PARAM_INT);
        $result->bindParam(':create_date', $date, PDO::PARAM_INT);
        $result->bindParam(':status', $status, PDO::PARAM_INT);
        return $result->execute();
    }

    public static function getLastFeedbackId() {
        $db = Db::getConnection();
        $sql = "SELECT MAX(id) AS `maxid` FROM `".PREFICS."feedback`";

        $result = $db->query($sql);
        return $result->fetch() ?? 0;
    }
    
    
    
    // ПОЛУЧИТЬ СПИСОК СООБЩЕНИЙ С САЙТА
    public static function getFeedBackList()
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."feedback ORDER BY id DESC");
        $i = 0;
        while($row = $result->fetch()) {
            $data[$i]['id'] = $row['id'];
            $data[$i]['name'] = $row['name'];
            $data[$i]['email'] = $row['email'];
            $data[$i]['text'] = $row['text'];
            $data[$i]['status'] = $row['status'];
            $data[$i]['create_date'] = $row['create_date'];
            $data[$i]['form_id'] = $row['form_id'];
            $i++;
        }
        if (isset($data)) return $data;
        else return false;
    }
    
    
    
    // УДАЛИТЬ СООБЩЕНИЕ С САЙТА
    public static function deleteFeedback($id)
    {
        $db = Db::getConnection();
        $sql = 'DELETE FROM '.PREFICS.'feedback WHERE id = :id';
        $result = $db->prepare($sql);
        $result->bindParam(':id', $id, PDO::PARAM_INT);
        return $result->execute();
    }





    //ПРЕОБРАЗОВАТЬ РАЗМЕР ФАЙЛА В БАЙТЫ
    public static function convertSize($size) {
        if (preg_match('/^(-?[\d\.]+)(|[KMG])$/i', $size, $match)) {
            $pos = array_search($match[2], array("", "K", "M", "G"));
            return $match[1] * pow(1024, $pos);
        }
    }


    //ПОЛУЧИТЬ МАКС. РАЗМЕР ЗАГРУЖАЕМЫХ ФАЙЛОВ
    public static function getPostMaxSize($type = 'b') {
        $max_size = ini_get('post_max_size');
        $bytes = self::convertSize($max_size);
        switch ($type) {
            case 'b':
                $res = $bytes;
                break;
            case 'kb':
                $res = $bytes / 1024;
                break;
            case 'mb':
                $res = $bytes / 1048576;
                break;
            case 'gb':
                $res = $bytes / 1073741824;
                break;
        }

        return $res;
    }


    /**
     * УСТАНОВИТЬ РАСШИРЕНИЕ
     * @param $file_path
     * @param $name
     * @param string $type
     * @return bool
     */
    public static function installExtensions($file_path, $name, $type='extension') {
        if (self::getPostMaxSize() < filesize($file_path)) {
            self::addError('Размер загружаемого файла превышает максимально допустимый ('.self::getPostMaxSize('mb').' Мб)');
            return false;
        }

        if (!extension_loaded('zip')) {
            self::addError('На вашем хостинге не установлен модуль ZIP');
            return false;
        }

        $part_message = $type == 'update' ? 'Обновление' : 'Расширение';
        $dir = time();
        $tmp_path = ROOT . "/tmp/$dir";

        $zip = new ZipArchive(); //Создаём объект для работы с ZIP-архивами

        if ($res = $zip->open($file_path) === true) { //Открываем архив archive.zip и делаем проверку успешности открытия
            $zip->extractTo($tmp_path);
            $zip->close();
        } else {
            $message = self::addError('Не удалось открыть файл с расширением');
            return false;
        }


        ActionLog::writeLog('system', $type, 'system', 0, time(), $_SESSION['admin_user'], json_encode(['name' => $name]));

        // УСТАНОВИТЬ DUMP В БД
        // TODO SM-1704 сделать эталон БД и внедрить куда-то сюда проверку или создать какой-то helper отдельный.
        $dumps = [];
        $dumps_dirpath = ROOT . "/tmp/$dir/dumps/";
        if (file_exists($dumps_dirpath)) {
            $dump_list = scandir($dumps_dirpath);

            foreach ($dump_list as $dump_file) {
                if ($dump_file != '.' && $dump_file != '..') {
                    $dump_version = pathinfo($dump_file)['filename'];
                    if (version_compare(CURR_VER, $dump_version, '<')) {
                        $dumps[] = "$dumps_dirpath/$dump_file";
                    }
                }
            }
        }

        if (empty($dumps)) {
            $dumps[] = ROOT . "/tmp/$dir/dump.sql";
        }

        foreach ($dumps as $dump_path) {
            if (file_exists($dump_path)) {
                $dump = file_get_contents($dump_path);
                $dump = str_replace("#PREFIX#", PREFICS, $dump);
                $queries = preg_split("/;+(?=([^'|^\\\']*['|\\\'][^'|^\\\']*['|\\\'])*[^'|^\\\']*[^'|^\\\']$)/", $dump);

                foreach($queries as $query) {
                    $query = trim(str_replace('#END_QUERY#', ';', $query));
                    // TODO SM-1703 Добавить логирование если какие-то запросы были пропущены
                    if ((preg_match('#^ALTER TABLE\s`(.*?)`\sADD\s`#i', $query, $matches) && preg_match('#\sADD\s`(.*?)`\s#i', $query, $matches2) && $query_type = 1) // добавление колонки
                        || (preg_match('#^ALTER TABLE\s`(.*?)`\sDROP COLUMN\s`#i', $query, $matches) && preg_match('#\sDROP COLUMN\s`(.*?)`#i', $query, $matches2) && $query_type = 2) // удаление колонки
                        || (preg_match('#^ALTER TABLE\s`(.*?)`\sCHANGE\s`#i', $query, $matches) && preg_match('#\sCHANGE\s`(.*?)`\s`(.*?)`\s#i', $query, $matches2) && $query_type = 3) // изменение колонки
                        || (preg_match('#^ALTER TABLE\s`(.*?)`\sADD INDEX\s#i', $query, $matches) && preg_match('#\sADD INDEX\s(.*?)\s\(`(.*?)`\)#i', $query, $matches2) && $query_type = 4)) // добавление индекса для колонки
                    {
                        $table_name = trim($matches[1]);
                        if ($query_type == 4) {
                            $column_name = trim($matches2[2]);
                            $index_name = trim($matches2[1], " \t\n\r`");
                            $result = System::index_exists($table_name, $column_name, $index_name);
                        } elseif($query_type == 3) {
                            $column1_name = trim($matches2[1]);
                            $column2_name = trim($matches2[2]);
                            if ($column1_name !== $column2_name) {
                                $result = System::column_exists($table_name, $column1_name) && !System::column_exists($table_name, $column2_name);
                            } else {
                                $result = System::column_exists($table_name, $column1_name);
                            }
                        } else {
                            $column_name = trim($matches2[1]);
                            $result = System::column_exists($table_name, $column_name);
                        }

                        if ((!$result && in_array($query_type, [1,4])) || ($result && in_array($query_type, [2,3]))) {
                            System::RestoreExtDump($query);
                        }
                    } else {
                        System::RestoreExtDump($query);
                    }
                }
            }
        }


        if (file_exists("$tmp_path/db/migrations") && version_compare(CURR_VER, '3.8.2', '>')) { // УСТАНОВКА МИГРАЦИЙ
            $migration_list = [];
            $migrations = scandir("$tmp_path/db/migrations");

            foreach ($migrations as $migration) {
                if ($migration !== '.' && $migration !== '..' && is_dir("$tmp_path/db/migrations/$migration")) {
                    if (!file_exists(ROOT . "/db/migrations/$migration")) {
                        if (!mkdir(ROOT . "/db/migrations/$migration")) {
                            self::addError("Не удалось создать папку $migration при установке миграций");
                            return false;
                        }
                    }

                    if (str_replace('.','', CURR_VER) < $migration) {
                        $migration_list[] = $migration;
                        $migration_files = self::getFilesList("$tmp_path/db/migrations/$migration");
                        if ($migration_files) {
                            foreach ($migration_files as $migration_file_path) {
                                $path_info = pathinfo($migration_file_path);
                                $migration_file_dest = ROOT."/db/migrations/$migration/{$path_info['filename']}.php";

                                if (!rename($migration_file_path, $migration_file_dest)) {
                                    self::addError("Не удалось создать файл миграции {$path_info['filename']}.php");
                                    return false;
                                }
                            }
                        }
                    }
                }
            }

            if ($migration_list) {
                foreach ($migration_list as $migration) {
                    $migrations = new \Migrations\migrationHandler($migration, "migrate", "web", false);
                    $result = $migrations->runTasks();

                    if (!$result || $result['errors']) {
                        self::addError("Не удалось установить миграцию $migration");
                        return false;
                    }

                    if ($result['executedMigrations']) {
                        foreach ($result['executedMigrations'] as $result_item) {
                            if ($result_item['version'] == $migration && !$result_item['status']) {
                                self::addError("Не удалось установить миграцию $migration");
                                return false;
                            }
                        }
                    }
                }
            }
        }


        $dir_erros = [];
        $file_erros = [];

        // Подключить файл params.php
        if (include (ROOT . "/tmp/$dir/params.php")) {
            // Сделать запись в БД
            $version = isset($version) ? $version : null;
            $update_url = isset($update_url) ? $update_url : null;
            $menu = isset($menu) ? $menu : null;

            $install = self::installExtens($name, $title, $type, $params, $enable, $link, $version, $update_url, $menu);

            if ($install) {
                if (isset($del_folders) && $del_folders) {
                    foreach ($del_folders as $del_folder) {
                        self::removeDirectory(ROOT.$del_folder);
                    }
                }

                if (isset($del_files) && $del_files) {
                    foreach ($del_files as $del_file) {
                        if (file_exists(ROOT.'/'.$del_file)) {
                            @unlink(ROOT.'/'.$del_file);
                        }
                    }
                }

                if ($folders) { // Создать папки
                    foreach($folders as $folder) {
                        $path = ROOT.$folder[0];
                        if (!is_dir($path)) {
                            if (!mkdir($path)) {
                                $dir_erros[] = $path;
                            }
                        }
                    }
                }

                if ($files) { // Переместить файлы согласно инструкции в params.php
                    foreach ($files as $file) {
                        $old = $file[0];
                        $new = $file[1];
                        if (!rename($tmp_path.$old, $new)) {
                            $file_erros[] = $new;
                        }
                    }
                }
            }
        } else {
            $message = self::addError('Не найден файл установки params.php');
            self::removeTempDirectory(); // Очистить папку tmp
            return false;
        }

        self::removeTempDirectory(); // Очистить папку tmp

        if (!empty($dir_erros) || !empty($file_erros)) {
            $error_message = '';
            if (!empty($dir_erros)) {
                $error_message .= 'Не удалось обновить следующие папки:<br>' . implode(', ', $dir_erros);
            }
            if (!empty($file_erros)) {
                $error_message .= ($error_message ? '<br><br>' : '') . 'Не удалось обновить следующие файлы:<br>' . implode(', ', $file_erros);
            }

            self::writeError($error_message, 'cms_update');
            self::addError($error_message);
            return false;
        } else {
            self::addSuccess("$part_message успешно установлено");
        }

        return true;
    }
    
    
    // ПОЛУЧИТЬ СПИСОК ФУНКЦИЙ/РАСШИРЕНИЙ 
    public static function getAllExtensions($type)
    {
        $db = Db::getConnection();
        $query = "SELECT * FROM ".PREFICS."extensions";
        $query .= ($type != 'all' ? " WHERE type = '$type'" : '') . ' ORDER BY id ASC';
        $result = $db->query($query);

        $data = [];
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return !empty($data) ? $data : false;
    }
    
    
    // ПРОВЕРИТЬ ВКЛЮЧЕНО ЛИ РАСШИРЕНИЕ
    // Принимает название расширения и статус
    public static function CheckExtensension($extension, $status = 1)
    {
        $data = cache::get('ext'.$extension.$status);
        if (!$data) {
            $db = Db::getConnection();
            $result = $db->query(" SELECT * FROM " . PREFICS . "extensions WHERE name = '$extension' AND enable = $status LIMIT 1");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            cache::set('ext'.$extension.$status, $data ?? '', cache::$extensTime);
        }

        return !empty($data) ? $data : false;
    }

    public static function changeExtStatus($id, $status) {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'extensions SET enable = :enable WHERE id = '.$id;

        $result = $db->prepare($sql);
        $result->bindParam(':enable', $status, PDO::PARAM_INT);

        return $result->execute();
    }

    public static function changePaymentStatus($id, $status) {
        $db = Db::getConnection();
        $sql = 'UPDATE `'.PREFICS.'payments` SET status = :status WHERE payment_id = '.$id;

        $result = $db->prepare($sql);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * УСТАНОВИТЬ РАСШИРЕНИЕ И ПАРАМЕТРЫ
     * @param $name
     * @param $title
     * @param $type
     * @param $params
     * @param $enable
     * @param $link
     * @param $version
     * @param $update_url
     * @param null $menu
     * @return bool
     */
    public static function installExtens($name, $title, $type, $params, $enable, $link, $version, $update_url, $menu = null)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(id) FROM ".PREFICS."extensions WHERE name = '$name'");
        $count = $result->fetch();

        if ($count[0] == 0) {
            $sql = 'INSERT INTO '.PREFICS.'extensions (name, title, type, enable, params, link, version, update_url, menu) 
                    VALUES (:name, :title, :type, :status, :params, :link, :version, :update_url, :menu)';
                
            $result = $db->prepare($sql);
            $result->bindParam(':name', $name, PDO::PARAM_STR);
            $result->bindParam(':title', $title, PDO::PARAM_STR);
            $result->bindParam(':type', $type, PDO::PARAM_STR);
            $result->bindParam(':status', $enable, PDO::PARAM_INT);
            $result->bindParam(':params', $params, PDO::PARAM_STR);
            $result->bindParam(':link', $link, PDO::PARAM_STR);
            $result->bindParam(':version', $version, PDO::PARAM_STR);
            $result->bindParam(':update_url', $update_url, PDO::PARAM_STR);
            $result->bindParam(':menu', $menu, PDO::PARAM_STR);
        } else {
            $sql = 'UPDATE '.PREFICS."extensions SET name = :name, title = :title, version = :version,
                    update_url = :update_url, menu  = :menu WHERE name = '$name'";
            
            $result = $db->prepare($sql);
            $result->bindParam(':name', $name, PDO::PARAM_STR);
            $result->bindParam(':title', $title, PDO::PARAM_STR);
            $result->bindParam(':version', $version, PDO::PARAM_STR);
            $result->bindParam(':update_url', $update_url, PDO::PARAM_STR);
            $result->bindParam(':menu', $menu, PDO::PARAM_STR);
        }

        return $result->execute();
    }


    /**
     * Дамп таблиц при установке
     * @param $query
     * @return bool
     */
    public static function RestoreExtDump($query)
    {
        $db = Db::getConnection();
        $result = $db->query($query);

        return $result ? true : false;
    }


    /**
     * УСТАНОВИТЬ ПЛАТЁЖКУ
     * @param $name
     * @param $title
     * @param $enable
     * @param $params
     * @param $payment_desc
     * @return bool
     */
    public static function installPayment($name, $title, $enable, $params, $payment_desc)
    {
        $db = Db::getConnection();
        $sort = 0;
        
        $result = $db->query("SELECT COUNT(payment_id) FROM ".PREFICS."payments WHERE name = '$name'");
        $count = $result->fetch();
        if ($count[0] == 0) {
            $sql = 'INSERT INTO '.PREFICS.'payments (name, title, payment_desc, status, sort, params ) 
                VALUES (:name, :title, :payment_desc, :enable, :sort, :params)';

            $result = $db->prepare($sql);
            $result->bindParam(':name', $name, PDO::PARAM_STR);
            $result->bindParam(':title', $title, PDO::PARAM_STR);
            $result->bindParam(':payment_desc', $payment_desc, PDO::PARAM_STR);
            $result->bindParam(':sort', $sort, PDO::PARAM_STR);
            $result->bindParam(':enable', $enable, PDO::PARAM_INT);
            $result->bindParam(':params', $params, PDO::PARAM_STR);

            return $result->execute();
        } else {
            return false;
        }
    }


    /**
     * УДАЛИТЬ ПЛАТЁЖКУ
     * @param $id
     * @return bool
     */
    public static function deletePayment($id)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT name FROM ".PREFICS."payments WHERE payment_id = $id ");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        if (isset($data)) {
            $dir = ROOT .'/payments/'.$data['name'];
            $del = self::removeDirectory($dir);
            
            $sql = 'DELETE FROM '.PREFICS.'payments WHERE payment_id = :id';
            $result = $db->prepare($sql);
            $result->bindParam(':id', $id, PDO::PARAM_INT);

            return $result->execute();
        }
    }


    //УДАЛЕНИЕ ПАПКИ С ФАЙЛАМИ
    public static function removeDirectory($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        if (is_file($path) || is_link($path)) {
            return @unlink($path);
        }

        $files = scandir($path);
        if ($files) {
            foreach($files as $filename)
            {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $file = $path . '/' . $filename;
                is_dir($file) ? self::removeDirectory($file) : @unlink($file);
            }
        }

        return !@rmdir($path) ? false : true;
    }


    public static function getFilesList($path) {
        if (is_file($path)) {
            return $path;
        }

        $files = scandir($path);
        $list = [];

        if ($files) {
            foreach($files as $filename) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $file = "$path/$filename";
                if (is_dir($file)) {
                    $list = array_merge($list, self::getFilesList($file));
                } else {
                    $list[] = $file;
                }
            }
        }

        return $list;
    }


    //УДАЛЕНИЕ ПАПКИ ДЛЯ ВРЕМЕННЫХ ФАЙЛОВ
    public static function removeTempDirectory()
    {
        $path = ROOT . '/tmp';
        if ($objs = glob($path."/*")) {
            foreach($objs as $obj) {
                self::removeDirectory($obj);
            }
        }
    }


    /**
     * ПОЛУЧИТь ГЛАВНЫЕ НАСТРОЙКИ
     * @return bool|mixed
     */
    public static function getMainSetting() {
        if (empty(self::$main_settings)) {
            $db = Db::getConnection();
            $query = "SELECT script_url, debug_mode, license_key, lang, currency, template, cookie, security_key 
                      FROM " . PREFICS . "settings WHERE setting_id = 1";
            $result = $db->query($query);
            $data = $result->fetch(PDO::FETCH_ASSOC);
            self::$main_settings = !empty($data) ? $data : false;
        }

        return self::$main_settings;
    }
    
    
    
    // СОХРАНИТЬ ПУНКТЫ ЮЗЕРСКОГО ВЫПАДАЮЩЕГО МЕНЮ
    public static function saveUserMenu($user_menu)
    {
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'settings_main_page SET user_menu = :user_menu WHERE settings_main_page_id = 1';
        $result = $db->prepare($sql);
        $result->bindParam(':user_menu', $user_menu, PDO::PARAM_STR);
        return $result->execute();
    }


    // ПОЛУЧИТЬ ДОП, НАСТРОЙКИ
    public static function getSettingMainpage()
    {
        $data = cache::get('getSettingMainpage');
        if (!$data) {
            $db = Db::getConnection();
            $result = $db->query(" SELECT * FROM " . PREFICS . "settings_main_page WHERE settings_main_page_id = 1 ");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            cache::set('getSettingMainpage', $data, cache::$extensTime);
        }
        if (isset($data)) return $data;
    }


    // +KEMSTAT-8
    public static function getSettingMainpageBySecondId()
    {
        $data = cache::get('getSettingMainpage');
        if (!$data) {
            $db = Db::getConnection();
            $result = $db->query(" SELECT * FROM " . PREFICS . "settings_main_page WHERE settings_main_page_id = 2 ");
            $data = $result->fetch(PDO::FETCH_ASSOC);
            cache::set('getSettingMainpage', $data, cache::$extensTime);
        }
        if (isset($data)) return $data;
    }
    // +KEMSTAT+8
    
    
    /**
     * ПОЛУЧИТЬ ВСЕ НАСТРОЙКИ
     * @param bool $v
     * @return bool|mixed
     */
    public static function getSetting($v = false)
    {
        if (empty(self::$settings)) {
            $db = Db::getConnection();
            $query = "SELECT s.*, ls.* FROM ".PREFICS."settings AS s
                      LEFT JOIN ".PREFICS."letters_settings AS ls ON s.setting_id = ls.setting_id
                      WHERE s.setting_id = 1";
            $result = $db->query($query);

            $data = $result->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $data['params'] = $v ? json_decode($data['params'], true) : $data['params'];
                $data['reg_sms'] = json_decode($data['reg_sms'] ?? "{}", true);
                $data['user_sessions'] = json_decode($data['user_sessions'], true);
                $data['user_sessions']['count'] = isset($data['user_sessions']['count']) ? $data['user_sessions']['count'] : 3;
                $data['user_sessions']['time_delete'] = isset($data['user_sessions']['time_delete']) ? $data['user_sessions']['time_delete'] : 12;
            }

            self::$settings = !empty($data) ? $data : false;
        }

        return self::$settings;
    }


    /**
     * СОХРАНИТЬ НАСТРОЙКИ
     * @param $site_name
     * @param $admin_email
     * @param $support_email
     * @param $lang
     * @param $currency
     * @param $template
     * @param $template_set
     * @param $show_items
     * @param $script_url
     * @param $security_key
     * @param $cookie
     * @param $secret_key
     * @param $debug_mode
     * @param $max_upload
     * @param $use_cart
     * @param $enable_catalog
     * @param $enable_reviews
     * @param $enable_landing
     * @param $enable_sale
     * @param $enable_cabinet
     * @param $enable_registration
     * @param $multiple_authorizations
     * @param $user_sessions
     * @param $enable_feedback
     * @param $write_feedback
     * @param $split_test_enable
     * @param $request_phone
     * @param $show_order_note
     * @param $email_protection
     * @param $strict_report
     * @param $simple_free_dwl
     * @param $dwl_in_lk
     * @param $order_life_time
     * @param $dwl_time
     * @param $dwl_count
     * @param $yacounter
     * @param $ga_target
     * @param $use_smtp
     * @param $smtp_host
     * @param $smtp_port
     * @param $smtp_user
     * @param $smtp_pass
     * @param $smtp_ssl
     * @param $sender_name
     * @param $sender_email
     * @param $smtp_domain
     * @param $smtp_selector
     * @param $smtp_private_key
     * @param $img
     * @param $return_path
     * @param $login_redirect
     * @param $show_surname
     * @param $show_patronymic
     * @param $show_telegram_nick
     * @param $show_instagram_nick
     * @param $smsc
     * @param $countries_list
     * @param $session_time
     * @param $private_key
     * @param $params
     * @param $editor
     * @param $logs_life_time
     * @param $nds_enable
     * @param $nds_value
     * @return bool
     */
    public static function saveSettings(
        $site_name, $admin_email, $support_email, $lang, $currency, $template, $template_set,
        $show_items, $script_url, $security_key, $cookie, $secret_key, $debug_mode, $max_upload,
        $use_cart, $enable_catalog, $enable_reviews, $enable_landing, $enable_sale, $enable_cabinet,
        $enable_registration, $multiple_authorizations, $user_sessions, $enable_feedback, $write_feedback,
        $split_test_enable, $request_phone, $show_order_note, $email_protection, $strict_report,
        $simple_free_dwl, $dwl_in_lk, $order_life_time, $dwl_time, $dwl_count, $yacounter, $ga_target,
        $use_smtp, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_ssl, $sender_name,
        $sender_email, $smtp_domain, $smtp_selector, $smtp_private_key, $img, $return_path,
        $login_redirect, $show_surname, $only_name2name, $show_patronymic, $show_telegram_nick,
        $show_instagram_nick, $smsc, $countries_list, $session_time, $private_key, $params, $editor,
        $logs_life_time, $nds_enable, $nds_value, $notify_admin_about_account_statement, $emails_for_account_statement_notifications,
        $reCaptcha, $sms_service, $mobizon, $show_vk_page
    ){
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'settings SET lang = :lang, currency = :currency, admin_email = :admin_email, support_email = :support_email,
                template = :template, template_set = :template_set, show_items = :show_items, script_url = :script_url, simple_free_dwl = :simple_free_dwl,
                security_key = :security_key, cookie = :cookie, secret_key = :secret_key, use_cart = :use_cart, request_phone = :request_phone,
                order_life_time = :order_life_time, enable_catalog = :enable_catalog, enable_landing = :enable_landing, enable_sale = :enable_sale,
                enable_cabinet = :enable_cabinet, enable_registration = :enable_registration, multiple_authorizations = :multiple_authorizations,
                user_sessions = :user_sessions, enable_feedback = :enable_feedback, write_feedback = :write_feedback, dwl_time = :dwl_time, dwl_count = :dwl_count,
                dwl_in_lk = :dwl_in_lk, use_smtp = :use_smtp, smtp_host = :smtp_host, smtp_port = :smtp_port, smtp_user = :smtp_user,
                smtp_pass = :smtp_pass, smtp_ssl = :smtp_ssl, sender_email = :sender_email, sender_name = :sender_name, smtp_domain = :smtp_domain,
                smtp_selector = :smtp_selector, smtp_private_key = :smtp_private_key, strict_report = :strict_report, debug_mode = :debug_mode,
                yacounter = :yacounter, ga_target = :ga_target, cover = :cover, site_name = :site_name, enable_reviews = :enable_reviews,
                max_upload = :max_upload, show_order_note = :show_order_note, email_protection = :email_protection, split_test_enable = :split_test_enable,
                return_path = :return_path, login_redirect = :login_redirect, show_surname = :show_surname, only_name2name = :only_name2name, 
                show_patronymic = :show_patronymic, show_telegram_nick = :show_telegram_nick, show_instagram_nick = :show_instagram_nick, smsc = :smsc,
                countries_list = :countries_list, session_time = :session_time, private_key = :private_key, params = :params, editor = :editor,
                logs_life_time = :logs_life_time, nds_enable = :nds_enable, nds_value = :nds_value, 
                notify_admin_about_account_statement = :notify_admin_about_account_statement, 
                emails_for_account_statement_notifications = :emails_for_account_statement_notifications,
                reCaptcha = :reCaptcha, sms_service = :sms_service, mobizon = :mobizon, show_vk_page = :show_vk_page WHERE setting_id = 1;';
                
        $result = $db->prepare($sql);
        $result->bindParam(':smsc', $smsc, PDO::PARAM_STR);
        $result->bindParam(':lang', $lang, PDO::PARAM_STR);
        $result->bindParam(':currency', $currency, PDO::PARAM_STR);
        $result->bindParam(':support_email', $support_email, PDO::PARAM_STR);
        $result->bindParam(':admin_email', $admin_email, PDO::PARAM_STR);
        $result->bindParam(':debug_mode', $debug_mode, PDO::PARAM_INT);
        $result->bindParam(':cover', $img, PDO::PARAM_STR);
        $result->bindParam(':site_name', $site_name, PDO::PARAM_STR);
        $result->bindParam(':split_test_enable', $split_test_enable, PDO::PARAM_INT);
        $result->bindParam(':yacounter', $yacounter, PDO::PARAM_STR);
        $result->bindParam(':ga_target', $ga_target, PDO::PARAM_INT);
        
        $result->bindParam(':logs_life_time', $logs_life_time, PDO::PARAM_INT);
        $result->bindParam(':nds_enable', $nds_enable, PDO::PARAM_INT);
        $result->bindParam(':nds_value', $nds_value, PDO::PARAM_INT);
        
        $result->bindParam(':show_surname', $show_surname, PDO::PARAM_INT);
        $result->bindParam(':only_name2name', $only_name2name, PDO::PARAM_INT);
        $result->bindParam(':show_patronymic', $show_patronymic, PDO::PARAM_INT);
        $result->bindParam(':show_telegram_nick', $show_telegram_nick, PDO::PARAM_INT);
        $result->bindParam(':show_instagram_nick', $show_instagram_nick, PDO::PARAM_INT);
        
        $result->bindParam(':login_redirect', $login_redirect, PDO::PARAM_INT);
        $result->bindParam(':template', $template, PDO::PARAM_STR);
        $result->bindParam(':template_set', $template_set, PDO::PARAM_INT);
        $result->bindParam(':show_items', $show_items, PDO::PARAM_INT);
        $result->bindParam(':use_cart', $use_cart, PDO::PARAM_INT);
        $result->bindParam(':script_url', $script_url, PDO::PARAM_STR);
        $result->bindParam(':security_key', $security_key, PDO::PARAM_STR);
        $result->bindParam(':cookie', $cookie, PDO::PARAM_STR);
        $result->bindParam(':secret_key', $secret_key, PDO::PARAM_STR);
        $result->bindParam(':private_key', $private_key, PDO::PARAM_STR);
        $result->bindParam(':request_phone', $request_phone, PDO::PARAM_INT);
        $result->bindParam(':order_life_time', $order_life_time, PDO::PARAM_INT);
        $result->bindParam(':dwl_time', $dwl_time, PDO::PARAM_INT);
        $result->bindParam(':dwl_count', $dwl_count, PDO::PARAM_INT);
        $result->bindParam(':simple_free_dwl', $simple_free_dwl, PDO::PARAM_INT);
        $result->bindParam(':strict_report', $strict_report, PDO::PARAM_INT);
        
        $result->bindParam(':enable_catalog', $enable_catalog, PDO::PARAM_INT);
        $result->bindParam(':enable_reviews', $enable_reviews, PDO::PARAM_INT);
        $result->bindParam(':enable_landing', $enable_landing, PDO::PARAM_INT);
        $result->bindParam(':enable_sale', $enable_sale, PDO::PARAM_INT);
        $result->bindParam(':enable_cabinet', $enable_cabinet, PDO::PARAM_INT);
        $result->bindParam(':enable_registration', $enable_registration, PDO::PARAM_INT);
        $result->bindParam(':multiple_authorizations', $multiple_authorizations, PDO::PARAM_INT);
        $result->bindParam(':user_sessions', $user_sessions, PDO::PARAM_STR);
        $result->bindParam(':dwl_in_lk', $dwl_in_lk, PDO::PARAM_INT);
        $result->bindParam(':enable_feedback', $enable_feedback, PDO::PARAM_INT);
        $result->bindParam(':write_feedback', $write_feedback, PDO::PARAM_INT);
        
        $result->bindParam(':use_smtp', $use_smtp, PDO::PARAM_INT);
        $result->bindParam(':smtp_host', $smtp_host, PDO::PARAM_STR);
        $result->bindParam(':smtp_port', $smtp_port, PDO::PARAM_INT);
        $result->bindParam(':smtp_user', $smtp_user, PDO::PARAM_STR);
        $result->bindParam(':smtp_pass', $smtp_pass, PDO::PARAM_STR);
        $result->bindParam(':smtp_ssl', $smtp_ssl, PDO::PARAM_STR);
        $result->bindParam(':return_path', $return_path, PDO::PARAM_STR);
        
        $result->bindParam(':sender_email', $sender_email, PDO::PARAM_STR);
        $result->bindParam(':sender_name', $sender_name, PDO::PARAM_STR);
        
        $result->bindParam(':smtp_domain', $smtp_domain, PDO::PARAM_STR);
        $result->bindParam(':smtp_selector', $smtp_selector, PDO::PARAM_STR);
        $result->bindParam(':smtp_private_key', $smtp_private_key, PDO::PARAM_STR);
        
        $result->bindParam(':max_upload', $max_upload, PDO::PARAM_INT);
        $result->bindParam(':show_order_note', $show_order_note, PDO::PARAM_INT);
        $result->bindParam(':email_protection', $email_protection, PDO::PARAM_INT);
        $result->bindParam(':countries_list', $countries_list, PDO::PARAM_STR);
        $result->bindParam(':session_time', $session_time, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':editor', $editor, PDO::PARAM_INT);

        $result->bindParam(':notify_admin_about_account_statement', $notify_admin_about_account_statement, PDO::PARAM_INT);
        $result->bindParam(':emails_for_account_statement_notifications', $emails_for_account_statement_notifications, PDO::PARAM_STR);

        $result->bindParam(':reCaptcha', $reCaptcha, PDO::PARAM_STR);

        $result->bindParam(':sms_service', $sms_service, PDO::PARAM_STR);
        $result->bindParam(':mobizon', $mobizon, PDO::PARAM_STR);
        $result->bindParam(':show_vk_page', $show_vk_page, PDO::PARAM_INT);

        return $result->execute();
    }
    
    
    
    public static function SaveVID(
        $main_page_content, $main_page_title, $main_page_desc,
        $main_page_keys, $main_page_tmpl, $main_page_text, $in_head, $in_body, $catalog_title,
        $catalog_h1, $catalog_desc, $catalog_keys, $reviews_tune, $politika_link, $oferta_link,
        $politika_text, $oferta_text, $external_url, $catalog_filter, $params,$oferta_text2
    ){
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'settings_main_page SET main_page_content = :main_page_content, main_page_title = :main_page_title,
                main_page_desc = :main_page_desc, main_page_keys = :main_page_keys, main_page_text = :main_page_text,
                main_page_tmpl = :main_page_tmpl, politika_link = :politika_link, politika_text = :politika_text,
                oferta_link = :oferta_link, oferta_text = :oferta_text, external_url = :external_url WHERE settings_main_page_id = 2';
        $result = $db->prepare($sql);
        $result->bindParam(':politika_link', $politika_link, PDO::PARAM_STR);
        $result->bindParam(':politika_text', $politika_text, PDO::PARAM_STR);
        $result->bindParam(':oferta_link', $oferta_link, PDO::PARAM_STR);
        $result->bindParam(':oferta_text', $oferta_text2, PDO::PARAM_STR);
        $result->bindParam(':external_url', $external_url, PDO::PARAM_STR);
        $result->bindParam(':main_page_title', $main_page_title, PDO::PARAM_STR);
        $result->bindParam(':main_page_desc', $main_page_desc, PDO::PARAM_STR);
        $result->bindParam(':main_page_keys', $main_page_keys, PDO::PARAM_STR);
        $result->bindParam(':main_page_tmpl', $main_page_tmpl, PDO::PARAM_INT);
        $result->bindParam(':main_page_content', $main_page_content, PDO::PARAM_INT);
        $result->bindParam(':main_page_text', $main_page_text, PDO::PARAM_STR);
        $result->execute();
        
        $db = Db::getConnection();  
        $sql = 'UPDATE '.PREFICS.'settings SET in_head = :in_head,
                in_body = :in_body, catalog_title = :catalog_title, catalog_desc = :catalog_desc, catalog_keys = :catalog_keys,
                catalog_h1 = :catalog_h1, reviews_tune = :reviews_tune, catalog_filter = :catalog_filter, params = :params WHERE setting_id = 1;
        
                UPDATE '.PREFICS.'settings_main_page SET main_page_content = :main_page_content, main_page_title = :main_page_title,
                main_page_desc = :main_page_desc, main_page_keys = :main_page_keys, main_page_text = :main_page_text,
                main_page_tmpl = :main_page_tmpl, politika_link = :politika_link, politika_text = :politika_text, 
                oferta_link = :oferta_link, oferta_text = :oferta_text, external_url = :external_url WHERE settings_main_page_id = 1';
        $result = $db->prepare($sql);


        $result->bindParam(':politika_link', $politika_link, PDO::PARAM_STR);
        $result->bindParam(':politika_text', $politika_text, PDO::PARAM_STR);
        $result->bindParam(':oferta_link', $oferta_link, PDO::PARAM_STR);
        $result->bindParam(':oferta_text', $oferta_text, PDO::PARAM_STR);
        $result->bindParam(':external_url', $external_url, PDO::PARAM_STR);
        
        $result->bindParam(':main_page_title', $main_page_title, PDO::PARAM_STR);
        $result->bindParam(':main_page_desc', $main_page_desc, PDO::PARAM_STR);
        $result->bindParam(':main_page_keys', $main_page_keys, PDO::PARAM_STR);
        $result->bindParam(':main_page_tmpl', $main_page_tmpl, PDO::PARAM_INT);
        $result->bindParam(':main_page_content', $main_page_content, PDO::PARAM_INT);
        $result->bindParam(':main_page_text', $main_page_text, PDO::PARAM_STR);

        $result->bindParam(':in_head', $in_head, PDO::PARAM_STR);
        $result->bindParam(':in_body', $in_body, PDO::PARAM_STR);
        
        $result->bindParam(':catalog_title', $catalog_title, PDO::PARAM_STR);
        $result->bindParam(':catalog_desc', $catalog_desc, PDO::PARAM_STR);
        $result->bindParam(':catalog_keys', $catalog_keys, PDO::PARAM_STR);
        $result->bindParam(':catalog_h1', $catalog_h1, PDO::PARAM_STR);
        $result->bindParam(':reviews_tune', $reviews_tune, PDO::PARAM_STR);
        $result->bindParam(':catalog_filter', $catalog_filter, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);

        return $result->execute();
    }

public static function GetByDate($id_partner = null, $date = null)
{
    $db = Db::getConnection();

    if ($id_partner !== null) {
        // Если указан partner_id
        $sql = 'SELECT * FROM '.PREFICS.'oferta 
                WHERE id_partner = :id_partner
                  AND   data = :date
                ORDER BY data ASC';

        $result = $db->prepare($sql);
        $result->bindParam(':id_partner', $id_partner, PDO::PARAM_INT);
        $result->bindParam(':date', $date, PDO::PARAM_STR);
    } else {
        // Если partner_id нет (NULL)
        $sql = 'SELECT * FROM '.PREFICS.'oferta 
                WHERE  id_partner IS NULL
                AND
                   data = :date
                ORDER BY data ASC';

        $result = $db->prepare($sql);
        $result->bindParam(':date', $date, PDO::PARAM_STR);
    }

    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

  public static function InsertOferta($text, $id_partner = null)
    {
        $db = Db::getConnection();

        if ($id_partner === null) {
            $sql = 'INSERT INTO '.PREFICS.'oferta (text, data) 
                    VALUES (:text, NOW())';
        } else {
            $sql = 'INSERT INTO '.PREFICS.'oferta (text, id_partner, data) 
                    VALUES (:text, :id_partner, NOW())';
        }

        $result = $db->prepare($sql);
        $result->bindParam(':text', $text, PDO::PARAM_STR);

        if ($id_partner !== null) {
            $result->bindParam(':id_partner', $id_partner, PDO::PARAM_INT);
        }

        return $result->execute();
    }

    public static function GetWithPartner()
    {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'oferta 
                WHERE id_partner IS NOT NULL 
                ORDER BY data DESC ';
        $result = $db->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetWithoutPartner()
    {
        $db = Db::getConnection();
        $sql = 'SELECT * FROM '.PREFICS.'oferta 
                WHERE id_partner IS NULL 
                ORDER BY data DESC ';
        $result = $db->query($sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * @param $client_letter_subj
     * @param $client_letter
     * @param $reg_confirm_letter
     * @param $register_letter
     * @param $pass_reset_letter
     * @param $remind_letter1
     * @param $remind_letter2
     * @param $remind_letter3
     * @param $remind_sms1
     * @param $remind_sms2
     * @param $reg_sms
     * @param $ticket
     * @return bool
     */
    public static function saveLetters($client_letter_subj, $client_letter, $reg_confirm_letter, $register_letter,
        $pass_reset_letter, $remind_letter1, $remind_letter2,  $remind_letter3, $remind_sms1, $remind_sms2, $reg_sms, $ticket)
    {
        $db = Db::getConnection();
        $sql = 'UPDATE '.PREFICS.'letters_settings SET client_letter_subj = :client_letter_subj, client_letter = :client_letter,
                reg_confirm_letter = :reg_confirm_letter, register_letter = :register_letter, pass_reset_letter = :pass_reset_letter,
                remind_letter1 = :remind_letter1, remind_letter2 = :remind_letter2, remind_letter3 = :remind_letter3,
                remind_sms1 = :remind_sms1, remind_sms2 = :remind_sms2, reg_sms = :reg_sms, org_data = :ticket WHERE setting_id = 1';

        $result = $db->prepare($sql);
        $result->bindParam(':client_letter_subj', $client_letter_subj, PDO::PARAM_STR);
        $result->bindParam(':client_letter', $client_letter, PDO::PARAM_STR);
        $result->bindParam(':remind_letter1', $remind_letter1, PDO::PARAM_STR);
        $result->bindParam(':remind_letter2', $remind_letter2, PDO::PARAM_STR);
        $result->bindParam(':remind_letter3', $remind_letter3, PDO::PARAM_STR);
        $result->bindParam(':reg_confirm_letter', $reg_confirm_letter, PDO::PARAM_STR);
        $result->bindParam(':register_letter', $register_letter, PDO::PARAM_STR);
        $result->bindParam(':pass_reset_letter', $pass_reset_letter, PDO::PARAM_STR);
        $result->bindParam(':remind_sms1', $remind_sms1, PDO::PARAM_STR);
        $result->bindParam(':remind_sms2', $remind_sms2, PDO::PARAM_STR);
        $result->bindParam(':reg_sms', $reg_sms, PDO::PARAM_STR);
        $result->bindParam(':ticket', $ticket, PDO::PARAM_STR);

        return $result->execute();
    }
    
    static $lang;

    // ПЕРЕВОД ЛОКАЛИЗАЦИИ
    public static function Lang($str)
    {
        $setting = System::getSetting();
        $params = json_decode($setting['params'], true);

        if (empty(self::$lang)) {
            if(isset($params['auto_lang']) && $params['auto_lang'] == 1){

                // Получили текущий язык
                $lang = isset($_SERVER [ 'HTTP_ACCEPT_LANGUAGE' ]) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'ru';

                switch($lang){
                    case 'en':
                    $lang = 'en';
                    break;

                    case 'uk':
                    $lang = 'ua';
                    break;

                    default:
                    $lang = 'ru';
                    break;
                }
            } else $lang = $setting['lang'];

                require(ROOT . '/lang/' . $lang . '_lang.php');

                if (file_exists(ROOT . '/lang/override_lang.php')) {
                    require(ROOT . '/lang/override_lang.php');
                    $lang = array_merge($lang, $override);
                }
                self::$lang = $lang;
        }
        
        // а тут можно подключить переопределения из БД
        return (!array_key_exists($str, self::$lang)) ? $str : self::$lang[$str];
    }
    
    
    
    // БЭКАП БД
    public static function createBackup()
    {
        require (ROOT.'/config/config.php');
        $file = 'db_'.date("Y_m_d__H_i_s", time()).$dbname.'.sql.gz';
        $arr1 = array();
        $go = exec('mysqldump --user='.$user.' --password="'.addslashes($password).'" --host='.$host.' --databases '.$dbname.' | gzip -c > '.ROOT."/tmp/".$file);
        return $file;

    }
    
    
    // ТРАНСЛИТ
    public static function Translit($name) {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' - ', ' ', '(', ')', '.', ',', '?', '', '_','[', ']',
                        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', ':', '+', '«', '»', '*', '%', '№', '#', '!', '?', '|', '@' ,'\'', '<', '>', '~');
        
        $lat = array('a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya', '-', '-', '', '', '-','', '', '', '-', '', '',
                        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        
        $name = str_replace($rus, $lat, $name);
        $name = mb_ereg_replace('[^-0-9a-z\.]', '-', $name);
        $name = mb_ereg_replace('[-]+', '-', $name);
        return $name;
    }


    /**
     * ПОИСК ДУБЛЕЙ АЛИАСОВ
     * @param $alias
     * @param $from
     * @param bool $update
     * @return bool
     */
    public static function searchDuplicateAlias($alias, $from, $update = false)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT COUNT(alias) FROM ".PREFICS."$from WHERE alias = '$alias' ");
        $count = $result->fetch();
        if ($update) {
            return $count[0] > 1 ? $count[0] : false;
        } else {
            return $count[0] > 0 ? $count[0] : false;
        }
    }


    /**
     * ПОИСК ДУБЛЕЙ АЛИАСОВ (УЛУЧШЕННЫЙ ПОИСК)
     * @param $alias
     * @param $from
     * @param null $id
     * @param string $field
     * @return mixed
     */
    public static function searchDuplicateAliases($alias, $from, $id = null, $field = '')
    {
        $db = Db::getConnection();
        $query = "SELECT COUNT(alias) FROM ".PREFICS."$from WHERE alias = '$alias'";
        $query .= $id ? " AND $field <> $id" : '';
        $result = $db->query($query);
        $count = $result->fetch();

        return $count[0];
    }


    /**
     * @return string
     */
    public static function CurrVersion()
    {
        // Сравнить версию
        if (self::isAvailblNewVrsn()) {
            return $result = CURR_VER . ' <span style="color:#FFCA10">(Новая '.$_SESSION['actual_ver'].')</span>';
        } else {
            return $result = CURR_VER;
        }
    }


    // ВЕРСИОННОСТЬ СКРИПТА
    public static function isAvailblNewVrsn()
    {
        return version_compare(CURR_VER, $_SESSION['actual_ver'], '<') ? true : false;
    }

    
    // РЕСАЙЗ КАРТИНОК 
    public static function imgResize($image, $w_o = false, $h_o = false)
    {
        if (($w_o < 0) || ($h_o < 0)) {
            echo "Некорректные входные параметры";
            return false;
        }

        list($w_i, $h_i, $type) = getimagesize($image); // Получаем размеры и тип изображения (число)

        $types = array("", "gif", "jpeg", "png"); // Массив с типами изображений
        $ext = $types[$type]; // Зная "числовой" тип изображения, узнаём название типа

        if ($ext) {
            $func = 'imagecreatefrom'.$ext; // Получаем название функции, соответствующую типу, для создания изображения
            $img_i = $func($image); // Создаём дескриптор для работы с исходным изображением
        } else {
            exit('Некорректное изображение'); // Выводим ошибку, если формат изображения недопустимый
        }

        /* Если указать только 1 параметр, то второй подстроится пропорционально */
        if (!$h_o) $h_o = $w_o / ($w_i / $h_i);
        if (!$w_o) $w_o = $h_o / ($h_i / $w_i);
        $img_o = imagecreatetruecolor($w_o, $h_o); // Создаём дескриптор для выходного изображения
        
        if ($ext == 'png' || $ext == 'gif') { /* Сохраняем прозрачность (альфа-канал) для png и gif */ 
            imagealphablending($img_o, false); 
            imagesavealpha($img_o, true);
        }
        imagecopyresampled($img_o, $img_i, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i); // Переносим изображение из исходного в выходное, масштабируя его
        $func = 'image'.$ext; // Получаем функция для сохранения результата
        $quality = in_array($ext, array('jpeg', 'bmp')) ? 100 : null;

        return $func($img_o, $image, $quality); // Сохраняем изображение в тот же файл, что и исходное, возвращая результат этой операции
    }


    // ПОЛУЧИТЬ ДАННЫЕ ЗАПИСИ CRON ЛОГА
    public static function getCronLog($jobs)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT jobs_cron, last_run, jobs_error FROM ".PREFICS."cron_logs WHERE jobs_cron = '$jobs'");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if (isset($data) && !empty($data)) return $data;
        else return false;
        
    }


    /**
     * ПРОВЕРИТЬ СУЩЕСТВУЕТ ЛИ СТОЛБЕЦ В ТАБЛИЦЕ
     * @param $table_name
     * @param $column_name
     * @return bool
     */
    public static function column_exists($table_name, $column_name) {
        require (ROOT.'/config/config.php');
        $db = Db::getConnection();
        $query = "SELECT COUNT(table_name) AS count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbname' 
                  AND TABLE_NAME = '$table_name' 
                  AND COLUMN_NAME = '$column_name'";
        $result = $db->query($query);
        $data = $result->fetch(PDO::FETCH_ASSOC);
        
        return $data['count'] ? true : false;
    }


    /**
     * ПРОВЕРИТЬ СУЩЕСТВУЕТ ЛИ INDEX В ТАБЛИЦЕ
     * @param $table_name
     * @param $column_name
     * @param $index_name
     * @return bool
     */
    public static function index_exists($table_name, $column_name, $index_name) {
        require (ROOT.'/config/config.php');
        $db = Db::getConnection();
        $query = "SELECT COUNT(table_name) AS count FROM INFORMATION_SCHEMA.statistics WHERE TABLE_SCHEMA = '$dbname' 
                  AND TABLE_NAME = '$table_name' 
                  AND COLUMN_NAME = '$column_name'
                  AND INDEX_NAME = '$index_name'";

        $result = $db->query($query);
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return $data['count'] ? true : false;
    }


    /**
     * ПОЛУЧИТЬ ID, КОТОРЫЙ БУДЕТ ИСПОЛЬЗОВАТЬ ПРИ ВСТАВКЕ В ТАБЛИЦУ
     * @param $table_name
     * @return bool
     */
    public static function getInsertId($table_name) {
        require (ROOT.'/config/config.php');
        $db = Db::getConnection();
        $query = "SELECT AUTO_INCREMENT FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = '$table_name'";
        $result = $db->query($query);
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return $data ? $data['AUTO_INCREMENT'] : false;
    }


    public static function file_get_contents_curl($url) {
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Устанавливаем параметр, чтобы curl возвращал данные, вместо того, чтобы выводить их в браузер.
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $data = curl_exec($ch);
        curl_close($ch);
    
        return $data;
    }


    /**
     * @param $url
     * @param array $data
     * @param int $time_out
     * @return array
     */
    public static function curl($url, $data = [], $time_out = 10) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_NOBODY,false);
        curl_setopt($ch,CURLOPT_HEADER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);

        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return array('content' => $output, 'info' => $info);
    }


    /**
     * ОТПРАВКА ДАННЫХ БЕЗ ОЖИДАНИЯ ОТВЕТА
     * @param $url
     * @param array $params
     */
    public static function curlAsync($url, $params = []) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_NOBODY,false);
        curl_setopt($ch,CURLOPT_HEADER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);

        curl_exec($ch);
        curl_close($ch);
    }


    /**
     * ПОЛУЧИТЬ ТЕЛЕФОНЫ С НЕПРАВИЛЬНЫМ КОДОМ СТРАНЫ
     * @return bool
     */
    public static function getWrongPhones()
    {
        $db = Db::getConnection();

        $query = "SELECT * FROM ".PREFICS."users WHERE LENGTH(phone) > 9 AND phone REGEXP '^(8|9)' LIMIT 100";

        //$result = $db->query("SELECT * FROM ".PREFICS."users WHERE phone = '+79176096411' ");
        $result = $db->query($query);

        $i = 0;
        while($row = $result->fetch()) {
            $data[$i]['user_id'] = $row['user_id'];
            $data[$i]['phone'] = $row['phone'];
            $data[$i]['user_name'] = $row['user_name'];
            $i++;
        }
        if (isset($data) && !empty($data)) return $data;
        else return false;
    }
    
    
    /**
     * ПОЛУЧИТЬ КОЛИЧЕСТВО ТЕЛЕФОНОВ С НЕПРАВИЛЬНЫМ КОДОМ СТРАНЫ
     * @return mixed
     */
    public static function getWrongPhonesCount()
    {
        $db = Db::getConnection();

        $query = "SELECT COUNT(*) FROM ".PREFICS."users WHERE LENGTH(phone) = 10 AND phone REGEXP '^9';";
        $result = $db->query($query);
        $count = $result->fetch();
        return $count[0];
    }
    
    
    /**
     * ОТФОРМАТИРОВАТЬ КОД СТРАНЫ ТЕЛЕФОНА
     * @return bool
     */
    public static function correctionWrongPhones() {
        $db = Db::getConnection();

        $sql = "UPDATE ".PREFICS."users SET phone = CONCAT('+7', `phone`) WHERE LENGTH(phone) = 10 AND phone REGEXP '^9';";
        $result = $db->query($sql);

        //$sql = "UPDATE ".PREFICS."users SET phone = STUFF(phone,1,1,'+7') WHERE LENGTH(phone) = 11 AND phone REGEXP '^8';";
        //$result = $db->query($sql);

        return $result->execute();
    }


    /**
     * УДАЛИТЬ ДУБЛИКАТЫ ПРОХОЖДЕНИЯ УРОКОВ
     */
    public static function deleteDoubleLessonMap() {
        $db = Db::getConnection();


        $query = "select count(*) from ".PREFICS."course_lesson_map" ;
        $result = $db->query($query);
        $count = $result->fetch();
        echo 'Всего записей: ' .$count[0] . '<br>';

        $query = "select count(*) from ".PREFICS."course_lesson_map where id not in (
                    select max(id) from ".PREFICS."course_lesson_map group by user_id, lesson_id HAVING count(id) > 1)
                 and id not in
                    (select max(id) from ".PREFICS."course_lesson_map group by  user_id, lesson_id  HAVING count(id) = 1)
                " ;
        $result = $db->query($query);
        $count = $result->fetch();
        echo 'Дубл. записей: ' .$count[0] . '<br>';


        $query = "delete from ".PREFICS."course_lesson_map where id not in (SELECT * FROM (
                        select max(id) from ".PREFICS."course_lesson_map group by user_id, lesson_id HAVING count(id) > 1) as t1)
                     and id not in (SELECT * FROM(
                        select max(id) from ".PREFICS."course_lesson_map group by  user_id, lesson_id  HAVING count(id) = 1) as t2)
                    ";
        $result = $db->query($query);

        $query = "SELECT COUNT(*) FROM ".PREFICS."course_lesson_map" ;
        $result = $db->query($query);
        $count = $result->fetch();
        echo 'Записей после удаления:' .$count[0];exit;
    }
    

    /**
     * В НИЖНИЙ РЕГИСТР ПЕРВЫЙ СИМВОЛ (ДЛЯ КИРИЛЛИЦЫ)
     * 
     * @param  string $str
     * @return string
     */
    public static function mb_lcfirst($str) {
        return mb_strtolower(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }
    
    
    /**
     * ДОБАВИТЬ ОКОНЧАНИЕ ТЕКСТА В ЗАВИСИМОСТИ ОТ КОЛИЧЕСТВА
     * @param $quantity
     * @param $text
     * @return mixed
     */
    public static function addTermination($quantity, $text)
    {
        // Получили текущий язык
        $lg = self::getMainSetting();
        
        if ($lg['lang'] == 'ru') { // тут русские окончания 
            if(strpos($text, "ль[TRMNT]") !== false){
                $text = str_replace('ль[TRMNT]', 'л[TRMNT]', $text);

                if (preg_match('/(5|6|7|8|9|0|1[0-9])$/', $quantity)) 
                    $term = 'ей';
                elseif (preg_match('/1$/', $quantity)) 
                    $term = 'ь';
                elseif (preg_match('/(2|3|4)$/', $quantity)) 
                    $term = 'я';
                
            }else{
                if (preg_match('/(5|6|7|8|9|0|1[0-9])$/', $quantity)) 
                    $term = 'ов';

                elseif (preg_match('/1$/', $quantity)) 
                    $term = '';

                elseif (preg_match('/(2|3|4)$/', $quantity))
                    $term = 'а';
            }
        } elseif ($lg['lang'] == 'en') { // в английском все просто больше одного на конце s добавляем и всё
            if (intval($quantity)>1) {
                $term = 's';
            } else {
                $term = ''; 
            }
        }

        return str_replace('[TRMNT]', $term, $text);
    }


    /**
     * @param $quantity
     * @param $text
     * @return mixed
     */
    public static function addTermination2($quantity, $text)
    {
        // Получили текущий язык
        $lg = self::getMainSetting();

        if ($lg['lang'] == 'ru') { // тут русские окончания
            if (preg_match('/(5|6|7|8|9|0|1[0-9])$/', $quantity)) {
                $term = 'ых';
            } elseif (preg_match('/1$/', $quantity)) {
                $term = 'ый';
            } elseif (preg_match('/(2|3|4)$/', $quantity)) {
                $term = 'ого';
            }
        } elseif ($lg['lang'] == 'en') { // в английском все просто больше одного на конце s добавляем и всё
            if (intval($quantity)>1) {
                $term = 's';
            } else {
                $term = '';
            }
        }

        return str_replace('[TRMNT]', $term, $text);
    }


    /**
     * @param $quantity
     * @param $text
     * @return mixed
     */
    public static function addTermination3($quantity, $text) {
        if (preg_match('/(2|3|4|5|6|7|8|9|0|1[0-9])$/', $quantity)) {
            $term = 'ые';
        } else {
            $term = 'ую';
        }

        return str_replace('[TRMNT]', $term, $text);
    }


    /**
     * @param $quantity
     * @return mixed|string
     */
    public static function getTextNumber($quantity) {
        if ($quantity > 99) {
            return $quantity;
        }

        $digits = [
            'нулевого', 'первого', 'второго', 'третьего', 'четвертого', 'пятого',
            'шестого', 'седьмого', 'восьмого', 'девятого', 'десятого',
        ];

        $numbers = [
            11 => 'одиннадцатого',
            12 => 'двенадцатого',
            13 => 'тринадцатого',
            14 => 'четырнадцатого',
            15 => 'пятнадцатого',
            16 => 'шестнадцатого',
            17 => 'семнадцатого',
            18 => 'восемнадцатого',
            19 => 'девятнадцатого',
            20 => 'двадцатого',
            30 => 'тридцатого',
            40 => 'сорокового',
            50 => 'пятидесятого',
            60 => 'шестидесятого',
            70 => 'семидесятого',
            80 => 'восьмидесятого',
            90 => 'девяностого'
        ];

        $discharges = [
            2 => 'двадцать',
            3 => 'тридцать',
            4 => 'сорок',
            5 => 'пятьдесят',
            6 => 'шестьдесят',
            7 => 'семьдесят',
            8 => 'восемьдесят',
            9 => 'девяносто'
        ];

        if ($quantity <= 10) {
            return $digits[$quantity];
        }

        $digit = $quantity % 10; // цифра
        $discharge = (int)($quantity / 10); // разряд

        if ($quantity <= 20 || $digit == 0) {
            return $numbers[$quantity];
        }

        if ($quantity < 100) {
            return "{$discharges[$discharge]} {$digits[$digit]}";
        }
    }


    /**
     * @param $quantity
     * @param $text
     * @return mixed
     */
    public static function addTermination4($quantity, $text) {
        if (preg_match('/(2|3|4|1[0-9])$/', $quantity)) {
            $term = 'ы';
        } elseif(preg_match('/(5|6|7|8[0-9])$/', $quantity)) {
            $term = '';
        } else {
            $term = 'у';
        }

        return str_replace('[TRMNT]', $term, $text);
    }


    /**
     * @param $week_day
     * @param $text
     * @return mixed
     */
    public static function addTerminationByWeekDay($week_day, $text) {
        if (preg_match('/(1|2|4)$/', $week_day)) {
            $term = 'ый';
        } elseif (preg_match('/(3|5|6)$/', $week_day)) {
            $term = 'ую';
        } else {
            $term = 'ое';
        }

        return str_replace('[TRMNT]', $term, $text);
    }

    /**
     * СОЗДАТЬ ПАРОЛЬ
     * @param int $max
     * @return array
     */
    public static function createPass($max = 8)
    {

        $password = self::generateStr($max);
        $hash = password_hash($password, PASSWORD_DEFAULT);

        return array(
            'pass' => $password,
            'hash' => $hash
        );
    }


    /**
     * СГЕНЕРИРОВАТЬ СЛУЧАНЫЙ ТЕКСТ
     * @param $max
     * @return string
     */
    public static function generateStr($max)
    {
        $chars="abcdefghigkmnopqrstuvwxyzABCDEFGHGKLMNPQRSTUVWXYZ123456789";
        $size = strlen($chars)-1;
        $str = '';

        while($max--) {
            $str .= $chars[mt_rand(0, $size)];
        }

        return $str;
    }


    /**
     * СГЕНЕРИРОВАТЬ СЛУЧАНЫЙ НАБОР ЦИФР
     * @param $max
     * @return string
     */
    public static function generateNums($max)
    {
        $chars="1234567890";
        $size = strlen($chars)-1;
        $str = '';

        while($max--) {
            $str .= $chars[mt_rand(0, $size)];
        }

        return $str;
    }


    /**
     * ПОЛУЧИТЬ ТЕСТ, БЕЗОПАСНЫЙ ДЛЯ СОХРАНЕНИЯ
     * @param $str
     * @param bool $to_lower
     * @return string
     */
    public static function getSecureString($str, $to_lower = false) {
        $str = strip_tags($str);
        $str = str_replace(['&lt','&gt','&amp', '&quot'], '-', $str);
        $str = trim(str_replace(['/', '\\', ':', '"', '!', '*', '?', '<', '>', ';'], '', $str));
     
        return $to_lower ? strtolower($str) : $str;
    }


    /**
     * ЗАПИСАТЬ ОШИБКУ В ЛОГ
     * @param $error_msg
     */
    public static function writeError($error_msg, $filename) {
        $error = date('d.m.Y H:i:s', time()) . " Error: $error_msg";
        $dirpath = ROOT . '/log';
        if (!file_exists($dirpath)) {
            mkdir($dirpath);
        }

        file_put_contents("{$dirpath}/{$filename}.txt", PHP_EOL . $error, FILE_APPEND);
    }


    /**
     * СДЕЛАТЬ РЕДИРЕКТ НА СТРАНИЦУ
     * @param $url
     * @param null $status
     * @param null $anchor
     */
    public static function redirectUrl($url, $status = null, $anchor = null) {
        $query = '';
        if ($status !== null) {
            $query = strpos($url, '?') === false ? '?' : '&';
            $query .= $status ? 'success' : 'fail';
        }

        header("Location: {$url}{$query}{$anchor}");
        exit;
    }


    /**
     * НАЗНАЧИТЬ УВЕДОМЛЕНИЕ
     * @param string $type
     * @param string $message
     * @param int $time
     * @return bool
     */
    public static function setNotif($type = "message", $message = "", $time = 5) {
        if($type === true || $type == 'save' || $type == 'delete'){ /*#todo доделать под разные типы*/
            $type = "message";
            if($message == "")
                $message = "Сохранено!";

        }elseif($type === false || $type == 'error' || $type == 'system_error' || $type == 'perm_error'){
            $type = "warning";
            if($message == "")
                $message = "Ошибка при сохранении!";
        }
        
        $type = htmlspecialchars($type);
        $message = htmlspecialchars($message);

        $cookie_name = "notif-{$type}-{$time}-" . mt_rand(10, 99);

        $_COOKIE[$cookie_name] = $message;
        
        @ setcookie($cookie_name, $message, time() + 3, '/admin');

        if(headers_sent()) /*если был отправлен заголовок*/
            print("<script type=\"text/javascript\">new Notification().addMessage(\"{$type}\", \"{$message}\", {$time});</script>");
    }

    /**
     * ЗАГРУЗКА ФАЙЛА НА СЕРВЕР ПОСРЕДСТВОМ POST FILE
     * 
     * @param  string $load_name
     * @param  string $folder
     * @param  ? string $double_prfx
     * @param  ? string $save_name
     * @return null|string
     */
    public static function uploadPostFile(
        string $load_name,   string $folder,
        $double_prfx = "1",  string $save_name = ""
    ){
        if (!isset($_FILES[$load_name]))
            return null;

        $tmp_name = $_FILES[$load_name]["tmp_name"]; // Временное имя на сервере
        $file = empty($save_name) 
            ? $_FILES[$load_name]["name"] 
            : $save_name; // Имя при загрузке

        if(!is_uploaded_file($tmp_name))
            return null;

        $folder = str_replace('\\', '/', $folder);

        if(strpos($folder, ROOT) === false) //!full_dir
            $folder = ROOT . $folder;

        if(!is_dir($folder) && !mkdir($folder, 0777))
            return null;

        if(substr($folder, -1) != '/')
            $folder .= '/';

        $path = $folder . $file;

        $pathinfo = pathinfo($path);

        while($double_prfx){
            if(!file_exists($path))
                break;
            if(is_numeric($double_prfx))
                $file = "{$pathinfo['filename']}({$double_prfx}).{$pathinfo['extension']}";

            else
                $file = "{$pathinfo['filename']}-{$double_prfx}.{$pathinfo['extension']}";

            $path = $folder . $file;

            if(is_numeric($double_prfx))
                $double_prfx++;

            else{
                $pathinfo['filename'] .= '-' . $double_prfx;
                $double_prfx = 1;
            }
        }
    
        if(move_uploaded_file($tmp_name, $path))
            return $path;
        
        return null;        
    }

    /**
     * ПОЛУЧИТЬ СПИСОК СТРАН ДЛЯ МАСКИ ТЕЛЕФОНА
     * @param null $code
     * @return array|mixed
     */
    public static function getCountriesToPhone($code = null) {
        $countries = [
            'ru' => 'Russia (Россия)',
            'ua' => 'Ukraine (Україна)',
            'by' => 'Belarus (Беларусь)',
            'kz' => 'Kazakhstan (Казахстан)',
            'az' => 'Azerbaijan (Azərbaycan)',
            'at' => 'Austria (Österreich)',
            'am' => 'Armenia (Հայաստան)',
            'bg' => 'Bulgaria (България)',
            'gb' => 'United Kingdom',
            'hu' => 'Hungary (Magyarország)',
            'de' => 'Germany (Deutschland)',
            'ge' => 'Georgia (საქართველო)',
            'il' => 'Israel (‫ישראל‬‎)',
            'es' => 'Spain (España)',
            'it' => 'Italy (Italia)',
            'kg' => 'Kyrgyzstan (Кыргызстан)',
            'lv' => 'Latvia (Latvija)',
            'lt' => 'Lithuania (Lietuva)',
            'ee' => 'Estonia (Eesti)',
            'pl' => 'Poland (Polska)',
            'ro' => 'Romania (România)',
            'sk' => 'Slovakia (Slovensko)',
            'us' => 'United States',
            'tj' => 'Tajikistan',
            'tm' => 'Turkmenistan',
            'uz' => 'Uzbekistan (Oʻzbekiston)',
            'fr' => 'France',
            'cz' => 'Czech Republic (Česká republika)',
            'ch' => 'Switzerland (Schweiz)',
            'tr' => 'Turkey (Türkiye)',
            'fi' => 'Finland (Suomi)',
            'af' => 'Afghanistan (‫افغانستان‬‎)',
            'al' => 'Albania (Shqipëri)',
            'dz' => 'Algeria (‫الجزائر‬‎)',
            'as' => 'American Samoa',
            'ad' => 'Andorra',
            'ao' => 'Angola',
            'ai' => 'Anguilla',
            'ag' => 'Antigua and Barbuda',
            'ar' => 'Argentina',
            'aw' => 'Aruba',
            'au' => 'Australia',
            'bs' => 'Bahamas',
            'bh' => 'Bahrain (‫البحرين‬‎)',
            'bd' => 'Bangladesh (Afghanistan)',
            'bb' => 'Barbados',
            'be' => 'Belgium (België)',
            'bz' => 'Belize',
            'bj' => 'Benin (Bénin)',
            'bm' => 'Bermuda',
            'bt' => 'Bhutan (འབྲུག)',
            'bo' => 'Bolivia',
            'ba' => 'Bosnia and Herzegovina (Босна и Херцеговина)',
            'bw' => 'Botswana',
            'br' => 'Brazil (Brasil)',
            'io' => 'British Indian Ocean Territory',
            'vg' => 'British Virgin Islands',
            'bn' => 'Brunei',
            'bf' => 'Burkina Faso',
            'bi' => 'Burundi (Uburundi)',
            'kh' => 'Cambodia (កម្ពុជា)',
            'cm' => 'Cameroon (Cameroun)',
            'ca' => 'Canada',
            'cv' => 'Cape Verde (Kabu Verdi)',
            'bq' => 'Caribbean Netherlands',
            'ky' => 'Cayman Islands',
            'cf' => 'Central African Republic (République centrafricaine)',
            'td' => 'Chad (Tchad)',
            'cl' => 'Chile',
            'cn' => 'China (中国)',
            'cx' => 'Christmas Island',
            'cc' => 'Cocos (Keeling) Islands',
            'co' => 'Colombia',
            'km' => 'Comoros (‫جزر القمر‬‎)',
            'cd' => 'Congo (DRC) (Jamhuri ya Kidemokrasia ya Kongo)',
            'cg' => 'Congo (Republic) (Congo-Brazzaville)',
            'ck' => 'Cook Islands',
            'cr' => 'Costa Rica',
            'ci' => 'Côte d’Ivoire',
            'hr' => 'Croatia (Hrvatska)',
            'cu' => 'Cuba',
            'cw' => 'Curaçao',
            'cy' => 'Cyprus (Κύπρος)',
            'dk' => 'Denmark (Danmark)',
            'dj' => 'Djibouti',
            'dm' => 'Dominica',
            'do' => 'Dominican Republic (República Dominicana)',
            'ec' => 'Ecuador',
            'eg' => 'Egypt (‫مصر‬‎)',
            'sv' => 'El Salvador',
            'gq' => 'Equatorial Guinea (Guinea Ecuatorial)',
            'er' => 'Eritrea',
            'et' => 'Ethiopia',
            'fk' => 'Falkland Islands (Islas Malvinas)',
            'fo' => 'Faroe Islands (Føroyar)',
            'fj' => 'Fiji',
            'gf' => 'French Guiana (Guyane française)',
            'pf' => 'French Polynesia (Polynésie française)',
            'ga' => 'Gabon',
            'gm' => 'Gambia',
            'gh' => 'Ghana (Gaana)',
            'gi' => 'Gibraltar',
            'gr' => 'Greece (Ελλάδα)',
            'gl' => 'Greenland (Kalaallit Nunaat)',
            'gd' => 'Grenada',
            'gp' => 'Guadeloupe',
            'gu' => 'Guam',
            'gt' => 'Guatemala',
            'gg' => 'Guernsey',
            'gn' => 'Guinea (Guinée)',
            'gw' => 'Guinea-Bissau (Guiné Bissau)',
            'gy' => 'Guyana',
            'ht' => 'Haiti',
            'hn' => 'Honduras',
            'hk' => 'Hong Kong (香港)',
            'is' => 'Iceland (Ísland)',
            'in' => 'India (भारत)',
            'id' => 'Indonesia',
            'ir' => 'Iran (‫ایران‬‎)',
            'iq' => 'Iraq (‫العراق‬‎)',
            'ie' => 'Ireland',
            'im' => 'Isle of Man',
            'jm' => 'Jamaica',
            'jp' => 'Japan (日本)',
            'je' => 'Jersey',
            'jo' => 'Jordan (‫الأردن‬‎)',
            'ke' => 'Kenya',
            'ki' => 'Kiribati',
            'xk' => 'Kosovo',
            'kw' => 'Kuwait (‫الكويت‬‎)',
            'la' => 'Laos (ລາວ)',
            'lb' => 'Lebanon (‫لبنان‬‎)',
            'ls' => 'Lesotho',
            'lr' => 'Liberia',
            'ly' => 'Libya (‫ليبيا‬‎)',
            'li' => 'Liechtenstein',
            'lu' => 'Luxembourg',
            'mo' => 'Macau (澳門)',
            'mk' => 'Macedonia (FYROM) (Македонија)',
            'mg' => 'Madagascar (Madagasikara)',
            'mw' => 'Malawi',
            'my' => 'Malaysia',
            'mv' => 'Maldives',
            'ml' => 'Mali',
            'mt' => 'Malta',
            'mh' => 'Marshall Islands',
            'mq' => 'Martinique',
            'mr' => 'Mauritania (‫موريتانيا‬‎)',
            'mu' => 'Mauritius (Moris)',
            'yt' => 'Mayotte',
            'mx' => 'Mexico (México)',
            'fm' => 'Micronesia',
            'md' => 'Moldova (Republica Moldova)',
            'mc' => 'Monaco',
            'mn' => 'Mongolia (Монгол)',
            'me' => 'Montenegro (Crna Gora)',
            'ms' => 'Montserrat',
            'ma' => 'Morocco (‫المغرب‬‎)',
            'mz' => 'Mozambique (Moçambique)',
            'mm' => 'Myanmar (Burma) (မြန်မာ)',
            'na' => 'Namibia (Namibië)',
            'nr' => 'Nauru',
            'np' => 'Nepal (नेपाल)',
            'nl' => 'Netherlands (Nederland)',
            'nc' => 'New Caledonia (Nouvelle-Calédonie)',
            'nz' => 'New Zealand',
            'ni' => 'Nicaragua',
            'ne' => 'Niger (Nijar)',
            'ng' => 'Nigeria',
            'nu' => 'Niue',
            'nf' => 'Norfolk Island',
            'kp' => 'North Korea (조선 민주주의 인민 공화국)',
            'mp' => 'Northern Mariana Islands',
            'no' => 'Norway (Norge)',
            'om' => 'Oman (‫عُمان‬‎)',
            'pk' => 'Pakistan (‫پاکستان‬‎)',
            'pw' => 'Palau',
            'ps' => 'Palestine (‫فلسطين‬‎)',
            'pa' => 'Panama (Panamá)',
            'pg' => 'Papua New Guinea',
            'py' => 'Paraguay',
            'pe' => 'Peru (Perú)',
            'ph' => 'Philippines',
            'pt' => 'Portugal',
            'pr' => 'Puerto Rico',
            'qa' => 'Qatar (‫قطر‬‎)',
            're' => 'Réunion (La Réunion)',
            'rw' => 'Rwanda',
            'bl' => 'Saint Barthélemy',
            'sh' => 'Saint Helena',
            'kn' => 'Saint Kitts and Nevis',
            'lc' => 'Saint Lucia',
            'mf' => 'Saint Martin (Saint-Martin (partie française))',
            'pm' => 'Saint Pierre and Miquelon (Saint-Pierre-et-Miquelon)',
            'vc' => 'Saint Vincent and the Grenadines',
            'ws' => 'Samoa',
            'sm' => 'San Marino',
            'st' => 'São Tomé and Príncipe (São Tomé e Príncipe)',
            'sa' => 'Saudi Arabia (‫المملكة العربية السعودية‬‎)',
            'sn' => 'Senegal (Sénégal)',
            'rs' => 'Serbia (Србија)',
            'sc' => 'Seychelles',
            'sl' => 'Sierra Leone',
            'sg' => 'Singapore',
            'sx' => 'Sint Maarten',
            'si' => 'Slovenia (Slovenija)',
            'sb' => 'Solomon Islands',
            'so' => 'Somalia (Soomaaliya)',
            'za' => 'South Africa',
            'kr' => 'South Korea (대한민국)',
            'ss' => 'South Sudan (‫جنوب السودان‬‎)',
            'lk' => 'Sri Lanka (ශ්‍රී ලංකාව)',
            'sd' => 'Sudan (‫السودان‬‎)',
            'sr' => 'Suriname',
            'sj' => 'Svalbard and Jan Mayen',
            'sz' => 'Swaziland',
            'se' => 'Sweden (Sverige)',
            'sy' => 'Syria (‫سوريا‬‎)',
            'tw' => 'Taiwan (台灣)',
            'tz' => 'Tanzania',
            'th' => 'Thailand (ไทย)',
            'tl' => 'Timor-Leste',
            'tg' => 'Togo',
            'tk' => 'Tokelau',
            'to' => 'Tonga',
            'tt' => 'Trinidad and Tobago',
            'tn' => 'Tunisia (‫تونس‬‎)',
            'tc' => 'Turks and Caicos Islands',
            'tv' => 'Tuvalu',
            'vi' => 'U.S. Virgin Islands',
            'ug' => 'Uganda',
            'ae' => 'United Arab Emirates (‫الإمارات العربية المتحدة‬‎)',
            'uy' => 'Uruguay',
            'vu' => 'Vanuatu',
            'va' => 'Vatican City (Città del Vaticano)',
            've' => 'Venezuela',
            'vn' => 'Vietnam (Việt Nam)',
            'wf' => 'Wallis and Futuna (Wallis-et-Futuna)',
            'eh' => 'Western Sahara (‫الصحراء الغربية‬‎)',
            'ye' => 'Yemen (‫اليمن‬‎)',
            'zm' => 'Zambia',
            'zw' => 'Zimbabwe',
            'ax' => 'Åland Islands',
        ];
        
        return $code && isset($countries[$code]) ? $countries[$code] : $countries;
    }


    /**
     * ПРОВЕРИТЬ ТЕКСТ НА КОДИРОВКУ BASE64
     * @param $text
     * @return bool
     */
    public static function isBase64($text) {
        return base64_encode(base64_decode($text, true)) === $text ? true : false;
    }


    /**
     * ПОЛУЧИТЬ UTM МЕТКИ
     * @param $request
     * @return string
     */
    public static function getUtm($request = null) {
        $utm = '';
        if ($request !== null) {
            $utm_keys = self::getUtmKeys();
            foreach ($utm_keys as $utm_key) {
                if (isset($request[$utm_key])) {
                    $utm .= (!$utm ? '?' : '&') . "{$utm_key}=" . htmlentities($request[$utm_key]);
                }
            }
        } elseif(isset($_SESSION['current_utm'])) {
            $utm = $_SESSION['current_utm'];
        }

        return $utm ? $utm : null;
    }


    /**
     * ПОЛУЧИТЬ КЛЮЧИ UTM-МЕТОК
     * @return array
     */
    public static function getUtmKeys() {
        return ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'utm_referrer'];
    }


    /**
     * ПОЛУЧИТЬ МАССИВ С UTM МЕТКАМИ
     * @param $argv_utm
     * @return array|bool
     */
    public static function getUtmData($argv_utm = null) {
        $utm = false;
        $argv_utm = $argv_utm === null && isset($_SESSION['current_utm']) ? $_SESSION['current_utm'] : $argv_utm;
        if ($argv_utm) {
            parse_str(str_replace('?utm_', 'utm_', $argv_utm), $utm);
        }

        return $utm;
    }

    /**
     * ОБРАБОТАТЬ И ПОЛУЧИТЬ МАССИВ ДЛЯ ИСПОЛЬЗОВАНИЯ В ЗАПРОСАХ BD
     * @param $data
     * @param string $type_values
     * @return mixed
     */
    public static function getSecureData($data, $type_values = 'int') {
        if (is_array($data)) {
            $safe_date = [];
            foreach ($data as $key => $value) {
                $value = $type_values == 'int' ? (int)$value : htmlentities($value);
                if ($value) {
                    $safe_date[$key] = $value;
                }
            }
        } else {
            $safe_date = $type_values == 'int' ? (int)$data : htmlentities($data);
        }

        return $safe_date;
    }


    /**
     * ОТДАТЬ ФАЙЛ СРЕДСТВАМИ PHP
     * @param $path
     * @param $file_name
     */
    public static function fileForceDownload($path, $file_name) {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$file_name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));

        readfile($path);
        exit;
    }


    /**
     * ПОЛУЧИТЬ ДАТУ ВИДА 1 февраля 2021 г. в 11:11
     * @param $timestamp
     * @param bool $time
     * @return mixed|string
     */
    public static function dateSpeller($timestamp, $time = false){
        
        $setting = System::getSetting();
        if($setting['lang'] == 'ru'){
            $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        } else {
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November','December'];
        }
        
        return is_numeric($timestamp) ? str_replace(
            ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov','Dec'],
            $months,
            date('j M Y' . ($time ? ' в H:i' : ''), $timestamp)
        ) : '';
    }


    /**
     * ПОЛУЧИТЬ СЛЕДУЮЩУЮ ДАТУ ЧЕРЕЗ МЕСЯЦ
     * @param $start_date
     * @return false|int
     */
    public static function getNextDateInMonth($start_date) {
        $day = (int)date('d', $start_date);
        $month = (int)date('m', $start_date);
        $year = (int)date('Y', $start_date);

        $year = $month < 12 ? $year : $year + 1;
        $next_month = $month < 12 ? ++$month : 1;
        $next_month_last_day = date('t', mktime(0, 0, 0, $next_month, 1, $year));
        $day = $day > $next_month_last_day ? $next_month_last_day : $day;

        $day = $day < 10 ? "0{$day}" : $day;
        $next_month = $next_month < 10 ? "0{$next_month}" : $next_month;
        $next_date = strtotime("$day-$next_month-$year");

        return $next_date;
    }


    /**
     * ПОЛУЧИТЬ ID ПАРТЕРА
     * @param $email
     * @param $cookie
     * @return bool|int|null
     */
    public static function getPartnerId($email, $cookie = null) {
        if ($cookie === null) {
            $setting = System::getSetting();
            $cookie = $setting['cookie'];
        }
        
        $partnership = System::CheckExtensension('partnership', 1);
        if(!$partnership) return false;
        
        $p_id = false;

        // Если в запросе указан партнёр

        if (isset($_REQUEST['pid'])) {
            $p_id = intval($_REQUEST['pid']);
            
        } else {
            // Если в запросе партнёр не указан, то проверяем закрепление партнёра за юзером и куки
            $user = User::getUserDataByEmail($email);
            $from_id = false;

            if ($user && !empty($user['from_id'])) {
                $aff_set = unserialize(self::getExtensionSetting('partnership'));
                $aff_life = intval($aff_set['params']['aff_life']) * 86400;
                $period = time() - $user['reg_date'];

                if ($period < $aff_life) {
                    $from_id = intval($user['from_id']);
                }
            }
            
            if(isset($_COOKIE["aff_$cookie"])){
                $p_id = intval($_COOKIE["aff_$cookie"]); // Если есть партнёрские куки
                
            } elseif(isset($_SESSION["aff_$cookie"])){
                $p_id = intval($_SESSION["aff_$cookie"]); // Если есть обычная партнёрская сессия
                
            } elseif(isset($_SESSION["real_aff_$cookie"])){
                $p_id = intval($_SESSION["real_aff_$cookie"]); // Если есть партнёрская спец.сессия
                
            } elseif($from_id){
                $p_id = $from_id; // Если клиент закреплен за партнёром
                
            }

            if ($p_id) {
                $verify = Aff::PartnerVerify($p_id);
                if ($verify && $verify['email'] != $email) {
                    return $p_id;
                }
            }
        }

        return $p_id;
    }


    /**
     * ПОЛУЧИТЬ IP КЛИЕНТА
     * @return mixed|string|null
     */
    public static function getUserIp() {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = @$_SERVER['REMOTE_ADDR'];
        $ip = null;

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = htmlentities($remote);
        }

        return $ip;
    }


    /**
     * ПОЛУЧИТЬ УРЛ ДЛЯ ЮТУБОВСКОГО ИФРЕЙМА ИЗ УРЛА
     * @param $url
     * @return string
     */
    public static function getYoutubeUrl2Iframe($url) {
        $data = parse_url($url);

        if (strpos($url, 'https://www.youtube.com/embed') === false && isset($data['path']) && $data['path']) {
            $path = $data['path'];
            $_url = "https://www.youtube.com/embed";

            if ($path == '/watch' && isset($data['query'])) {
                $_url .= '/' . str_replace('v=', '', $data['query']);
            } elseif($path != '/watch') {
                $_url .= $path;
            }

            $url = $_url;
        }

        return $url;
    }

    /**
     * ПОЛУЧИТЬ УРЛ ДЛЯ RUTUBE ИФРЕЙМА ИЗ УРЛА
     * @param $url
     * @return string
     */
    public static function getRutubeUrl2Iframe($url) {
        $data = parse_url($url);
        
        if (strpos($url, 'rutube.ru/play/embed') !== false) {
            return $url;
        }
        
        if (strpos($url, 'rutube.ru/video/private')) {
            $path = $data['path'];
            if (strpos($path, '/') == 0) {
              $id_video = explode('/',$path)[3];  
            }    
        } else {
            $path = $data['path'];
            if (strpos($path, '/') == 0) {
              $id_video = explode('/',$path)[2];  
            }    
        }
        
        $query = isset($data['query']) ? '?' . $data['query'] : '';
        $url = "https://rutube.ru/play/embed/".$id_video.$query;

        return $url;
    }


    /**
     * СОХРАНИТЬ НАСТРОЙКИ РАСШИРЕНИЯ
     * @param $ext
     * @param $params
     * @param null $status
     * @return bool
     */
    public static function SaveExtensionSetting($ext, $params, $status = null) {
        $db = Db::getConnection();
        $sql = "UPDATE ".PREFICS."extensions SET params = :params".($status !== null ? ', enable = :enable' : '')." WHERE name = '$ext'";
        $result = $db->prepare($sql);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        if ($status !== null) {
            $result->bindParam(':enable', $status, PDO::PARAM_INT);
        }

        return $result->execute();
    }


    /**
     * ПОЛУЧИТЬ СТАТУС РАСШИРЕНИЯ
     * @param $ext
     * @return bool
     */
    public static function getExtensionStatus($ext)
    {
        $db = Db::getConnection();
        $result = $db->query(" SELECT enable FROM ".PREFICS."extensions WHERE name = '$ext'");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return $data ? $data['enable'] : false;
    }


    /**
     * ПОЛУЧИТЬ НАСТРОЙКИ РАСШИРЕНИЯ
     * @param $ext
     * @return bool
     */
    public static function getExtensionSetting($ext)
    {
        $db = Db::getConnection();
        $result = $db->query("SELECT params FROM ".PREFICS."extensions WHERE name = '$ext'");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return  $data ? $data['params'] : false;
    }
    
    /**
     * СКРЫТЬ ЧАСТЬ EMAIL ПОЛЬЗОВАТЕЛЯ 
     * @param $email
     * @return string
     */
    public static function hideEmail($email) {
    
        $mail_parts = explode("@", $email);
        $length = strlen($mail_parts[0]);
        $show = floor($length/2);
        $hide = $length - $show;
        $replace = str_repeat("*", $hide);
        
        if(!isset($mail_parts[1])) return $email;
        return substr_replace ( $mail_parts[0] , $replace , $show, $hide ) . "@" . substr_replace($mail_parts[1], "**", 0, 2);
    }


    /** 
     * ИСПРАВИТЬ ОПЕЧАТКИ в популярных доменах Email`а
     * @param $email
     * @return string
     */
    public static function checkemaildomain($email) {

        $email_mass = explode('@',$email);

        if (in_array($email_mass[1], array('gmail.ru', 'gmial.com', 'gmial.ru','gvail.com','gail.com','gmal.com','qmail.com',
                                            'bmail.com','hmail.com','gmeil.com','gmal.ru','gmal.com','jmail.com'))) {
            $email_mass[1] = 'gmail.com';
        } elseif ($email_mass[1]== 'bk.ry') {
            $email_mass[1] = 'bk.ru';
        } elseif (in_array($email_mass[1], array('yndex.ru','yanbex.ru','uandex.ru','yadex.ru','eandex.ru',
                                                'iandex.ru','jandex.ru','yandekx.ru','yahdex.ru','yqndex.ru'))) {
            $email_mass[1] = 'yandex.ru';
        } elseif (in_array($email_mass[1], array('indox.ru','invox.ru','ibnox.ru'))) {
            $email_mass[1] = 'inbox.ru';
        } elseif (in_array($email_mass[1], array('maii.ru','mael.ru','meil.ru','maile.ru','maiil.ru','mial.ru','vail.ru'))) {
            $email_mass[1] = 'mail.ru';
        }
      
        $result = $email_mass[0].'@'.$email_mass[1];
        return $result;
    }


    /** 
     * ОБНОВИТЬ COOKIE
     * @param string $name
     * @param string|array $data
     * @param int $time
     * @param string $location
     * @return bool
     */
    public static function addCookie(string $name, $data, int $time = 86400, string $location = "/admin"): bool {

        if(isset($_COOKIE[$name])){
            $cookie = $_COOKIE[$name];
            if(is_string($data) && is_string($data))
                $data = $cookie . $data;
            
            elseif(is_array($data) 
                && ($cookie = @ json_decode($cookie, true)) 
                && ($cookie !== null && json_last_error() === JSON_ERROR_NONE)
                && is_array($cookie)
            )
                $data = json_encode($cookie + $data);   
        }

        return self::setCookie($name, $data, $time, $location);
    }


    /** 
     * СОЗДАТЬ COOKIE
     * @param string $name
     * @param string|array $data
     * @param int $time
     * @param string $location
     * @return bool
     */
    public static function setCookie(string $name, $data, int $time = 86400, string $location = "/admin"): bool {
        if(is_array($data))
            $data = json_encode($data);

        $data = base64_encode($data);

        $_COOKIE[$name] = $data;
        
        return setcookie($name, $data, time() + $time, $location);
    }

    /** 
     * ПОЛУЧИТЬ COOKIE
     * @param string $name
     * @param string $key
     * @param bool $delete
     * @return mixed
     */
    public static function getCookie(string $name, string $key = '', bool $delete = false) {
        if(!isset($_COOKIE[$name]))
            return null;

        $cookie = $_COOKIE[$name];

        if($delete && empty($key))
            self::unsetCookie($name);

        if(empty($cookie) || $cookie == "-")
            return null;

        $cookie = base64_decode($cookie);

        if((substr($cookie, 0, 1) == '[' && substr($cookie, -1) == ']')
            || (substr($cookie, 0, 1) == '{' && substr($cookie, -1) == '}')) {

            $json_decode = @ json_decode($cookie, true);

            if ($json_decode !== null && json_last_error() === JSON_ERROR_NONE){
                if(empty($key))
                    return $json_decode;

                if(is_array($json_decode) && isset($json_decode[$key])){
                    if($delete){
                        $new_data = $json_decode;
                        unset($new_data[$key]);
                        self::setCookie($name, $new_data);
                    }

                    return $json_decode[$key];
                }

                return null;
            }
        }

        return $cookie;
    }

    /** 
     * УДАЛИТЬ COOKIE
     * @param string $name
     * @return mixed
     */
    public static function unsetCookie(string $name) {
        if(!isset($_COOKIE[$name]))
            return null;

        setcookie($name, "-", time() - 18000);
        unset($_COOKIE[$name]);

        return true;
    }

    /**
     * УЗНАТЬ ИМЯ ФУНКЦИИ - Caller`а
     * @param  __FUNCTION__
     * @param  debug_backtrace()
     * @return string
     */
    public static function get_caller($function = NULL, $use_stack = NULL): string { 
        $stack = is_array($use_stack) 
            ? $use_stack
            : debug_backtrace(); 

        if ($function == NULL) 
            $function = self::get_caller(__FUNCTION__, $stack); 

        if (is_string($function) && $function != "" ){
            for ($i = 0; $i < count($stack); $i++) { 
                $curr_function = $stack[$i];

                if ($curr_function["function"] == $function
                    && ($i + 1) < count($stack)
                )
                    return $stack[$i + 1]["function"]; 
            }
        }

        return ""; 
    }

    public static function callers_history(string $function = '', array $use_stack = []): string { 
        $stack = empty($use_stack) 
            ? debug_backtrace()
            : $use_stack;

        if (empty($function)) 
            $function = self::callers_history(__FUNCTION__, $stack); 

        $res = '┌';
        $tab = "\n";
        $stack = array_reverse($stack);

        foreach ($stack as $fn) {
            if($fn['function'] == __FUNCTION__)
                continue;

            $res .= 'func: ' . @ $fn["class"] . @ $fn["type"] . "{$fn["function"]}(" . (empty($fn['args']) ? '' : @ implode(', ', $fn['args'])) . ");";
            $res .= "{$tab}├file: {$fn["file"]}:{$fn["line"]}{$tab}│{$tab}└┬";
            $tab .= ' ';

            if($fn['function'] == $function)
                break;
        }
        $res .= 'END.';

        return $res; 
    }

        /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @since 2.0.5
     *
     * @param string $data   Value to check to see if was serialized.
     * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     */
    public static function is_serialized( $data, $strict = true ) {
        // If it isn't a string, it isn't serialized.
        if ( ! is_string( $data ) ) {
            return false;
        }
        $data = trim( $data );
        if ( 'N;' === $data ) {
            return true;
        }
        if ( strlen( $data ) < 4 ) {
            return false;
        }
        if ( ':' !== $data[1] ) {
            return false;
        }
        if ( $strict ) {
            $lastc = substr( $data, -1 );
            if ( ';' !== $lastc && '}' !== $lastc ) {
                return false;
            }
        } else {
            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            // Either ; or } must exist.
            if ( false === $semicolon && false === $brace ) {
                return false;
            }
            // But neither must be in the first X characters.
            if ( false !== $semicolon && $semicolon < 3 ) {
                return false;
            }
            if ( false !== $brace && $brace < 4 ) {
                return false;
            }
        }
        $token = $data[0];
        switch ( $token ) {
            case 's':
                if ( $strict ) {
                    if ( '"' !== substr( $data, -2, 1 ) ) {
                        return false;
                    }
                } elseif ( false === strpos( $data, '"' ) ) {
                    return false;
                }
                // Or else fall through.
            case 'a':
            case 'O':
                return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
        }
        return false;
    }


    /**
     * ПАРСЕР PHP
     * @param $code_area
     */
    public static function parsePHPviaFile($code_area) {
        $tmp_fname = tempnam(ROOT."/tmp", "html");
        $handle = fopen($tmp_fname, "w");
        fwrite($handle, $code_area, strlen($code_area));
        fclose($handle);
        require_once($tmp_fname);
        unlink($tmp_fname);
    }


    /**
     * ПОЛУЧИТЬ СПИСОК РАЗРЕШЕННЫХ РАСШИРЕНИЕЙ У ФАЙЛОВ ДЛЯ ЗАГРУЗКИ НА СЕРВЕР
     * @return array
     */
    public static function getAllowedFileExtensions() {
        $allowed_extensions = [
            'doc','docx','pdf','xls','xlsx','ppt', 'pptx', 'zip',
            'rar','7z','jpg','jpeg','jpe','bmp', 'png','txt',
            'rtf','gif','xps','odt','ods','odp','csv','xmind', 'cdw', 'frw',
            'kdw', 'spw', 'm3d', 'a3d', 'xsp', 'mp3', 'aac', 'mp4', 'avi', 'mov', 'slddrw', 'sldprt', 'sldasm'
        ];

        return $allowed_extensions;
    }


    /**
     * ПОЛУЧИТЬ СПИСОК РАЗРЕШЕННЫХ MIME-ТИПОВ ДЛЯ ЗАГРУЗКИ НА СЕРВЕР
     * @return array
     */
    public static function getAllowedMimes() {
        $allowed_mimes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/rtf',
            'image/jpeg',
            'image/png',
            'application/pdf',
            'image/gif',
            'application/zip',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/pjpeg',
            'application/xml',
            'application/excel',
            'application/vnd.ms-excel',
            'application/msexcel',
            'application/x-excel',
            'application/x-dos_ms_excel',
            'application/xls',
            'application/x-xls',
            'application/x-msexcel',
            'application/x-ms-excel',
            'application/x-compressed',
            'application/x-zip-compressed',
            'multipart/x-zip',
            'application/octet-stream',
            'application/x-xmind',
            'application/acad',
            'application/x-acad',
            'application/autocad_dwg',
            'image/x-dwg',
            'application/dwg',
            'application/x-dwg',
            'application/x-autocad',
            'image/vnd.dwg',
            'drawing/dwg',
            'image/cdw',
            'image/vnd.dxf',
            'model/obj'
        ];

        return $allowed_mimes;
    }


    /**
     * РАЗРЕШЕНА ЛИ ЗАГРУЗКА ВЛОЖЕНИЯ НА СЕРВЕР
     * @param $attach_name
     * @param $mime_type
     * @param $attach_size
     * @param $settings
     * @return bool
     */
    public static function isAllowedUploadAttach($attach_name, $mime_type, $attach_size, $settings) {
        $allowed_extensions = System::getAllowedFileExtensions();
        $allowed_mimes = System::getAllowedMimes();
        $max_size = $settings['max_upload']  ? $settings['max_upload'] * 1048576 : 7340032;
        $path_info = pathinfo($attach_name);

        if ($attach_size <= $max_size && isset($path_info['extension']) && in_array($path_info['extension'], $allowed_extensions)
            &&  in_array($mime_type, $allowed_mimes)) {
            return true;
        }

        return false;
    }


    /**
     * @param $text
     * @return float
     */
    public static function getCountSMS($text) {
        $length = strlen($text);
        $sms_count_characters = preg_match('#[u0400-u04FF]#i', $text) ? 70 : 160;
        $count_sms = ceil($length/$sms_count_characters);

        return $count_sms;
    }
    
    
    public static function getSQL($sql)
    {
        $db = Db::getConnection();
        $result = $db->query($sql);
        return $result->fetchAll();
    }


    public static function getEmailsToAccountStatementIfItIsEnabled() {
        $db = Db::getConnection();
        $sql = "SELECT `notify_admin_about_account_statement`, `emails_for_account_statement_notifications` FROM `".PREFICS."settings` WHERE `setting_id` = 1";
        $result = $db->query($sql);
        $result = $result->fetch();
        if ($result['notify_admin_about_account_statement'] == 1) {
            $emails = json_decode($result['emails_for_account_statement_notifications']);
            return $emails;
        }
        return false;
    }

    /**
     * Получает пути всех файлов и папок в указанной папке.
     *
     * @param  string $dir             Путь до папки (на конце со слэшем или без).
     * @param  bool   $recursive       Включить вложенные папки или нет?
     * @param  bool   $include_folders Включить ли в список пути на папки?
     *
     * @return array Вернет массив путей до файлов/папок.
     */
     public static function get_dir_files(string $dir, $recursive = true, $include_folders = false, $onlyDirs = false){
        if( ! is_dir($dir) )
            return array();

        $files = array();

        $dir = rtrim( $dir, '/\\' ); // удалим слэш на конце

        foreach( glob( "$dir/{,.}[!.,!..]*", GLOB_BRACE ) as $file ){

            if( is_dir( $file ) ) {
                if( $include_folders ) {
                    $files[] = $file;
                }

                if( $recursive ) {
                    $files = array_merge($files, call_user_func(['System', 'get_dir_files'], $file, $recursive, $include_folders, $onlyDirs));
                }
            }
            else {
                if (!$onlyDirs) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }


    /**
     * @param $file_name
     * @return bool
     */
    public static function isImage($file_name) {
        if (preg_match("/(.*)\.(gif|jpg|jpe?g|png|bmp)$/", $file_name)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $emails
     *
     * @return mixed
     */
    public static function parseEmails(string $emails) {
        $pattern = "/[-a-z0-9!#$%&'*_`{|}~]+[-a-z0-9!#$%&'*_`{|}~\.=?]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+/i";

        preg_match_all($pattern, $emails, $parsedEmails);

        return !empty($parsedEmails[0]) ? $parsedEmails[0] : [];
    }

    public static function isEmail(string $str) {
        $pattern = "/[-a-z0-9!#$%&'*_`{|}~]+[-a-z0-9!#$%&'*_`{|}~\.=?]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+/i";

        return preg_match($pattern, $str) ? true : false;
    }

    /**
     * Разрешить долгое ожидание для бд
     * @param $max_exec_time
     */
    public static function enableLongWaitForQueries($max_exec_time = 3600) {
        Db::getConnection()->prepare("SET SESSION interactive_timeout = $max_exec_time")->execute();
        Db::getConnection()->prepare("SET SESSION wait_timeout = $max_exec_time")->execute();
    }

    /**
     * Получить расширение файла
     * @param $filename
     * @return string
     */
    public static function getFileExtension($filename) {
        $path_info = pathinfo($filename);
        return $path_info['extension'] ?? "";
    }

    public static function sessionAdd($key, $value) {
        return $_SESSION[$key] = $value;
    }

    public static function sessionFlush($key) {
        $value = $_SESSION[$key] ?? "";
        unset($_SESSION[$key]);
        return $value;
    }
}