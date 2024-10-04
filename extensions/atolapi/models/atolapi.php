<?php
require_once 'extensions/atolapi/controllers/atolapiController.php';
require_once 'extensions/atolapi/models/Client.php';
require_once 'extensions/atolapi/models/Company.php';
require_once 'extensions/atolapi/models/Item.php';
require_once 'extensions/atolapi/models/Items.php';
require_once 'extensions/atolapi/models/Receipt.php';

use Atol\Client;
use Atol\Company;
use Atol\Item;
use Atol\Items;
use Atol\Receipt;


defined('BILLINGMASTER') or die;

class Atolapi {
    private $db;
    private $atol;
    private $client;
    private  $company;
    private $item;
    private $items;
    private $url; //url на который отправляется запрос
    private $token;
    private $operation='sell'; // чек «Приход» //TODO поставлена операция "приход", т.к. сведений пока нет
    private $receipt;

    public function __construct(){
        $db = new Db();
        $this->db = $db::getConnection();
        $this->atol = new atolapiController();
        $this->client = new Client();
        $this->company = new Company();
        $this->item = new Item();
        $this->items = new Items();
    }

        //Запись или изменение данных компании продавца в БД
    public function setDataCompanyAtolToDB($data,$is_exist_company=false){

        if($is_exist_company==true)
            $sql = 'UPDATE `'.PREFICS.'atol_company` SET `name` = :name_company,`inn` = :inn,`email` = :email,`phone` = :phone,`address` = :address,`sno` = :sno,`nds` = :nds,`login` = :login,`pass` = :pass,`url` = :url , `group_code` = :group_code WHERE `id` = :id';
        else
          $sql = 'INSERT INTO `'.PREFICS.'atol_company` (`id`,`name`, `inn`, `email`, `phone`, `address`, `sno`, `nds`, `login`, `pass`, `url`, `group_code`) 
                VALUES (:id,:name_company,:inn ,:email ,:phone ,:address,:sno ,:nds,:login,:pass,:url,:group_code)';
      $query = $this->db->prepare($sql);
      $query->execute([
          'id'=>1,
          'name_company'=>$data['name'],
          'inn'=>$data['inn'],
          'email'=>$data['email'],
          'phone'=>$data['phone'],
          'address'=>$data['address'],
          'sno'=>$data['sn'],
          'nds'=>$data['vat'],
          'login'=>$data['login'],
          'pass'=>$data['password'], //TODO пока без шифорования, как хранить пароль? дело в том, что атол запрашивает пароль в чистом виде, а не в хеше с солью, и проблема в том, что хранить в БД чистый пароль нельзя, а извлечь из хеша с солью - невозможно. Токен атола действует сутки, тогда - либо использовать токен на сутки и обновлять его, либо при каждой операции вводить пароль вручную.
          'url'=>$data['url'],
          'group_code'=>$data['code']
      ]);
    }

    //Получение данных компании продавца из БД
    public function getDataCompanyFromDB($id=1){

        $sql='SELECT * FROM `'.PREFICS.'atol_company` WHERE `id`=:id';
        $query = $this->db->prepare($sql);
        $query->execute(['id'=>$id]);
        return $query->fetch(PDO::FETCH_ASSOC);

    }


    //При оплате заказа получаем id заказа
    public function is_paid_order($id){
            $error = null;
            $data_order=[];
            $sql = 'SELECT * FROM `'.PREFICS.'orders` WHERE `order_id`=:id'; //status при оплате = 1, если оплата прошла и статус стал =1 тогда отправляем данные в атол
            $query = $this->db->prepare($sql);
            $query->execute(['id'=>$id]);
            $data_order = $query->fetch(PDO::FETCH_ASSOC); // все данные из таблицы orders

            if( $data_order != null){
                        //получаем данние клиента
                $data_client['name'] = $data_order['client_name'];
                $data_client['email'] = $data_order['client_email'];
                $data_client['phone'] = $data_order['client_phone'];
                $data_client['inn'] = ''; //TODO  в таблице заказов нет столбца с ИНН, пока пустое значение.
                $client=$this->client->getClient($data_client); // данные клиента для Send ATOL
                if(isset($client['error']))
                    $error = $client['error'];  // в переменной записана ошибка, если она есть
                //получаем данные продавца
                $data_company = $this->getDataCompanyFromDB();
                $company = $this->company->getCompany($data_company);// данные компании продавца для Send ATOL

                    //получаем данные продукта
                $id_product =  $data_order['product_id'];
                $product = $this->getOrder($id_product);
                $item = $this->item->getItem($product);
                // TODO по идее, если в заказе (таблица orders) несколько позиций товара, тогда должно формироваться items  из нескольких item, но с учетом того, что product только один формируем items из одного item
                $item['vat'] = $company['vat'];
                $items = $this->items->getItems([$item]); // в массиве передаем все товары для Send ATOL
                //получаем токен
                $this->token = self::getToken($data_company);
                    if(isset($this->token->error) && $this->token->error != null){
                        $token = '';
                        $error = $this->token->error->text;
                    }
                    else{
                        $token = $this->token->token;
                    }
                $this->url = $data_company['url'].'possystem/v4/'.$data_company['group_code'].'/'.$this->operation.'?token='.$token;
//                $this->url = 'https://testonline.atol.ru/possystem/v4/v4-online-atol-ru_4179/buy_refund?token='.$token;
                //формиуем запрос в атол
                $this->receipt = new Receipt($items);
                $receipt =$this->receipt->Receipt($company,$client);
//                print_r($receipt);
                $result = json_decode($this->atol->SendDataToAtol($this->url,$receipt));
                    if(isset($result->error))
                           $error = $result->error->text;
                    elseif ($error == null)
                        return $result; //stdClass Object ( [uuid] => e55c285e-6f28-433c-845e-b062090eb768 [status] => wait [error] => [timestamp] => 24.03.2022 13:04:16 )
                        //TODO  далее с $result - записываем в БД uuid, и то что нам нужно для дальнейшей обработки.
            }
            else{
                $error = 'Не найден указанный продукт';
            }
            return $error; //если не вернули результат или были ошибки - возращаем ошибку.
    }

    public function getToken($data_company){

        $data = [
            'login' => $data_company['login'],
            'pass' => $data_company['pass']
        ];
        $url = $data_company['url'].'possystem/v4/getToken';
        return json_decode($this->atol->SendDataToAtol($url,$data));

    }

    //Получение данных о продукте
    public function getOrder($id){
        $sql='SELECT * FROM `'.PREFICS.'products` WHERE `product_id`=:id';
        $query=$this->db->prepare($sql);
        $query->execute(['id'=>$id]);
        $data_product=$query->fetch(PDO::FETCH_ASSOC); // все данные из таблицы orders
        //TODO на будущее - непониятно, по какой цене прошла оплата если статус оплаты в таблице orders = 1 ( красная цена, или со скидкой?)???
        $product = [
            'name'=>$data_product['product_name'],
            'price'=>$data_product['price'],
            'quantity'=>1, //TODO в БД  таблица orders нет данных о количестве товара, пока поставил 1
//            'sum'=>$data_product['summ'],
            'measurement_unit'=>'шт.',//TODO в БД  таблица orders нет данных о еденице измерения товара, пока поставил шт
            'payment_method'=>'full_prepayment'//TODO в БД  таблица orders нет данных о способе оплаты товара, пока поставил полную 100% предоплату
        ];
        $product['sum'] =(float) $product['price'] * (float) $product['quantity'];
       return $product;
    }

/*
 * $this->operation
 тип операции, которая должна быть выполнена. Возможные типы
операция:
o sell: чек «Приход»;
o sell_refund: чек «Возврат прихода»;
o sell_correction: чек «Коррекция прихода»;
o buy: чек «Расход»;
o buy_refund: чек «Возврат расхода»;
o buy_correction: чек «Коррекция расхода».
 * */

}