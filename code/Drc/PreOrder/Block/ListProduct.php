<?php
namespace Drc\PreOrder\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use \Magento\Framework\App\ObjectManager;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    protected $_stockItemRepository;
    protected $_stockRegistry;
    protected $scopeConfig;
    protected $bundleProduct;
    public function __construct(
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,        
        \Magento\Bundle\Model\Product\Type $bundleProduct,
        array $data = []
    ) {
        $this->_stockItemRepository = $stockItemRepository;
        $this->scopeConfig = $context->getScopeConfig();
        $this->bundleProduct=$bundleProduct;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
        $this->_stockRegistry =  ObjectManager::getInstance()->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
    }

    public function getStockItem($productId)
    {
        return $this->_stockRegistry->getStockItem($productId);
    }
    public function getPreorderButtonText()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/button_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getBidDaysBeforeTimer()
    {
      return intval($this->scopeConfig->getValue('drc_preorder_setting/display/datetovisualize', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }
}
