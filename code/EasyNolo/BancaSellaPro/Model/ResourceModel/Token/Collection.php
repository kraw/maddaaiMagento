<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 23/02/17
 * Time: 11:05
 */

namespace EasyNolo\BancaSellaPro\Model\ResourceModel\Token;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_customerId = null;

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'EasyNolo\BancaSellaPro\Model\Token',
            'EasyNolo\BancaSellaPro\Model\ResourceModel\Token'
        );
    }

    /**
     * Add field filter to collection
     *
     * @see self::_getConditionSql for $condition
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'customer_id') {
            $this->_customerId = $condition;
        }

        return parent::addFieldToFilter($field, $condition = null);
    }

    public function toJson(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\EasyNolo\BancaSellaPro\Helper\Data');
        $localeDate = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $_tokens = [];

        if ((int)$this->_customerId) {
            foreach ($this as $t) {
                $_tokens[] = [
                    'id' => $t->getId(),
                    'token' => $helper->getFormattedToken($t->getToken()),
                    'vendor' => $helper->getCardVendor($t->getToken()),
                    'expiration' => $localeDate->formatDate($t->getExpireAt(), \IntlDateFormatter::LONG)
                ];
            }
        }

        return $_tokens;
    }
}