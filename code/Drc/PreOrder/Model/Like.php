<?php
namespace Drc\PreOrder\Model;
class Like extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'drc_preorder_relationship_like_customer_product';

	protected function _construct()
	{
		$this->_init('Drc\PreOrder\Model\ResourceModel\Like');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}
}

