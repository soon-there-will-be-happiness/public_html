<?php


class apiController extends apiBaseController
{
    ///////////////
    // USERS ---------------------------------
    //////////////\\
    private $userValidation = [
        'name' => ['required', 'string'],
        'email' => ['required', 'email', 'unique'=>'users:email'],
        'phone' => ['phone'],
        'city' => ['string'],
        'zipcode' => ['string'],
        'address' => ['string'],
        'status' => ['required', 'int'],
        'note' => ['string'],
        'is_partner' => ['int'],
        'is_subs' => ['int'],
        'role' => ['required', 'string', 'values'=>'user/manager/admin'],
        'login' => ['string'],
        'surname' => ['string'],
        'patronymic' => ['string'],
        'sex' => ['string'],
        'nick_telegram' => ['string'],
        'vk_url' => ['string'],
        'level' => ['int'],
        'nick_instagram' => ['string'],
        'spec_aff' => ['int'],
        'pass'=>['string', 'min'=>'6'],
        //нужно передавать массив
        'groups'=>['required', 'array'],
        'groups_dates'=>['required', 'array'],
    ];

    public function actionGetUser($id) {
        $user = user::getUserById($id);
        if ($user == false) {
            return $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }
        $this->response($user);
    }

    public function actionDeleteUser($id) {
        $user = user::getUserById($id);
        if ($user == false) {
            return $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }
        $result = user::deleteUser($id, true);
        $this->response(['status'=>$result, 'message'=>'ok']);
    }

    public function actionEditUser($id) {

        $data = $_POST;
        $user = user::getUserById($id);
        if ($user == false) {
            return $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }
        $validator = new validator($data, true);
        $data = $validator->validate($this->userValidation);

        @$result = user::editUser($id, $data['name'], $data['email'], $data['phone'], $data['city'],  $data['zipcode'],  $data['address'],  $data['note'],
            $data['status'],  $data['pass'],  $data['groups'],  $data['groups_dates'],  $data['is_partner'],  $data['is_subs'],  $data['role'],  $data['login'],
            $data['surname'],  $data['patronymic'],  $data['sex'],  $data['nick_telegram'],  $data['nick_instagram'],  $data['level'],  $data['vk_url'],
            $data['spec_aff'], $data['curators']);

        $this->response([
            'status'=> $result,
            'message'=> $result ? 'Успешно изменено' : 'Ошибка',
        ],$result ? 200 : 422);
    }

    public function actionAddUser() {

        $data = $_POST;
        $validator = new validator($data, true);

        $this->userValidation['is_client'] = ['required', 'int'];

        $data = $validator->validate($this->userValidation);
        $time = time();
        $user_param = "$time;0;;";
        $hash = password_hash($data['pass'], PASSWORD_DEFAULT);
        $settings = System::getSetting(true);

        $result = user::AddNewClient($data['name'], $data['email'], $data['phone'], $data['city'], $data['address'],  $data['zipcode'],  $data['role'],
            $data['is_client'],  $time,  'api',  $user_param,  $data['status'],  $hash,  $data['pass'],  false,
            $settings['register_letter'],  $data['is_subs'],  $data['login'],  null,  $data['surname'], $data['patronymic'],  $data['nick_telegram'],  $data['nick_instagram'],
        );
        $this->response([
            'status' => true,
            'user' => $result,
        ],201);
    }


    ///////////////
    // ORDERS ---------------------------------
    //////////////\\

    private $orderValidation = [
        'summ' => ['required', 'int'],
        'order_date' => ['required', 'int'],
        'product_id' => ['required', 'int'],
        'client_name' => ['required', 'string'],
        'client_email' => ['required', 'email'],
        'client_phone' => ['phone'],
        'client_city' => ['string'],
        'client_address' => ['string'],
        'client_comment' => ['string'],
        'client_index' => ['string'],
        'sale_id' => ['int'],
        'partner_id' => ['int'],
        'status' => ['required', 'int'],
        'base_id' => ['int'],
        'visit_param' => ['string'],
        'channel_id' => ['int'],
        'remind_letter' => ['int'],
        'installment_map_id' => ['int'],
        'is_recurrent' => ['int'],
        'expire_date' => ['int'],
        'org_id' => ['int'],
        'create_from' => ['int'],
        'split_var'=>['int'],
        'type_id'=>['int'],
        'number'=>['int'],
        'price'=>['int'],
        'nds'=>['float'],
        'cast'=>['int'],
        'dwl_count'=>['int'],
        'product_name'=>['string'],
        'surname'=>['string'],
        'patronymic'=>['string'],
        'nick_telegram'=>['string'],
        'nick_instagram'=>['string'],
        'utm'=>['string'],
        'subs_id'=>['int'],
    ];


    public function actionGetOrder($id) {
        $order = order::getOrder($id);
        if ($order == false) {
            return $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }
        $this->response($order);
    }

    public function actionAddOrder() {
        $data = $_POST;

        $time = time();
        $ip = System::getUserIp();


        $validator = new validator($data, true);
        $data = $validator->validate($this->orderValidation);
        $price = Price::getFinalPrice($data['product_id']);
        $nds_price = Price::getNDSPrice($price['real_price']);

        @$result = order::addOrder($data['product_id'], $nds_price['price'], $nds_price['nds'], $data['client_name'],
            $data['client_email'], $data['client_phone'], $data['client_index'], $data['client_city'], $data['client_address'],
            $data['client_comment'], $data['visit_param'], $data['partner_id'], $time, $data['sale_id'], $data['status'],
            $data['base_id'], $data['split_var'], $data['type_id'], $data['product_name'], $ip,0,
            $data['surname'], $data['patronymic'], $data['nick_telegram'], $data['nick_instagram'],
            $data['installment_map_id'], $data['utm'], $data['is_recurrent'], $data['subs_id'], $data['create_from']
        );

        if (is_int(intval($result))) {
            $status = true;
            $result = order::getOrder($result);
        }
        else {
            $this->response([
                'status' => false,
                'message' => 'Не известная ошибка'
            ]);
        }
        $this->response([
            'status'=> $status,
            'order'=> $result,
        ]);
    }

    public function actionGetOrderList() {
        $list = order::getOrdersList(self::paginate());
        return $this->response($list);
    }

    public function actionEditOrder($id) {
        $data = $_POST;
        $order = order::getOrder($id);

        if ($order == false) {
            $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }

        $this->orderValidation = array_merge($this->orderValidation, [
            'summ' => ['int'],
            'order_date' => ['int'],
            'product_id' => ['int'],
            'client_name' => ['string'],
            'client_email' => ['email'],
            'ship_status' => ['int'/*, 'notNull'=>'0'*/],
            'crm_status' => ['int'],
            'manager_id' => ['int'],
            'status' => ['int'],
        ]);

        $validator = new validator($data, true);
        $data = $validator->validate($this->orderValidation);

        $data = array_merge($order, $data);
        @$result = order::updateOrderToAdmin($id, $data['client_name'], $data['client_email'], $data['client_phone'],
            $data['client_city'], $data['client_index'], $data['client_address'], $data['status'], $data['ship_status'],
            $data['client_comment'], $data['admin_comment'], $data['order_date'], $data['payment_date'],
            $data['expire_date'], $data['crm_status'], $data['manager_id'],$data['order_info']
        );

        $this->response([
            'status'=> $result,
            'message' => 'Успешно отредактировано'
        ]);
    }

    ///////////////
    // MEMBER ---------------------------------
    //////////////\\
    ///

    private $memberValidation = [
        'name' => ['string', 'required', 'notNull'=>'based_name'],
        'subs_desc' => ['string', 'required'],
        'lifetime' => ['int', 'required'],
        'period_type' => ['string', 'values'=>'Day/Week/Month'],//Day, Week, Month
        'extension_from_type' => ['int'],
        'max_periods' => ['int'],
        'delay' => ['int'],
        'access_id' => ['int', 'required'],
        'del_groups' => ['array'],

        'letter_1' => ['string'],
        'letter_1_time' => ['int'],
        'letter_2' => ['string'],
        'letter_2_time' => ['int'],
        'letter_3' => ['string'],
        'letter_3_time' => ['int'],
        'letter_1_subj' => ['string'],
        'letter_2_subj' => ['string'],
        'letter_3_subj' => ['string'],

        'renewal_type' => ['int'],
        'renewal_product' => ['int', 'required'],
        'renewal_link' => ['string'],

        'status' => ['int', 'required'],
        'amount' => ['int'],
        'recurrent_label' => ['string'],
        'select_payments' => ['string'],
        'manager_letter' => ['string'],
        'add_groups' => ['array'],
        'add_planes' => ['string'],
        'recurrent_enable' => ['int'],
        'service_name' => ['string'],
        'del_tg_chats' => ['string'],
        'related_planes' => ['string'],
        'create_new' => ['int'],
        'prolong_active' => ['int'],
        'prolong_link' => ['string'],
        'first_time' => ['int'],
        'first_time_data' => ['string'],

        'sms1_status' => ['int'],
        'sms2_status' => ['int'],
        'sms3_status' => ['int'],
        'sms1_text' => ['string'],
        'sms2_text' => ['string'],
        'sms3_text' => ['string'],

        'letter_1_status' => ['int'],
        'letter_2_status' => ['int'],
        'letter_3_status' => ['int'],
    ];

    public function actionGetMember($id) {
        $member = Member::getPlaneByID($id);
        if ($member == false) {
            $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }
        $this->response($member);
    }

    public function actionGetMemberList() {
        $members = Member::getPlanes();
        if ($members == false) {
            $this->response(['status'=>false, 'message'=>'Не найдено'], 404);
        }
        $this->response($members);
    }

    public function actionAddMember() {
        $data = $_POST;
        $db = Db::getConnection();
        $validator = new validator($data, true);
        $data = $validator->validate($this->memberValidation);

        $result = Member::AddNewPlane($data);
        $id = $db->lastInsertId();
        $member = Member::getPlaneByID($id);
        $this->response([
            'status' => $result,
            'message'=> 'Успешно создано',
            'memberplane' => $member,
        ], 201);
    }

    public function actionEditMember($id) {
        $data = $_POST;

        $validator = new validator($data, true);
        $data = $validator->validate($this->memberValidation);

        $result = Member::editPlane($id, $data);

        $this->response([
            'status'=> $result,
            'message'=> 'Успешно изменено',
        ]);
    }
}