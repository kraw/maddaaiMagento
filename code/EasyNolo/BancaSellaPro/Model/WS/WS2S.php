<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 22/01/17
 * Time: 01:23
 */

namespace EasyNolo\BancaSellaPro\Model\WS;

class WS2S extends AbstractWebService
{
    const PATH_WS_CRYPT_DECRIPT = '/gestpay/gestpayws/WSS2S.asmx?WSDL';

    public function executePaymentS2S($params){
        $result = $this->client->callPagamS2S($params);
        $this->setResponse($result, 'callPagamS2SResult');
        return $this;
    }

    public function executeCallReadTrxS2S($params) {
        $result = $this->client->callReadTrxS2S($params);
        $this->setResponseCallReadTrxS2S($result, 'callReadTrxS2S');
        return $this;
    }

    public function capturePayment($payment, $order, $params){
        $result = $this->client->callSettleS2S($params);
        $result = simplexml_load_string($result->callSettleS2SResult->any);
        if ($result->TransactionResult == "KO") {
            $payment->setIsTransactionPending(true);
            $message = __('Capture amount of %1 online failed: %2', $order->formatPriceTxt($params['amount']), $result->ErrorDescription);
            $order->addStatusHistoryComment($message, false);
            throw new \Exception($message);
        }
        $message = __('Capture amount of %1 online done.', $order->formatPriceTxt($params['amount']));
        $order->addStatusHistoryComment($message, false);
    }

    private function setResponse($result, $method){

        $this->_helper->log('Save S2S response params - '.$method);

        $realResult = simplexml_load_string($result->$method->any);

        $this->setTransactionType((string)$realResult->TransactionType);
        $this->setTransactionResult((string)$realResult->TransactionResult);
        $this->setErrorCode((string)$realResult->ErrorCode);
        $this->setErrorDescription((string)$realResult->ErrorDescription);

        $this->setShopTransactionID((string)$realResult->ShopTransactionID);
        $this->setBankTransactionID((string)$realResult->BankTransactionID);
        $this->setAuthorizationCode((string)$realResult->AuthorizationCode);
        $this->setCurrency((string)$realResult->Currency);
        $this->setAmount((string)$realResult->Amount);
        $this->setCountry((string)$realResult->Country);
        $this->setCustomInfo((string)$realResult->CustomInfo);
        $this->setBuyerName((string)$realResult->Buyer->BuyerName);
        $this->setBuyerEmail((string)$realResult->Buyer->BuyerEmail);
        $this->setTDLevel((string)$realResult->TDLevel);
        $this->setAlertCode((string)$realResult->AlertCode);

        $this->setAlertDescription((string)$realResult->AlertDescription);
        $this->setVbVRisp((string)$realResult->VbV->VbVRisp);
        $this->setVbVBuyer((string)$realResult->VbV->VbVBuyer);
        $this->setVbVFlag((string)$realResult->VbV->VbVFlag);
        $this->setTransactionKey((string)$realResult->TransactionKey);
        $this->setPaymentMethod((string)$realResult->PaymentMethod);

        //token
        $this->setToken((string)$realResult->TOKEN);
        $this->setTokenExpiryMonth((string)$realResult->TokenExpiryMonth);
        $this->setTokenExpiryYear((string)$realResult->TokenExpiryYear);

        //RED
        // ACCEPT, DENY, CHALLENGE, NOSCORE, ERROR, ENETFP, ETMOUT, EIVINF
        $this->setRedResponseCode((string)$realResult->RED->RedResponseCode);
        $this->setRedResponseDescription((string)$realResult->RED->RedResponseDescription);

        //Riskified
        $this->setRiskResponseCode((string)$realResult->RISK->RiskResponseCode);
        $this->setRiskResponseDescription((string)$realResult->RISK->RiskResponseDescription);

        $this->_helper->log($this->getData());
    }
}