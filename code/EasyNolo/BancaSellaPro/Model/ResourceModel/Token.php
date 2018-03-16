<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 23/02/17
 * Time: 11:03
 */

namespace EasyNolo\BancaSellaPro\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Token extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('easynolo_bancasellapro_token', 'id');
    }
}