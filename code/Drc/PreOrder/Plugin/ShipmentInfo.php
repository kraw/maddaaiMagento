<?php
namespace Drc\PreOrder\Plugin;

class ShipmentInfo
{
    public function beforeSetTemplate(\Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        if ($enable) {
            if ($template == 'Magento_Shipping::create/items/renderer/default.phtml') {
                return ['Drc_PreOrder::order/default.phtml'];
            } else {
                return [$template];
            }
        } else {
            return [$template];
        }
    }
}
