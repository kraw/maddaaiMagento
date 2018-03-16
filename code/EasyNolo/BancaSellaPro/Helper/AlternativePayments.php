<?php

namespace EasyNolo\BancaSellaPro\Helper;
use Magento\Framework\Config\Data as DataConfig;

class AlternativePayments extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;
    /**
     * @var \EasyNolo\BancaSellaPro\Helper\AlternativePayments\Config
     */
    protected $_alternativePaymentsConfig;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    protected $_methods = null;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_layoutFactory = $layoutFactory;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_alternativePaymentsConfig = $objectManager->create('EasyNolo\BancaSellaPro\Model\Config');

        $this->_checkoutSession = $checkoutSession;

        return parent::__construct($context);
    }

    protected function _init()
    {
        if (is_null($this->_methods)) {
            $this->_methods = $this->_alternativePaymentsConfig->getActiveAlternativeMethods();
        }

        return $this->_methods;
    }

    public function isEnabled()
    {
        $methods = $this->_init();

        return count($methods) > 0;
    }

    public function getAlternativePayments()
    {
        $json = [];
        $methods = $this->_init();

        foreach ($methods as $code => $method) {

            if ($code == 'klarna') {
                $shippingAddress = $this->_checkoutSession->getQuote()->getShippingAddress();

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $helperPayment = $objectManager->create($method['encrypt_helper']);

                if (!$shippingAddress || !$shippingAddress->getCountryId()) {
                    // accept if no country
                } else if (!$helperPayment->isAvailableForCountry($shippingAddress->getCountryId())) {
                    continue;
                }
            }

            $json[$code] = array(
                'title' => $method['title'],
                'type' => $method['type'],
                'encrypt_helper' => empty($method['encrypt_helper']) ? '' : $method['encrypt_helper'],
                'code' => $code,
            );

            if (empty($method['template'])) {
                $method['template'] = 'default.phtml';
            }

            $templateBlock = $this->_layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
            $templateBlock->setTemplate('EasyNolo_BancaSellaPro::alternative_payments/'.$method['template']);
            $templateBlock->setData('code', $code);
            $templateBlock->setData('method_config', $method);
            $templateBlock->setData('address', $this->_checkoutSession->getQuote()->getShippingAddress());
            $json[$code]['form'] = $templateBlock->toHtml();
        }

        return $json;
    }

    public function getMethodsJson()
    {
        $json = array();
        $methods = $this->_init();

        foreach ($methods as $code => $method) {
            $json[$code] = array(
                'title' => $method['title'],
                'type' => $method['type'],
            );
        }

        return json_encode($json);
    }
}