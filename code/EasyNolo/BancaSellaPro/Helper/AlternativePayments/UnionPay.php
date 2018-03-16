<?php
namespace EasyNolo\BancaSellaPro\Helper\AlternativePayments;

class UnionPay extends \Magento\Framework\App\Helper\AbstractHelper {

    public function getEncryptParams(\Magento\Sales\Model\Order $order){

        $params = array('OrderDetails' => array('CustomerDetail' => array()));

        $params['OrderDetails']['CustomerDetail']['PrimaryEmail'] = $order->getCustomerEmail();
        $params['OrderDetails']['CustomerDetail']['PrimaryPhone'] = $order->getBillingAddress()->getTelephone();

        return $params;
    }

}
