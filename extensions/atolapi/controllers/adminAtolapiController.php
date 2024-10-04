<?php defined('BILLINGMASTER') or die;
require_once 'extensions/atolapi/models/atolapi.php';

class adminAtolapiController extends AdminBase {
    private $company;
    private $is_exist_company=false;
    private $company_name = '';
    private $company_inn = '';
    private $company_email = '';
    private $company_phone = '';
    private $company_address = '';
    private $company_sn = 'osn';
    private $company_vat = 'none';
    private $company_url = 'https://online.atol.ru/';//https://testonline.atol.ru/ - данные для теста
    private $company_login = ''; // v4-online-atol-ru  - данные для теста
    private $company_code = ''; // v4-online-atol-ru_4179  - данные для теста
    private $company_password = ''; //iGFFuihss   - данные для теста


    public function __construct() {
        $this->company = new atolapi();
        $find = $this->company->getDataCompanyFromDB();
        if($find != null){// проверяем, существует ли запись о компании в бд
            $this->is_exist_company = true;
            $this->company_name = $find['name'];
            $this->company_inn = $find['inn'];
            $this->company_email = $find['email'];
            $this->company_phone = $find['phone'];
            $this->company_address = $find['address'];
            $this->company_sn = $find['sno'];
            $this->company_vat = $find['nds'];
            $this->company_url = $find['url'];
            $this->company_login = $find['login'];
            $this->company_password = $find['pass']; //TODO  как сделать хранение пароля в БД????
            $this->company_code = $find['group_code'];
        }
        else
            $this->is_exist_company=false;

    }

    // НАСТРОЙКИ Atolapi
    public function actionSettings() {
        $acl = self::checkAdmin();
        $name = $_SESSION['admin_name'];
        if (isset($_POST['save']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['admin_token']) {//если POST

            isset($_POST['atol']['company']['name']) ? $this->company_name = trim(filter_var($_POST['atol']['company']['name'],FILTER_SANITIZE_STRING))  :$this->company_name = '' ;
            isset($_POST['atol']['company']['inn']) ? $this->company_inn = (integer) trim(filter_var($_POST['atol']['company']['inn'],FILTER_SANITIZE_STRING))  :$this->company_inn = '' ;
            isset($_POST['atol']['company']['email']) ? $this->company_email = trim(filter_var($_POST['atol']['company']['email'],FILTER_SANITIZE_EMAIL))  :$this->company_email = '' ;
            isset($_POST['atol']['company']['phone']) ? $this->company_phone = trim(filter_var($_POST['atol']['company']['phone'],FILTER_SANITIZE_STRING))  :$this->company_phone = '' ;
            isset($_POST['atol']['company']['address']) ? $this->company_address = trim(filter_var($_POST['atol']['company']['address'],FILTER_SANITIZE_STRING))  :$this->company_address = '' ;
            isset($_POST['atol']['company']['sn']) ? $this->company_sn = trim(filter_var($_POST['atol']['company']['sn'],FILTER_SANITIZE_STRING))  :$this->company_sn = 'osn' ;
            isset($_POST['atol']['company']['vat']) ? $this->company_vat = trim(filter_var($_POST['atol']['company']['vat'],FILTER_SANITIZE_STRING))  :$this->company_vat = 'none' ;
            isset($_POST['atol']['company']['url']) ? $this->company_url = trim(filter_var($_POST['atol']['company']['url'],FILTER_SANITIZE_STRING))  :$this->company_url = 'https://online.atol.ru/' ;
            isset($_POST['atol']['company']['login']) ? $this->company_login = trim(filter_var($_POST['atol']['company']['login'],FILTER_SANITIZE_STRING))  :$this->company_login = '' ;
            isset($_POST['atol']['company']['password']) ? $this->company_password = trim(filter_var($_POST['atol']['company']['password'],FILTER_SANITIZE_STRING))  :$this->company_password = '' ;
            isset($_POST['atol']['company']['group_code']) ? $this->company_code = trim(filter_var($_POST['atol']['company']['group_code'],FILTER_SANITIZE_STRING))  :$this->company_code = '' ;

            $message = self::checkDataCompanyAtol();
               if($message != ''){
                   $error = $message;
               }
               else{
                   $error = '';
                   $data_company = [
                       'name'=>$this->company_name,
                       'inn'=>$this->company_inn,
                       'email'=>$this->company_email,
                       'phone'=>$this->company_phone,
                       'address'=>$this->company_address,
                       'sn'=>$this->company_sn,
                       'vat'=>$this->company_vat,
                       'url'=>$this->company_url,
                       'login'=>$this->company_login,
                       'password'=>$this->company_password,
                       'code'=>$this->company_code
                   ];


                   $this->company->setDataCompanyAtolToDB($data_company,$this->is_exist_company);
                   $params = ''; //serialize($_POST['customapi']); Тут можно с формы передать нужные поля для записи в базу
                   $enable = trim($_POST['status']);
                   $save = System::SaveExtensionSetting('atolapi', $params, $enable);

                System::redirectUrl('/admin/atolapi-settings', $save);
               }

        }
        $settings = System::getExtensionSetting('atolapi');
        $params = unserialize($settings);
        $enable = System::getExtensionStatus('atolapi');

//            $tmp=$this->company->is_paid_order(5);  //проверочная строка
//            print_r($tmp);//проверочная строка
        require_once (__DIR__ . '/../views/settings.php');
    }


    //валидация полей ввода компании
    public function checkDataCompanyAtol(){

        $testConnectionToAlol = new atolapiController();
        $this->company_url != '' && $this->company_login != '' && $this->company_password != '' ? $data = [
            'login' => $this->company_login,
            'pass' => $this->company_password
        ] : $data=[] ;
        $url=$this->company_url.'possystem/v4/getToken';
        $resultConnectionAlol = json_decode($testConnectionToAlol->SendDataToAtol($url,$data)) ;

                //Наименование
        if($this->company_name == '')
            return 'Наименование организации не может быть пустым';
         elseif (strlen($this->company_name) < 3)
            return 'Наименование организации слишком короткое, от 3 символов';
         elseif (strlen($this->company_name) > 50)
             return 'Наименование организации слишком длинное, до 50 символов';
                        //ИНН
        elseif ($this->company_inn == '')
            return 'ИНН организации не может быть пустым';
        elseif (!is_integer($this -> company_inn))
            return 'ИНН организации не может содержать буквы';
        elseif ($this->company_inn < 999999999 || $this->company_inn > 10000000000 && $this->company_inn < 100000000000 || $this->company_inn > 999999999999)
            return 'ИНН организации не верен';
                         //E-mail
          elseif ($this->company_email == '')
            return 'E-mail организации не может быть пустым';
                          //Адрес
        elseif ($this->company_address == '')
            return 'Адрес организации не может быть пустым';
        elseif (strlen($this->company_address) < 10)
            return 'Адрес организации слишком короткий';
        elseif (strlen($this->company_address) > 100)
            return 'Адрес организации слишком длинный';
                          //login
        elseif ($this->company_login == '')
            return 'Login не может быть пустым';
                          //password
        elseif ($this->company_password == '')
            return 'Password не может быть пустым';
        elseif ($this->company_code == '')
            return 'Код группы не может быть пустым';
        elseif (isset($resultConnectionAlol->error) && $resultConnectionAlol->error != null )
            return $resultConnectionAlol->error->text;
        elseif (isset($resultConnectionAlol->token) && $resultConnectionAlol->token != null  )
            return '';
        else
             return 'Неверно записан url адрес  сервера  Atol  или неверные данные логин\пароль';

    }


}