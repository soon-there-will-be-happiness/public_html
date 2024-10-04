<?php defined('BILLINGMASTER') or die;

class SegmentFilter {

    const FILTER_TYPE_ORDERS = 1;
    const FILTER_TYPE_USERS = 2;


    /**
     * @param $filter_model
     * @param $condition_titles
     * @param $groups
     * @param $group
     * @param $skip
     * @param $segment_data
     * @param bool $cond_change
     */
    public static function getFiltersHtmlItem($filter_model, $condition_titles, $groups, $group, &$skip, $segment_data, $cond_change = true) {
        require (ROOT.'/template/admin/views/segment_filter/filters.php');
    }


    /**
     * @param $segment_data
     * @param bool $cond_change
     * @return false|string|null
     */
    public static function getFiltersHtml($segment_data, $cond_change = true) {
        $filter_model = SegmentFilter::getFilterModel();
        $out_data = null;
        $groups = isset($segment_data['groups_data']) && $segment_data['groups_data'] ? json_decode($segment_data['groups_data'], 1) : null;

        if ($groups) {
            $condition_titles = $filter_model::getFilterTitles();
            ob_start();
            $skip = [];
            foreach ($groups as $group) {
                if ($skip && in_array($group['index'], $skip)) {
                    continue;
                }

                $invert = isset($group['invert']) ? $group['invert'] : 0;
                self::getFiltersHtmlItem($filter_model, $condition_titles, $groups, $group, $skip, $segment_data, $cond_change);
            }
            $out_data = ob_get_contents();
            ob_end_clean();
        } elseif(isset($segment_data['condition_type'])) {
            require_once(ROOT.'/template/admin/views/segment_filter/condition.php');
        }

        return $out_data;
    }


    /**
     * @param $groups
     * @param $data
     * @param $group
     * @param $clauses
     * @param $skip
     * @return string
     */
    public static function getConditions2Group($groups, $data, $group, $clauses, &$skip) {
        $operator = htmlentities(strtoupper($group['logic_type']));
        $invert = $group['invert'] ? 'NOT' : '';
        $conditions = '';

        if ($group['groups']) {
            $count_groups = count($group['groups']);

            foreach ($group['groups'] as $key => $inner_group_index) {
                $index = array_search($inner_group_index, array_column($groups, 'index'));
                $inner_group = $groups[$index];
                $condition = self::getConditions2Group($groups, $data, $inner_group, $clauses, $skip);

                if ($condition) {
                    $conditions .= $condition.($key < $count_groups - 1 ? " $operator $invert " : '');
                }
                $skip[] = $inner_group_index;
            }
        }

        if ($group['conditions']) {
            foreach ($group['conditions'] as $key => $cond_index) {
                $cond_invert = $data['invert'][$cond_index] ? 'NOT' : '';
                if (!isset($clauses[$cond_index]) || !$clauses[$cond_index]) {
                    continue;
                }
                $conditions .= ($conditions ? " $operator " : '')."$cond_invert {$clauses[$cond_index]}";
            }
        }

        return $conditions ? "$invert ($conditions)" : '';
    }


    /**
     * @param $segment_data
     * @param null $filter_model
     * @return bool|string|null
     */
    public static function getConditions($segment_data, $filter_model = null) {
        $filter_model = $filter_model ?: self::getFilterModel($filter_model);

        if (!$filter_model || !isset($segment_data['segment'])) {
            return false;
        }

        if ($segment_data['segment'] != 'segment' && !isset($segment_data['condition_type'])) {
            $segment_data = self::getSegmentData($filter_model, (int)$segment_data['segment']);
        }

        if (!$segment_data || !isset($segment_data['condition_type'])) {
            return null;
        }

        $clauses = $group_clauses = $having = [];
        $segment_data['groups_data'] = json_decode($segment_data['groups_data'], true);

        foreach ($segment_data['condition_type'] as $cond_index => $condition_type) {
            $condition = $filter_model::getCondition($condition_type, $cond_index, $segment_data);
            if (!$condition) {
                continue;
            }
            $clauses[$cond_index] = is_array($condition) ? '('.implode(' AND ', $condition).')' : $condition;
        }

        if (empty($clauses) || !array_filter($clauses, 'strlen')) {
            return null;
        }


        $out_clauses = '';
        $skip = [];

        if ($segment_data['groups_data']) {
            foreach ($segment_data['groups_data'] as $group) {
                $index = $group['index'];
                $operator = htmlentities(strtoupper($group['logic_type']));

                if (in_array($index, $skip)) {
                    continue;
                }

                $clause = self::getConditions2Group($segment_data['groups_data'], $segment_data, $group, $clauses, $skip);
                $out_clauses .= $clause;
            }
        } else {
            foreach ($clauses as $key => $clause) {
                $invert = $segment_data['invert'][$key] ? 'NOT' : '';
                $out_clauses .= "$invert $clause";
            }
        }

        return $out_clauses;
    }


    /**
     * @param $filter_model
     * @param $segment_id
     * @return mixed
     */
    public static function getSegmentData($filter_model, $segment_id) {
        $segment_data = $filter_model::getSegment($segment_id);
        $segment_data = $segment_data ? base64_decode($segment_data['data']) : null;
        parse_str($segment_data, $data);

        return $data;
    }


    /**
     * @param null $filter_type
     * @return OrderFilter|UserFilter
     */
    public static function getFilterModel($filter_type = null) {
        if ($filter_type !== null) {
            return $filter_type == self::FILTER_TYPE_ORDERS ? 'OrderFilter' : 'UserFilter';
        } elseif (isset($_GET['filter_model'])) {
            return $_GET['filter_model'] == self::FILTER_TYPE_ORDERS ? 'OrderFilter' : 'UserFilter';
        } else {
            $page_url = isset($_GET['page_url']) ? $_GET['page_url'] : $_SERVER['REQUEST_URI'];
            if (strpos($page_url, '/admin/orders') === 0) {
                return 'OrderFilter';
            } elseif(strpos($page_url, '/admin/users') === 0) {
                return 'UserFilter';
            } elseif(strpos($page_url, '/admin/conditions') === 0) {
                return 'OrderFilter';
            }
        }
    }


    /**
     * @param $model
     * @param $segment_id
     * @return bool
     */
    public static function getSegmentUrl($model, $segment_id) {
        $url = $model == 'OrderFilter' ? "/admin/orders/" : "/admin/users/";
        return "$url?filter=фильтр&segment=$segment_id";
    }


    /**
     * @param $value
     * @param null $value1
     * @param null $value2
     * @return array|null
     */
    public static function getDate2Filter($value, $value1 = null, $value2 = null) {
        $dates = [];

        switch ($value) {
            case 'now':
                $dates = [strtotime(date('d.m.Y 00:00:00')), time()];
                break;
            case 'yesterday':
                $dates = [
                    strtotime(date('d.m.Y 00:00:00')) - 86400,
                    strtotime(date('d.m.Y 23:59:59')) - 86400
                ];
                break;
            case 'this_week':
                $n = date('w') == 0 ? 6 : date('w') - 1;
                $date1 = strtotime(date('d-m-Y 00:00:00')) - 86400 * $n;
                $dates = [$date1, $date1 + 604799];
                break;
            case 'last_week':
                $n = date('w') == 0 ? 7 : date('w');
                $end_date = strtotime(date('d-m-Y 23:59:59', strtotime("-$n day", time())));
                $start_date = $end_date - 86400 * 7 + 1;
                $dates = [$start_date, $end_date];
                break;
            case 'this_month':
                $date1 = strtotime(date('1-m-Y 00:00:00'));
                return [$date1, strtotime(date('1-m-Y 00:00:00', strtotime("+1 month", $date1))) - 1];
            case 'last_month':
                $date2 = strtotime(date('1-m-Y 00:00:00')) - 1;
                $dates = [
                    strtotime(date('1-m-Y 00:00:00', $date2)),
                    $date2
                ];
                break;
            case 'month':
                $date1 = strtotime(date('d-m-Y 00:00:00')); //текущий день месяца
                $dates = [$date1, strtotime("+1 month", $date1) - 1];
                break;
            case 'n_days_ago':
                if ($value1 || $value2) {
                    $dates = [
                        $value2 ? strtotime(date('d-m-Y 00:00:00')) - 86400 * $value2 : time(),
                        $value1 ? strtotime(date('d-m-Y 23:59:59')) - 86400 * $value1 : time()
                    ];
                }
                break;
            case 'n_hours_ago':
                if ($value1 || $value2) {
                    $dates = [
                        $value2 ? time() - $value2 * 3600 : time(),
                        $value1 ? time() - $value1 * 3600 : time(),
                    ];
                }
                break;
            case 'period':
                if ($value1 || $value2) {
                    $dates = [
                        $value1 ? strtotime($value1) : 0,
                        $value2 ? strtotime($value2) : time()
                    ];
                }
                break;
            case 'all_time':
                $dates = [-2145925817, 2145819600];
                break;
        }

        return $dates ? $dates : null;
    }


    /**
     * @param $value
     * @param $value1
     * @param $value2
     * @return string
     */
    public static function getDateText2Filter($value, $value1, $value2) {
        $dates = self::getDate2Filter($value, $value1, $value2);
        $text = $dates ? 'с '.date('d.m.Y H:i', $dates[0]) . ' по '.date('d.m.Y H:i', $dates[1]) : '';

        return $text;
    }


    /**
     * @return array
     */
    public static function getDateFilters() {
        $dates = [
            [
                'title' => 'Сегодня',
                'value' => 'now'
            ],
            [
                'title' => 'Вчера',
                'value' => 'yesterday'
            ],
            [
                'title' => 'Эта неделя',
                'value' => 'this_week'
            ],
            [
                'title' => 'Прошлая неделя',
                'value' => 'last_week'
            ],
            [
                'title' => 'Этот месяц',
                'value' => 'this_month'
            ],
            [
                'title' => 'Прошлый месяц',
                'value' => 'last_month'
            ],
            [
                'title' => 'месяц',
                'value' => 'month'
            ],
            [
                'title' => 'N дней назад',
                'value' => 'n_days_ago',
            ],
            [
                'title' => 'N часов назад',
                'value' => 'n_hours_ago',
            ],
            [
                'title' => 'Все время',
                'value' => 'all_time'
            ],
            [
                'title' => 'Период',
                'value' => 'period',
            ],
        ];

        return $dates;
    }
}
