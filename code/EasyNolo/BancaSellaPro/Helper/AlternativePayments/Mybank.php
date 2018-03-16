<?php
namespace EasyNolo\BancaSellaPro\Helper\AlternativePayments;

class MyBank extends \Magento\Framework\App\Helper\AbstractHelper {

    public function getEncryptParams(\Magento\Sales\Model\Order $order){
        return array();
    }

}