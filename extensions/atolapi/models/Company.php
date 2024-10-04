<?php


namespace Atol;


/**
 * Class Company
 * @package Atol
 */
class Company{

    protected $email; //e-mail
    protected $sno; // Система налогообложения
    protected $inn; //ИНН
    protected $payment_address; // адрес магазина
    protected $vat; // ставка НДС

    public function getCompany($data){

        $this->email = $data['email'];
        $this->sno = $data['sno'];
        $this->inn = $data['inn'];
        $this->payment_address = $data ['address'];
        $this->vat = $data['nds'];

        return $company=['email'=>$this->email,'sno' =>$this->sno,'inn'=>$this->inn,'payment_address'=>$this->payment_address,'vat'=>$this->vat];
    }
//
//
//    public function setEmail($email){
//        $this -> email = $email;
//    }

//    public function getVat(){ // ставка НДС
//        return $this->vat;
//    }

}