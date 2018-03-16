<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 25/01/17
 * Time: 00:19
 */
namespace EasyNolo\BancaSellaPro\Controller\Gestpay;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Registry;
use \EasyNolo\BancaSellaPro\Helper\Data as GestpayData;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Quote\Model\Quote;

class Result extends \Magento\Framework\App\Action\Action
{
    protected $_request, $_registry, $_dataHelper, $checkoutSession, $messageManager, $_quote, $_urlBuilder;

    public function __construct(
        Context $context,
        Http $request,
        Registry $registry,
        GestpayData $dataHelper,
        CheckoutSession $checkoutSession,
        Quote $quote
    ) {
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $context->getMessageManager();
        $this->_quote = $quote;
        $this->_urlBuilder = $context->getUrl();
        return parent::__construct($context);
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function execute()
    {
        $a = $this->getRequest()->getParam('a', false);
        $b = $this->getRequest()->getParam('b', false);

        if (!$a || !$b) {
            $this->_dataHelper->log('Accesso alla pagina per il risultato del pagamento non consentito, mancano i parametri di input');
            $this->getRequest()->initForward();
            $this->getRequest()->setActionName('noroute');
            $this->getRequest()->setDispatched(false);
            return;
        }

        $this->_registry->register('bancasella_param_a', $a);
        $this->_registry->register('bancasella_param_b', $b);

        $paymentCheckResult = $this->_dataHelper->isPaymentOk($a, $b);
        if ($paymentCheckResult[0] === true) {

            $this->_dataHelper->log('L\'utente ha completato correttamente l\'inserimento dei dati su bancasella');
            // reset quote on checkout session
            if ($lastQuoteId = $this->checkoutSession->getLastQuoteId()) {
                $quote = $this->_quote->load($lastQuoteId);
                if ($quoteId = $quote->getId()) {
                    $quote->setIsActive(false)->save();
                    $this->checkoutSession->setQuoteId(null);
                }
            }
            $redirect = 'checkout/onepage/success';

        } else {

            $this->_dataHelper->log('L\'utente ha annullato il pagamento, oppure qualche dato non corrisponde');
            // set order quote to active
            if ($lastQuoteId = $this->checkoutSession->getLastQuoteId()) {
                $quote = $this->_quote->load($lastQuoteId);
                if ($quoteId = $quote->getId()) {
                    $quote->setIsActive(true);
                    $quote->setReservedOrderId(null);
                    $quote->save();
                    $this->checkoutSession->setQuoteId($quoteId);
                }
            }
            $this->messageManager->addErrorMessage($paymentCheckResult[0]);
            $redirect = 'checkout/cart';

        }

        //se è impostato lo store allora reindirizzo l'utente allo store corretto
        $store = $this->_registry->registry('easynolo_bancasellapro_store_maked_order');
        if ($store && $store->getId()) {
            $this->redirectInCorrectStore($store, $redirect);
        } else {
            $this->_redirect($redirect);
        }
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