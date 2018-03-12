<?php
namespace Drc\PreOrder\Helper;

class Check extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function checkEnable(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $scopeConfig=$objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $enable=$scopeConfig->getValue('drc_preorder_setting/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $enable;
    }

    public function displayTimer(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $enable = $scopeConfig->getValue('drc_preorder_setting/display/datetovisualize', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $enable;
    }
}
