<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 22/01/17
 * Time: 10:35
 */
namespace EasyNolo\BancaSellaPro\Model;

class GestpayConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    public function getConfig()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $gestpay = $objectManager->create('EasyNolo\BancaSellaPro\Model\Gestpay');
        $urlBuilder = $objectManager->create('Magento\Framework\UrlInterface');
        $tokenModel = $objectManager->create('EasyNolo\BancaSellaPro\Model\Token');
        $alternativeHelper = $objectManager->create('EasyNolo\BancaSellaPro\Helper\AlternativePayments');
        $customerSession = $objectManager->create('\Magento\Customer\Model\Session');

        $tokens = $tokenModel->getCollection();
        if($customerSession->isLoggedIn()) {
            $tokens->addFieldToFilter('customer_id', $customerSession->getCustomerId());
        }

        $alternatives = $alternativeHelper->getAlternativePayments();

        return [
            'payment' => [
                'easynolo_bancasellapro' => [
                    'description' => $gestpay->getConfigData('description'),
                    'base_url' => $gestpay->getBaseWSDLUrlSella(),
                    'shop_login' => $gestpay->getConfigData('merchant_id'),
                    'redirect_url' => $urlBuilder->getUrl("bancasellapro/gestpay/redirect/"),
                    'pay_using_token_url' => $urlBuilder->getUrl("bancasellapro/token/payUsingToken/"),
                    'success_redirect_url' => $urlBuilder->getUrl("bancasellapro/gestpay/result/"),
                    'get_encrypted_string_url' => $urlBuilder->getUrl("bancasellapro/gestpay/getEncryptedString/"),
                    'is_iframe_enabled' => ($gestpay->getConfigData('iframe') == 1),
                    '3d_auth_page_url' => $gestpay->get3dAuthPageUrl(),
                    '3d_auth_redirect_url' => $urlBuilder->getUrl("bancasellapro/gestpay/confirm3d/"),
                    'tokens' => $tokens->toJson(),
                    'alternatives' => array_values($alternatives),
                ]
            ]
        ];
    }

}