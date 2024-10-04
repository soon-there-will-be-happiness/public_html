<?php

namespace esas\hutkigrosh\controllers;

use esas\hutkigrosh\lang\Translator;
use esas\hutkigrosh\protocol\BillInfoRq;
use esas\hutkigrosh\protocol\BillInfoRs;
use esas\hutkigrosh\protocol\HutkigroshProtocol;
use esas\hutkigrosh\wrappers\ConfigurationWrapper;
use esas\hutkigrosh\wrappers\OrderWrapper;
use Exception;
use Throwable;

/**
 * Created by PhpStorm.
 * User: nikit
 * Date: 22.03.2018
 * Time: 11:37
 */
abstract class ControllerNotify extends Controller
{
    /**
     * @var OrderWrapper
     */
    protected $localOrderWrapper;

    /**
     * @var BillInfoRs
     */
    protected $billInfoRs;

    public function __construct(ConfigurationWrapper $configurationWrapper, Translator $translator)
    {
        parent::__construct($configurationWrapper, $translator);
    }

    /**
     * @param $billId
     * @throws Exception
     */
    public function process($billId)
    {
        try {
            $loggerMainString = "Bill[" . $billId . "]: ";
            $this->logger->info($loggerMainString . "Controller started");
            if (empty($billId))
                throw new Exception('Wrong billid[' . $billId . "]");
            $this->logger->info($loggerMainString . "Loading order data from Hutkigrosh gateway...");
            $hg = new HutkigroshProtocol($this->configurationWrapper);
            $resp = $hg->apiLogIn();
            if ($resp->hasError()) {
                $hg->apiLogOut();
                throw new Exception($resp->getResponseMessage(), $resp->getResponseCode());
            }

            $this->billInfoRs = $hg->apiBillInfo(new BillInfoRq($billId));
            $hg->apiLogOut();
            if ($this->billInfoRs->hasError())
                throw new Exception($resp->getResponseMessage(), $resp->getResponseCode());
            $this->logger->info($loggerMainString . 'Loading local order object for id[' . $this->billInfoRs->getInvId() . "]");
            $this->localOrderWrapper = $this->getOrderWrapperByOrderNumber($this->billInfoRs->getInvId());

            if (empty($this->localOrderWrapper))
                throw new Exception('Can not load order info for id[' . $this->billInfoRs->getInvId() . "]");

            if ($this->billInfoRs->getFullName() != $this->localOrderWrapper->getFullName() || $this->billInfoRs->getAmount() != $this->localOrderWrapper->getAmount()) {
                throw new Exception("Unmapped purchaseid: localFullname[" . $this->localOrderWrapper->getFullName()
                    . "], remoteFullname[" . $this->billInfoRs->getFullName()
                    . "], localAmount[" . $this->localOrderWrapper->getAmount()
                    . "], remoteAmount[" . $this->billInfoRs->getAmount() . "]");
            }

            if ($this->billInfoRs->isStatusPayed()) {
                $this->logger->info($loggerMainString . "Remote order status is 'Payed'");
                $this->onStatusPayed();
            } elseif ($this->billInfoRs->isStatusCanceled()) {
                $this->logger->info($loggerMainString . "Remote order status is 'Canceled'");
                $this->onStatusCanceled();
            } elseif ($this->billInfoRs->isStatusPending()) {
                $this->logger->info($loggerMainString . "Remote order status is 'Pending'");
                $this->onStatusPending();
            }
            $this->logger->info($loggerMainString . "Controller ended");
        } catch (Throwable $e) {
            $this->logger->error($loggerMainString . "Controller exception! ", $e);
        } catch (Exception $e) { // для совместимости с php 5
            $this->logger->error($loggerMainString . "Controller exception! ", $e);
        } finally {
            return $this->billInfoRs;
        }
    }

    /**
     * По локальному номеру счета (номеру заказа) возвращает wrapper
     * @param $orderNumber
     * @return mixed
     */
    public abstract function getOrderWrapperByOrderNumber($orderNumber);

    public function updateStatus($status){
        if (isset($status) && $this->localOrderWrapper->getStatus() != $status) {
            $this->logger->info("Setting status[" . $status . "] for order[" . $this->billInfoRs->getInvId() . "]...");
            $this->localOrderWrapper->updateStatus($status);
        }
    }

    public function onStatusPayed(){
        $this->updateStatus($this->configurationWrapper->getBillStatusPayed());
    }

    public function onStatusCanceled(){
        $this->updateStatus($this->configurationWrapper->getBillStatusCanceled());
    }

    public function onStatusPending(){
        $this->updateStatus($this->configurationWrapper->getBillStatusPending());
    }
}