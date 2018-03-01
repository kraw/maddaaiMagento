<?php
namespace Drc\PreOrder\Plugin;

class BundleProductShipment
{
    public function beforeSetTemplate(\Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        if ($enable) {
            if ($template == 'sales/shipment/create/items/renderer.phtml') {
                return ['Drc_PreOrder::order/renderer.phtml'];
            } else {
                return[$template];
            }
        } else {
            return[$template];
        }
    }
}
