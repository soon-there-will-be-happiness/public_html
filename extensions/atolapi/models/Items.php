<?php


namespace Atol;


class Items{
    protected $vat;
    protected $items = [];
    protected $total = 0;


    public function getItems($data){

        foreach ($data as $el){
            $vat=['type'=>$el['vat']];
            $item = [
                'name'=>$el['name'],
                'price'=>(integer) $el['price'],
                'quantity'=>$el['quantity'],
                'sum'=>(float) $el['price']*(float)$el['quantity'],
                'measurement_unit'=>$el['measurement_unit'],
                'payment_method'=>$el['payment_method'],
                'payment_object'=>$el['payment_object'],
                'vat'=>$vat
            ];
          array_push($this->items,$item);
            $this->total = $this->total+(float)$item['sum'];
        }

        return ['items'=>$this->items,'total'=>$this->total] ;
    }

}