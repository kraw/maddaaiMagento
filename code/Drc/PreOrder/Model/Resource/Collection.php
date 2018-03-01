<?php

namespace Drc\PreOrder\Model\Resource\PreorderPending;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Drc\PreOrder\Model\PreorderPending',
            'Drc\PreOrder\Model\Resource\PreorderPending'
        );
    }
}


