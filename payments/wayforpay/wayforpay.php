<?php 

class WayForPay
{
    const ORDER_APPROVED = 'Approved';
    const ORDER_HOLD_APPROVED = 'WaitingAuthComplete';
    const ORDER_IS_PENDING = 'Pending';

    const ORDER_SEPARATOR = '#';

    const SIGNATURE_SEPARATOR = ';';

    const URL = "https://secure.wayforpay.com/pay";

    protected $secret_key = '';
    protected $keysForResponseSignature = array(
        'merchantAccount',
        'orderReference',
        'amount',
        'currency',
        'authCode',
        'cardPan',
        'transactionStatus',
        'reasonCode'
    );

    /** @var array */
    protected $keysForSignature = array(
        'merchantAccount',
        'merchantDomainName',
        'orderReference',
        'orderDate',
        'amount',
        'currency',
        'productName',
        'productCount',
        'productPrice'
    );


    /**
     * @param $option
     * @param $keys
     * @return string
     */
    public function getSignature($option, $keys)
    {
        $hash = array();
        foreach ($keys as $dataKey) {
            if (!isset($option[$dataKey])) {
                $option[$dataKey] = '';
            }
            if (is_array($option[$dataKey])) {
                foreach ($option[$dataKey] as $v) {
                    $hash[] = $v;
                }
            } else {
                $hash [] = $option[$dataKey];
            }
        }

        $hash = implode(self::SIGNATURE_SEPARATOR, $hash);
        return hash_hmac('md5', $hash, $this->getSecretKey());
    }


    /**
     * @param $options
     * @return string
     */
    public function getRequestSignature($options)
    {
        return $this->getSignature($options, $this->keysForSignature);
    }

    /**
     * @param $options
     * @return string
     */
    public function getResponseSignature($options)
    {
        return $this->getSignature($options, $this->keysForResponseSignature);
    }


    /**
     * @param array $data
     * @return string
     */
    public function getAnswerToGateWay($data)
    {
        $time = time();
        $responseToGateway = array(
            'orderReference' => $data['orderReference'],
            'status' => 'accept',
            'time' => $time
        );
        $sign = array();
        foreach ($responseToGateway as $dataKey => $dataValue) {
            $sign [] = $dataValue;
        }
        $sign = implode(self::SIGNATURE_SEPARATOR, $sign);
        $sign = hash_hmac('md5', $sign, $this->getSecretKey());
        $responseToGateway['signature'] = $sign;

        return json_encode($responseToGateway);
    }

    /**
     * @param $response
     * @return bool|string
     */
    public function isPaymentValid($response)
    {
        
        if (!isset($response['merchantSignature']) && isset($response['reason'])) {
            return $response['reason'];
        }
		$sign = $this->getResponseSignature($response);
        if ($sign != $response['merchantSignature']) {
            return 'An error has occurred during payment ';
        }

        if (
            $response['transactionStatus'] == self::ORDER_APPROVED ||
            $response['transactionStatus'] == self::ORDER_HOLD_APPROVED ||
            $response['transactionStatus'] == self::ORDER_IS_PENDING		
        ) {
            return true;
        }

        return false;
    }

    public function setSecretKey($key)
    {
        $this->secret_key = $key;
    }

    public function getSecretKey()
    {
        return $this->secret_key;
    }
}
?>