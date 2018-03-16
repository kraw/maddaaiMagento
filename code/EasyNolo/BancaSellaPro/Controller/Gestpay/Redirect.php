<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 24/01/17
 * Time: 20:02
 */
namespace EasyNolo\BancaSellaPro\Controller\Gestpay;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session;
use \EasyNolo\BancaSellaPro\Helper\Data as GestpayData;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $_dataHelper, $_order;

    public function __construct(Context $context, GestpayData $dataHelper, Session $checkoutSession)
    {
        $this->_dataHelper = $dataHelper;
        $this->_order = $checkoutSession->getLastRealOrder();
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = $this->_dataHelper->getRedirectUrlToPayment($this->_order);
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }

}
