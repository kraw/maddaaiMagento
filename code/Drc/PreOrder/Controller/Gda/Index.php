<?php
namespace Drc\PreOrder\Controller\Gda;

class Index extends \Magento\Framework\App\Action\Action {
	
  public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $session,
    \Magento\Framework\App\ResponseFactory $responseFactory,
    \Magento\Catalog\Model\Product $product,
    \Magento\Checkout\Model\Cart $cart,
    \Magento\Framework\View\Result\PageFactory $pageFactory,
    \Drc\PreOrder\Model\PreorderPending $preModel,
    \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository,
    \Magento\Catalog\Model\ProductFactory $productLoader,
	\Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\Registry $coreRegistry

)
  {
    $this->_context = $context;
    $this->_session = $session;
    $this->_responseFactory = $responseFactory;
    $this->cart = $cart;
    $this->product = $product;
    $this->_pageFactory = $pageFactory;
    $this->_preModel = $preModel;
    $this->customerRepository = $customerRepository;
    $this->_productLoader = $productLoader;
    $this->resultPageFactory = $resultPageFactory;   
	$this->_coreRegistry = $coreRegistry;


    return parent::__construct($context);
  }


 public function execute() {

   $this->_coreRegistry->register('customer_id', $this->_session->getCustomerId());
   return $this->resultPageFactory->create(); 

  }

}