<?php
namespace Drc\PreOrder\Plugin;

class ProductCartPlugin
{
    public function beforeSetTemplate(\Magento\Checkout\Block\Cart\Item\Renderer $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();

        if ($enable==1) {
            return ['Drc_PreOrder::cart/cart.phtml'];
        } else {
            return [$template];
        }
    }
}
