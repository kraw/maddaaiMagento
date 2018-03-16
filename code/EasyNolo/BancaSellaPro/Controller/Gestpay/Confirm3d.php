<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 05/02/17
 * Time: 14:44
 */
namespace EasyNolo\BancaSellaPro\Controller\Gestpay;
use \Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\View\Result\PageFactory;
use \EasyNolo\BancaSellaPro\Helper\Data as GestpayData;

class Confirm3d extends \Magento\Framework\App\Action\Action
{
    protected $_dataHelper, $_order, $resultPageFactory;

    public function __construct(Context $context, GestpayData $dataHelper, Session $checkoutSession, PageFactory $resultPageFactory)
    {
        $this->_dataHelper = $dataHelper;
        $this->_order = $checkoutSession->getLastRealOrder();
        $this->resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->_dataHelper->log('Richiamata azione conferma 3dsecure');
        if($this->_order->getId()){
            $this->_order->addStatusHistoryComment(__('User is redirecting to issuing bank for 3d authentification.'));
            $this->_order->save();
        }

        return $this->resultPageFactory->create();
    }

}