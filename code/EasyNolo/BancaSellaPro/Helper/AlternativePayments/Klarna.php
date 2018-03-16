<?php
namespace EasyNolo\BancaSellaPro\Helper\AlternativePayments;

class Klarna extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_supportedCountries = array('AT', 'DK', 'FI', 'DE', 'NL', 'NO', 'SE', 'GB', 'US');

    public function isAvailableForCountry($countryCode)
    {
        return in_array($countryCode, $this->_supportedCountries);
    }

    public function getEncryptParams(\Magento\Sales\Model\Order $order){

        $method = $order->getPayment()->getMethodInstance();
        $shipping_address = $order->getIsVirtual() ? $order->getBillingAddress() : $order->getShippingAddress();
        $additionalData = $method->getInfoInstance()->getAdditionalInformation();

        if (!in_array($shipping_address->getCountryId(), $this->_supportedCountries)) {
            throw new \Exception('Unsupported country. Klarna can be used only in: '.implode(', ', $this->_supportedCountries));
        }

        $params = array('OrderDetails' => array('CustomerDetail' => array(), 'BillingAddress' => array(), 'ProductDetails' => array()));

        $params['OrderDetails']['CustomerDetail']['FirstName'] = $order->getCustomerFirstname();
        $params['OrderDetails']['CustomerDetail']['Lastname'] = $order->getCustomerLastname();
        $params['OrderDetails']['CustomerDetail']['PrimaryEmail'] = $order->getCustomerEmail();
        $params['OrderDetails']['CustomerDetail']['PrimaryPhone'] = $shipping_address->getTelephone();
        if ($additionalData) {
            $params['OrderDetails']['CustomerDetail']['SocialSecurityNumber'] = $additionalData['payment[social_security_number]'];
            $params['OrderDetails']['BillingAddress']['StreetName'] = $additionalData['payment[klarna_street]'];
            if($shipping_address->getCountryId() != 'DE' && $shipping_address->getCountryId() != 'NL') {
                if (!empty($additionalData['payment[klarna_street_number]'])) {
                    $params['OrderDetails']['BillingAddress']['StreetNumber'] = $additionalData['payment[klarna_street_number]'];
                }
            }
            if($shipping_address->getCountryId() == 'DE'){
                if (!empty($additionalData['payment[klarna_house_number]'])) {
                    $params['OrderDetails']['BillingAddress']['HouseNumber'] = $additionalData['payment[klarna_house_number]'];
                }
            }
            if($shipping_address->getCountryId() == 'NL'){
                if (!empty($additionalData['payment[klarna_house_number]'])) {
                    $params['OrderDetails']['BillingAddress']['HouseNumber'] = $additionalData['payment[klarna_house_number]'];
                }

                if (!empty($additionalData['payment[klarna_house_extension]'])) {
                    $params['OrderDetails']['BillingAddress']['HouseExtention'] = $additionalData['payment[klarna_house_extension]'];
                }
            }
        }

        $params['OrderDetails']['BillingAddress']['City'] = $shipping_address->getCity();
        $params['OrderDetails']['BillingAddress']['ZipCode'] = $shipping_address->getPostcode();
        if ($shipping_address->getCountryId() == 'US') {
            $params['OrderDetails']['BillingAddress']['State'] = $shipping_address->getRegion();
        }
        $params['OrderDetails']['BillingAddress']['CountryCode'] = $shipping_address->getCountryId();



        $taxType = 0;
        $surchargeType = 0;
        $storeCreditType = 0;
        $giftcardType = 0;
        $surchargeAmount = $order->getGrandTotal();
        switch ($shipping_address->getCountryId()) {
            case 'US':
                $taxType = 7;
            case 'GB':
                $shippingType = 6;
                $giftcardType = 9;
                $storeCreditType = 10;
                $surchargeType = 11;
                break;

            default:
                $shippingType = 2;
        }

        foreach($order->getAllItems() as $order_item) {
            if($order_item->getParentItem())
                continue;


            switch ($shipping_address->getCountryId()) {
                case 'US':
                case 'GB':
                    if ($order_item->getProduct()->getIsVirtual()) {
                        $type = 8;
                    } else {
                        $type = 5;
                    }

                    break;

                default:
                    $type = 1;
            }

            $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                'ProductCode' => $order_item->getId(),
                'Name' => $order_item->getName(),
                'SKU' => $order_item->getSku(),
                'Description' => $order_item->getName(),
                'Quantity' => (int)$order_item->getQtyOrdered(),
                'UnitPrice' => round($taxType ? $order_item->getRowTotal() : $order_item->getRowTotalInclTax(), 2),
                'Price' => round($taxType ? $order_item->getPrice() : $order_item->getPriceInclTax(), 2),
                'Type' => $type,
                'Vat' => !$taxType ? round($order_item->getTaxPercent(), 2) : 0,
                'Discount' => 0
            );

            if ($surchargeType) {
                $surchargeAmount -= round($taxType ? $order_item->getRowTotal() : $order_item->getRowTotalInclTax(), 2);
            }
        }

        if ($giftcardType && $order->getAwGiftCards()) {
            foreach($order->getAwGiftCards() as $card) {
                //add shipping costs
                $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                    'ProductCode' => 'Gift_Card',
                    'Name' => 'Gift_Card',
                    'SKU' => 'Gift_Card',
                    'Description' => 'Gift_Card',
                    'Quantity' => 1,
                    'UnitPrice' => round(-1 * abs($card->getGiftcardAmount()), 2),
                    'Price' => round(-1 * abs($card->getGiftcardAmount()), 2),
                    'Type' => $giftcardType,
                );

                if ($surchargeType) {
                    $surchargeAmount -= round(-1 * abs($card->getGiftcardAmount()), 2);
                }
            }
        }

        if ($shippingType && round($order->getShippingAmount(), 2) > 0.001) {
            //add shipping costs
            $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                'ProductCode' => $order->getShippingMethod(),
                'Name' => $order->getShippingDescription(),
                'SKU' => $order->getShippingMethod(),
                'Description' => $order->getShippingDescription(),
                'Quantity' => 1,
                'UnitPrice' => round($order->getShippingAmount(), 2),
                'Price' => round($order->getShippingAmount(), 2),
                'Type' => $shippingType,
                'Vat' => !$taxType && $order->getShippingTaxAmount() > 0.001 ? round($order->getShippingTaxAmount() / $order->getShippingInclTax(), 2) : 0,
                'Discount' => round($order->getShippingDiscountAmount(), 2)
            );

            if ($surchargeType) {
                $surchargeAmount -= round($order->getShippingAmount(), 2);
            }
        }

        if (round($order->getDiscountAmount(), 2) < -0.001) {
            //add shipping costs
            $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                'ProductCode' => 'Discount',
                'Name' => 'Discount',
                'SKU' => 'Discount',
                'Description' => $order->getDiscountDescription(),
                'Quantity' => 1,
                'UnitPrice' => -1*abs(round($order->getDiscountAmount(), 2)),
                'Price' => -1*abs(round($order->getDiscountAmount(), 2)),
                'Type' => 4,
                'Vat' => 0,
                'Discount' => 0
            );

            if ($surchargeType) {
                $surchargeAmount -= -1*abs(round($order->getDiscountAmount(), 2));
            }
        }

        if ($taxType && round($order->getTaxAmount(), 2) > 0.001) {
            //add shipping costs
            $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                'ProductCode' => 'Sales_tax',
                'Name' => 'Sales_tax',
                'SKU' => 'Sales_tax',
                'Description' => 'Sales_tax',
                'Quantity' => 1,
                'UnitPrice' => round($order->getTaxAmount(), 2),
                'Price' => round($order->getTaxAmount(), 2),
                'Type' => $taxType,
            );

            if ($surchargeType) {
                $surchargeAmount -= round($order->getTaxAmount(), 2);
            }
        }

        if ($storeCreditType) {
            $storeCreditAmount = 0;
            foreach (array('customer_credit', 'store_credit') as $field) {
                if ($order->getData($field) && $order->getData($field) > 0.001) {
                    $storeCreditAmount = number_format(-1 * abs($order->getData($field)), 2);
                    break;
                }
                $field .= '_amount';
                if ($order->getData($field) && $order->getData($field) > 0.001) {
                    $storeCreditAmount = number_format(-1 * abs($order->getData($field)), 2);
                    break;
                }
            }

            if ($storeCreditAmount) {
                //add storecredit costs
                $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                    'ProductCode' => 'Store_credit',
                    'Name' => 'Store_credit',
                    'SKU' => 'Store_credit',
                    'Description' => 'Store_credit',
                    'Quantity' => 1,
                    'UnitPrice' => $storeCreditAmount,
                    'Price' => $storeCreditAmount,
                    'Type' => $storeCreditType,
                );

                if ($surchargeType) {
                    $surchargeAmount -= $storeCreditAmount;
                }
            }
        }

        if ($surchargeType && $surchargeAmount >= 0.001) {
            $params['OrderDetails']['ProductDetails']['ProductDetail'][] = array(
                'ProductCode' => 'Surcharge',
                'Name' => 'Surcharge',
                'SKU' => 'Surcharge',
                'Description' => 'Surcharge',
                'Quantity' => 1,
                'UnitPrice' => $surchargeAmount,
                'Price' => $surchargeAmount,
                'Type' => $surchargeType,
            );
        }

        return $params;
    }

}