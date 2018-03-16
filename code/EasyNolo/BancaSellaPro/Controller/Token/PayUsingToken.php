<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 23/02/17
 * Time: 11:31
 */

namespace EasyNolo\BancaSellaPro\Controller\Token;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Customer\Model\Session as CustomerSession;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Quote\Model\Quote;
use \Magento\Framework\Registry;
use \EasyNolo\BancaSellaPro\Model\WS\WS2S;
use \EasyNolo\BancaSellaPro\Model\TokenFactory;
use \EasyNolo\BancaSellaPro\Helper\Data;

class PayUsingToken extends \Magento\Framework\App\Action\Action {

    protected $tokenFactory, $customerSession, $checkoutSession, $s2s, $_order, $helper, $_quote, $_registry, $_urlBuilder, $resultRedirectFactory, $_request;

    public function __construct(
        Context $context,
        TokenFactory $tokenFactory,
        CustomerSession $customerSession,
        Http $request,
        WS2S $s2s,
        CheckoutSession $checkoutSession,
        Data $helper,
        Quote $quote,
        Registry $registry
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->tokenFactory = $tokenFactory;
        $this->customerSession = $customerSession;
        $this->_request = $request;
        $this->s2s = $s2s;
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->_order = $checkoutSession->getLastRealOrder();
        $this->_quote = $quote;
        $this->_registry = $registry;
        $this->_urlBuilder = $context->getUrl();
        return parent::__construct($context);
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function execute() {

        $resultRedirect = $this->resultRedirectFactory->create();
        if($this->customerSession->isLoggedIn()) {
            $tokenModel = $this->tokenFactory->create();
            $token = $tokenModel->load($this->getRequest()->getParam('token', 0));
            if ($token && $token->getId() && ($token->getCustomerId() == $this->customerSession->getCustomerId())) {

                $order = $this->_order;
                $_helper = $this->helper;
                $params = $_helper->getGestpayParams($order, ['tokenValue' => $token->getToken()]);
                $result = $this->s2s->executePaymentS2S($params);
                $method = $order->getPayment()->getMethodInstance();

                // Analyze result from S2S call
                if (strcmp($result->getErrorCode(), '8006') == 0) {
                    $this->checkoutSession->setGestpayTransactionKey($result->getTransactionKey());
                    $_a = $method->getConfigData('merchant_id');
                    $_b = $result->getVbVRisp();
                    $_c = $this->_urlBuilder->getUrl('bancasellapro/gestpay/confirm3dS2S', ['_secure' => $this->getRequest()->isSecure()]);
                    $_final_url = $method->get3dAuthPageUrl() . '?a='.$_a.'&b='.$_b.'&c='.urlencode($_c);
                    return $resultRedirect->setUrl($_final_url);
                } else {
                    if (!$result->getTransactionResult() || $result->getTransactionResult() == 'KO') {
                        $this->messageManager->addError($result->getErrorDescription());
                        $redirect = 'checkout/cart';
                    } else {
                        $method->setStatusOrderByS2SRequest($order, $result);
                        if ($order->getStatus() != \Magento\Sales\Model\Order::STATUS_FRAUD) {
                            $this->helper->log('Invio email di conferma creazione ordine all\'utente');
                            //$order->sendNewOrderEmail();
                        }
                        $order->save();
                        // reset quote on checkout session
                        if ($lastQuoteId = $this->checkoutSession->getLastQuoteId()) {
                            $quote = $this->_quote->load($lastQuoteId);
                            if ($quoteId = $quote->getId()) {
                                $quote->setIsActive(false)->save();
                                $this->checkoutSession->setQuoteId(null);
                            }
                        }
                        $redirect = 'checkout/onepage/success';
                    }

                    $store = $this->_registry->registry('easynolo_bancasellapro_store_maked_order');
                    if ($store && $store->getId()) {
                        $this->redirectInCorrectStore($store, $redirect);
                    } else {
                        return $resultRedirect->setPath($redirect);
                    }
                }
            }
        }
        return $resultRedirect->setPath('/');
    }

    protected function redirectInCorrectStore($store, $path, $arguments = array())
    {
        $params = array_merge(
            $arguments,
            array(
                '_use_rewrite' => false,
                '_store' => $store,
                '_store_to_url' => true,
                '_secure' => $store->isCurrentlySecure()
            ) );
        $url = $this->_urlBuilder->getUrl($path, $params);

        $this->getResponse()->setRedirect($url);
        return;
    }

}