<?php

namespace Drc\PreOrder\Controller\Adminhtml\Bid;


class Email extends \Magento\Backend\App\Action
{


	 protected $resultPageFactory;

        public function __construct(
            \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
             parent::__construct($context);
             $this->resultPageFactory = $resultPageFactory;
        }



    public function execute()
    {
        return "email";    
	return  $resultPage = $this->resultPageFactory->create();
    }
}
