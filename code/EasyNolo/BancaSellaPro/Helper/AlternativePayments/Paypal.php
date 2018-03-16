<?php
namespace EasyNolo\BancaSellaPro\Helper\AlternativePayments;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Paypal extends \Magento\Framework\App\Helper\AbstractHelper {

    public function getEncryptParams(\Magento\Sales\Model\Order $order){

        $params = array();

        $storeId = $order->getStoreId();
        $showProductInfo = (bool)$this->scopeConfig->getValue(
            'payment/easynolo_bancasellapro_alternative/paypal_show_product_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $sellerProtection = (bool)$this->scopeConfig->getValue(
            'payment/easynolo_bancasellapro_alternative/paypal_seller_protection',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if($showProductInfo){
            $params['OrderDetails'] = array();
            $params['OrderDetails']['ProductDetails'] = array();
            foreach($order->getAllItems() as $order_item) {
                $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                    'ProductCode' => $order_item->getId(),
                    'Name' => $order_item->getName(),
                    'SKU' => $order_item->getSku(),
                    'Description' => $order_item->getDescription(),
                    'Quantity' => (int)$order_item->getQtyOrdered(),
                    'UnitPrice' => round($order_item->getPrice(), 2),
                    'Price' => round($order_item->getRowTotal(), 2),
                    'Type' => 1,
                    'Vat' => $order_item->getTaxPercent().'%',
                    'Discount' => round($order_item->getDiscountAmount(), 2)
                );
            }
        }

        if($sellerProtection){
            $params['ppSellerProtection'] = 1;
            $shipping_address = $order->getShippingAddress();
            $params['shippingDetails'] = array();
            $params['shippingDetails']['shipToName'] = $shipping_address->getFirstname().' '.$shipping_address->getLastname();
            $params['shippingDetails']['shipToStreet'] = $shipping_address->getStreetLine(1);
            $params['shippingDetails']['shipToCity'] = $shipping_address->getCity();
            $params['shippingDetails']['shipToCountryCode'] = $shipping_address->getCountryId();
            $params['shippingDetails']['shipToZip'] = $shipping_address->getPostcode();
            $params['shippingDetails']['shipToStreet2'] = $shipping_address->getStreetLine(2);
        }

        return $params;
    }

}
