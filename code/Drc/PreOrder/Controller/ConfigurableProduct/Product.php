<?php
namespace Drc\PreOrder\Controller\ConfigurableProduct;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Product extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $_stockItemRepository;
    protected $scopeConfig;
    protected $productCollection;
    protected $resultJsonFactory;
    public function __construct(Context $context, PageFactory $pageFactory, \Magento\Framework\App\Request\Http $request, \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\Data\ProductInterface $productCollection,\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->_stockItemRepository = $stockItemRepository;
        $this->scopeConfig=$scopeConfig;
        $this->request = $request;
        $this->productCollection=$productCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create(); 
        $json = [];
        $productId = $this->request->getPost("product_id");
        $productStock = $this->_stockItemRepository->get($productId);
        $backorderStatus = $productStock->getBackorders();
        $defaulttxt=$this->scopeConfig->getValue('drc_preorder_setting/display/button_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $defaultnote=$this->scopeConfig->getValue('drc_preorder_setting/display/preorder_note', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $product=$this->productCollection->load($productId);
        if (($backorderStatus=='3' && ($product->IsSalable())) || ($backorderStatus=='4' && (!$product->IsSalable()))) {
            $note=$product->getData('preorder note');
           
            if ($defaulttxt != '') {
                if ($note != '') {
                    $json['buttontxt'] =__($defaulttxt);
                    $json['note'] = __($note);
                    return $result->setData(json_encode($json));
                  
                } elseif ($defaultnote != '') {
                    $json['buttontxt']=__($defaulttxt);
                    $json['note']=$defaultnote;
                    return $result->setData(json_encode($json));
                } else {
                    $json['buttontxt']=__($defaulttxt);
                    $json['note']=__('available soon...!');
                     return $result->setData(json_encode($json));
                }
            } else {
                if ($note != '') {
                    $json['buttontxt']=__('Pre Order');
                    $json['note']=__($note);
                    return $result->setData(json_encode($json));
                } elseif ($defaultnote != '') {
                    $json['buttontxt']=__('Pre Order');
                    $json['note']=__($defaultnote);
                    return $result->setData(json_encode($json));
                } else {
                    $json['buttontxt']=__('Pre Order');
                    $json['note']=__('available soon...!');
                     return $result->setData(json_encode($json));
                }
            }
        } elseif ((!$product->IsSalable()) && (!$backorderStatus == '4')) {
            return $result->setData(['outofstock' => 'Out Of Stock']);
        } else {
           return $result->setData(['false' => 'false']);
        }
    }
}
