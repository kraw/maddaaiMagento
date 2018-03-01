<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:56
 */

namespace Drc\PreOrder\Controller\Adminhtml\Bid;


class Index extends \Magento\Backend\App\Action
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
            
	return  $resultPage = $this->resultPageFactory->create();
    }
}
