<?php
namespace Drc\PreOrder\Model\Rewrite\Source;
 
class Backorders extends \Magento\CatalogInventory\Model\Source\Backorders
{
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig=$objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $moduleEnable=$scopeConfig->getValue('drc_preorder_setting/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($moduleEnable==1) {
                    return [
                ['value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO, 'label' => __('No Backorders')],
                [
                    'value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY,
                    'label' => __('Abilita per quntity 0')
                ],
                [
                    'value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY,
                    'label' => __('Allow Qty Below 0 and Notify Customer')
                ],
                [
                    'value' => 3,
                    'label' => __('Permetti Pre-Order')
                ],
                [
                    'value' => 4,
                    'label' => __('Pre-Order per Out-Of-Stock')
                ]
                    ];
        } else {
               return [
                ['value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO, 'label' => __('No Backorders')],
                [
                    'value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY,
                    'label' => __('Allow Qty Below 0')
                ],
                [
                    'value' => \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY,
                    'label' => __('Allow Qty Below 0 and Notify Customer')
                ]
                    
               ];
        }
    }
}
