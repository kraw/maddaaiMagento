<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 23/02/17
 * Time: 11:31
 */

namespace EasyNolo\BancaSellaPro\Controller\Token;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Request\Http;
use \Magento\Framework\Controller\Result\RedirectFactory;
use \EasyNolo\BancaSellaPro\Model\TokenFactory;
use \Magento\Customer\Model\Session as CustomerSession;

class Delete extends \Magento\Framework\App\Action\Action {

    protected $tokenFactory, $customerSession, $_request, $resultRedirectFactory;

    public function __construct(Context $context, TokenFactory $tokenFactory, CustomerSession $customerSession, Http $request)
    {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->tokenFactory = $tokenFactory;
        $this->customerSession = $customerSession;
        $this->_request = $request;
        return parent::__construct($context);
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function execute() {

        $resultRedirect = $this->resultRedirectFactory->create();
        if($this->customerSession->isLoggedIn()) {
            $tokenModel = $this->tokenFactory->create();
            $token = $tokenModel->load($this->getRequest()->getParam('token', 0));
            if($token && $token->getId() && ($token->getCustomerId() == $this->customerSession->getCustomerId())){
                $token->delete();
                $this->messageManager->addSuccess(__('Token successfully deleted.'));
            }
            else{
                $this->messageManager->addError(__('Request is invalid.'));
            }
            return $resultRedirect->setPath('bancasellapro/customer/token');
        }
        return $resultRedirect->setPath('/');

    }

}