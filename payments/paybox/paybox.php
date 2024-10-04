<?php


class paybox
{
    //TODO: добавить допольнительные значения из api https://paybox.money/docs/#inicializaciya_plateja
    protected $pg_order_id;

    protected $pg_merchant_id;

    protected $pg_amount;

    protected $pg_description;

    protected $pg_salt;

    protected $pg_sig = '1323';

    /** @var string url - куда вернуть юзера в случае удачи */
    protected $pg_success_url;
    /** @var string url - куда вернуть юзера в случае удачи */
    protected $pg_failure_url;

    protected $pg_currency = 'RUB';

    protected $pg_user_email;

    protected $pg_user_phone;

    /*protected $pg_testing_mode = 1;*/

    /**
     * paybox constructor.
     * @param int $order_id
     * @param int $merchant_id
     * @param int $amount
     * @param string $description
     * @param string $salt
     */
    public function __construct(int $order_id, int $merchant_id, int $amount, string $description, string $salt, $secret, $user_email, $user_phone) {

        $protocol = isset($_SERVER["HTTPS"]) ? "https://" : "http://";

        $this->pg_order_id = $order_id;
        $this->pg_merchant_id = $merchant_id;
        $this->pg_amount = $amount;
        $this->pg_description = $description;
        $this->pg_salt = $salt;
        $this->pg_user_email = $user_email;
        $this->pg_user_phone = $user_phone;
        $this->pg_success_url  = $protocol . $_SERVER['HTTP_HOST'].'/payments/paybox/success.php';
        $this->pg_failure_url = $protocol . $_SERVER['HTTP_HOST'].'/payments/paybox/fail.php';
        $this->generateSignature($secret);
    }

    /**
     * Создает и возращает подпись
     *
     * @return string
     */
    public function generateSignature($secret) {
        //Получить данные
        $dataArray = $this->getDataArray();
        unset($dataArray['pg_sig']);
        //Сделать плоский массив
        $flatArray = self::makeFlatParamsArray($dataArray);

        ksort($flatArray); // Сортировка по ключю
        array_unshift($flatArray, 'payment.php'); // Добавление в начало имени скрипта
        array_push($flatArray, $secret); // Добавление в конец секретного ключа
        $this->pg_sig = md5(implode(';', $flatArray)); // Полученная подпись

        return $this->pg_sig;
    }

    /**
     * Функция преобразует массив в плоский вид
     *
     * @param $arrParams
     * @param string $parent_name
     * @return array|string[]
     */
    public static function makeFlatParamsArray($arrParams, $parent_name = '') {
        $arrFlatParams = [];
        $parent_name = '';
        $i = 0;
        foreach ($arrParams as $key => $val) {
            $i++;
            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $key . sprintf('%03d', $i);
            if (is_array($val)) {
                $arrFlatParams = array_merge($arrFlatParams, self::makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }

        return $arrFlatParams;
    }

    /**
     * Возращает все значения в виде массива
     *
     * @return array
     */
    public function getDataArray() {
        return get_object_vars($this);
    }

    public function generateHiddenInputs() {
        $inputs = '';
        $objectData = $this->getDataArray();
        foreach ($objectData as $key => $data) {
            $inputs .= "<input type='hidden' name='$key' value='$data'>";
        }
        return $inputs;
    }

    public static function generateSign($data, $secret) {
        unset($data['pg_sig']);
        $flatArray = self::makeFlatParamsArray($data);

        ksort($flatArray); // Сортировка по ключю
        array_unshift($flatArray, 'result.php'); // Добавление в начало имени скрипта
        array_push($flatArray, $secret); // Добавление в конец секретного ключа

        return md5(implode(';', $flatArray));
    }

}