<?php defined('BILLINGMASTER') or die;

class Db {

    private static $db = null;

    /**
     * @return PDO
     */
    public static function getConnection() {
        if (self::$db) {
            return self::$db;
        } else {
            $paramPath = ROOT . '/config/config.php';
            $params = include($paramPath);

            //$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4"; my be error
			$dsn = "mysql:host=$host;dbname=$dbname";
            self::$db = new PDO($dsn, $user, $password);
            self::$db->exec("set names utf8mb4");
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$db;
    }


    /**
     * @param $fields
     * @param $table
     * @param int $type
     * @param array $extensions
     * @param string $conditions
     * @return string
     */
    public static function getInsertSQL($fields, $table, $type = 1, $extensions = [], $conditions = "") {
        $str_fields = $str_values = '';
        foreach ($fields as $_fields) {
            foreach ($_fields as $field) {
                $str_fields .= ($str_fields ? ', ' : '').$field;
                if ($type == 1) {
                    $str_values .= ($str_values ? ', ' : '').":$field";
                } else {
                    $str_values .= ($str_values ? ', ' : '')."$field";
                }
            }
        }

        if ($extensions) {
            foreach ($extensions as $field => $value) {
                $str_fields .= ", $field";
                $str_values .= ($type == 1 ? ", :$value" : ", $value");
            }
        }

        if ($type == 1) {
            $query = "INSERT INTO $table ($str_fields) VALUES ($str_values)";
        } else {
            $query = "INSERT INTO $table ($str_fields) SELECT $str_values FROM $table WHERE $conditions";
        }

        return $query;
    }

    /**
     * @param $fields
     * @param $table
     * @param $conditions
     * @return string
     */
    public static function getUpdateSQL($fields, $table, $conditions) {
        $str_fields = '';
        foreach ($fields as $type => $_fields) {
            foreach ($_fields as $field) {
                $str_fields .= ($str_fields ? ', ' : '')."$field = :$field";
            }
        }

        return "UPDATE $table SET $str_fields WHERE $conditions";
    }


    /**
     * @param PDO $db
     * @param $sql
     * @param $fields
     * @param $data
     * @return bool|PDOStatement
     */
    public static function bindParams(PDO $db, $sql, $fields, $data) {
        $result = $db->prepare($sql);
        foreach ($fields as $type => $_fields) {
            foreach ($_fields as $field) {
                if ($type == 'integer') {
                    $result->bindParam(":$field", $data[$field], PDO::PARAM_INT);
                } else {
                    $result->bindParam(":$field", $data[$field], PDO::PARAM_STR);
                }
            }
        }

        return $result;
    }

    public static function _insert_or_upd(string $table_name, array $ins_data, array $upd_data){
        $sql_fields = new sqlGenerater($ins_data, 'insert');
        $str_fields = $sql_fields(' VALUES ');

        $sql_upd = new sqlGenerater($upd_data, 'update');
        $str_upd = $sql_upd(', ');

        $db = self::getConnection();
        $sql = "
            INSERT INTO ".PREFICS."{$table_name}
            $str_fields
            ON DUPLICATE KEY 
                UPDATE {$str_upd}
        ";

        $result = $db->prepare($sql);
        $result = $sql_fields->bindParams($result);
        $result = $sql_upd->bindParams($result);

        return $result->execute();
    }

    /**
     * ПОЛУЧИТЬ ДАННЫЕ ВСЕЙ ТАБЛИЦЫ
     * @param  string $table_name имя таблицы без префикса
     * @param  array  $fields     ? имена столбцов что надо вернуть
     * @param  array  $sort_key   ? имя столбца для присваивания индекса в массиве данных
     * @param  bool   $to_arr     ? перевести строки в массив (при возможности) 
     * @return bool
     */
    public static function _select_all(string $table_name, array $fields = [], string $sort_key = '', bool $to_arr = false) {
        $fields = empty($fields)
            ? '*'
            : implode(',', $fields);  

        $db = self::getConnection();
        $sql = "
            SELECT {$fields}
            FROM " . PREFICS . "$table_name 
        ";
        $result = $db->query($sql);

        $id = 0;
        $datas = [];

        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            if($sort_key && isset($row[$sort_key]))
                $id = $row[$sort_key];

            else
                $sort_key = false;

            if($to_arr){
                foreach ($row as $key => $value)
                    $row[$key] = self::StringToArray($value);
            }

            $datas[$id] = $row;

            if(is_numeric($id)) 
                $id++;
        }

        if(empty($datas))
            return null;

        if(!$to_arr)
            return $datas;

        foreach ($datas as $i => $data){
            foreach ($data as $key => $value){

                $datas[$i][$key] = self::StringToArray($value);
            }
        }

        return $datas;
    } 


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ИЗ ТАБЛИЦ
     * @param  string $table_name имя таблицы без префикса
     * @param  string $J_table_name имя таблицы join без префикса
     * @param  array  $on_joins   массив пар ключей для JPON ON
     * @param  array  $wheres     массивы данных для where
     * @param  array  $fields     ? имена столбцов что надо вернуть
     * @param  bool   $to_arr     ? перевести строки в массив (при возможности) 
     * @return bool
     */
    public static function _select_one_join(
        string $table_name, string $J_table_name, array $on_joins,  
        array $wheres,      array $fields = [],   bool $to_arr = false
    ) {
        $sql_wheres = new sqlGenerater($wheres, 'column', [], [], ' ');
        $str_wheres = $sql_wheres(' AND ');

        $fields = empty($fields)
            ? '*'
            : implode(',', $fields);        

        $join_str = '';
        foreach ($on_joins as $on_join) 
            $join_str .= (empty($join_str) ? '' : ' AND ') . "1t.{$on_join[0]} = 2t.{$on_join[1]}";

        $db = self::getConnection();
        $sql = "
            SELECT {$fields}
            FROM " . PREFICS . "$table_name as 1t
            LEFT JOIN " . PREFICS . "$J_table_name as 2t 
                ON {$join_str}
            WHERE {$str_wheres}
            LIMIT 1
        ";
        
        $result = $db->prepare($sql);
        $result = $sql_wheres->bindParams($result);
        $result->execute();

        $data = $result->fetch(PDO::FETCH_ASSOC);

        if(empty($data))
            return null;

        if(!$to_arr)
            return $data;

        foreach ($data as $key => $value)
            $data[$key] = self::StringToArray($value);

        return $data;
    }


    /**
     * ПОЛУЧИТЬ ДАННЫЕ ИЗ ТАБЛИЦЫ
     * @param  string $table_name имя таблицы без префикса
     * @param  array  $wheres     данные для where
     * @param  array  $fields     ? имена столбцов что надо вернуть
     * @param  bool   $to_arr     ? перевести строки в массив (при возможности) 
     * @return bool
     */
    public static function _select_one(string $table_name, array $wheres, array $fields = [], bool $to_arr = false) {
        $sql_wheres = new sqlGenerater($wheres, 'column');
        $str_wheres = $sql_wheres(' AND ');

        $fields = empty($fields)
            ? '*'
            : implode(',', $fields);        

        $db = self::getConnection();
        $sql = "
            SELECT {$fields}
            FROM " . PREFICS . "$table_name 
            WHERE $str_wheres
            LIMIT 1
        ";
        $result = $db->prepare($sql);
        $result = $sql_wheres->bindParams($result);
        $result->execute();

        $data = $result->fetch(PDO::FETCH_ASSOC);

        if(empty($data))
            return null;

        if(!$to_arr)
            return $data;

        foreach ($data as $key => $value)
            $data[$key] = self::StringToArray($value);

        return $data;
    }

    /**
     * ОБНОВИТЬ ДАННЫЕ В ТАБЛИЦЕ ПО МАССИВУ
     * @param  string $table_name имя таблицы без префикса
     * @param  array  $data       данные для update
     * @param  array  $wheres     данные для where
     * @return bool
     */
    public static function _update(string $table_name, array $data, array $wheres, array $fields = []) {
        $sql_fields = new sqlGenerater($data, 'column', $fields);
        $str_fields = $sql_fields(', ');

        $sql_whiles = new sqlGenerater($wheres, 'column', [], ($sql_fields->str + $sql_fields->int));
        $str_whiles = $sql_whiles(' AND ');

        $db = self::getConnection();
        $sql = "
            UPDATE " . PREFICS . "$table_name 
            SET $str_fields 
            WHERE $str_whiles
        ";
        $result = $db->prepare($sql);
        $result = $sql_fields->bindParams($result);
        $result = $sql_whiles->bindParams($result);

        return (bool) $result->execute();
    }


    public static function StringToArray($string){
        if(is_array($string))
            return $string;
        if ($string === null)
            return null;
        
        $res = $string;

         # докодировка base64
        $pre_dec = @ base64_encode(base64_decode($string, true)) === $string
            ? base64_decode($string)
            : $string;

        # докодировка json
        if(in_array(substr($pre_dec, 0, 1), ['[', '{'])
            && ($dec = @ json_decode($pre_dec, true)) && json_last_error() === JSON_ERROR_NONE
            && is_array($dec)
        )
            $res = $dec;

        # докодировка serialize array
        elseif(substr($pre_dec, 0, 2) == 'a:'
            && ($dec = @ unserialize($pre_dec)) && $dec !== false
            && is_array($dec)
        )
            $res = $dec;

        return $res;
    }

}

class sqlGenerater{
    public $str = [];
    public $int = [];
    public $type;
    public $fields;

    public $copy = [];
    public $table;

    private $bind_prms = [];
    private $res_string = '';

    private $pre_res = [];

    function __construct(array $data, string $sql_type = 'column', array $fields = [], $copy = [], string $table = ''){
        $this->fields = empty($fields) ? false : $fields;
        $this->table = empty($table) ? '' : $table . '.';

        if(!empty($table) && class_exists('System') && System::get_caller(__FUNCTION__) == '_select_one_join'){
            $use_table = false;
            foreach ($data as $key => $value) {
                if(is_array($value)){
                    $use_table = true;

                    $explr = new sqlGenerater($value, $sql_type, $fields, $copy, $key);
                    $this->bind_prms = array_merge($this->bind_prms, $explr->getbindParams());
                    $this->res_string .= (empty($this->res_string) ? '' : '#&0;') . $explr->getResString();
                }
            }
            if($use_table)
                return $this;
        }

        $this->str = isset($data['string']) && is_array($data['string']) ? $data['string'] : [];
        $this->int = isset($data['integer']) && is_array($data['integer']) ? $data['integer'] : [];
        unset($data['string'], $data['integer']);

        $this->copy = $copy;

        if(!empty($data) && is_array($data)){
            foreach ($data as $key => $value) {                
                if(isset($this->str[$key]) || isset($this->int[$key]))
                    continue;

                if(is_array($value))
                    $value = serialize($value);

                if(is_string($value))
                    $this->str[$key] = $value;

                elseif(is_numeric($value) || is_bool($value))
                    $this->str[$key] = (int) $value;

                elseif($value === null)
                    $this->str[$key] = null;
            }
        }

        $this->process(strtolower($sql_type));
    }

    public function __invoke($delimiter = ', '){

        return str_replace('#&0;', $delimiter, $this->res_string);
    }

    public function getResString(){
        return $this->res_string;
    }

    public function getbindParams(){
        return $this->bind_prms;
    }

    public function bindParams($result){
        foreach ($this->bind_prms as $bind_prm) 
            $result->bindParam(... $bind_prm);

        return $result;
    }


    private function process(string $sql_type){
        $fields = $this->fields;
        $str = $this->str;
        $int = $this->int;

        if(strtolower($sql_type) == 'insert'){
            $this->addSQL('line', $str, 'STR', '`');
            $this->addSQL('line', $int, 'INT', '`');
        }

        else{
            $this->addSQL('column', $str, 'STR');
            $this->addSQL('column', $int, 'INT');
        }
    }

    private function getBPfield(string $field): string{

        if(!empty($this->copy) && is_array($this->copy)){

            if(array_keys($this->copy) === range(0, count($this->copy) - 1) && in_array($field, $this->copy))
                while(in_array($field, $this->copy))
                    $field .= '_d';
            
            elseif(isset($this->copy[$field]) || array_key_exists($field, $this->copy))
                while(isset($this->copy[$field]) || array_key_exists($field, $this->copy))
                    $field .= '_d';
        }

        return ":{$field}";
    }

    private function addBP($field, $value, $type){
        $this->bind_prms[] = [$field, $value, mb_strtolower($type) == 'int' ? 1 : 2];
    }

    private function addSQL($sql_type, $data, $type, $quote = ''){

        if($sql_type == 'line'){
            $pre_res =& $this->pre_res;
            if(empty($this->pre_res)) 
                $pre_res = ['', ''];

            foreach ($data as $field => $value){
                if($this->fields && !in_array($field, $this->fields))
                    continue;
                $bp_field = $this->getBPfield($field);

                $this->addBP($bp_field, $value, $type);
                $pre_res[0] .= ($pre_res[0] ? ', ' : '') . "{$quote}{$this->table}{$field}{$quote}";
                $pre_res[1] .= ($pre_res[1] ? ', ' : '') . "{$bp_field}";
            }

            $this->res_string = "({$pre_res[0]}) #&0; ({$pre_res[1]})";
        }

        elseif($sql_type = 'column'){
            foreach ($data as $field => $value) {
                if($this->fields && !in_array($field, $this->fields))
                    continue;
                $bp_field = $this->getBPfield($field);

                $this->addBP($bp_field, $value, $type);
                $this->res_string .= ($this->res_string ? '#&0;' : '') . "{$this->table}{$field} = {$bp_field}";
            }
        }
    }

}