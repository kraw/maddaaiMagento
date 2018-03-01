<?php
namespace Drc\PreOrder\Model\Config\Source;
 
class Category implements \Magento\Framework\Option\ArrayInterface
{
    protected $categoryCollection;

    public function __construct(
	\Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection
				) {
	$this->categoryCollection = $categoryCollection;
    }

    public function toOptionArray()
    {
	$temp = [];
	$this->categoryCollection->addAttributeToSelect("*");
	$this->categoryCollection->load();
	foreach($this->categoryCollection as $category) {
		$temp[] = ['value' => $category->getId(), 'label' => $category->getName() ];
	}
	return $temp;
    }
}
