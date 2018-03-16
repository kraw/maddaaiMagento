<?php

namespace EasyNolo\BancaSellaPro\Helper;

class Red extends \Magento\Framework\App\Helper\AbstractHelper
{

    private function _sanitize($str, $type, $length=null){

        if (!$length) {
            $length = strlen($str);
        }

        switch ($type){
            case 'Alphanumeric':
                return substr(preg_replace("/[^A-Za-z0-9 ]/", '', $str), 0, $length);
            case 'Numeric';
                return (int)substr($str, 0, $length);
            case 'String':
                return substr($str, 0, $length);
            case 'Email';
                if($f = filter_var(substr($str, 0, $length), FILTER_VALIDATE_EMAIL))
                    return $f;
                return '';
            case 'DoB':
                if(preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $str))
                    return $str;
                return '';
            case 'IP';
                if(preg_match("/^[0-9]{3}\.[0-9]{3}\.[0-9]{3}\.[0-9]{3}$/", $str))
                    return $str;
                return '';
        }

        return $str;
    }

    public function addParams(&$params, $order=null){
        if (!$order || !$order->getId()) return;

        $gestpay = $order->getPayment()->getMethodInstance();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $countryFactory = $objectManager->create('Magento\Directory\Model\CountryFactory');

        $params['redFraudPrevention'] = 1;
        // Red_CustomerInfo
        if($gestpay->getRedConfigData('customer_info')) {
            $params['Red_CustomerInfo'] = array(
                'Customer_Name' => $this->_sanitize($order->getCustomerFirstname(), 'String', 30),
                'Customer_Surname' => $this->_sanitize($order->getCustomerLastname(), 'String', 30),
                'Customer_Email' => $this->_sanitize($order->getCustomerEmail(), 'Email', 45),
            );
            if($order->getCustomerPrefix())
                $params['Red_CustomerInfo']['Customer_Title'] = $this->_sanitize($order->getCustomerPrefix(), 'String', 5);
        }
        // Red_ShippingInfo
        if($gestpay->getRedConfigData('shipping_info')) {
            $shipping_address = $order->getShippingAddress();
            $params['Red_ShippingInfo'] = array(
                'Shipping_Name' => $this->_sanitize($shipping_address->getFirstname(), 'String', 30),
                'Shipping_Surname' => $this->_sanitize($shipping_address->getLastname(), 'String', 30),
                'Shipping_Email' => $this->_sanitize($shipping_address->getEmail(), 'Email', 45),
                'Shipping_Address' => $this->_sanitize($shipping_address->getData('street'), 'String', 30),
                'Shipping_City' => $this->_sanitize($shipping_address->getCity(), 'String', 20),
                'Shipping_Country' => $this->_sanitize($countryFactory->create()->loadByCode($shipping_address->getCountryId())->getIso3Code(), 'String', 3),
                'Shipping_PostalCode' => $this->_sanitize($shipping_address->getPostcode(), 'Alphanumeric', 9),
                'Shipping_HomePhone' => $this->_sanitize($shipping_address->getTelephone(), 'Numeric', 19),
            );
            if($shipping_address->getRegionId())
                $params['Red_ShippingInfo']['Shipping_StateCode'] = $this->_sanitize($shipping_address->getRegion(), 'String', 2);
        }
        // Red_BillingInfo
        if($gestpay->getRedConfigData('billing_info')) {
            $billing_address = $order->getBillingAddress();
            $params['Red_BillingInfo'] = array(
                'Billing_Id' => $this->_sanitize($billing_address->getEntityId(), 'Alphanumeric', 16),
                'Billing_Name' => $this->_sanitize($billing_address->getFirstname(), 'String', 30),
                'Billing_Surname' => $this->_sanitize($billing_address->getLastname(), 'String', 30),
                'Billing_DateOfBirth' => $this->_sanitize(date('Y-m-d', strtotime($order->getCustomerDob())), 'DoB'),
                'Billing_Email' => 'challenge@email.com',
                'Billing_Address' => $this->_sanitize($billing_address->getData('street'), 'String', 30),
                'Billing_City' => $this->_sanitize($billing_address->getCity(), 'String', 20),
                'Billing_Country' => $this->_sanitize($countryFactory->create()->loadByCode($billing_address->getCountryId())->getIso3Code(), 'String', 3),
                'Billing_PostalCode' => $this->_sanitize($billing_address->getPostcode(), 'Alphanumeric', 9),
                'Billing_HomePhone' => $this->_sanitize($billing_address->getTelephone(), 'Numeric', 19),
            );
            if($billing_address->getRegionId())
                $params['Red_BillingInfo']['Billing_StateCode'] = $this->_sanitize($billing_address->getRegion(), 'String', 2);
        }
        // Red_CustomerData
        if($gestpay->getRedConfigData('customer_data')){
            $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $baseUrl= $storeManager->getStore()->getBaseUrl();
            $params['Red_CustomerData'] = array(
                'MerchantWebSite' => $this->_sanitize($baseUrl, 'String', 60),
            );
            if($order->getRemoteIp())
                $params['Red_CustomerData']['Customer_IpAddress'] = $this->_sanitize($order->getRemoteIp(), 'IP');
            if($gestpay->getRedConfigData('merchant_id'))
                $params['Red_CustomerData']['Red_Merchant_ID'] = $gestpay->getRedConfigData('merchant_id');
            if($gestpay->getRedConfigData('service_type'))
                $params['Red_CustomerData']['Red_ServiceType'] = $gestpay->getRedConfigData('service_type');
        }
        // Red_Items
        if($gestpay->getRedConfigData('order_items')) {
            $params['Red_Items'] = array(
                'NumberOfItems' => count($order->getAllItems()),
                'Red_Item' => array()
            );
            foreach($order->getAllItems() as $order_item) {
                $params['Red_Items']['Red_Item'][] = array(
                    'Item_ProductCode' => $this->_sanitize($order_item->getSku(), 'String', 12),
                    'Item_Description' => $this->_sanitize($order_item->getName(), 'String', 26),
                    'Item_Quantity' => (int)$order_item->getQtyOrdered(),
                    'Item_InitCost' => (int)($order_item->getPrice() * 10000),
                    'Item_TotalCost' => (int)($order_item->getRowTotal() * 10000)
                );
            }
        }
    }
}