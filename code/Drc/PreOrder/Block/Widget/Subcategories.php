<?php 
namespace Drc\PreOrder\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface; 
 
class Subcategories extends Template implements BlockInterface {

	protected $_template = "widget/subcategories.phtml";
	protected $categoryFactory;
	
	public function __construct(
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Framework\View\Element\Template\Context $context,
    		array $data = []
				) {
		parent::__construct($context, $data);
		//category list model
		$this->categoryFactory = $categoryFactory;
	}


	public function getSubcategories($category_id) {
		$category = $this->categoryFactory->create();
		$category->load($category_id);
		return $category->getChildrenCategories();
	}

}


