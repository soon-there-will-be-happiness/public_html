<?php defined('BILLINGMASTER') or die;

class OrderFilter extends SegmentFilter {

    /**
     * @param null $type
     * @return array|mixed
     */
    public static function getFilterTitles($type = null) {
        $filter = [
            'order_id' => 'ID заказа',
            'order_number' => 'Номер заказа',
            'order_status' => 'Статус оплаты заказа',
            'crm_status' => 'Статус работы менеджера',
            'order_type' => 'Тип заказа',
            'sale_id' => 'Акция по заказу',
            'order_sum' => 'Сумма заказа',
            'order_date' => 'Дата создания',
            'payment_date' => 'Дата оплаты',
            'product_type' => 'Тип продукта',
            'product_id' => 'Продукт',
            'order_actual' => 'Актуальный заказ',
            'client_email' => 'E-mail',
            'client_phone' => 'Номер телефона',
            'client_fio' => 'ФИО',
            'payment_method' => 'Способ оплаты',
            'prepayment_added' => 'Менеджер добавил предоплату',
            'unpaid_prepaid_orders' => 'Неоплаченные заказы с предоплатой',
            'partner_id' => 'ID партнера',
        ];

        if (Organization::getOrgList()) {
            $filter['organization'] = 'Организация';
        }

        if (User::getManagerList()) {
            $filter['manager_id'] =  'Менеджер';
        }

        if (System::CheckExtensension('learning_flows', 1)) {
            $filter['flow_id'] =  'Поток';
        }

        return $type && isset($filter[$type]) ? $filter[$type] : $filter;
    }


    /**
     * @param $segment_data
     * @param null $type
     * @param null $cond_index
     * @param bool $cond_change
     * @return string
     */
    public static function getFilterHtml($segment_data, $type = null, $cond_index = null, $cond_change = true) {
        $form_elements = new SegmentFilterFormElements();
        $cond_index = $cond_index !== null ? $cond_index : 0;
        $type = $type !== null ? $type : $segment_data['condition_type'][$cond_index];
        $value = isset($segment_data[$type][$cond_index]) ? $segment_data[$type][$cond_index] : null;
        $start = isset($segment_data["{$type}_start"][$cond_index]) ? $segment_data["{$type}_start"][$cond_index] : null;
        $end = isset($segment_data["{$type}_end"][$cond_index]) ? $segment_data["{$type}_end"][$cond_index] : null;
        $start_date = isset($segment_data["{$type}_start_date"][$cond_index]) ? $segment_data["{$type}_start_date"][$cond_index] : null;
        $end_date = isset($segment_data["{$type}_end_date"][$cond_index]) ? $segment_data["{$type}_end_date"][$cond_index] : null;

        switch ($type) {
            case 'order_id':
                $form_elements->addTextInput("order_id[$cond_index]", $value, '', 'Введите ID заказа');
                break;
            case 'order_number':
                $form_elements->addTextInput("order_number[$cond_index]", $value, '', 'Введите номер заказа');
                break;
            case 'order_sum':
                $form_elements->addTextInput("order_sum_start[$cond_index]", $start,'', 'от', 'inline-block');
                $form_elements->addTextInput("order_sum_end[$cond_index]",  $end, '', 'до', 'inline-block');
                break;
            case 'order_actual':
                $form_elements->addRadio("order_actual[$cond_index]", $value, '',
                    [
                        [
                            'title' => 'Да',
                            'value' => 1
                        ],
                        [
                            'title' => 'Нет',
                            'value' => 0
                        ]
                    ]
                );
                break;
            case 'order_date':
                $form_elements->addSelect("order_date[$cond_index]", $value, '', self::getDateFilters(), 'date-options main-option');

                $form_elements->addTextInput("order_date_start[$cond_index]", $start, '', 'от', 'date-options');
                $form_elements->addTextInput("order_date_end[$cond_index]", $end, '', 'до', 'date-options');

                $form_elements->addDependency("order_date[$cond_index]", 'n_days_ago',
                    ["order_date_start[$cond_index]", "order_date_end[$cond_index]"]
                );
                $form_elements->addDependency("order_date[$cond_index]", 'n_hours_ago',
                    ["order_date_start[$cond_index]", "order_date_end[$cond_index]"]
                );

                $form_elements->addDateInput("order_date_start_date[$cond_index]", $start_date, '', 'с');
                $form_elements->addDateInput("order_date_end_date[$cond_index]", $end_date, '', 'по');

                $form_elements->addDependency("order_date[$cond_index]", 'period',
                    ["order_date_start_date[$cond_index]", "order_date_end_date[$cond_index]"]
                );
                break;
            case 'payment_date':
                $form_elements->addSelect("payment_date[$cond_index]", $value, '', self::getDateFilters(), 'date-options main-option');

                $form_elements->addTextInput("payment_date_start[$cond_index]", $start, '', 'от', 'date-options');
                $form_elements->addTextInput("payment_date_end[$cond_index]", $end, '', 'до', 'date-options');

                $form_elements->addDependency("payment_date[$cond_index]", 'n_days_ago',
                    ["payment_date_start[$cond_index]", "payment_date_end[$cond_index]"]
                );
                $form_elements->addDependency("payment_date[$cond_index]", 'n_hours_ago',
                    ["payment_date_start[$cond_index]", "payment_date_end[$cond_index]"]
                );

                $form_elements->addDateInput("payment_date_start_date[$cond_index]", $start_date, '', 'с');
                $form_elements->addDateInput("payment_date_end_date[$cond_index]", $end_date, '', 'по');

                $form_elements->addDependency("payment_date[$cond_index]", 'period',
                    ["payment_date_start_date[$cond_index]", "payment_date_end_date[$cond_index]"]
                );
                break;
            case 'payment_method':
                $pay_methods = Order::getPaymentsForAdmin();
                $data = [
                    [
                        'title' => 'Статус изменен на "оплачен" администратором',
                        'value' => "null"
                    ]
                ];
                if ($pay_methods) {
                    foreach ($pay_methods as $id => $pay_mthod) {
                        $data[] = [
                            'title' => $pay_mthod['title'],
                            'value' => $id
                        ];
                    }
                }

                $form_elements->addSelect("payment_method[$cond_index]", $value, '', $data);
                break;
            case 'sale_id':
                $sales = Product::getSaleList();
                $data = [];
                if ($sales) {
                    foreach ($sales as $sale) {
                        $data[] = [
                            'title' => "ID " . $sale['id'] . " | " . $sale['name'],
                            'value' => $sale['id']
                        ];
                    }
                }

                $form_elements->addSelect("sale_id[$cond_index]", $value, '', $data);
                break;
            case 'order_status':
                $statuses = Order::getStatuses();
                $data = [];
                if ($statuses) {
                    foreach ($statuses as $status => $title) {
                        $data[] = [
                            'title' => $title,
                            'value' => $status
                        ];
                    }
                }

                $form_elements->addSelect("order_status[$cond_index]", $value, '', $data);
                break;
            case 'crm_status':
                $statuses = Order::getCRMStatusList();
                $data = [];
                if ($statuses) {
                    foreach ($statuses as $status) {
                        $data[] = [
                            'title' => $status['title'],
                            'value' => $status['id']
                        ];
                    }
                }

                $form_elements->addSelect("crm_status[$cond_index]", $value, '', $data);
                break;
            case 'manager_id':
                $managers = User::getManagerList(1);
                $data = [];
                if ($managers) {
                    foreach ($managers as $manager) {
                        $data[] = [
                            'title' => $manager['user_name'],
                            'value' => $manager['user_id']
                        ];
                    }
                }

                $form_elements->addSelect("manager_id[$cond_index]", $value, '', $data);
                break;
            case 'order_type':
                $form_elements->addSelect(
                    "order_type[$cond_index]", $value, '', [
                        [
                            'title' => 'Платный',
                            'value' => 1
                        ],
                        [
                            'title' => 'Бесплатный',
                            'value' => 0
                        ]
                ]);
                break;
            case 'client_email':
                $form_elements->addTextInput("client_email[$cond_index]", $value, '', 'Введите e-mail клиента');
                break;
            case 'client_phone':
                $form_elements->addTextInput("client_phone[$cond_index]", $value, '', 'Введите телефон клиента');
                break;
            case 'client_fio':
                $form_elements->addTextInput("client_fio[$cond_index]", $value, '', 'Введите фамилию или имя клиента');
                break;
            case 'product_type':
                $types = Product::getTypes();
                $data = [];
                if ($types) {
                    foreach ($types as $type => $title) {
                        $data[] = [
                            'title' => $title,
                            'value' => $type
                        ];
                    }
                }

                $form_elements->addSelect("product_type[$cond_index]", $value, '', $data);
                break;
            case 'product_id':
                $products = Product::getAdminProductList();
                $data = [];
                if ($products) {
                    foreach ($products as $product) {
                        $data[] = [
                            'title' => $product['name_with_id'],
                            'value' => $product['product_id']
                        ];
                    }
                }

                $form_elements->addSelect("product_id[$cond_index]", $value, '', $data);
                break;
            case 'organization':
                $organizations = Organization::getOrgList();
                $data = [];
                if ($organizations) {
                    foreach ($organizations as $organization) {
                        $data[] = [
                            'title' => $organization['org_name'],
                            'value' => $organization['id']
                        ];
                    }
                }

                $form_elements->addSelect("organization[$cond_index]", $value, '', $data);
                break;
            case 'flow_id':
                $flows = Flows::getFlows();
                $data = [];
                if ($flows) {
                    foreach ($flows as $flow) {
                        $data[] = [
                            'title' => $flow['flow_name'],
                            'value' => $flow['flow_id']
                        ];
                    }
                }

                $form_elements->addSelect("flow_id[$cond_index]", $value, '', $data);
                break;
            case 'partner_id':
                $form_elements->addTextInput("partner_id[$cond_index]", $value, '', 'Введите ID партнера');
                break;
        }

        if (in_array($type, ['order_date', 'payment_date']) && $value != 'period') {
            $additional_info = self::getDateText2Filter($value, $start, $end);
            $form_elements->getElement("{$type}[$cond_index]")->setAdditionalInfo($additional_info);
        }

        if (!$cond_change) {
            $form_elements->setAttributes('disabled', 'disabled');
        }

        return $form_elements->getHtml();
    }


    /**
     * @param $condition_type
     * @param $cond_index
     * @param $data
     * @return array|string
     */
    public static function getCondition($condition_type, $cond_index, $data) {
        $condition = [];
        $value = isset($data[$condition_type][$cond_index]) ? trim($data[$condition_type][$cond_index]) : null;

        switch ($condition_type) {
            case 'order_id':
                $condition = 'o.order_id = '.(int)$value;
                break;
            case 'order_number':
                $condition = 'o.order_date = '.(int)$value;
                break;
            case 'order_sum':
                $_condition = [];
                if ($data['order_sum_start'][$cond_index]) {
                    $_condition[] = 'SUM(price) >= '.(int)$data['order_sum_start'][$cond_index];
                }
                if ($data['order_sum_end'][$cond_index]) {
                    $_condition[] = 'SUM(price) <= '.(int)$data['order_sum_end'][$cond_index];
                }
                $_condition = 'SELECT order_id FROM '.PREFICS.'order_items GROUP BY order_id'.
                               (!empty($_condition) ? ' HAVING '.implode(' AND ', $_condition) : '');
                $condition = "o.order_id IN ($_condition)";
                break;
            case 'order_actual':
                $condition = $value ? '(o.expire_date > '.time().' AND o.status = 0)' : '(o.expire_date <= '.time().' OR o.status <> 0)';
                break;
            case 'order_date':
                $key1 = $value != 'period' ? 'order_date_start' : 'order_date_start_date';
                $key2 = $value != 'period' ? 'order_date_end' : 'order_date_end_date';
                $date = self::getDate2Filter($data['order_date'][$cond_index], htmlentities($data[$key1][$cond_index]),
                    htmlentities($data[$key2][$cond_index])
                );

                if ($date) {
                    $condition = [
                        "o.order_date >= {$date[0]}",
                        "o.order_date <= {$date[1]}"
                    ];
                }
                break;
            case 'payment_date':
                $key1 = $value != 'period' ? 'payment_date_start' : 'payment_date_start_date';
                $key2 = $value != 'period' ? 'payment_date_end' : 'payment_date_end_date';
                $date = self::getDate2Filter($data['payment_date'][$cond_index], htmlentities($data[$key1][$cond_index]),
                    htmlentities($data[$key2][$cond_index])
                );

                if ($date) {
                    $condition = [
                        "o.payment_date >= {$date[0]}",
                        "o.payment_date <= {$date[1]}"
                    ];
                }
                break;
            case 'payment_method':
                $condition = $value == "null"
                    ? [
                        'o.payment_id IS NULL',
                        'o.status = 1'
                        ]
                    : 'o.payment_id = ' . (int) $value;
                break;
                break;
            case 'sale_id':
                $condition = 'o.sale_id = '.(int)$value;
                break;
            case 'order_status':
                $condition = 'o.status = '.(int)$value;
                break;
            case 'crm_status':
                $condition = 'o.crm_status = '.(int)$value;
                break;
            case 'manager_id':
                $condition = 'o.manager_id = '.(int)$value;
                break;
            case 'order_type':
                $condition = $value ? 'o.summ > 0' : 'o.summ = 0';
                break;
            case 'client_email':
                $condition = "o.client_email LIKE '%".htmlentities($value)."%'";
                break;
            case 'client_phone':
                $condition = "o.client_phone LIKE '%".htmlentities($value)."%'";
                break;
            case 'client_fio':
                $value = trim(htmlentities($value));
                if ($value) {
                    $condition = "o.client_name REGEXP '".str_replace(' ', '|', $value)."'";
                }

                break;
            case 'product_type':
                $condition = 'p.type_id = '.(int)$value;
                break;
            case 'product_id':
                $condition = 'p.product_id = '.(int)$value;
                break;
            case 'organization':
                $condition = 'o.org_id = '.(int)$value;
                break;
            case 'prepayment_added':
                $condition = "o.deposit != 'null'";
                break;
            case 'unpaid_prepaid_orders':
                $condition = "(o.deposit != 'null' AND (o.status = 0 OR o.status = 2))";
                break;
            case 'flow_id':
                $condition = $value ? 'oi.flow_id = '.(int)$value : '';
                break;
            case 'partner_id':
                $condition = $value ? 'o.partner_id = '.(int)$value : '';
                break;
        }

        return $condition ? $condition : '';
    }


    /**
     * @param $segment_id
     * @param $segment_name
     * @param $data
     * @return bool|string
     */
    public static function addSegment($segment_id, $segment_name, $data) {
        $db = Db::getConnection();
        $result = $db->prepare('INSERT INTO '.PREFICS.'orders_filter_segments (segment_id, segment_name, data)
                                        VALUES (:segment_id, :segment_name, :data)'
        );

        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);
        $result->bindParam(':segment_name', $segment_name, PDO::PARAM_STR);
        $result->bindParam(':data', $data, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @param $segment_id
     * @param $data
     * @return bool
     */
    public static function updSegment($segment_id, $data) {
        $db = Db::getConnection();
        $result = $db->prepare('UPDATE '.PREFICS.'orders_filter_segments SET data = :data WHERE segment_id = :segment_id'
        );

        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);
        $result->bindParam(':data', $data, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @param $segment_id
     * @return bool
     */
    public static function delSegment($segment_id) {
        $db = Db::getConnection();
        $result = $db->prepare('DELETE FROM '.PREFICS.'orders_filter_segments WHERE segment_id = :segment_id');
        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @return mixed
     */
    public static function getMaxSegmentId() {
        $db = Db::getConnection();
        $result = $db->query('SELECT MAX(segment_id) FROM '.PREFICS.'orders_filter_segments');
        $data = $result->fetch();

        return $data[0];
    }


    /***
     * @param $id
     * @return bool|mixed
     */
    public static function getSegment($id) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT * FROM '.PREFICS.'orders_filter_segments WHERE segment_id = :segment_id');
        $result->bindParam(':segment_id', $id, PDO::PARAM_STR);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return $data ? $data : false;
    }

    /**
     * @return array|bool
     */
    public static function getSegments() {
        $db = Db::getConnection();
        $result = $db->query('SELECT * FROM '.PREFICS.'orders_filter_segments ORDER BY segment_id DESC');
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data ? $data : false;
    }
}
