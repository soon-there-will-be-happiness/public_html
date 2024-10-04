<?php defined('BILLINGMASTER') or die;

class UserFilter extends SegmentFilter {

    /**
     * @param null $type
     * @return array|mixed
     */
    public static function getFilterTitles($type = null) {
        $filter = [
            'user_id' => 'ID пользователя',
            'user_name' => 'Имя',
            'user_surname' => 'Фамилия',
            'user_patronymic' => 'Отчество',
            'user_email' => 'E-mail',
            'phone' => 'Телефон',
            'vk' => 'Адрес страницы в ВК',
            'tg' => 'Логин Telegram',
            'ig' => 'Логин Instagram',
            'user_role' => 'Роль пользователя',
            'status' => 'Статус пользователя',
            'count_subs_renewals' => 'Кол-во продлений подписки'
        ];
        $filter['is_has_group'] = 'Наличие группы'; // да/нет
        if (User::getUserGroups()) {
            $filter['group'] = 'Группа';
            $filter['groups'] = 'Группы';
        }
        $filter['is_has_subscribe'] = 'Наличие подписки'; // да/нет
        $filter['subscribe_actual'] = 'Актуальность подписки';
        if (Member::getPlanes()) {
            $filter['plane'] = 'Подписка';
            $filter['plane_begin_date'] = 'Дата начала действия подписки';
            $filter['plane_finish_date'] = 'Дата окончания подписки';
            $filter['plane_canceled'] = 'Недавно отменившиее подписку';
            $filter['plane_pay_status'] = 'Статус платежей по подписке';
            $filter['plane_status'] = 'Статус действия подписки';
        }
        $filter = array_merge($filter,  [
            'user_brought_money' => 'Принес денег', // от, до
            'partner_brought_money' => 'Принес денег (как партнер)', // от, до
            'partner_earned_money' => 'Заработал денег (как партнер)', // от, до
            'is_bind_tg' => 'Подключен Telegram', // да/нет
            'is_bind_vk' => 'Подключен ВКонтакте', // да/нет
            'is_mailing_subscribe' => 'Подписан на рассылку',
        ]);
        if (Responder::getDeliveryList(1)) {
            $filter['mailing'] = 'Рассылка';
        }
        $filter = array_merge($filter,  [
            'is_installment' => 'Рассрочка', // да/нет
            'installment_status' => 'Статус платежей по рассрочке',
            'reg_date' => 'Дата регистрации',
            'last_auth' => 'Дата последнего входа',
            'birthday' => 'День Рождения',
            'is_author' => 'Является автором',
            'is_curator' => 'Является куратором',
            'is_client' => 'Является клиентом',
            'is_partner' => 'Является партнером',
            'has_curator' => 'Наличие куратора',
            'has_specific_curator' => 'Наличие конкретного куратора',
        ]);

        if (System::CheckExtensension('training', 1)) {
            //$filter['training_access'] = 'Доступ к тренингу'; // да/нет
            $filter['training'] = 'Прохождение тренинга';
            //$filter['training_lesson_access'] = 'Доступ к уроку'; // да/нет
            $filter['training_lesson'] = 'Прохождение урока';
            $filter['training_lessons'] = 'Прохождение уроков';
            $filter['last_passed_lesson'] = 'Последний пройденный урок'; // да/нет
        }

        if (System::CheckExtensension('learning_flows', 1)) {
            $filter['flow_id'] =  'Поток';
        }

        $custom_fields = CustomFields::getFields();
        if ($custom_fields) {
            foreach ($custom_fields as $custom_field) {
                $filter[$custom_field['column_name']] = $custom_field['field_name'];
            }
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
            case 'user_id':
                $form_elements->addTextInput("user_id[$cond_index]", $value, '', 'Введите ID пользователя');
                break;
            case 'user_email':
                $form_elements->addTextInput("user_email[$cond_index]", $value, '', 'Введите e-mail');
                break;
            case 'user_name':
                $form_elements->addTextInput("user_name[$cond_index]", $value, '', 'Введите имя');
                break;
            case 'user_surname':
                $form_elements->addTextInput("user_surname[$cond_index]", $value, '', 'Введите фамилию');
                break;
            case 'user_patronymic':
                $form_elements->addTextInput("user_patronymic[$cond_index]", $value, '', 'Введите отчество');
                break;
            case 'phone':
                $form_elements->addTextInput("phone[$cond_index]", $value, '', 'Введите телефон');
                break;
            case 'is_has_group':
                $form_elements->addRadio("is_has_group[$cond_index]", $value, '',
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
            case 'status':
                $form_elements->addRadio("status[$cond_index]", $value, '',
                    [
                        [
                            'title' => 'Включен',
                            'value' => 1
                        ],
                        [
                            'title' => 'Выключен',
                            'value' => 0
                        ],
                        [
                            'title' => 'В черном списке',
                            'value' => 6
                        ]
                    ]
                );
                break;
            case 'group':
                $groups = User::getUserGroups();
                $data = [];
                if ($groups) {
                    foreach ($groups as $group) {
                        $data[] = [
                            'title' => $group['group_title'],
                            'value' => $group['group_id']
                        ];
                    }
                }

                $form_elements->addSelect("group[$cond_index]", $value, '', $data);
                break;
            case 'groups':
                $groups = User::getUserGroups();
                $data = [];
                if ($groups) {
                    foreach ($groups as $group) {
                        $data[] = [
                            'title' => $group['group_title'],
                            'value' => $group['group_id']
                        ];
                    }
                }

                $form_elements->addMultiSelect("groups[$cond_index]", $value, '', $data);
                break;
            case 'is_has_subscribe':
                $form_elements->addRadio("is_has_subscribe[$cond_index]", $value, '',
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
            case 'subscribe_actual':
                $form_elements->addRadio("subscribe_actual[$cond_index]", $value, '',
                    [
                        [
                            'title' => 'Действующая',
                            'value' => 1
                        ],
                        [
                            'title' => 'Завершенная',
                            'value' => 0
                        ]
                    ]
                );
                break;
            case 'plane':
                $planes = Member::getPlanes();
                $data = [];
                if ($planes) {
                    foreach ($planes as $plane) {
                        $data[] = [
                            'title' => $plane['name'],
                            'value' => $plane['id']
                        ];
                    }
                }
                $form_elements->addSelect("plane[$cond_index]", $value, '', $data);
                break;
            case 'plane_begin_date':
                $form_elements->addDateInput("plane_begin_date_start[$cond_index]", $start, '', 'От');
                $form_elements->addDateInput("plane_begin_date_end[$cond_index]", $end, '', 'До');
                $form_elements->addGroups(["plane_begin_date_start[$cond_index]", "plane_begin_date_end[$cond_index]"], 'plane_begin_date');
                break;
            case 'plane_finish_date':
                $form_elements->addDateInput("plane_finish_date_start[$cond_index]", $start, '', 'От');
                $form_elements->addDateInput("plane_finish_date_end[$cond_index]", $end, '', 'До');
                $form_elements->addGroups(["plane_finish_date_start[$cond_index]", "plane_finish_date_end[$cond_index]"], 'plane_finish_date');
                break;
            case 'plane_canceled':
                $form_elements->addSelect("plane_canceled[$cond_index]", $value, '', self::getDateFilters(), 'date-options main-option');

                $form_elements->addTextInput("plane_canceled_start[$cond_index]", $start, '', 'от', 'date-options');
                $form_elements->addTextInput("plane_canceled_end[$cond_index]", $end, '', 'до', 'date-options');

                $form_elements->addDependency("plane_canceled[$cond_index]", 'n_days_ago',
                    ["plane_canceled_start[$cond_index]", "plane_canceled_end[$cond_index]"]
                );
                $form_elements->addDependency("plane_canceled[$cond_index]", 'n_hours_ago',
                    ["plane_canceled_start[$cond_index]", "plane_canceled_end[$cond_index]"]
                );

                $form_elements->addDateInput("plane_canceled_start_date[$cond_index]", $start_date, '', 'с');
                $form_elements->addDateInput("plane_canceled_end_date[$cond_index]", $end_date, '', 'по');

                $form_elements->addDependency("plane_canceled[$cond_index]", 'period',
                    ["plane_canceled_start_date[$cond_index]", "plane_canceled_end_date[$cond_index]"]
                );
                break;
            case 'plane_pay_status':
                $data = [
                    [
                        'title' => 'Активные',
                        'value' => 1,
                    ],
                    [
                        'title' => 'Отменены',
                        'value' => 0,
                    ]
                ];
                $form_elements->addSelect("plane_pay_status[$cond_index]", $value, '', $data);
                break;
            case 'plane_status':
                $form_elements->addRadio("plane_status[$cond_index]", $value, '',
                    [
                        [
                            'title' => 'Включен',
                            'value' => 1
                        ],
                        [
                            'title' => 'Отключен',
                            'value' => 0
                        ]
                    ]
                );
                break;
            case 'vk':
                $form_elements->addTextInput("vk[$cond_index]", $value, '', 'Введите адрес страницы в ВКонтакте');
                break;
            case 'tg':
                $form_elements->addTextInput("tg[$cond_index]", $value, '', 'Введите Telegram');
                break;
            case 'ig':
                $form_elements->addTextInput("ig[$cond_index]", $value, '', 'Введите Instagram');
                break;
            case 'user_role':
                $roles = User::getRoleUser();
                $data = [];
                foreach ($roles as $role => $title) {
                    $data[] = [
                        'title' => $title,
                        'value' => $role
                    ];
                }

                $form_elements->addSelect("user_role[$cond_index]", $value, '', $data);
                break;
            case 'user_brought_money':
                $form_elements->addTextInput("user_brought_money_start[$cond_index]", $start,'', 'от', 'inline-block');
                $form_elements->addTextInput("user_brought_money_end[$cond_index]",  $end, '', 'до', 'inline-block');
                break;
            case 'partner_brought_money':
                $form_elements->addTextInput("partner_brought_money_start[$cond_index]", $start,'', 'от', 'inline-block');
                $form_elements->addTextInput("partner_brought_money_end[$cond_index]",  $end, '', 'до', 'inline-block');
                break;
            case 'partner_earned_money':
                $form_elements->addTextInput("partner_earned_money_start[$cond_index]", $start,'', 'от', 'inline-block');
                $form_elements->addTextInput("partner_earned_money_end[$cond_index]",  $end, '', 'до', 'inline-block');
                break;
            case 'is_bind_tg':
                $form_elements->addRadio("is_bind_tg[$cond_index]", $value, '',
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
            case 'is_bind_vk':
                $form_elements->addRadio("is_bind_vk[$cond_index]", $value, '',
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
            case 'is_author':
                $form_elements->addRadio("is_author[$cond_index]", $value, '',
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
            case 'is_curator':
                $form_elements->addRadio("is_curator[$cond_index]", $value, '',
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
            case 'is_client':
                $form_elements->addRadio("is_client[$cond_index]", $value, '',
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
            case 'is_partner':
                $form_elements->addRadio("is_partner[$cond_index]", $value, '',
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
            case 'has_curator':
                $form_elements->addRadio("has_curator[$cond_index]", $value, '',
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
            case 'has_specific_curator':
                $curators = User::getCurators();
                $data = [];
                if ($curators) {
                    foreach ($curators as $curator) {
                        $curator_name = $curator['surname'] ? "{$curator['user_name']} {$curator['surname']}" : $curator['user_name'];
                        $data[] = [
                            'title' => $curator_name,
                            'value' => $curator['user_id']
                        ];
                    }
                }
                $form_elements->addSelect("has_specific_curator[$cond_index]", $value, '', $data);
                break;
            case 'last_auth':
                $form_elements->addSelect("last_auth[$cond_index]", $value, '', self::getDateFilters(), 'date-options main-option');

                $form_elements->addTextInput("last_auth_start[$cond_index]", $start, '', 'от', 'date-options');
                $form_elements->addTextInput("last_auth_end[$cond_index]", $end, '', 'до', 'date-options');

                $form_elements->addDependency("last_auth[$cond_index]", 'n_days_ago',
                    ["last_auth_start[$cond_index]", "last_auth_end[$cond_index]"]
                );
                $form_elements->addDependency("last_auth[$cond_index]", 'n_hours_ago',
                    ["last_auth_start[$cond_index]", "last_auth_end[$cond_index]"]
                );

                $form_elements->addDateInput("last_auth_start_date[$cond_index]", $start_date, '', 'с');
                $form_elements->addDateInput("last_auth_end_date[$cond_index]", $end_date, '', 'по');

                $form_elements->addDependency("last_auth[$cond_index]", 'period',
                    ["last_auth_start_date[$cond_index]", "last_auth_end_date[$cond_index]"]
                );
                break;
            case 'birthday':
                $months = [
                    [
                        'title' => 'Месяц',
                        'value' => null,
                    ]
                ];
                $days = [
                    [
                        'title' => 'День',
                        'value' => null,
                    ]
                ];

                for ($day = 1; $day < 32; $day++) {
                    $days[] = [
                        'title' => $day,
                        'value' => $day
                    ];
                    if ($day < 13) {
                        $months[] = [
                            'title' => $day,
                            'value' => $day
                        ];
                    }
                }

                $birthday_month = isset($segment_data['birthday_month'][$cond_index]) ? (int)$segment_data['birthday_month'][$cond_index] : null;
                $birthday_day = isset($segment_data['birthday_day'][$cond_index]) ? (int)$segment_data['birthday_day'][$cond_index] : null;

                $form_elements->addSelect("birthday_month[$cond_index]", $birthday_month, 'Месяц', $months, 'inline-block');
                $form_elements->addSelect("birthday_day[$cond_index]", $birthday_day, 'День', $days, 'inline-block mr-13');
                break;
            case 'reg_date':
                $form_elements->addSelect("reg_date[$cond_index]", $value, '', self::getDateFilters(), 'date-options main-option');

                $form_elements->addTextInput("reg_date_start[$cond_index]", $start, '', 'от', 'date-options');
                $form_elements->addTextInput("reg_date_end[$cond_index]", $end, '', 'до', 'date-options');

                $form_elements->addDependency("reg_date[$cond_index]", 'n_days_ago',
                    ["reg_date_start[$cond_index]", "reg_date_end[$cond_index]"]
                );
                $form_elements->addDependency("reg_date[$cond_index]", 'n_hours_ago',
                    ["reg_date_start[$cond_index]", "reg_date_end[$cond_index]"]
                );

                $form_elements->addDateInput("reg_date_start_date[$cond_index]", $start_date, '', 'с');
                $form_elements->addDateInput("reg_date_end_date[$cond_index]", $end_date, '', 'по');

                $form_elements->addDependency("reg_date[$cond_index]", 'period',
                    ["reg_date_start_date[$cond_index]", "reg_date_end_date[$cond_index]"]
                );
                break;
            case 'is_mailing_subscribe':
                $form_elements->addRadio("is_mailing_subscribe[$cond_index]", $value, '',
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
            case 'is_installment':
                $form_elements->addRadio("is_installment[$cond_index]", $value, '',
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
            case 'installment_status':
                $statuses = Installment::getStatuses();
                $data = [];

                foreach ($statuses as $status => $title) {
                    $data[] = [
                        'title' => $title,
                        'value' => $status
                    ];
                }
                $form_elements->addSelect("installment_status[$cond_index]", $value, '', $data);
                break;
            case 'mailing':
                $mailings = Responder::getDeliveryList(2);
                $data = [];
                if ($mailings) {
                    foreach ($mailings as $mailing) {
                        $data[] = [
                            'title' => $mailing['name'],
                            'value' => $mailing['delivery_id']
                        ];
                    }
                    $form_elements->addSelect("mailing[$cond_index]", $value, '', $data);
                }
                break;
            case 'training_access':
                $trainings = Training::getTrainingList();
                if (!$trainings) {
                    break;
                }

                $data = [
                    [
                        'title' => 'Выбрать тренинг',
                        'value' => null
                    ],
                ];

                foreach ($trainings as $training) {
                    $data[] = [
                        'title' => $training['name'],
                        'value' => $training['training_id']
                    ];
                }

                $training_id = isset($segment_data['training_access_training_id'][$cond_index]) ? (int)$segment_data['training_access_training_id'][$cond_index] : null;
                $form_elements->addSelect("training_access_training_id[$cond_index]", $training_id, '', $data);

                $form_elements->addRadio("training_access[$cond_index]", $value, '',
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
            case 'training':
                $trainings = Training::getTrainingList();
                if (!$trainings) {
                    break;
                }

                $data = [
                    [
                        'title' => 'Выбрать тренинг',
                        'value' => null
                    ],
                ];

                foreach ($trainings as $training) {
                    $data[] = [
                        'title' => $training['name'],
                        'value' => $training['training_id']
                    ];
                }
                $form_elements->addSelect("training[$cond_index]", $value, '', $data);

                $data = [
                    [
                        'title' => 'Выбрать статус',
                        'value' => 0
                    ],
                    [
                        'title' => 'Ни разу не входили в курс',
                        'value' => 2
                    ],
                    [
                        'title' => 'Не заходили x дней',
                        'value' => 'training_not_enter_xdays'
                    ],
                    [
                        'title' => 'Заходили, но не проходили уроки',
                        'value' => 3
                    ],
                    [
                        'title' => 'Закончил',
                        'value' => 5
                    ],
                ];
                $_value = isset($segment_data['training_status'][$cond_index]) ? $segment_data['training_status'][$cond_index] : null;
                $form_elements->addSelect("training_status[$cond_index]", $_value, '', $data);

                $_value = isset($segment_data['training_not_enter_xdays'][$cond_index]) ? $segment_data['training_not_enter_xdays'][$cond_index] : null;
                $form_elements->addTextInput("training_not_enter_xdays[$cond_index]", $_value, '', 'Введите кол-во дней', 'mt-10');

                $form_elements->addDependency("training_status[$cond_index]", 'training_not_enter_xdays',
                    ["training_not_enter_xdays[$cond_index]"]
                );
                break;
            case 'training_lesson_access':
                $trainings = Training::getTrainingList();
                if (!$trainings) {
                    break;
                }

                $data = [
                    [
                        'title' => 'Выбрать тренинг',
                        'value' => null
                    ],
                ];

                foreach ($trainings as $training) {
                    $data[] = [
                        'title' => $training['name'],
                        'value' => $training['training_id']
                    ];
                }

                $training_id = isset($segment_data['training_lesson_access_training_id'][$cond_index])
                    ? (int)$segment_data['training_lesson_access_training_id'][$cond_index] : null;
                $form_elements->addSelect("training_lesson_access_training_id[$cond_index]", $training_id, '', $data);

                $lessons = $training_id ? TrainingLesson::getLessons($training_id) : [];
                $data = [
                    [
                        'title' => 'Выбрать урок',
                        'value' => null
                    ],
                ];

                if ($lessons) {
                    foreach ($lessons as $lesson) {
                        $data[] = [
                            'title' => $lesson['name'],
                            'value' => $lesson['lesson_id']
                        ];
                    }
                }

                $lesson_id = isset($segment_data['training_lesson_access_lesson_id'][$cond_index]) ? (int)$segment_data['training_lesson_access_lesson_id'][$cond_index] : null;
                $form_elements->addSelect("training_lesson_access_lesson_id[$cond_index]", $lesson_id, '', $data);

                $form_elements->addRadio("training_lesson_access[$cond_index]", $value, '',
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
            case 'training_lesson':
                $trainings = Training::getTrainingList();
                if (!$trainings) {
                    break;
                }

                $data = [
                    [
                        'title' => 'Выбрать тренинг',
                        'value' => null
                    ],
                ];

                foreach ($trainings as $training) {
                    $data[] = [
                        'title' => $training['name'],
                        'value' => $training['training_id']
                    ];
                }

                $training_id = isset($segment_data['training_lesson_training_id'][$cond_index]) ? (int)$segment_data['training_lesson_training_id'][$cond_index] : null;
                $form_elements->addSelect("training_lesson_training_id[$cond_index]", $training_id, '', $data);

                $lessons = $training_id ? TrainingLesson::getLessons($training_id) : [];
                $data = [
                    [
                        'title' => 'Выбрать урок',
                        'value' => null
                    ],
                ];

                if ($lessons) {
                    foreach ($lessons as $lesson) {
                        $data[] = [
                            'title' => $lesson['name'],
                            'value' => $lesson['lesson_id']
                        ];
                    }
                }
                $form_elements->addSelect("training_lesson[$cond_index]", $value, '', $data);

                $data = [
                    [
                        'title' => 'Состояние урока',
                        'value' => null
                    ],
                    [
                        'title' => 'Прошел урок',
                        'value' => 3
                    ],
                    [
                        'title' => 'Отправил дз на проверку',
                        'value' => 1
                    ],
                    [
                        'title' => 'Не проходил тест',
                        'value' => 4
                    ],
                    [
                        'title' => 'Не сдал тест',
                        'value' => 5
                    ],
                    [
                        'title' => 'Получил незачет',
                        'value' => 2
                    ],
                ];
                $lesson_status = isset($segment_data['training_lesson_status'][$cond_index]) ? $segment_data['training_lesson_status'][$cond_index] : null;
                $form_elements->addSelect("training_lesson_status[$cond_index]", $lesson_status, '', $data);
                break;
            case 'training_lessons':
                $trainings = Training::getTrainingList();
                if (!$trainings) {
                    break;
                }

                $data = [
                    [
                        'title' => 'Выбрать тренинг',
                        'value' => null
                    ],
                ];

                foreach ($trainings as $training) {
                    $data[] = [
                        'title' => $training['name'],
                        'value' => $training['training_id']
                    ];
                }

                $training_id = isset($segment_data['training_lessons_training_id'][$cond_index]) ? (int)$segment_data['training_lessons_training_id'][$cond_index] : null;
                $form_elements->addSelect("training_lessons_training_id[$cond_index]", $training_id, '', $data);

                $lessons = $training_id ? TrainingLesson::getLessons($training_id) : [];
                $data = [
                    [
                        'title' => 'Выбрать уроки',
                        'value' => null
                    ],
                ];

                if ($lessons) {
                    foreach ($lessons as $lesson) {
                        $data[] = [
                            'title' => $lesson['name'],
                            'value' => $lesson['lesson_id']
                        ];
                    }
                }
                $form_elements->addMultiSelect("training_lessons[$cond_index]", $value, '', $data);

                $data = [
                    [
                        'title' => 'Состояние урока',
                        'value' => null
                    ],
                    [
                        'title' => 'Прошел урок',
                        'value' => 3
                    ],
                    [
                        'title' => 'Отправил дз на проверку',
                        'value' => 1
                    ],
                    [
                        'title' => 'Не проходил тест',
                        'value' => 4
                    ],
                    [
                        'title' => 'Не сдал тест',
                        'value' => 5
                    ],
                    [
                        'title' => 'Получил незачет',
                        'value' => 2
                    ],
                ];
                $lesson_status = isset($segment_data['training_lessons_status'][$cond_index]) ? $segment_data['training_lessons_status'][$cond_index] : null;
                $form_elements->addSelect("training_lessons_status[$cond_index]", $lesson_status, '', $data);
                break;
            case 'last_passed_lesson':
                $form_elements->addSelect("last_passed_lesson[$cond_index]", $value, '', self::getDateFilters(), 'date-options main-option');

                $form_elements->addTextInput("last_passed_lesson_start[$cond_index]", $start, '', 'от', 'date-options');
                $form_elements->addTextInput("last_passed_lesson_end[$cond_index]", $end, '', 'до', 'date-options');

                $form_elements->addDependency("last_passed_lesson[$cond_index]", 'n_days_ago',
                    ["last_passed_lesson_start[$cond_index]", "last_passed_lesson_end[$cond_index]"]
                );
                $form_elements->addDependency("last_passed_lesson[$cond_index]", 'n_hours_ago',
                    ["last_passed_lesson_start[$cond_index]", "last_passed_lesson_end[$cond_index]"]
                );

                $form_elements->addDateInput("last_passed_lesson_start_date[$cond_index]", $start_date, '', 'с');
                $form_elements->addDateInput("last_passed_lesson_end_date[$cond_index]", $end_date, '', 'по');

                $form_elements->addDependency("last_passed_lesson[$cond_index]", 'period',
                    ["last_passed_lesson_start_date[$cond_index]", "last_passed_lesson_end_date[$cond_index]"]
                );
                break;
            case 'count_subs_renewals':
                $form_elements->addTextInput("count_subs_renewals_start[$cond_index]", $start, '', 'От', 'inline-block');
                $form_elements->addTextInput("count_subs_renewals_end[$cond_index]", $end, '', 'До', 'inline-block');
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
        }

        if (in_array($type, ['last_auth', 'reg_date', 'plane_canceled', 'last_passed_lesson']) && $value != 'period') {
            $additional_info = self::getDateText2Filter($value, $start, $end);
            $form_elements->getElement("{$type}[$cond_index]")->setAdditionalInfo($additional_info);
        }

        if (strpos($type, 'custom_field_') === 0) {
            $custom_field = CustomFields::getDataFieldByColumnName(htmlentities($type));

            if ($custom_field) {
                $custom_data = CustomFields::getDataField($custom_field['id']);
                $params = json_decode($custom_data['params']);
                $data = [];

                if (!in_array($custom_field['field_type'], [CustomFields::FIELD_TYPE_TEXT, CustomFields::FIELD_TYPE_TEXTAREA])) {
                    foreach ($params as $custom_field_value => $title) {
                        $data[] = [
                            'title' => $title,
                            'value' => $custom_field_value
                        ];
                    }
                }

                if ($data) {
                    if (in_array($custom_field['field_type'], [CustomFields::FIELD_TYPE_CHECKBOX, CustomFields::FIELD_TYPE_MULTI_SELECT])) {
                        $form_elements->addMultiSelect("{$type}[$cond_index]", $value, '', $data);
                    } else {
                        $form_elements->addSelect("{$type}[$cond_index]", $value, '', $data);
                    }
                } else {
                    $form_elements->addTextInput("{$type}[$cond_index]", $value, '', "Введите {$custom_field['field_name']}");
                }
            }
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
        $value = isset($data[$condition_type][$cond_index]) ? $data[$condition_type][$cond_index] : null;
        if ($value) {
            $value = !is_array($value) ? htmlentities(trim($data[$condition_type][$cond_index])) : System::getSecureData($value, 'string');
        }

        switch ($condition_type) {
            case 'user_email':
                $condition = "u.email LIKE '%$value%'";
                break;
            case 'user_name':
                $condition = "u.user_name LIKE '%$value%'";
                break;
            case 'user_surname':
                $condition = "u.surname LIKE '%$value%'";
                break;
            case 'user_patronymic':
                $condition = "u.patronymic LIKE '%$value%'";
                break;
            case 'phone':
                $condition = "u.phone LIKE '%$value%'";
                break;
            case 'is_has_group':
                $condition = $value ? 'ugm.group_id > 0' : 'u.user_id NOT IN (SELECT DISTINCT user_id FROM '.PREFICS.'user_groups_map)';
                break;
            case 'group':
                $condition = 'u.user_id IN (SELECT DISTINCT user_id FROM '.PREFICS.'user_groups_map WHERE group_id = '.(int)$value.')';
                break;
            case 'groups':
                if (isset($data['groups'][$cond_index])) {
                    $groups = $data['groups'][$cond_index] ? htmlentities(implode(',',$data['groups'][$cond_index])) : '';
                    $count_groups = count($data['groups'][$cond_index]);
                    $condition = 'u.user_id IN (SELECT DISTINCT user_id FROM '.PREFICS."user_groups_map WHERE group_id IN ($groups) GROUP BY user_id HAVING COUNT(group_id) = $count_groups)";
                }
                break;
            case 'is_has_subscribe':
                $condition = $value ? 'mm.subs_id > 0' : 'u.user_id NOT IN (SELECT DISTINCT user_id FROM '.PREFICS.'member_maps)';
                break;
            case 'subscribe_actual':
                $condition = $value ? '(mm.status = 1 AND mm.end > '.time().')' : '(mm.status <> 1 OR mm.end <= '.time().')';
                break;
            case 'plane':
                $condition = 'mm.subs_id = '.(int)$value;
                break;
            case 'status':
                if ($value == 6) {
                    $condition = "ub.email = u.email";
                } else {
                    $condition = "u.status = '$value'";
                }
                break;
            case 'plane_begin_date':
                if ($data['plane_begin_date_start'][$cond_index] || $data['plane_begin_date_end'][$cond_index]) {
                    if ($data['plane_begin_date_start'][$cond_index]) {
                        $condition[] = 'mm.begin >= '.strtotime($data['plane_begin_date_start'][$cond_index]);
                    }
                    if ($data['plane_begin_date_end'][$cond_index]) {
                        $condition[] = 'mm.begin <= '.strtotime($data['plane_begin_date_end'][$cond_index]);
                    }
                }
                break;
            case 'plane_finish_date':
                if ($data['plane_finish_date_start'][$cond_index] || $data['plane_finish_date_end'][$cond_index]) {
                    if ($data['plane_finish_date_start'][$cond_index]) {
                        $condition[] = 'mm.end >= '.strtotime($data['plane_finish_date_start'][$cond_index]);
                    }
                    if ($data['plane_finish_date_end'][$cond_index]) {
                        $condition[] = 'mm.end <= '.strtotime($data['plane_finish_date_end'][$cond_index]);
                    }
                }
                break;
            case 'plane_canceled':
                $key1 = $value != 'period' ? 'plane_canceled_start' : 'plane_canceled_start_date';
                $key2 = $value != 'period' ? 'plane_canceled_end' : 'plane_canceled_end_date';
                $date = self::getDate2Filter($data['plane_canceled'][$cond_index], htmlentities($data[$key1][$cond_index]),
                    htmlentities($data[$key2][$cond_index])
                );
                if ($date) {
                    $condition[] = 'mm.rec_cancelled_date >= '.(int)$date[0];
                    $condition[] = 'mm.rec_cancelled_date <= '.(int)$date[1];
                }
                break;
            case 'plane_pay_status':
                if ($value) {
                    $condition[] = "((mm.recurrent_cancelled = 0 OR mm.recurrent_cancelled IS NULL) AND mm.subscription_id IS NOT NULL AND mm.subscription_id > 0)";
                } else {
                    $condition[] = "mm.recurrent_cancelled = 1";
                }
                break;
            case 'plane_status':
                $condition[] = 'mm.status = '.(int)$value;
                break;
            case 'vk':
                $condition = "u.vk_url LIKE '%$value%'";
                break;
            case 'tg':
                $condition = "u.nick_telegram LIKE '%$value%'";
                break;
            case 'ig':
                $condition = "u.nick_instagram LIKE '%$value%'";
                break;
            case 'user_id':
                $condition = 'u.user_id = '.(int)$value;
                break;
            case 'user_role':
                $condition = "u.role = '$value'";
                break;
            case 'user_brought_money':
                if ($data['user_brought_money_start'][$cond_index]) {
                    $condition[] = 'osc.orders_sum >= '.(int)$data['user_brought_money_start'][$cond_index];
                }
                if ($data['user_brought_money_end'][$cond_index]) {
                    $condition[] = 'osc.orders_sum <= '.(int)$data['user_brought_money_end'][$cond_index];
                }
                $condition[] = '(osc.partner_id IS NULL OR osc.partner_id = 0)';
                break;
            case 'partner_brought_money':
                if ($data['partner_brought_money_start'][$cond_index]) {
                    $condition[] = 'osp.orders_sum >= '.(int)$data['partner_brought_money_start'][$cond_index];
                }
                if ($data['partner_brought_money_end'][$cond_index]) {
                    $condition[] = 'osp.orders_sum <= '.(int)$data['partner_brought_money_end'][$cond_index];
                }
                $condition[] = "u.is_partner = 1 AND osp.partner_id IS NOT NULL AND osp.partner_id > 0";
                break;
            case 'partner_earned_money':
                $_condition = [];
                if ($data['partner_earned_money_start'][$cond_index]) {
                    $_condition[] = 'SUM(summ) >= '.(int)$data['partner_earned_money_start'][$cond_index];
                }
                if ($data['partner_earned_money_end'][$cond_index]) {
                    $_condition[] = 'SUM(summ) <= '.(int)$data['partner_earned_money_end'][$cond_index];
                }
                $str_condition = 'SELECT DISTINCT user_id FROM '.PREFICS.'aff_transaction'
                .(!empty($_condition) ? ' GROUP BY user_id HAVING '.implode(' AND ', $_condition) : '');
                $condition = "u.is_partner = 1 AND u.user_id IN ($str_condition)";
                break;
            case 'last_auth':
                $key1 = $value != 'period' ? 'last_auth_start' : 'last_auth_start_date';
                $key2 = $value != 'period' ? 'last_auth_end' : 'last_auth_end_date';
                $date = self::getDate2Filter($data['last_auth'][$cond_index], htmlentities($data[$key1][$cond_index]),
                    htmlentities($data[$key2][$cond_index])
                );
                $where = $date ? "WHERE auth_date >= {$date[0]} AND auth_date <= {$date[1]}" : '';
                $_condition = 'SELECT DISTINCT user_id FROM '.PREFICS."user_sessions $where";
                $condition = "u.user_id IN($_condition)";
                break;
            case 'birthday':
                $birthday_day = (int)$data['birthday_day'][$cond_index];
                $birthday_month = (int)$data['birthday_month'][$cond_index];
                if ($birthday_day) {
                    $condition[] = "u.bith_day = $birthday_day";
                }
                if ($birthday_month) {
                    $condition[] = "u.bith_month = $birthday_month";
                }
                if (!$condition) {
                    $condition[] = "u.user_id > 0";
                }
                break;
            case 'reg_date':
                $key1 = $value != 'period' ? 'reg_date_start' : 'reg_date_start_date';
                $key2 = $value != 'period' ? 'reg_date_end' : 'reg_date_end_date';
                $date = self::getDate2Filter($data['reg_date'][$cond_index], htmlentities($data[$key1][$cond_index]),
                    htmlentities($data[$key2][$cond_index])
                );
                if ($date) {
                    $condition[] = 'u.reg_date >= '.$date[0];
                    $condition[] = 'u.reg_date <= '.$date[1];
                }
                break;
            case 'is_bind_tg':
                $condition = '(cu.tg_id '.($value ? ' > 0' : 'IS NULL OR cu.tg_id  = 0').')';
                break;
            case 'is_bind_vk':
                $condition = '(cu.vk_id '.($value ? ' > 0' : 'IS NULL OR cu.vk_id  = 0').')';
                break;
            case 'is_author':
                $condition = '(u.is_author '.($value ? '= 1' : 'IS NULL OR u.is_author = 0').')';
                break;
            case 'is_curator':
                $condition = '(u.is_curator '.($value ? '= 1' : 'IS NULL OR u.is_curator = 0').')';
                break;
            case 'is_partner':
                $condition = '(u.is_partner '.($value ? '= 1' : 'IS NULL OR u.is_partner = 0').')';
                break;
            case 'is_client':
                $condition = '(u.is_client '.($value ? '= 1' : 'IS NULL OR u.is_client = 0').')';
                break;
            case 'is_mailing_subscribe':
                $_condition = 'SELECT DISTINCT email FROM '.PREFICS.'email_subs_map';
                $condition = 'u.email '.($value ? 'IN' : 'NOT IN')." ($_condition)";
                break;
            case 'is_installment':
                $condition = $value ? 'o.installment_map_id > 0' : 'o.installment_map_id = 0';
                break;
            case 'has_curator':
                $_condition = 'SELECT DISTINCT user_id FROM '.PREFICS.'training_curator_to_user';
                $condition = 'u.user_id '.($value ? 'IN' : 'NOT IN')." ($_condition)";
                break;
            case 'has_specific_curator':
                $_condition = 'SELECT DISTINCT user_id FROM '.PREFICS.'training_curator_to_user WHERE curator_id = '.(int)$value;
                $condition = "u.user_id IN ($_condition)";
                break;
            case 'mailing':
                $_condition = 'SELECT DISTINCT email FROM '.PREFICS.'email_subs_map WHERE delivery_id = '.(int)$value;
                $condition = "u.email IN ($_condition)";
                break;
            case 'installment_status':
                $_condition = 'SELECT DISTINCT email FROM '.PREFICS.'installment_map WHERE status = '.(int)$value;
                $condition = "u.email IN ($_condition)";
                break;
            case 'training_access':
                $training_id = isset($data['training_access_training_id'][$cond_index]) ? (int)$data['training_access_training_id'][$cond_index] : null;
                $training = $training_id ? Training::getTraining($training_id) : null;
                if (!$training) {
                    $condition[] = 'u.user_id > 0';
                    break;
                }

                if ($training['access_type'] == 0) { // свободный доступ
                    $condition[] = $value ? 'u.user_id > 0' : 'u.user_id = 0';
                } elseif($training['access_type'] == 1) { // по группе
                    $groups = $training['access_groups'] ? implode(',', json_decode($training['access_groups'], true) ) : 0;
                    $condition[] = $value ? "ugm.group_id IN ($groups)" : "ugm.group_id NOT IN ($groups)";
                } else { // по подписке
                    $planes = $training['access_planes'] ? implode(',', json_decode($training['access_planes'], true) ) : 0;
                    $condition[] = $value ? "mm.subs_id IN ($planes)" : "mm.subs_id NOT IN ($planes)";
                }
                break;
            case 'training':
            case 'training_status':
                $training_id = (int)$value;
                $not_enter_time = isset($data['training_not_enter_xdays'][$cond_index]) ? time() - (int)$data['training_not_enter_xdays'][$cond_index] * 86400 : time();

                if (isset($data['training_status'][$cond_index]) && $data['training_status'][$cond_index] !== null) {
                    switch ($data['training_status'][$cond_index]) {
                        case 2: // не входили в курс
                            $condition[] = "u.user_id NOT IN (SELECT DISTINCT user_id FROM ".PREFICS."training_user_visits WHERE training_id = $training_id)";
                            break;
                        case 3: // Заходили, но не проходили уроки
                            $condition[] = "u.user_id IN (SELECT DISTINCT user_id FROM ".PREFICS."training_user_visits WHERE training_id = $training_id)";
                            $condition[] = "u.user_id NOT IN (SELECT DISTINCT user_id FROM ".PREFICS."training_user_map WHERE training_id = $training_id AND status = 3)";
                            break;
                        case 'training_not_enter_xdays': // Не заходили x дней
                            $condition[] = "u.user_id NOT IN (SELECT DISTINCT user_id FROM ".PREFICS."training_user_visits WHERE training_id = $training_id AND date > $not_enter_time)";
                            break;
                        case 5: // Закончил
                            $training = $training_id ? Training::getTraining($training_id) : null;
                            if ($training) {
                                if ($training['finish_type'] == Training::FINISH_TYPE_DATE) {
                                    $condition[] = $training['end_date'] <= time() ? 'u.user_id > 0' : 'u.user_id = 0';
                                } else {
                                    $condition[] = "tuc.training_id = $training_id";
                                }
                            } else {
                                $condition[] = "tuc.training_id > 0";
                            }
                            break;
                        default:
                            $condition[] = 'u.user_id > 0';
                    }
                }
                break;
            case 'training_lesson_access':
                $lesson_id = (int)$data['training_lesson_access_lesson_id'][$cond_index];
                $lesson = TrainingLesson::getLesson($lesson_id);
                $access_data = TrainingLesson::getAccessData($lesson);
                if(isset($access_data['groups']) && $access_data['groups']) {
                    $condition[] = 'ugm.group_id '.($value ? 'IN(' : 'NOT IN(').implode(',', $access_data['groups']).')';
                } elseif(isset($access_data['planes']) && $access_data['planes']) {
                    $condition[] = 'mm.subs_id '.($value ? 'IN(' : 'NOT IN(').implode(',', $access_data['planes']).')';
                } else {
                    $condition[] = $value && $access_data ? 'u.user_id > 0' : 'u.user_id = 0';
                }
                break;
            case 'training_lesson':
            case 'training_lesson_status':
            if (isset($data['training_lesson'][$cond_index]) && $data['training_lesson'][$cond_index] !== '') {
                $training_id = (int)$data['training_lesson_training_id'][$cond_index];
                $lesson_id = (int)$data['training_lesson'][$cond_index];

                if (isset($data['training_lesson_status'][$cond_index]) && $data['training_lesson_status'][$cond_index] !== '') {
                    $lesson_status = (int)$data['training_lesson_status'][$cond_index];
                    if (in_array($lesson_status, [4,5])) { // 4- не проходил тест, 5 - не сдал тест
                        if ($lesson_status == 4) {
                            $condition[] = 'u.user_id NOT IN(SELECT user_id FROM '.PREFICS."training_home_work WHERE lesson_id = $lesson_id AND test IS NULL)";
                        } else {
                            $condition[] = 'u.user_id IN(SELECT user_id FROM '.PREFICS."training_home_work WHERE lesson_id = $lesson_id AND test = 0)";
                        }
                    } else {
                        $condition[] = "tum.lesson_id = $lesson_id AND tum.status = $lesson_status";
                    }
                }
            }
                break;
            case 'training_lessons':
            case 'training_lessons_status':
                if (isset($data['training_lessons'][$cond_index]) && $data['training_lessons'][$cond_index] !== '') {
                    $training_id = (int)$data['training_lessons_training_id'][$cond_index];
                    $lessons = $data['training_lessons'][$cond_index] ? htmlentities(implode(',',$data['training_lessons'][$cond_index])) : '';
                    $count_lessons = count($data['training_lessons'][$cond_index]);

                    if (isset($data['training_lessons_status'][$cond_index]) && $data['training_lessons_status'][$cond_index] !== '') {
                        $lesson_status = (int)$data['training_lessons_status'][$cond_index];
                        if (in_array($lesson_status, [4,5])) { // 4- не проходил тест, 5 - не сдал тест
                            if ($lesson_status == 4) {
                                $condition[] = 'u.user_id NOT IN(SELECT user_id FROM '.PREFICS."training_home_work WHERE lesson_id IN($lessons) AND test IS NULL GROUP BY user_id HAVING COUNT(user_id) = $count_lessons)";
                            } else {
                                $condition[] = 'u.user_id IN(SELECT user_id FROM '.PREFICS."training_home_work WHERE lesson_id IN($lessons) AND test = 0 GROUP BY user_id HAVING COUNT(user_id) = $count_lessons)";
                            }
                        } else {
                            $condition[] = 'u.user_id IN(SELECT user_id FROM '.PREFICS."training_user_map WHERE lesson_id IN($lessons) AND status = $lesson_status GROUP BY user_id HAVING COUNT(user_id) = $count_lessons)";
                        }
                    }
                }
                break;
            case 'last_passed_lesson':
                $key1 = $value != 'period' ? 'last_passed_lesson_start' : 'last_passed_lesson_start_date';
                $key2 = $value != 'period' ? 'last_passed_lesson_end' : 'last_passed_lesson_end_date';
                $date = self::getDate2Filter($data['last_passed_lesson'][$cond_index], htmlentities($data[$key1][$cond_index]),
                    htmlentities($data[$key2][$cond_index])
                );
                
                if ($date) {
                    $condition[] = 'tum.status = 3';
                    $condition[] = 'tum.date >= '.$date[0];
                    $condition[] = 'tum.date <= '.$date[1];
                }
                break;
            case 'count_subs_renewals':
                if ($data['count_subs_renewals_start'][$cond_index]) {
                    $condition[] = 'mm.update_count >= '.intval($data['count_subs_renewals_start'][$cond_index]);
                }
                if ($data['count_subs_renewals_end'][$cond_index]) {
                    $condition[] = 'mm.update_count <= '.intval($data['count_subs_renewals_end'][$cond_index]);
                }
                break;
            case 'flow_id':
                $condition[] = 'u.user_id IN(SELECT user_id FROM '.PREFICS.'flows_maps WHERE flow_id = '.(int)$value.')';
                break;
        }

        if (strpos($condition_type, 'custom_field_') === 0 && $value !== null) {
            $condition_type = htmlentities($condition_type);
            $custom_field = CustomFields::getDataFieldByColumnName(htmlentities($condition_type));
            $clause = null;

            if (is_array($value) && $value && in_array($custom_field['field_type'], [CustomFields::FIELD_TYPE_CHECKBOX, CustomFields::FIELD_TYPE_MULTI_SELECT])) {
                $clause = "$condition_type LIKE '";

                foreach ($value as $item) {
                    $clause .= "%\"$item\"%";
                }
                $clause .= "'";
            } else {
                $clause = "$condition_type LIKE '%$value%'";
            }

            if ($clause) {
                $condition = 'u.user_id IN (SELECT user_id FROM '.PREFICS."custom_fields WHERE $clause)";
            }
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
        $result = $db->prepare('INSERT INTO '.PREFICS.'users_filter_segments (segment_id, segment_name, data)
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
        $result = $db->prepare('UPDATE '.PREFICS.'users_filter_segments SET data = :data WHERE segment_id = :segment_id'
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
        $result = $db->prepare('DELETE FROM '.PREFICS.'users_filter_segments WHERE segment_id = :segment_id');
        $result->bindParam(':segment_id', $segment_id, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @return mixed
     */
    public static function getMaxSegmentId() {
        $db = Db::getConnection();
        $result = $db->query('SELECT MAX(segment_id) FROM '.PREFICS.'users_filter_segments');
        $data = $result->fetch();

        return $data[0];
    }


    /***
     * @param $id
     * @return bool|mixed
     */
    public static function getSegment($id) {
        $db = Db::getConnection();
        $result = $db->prepare('SELECT * FROM '.PREFICS.'users_filter_segments WHERE segment_id = :segment_id');
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
        $result = $db->query('SELECT * FROM '.PREFICS.'users_filter_segments ORDER BY segment_id DESC');
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data ? $data : false;
    }
}
