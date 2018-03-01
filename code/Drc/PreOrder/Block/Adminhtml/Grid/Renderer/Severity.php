<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:47
 */

namespace Drc\PreOrder\Block\Adminhtml\Grid\Renderer;

class Severity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $ticketFactory;
    public function __construct(\Magento\Backend\Block\Context $context,
                           //     Drc\PreOrder\Model\TicketFactory $ticketFactory,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                array $data = []){
        parent::__construct($context, $data);
        $this->ticketFactory = $productFactory;
    }

    public function render(\Magento\Framework\DataObject $row){
        $ticket = $this->productFactory->create()->load($row-> getId());
        if ($ticket && $ticket->getId()) {
            return $ticket->getSeverityAsLabel();
        }
        return '';
    }
}