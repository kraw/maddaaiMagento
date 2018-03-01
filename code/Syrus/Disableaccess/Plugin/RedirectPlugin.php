<?php
namespace Syrus\Disableaccess\Plugin;
class RedirectPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    public function afterExecute(\Magento\Customer\Controller\Account\LogoutSuccess $logout)
    {
	$obm = \Magento\Framework\App\ObjectManager::getInstance();
    	$redirect = $obm->get('\Magento\Framework\App\Response\Http');
	$redirect->setRedirect("http://www.maddaai.it");	
    }
}
