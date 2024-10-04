<?php

/**
 * Class validator
 *
 * Валидация данных в массиве.
 * Можно использовать для проверки данных из формы
 *
 *
 */
class validator
{
    /** @var array - массив, который должен быть провалидирован */
    private $toValidate;

    /** @var bool - если true - то при ошибке, вернет json и 422 статус код, false - после валидации будет массив с ошибками */
    private $validateDataApi;

    /** @var array - Ошибки при валидации */
    private $validationErrors = [];

    /**
     * Создать объект валидатора
     *
     * @param array $toValidate - массив, который должен быть провалидирован. Формат ['поле'=>'значение']
     * @param bool $validateDataApi - если валидация используется для api(если true - то при ошибке, вернет json и 422 статус код, false - после валидации будет массив с ошибками)
     */
    public function __construct (array $toValidate, $validateDataApi = false) {
        $this->toValidate = $toValidate;
        $this->validateDataApi = $validateDataApi;
    }

    /**
     * Валидирует данные
     * Пример
     * $validator->validate([
     *   'name' => ['required', 'string', 'max'=>'16', 'min'=>'4'],
     *   'phone' => ['required', 'phone'],
     *   'id' => ['required', 'int']
     *   'email' => ['required', 'email', 'unique'=>'users:email'],
     *   'expire' => ['notNull'=>'day', 'values'=>'minute/hour/day']
     *]);
     * Проверит name - на существование, тип данных - строка, максимальный размер - 16, минимальный - 4
     * Проверит phone - на существование, телефон на формат
     * id - на существование, и тип - int
     * email - существование, формат - эл.почта, уникальность: в таблице users, колонка email
     * expire - если поля нету в массиве для валидации, или равно null, значения поля будет равно 'day'.
     *          Проверка на значение поля: поле должно быть равно 'minute' / 'hour' / 'day', иначе ошибка
     *
     * @param array $validateFields - правила валидации, формат ['имя проверяемого поля'=>['required', 'min'=>''] ].
     * Текущие поддерживаемые данные:
     * 'required' - проверка на существования поля,
     * 'string/int/array' - проверка на тип,
     * 'email'/'phone' - проверка на емайл или телефон,
     * 'min'=>'значение'/'max'=>'10' - проверка на длину
     * 'unique'=>'users:email' - проверка на уникальность в бд. значение: '<имя таблицы>:<имя колонки>'
     * 'notNull'=><значение вместо null> - если поля не существует, или оно null, то оно будет равнятся указанному значению
     * 'values'=><значение1/2/3> - если поле не равно одному из перечисленных через '/' значений - произойдет ошибка
     *  @return bool
     */
    public function validate(array $validateFields) {
        foreach ($validateFields as $fieldName => $fieldRules ) {
            //Здесь имя поля($fieldName) -> как валидировать($fieldRules)
            if (!in_array('required', $fieldRules) && !isset($this->toValidate[$fieldName])) {
                continue;
            }

            if (key_exists('notNull', $fieldRules)) {
                $this->nullToSomething($fieldName, $fieldRules['notNull']);
            }


            if (in_array('required', $fieldRules)) {
                $result = $this->validateRequired($fieldName);
                if ($result == false) {
                    break;
                }
            }

            if (in_array('trim', $fieldRules)) {
                $this->trim($fieldName);
            }

            if (key_exists('values', $fieldRules)) {
                $this->validateValues($fieldName, $fieldRules['values']);
            }

            if (key_exists('htmlentities', $fieldRules)) {
                $this->htmlentities($fieldName);
            }

            if (in_array('array', $fieldRules)) {
                $this->validateType($fieldName, 'array');
            } else {
                if (in_array('int', $fieldRules)) {
                    $this->validateType($fieldName, 'integer');
                }
            }

            if (in_array('email', $fieldRules)) {
                $this->validateEmail($fieldName);
            }

            if (in_array('phone', $fieldRules)) {
                $this->validatePhone($fieldName);
            }

            if (key_exists('unique', $fieldRules)) {
                $unique = $fieldRules['unique'];
                $unique = explode(':',$unique);//получаем массив table : column
                $this->validateUnique($fieldName, $unique[0], $unique[1]);
            }

            if (key_exists('min', $fieldRules)) {
                $this->validateMin($fieldName, $fieldRules['min']);
            }

            if (key_exists('max', $fieldRules)) {
                $this->validateMax($fieldName, $fieldRules['max']);
            }
        }
        return $this->resultHandler();
    }

    /**
     * Обработчик ошибок
     * Принимает поле и текст ошибки
     *
     * Если $validateDataApi - true, то вернет 422 статус код, и json с ошибоками
     * Если $validateDataApi - false, то запишет ошибку в массив $validationErrors
     *
     * @param $fieldName - имя поля
     * @param string $error - текст ошибки
     */
    private function errorHandler($fieldName, $error = '') {
        if ($this->validateDataApi == true) {
            http_response_code(422);
            echo json_encode([
                'status' => false,
                'message' => $error,
                'fieldName' => $fieldName,
            ]);
            exit();
        } else {
            $this->validationErrors[] = [
                'fieldName' => $fieldName,
                'message' => $error,
            ];
        }
    }

    /**
     * Обработчик результата
     * Если $validateDataApi - true, то вернет массив с исправленными ошибками
     * Если $validateDataApi - false, то отдаст массив с исправленными ошибками объедененный с $validationErrors (список ошибок)
     *
     * @return array|bool
     */
    private function resultHandler() {
        if ($this->validateDataApi == false) {
            if (isset($this->validationErrors)) {
                array_unshift($this->toValidate, $this->validationErrors);
            }
        }

        return $this->toValidate;
    }

    /**
     * Проверка на существование
     *
     * @param $fieldName
     * @return bool|void
     *
     */
    public function validateRequired($fieldName) {
        if (!isset($this->toValidate[$fieldName])) {
            return $this->errorHandler($fieldName, "Ошибка. Поле '$fieldName' отсутствует");
        }
        return true;
    }

    /**
     * Проверка на тип данных
     * (пока только int и string + array)
     * TODO: Доделать проверку на другие типы(float!!!)
     *
     * @param $fieldName
     * @param $type
     * @return bool|void
     */
    public function validateType($fieldName, $type) {
        $validate = false;

        if ($type == 'integer') {
            $validate = is_numeric($this->toValidate[$fieldName]) ? true : false;
            $this->intval($fieldName);
        }

        if (!$validate && gettype($this->toValidate[$fieldName]) != $type) {
            return $this->errorHandler($fieldName, "Ошибка. Поле '$fieldName' должно быть типом $type");
        }

        return true;
    }

    /**
     * Проверка на уникальность в БД
     *
     * @param $fieldName
     * @param $table - таблица где нужно проверить
     * @param $column - колонка, которую нужно проверить
     * @return bool|void
     */
    public function validateUnique($fieldName, $table, $column) {
        $sql = "SELECT * FROM `".PREFICS."$table` WHERE `$column` = '".$this->toValidate[$fieldName]."' LIMIT 1";
        $db = Db::getConnection();
        $result = $db->query($sql);
        $result = $result->fetch();
        if ($result != false) {
            return $this->errorHandler($fieldName, "Ошибка. Поле '$fieldName' не уникально");
        }
        return true;
    }

    /**
     * Проверяет строку на минимальный размер
     *
     * @param $fieldName
     * @param $minLength
     * @return bool|void
     */
    public function validateMin($fieldName, $minLength) {
        $strlen = strlen($this->toValidate[$fieldName]);
        if ($strlen < $minLength) {
            return $this->errorHandler($fieldName, "Ошибка. Для поля '$fieldName' минимальный размер $minLength символов");
        }
        return true;
    }

    /**
     * Проверяет строку на максимальный размер
     * @param $fieldName
     * @param $maxLength
     * @return bool|void
     */
    public function validateMax($fieldName, $maxLength) {
        $strlen = strlen($this->toValidate[$fieldName]);
        if ($strlen > $maxLength) {
            return $this->errorHandler($fieldName, "Ошибка. Для поля '$fieldName' максимальный размер $maxLength символов");
        }
        return true;
    }

    /**
     * Проверяет email
     * @param $fieldName
     * @return bool|void
     */
    public function validateEmail($fieldName) {
        if (!filter_var($this->toValidate[$fieldName], FILTER_VALIDATE_EMAIL)) {
            return $this->errorHandler($fieldName, "Ошибка. Поле '$fieldName' - электронная почта указана некорректно");
        }
        return true;
    }

    /**
     * Проверяет поле телефон на формат
     *
     * @param $fieldName
     * @return bool|void
     */
    public function validatePhone($fieldName) {
        if (preg_match('/^\s?(\+\s?7|8)([- ()]*\d){10}$/', $this->toValidate[$fieldName]) != 1) {
            return $this->errorHandler($fieldName, "Ошибка. Поле '$fieldName' - номер телефона указан некорректно");
        }

        return true;
    }

    /**
     * Заменит null на что-либо
     *
     * @param $fieldName
     * @param $value - на что заменить
     *
     * @return mixed
     */
    public function nullToSomething($fieldName, $value) {
        if (!isset($this->toValidate[$fieldName])) {
            return $this->toValidate[$fieldName] = $value;
        }
        return true;
    }


    /**
     * Проверяет поле на определненные значения. Если
     *
     * @param $fieldName
     * @param string $values - 'знач1/знач2/знач3' - вернет ошибку, если поле не будет равно одному из этих значений
     *
     * @return bool|void
     */
    public function validateValues($fieldName, $values) {
        $valuesarr = explode('/', $values);
        if (!in_array($this->toValidate[$fieldName], $valuesarr)) {
            return $this->errorHandler($fieldName, "Ошибка. Поле '$fieldName' - должно иметь значение одного из этого: '$values'");
        }
        return true;
    }

    /**
     * Выполнить функицию trim для поля
     *
     * @param $fieldName
     *
     * @return string
     */
    public function trim($fieldName) {
        return $this->toValidate[$fieldName] = trim($this->toValidate[$fieldName]);
    }

    /**
     * Выполнить функцию htmlentities для поля
     * @param $fieldName
     *
     * @return string
     */
    public function htmlentities($fieldName) {
        return $this->toValidate[$fieldName] = htmlentities($this->toValidate[$fieldName]);
    }

    /**
     * Выполнить функцию intval для поля
     * @param $fieldName
     *
     * @return string
     */
    public function intval($fieldName) {
        return $this->toValidate[$fieldName] = intval($this->toValidate[$fieldName]);
    }
}