<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:50
 */

namespace Drc\PreOrder\Block\Adminhtml\Grid\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ticketFactory;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        // \Drc\PreOrder\Model\TicketFactory $ticketFactory,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('hello.hello.edit.tab.grid');
        return $resultLayout;
        /**$ticket = $this->ticketFactory->create()->load($row-> getId());
        if ($ticket && $ticket->getId()) {
            return $ticket->getStatusAsLabel();
        }*/
        //return 'Status Render';
    }
}