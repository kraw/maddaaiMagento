<?php
namespace Drc\PreOrder\Block;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;

class Cart extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    protected $_stockRegistry;
    protected $scopeConfig;
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        array $data = []
    ) {
        $this->_stockRegistry = $stockRegistry;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $productConfig, $checkoutSession, $imageBuilder, $urlHelper, $messageManager, $priceCurrency, $moduleManager, $messageInterpretationStrategy, $data);
    }

    /**
     * @param $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId)
    {
        return $this->_stockRegistry->getStockItem($productId);
    }
    public function getPreorderNote()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/preorder_note', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
