<?php
namespace Drc\PreOrder\Plugin;

class ProductConfigurableSwatchPlugin
{
    public function beforeSetTemplate(\Magento\Swatches\Block\Product\Renderer\Listing\Configurable $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        if ($enable) {
            if ($template == 'Magento_Swatches::product/listing/renderer.phtml') {
                return ['Drc_PreOrder::product/swatchrenderer.phtml'];
            } else {
                return[$template];
            }
        } else {
            return[$template];
        }
    }
}
