<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 05/02/17
 * Time: 14:44
 */
namespace EasyNolo\BancaSellaPro\Controller\Gestpay;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Checkout\Model\Session;
use \Magento\Quote\Model\Quote;
use \Magento\Framework\Registry;
use \EasyNolo\BancaSellaPro\Helper\Data as GestpayData;
use \EasyNolo\BancaSellaPro\Model\WS\WS2S;

class Confirm3dS2S extends \Magento\Framework\App\Action\Action
{
    protected $helper, $_order, $resultRedirectFactory, $checkoutSession, $_request, $_quote, $s2s, $_registry;

    public function __construct(
        Context $context,
        GestpayData $dataHelper,
        Session $checkoutSession,
        Http $request,
        WS2S $s2s,
        Quote $quote,
        Registry $registry
    )
    {
        $this->helper = $dataHelper;
        $this->_order = $checkoutSession->getLastRealOrder();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->_request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->s2s = $s2s;
        $this->_quote = $quote;
        $this->_registry = $registry;
        return parent::__construct($context);
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->helper->log('Richiamata azione conferma 3dsecure');
        if($this->_order->getId()){
            $this->_order->addStatusHistoryComment(__('User is redirecting to issuing bank for 3d authentification.'));
            $paRes = $this->getRequest()->get('PaRes');
            $transactionKey = $this->checkoutSession->getGestpayTransactionKey();
            $order = $this->_order;
            $method = $order->getPayment()->getMethodInstance();

            $params = $this->helper->getGestpayParams($order);
            unset($params['requestToken']);
            $params['PARes'] = $paRes;
            $params['transKey'] = $transactionKey;

            $result = $this->s2s->executePaymentS2S($params);
            if(!$result->getTransactionResult() || $result->getTransactionResult() == 'KO') {
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

                $redirect ='checkout/onepage/success';
            }

            $this->_order->save();

            $store = $this->_registry->registry('easynolo_bancasellapro_store_maked_order');
            if ($store && $store->getId()) {
                $this->redirectInCorrectStore($store, $redirect);
            } else {
                return $resultRedirect->setPath($redirect);
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