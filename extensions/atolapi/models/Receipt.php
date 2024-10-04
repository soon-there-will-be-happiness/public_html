<?php


namespace Atol;


class Receipt{
    protected $callback_url = '';
    protected $timestamp = '';
    protected $total = '';
    protected $items = [];
    protected $payments = [];

    public function __construct($data){

        $this->callback_url = 'http://'. $_SERVER['SERVER_NAME']; // куда будет приходить ответ с атол
        $this->timestamp = date('d-m-Y H:i:s');
        $this->total = $data['total'];
        $this->items = $data['items'];
        $this->payments = ['type'=>1,'sum'=>$this->total];

    }

    public function Receipt($company,$client){

        $external_id='pay_order'.$company['inn'].time();
        $receipt=[
            'external_id'=>$external_id,
            'receipt'=>[
                'client'=>[
                    'email'=>$client['email'],
                    'phone'=>$client['phone']
                ],
                'company'=>[
                    'email'=>$company['email'],
                    'sno'=>$company['sno'],
                    'inn'=>$company['inn'],
                    'payment_address'=>$company['payment_address']
                ],
                'items'=>$this->items,
                'payments'=>[$this->payments],
                'total'=> $this->total,
                 ],
            'service'=>['callback_url'=>$this->callback_url],
            'timestamp'=>$this->timestamp
        ];
       return $receipt;
    }

}