<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 25/01/17
 * Time: 12:10
 */

namespace EasyNolo\BancaSellaPro\Controller\Gestpay;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Registry;
use \Magento\Sales\Model\Order;
use \EasyNolo\BancaSellaPro\Helper\Data as GestpayData;
use \EasyNolo\BancaSellaPro\Model\WS\CryptDecrypt;
use \EasyNolo\BancaSellaPro\Model\TokenFactory;

class S2s extends \Magento\Framework\App\Action\Action
{
    protected $_request, $_registry, $_dataHelper, $cryptDecrypt, $order, $_modelTokenFactory;

    public function __construct(
        Context $context,
        Http $request,
        Registry $registry,
        Order $order,
        GestpayData $dataHelper,
        CryptDecrypt $cryptDecrypt,
        TokenFactory $modelTokenFactory
    ) {
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_dataHelper = $dataHelper;
        $this->cryptDecrypt = $cryptDecrypt;
        $this->order = $order;
        $this->_modelTokenFactory = $modelTokenFactory;
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

        $params = $this->_dataHelper->_getDecryptParams($a, $b);
        $result = $this->cryptDecrypt->decryptRequest($params);

        $orderId = $result->getShopTransactionID();
        $order = $this->order->loadByIncrementId($orderId);

        if ($order->getId()) {

            if ($result->getToken() && $order->getCustomerId()) {
                $this->_dataHelper->log('Salvo il token');
                $token = $this->_modelTokenFactory->create();
                $token->setTokenInfo(
                    $result->getToken(),
                    $result->getTokenExpiryMonth(),
                    $result->getTokenExpiryYear());
                $token->setCustomerId($order->getCustomerId());
                $token->save();
            }

            $this->_dataHelper->log('Imposto lo stato dell\'ordine in base al decrypt');
            $method = $order->getPayment()->getMethodInstance();
            $method->setStatusOrderByS2SRequest($order, $result);

        } else {
            $this->_dataHelper->log('La richiesta effettuata non ha un corrispettivo ordine. Id ordine= ' . $result->getShopTransactionID());
        }

        //restiutisco una pagina vuota per notifica a GestPay
        $this->getResponse()->setBody('<html></html>');
        return;
    }
}