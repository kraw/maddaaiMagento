<?php
namespace Drc\PreOrder\Model\ResourceModel;


class Like extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        $this->_date = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('drc_preorder_relationship_like_customer_product', 'id');
    }

    /**
     * Retrieves Post Name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getLikeById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('id = :id');
        $binds = ['id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    public function getLikeByProductIdCustomerId($productId, $customerId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('product_id = :product_id')
            ->where('customer_id = :customer_id');
        $binds = ['product_id' => (int)$productId, 'customer_id' => (int)$customerId];
        return $adapter->fetchOne($select, $binds);
    }
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\HelloWorld\Model\Post $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        // $object->setUpdatedAt($this->_date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->_date->date());
        }
        return parent::_beforeSave($object);
    }
}

