<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 03/02/17
 * Time: 18:13
 */

namespace Drc\PreOrder\Model;


class Bid extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Drc\Preorder\Model\ResourceModel\Bid');
    }
}
