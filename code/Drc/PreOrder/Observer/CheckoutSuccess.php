<?php
namespace Drc\PreOrder\Observer;

class CheckoutSuccess implements \Magento\Framework\Event\ObserverInterface
{

  public function __construct(
	  \Magento\Checkout\Model\Cart $cart,
      \Magento\Backend\Block\Template\Context $context
  ){

   $this->_context = $context;
   $this->cart = $cart;

   }


  public function execute(\Magento\Framework\Event\Observer $observer){

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $session = $objectManager->create("\Magento\Customer\Model\Session");
    $customerData = $session->getCustomerData();
    $id_customer = $customerData->getId();

    $token = $_COOKIE["$id_customer"];

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resource->getConnection();
    $tableName = $resource->getTableName('drc_preorder_pending');

    $sql = "UPDATE " . $tableName . " SET deleted = 1 WHERE token = '".$token."'";
    $connection->query($sql);

    unset($_COOKIE["$id_customer"]);
    setcookie("$id_customer", '', 0, '/');
	  
	  
		  
	  $this->cart->truncate();
	  $this->cart->save();

    
  }

}

