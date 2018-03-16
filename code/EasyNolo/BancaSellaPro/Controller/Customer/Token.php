<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 23/02/17
 * Time: 11:31
 */

namespace EasyNolo\BancaSellaPro\Controller\Customer;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\RedirectFactory;
use \EasyNolo\BancaSellaPro\Model\TokenFactory;
use \Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\Registry;

class Token extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory, $tokenFactory, $customerSession, $_coreRegistry, $resultRedirectFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory, TokenFactory $tokenFactory, CustomerSession $customerSession, Registry $coreRegistry)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->tokenFactory = $tokenFactory;
        $this->customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        return parent::__construct($context);
    }

    public function execute() {

        if($this->customerSession->isLoggedIn()) {
            $tokenModel = $this->tokenFactory->create();
            $tokenCollection = $tokenModel->getCollection()->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
            $resultPage = $this->resultPageFactory->create();
            $this->_coreRegistry->register('token_collection', $tokenCollection);
            $resultPage->getConfig()->getTitle()->set('My Credit Cards');
            return $resultPage;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('/');

    }

}