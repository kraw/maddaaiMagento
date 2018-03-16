<?php
namespace EasyNolo\BancaSellaPro\Helper\AlternativePayments;

class Qiwi extends \Magento\Framework\App\Helper\AbstractHelper {

    public function getEncryptParams(\Magento\Sales\Model\Order $order){

        $params = array('OrderDetails' => array('BillingAddress' => array(), 'CustomerDetail' => array()));

        $params['OrderDetails']['BillingAddress']['CountryCode'] = $order->getBillingAddress()->getCountryId();
        $params['OrderDetails']['CustomerDetail']['PrimaryPhone'] = $order->getBillingAddress()->getTelephone();

        return $params;
    }

}
