<?php
namespace Drc\PreOrder\Plugin;

class BundleProductInfo
{
    public function beforeSetTemplate(\Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        if ($enable) {
            if ($template == 'sales/order/view/items/renderer.phtml') {
                return ['Drc_PreOrder::order/bundle/renderer.phtml'];
            } else {
                return[$template];
            }
        } else {
            return[$template];
        }
    }
}
