<?php
namespace Drc\PreOrder\Block;

class Preordernote extends \Magento\Catalog\Block\Product\View\AbstractView
{

    protected $_stockItemRepository;
    protected $scopeConfig;
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,        
        array $data = []
    ) {
        $this->_stockItemRepository = $stockItemRepository;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }
    public function getStockItem($productId)
    {
        return $this->_stockItemRepository->get($productId);
    }
    public function getPreorderNote()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/preorder_note', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getBidTarget()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/bid_target', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
