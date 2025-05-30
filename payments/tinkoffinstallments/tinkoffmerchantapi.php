<?php

class TinkoffMerchantAPI
{
    protected $_api_url;
    protected $_terminalKey;
    protected $_secretKey;
    protected $_paymentId;
    protected $_status;
    protected $_error;
    protected $_response;
    protected $_paymentUrl;

    public function __construct($terminalKey, $secretKey)
    {
        $this->_api_url = 'https://securepay.tinkoff.ru/v2/';
        $this->_terminalKey = $terminalKey;
        $this->_secretKey = $secretKey;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'paymentId':
                return $this->_paymentId;
            case 'status':
                return $this->_status;
            case 'error':
                return $this->_error;
            case 'paymentUrl':
                return $this->_paymentUrl;
            case 'response':
                return htmlentities($this->_response);
            default:
                if ($this->_response) {
                    if ($json = json_decode($this->_response, true)) {
                        foreach ($json as $key => $value) {
                            if (strtolower($name) == strtolower($key)) {
                                return $json[$key];
                            }
                        }
                    }
                }

                return false;
        }
    }

    /**
     * @param $args mixed You could use associative array or url params string
     * @return bool
     * @throws HttpException
     */
    public function init($args)
    {
        return $this->buildQuery('Init', $args);
    }


    public function getState($args)
    {
        return $this->buildQuery('GetState', $args);
    }

    public function confirm($args)
    {
        return $this->buildQuery('Confirm', $args);
    }

    public function charge($args)
    {
        return $this->buildQuery('Charge', $args);
    }

    public function addCustomer($args)
    {
        return $this->buildQuery('AddCustomer', $args);
    }

    public function getCustomer($args)
    {
        return $this->buildQuery('GetCustomer', $args);
    }

    public function removeCustomer($args)
    {
        return $this->buildQuery('RemoveCustomer', $args);
    }

    public function getCardList($args)
    {
        return $this->buildQuery('GetCardList', $args);
    }

    public function removeCard($args)
    {
        return $this->buildQuery('RemoveCard', $args);
    }

    /**
     * Builds a query string and call sendRequest method.
     * Could be used to custom API call method.
     *
     * @param string $path API method name
     * @param mixed $args query params
     *
     * @return mixed
     * @throws HttpException
     */
    public function buildQuery($path, $args)
    {
        $url = $this->_api_url;
        if (is_array($args)) {
            if (!array_key_exists('TerminalKey', $args)) {
                $args['TerminalKey'] = $this->_terminalKey;
            }
            if (!array_key_exists('Token', $args)) {
                $args['Token'] = $this->_genToken($args);
            }
        }
        $url = $this->_combineUrl($url, $path);


        return $this->_sendRequest($url, $args);
    }

    /**
     * Generates Token
     *
     * @param $args
     * @return string
     */
    private function _genToken($args)
    {
        $token = '';
        $args['Password'] = $this->_secretKey;
        ksort($args);

        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $token .= $arg;
            }
        }
        $token = hash('sha256', $token);

        return $token;
    }

    protected function getHash($params)
    {
        $arrayObjRequest = new ArrayObject($params);
        $arrayObjRequest->ksort();
        $tokenStr = '';

        foreach ($arrayObjRequest as $key => $val) {
            $tokenStr .= $val;
        }

        return hash('sha256', $tokenStr);
    }
    /**
     * Combines parts of URL. Simply gets all parameters and puts '/' between
     *
     * @return string
     */
    private function _combineUrl()
    {
        $args = func_get_args();
        $url = '';
        foreach ($args as $arg) {
            if (is_string($arg)) {
                if ($arg[strlen($arg) - 1] !== '/') $arg .= '/';
                $url .= $arg;
            } else {
                continue;
            }
        }

        return $url;
    }

    /**
     * Main method. Call API with params
     *
     * @param $api_url
     * @param $args
     * @return bool|string
     * @throws HttpException
     */
    private function _sendRequest($api_url, $args)
    {
        $this->_error = '';
        if (is_array($args)) {
            $args = json_encode($args);
        }

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));

            $out = curl_exec($curl);
            $this->_response = $out;
            $json = json_decode($out);

            if ($json) {
                if (@$json->ErrorCode !== "0") {
                    $this->_error = @$json->Details;
                } else {
                    $this->_paymentUrl = @$json->PaymentURL;
                    $this->_paymentId = @$json->PaymentId;
                    $this->_status = @$json->Status;
                }
            }

            curl_close($curl);

            return $out;

        } else {
            throw new HttpException('Can not create connection to ' . $api_url . ' with args ' . $args, 404);
        }
    }
}