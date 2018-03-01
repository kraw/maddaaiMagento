<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:54
 */

namespace Drc\PreOrder\Controller\Adminhtml;


abstract class Bid extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $resultForwardFactory;
    protected $resultRedirectFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Drc_PreOrder::bid');
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Drc_PreOrder::bid')
             ->_addBreadcrumb(__('Helpdesk'), __('Tickets'));
        return $this;
    }


}