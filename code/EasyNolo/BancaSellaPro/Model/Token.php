<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 23/02/17
 * Time: 11:03
 */

namespace EasyNolo\BancaSellaPro\Model;

use Magento\Framework\Model\AbstractModel;

class Token extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('EasyNolo\BancaSellaPro\Model\ResourceModel\Token');
    }

    public function setTokenInfo($token, $expiryMonth, $expiryYear){

        $expiryDate = "20$expiryYear-$expiryMonth-01";
        $this->setExpireAt($expiryDate);
        $this->setCreatedAt(time());
        $this->setToken($token);

        return $this;

    }

}