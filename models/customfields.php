<?php defined('BILLINGMASTER') or die;


class CustomFields {

    use ResultMessage;

    const FIELD_DATA_TYPE_INT = 1; // Целые числа от -2147483648 до 2147483647
    const FIELD_DATA_TYPE_TEXT = 2; // Строка размером до 65 КБ

    const FIELD_TYPE_CHECKBOX = 1; //Чек боксы (несколько значений)
    const FIELD_TYPE_RADIO = 2; //Радио кнопки (одно из значений)
    const FIELD_TYPE_SELECT = 3; //Выпадающий список
    const FIELD_TYPE_MULTI_SELECT = 4; //Мультисписок
    const FIELD_TYPE_TEXT = 5; //Текстовая строка
    const FIELD_TYPE_TEXTAREA = 6; //Текстовое поле

    const PARSE_TYPE_LK = 1;
    const PARSE_TYPE_API = 2;
    const PARSE_TYPE_REGISTRATION = 3;
    const PARSE_TYPE_ORDER = 4;

    private static $field_values = null;

    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЕЙ
     * @param int $status
     * @return array
     */
    public static function getDataFields($status = 1) {
        $db = Db::getConnection();
        $where = $status !== null ? "WHERE status = $status" : '';
        $result = $db->query("SELECT * FROM ".PREFICS."custom_fields_data $where ORDER BY id DESC");
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] =  $row;
        }

        return $data;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЯ
     * @param $field_id
     * @return bool|mixed
     */
    public static function getDataField($field_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS.'custom_fields_data WHERE id = :field_id');
        $result->bindParam(':field_id', $field_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЯ
     * @param $column_name
     * @return bool|mixed
     */
    public static function getDataFieldByColumnName($column_name) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT * FROM ".PREFICS.'custom_fields_data WHERE column_name = :column_name');
        $result->bindParam(':column_name', $column_name, PDO::PARAM_STR);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ПОЛЯ ДЛЯ ПОЛЬЗОВАТЕЛЯ
     * @param $user_id
     * @param null $email
     * @param null $order_id
     * @return bool|mixed
     */
    public static function getUserFields($user_id, $email = null, $order_id = null) {
        $clauses = [];
        if ($user_id !== null) {
            $clauses[] = 'user_id = :user_id';
        }
        if ($email !== null) {
            $clauses[] = 'email = :email';
        }
        if ($order_id !== null) {
            $clauses[] = 'order_id = :order_id';
        }

        $query = 'SELECT * FROM '.PREFICS.'custom_fields WHERE '.implode(' AND ', $clauses);

        $db = Db::getConnection();
        $result = $db->prepare($query);
        if ($user_id !== null) {
            $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        }
        if ($email !== null) {
            $result->bindParam(':email', $email, PDO::PARAM_STR);
        }
        if ($order_id !== null) {
            $result->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        }

        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data : false;
    }


    /**
     * @param $user_id
     * @return mixed
     */
    public static function getCountFieldValues($user_id) {
        $db = Db::getConnection();
        $result = $db->prepare("SELECT COUNT(*) FROM ".PREFICS.'custom_fields WHERE user_id = :user_id');
        $result->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $result->execute();
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @param $email
     * @param $user_id
     * @return bool
     */
    public static function updUserId($email, $user_id) {
        $db = Db::getConnection();
        $result = $db->prepare('UPDATE '.PREFICS."custom_fields SET user_id = :user_id WHERE user_id = 0 AND email = :email");
        $result->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $result->bindParam(':email', $email, PDO::PARAM_STR);

        return $result->execute();
    }


    /**
     * @param $user_id
     * @param $email
     * @param $values
     * @param int $parse_type
     * @param null $order_id
     * @return bool
     */
    public static function saveUserFields($user_id, $email, $values, $parse_type = self::PARSE_TYPE_LK, $order_id = null) {
        $custom_fields = CustomFields::getCountFields($parse_type);
        if (!$custom_fields) {
            return false;
        }

        $db = Db::getConnection();
        $current_values = self::getUserFields($user_id, $email, $order_id);
        $fields_data = self::getFields();
        $columns = array_column($fields_data, 'column_name');

        if (!$current_values) {
            $query_fields = implode(',', $columns);
            $query_values = '';
            foreach ($columns as $key => $column_name) {
                $query_values .= ($key > 0 ? ',' : '').":{$column_name}";
            }
            $query = "INSERT INTO ".PREFICS."custom_fields ($query_fields, user_id, email, order_id)
                      VALUES ($query_values, :user_id, :email, :order_id)";
        } else {
            $query = "UPDATE ".PREFICS."custom_fields SET ";
            foreach ($columns as $key => $column_name) {
                $query .= ($key > 0 ? ',' : '')."$column_name = :$column_name";
            }
            $query .= ", user_id = :user_id, email = :email, order_id = :order_id WHERE id = {$current_values['id']}";
        }

        $result = $db->prepare($query);
        foreach ($fields_data as $field_data) {
            $column_name = $field_data['column_name'];
            $values[$column_name] = self::getFieldValue2Save($field_data, $values, $current_values, $column_name, $parse_type);

            if ($field_data['field_data_type'] == self::FIELD_DATA_TYPE_INT) {
                $values[$column_name] = (int)$values[$column_name];
                $result->bindParam(":{$column_name}",$values[$column_name], PDO::PARAM_INT);
            } else {
                if (!in_array($field_data['field_type'], [self::FIELD_TYPE_MULTI_SELECT, self::FIELD_TYPE_CHECKBOX])) {
                    $values[$column_name] = htmlentities($values[$column_name]);
                }
                $result->bindParam(":{$column_name}",$values[$column_name], PDO::PARAM_STR);
            }
        }

        $result->bindParam(":user_id",$user_id, PDO::PARAM_INT);
        $result->bindParam(":email",$email, PDO::PARAM_STR);
        $result->bindParam(":order_id",$order_id, PDO::PARAM_INT);

        $result->execute();
    }


    /**
     * ПОЛУЧИТЬ ТИП ДАННЫХ ПОЛЯ
     * @param null $type
     * @return array|mixed
     */
    public static function getFieldDataType($type = null) {
        $types = [
            self::FIELD_DATA_TYPE_TEXT => 'Текст (до 65 535 символов)',
            self::FIELD_DATA_TYPE_INT => 'Числа',
        ];

        return $type && isset($types[$type]) ? $types[$type] : $types;
    }


    /**
     * ПОЛУЧИТЬ ТИП ПОЛЯ
     * @param null $type
     * @return array|mixed
     */
    public static function getFieldType($type = null) {
        $types = [
            self::FIELD_TYPE_CHECKBOX => 'Чек боксы (несколько значений)',
            self::FIELD_TYPE_RADIO => 'Радио кнопки (одно из значений)',
            self::FIELD_TYPE_SELECT => 'Выпадающий список',
            self::FIELD_TYPE_MULTI_SELECT => 'Мультисписок',
            self::FIELD_TYPE_TEXT => 'Текстовая строка',
            self::FIELD_TYPE_TEXTAREA => 'Текстовое поле',
        ];

        return $type && isset($types[$type]) ? $types[$type] : $types;
    }


    /**
     * ПОЛУЧИТЬ ТИП/ТИПЫ ДАННЫХ КОЛОНКИ/КОЛОНОК
     * @param $type
     * @return array|mixed
     */
    public static function getColumnDataType($type) {
        $types = [
            self::FIELD_DATA_TYPE_INT => 'INT(11)', // Целые числа
            self::FIELD_DATA_TYPE_TEXT => 'TEXT', // Строка (до 65 535 символов)
        ];

        return isset($types[$type]) ? $types[$type] : $types;
    }


    /**
     * ДОБАВИТЬ ПОЛЕ И ДАННЫЕ
     * @param $field_name
     * @param $field_data_type
     * @param $field_type
     * @param $field_default_value
     * @param $is_show_in_profile
     * @param $is_show2registration
     * @param $is_editable
     * @param $field_sort
     * @param $is_parse_in_api
     * @param $params
     * @param $status
     * @param int $is_show2order
     * @return bool
     */
    public static function addField($field_name, $field_data_type, $field_type, $field_default_value, $is_show_in_profile,
                                    $is_show2registration, $is_editable, $field_sort, $is_parse_in_api, $params, $status,
                                    $is_show2order = 0) {
        $db = Db::getConnection();
        $column_number = (int)System::getInsertId(PREFICS.'custom_fields_data');
        $column_name = "custom_field_$column_number";
        $column_data_type = self::getColumnDataType($field_data_type);
        $query = 'ALTER TABLE '.PREFICS."custom_fields ADD `$column_name` $column_data_type COMMENT 'название поля: $field_name';";
        $result = $db->query($query);

        if ($result) {
            try {
                if ($field_data_type == self::FIELD_DATA_TYPE_TEXT && $field_default_value !== '') {
                    $result = $db->prepare('UPDATE '.PREFICS."custom_fields SET `$column_name` = :default_value");
                    $result->bindParam(':default_value', $field_default_value, PDO::PARAM_STR);
                    $result->execute();
                }

                $query = 'INSERT INTO '.PREFICS.'custom_fields_data (`field_name`, `field_data_type`, `field_type`, `column_name`, `default_value`, 
                            `is_show_in_profile`, `is_show2registration`, `is_editable`, `field_sort`, `is_parse_in_api`, `params`, `status`, `is_show2order`) 
                      VALUES (:field_name, :field_data_type, :field_type, :column_name, :default_value, :is_show_in_profile,
                            :is_show2registration, :is_editable, :field_sort, :is_parse_in_api, :params, :status, :is_show2order)';
                $result = $db->prepare($query);
                $result->bindParam(':field_name', $field_name, PDO::PARAM_STR);
                $result->bindParam(':column_name', $column_name, PDO::PARAM_STR);
                $result->bindParam(':field_data_type', $field_data_type, PDO::PARAM_INT);
                $result->bindParam(':field_type', $field_type, PDO::PARAM_INT);
                $result->bindParam(':default_value', $field_default_value, PDO::PARAM_STR);
                $result->bindParam(':is_show_in_profile', $is_show_in_profile, PDO::PARAM_INT);
                $result->bindParam(':is_show2registration', $is_show2registration, PDO::PARAM_INT);
                $result->bindParam(':is_editable', $is_editable, PDO::PARAM_INT);
                $result->bindParam(':field_sort', $field_sort, PDO::PARAM_INT);
                $result->bindParam(':is_parse_in_api', $is_parse_in_api, PDO::PARAM_INT);
                $result->bindParam(':params', $params, PDO::PARAM_STR);
                $result->bindParam(':status', $status, PDO::PARAM_INT);
                $result->bindParam(':is_show2order', $is_show2order, PDO::PARAM_INT);

                if ($result->execute()) {
                    return true;
                }

                throw new Exception('Ошибка при добавлении данных');
            } catch(Exception $e) {
                $db->query('ALTER TABLE '.PREFICS."custom_fields DROP COLUMN `$column_name`");
            }
        }

        return false;
    }


    /**
     * ОБНОВИТЬ ПОЛЕ И ДАННЫЕ
     * @param $field_data
     * @param $field_name
     * @param $field_data_type
     * @param $field_type
     * @param $field_default_value
     * @param $is_show_in_profile
     * @param $is_show2registration
     * @param $is_editable
     * @param $is_parse_in_api
     * @param $field_sort
     * @param $params
     * @param $status
     * @param int $is_show2order
     * @return bool
     */
    public static function updField($field_data, $field_name, $field_data_type, $field_type, $field_default_value, $is_show_in_profile,
                                    $is_show2registration, $is_editable, $is_parse_in_api, $field_sort, $params, $status, $is_show2order = 0) {
        if ($field_data && System::column_exists(PREFICS.'custom_fields', $field_data['column_name'])) {
            $db = Db::getConnection();
            $column_type = self::getColumnDataType($field_data_type);
            $query = 'ALTER TABLE '.PREFICS."custom_fields CHANGE `{$field_data['column_name']}` `{$field_data['column_name']}`
                      $column_type COMMENT 'название поля: $field_name'";
            $result = $db->query($query);

            if ($result) {
                try {
                    $query = 'UPDATE '.PREFICS.'custom_fields_data SET field_name = :field_name, field_data_type = :field_data_type,
                          field_type = :field_type, default_value = :default_value, is_show_in_profile = :is_show_in_profile,
                          is_show2registration = :is_show2registration, is_editable = :is_editable, is_parse_in_api = :is_parse_in_api,
                          field_sort = :field_sort, params = :params, status = :status, is_show2order = :is_show2order WHERE id = :field_id';
                    $result = $db->prepare($query);
                    $result->bindParam(':field_name', $field_name, PDO::PARAM_STR);
                    $result->bindParam(':field_type', $field_type, PDO::PARAM_INT);
                    $result->bindParam(':field_data_type', $field_data_type, PDO::PARAM_INT);
                    $result->bindParam(':default_value', $field_default_value, PDO::PARAM_STR);
                    $result->bindParam(':is_show_in_profile', $is_show_in_profile, PDO::PARAM_INT);
                    $result->bindParam(':is_show2registration', $is_show2registration, PDO::PARAM_INT);
                    $result->bindParam(':is_editable', $is_editable, PDO::PARAM_INT);
                    $result->bindParam(':is_parse_in_api', $is_parse_in_api, PDO::PARAM_INT);
                    $result->bindParam(':field_sort', $field_sort, PDO::PARAM_INT);
                    $result->bindParam(':field_id', $field_data['id'], PDO::PARAM_INT);
                    $result->bindParam(':params', $params, PDO::PARAM_STR);
                    $result->bindParam(':status', $status, PDO::PARAM_INT);
                    $result->bindParam(':is_show2order', $is_show2order, PDO::PARAM_INT);

                    if ($result->execute()) {
                        return true;
                    }
                    throw new Exception('Ошибка при добавлении данных');
                } catch(Exception $e) {
                    $column_type = self::getColumnDataType($field_data['field_data_type']);
                    $query = 'ALTER TABLE '.PREFICS."custom_fields CHANGE `{$field_data['column_name']}` `{$field_data['column_name']}`
                              $column_type COMMENT 'название поля: {$field_data['field_name']}'";
                    $db->query($query);
                }
            }
        }

        return false;
    }


    /**
     * ОБНОВИТЬ ДАННЫЕ ДЛЯ ПОЛЯ
     * @param $field_id
     * @param $field_name
     * @param $field_type
     * @param $is_show_in_profile
     * @param $is_editable
     * @param $is_parse_in_api
     * @param $field_sort
     * @param $params
     * @param $status
     * @return bool
     */
    public static function updFieldData($field_id, $field_name, $field_type, $is_show_in_profile, $is_editable, $is_parse_in_api,
                                        $field_sort, $params, $status) {
        $db = Db::getConnection();
        $query = 'UPDATE '.PREFICS.'custom_fields_data SET field_name = :field_name, field_type = :field_type,
                  is_show_in_profile = :is_show_in_profile, is_editable = :is_editable, is_parse_in_api = :is_parse_in_api,
                  field_sort = :field_sort, params = :params, status =:status WHERE id = :field_id';
        $result = $db->prepare($query);
        $result->bindParam(':field_name', $field_name, PDO::PARAM_STR);
        $result->bindParam(':field_type', $field_type, PDO::PARAM_INT);
        $result->bindParam(':is_show_in_profile', $is_show_in_profile, PDO::PARAM_INT);
        $result->bindParam(':is_editable', $is_editable, PDO::PARAM_INT);
        $result->bindParam(':is_parse_in_api', $is_parse_in_api, PDO::PARAM_INT);
        $result->bindParam(':field_sort', $field_sort, PDO::PARAM_INT);
        $result->bindParam(':field_id', $field_id, PDO::PARAM_INT);
        $result->bindParam(':params', $params, PDO::PARAM_STR);
        $result->bindParam(':status', $status, PDO::PARAM_INT);

        return $result->execute();
    }


    /**
     * @param null $parse_type
     * @param int $status
     * @return array|bool
     */
    public static function getFields($parse_type = null, $status = 1) {
        $clauses = [];
        if($status !== null) {
            $clauses[] = "status = $status";
        }
        if ($parse_type) {
            if ($parse_type == self::PARSE_TYPE_API) {
                $clauses[] =  'is_parse_in_api = 1';
            } elseif($parse_type == self::PARSE_TYPE_REGISTRATION) {
                $clauses[] =  'is_show2registration = 1';
            } elseif($parse_type == self::PARSE_TYPE_ORDER) {
                $clauses[] =  'is_show2order = 1';
            } else {
                $clauses[] =  'is_show_in_profile = 1';
            }
        }

        $db = Db::getConnection();
        $where = !empty($clauses) ? 'WHERE '.implode(' AND ', $clauses) : '';
        $result = $db->query("SELECT * FROM ".PREFICS."custom_fields_data $where");

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] =  $row;
        }

        return !empty($data) ? $data : false;
    }


    /**
     * @param null $parse_type
     * @param int $status
     * @return mixed
     */
    public static function getCountFields($parse_type = null, $status = 1) {
        $clauses = [];
        if($status !== null) {
            $clauses[] = "status = $status";
        }
        if ($parse_type) {
            if ($parse_type == self::PARSE_TYPE_API) {
                $clauses[] =  'is_parse_in_api = 1';
            } elseif($parse_type == self::PARSE_TYPE_REGISTRATION) {
                $clauses[] =  'is_show2registration = 1';
            } elseif($parse_type == self::PARSE_TYPE_ORDER) {
                $clauses[] =  'is_show2order = 1';
            } else {
                $clauses[] =  'is_show_in_profile = 1';
            }

            if ($parse_type == self::PARSE_TYPE_LK) {
                $clauses[] = 'is_editable = 1';
            }
        }

        $db = Db::getConnection();
        $where = !empty($clauses) ? 'WHERE '.implode(' AND ', $clauses) : '';
        $result = $db->query("SELECT COUNT(*) FROM ".PREFICS."custom_fields_data $where");
        $data = $result->fetch();

        return $data[0];
    }


    /**
     * @return bool|mixed
     */
    public static function getLastFieldId() {
        $db = Db::getConnection();
        $result = $db->query("SELECT id FROM ".PREFICS."custom_fields_data ORDER BY id DESC LIMIT 1");
        $data = $result->fetch(PDO::FETCH_ASSOC);

        return !empty($data) ? $data['id'] : false;
    }


    /**
     * УДАЛИТЬ ПОЛЕ И ДАННЫЕ
     * @param $field_id
     * @return bool
     */
    public static function delField($field_id) {
        $db = Db::getConnection();
        $result = $db->query("SELECT * FROM ".PREFICS."custom_fields_data WHERE id = $field_id");
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if ($data && System::column_exists(PREFICS.'custom_fields', $data['column_name'])) {
            $query = "ALTER TABLE ".PREFICS."custom_fields DROP {$data['column_name']}";
            $result = $db->query($query);
            if ($result) {
                return $db->query("DELETE FROM ".PREFICS."custom_fields_data WHERE id = $field_id");
            }
        }

        return false;
    }


    /**
     * @param $column_name
     * @return bool
     */
    public static function isAllowUpdField($column_name) {
        $db = Db::getConnection();
        $query = "SELECT COUNT(id) FROM ".PREFICS."custom_fields WHERE $column_name IS NOT NULL AND $column_name <> ''";
        $result = $db->query($query);
        $data = $result->fetch();

        return $data[0] > 0 ? false : true;
    }


    /**
     * @param $user_id
     * @param null $email
     * @param null $order_id
     * @return bool
     */
    public static function delUserFields($user_id, $email = null, $order_id = null) {
        $db = Db::getConnection();
        $clauses = [];
        if ($user_id !== null) {
            $clauses[] = 'user_id = :user_id';
        }
        if ($email !== null) {
            $clauses[] = 'email = :email';
        }
        if ($order_id !== null) {
            $clauses[] = 'order_id = :order_id';
        }
        $query = "DELETE FROM ".PREFICS."custom_fields WHERE ".implode(' AND ', $clauses);
        $result = $db->prepare($query);
        if ($user_id !== null) {
            $result->bindParam(':user_id',$user_id, PDO::PARAM_INT);
        }
        if ($email !== null) {
            $result->bindParam(':email',$email, PDO::PARAM_STR);
        }
        if ($order_id !== null) {
            $result->bindParam(':order_id',$order_id, PDO::PARAM_INT);
        }

        return $result->execute();
    }


    /**
     * @param $field
     * @param $values
     * @return mixed|string
     */
    public static function getFieldValue($field, $values) {
        $column_values = $values ? $values[$field['column_name']] : $field['default_value'];
        if (in_array($field['field_type'], [self::FIELD_TYPE_CHECKBOX, self::FIELD_TYPE_MULTI_SELECT]) && $column_values) {
            return json_decode($column_values, true);
        }

        return $column_values;
    }


    /**
     * @param $field
     * @param $values
     * @param string $separator
     * @return mixed|string|null
     */
    public static function getValueTitles($field, $values, $separator = ',') {
        $v_titles = [];
        $value = $values ? $values[$field['column_name']] : null;

        if ($values && !in_array($field['field_type'], [self::FIELD_TYPE_TEXT, self::FIELD_TYPE_TEXTAREA])) {
            $params = json_decode($field['params'], true);

            if (in_array($field['field_type'], [self::FIELD_TYPE_CHECKBOX, self::FIELD_TYPE_MULTI_SELECT])) {
                $_values = json_decode($value, true);
                if ($_values && is_array($_values)) {
                    foreach ($_values as $_value) {
                        if(isset($params[$_value])) {
                            $v_titles[] = $params[$_value];
                        }
                    }
                } elseif($_values) {
                    $v_titles[] = $_values;
                }
            } elseif(isset($params[$value])) {
                $v_titles[] = $params[$value];
            }
        } else {
            return $value;
        }

        return $v_titles ? implode($separator, $v_titles) : '';
    }


    /**
     * @param $field
     * @param $values
     * @param null $params
     * @param string $separator
     * @return null
     */
    public static function getValueTitles2($field, $values, $params = null, $separator = ', ') {
        if (!$values) {
            return '';
        }

        $params = $params ?: json_decode($field['params'], true);
        if (in_array($field['field_type'], [self::FIELD_TYPE_TEXT, self::FIELD_TYPE_TEXTAREA])) {
            return $values;
        } elseif (in_array($field['field_type'], [self::FIELD_TYPE_RADIO, self::FIELD_TYPE_SELECT])) {
            return $params && isset($params[$values]) ? $params[$values] : '';
        } elseif($params) {
            $_values = [];
            foreach ($values as $value) {
                if (isset($params[$value])) {
                    $_values[] = $params[$value];
                }
            }

            return $_values ? implode($separator, $_values) : '';
        }

        return '';
    }


    /**
     * @param $field
     * @param $values
     * @param $current_values
     * @param $column_name
     * @param $parse_type
     * @return mixed
     */
    public static function getFieldValue2Save($field, $values, $current_values, $column_name, $parse_type) {
        if (($parse_type == self::PARSE_TYPE_API && !$field['is_parse_in_api']) || $parse_type == self::PARSE_TYPE_LK && (!$field['is_show_in_profile'] || !$field['is_editable'])) {
            $values[$column_name] = $current_values ? $current_values[$column_name] : $field['default_value'];
        } elseif (in_array($field['field_type'], [self::FIELD_TYPE_MULTI_SELECT, self::FIELD_TYPE_CHECKBOX])) { // заначения для поля есть и тип поля чекбокс или мультиселект
            if (isset($values[$column_name])) {
                $values[$column_name] = is_array($values[$column_name]) ? $values[$column_name] : json_decode($values[$column_name], true);
                if ($values[$column_name] && is_array($values[$column_name])) {
                    foreach ($values[$column_name] as $key => $item) {
                        $values[$column_name][$key] = htmlentities($values[$column_name][$key]);
                    }
                    $values[$column_name] = json_encode($values[$column_name]);
                } else {
                    $values[$column_name] = '';
                }
            }
        }

        return isset($values[$column_name]) ? $values[$column_name] : '';
    }


    /**
     * @param $field
     * @param $user_id
     * @return string
     */
    public static function getFieldTag2LK($field, $user_id) {
        $tag = '';
        if ($user_id) {
            if (!isset(self::$field_values[$user_id])) {
                self::$field_values[$user_id] = self::getUserFields($user_id);
            }

            $values = self::$field_values[$user_id];
            $value = self::getFieldValue($field, $values);
        } else {
            $value = self::getFieldValue($field, null);
        }

        $params = $field['params'] ? json_decode($field['params'], true) : null;

        switch($field['field_type']) {
            case self::FIELD_TYPE_CHECKBOX:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    foreach ($params as $_value => $title) {
                        $checked = is_array($value) && in_array($_value , $value) ? ' checked="checked"' : '';
                        $tag .= "<label class=\"check_label\">
                                    <input type=\"checkbox\" name=\"custom_fields[{$field['column_name']}][]\" value=\"$_value\"$checked>
                                    <span>$title</span>
                                </label>";

                    }
                }
                break;
            case self::FIELD_TYPE_RADIO:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    foreach ($params as $_value => $title) {
                        $checked = $value && $value == $_value ? ' checked="checked"' : '';
                        $tag .= "<label class=\"custom-radio\">
                                    <input type=\"radio\" name=\"custom_fields[{$field['column_name']}]\" value=\"{$_value}\"$checked>
                                    <span>$title</span>
                                </label>";
                    }
                }
                break;
            case self::FIELD_TYPE_SELECT:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    $options_str = '';
                    foreach ($params as $_value => $title) {
                        $options_str .= "<option value=\"$_value\"".($value && $value == $_value ? ' selected=\"selected\"' : '').">$title</option>";
                    }
                    $tag = "<div class=\"select-wrap\">
                                <select name=\"custom_fields[{$field['column_name']}]\">
                                    $options_str
                                </select>
                            </div>";
                }
                break;
            case self::FIELD_TYPE_MULTI_SELECT:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    $options_str = '';
                    foreach ($params as $_value => $title) {
                        $options_str .= "<option value=\"$_value\"" . (is_array($value) && in_array($_value, $value) ? ' selected=\"selected\"' : '') . ">$title</option>";
                    }
                    $tag = "<div class=\"multiple-select\">
                                <select name=\"custom_fields[{$field['column_name']}][]\" multiple=\"multiple\">
                                    $options_str
                                </select>
                            </div>";
                }
                break;
            case self::FIELD_TYPE_TEXT:
                if (!$field['is_editable'] && $value) {
                    $tag .= "<div class=\"custom-field-text\">$value</div>";
                } elseif ($field['is_editable']) {
                    $tag = "<input type=\"text\" name=\"custom_fields[{$field['column_name']}]\" value=\"$value\">";
                }
                break;
            case self::FIELD_TYPE_TEXTAREA:
                if (!$field['is_editable'] && $value) {
                    $tag .= "<div class=\"custom-field-text\">$value</div>";
                } elseif ($field['is_editable']) {
                    $tag = "<textarea cols=\"45\" rows=\"3\" name=\"custom_fields[{$field['column_name']}]\">$value</textarea>";
                }
                break;
        }

        return $tag;
    }


    /**
     * @param $field
     * @param $user_id
     * @return string
     */
    public static function getFieldTag2Order($field, $user_id) {
        $tag = '';
        if ($user_id) {
            if (!isset(self::$field_values[$user_id])) {
                self::$field_values[$user_id] = self::getUserFields($user_id);
            }

            $values = self::$field_values[$user_id];
            $value = self::getFieldValue($field, $values);
        } else {
            $value = self::getFieldValue($field, null);
        }

        $params = $field['params'] ? json_decode($field['params'], true) : null;

        switch($field['field_type']) {
            case self::FIELD_TYPE_CHECKBOX:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    foreach ($params as $_value => $title) {
                        $checked = is_array($value) && in_array($_value , $value) ? ' checked="checked"' : '';
                        $tag .= "<label class=\"check_label\">
                                    <input type=\"checkbox\" name=\"custom_fields[{$field['column_name']}][]\" value=\"$_value\"$checked>
                                    <span>$title</span>
                                </label>";

                    }
                }
                break;
            case self::FIELD_TYPE_RADIO:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    foreach ($params as $_value => $title) {
                        $checked = $value && $value == $_value ? ' checked="checked"' : '';
                        $tag .= "<label class=\"custom-radio\">
                                    <input type=\"radio\" name=\"custom_fields[{$field['column_name']}]\" value=\"{$_value}\"$checked>
                                    <span>$title</span>
                                </label>";
                    }
                }
                break;
            case self::FIELD_TYPE_SELECT:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    $options_str = '';
                    foreach ($params as $_value => $title) {
                        $options_str .= "<option value=\"$_value\"".($value && $value == $_value ? ' selected=\"selected\"' : '').">$title</option>";
                    }
                    $tag = "<div class=\"select-wrap\">
                                <select name=\"custom_fields[{$field['column_name']}]\">
                                    $options_str
                                </select>
                            </div>";
                }
                break;
            case self::FIELD_TYPE_MULTI_SELECT:
                if (!$field['is_editable'] && $value) {
                    $titles = self::getValueTitles2($field, $value);
                    $tag .= "<div class=\"custom-field-text\">$titles</div>";
                } elseif ($field['is_editable'] && $params) {
                    $options_str = '';
                    foreach ($params as $_value => $title) {
                        $options_str .= "<option value=\"$_value\"" . (is_array($value) && in_array($_value, $value) ? ' selected=\"selected\"' : '') . ">$title</option>";
                    }
                    $tag = "<div class=\"multiple-select\">
                                <select name=\"custom_fields[{$field['column_name']}][]\" multiple=\"multiple\">
                                    $options_str
                                </select>
                            </div>";
                }
                break;
            case self::FIELD_TYPE_TEXT:
                if (!$field['is_editable'] && $value) {
                    $tag .= "<div class=\"custom-field-text\">$value</div>";
                } elseif ($field['is_editable']) {
                    $tag = "<input type=\"text\" name=\"custom_fields[{$field['column_name']}]\" value=\"$value\">";
                }
                break;
            case self::FIELD_TYPE_TEXTAREA:
                if (!$field['is_editable'] && $value) {
                    $tag .= "<div class=\"custom-field-text\">$value</div>";
                } elseif ($field['is_editable']) {
                    $tag = "<textarea cols=\"45\" rows=\"3\" name=\"custom_fields[{$field['column_name']}]\">$value</textarea>";
                }
                break;
        }

        return $tag;
    }


    /**
     * @param $order_id
     * @return string
     */
    public static function getFieldsInfoToOrder($order_id) {
        $order_info = '';
        $custom_fields = CustomFields::getFields(CustomFields::PARSE_TYPE_ORDER);
        $field_values = self::getUserFields(null, null, $order_id);

        if ($custom_fields && $field_values) {
            foreach ($custom_fields as $field) {
                $value = self::getFieldValue($field, $field_values);
                $titles = self::getValueTitles2($field, $value);
                $order_info .= "{$field['field_name']}: $titles<br>";
            }
        }

        return $order_info;
    }


    /**
     * @param $field
     * @param $user_id
     * @return string
     */
    public static function getFieldTag2Admin($field, $user_id) {
        $tag = '';
        if (!isset(self::$field_values[$user_id])) {
            self::$field_values[$user_id] = self::getUserFields($user_id);
        }

        $values = self::$field_values[$user_id];
        $params = $field['params'] ? json_decode($field['params'], true) : null;
        $value = self::getFieldValue($field, $values);

        switch($field['field_type']) {
            case self::FIELD_TYPE_CHECKBOX:
                if ($params) {
                    $tag .= "<label class=\"custom-field-name\">
                                <i class=\"visible-".($field['is_show_in_profile'] ? 'on' :'off')."\"></i>{$field['field_name']}:
                            </label>";

                    foreach ($params as $_value => $title) {
                        $checked = is_array($value) && in_array($_value , $value) ? ' checked="checked"' : '';
                        $tag .= "<label class=\"custom-chekbox-wrap\">
                                    <input type=\"checkbox\" name=\"custom_fields[{$field['column_name']}][]\" value=\"$_value\"$checked>
                                    <span class=\"custom-chekbox\"></span>$title
                                </label>";
                    }
                }

                break;
            case self::FIELD_TYPE_RADIO:
                if ($params) {
                    $count_params = count($params);
                    $tag .= "<label class=\"custom-field-name\">
                                <i class=\"visible-".($field['is_show_in_profile'] ? 'on' :'off')."\"></i>{$field['field_name']}:
                            </label>
                    <span class=\"custom-radio-wrap\">";
                        foreach ($params as $_value => $title) {
                            $checked = $value && $value == $_value ? ' checked="checked"' : '';
                            $tag .= "<label class=\"custom-radio\">
                                        <input type=\"radio\" name=\"custom_fields[{$field['column_name']}]\" value=\"$_value\"$checked>
                                        <span>$title</span>
                                    </label>".($count_params > 2 ? '<br>' : '');
                        }
                    $tag .= "</span>";
                }
                break;
            case self::FIELD_TYPE_SELECT:
                $options_str = '';
                if ($params) {
                    foreach ($params as $_value => $title) {
                        $options_str .= "<option value=\"$_value\"".($value && $value == $_value ? ' selected=\"selected\"' : '').">$title</option>";
                    }
                }
                $tag = "<label class=\"custom-field-name\">
                            <i class=\"visible-".($field['is_show_in_profile'] ? 'on' :'off')."\"></i>{$field['field_name']}:
                        </label>
                        <div class=\"select-wrap\">
                            <select name=\"custom_fields[{$field['column_name']}]\">
                                $options_str
                            </select>
                        </div>";
                break;
            case self::FIELD_TYPE_MULTI_SELECT:
                $options_str = '';
                if ($params) {
                    foreach ($params as $_value => $title) {
                        $options_str .= "<option value=\"$_value\"" . (is_array($value) && in_array($_value, $value) ? ' selected=\"selected\"' : '') . ">$title</option>";
                    }
                }
                $tag = "<label class=\"custom-field-name\">
                            <i class=\"visible-".($field['is_show_in_profile'] ? 'on' :'off')."\"></i>{$field['field_name']}:
                        </label>
                        <select class=\"multiple-select\" name=\"custom_fields[{$field['column_name']}][]\" multiple=\"multiple\">
                            $options_str
                        </select>";
                break;
            case self::FIELD_TYPE_TEXT:
                $tag = "<label class=\"custom-field-name\">
                            <i class=\"visible-".($field['is_show_in_profile'] ? 'on' :'off')."\"></i>{$field['field_name']}:
                        </label>
                        <input type=\"text\" name=\"custom_fields[{$field['column_name']}]\" value=\"$value\">";
                break;
            case self::FIELD_TYPE_TEXTAREA:
                $tag = "<label class=\"custom-field-name\">
                            <i class=\"visible-".($field['is_show_in_profile'] ? 'on' :'off')."\"></i>{$field['field_name']}:
                        </label>
                        <textarea cols=\"45\" rows=\"3\" name=\"custom_fields[{$field['column_name']}]\">$value</textarea>";
                break;
        }

        return $tag;
    }


    /**
     * РЕПЛЭЙСИНГ ПОЛЕЙ
     * @param $content
     * @param null $email
     * @return mixed
     */
    public static function replaceContent($content, $email = null) {
        if ($email) {
            $user = User::getUserDataByEmail($email);
            $user_id = $user ? $user['user_id'] : null;
        } else {
            $user_id = User::isAuth();
        }

        while (preg_match('#\[CUSTOM_FIELD_([0-9]+)\]#', $content, $matches)) {
            $field_num = (int)$matches[1];
            $field_name = "custom_field_$field_num";
            $field = CustomFields::getDataFieldByColumnName($field_name);

            if ($field) {
                $values = $user_id ? self::getUserFields($user_id) : null;
                $value = $values ? $values[$field['column_name']] : $field['default_value'];
                if ($values && in_array($field['field_type'], [self::FIELD_TYPE_CHECKBOX, self::FIELD_TYPE_MULTI_SELECT])) {
                    $values = json_decode($value, true);
                    $value = is_array($values) ? implode(',', $values) : $values;
                }
            } else {
                $value = '';
            }
            $content = str_replace($matches[0], $value, $content);
        }

        return $content;
    }


    /**
     * @param $data
     * @param $user_id
     * @param null $email
     * @return bool
     */
    public static function saveFields2Api($data, $user_id, $email = null) {
        $email = !$user_id ? $email : null;
        return self::saveUserFields($user_id, $email, $data, self::PARSE_TYPE_API);
    }
}
