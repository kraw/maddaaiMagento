<?php

namespace EasyNolo\BancaSellaPro\Helper;

class Riskified extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function addParams(&$params, $order = null){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        $beaconSessionID = $customerSession->getSessionId();
        if(!$order || !$order->getId()) return;
        if(!$beaconSessionID) return;

        $gestpay = $order->getPayment()->getMethodInstance();

        $params['OrderDetails'] = array();
        $params['OrderDetails']['FraudPrevention'] = array();
        $params['OrderDetails']['FraudPrevention']['SubmitForReview'] = 1;
        $createdAt = $order->getCreatedAt();
        $orderDate = date('Y-m-d', strtotime($createdAt));
        $params['OrderDetails']['FraudPrevention']['OrderDateTime'] = $orderDate;
        $params['OrderDetails']['FraudPrevention']['Source'] = 'website';
        $params['OrderDetails']['FraudPrevention']['SubmissionReason'] = 'rule_decision';
        $params['OrderDetails']['FraudPrevention']['BeaconSessionID'] = $beaconSessionID;

        if($gestpay->getRiskifiedConfigData('customer_data')) {
            $params['OrderDetails']['CustomerDetail'] = array();
            $params['OrderDetails']['CustomerDetail']['FirstName'] = $order->getCustomerFirstname();
            $params['OrderDetails']['CustomerDetail']['Lastname'] = $order->getCustomerLastname();
            $params['OrderDetails']['CustomerDetail']['PrimaryEmail'] = $order->getCustomerEmail();
            if ($order->getCustomerDob())
                $params['OrderDetails']['CustomerDetail']['DateOfBirth'] = date('d/m/Y', strtotime($order->getCustomerDob()));
        }

        if (!$order->getIsVirtual()) {
            if ($gestpay->getRiskifiedConfigData('shipping_info')) {
                $shipping_address = $order->getShippingAddress();
                $params['OrderDetails']['ShippingAddress'] = array();
                $params['OrderDetails']['ShippingAddress']['FirstName'] = $shipping_address->getFirstname();
                $params['OrderDetails']['ShippingAddress']['Lastname'] = $shipping_address->getLastname();
                $params['OrderDetails']['ShippingAddress']['Email'] = $shipping_address->getEmail();
                $params['OrderDetails']['ShippingAddress']['StreetName'] = $shipping_address->getData('street');
                $params['OrderDetails']['ShippingAddress']['City'] = $shipping_address->getCity();
                $params['OrderDetails']['ShippingAddress']['CountryCode'] = $shipping_address->getCountryId();
                $params['OrderDetails']['ShippingAddress']['ZipCode'] = $shipping_address->getPostcode();
                $params['OrderDetails']['ShippingAddress']['PrimaryPhone'] = $shipping_address->getTelephone();
            }
        }

        if($gestpay->getRiskifiedConfigData('billing_info')) {
            $billing_address = $order->getBillingAddress();
            $params['OrderDetails']['BillingAddress'] = array();
            $params['OrderDetails']['BillingAddress']['FirstName'] = $billing_address->getFirstname();
            $params['OrderDetails']['BillingAddress']['Lastname'] = $billing_address->getLastname();
            $params['OrderDetails']['BillingAddress']['Email'] = $billing_address->getEmail();
            $params['OrderDetails']['BillingAddress']['StreetName'] = $billing_address->getData('street');
            $params['OrderDetails']['BillingAddress']['City'] = $billing_address->getCity();
            $params['OrderDetails']['BillingAddress']['CountryCode'] = $billing_address->getCountryId();
            $params['OrderDetails']['BillingAddress']['ZipCode'] = $billing_address->getPostcode();
            $params['OrderDetails']['BillingAddress']['PrimaryPhone'] = $billing_address->getTelephone();
        }

        if($gestpay->getRiskifiedConfigData('product_details')) {
            $params['OrderDetails']['ProductDetails']['ProductDetail'] = array();
            foreach($order->getAllItems() as $order_item) {

                if($order_item->getParentItem())continue;

                $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                    'ProductCode' => $order_item->getId(),
                    'Name' => $order_item->getName(),
                    'SKU' => $order_item->getSku(),
                    'Quantity' => (int)$order_item->getQtyOrdered(),
                    'UnitPrice' => round($order_item->getPrice(), 2),
                    'Price' => round($order_item->getRowTotal(), 2),
                    'Type' => 1,
                    'Vat' => (int)round($order_item->getTaxPercent()),
                    'RequiresShipping' => !$order_item->getProduct()->getIsVirtual() ? 'true' : 'false',
                    'Brand' => $order_item->getProduct()->getData('manufacturer') ? $order_item->getProduct()->getAttributeText('manufacturer') : '',
                );
            }
            if (!$order->getIsVirtual()) {
                $params['OrderDetails']['ShippingLines']['ShippingLine'] = array(
                    'Price' => round($order->getShippingAmount(), 2),
                    'Title' => $order->getShippingDescription(),
                    'Code' => $order->getShippingMethod(),
                );
            }
        }
    }
}