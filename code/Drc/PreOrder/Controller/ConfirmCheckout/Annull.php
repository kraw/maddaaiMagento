<?php
namespace Drc\PreOrder\Controller\ConfirmCheckout;

use Drc\PreOrder\Model\PreorderPendingFactory;

use Zend_Debug;

class Annull extends \Magento\Framework\App\Action\Action
{
  public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $session,
    \Magento\Framework\App\ResponseFactory $responseFactory,
    \Magento\Catalog\Model\Product $product,
    \Magento\Checkout\Model\Cart $cart,
    \Magento\Framework\View\Result\PageFactory $pageFactory,
    \Drc\PreOrder\Model\PreorderPending $preModel


)
  {
    $this->_context = $context;
    $this->_session = $session;
    $this->_responseFactory = $responseFactory;
    $this->cart = $cart;
    $this->product = $product;
    $this->_pageFactory = $pageFactory;
    $this->_preModel = $preModel;


    return parent::__construct($context);
  }

  public function execute()
  {
    return $this->_pageFactory->create();  
  }
}
