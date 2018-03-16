<?php

namespace EasyNolo\BancaSellaPro\Helper;

use Magento\Framework\Convert\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_gestpay_logger = null;

    public function log($msg)
    {
        if (is_array($msg)) $msg = print_r($msg, true);
        if ($this->scopeConfig->getValue('payment/easynolo_bancasellapro/log'))
            $this->_getLogger()->debug($msg);
    }


    function _getDecryptParams($a, $b)
    {
        $this->log('Imposto i parametri da inviare al decrypt');
        $params = array();
        $params['shopLogin'] = $a;
        $params['CryptedString'] = $b;
        $this->log($params);
        return $params;
    }

    public function isElaborateS2S($order)
    {
        $state = $order->getState();
        if ($state == \Magento\Sales\Model\Order::STATE_NEW)
            return false;
        return true;
    }

    private function _getLogger()
    {
        if (!$this->_gestpay_logger) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/EasyNolo_BancaSellaPro.log');
            $this->_gestpay_logger = new \Zend\Log\Logger();
            $this->_gestpay_logger->addWriter($writer);
        }
        return $this->_gestpay_logger;
    }

    function createUrl($url, $param)
    {
        $paramether = '';
        if (count($param)) {
            $paramether = '?';
            foreach ($param as $name => $value) {
                $paramether .= $name . '=' . $value . '&';
            }
        }
        return $url . $paramether;
    }

    function getGestPayJs($order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cryptDecrypt = $objectManager->create('EasyNolo\BancaSellaPro\Model\WS\CryptDecrypt');
        $gestpay = $order->getPayment()->getMethodInstance();
        $url = null;
        if ($gestpay->isIframeEnabled()) {
            $url = $gestpay->getIframeUrl();
        }
        return $url;
    }

    function getRedirectUrlToPayment($order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cryptDecrypt = $objectManager->create('EasyNolo\BancaSellaPro\Model\WS\CryptDecrypt');
        $gestpay = $order->getPayment()->getMethodInstance();
        $params = $this->getGestpayParams($order, ['include_alternative_payments' => false]);
        try {
            $stringEncrypt = $cryptDecrypt->getEncryptString($params);
            $url = $gestpay->getRedirectPagePaymentUrl();
            return $url . '?a=' . $params['shopLogin'] . '&b=' . $stringEncrypt;
        } catch (\Exception $e) {
            $objectManager->create('\Magento\Framework\Message\ManagerInterface')->addError($e->getMessage());
        }
        $urlBuilder = $objectManager->create('Magento\Framework\UrlInterface');
        return $urlBuilder->getUrl("checkout/cart/index");
    }

    private function _getBaseParams($order)
    {
        $gestpay = $order->getPayment()->getMethodInstance();

        $total = $gestpay->getTotalByOrder($order);
        $params = [];

        $params['shopLogin'] = $gestpay->getConfigData('merchant_id');
        $params['shopTransactionId'] = $order->getIncrementId();
        $params['uicCode'] = $gestpay->getConfigData('currency');
        if ($gestpay->getConfigData('language')) {
            $params['languageId'] = $gestpay->getConfigData('language');
        }
        $params['amount'] = round($total, 2);

        if ($gestpay->getConfigData('tokenization'))
            $params['requestToken'] = 'MASKEDPAN';

        return $params;
    }

    protected function setPaymentParams(&$params, $order) {
        $method = $order->getPayment()->getMethodInstance();
        $additionalData = $method->getInfoInstance()->getAdditionalInformation();
        if (!empty($additionalData) && !empty($additionalData['alternative-payment'])) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $alternativeHelper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\AlternativePayments');
            $alternatives = $alternativeHelper->getAlternativePayments();
            if (!empty($alternatives) && !empty($alternatives[$additionalData['alternative-payment']])) {
                $method = $alternatives[$additionalData['alternative-payment']];

                $params['paymentTypes'] = array();
                $params['paymentTypes']['paymentType'] = array();
                $params['paymentTypes']['paymentType'][] = $method['type'];
                if (!empty($method['encrypt_helper'])) {
                    $helperPayment = $objectManager->create($method['encrypt_helper']);
                    if ($helperPayment) {
                        $additional = $helperPayment->getEncryptParams($order);
                        if ($additional && is_array($additional)) {
                            $params = array_merge_recursive($params, $additional);
                        }
                    }
                }
            }
        }
    }

    function getGestpayParams($order, $opts = [])
    {
        $params = $this->_getBaseParams($order);

        $this->setPaymentParams($params, $order);

        if (isset($opts['tokenValue'])) {
            unset($params['requestToken']);
            $params['tokenValue'] = $opts['tokenValue'];
        }

        $gestpay = $order->getPayment()->getMethodInstance();
        if ($gestpay->isRedEnabled()){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $_redHelper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\Red');
            $_redHelper->addParams($params, $order);
        }

        if ($gestpay->isRiskifiedEnabled()){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $riskifiedHelper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\Riskified');
            $riskifiedHelper->addParams($params, $order);
        }

        $this->log($params);
        return $params;
    }

    public function getFastResultPayment($transactionResult)
    {
        if (!$transactionResult || $transactionResult == 'KO')
            return false;
        return true;
    }

    public function getFormattedToken($token)
    {
        return preg_replace("/([0-9]{2}).{10}([0-9]{4})/", "\${1}**********\${2}", $token);
    }

    public function getCardVendor($token)
    {
        if (preg_match("/^4[0-9]/", $token))
            return array('label' => 'Visa', 'id' => 'visa');
        elseif (preg_match("/^5[1-5]/", $token))
            return array('label' => 'MasterCard', 'id' => 'mastercard');
        elseif (preg_match("/^3[47]/", $token))
            return array('label' => 'Amex', 'id' => 'america-express');
        elseif (preg_match("/^3[068]/", $token))
            return array('label' => 'Diners Club', 'id' => 'diners');
        elseif (preg_match("/^6[05]/", $token))
            return array('label' => 'Discover', 'id' => 'discover');
        elseif (preg_match("/^21/", $token) || preg_match("/^18/", $token) || preg_match("/^35/", $token))
            return array('label' => 'JCB', 'id' => 'jcb');
        else
            return array('label' => 'unknown', 'id' => 'credit-card');
    }



    public function isPaymentOk($a, $b)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cryptDecrypt = $objectManager->create('EasyNolo\BancaSellaPro\Model\WS\CryptDecrypt');
        $registry = $objectManager->create('\Magento\Framework\Registry');

        $params = $this->_getDecryptParams($a, $b);
        $result = $cryptDecrypt->decryptRequest($params);
        if (!$result) return false;
        $orderId = $result->getShopTransactionID();

        $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId, 'increment_id');

        $registry->register('easynolo_bancasellapro_store_maked_order', $order->getStore());
        $registry->register('easynolo_bancasellapro_order', $order);

        if ($order->getId()) {

            $payment = $order->getPayment();

            switch ($result->getTransactionResult()) {

                case 'XX':
                    $this->log('La transazione non è ancora stata inviata sul s2s');
                    $message = __("Authorizing amount of %1 is pending approval on gateway.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                    $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                    $status = $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                    $order->setStatus($status);
                    $order->addStatusHistoryComment($message, false);
                    $order->save();
                    break;

                case 'OK':

                    if ($this->isRedEnabled()):
                        switch ($result->getRedResponseCode()) {
                            case 'ACCEPT':
                                $this->log('La tranzazione è gia stata salvata, non cambio lo stato');
                                $message = __("Amount of %1 authorized.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                                $status = $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                                $order->setStatus($status);
                                $order->addStatusHistoryComment($message, false);
                                $order->save();
                                break;

                            default:
                                $message = __("Authorization approved on gateway but RED return with '%s' status. GestPay Transaction ID: %s", $result->getRedResponseCode(), $result->getBankTransactionID());
                                if ($paymentMethod = $result->getPaymentMethod()) {
                                    $message .= " (" . $paymentMethod . ")";
                                }
                                $payment->setAdditionalData(serialize($result->getData()))
                                    ->setTransactionAdditionalInfo(array(Payment\Transaction::RAW_DETAILS => $result->getData()), "");
                                $payment->setTransactionId($result->getShopTransactionId())
                                    ->setCurrencyCode($order->getBaseCurrencyCode())
                                    ->setIsTransactionClosed(0);

                                $status = Order::STATE_PAYMENT_REVIEW;

                                if ($result->getRedResponseCode() == 'DENY')
                                    $status = $this->getRedConfigData('deny_order_status');
                                elseif ($result->getRedResponseCode() == 'CHALLENGE')
                                    $status = $this->getRedConfigData('challenge_order_status');
                                $order->setState(Order::STATE_PAYMENT_REVIEW, $status, $message);

                                $order->save();
                        }
                    elseif ($this->isRiskifiedEnabled()):
                        switch ($result->getRiskResponseCode()) {
                            case 'approved':
                                $this->log('La tranzazione è gia stata salvata, non cambio lo stato');
                                $message = __("Amount of %1 authorized.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                                $status = $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                                $order->setStatus($status);
                                $order->addStatusHistoryComment($message, false);
                                $order->save();
                                break;
                            default:
                                $message = __("Authorization approved on gateway but Riskified return with '%s' status. Response description: %s. GestPay Transaction ID: %s", $result->getRiskResponseCode(), $result->getRiskResponseDescription(), $result->getBankTransactionID());
                                if ($paymentMethod = $result->getPaymentMethod()) {
                                    $message .= " (" . $paymentMethod . ")";
                                }
                                $payment->setAdditionalData(serialize($result->getData()))
                                    ->setTransactionAdditionalInfo(Payment\Transaction::RAW_DETAILS, $result->getData());
                                $payment->setTransactionId($result->getShopTransactionId())
                                    ->setCurrencyCode($order->getBaseCurrencyCode())
                                    ->setIsTransactionClosed(0);

                                $status = Order::STATE_PAYMENT_REVIEW;

                                if ($result->getRiskResponseCode() == 'declined')
                                    $status = $this->getRiskifiedConfigData('declined_order_status');
                                elseif ($result->getRiskResponseCode() == 'submitted')
                                    $status = $this->getRiskifiedConfigData('submitted_order_status');

                                $order->setState(Order::STATE_PAYMENT_REVIEW, $status, $message);

                                $order->save();
                        }
                    else:
                        $this->log('La tranzazione è gia stata salvata, non cambio lo stato');
                        $message = __("Amount of %1 authorized.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $status = $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $order->setStatus($status);
                        $order->addStatusHistoryComment($message, false);
                        $order->save();
                    endif;
                    break;

                case 'KO':
                    $this->log('Il web service ha restituito KO');
                    $message = __('Payment transaction not authorized: %1.', $result->getErrorDescription());
                    $method = $order->getPayment()->getMethodInstance();
                    $order->cancel();
                    $order->setState($method->getConfigData('order_status_ko_gestpay'));
                    $status = $order->getConfig()->getStateDefaultStatus($method->getConfigData('order_status_ko_gestpay'));
                    $order->setStatus($status);
                    $order->addStatusHistoryComment($message, false);
                    $order->save();
                    return [$message, $result];
            }

            return [true, $result];
        } else {
            $message = __("There was an error processing your order. Please contact us or try again later.");
            $this->log('L\'ordine restituito da bancasella non esiste. Increment id = ' . $orderId);
            return [$message, null];
        }
    }

    public function getRedConfigData($field, $storeId = null)
    {
        $path = 'payment/easynolo_bancasellapro_red/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRedEnabled()
    {
        return $this->getRedConfigData('enable');
    }

    public function getRiskifiedConfigData($field, $storeId = null)
    {
        $path = 'payment/easynolo_bancasellapro_riskified/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRiskifiedEnabled()
    {
        return $this->getRiskifiedConfigData('enable');
    }
}