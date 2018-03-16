<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 22/01/17
 * Time: 01:23
 */

namespace EasyNolo\BancaSellaPro\Model\WS;

class CryptDecrypt extends AbstractWebService
{
    const PATH_WS_CRYPT_DECRIPT = '/gestpay/gestpayws/WSCryptDecrypt.asmx?WSDL';

    public function getEncryptString($params){
        $result = $this->client->Encrypt($params);
        $this->setResponseEncrypt($result);
        $cryptDecryptString = $this->getCryptDecryptString();
        $this->_helper->log('Encrypt string: ' . $cryptDecryptString);
        return $cryptDecryptString;
    }

    private function setResponseEncrypt($result){
        $realResult = simplexml_load_string($result->EncryptResult->any);

        $this->setTransactionType((string)$realResult->TransactionType);
        $this->setTransactionResult((string)$realResult->TransactionResult);
        $this->setErrorCode((string)$realResult->ErrorCode);
        $this->setErrorDescription((string)$realResult->ErrorDescription);

        if($this->getTransactionResult() == 'OK')
            $this->setCryptDecryptString((string)$realResult->CryptDecryptString);
        else
            throw new \Exception($this->getErrorDescription());
    }

    public function decryptRequest($params){
        $result = $this->client->Decrypt($params);
        $this->setResponseDecrypt($result);
        return $this;
    }

    public function setResponseDecrypt($result){

        $this->_helper->log('Save decrypted params');

        $realResult = simplexml_load_string($result->DecryptResult->any);

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
        $this->setVbVRisp((string)$realResult->VbVRisp);
        $this->setVbVBuyer((string)$realResult->VbVBuyer);
        $this->setVbVFlag((string)$realResult->VbVFlag);
        $this->setTransactionKey((string)$realResult->TransactionKey);
        $this->setPaymentMethod((string)$realResult->PaymentMethod);

        //token
        $this->setToken((string)$realResult->TOKEN);
        $this->setTokenExpiryMonth((string)$realResult->TokenExpiryMonth);
        $this->setTokenExpiryYear((string)$realResult->TokenExpiryYear);

        //RED
        // ACCEPT, DENY, CHALLENGE, NOSCORE, ERROR, ENETFP, ETMOUT, EIVINF
        $this->setRedResponseCode((string)$realResult->RedResponseCode);
        $this->setRedResponseDescription((string)$realResult->RedResponseDescription);

        //Riskified
        $this->setRiskResponseCode((string)$realResult->RiskResponseCode);
        $this->setRiskResponseDescription((string)$realResult->RiskResponseDescription);

        $this->_helper->log($this->getData());
    }

}