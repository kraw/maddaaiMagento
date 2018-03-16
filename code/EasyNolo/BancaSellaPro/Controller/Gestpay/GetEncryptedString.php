<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 24/01/17
 * Time: 20:02
 */
namespace EasyNolo\BancaSellaPro\Controller\Gestpay;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session;
use \EasyNolo\BancaSellaPro\Helper\Data as GestpayData;

class GetEncryptedString extends \Magento\Framework\App\Action\Action
{
    protected $_dataHelper, $_order, $resultJsonFactory;

    public function __construct(Context $context, GestpayData $dataHelper, Session $checkoutSession, JsonFactory $resultJsonFactory)
    {
        $this->_dataHelper = $dataHelper;
        $this->_order = $checkoutSession->getLastRealOrder();
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cryptDecrypt = $objectManager->create('EasyNolo\BancaSellaPro\Model\WS\CryptDecrypt');
        $params = $this->_dataHelper->getGestpayParams($this->_order);
        $encryptedString = $cryptDecrypt->getEncryptString($params);
        $result = $this->resultJsonFactory->create();
        $result->setData(['EncString' => $encryptedString]);
        return $result;
    }

}
