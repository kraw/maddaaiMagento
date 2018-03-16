<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 22/01/17
 * Time: 01:25
 */
namespace EasyNolo\BancaSellaPro\Model\WS;

class AbstractWebService extends \Magento\Framework\Model\AbstractModel{

    protected $url_home;
    protected $_helper = null;
    protected $_gestpay = null;
    protected $client = null;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \EasyNolo\BancaSellaPro\Helper\Data $helper,
        \EasyNolo\BancaSellaPro\Model\Gestpay $gestpay,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_gestpay = $gestpay;
        $this->_initClient();
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    private function _initClient(){
        if (!extension_loaded('soap')) {
            $this->log('ERROR! Unable to create WebService client - PHP SOAP extension is required.');
            throw new \Exception('PHP SOAP extension is required.');
            return false;
        }

        $url = $this->getWSUrl();
        $this->client = new \Zend_Soap_Client(
            $url, [
                'compression' => SOAP_COMPRESSION_ACCEPT,
                'soap_version' => SOAP_1_2
            ]
        );
    }

    public function setBaseUrl($url)
    {
        $this->url_home = $url;
    }

    public function getWSUrl()
    {
        return $this->_gestpay->getBaseWSDLUrlSella() . static::PATH_WS_CRYPT_DECRIPT;
    }
}