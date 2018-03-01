<?php
namespace Drc\PreOrder\Plugin;

class ProductConfigurablePlugin
{
    public function beforeSetTemplate(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $enable=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        if ($enable) {
            if ($template == 'Magento_Catalog::product/view/type/default.phtml') {
                return [$template];
            } elseif ($template == 'Magento_Swatches::product/view/renderer.phtml') {
                return ['Drc_PreOrder::product/renderer.phtml'];
            } else {
                return ['Drc_PreOrder::product/configurable.phtml'];
            }
        } else {
            return[$template];
        }
    }
}
