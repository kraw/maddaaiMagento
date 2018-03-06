<?php
namespace Drc\PreOrder\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

class AddToCart extends \Magento\Catalog\Block\Product\View
{

    protected $_stockItemRepository;
    protected $scopeConfig;
    protected $like;

    private $_objectManager;

    const ORDINI_RAGGIUNTI = " ORIDINI RAGIUNTI";
    const ORDINI_RIMASTI = "MANCANO ANCORA ";
    const ORDINI_MANCANTI = " ORDINI PER RAGGIUNGERE IL QUORUM";


    public function __construct(
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,        
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
	    \Drc\PreOrder\Model\ResourceModel\Like $like,
        array $data = []
    ) {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_stockItemRepository = $stockItemRepository;
	    $this->like = $like;
        $this->scopeConfig = $context->getScopeConfig();
	    $this->customerSession = $customerSession;
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);
    }
    public function getStockItem($productId)
    {
        $productStockObj = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($productId);
        return $productStockObj->getQty();

    }

    public function isValutationProduct($_product){
        return $_product->getTypeId() === "valutation_product";
    }

    public function getPreorderButtonText()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/button_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getBidTarget()
    {
        return $this->scopeConfig->getValue('drc_preorder_setting/display/bid_target', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getBidDaysBeforeTimer()
    {
      return intval($this->scopeConfig->getValue('drc_preorder_setting/display/datetovisualize', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    /**
     * @TODO Da aggiungere stato se altrimenti
     * @return bool
     */
    public function checkLikeExistence() {
	$like = $this->like->getLikeByProductIdCustomerId($this->getProduct()->getId(), $this->customerSession->getCustomer()->getId());
	if($like)
		return true;
	return false;
    }
}
