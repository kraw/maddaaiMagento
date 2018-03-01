<?php
namespace Drc\PreOrder\Block;
use Magento\Customer\Controller\RegistryConstants;

class Gda extends \Magento\Framework\View\Element\Template
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
	)
	{
		$this->_coreRegistry = $registry;
		$this->customerSession = $customerSession;
		parent::__construct($context, $data);
	}

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry('customer_id');

	}
	

}
