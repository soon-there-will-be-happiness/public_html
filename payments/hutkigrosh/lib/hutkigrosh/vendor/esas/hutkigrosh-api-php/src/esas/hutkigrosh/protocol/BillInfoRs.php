<?php
/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 16.02.2018
 * Time: 12:49
 */

namespace esas\hutkigrosh\protocol;


class BillInfoRs extends HutkigroshRs
{
    private $eripId;
    private $invId;
    private $fullName;
    private $mobilePhone;
    private $email;
    private $fullAddress;
    private $amount;
    private $currency;
    private $products;
    private $status;

    /**
     * @return mixed
     */
    public function getEripId()
    {
        return $this->eripId;
    }

    /**
     * @param mixed $eripId
     */
    public function setEripId($eripId)
    {
        $this->eripId = trim($eripId);
    }

    /**
     * @return mixed
     */
    public function getInvId()
    {
        return $this->invId;
    }

    /**
     * @param mixed $invId
     */
    public function setInvId($invId)
    {
        $this->invId = intval($invId);
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = trim($fullName);
    }

    /**
     * @return mixed
     */
    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    /**
     * @param mixed $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = is_array($mobilePhone) ? trim(implode(',', $mobilePhone)) : trim($mobilePhone);
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = trim($email);
    }

    /**
     * @return mixed
     */
    public function getFullAddress()
    {
        return $this->fullAddress;
    }

    /**
     * @param mixed $fullAddress
     */
    public function setFullAddress($fullAddress)
    {
        $this->fullAddress = is_array($fullAddress) ? trim(implode(',', $fullAddress)) : trim($fullAddress);
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = trim($amount);
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = trim($currency);
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param mixed $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = trim($status);
    }

    public function isStatusPayed()
    {
        return $this->status == 'Payed';
    }

    public function isStatusCanceled()
    {
        return in_array($this->status, array('Outstending', 'DeletedByUser', 'PaymentCancelled'));
    }

    public function isStatusPending()
    {
        return in_array($this->status, array('PaymentPending', 'NotSet'));
    }
}