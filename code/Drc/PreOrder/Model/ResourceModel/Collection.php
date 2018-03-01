<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 03/02/17
 * Time: 18:14
 */

namespace Drc\PreOrder\Model\ResourceModel\Bid;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Drc\PreOrder\Model\Bid', 'Drc\PreOrder\Model\ResourceModel\Bid');
    }
}