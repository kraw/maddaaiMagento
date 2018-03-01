<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:40
 */

namespace Drc\PreOrder\Block\Adminhtml;

class Valutationproduct extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context)
	{
		parent::__construct($context);
	}



	public function prodotti(){

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

		$collection = $productCollection->create()
	            ->addAttributeToSelect('*');
		$collection->addAttributeToFilter('type_id', array('eq' => 'valutation_product'));
		$collection->load();	
		return $collection;
	}

}
