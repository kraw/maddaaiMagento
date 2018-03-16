<?php

namespace EasyNolo\BancaSellaPro\Model;

/**
 * Payment configuration model
 *
 * Used for retrieving configuration data by payment models
 */
class Config
{
    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    protected $_methods = null;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Config\DataInterface $dataStorage
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Retrieve active system payments
     *
     * @return array
     * @api
     */
    public function getActiveAlternativeMethods()
    {
        if (is_null($this->_methods)) {
            $this->_methods = array();
            foreach ($this->_dataStorage->get('alternative_payments') as $code => $payment) {
                if ($this->_scopeConfig->getValue('payment/easynolo_bancasellapro_alternative/enable_'.$code)) {
                    $this->_methods[$code] = (array)$payment;
                }
            }
        }

        return $this->_methods;
    }
}
