<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:56
 */

namespace Drc\PreOrder\Controller\Adminhtml\Bid;


class Index extends \Drc\PreOrder\Controller\Adminhtml\Bid
{
    public function execute()
    {


        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage-> setActiveMenu('Drc_PreOrder::Bid');
        $resultPage->getConfig()->getTitle()-> prepend(__('Gestione Bids'));
        $resultPage->addBreadcrumb(__('Gestione'), __(' Bids'));
        $resultPage->addBreadcrumb(__('Gestione'), __(' Bids'));
        return $resultPage;
    }
}
