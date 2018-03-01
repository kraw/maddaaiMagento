<?php

namespace Drc\PreOrder\Block;

class Grouped extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{
    protected $_stockItemRepository;
    protected $scopeConfig;
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,        
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    ) {
        $this->_stockItemRepository = $stockItemRepository;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $arrayUtils, $data);
    }
    
    public function getStockItem($productId)
    {
        return $this->_stockItemRepository->get($productId);
    }
    public function getPreorderNote()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/preorder_note', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
