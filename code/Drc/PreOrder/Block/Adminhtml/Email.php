<?php

namespace Drc\PreOrder\Block\Adminhtml;

class Email extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
	}



	public function prodotti(){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

		$collection = $productCollection->create()
	            ->addAttributeToSelect('*')
        	    ->load();
		
		return $collection;
	}
	
	
	public function customerUrl($id){
		return $this->getUrl('customer/index/edit', ['id' => intval($id)]);	
	}

}
