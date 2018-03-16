<?php

namespace EasyNolo\BancaSellaPro\Model;

use Magento\Framework\Convert\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class Gestpay extends \Magento\Payment\Model\Method\AbstractMethod
{

    const CODE = 'easynolo_bancasellapro';
    const PROD_PAYMENT_URL = 'https://ecomm.sella.it';
    const TEST_PAYMENT_URL = 'https://testecomm.sella.it';
    const PROD_WSDL_URL = 'https://ecomms2s.sella.it';
    const TEST_WSDL_URL = 'https://testecomm.sella.it';
    const PAGE_FOR_PAYMENT = '/pagam/pagam.aspx';
    const PAGE_FOR_3D_AUTH = '/pagam/pagam3d.aspx';

    const MINIMUM_AMOUNT = 0.01;

    protected $_code = self::CODE;

    protected $_canAuthorize = true;

    public function getBaseUrlSella()
    {
        $url = $this->getConfigData('url_live');
        if ($this->getConfigData('debug')) {
            $url = $this->getConfigData('url_test');
        }
        return $url;
    }

    public function getBaseWSDLUrlSella()
    {
        $url = $this->getConfigData('url_live_s2s');
        if ($this->getConfigData('debug')) {
            $url = $this->getConfigData('url_test_s2s');
        }
        return $url;
    }

    public function getRedirectPagePaymentUrl()
    {
        $domain = $this->getBaseUrlSella();
        return $domain . self::PAGE_FOR_PAYMENT;
    }

    public function get3dAuthPageUrl()
    {
        $domain = $this->getBaseUrlSella();
        return $domain . self::PAGE_FOR_3D_AUTH;
    }

    public function isUseTransactionKeyEnabled()
    {
        $enableTokenization = $this->getConfigData('use_transactionkey');
        if ($enableTokenization) {
            return true;
        }
        return false;
    }

    public function getTransactionKeySiteID()
    {
        return $this->getConfigData('tk_site_id');
    }

    public function getTransactionKeyApiKey()
    {
        return $this->getConfigData('tk_api_key');
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && $quote->getBaseGrandTotal() < self::MINIMUM_AMOUNT)
            return false;
        if (!$this->getConfigData('merchant_id'))
            return false;
        if (!extension_loaded('soap')) {
            $_helper = $this->_objectManager->create('EasyNolo\BancaSellaPro\Helper\Data');
            $_helper->log('Non è stato possibile creare il client per il webserver - PHP SOAP extension is required.');
            return false;
        }
        return parent::isAvailable($quote);
    }

    public function getTotalByOrder($order)
    {
        $defaultCurrency = $this->getConfigData('currency', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $storeCurrency = $this->getConfigData('currency');

        if ($defaultCurrency != $storeCurrency) {
            return $order->getGrandTotal();
        } else {
            return $order->getBaseGrandTotal();
        }

    }

    public function setStatusOrderByS2SRequest($order, $result)
    {
        $payment = $order->getPayment();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_helper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\Data');
        $_helper->log('Controllo l\'ordine in base alle risposte della S2S');

        if ($this->getConfigData('order_status_fraud_gestpay')) {

            $_helper->log('Controllo frode');

            $message = false;
            $total = $this->getTotalByOrder($order);
            $_helper->log('controllo il totale dell\'ordine : ' . $result->getAmount() . ' = ' . round($total, 2));
            if (round($result->getAmount(), 2) != round($total, 2)) {
                // il totatle dell'ordine non corrisponde al totale della transazione
                $message = __('Transaction amount differs from order grand total.');
            }

            if ($result->getAlertCode() != '') {
                $_helper->log('controllo alert della transazione : ' . $result->getAlertCode());
                $message = $result->getAlertDescription();
            }

            if ($message) {
                $_helper->log('rilevata possibile frode: ' . $message);

                $payment->setTransactionAdditionalInfo(Payment\Transaction::RAW_DETAILS, $result->getData());

                $payment->setTransactionId($result->getShopTransactionId())
                    ->setCurrencyCode($order->getBaseCurrencyCode())
                    ->setIsTransactionClosed(0)
                    ->setPreparedMessage($message);
                $order->setState(Order::STATE_PAYMENT_REVIEW, Order::STATUS_FRAUD, $message);

                $order->save();

                return false;
            }

        }

        switch ($result->getTransactionResult()) {

            case 'XX':
                $message = __("Authorizing amount of %1 is pending approval on gateway.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $status = $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $order->setStatus($status);
                $order->addStatusHistoryComment($message, false);
                $_helper->log('Pagamento effettuato con bonifico bancario, verificare a mano la transazione');
                $order->addStatusHistoryComment(__('Payment was using bank transfer. Please verify the order status on GestPay.'));
                break;

            case 'OK':

                if ($this->isRedEnabled()):
                    switch ($result->getRedResponseCode()) {
                        case 'ACCEPT':
                            $this->_setOrderPaid($order, $result);
                            break;
                        default:
                            $_helper->log('Pagamento effettuato correttamente ma il check RED è risultato \'' . $result->getRedResponseCode() . '\'. Cambio stato all\'ordine e salvo l\'id della transazione');

                            $message = __("Authorization approved on gateway but RED return with '%s' status. GestPay Transaction ID: %s", $result->getRedResponseCode(), $result->getBankTransactionID());
                            if ($paymentMethod = $result->getPaymentMethod()) {
                                $message .= " (" . $paymentMethod . ")";
                            }
                            $payment->setAdditionalData(serialize($result->getData()))
                                ->setTransactionAdditionalInfo(Payment\Transaction::RAW_DETAILS, $result->getData());
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
                            $this->_setOrderPaid($order, $result);
                            break;
                        default:
                            $_helper->log('Pagamento effettuato correttamente ma il check Riskified è risultato \'' . $result->getRiskResponseCode() . '\'. Cambio stato all\'ordine e salvo l\'id della transazione');
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
                    $this->_setOrderPaid($order, $result);
                endif;
                break;

            case 'KO':
                $_helper->log('Pagamento non andato a buon fine. Cambio stato all\'ordine e salvo l\'id della transazione');
                $message = __("Authorizing amount of %1 is pending approval on gateway.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                $order->cancel();
                $order->setState($this->getConfigData('order_status_ko_gestpay'));
                $status = $order->getConfig()->getStateDefaultStatus($this->getConfigData('order_status_ko_gestpay'));
                $order->setStatus($status);
                $order->addStatusHistoryComment($message, false);

                $message = __("Payment attempt has been declined. GestPay Transaction ID: %1", $result->getBankTransactionID());
                if ($paymentMethod = $result->getPaymentMethod()) {
                    $message .= " (" . $paymentMethod . ")";
                }
                $order->addStatusHistoryComment($message);
                break;
        }

        $order->save();
        $_helper->log('Dopo l\'elaborazione della s2s l\'ordine con id: ' . $order->getId() . ' ha state: ' . $order->getState() . ' e status: ' . $order->getStatus());
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $s2s = $objectManager->create('EasyNolo\BancaSellaPro\Model\WS\WS2S');
        $_helper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\Data');
        if (!$this->getConfigData('use_s2s_api')) {
            $message = __('Capture online not allowed. Check payment module configuration "Use S2S Sales API for Capture, Void, Refund actions".');
            throw new \Exception($message);
        }
        $order = $payment->getOrder();
        $params = $_helper->getGestpayParams($order);
        $params['bankTransID'] = $payment->getData('bankTransactionId');
        $s2s->capturePayment($payment, $order, $params);
        return $this;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $payment->setIsTransactionPending(true);
        return $this;
    }

    private function _setOrderPaid($order, $result)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $_helper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\Data');
        $payment = $order->getPayment();

        $_helper->log('Pagamento effettuato correttamente. Cambio stato all\'ordine e salvo l\'id della transazione');
        $message = __("Authorization of %1 approved on gateway. GestPay Transaction ID: %2", $order->formatPriceTxt($result->getAmount()), $result->getBankTransactionID());

        $order->addStatusHistoryComment($message);

        // create the authorization transaction
        $payment->setData('bankTransactionId', $result->getBankTransactionID());
        $payment->setAdditionalData(serialize($result->getData()));
        $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$result->getData()]);
        $payment->setTransactionId($result->getShopTransactionId());
        $payment->setCurrencyCode($order->getBaseCurrencyCode());
        $payment->setIsTransactionClosed(0);

        // perform the capture
        $setOrderAsPaid = true;

        if ($this->getConfigData('payment_action') == \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {

            // capture online if enabled
            if ($this->getConfigData('use_s2s_api')) {
                try {
                    $this->capture($payment, $result->getAmount());
                    $order->save();
                } catch (\Exception $e) {
                    $setOrderAsPaid = false;
                    $message = __("Failed capture online: %1", $e->getMessage());
                    $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                    $order->addStatusHistoryComment($message);
                }
            }

            // capture notification, used for capture offline too
            if ($setOrderAsPaid == true) {
                $payment->registerCaptureNotification($order->getBaseGrandTotal(), true);
            }
        }

        if ($setOrderAsPaid == true) {
            $order->setState($this->getConfigData('order_status_ok_gestpay'));
            $status = $order->getConfig()->getStateDefaultStatus($this->getConfigData('order_status_ok_gestpay'));
            $order->setStatus($status);
        }

        $payment->save();
        $order->save();
    }

    public function getRedConfigData($field, $storeId = null)
    {
        $path = 'payment/easynolo_bancasellapro_red/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRedEnabled()
    {
        return $this->getRedConfigData('enable');
    }

    public function getRiskifiedConfigData($field, $storeId = null)
    {
        $path = 'payment/easynolo_bancasellapro_riskified/' . $field;
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isRiskifiedEnabled()
    {
        return $this->getRiskifiedConfigData('enable');
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        /** @var DataObject $info */
        $info = $this->getInfoInstance();

        $details = array('alternative-payment' => null);
        $info->setAdditionalInformation('alternative-payment', null);

        if ($alt = $data->getAdditionalData('alternative_payment')) {
            $alt = json_decode($alt, true);
            foreach ($alt as $v) {
                $details[$v['name']] = $v['value'];
                $info->setAdditionalInformation($v['name'], $v['value']);
            }
        }

        if (!empty($details)) {
            $info->addData($details);
        }

        return $this;
    }
}