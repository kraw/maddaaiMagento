<?php
namespace Syrus\Deadline\Block\Product;
use DateTime;
use \Magento\Catalog\Model\ResourceModel\Product\Collection;
use \Magento\Catalog\Model\Product;
use \Magento\Framework\App\ObjectManager;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList implements \Magento\Widget\Block\BlockInterface
{
    const DEFAULT_COLLECTION_SORT_BY = 'created_at';
    const DEFAULT_COLLECTION_ORDER = 'desc';


    /**
     * Range di scadenza in giorni
     */
    private static $AVAILABILITY_RANGE;

    protected $_stockFilter;
    protected $_stockRegistry;
    protected $scopeConfig;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data
        );

        $this->scopeConfig = $context->getScopeConfig();
        $timer = ObjectManager::getInstance()->get('Drc\PreOrder\Helper\Check')->displayTimer();
        self::$AVAILABILITY_RANGE = "+$timer days";

        $this->_stockRegistry =  ObjectManager::getInstance()->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->_stockFilter =    ObjectManager::getInstance()->get('\Magento\CatalogInventory\Helper\Stock');
    }

    /**
     * Prepare and return product collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function createCollection()
    {
        $inStock = true;
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addAttributeToSort($this->getSortBy(), $this->getSortOrder());


        $filterByType = $this->getData('product_type');
        $filters = $this->getData('product_filter');

        $now = new DateTime();

        if ($filterByType === "valutation_product")
            $this->getValutationProducts($collection, $now);
        else {
            if ($filters === "conclusi"){
                $this->getClosedBidProducts($collection, $now);
                $inStock = false;
            }

            elseif ($filters === "in_scadenza")
                $this->getDeadlineProducts($collection, $now);

            else
                $this->getBidProducts($collection, $now);
        }

        if($inStock)
            $this->_stockFilter->addInStockFilterToCollection($collection);



        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1));

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        return $collection;
    }

    private function getCollectionByTemplate($filters){
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addAttributeToSort($this->getSortBy(), $this->getSortOrder());

        $now = new DateTime();
        if ($filters === "conclusi")
            $this->getClosedBidProducts($collection, $now);
        elseif ($filters === "in_scadenza"){
            $this->_stockFilter->addInStockFilterToCollection($collection);
            $this->getDeadlineProducts($collection, $now);
        }
        else{
            $this->_stockFilter->addInStockFilterToCollection($collection);
            $this->getBidProducts($collection, $now);
        }


        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1));

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        return $collection;
    }

    /**
     * @return $this|Collection
     */
    public function createDeadlineCollection(){
        return $this->getCollectionByTemplate('in_scadenza');
    }

    /**
     * @return $this|Collection
     */
    public function createClosedBidCollection(){
        return $this->getCollectionByTemplate('conclusi');
    }

    /**
     * @return $this|Collection
     */
    public function createBidCollection(){
        return $this->getCollectionByTemplate(FALSE);
    }


    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    private function getValutationProducts(Collection $collection, DateTime $dateTime){
        $collection->addAttributeToFilter('type_id', array('eq' => 'valutation_product'));
        $collection->addAttributeToFilter('valutation_product_end_date', array('lteq' => $dateTime->format('Y-m-d H:i:s')));
    }


    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param DateTime $dateTime
     */
    private function getBidProducts(Collection $collection, DateTime $dateTime){
        $collection->addFieldToFilter('bid_target', ['gt' => 0]);
        $dateTime->modify(self::$AVAILABILITY_RANGE);
        $collection->addFieldToFilter('bid_end_date', ['gteq' => $dateTime->format('Y-m-d H:i:s')]);

    }

    /**
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param DateTime $dateTime
     */
    private function getDeadlineProducts(Collection $collection, DateTime $dateTime){
        $collection->addFieldToFilter('bid_target', ['gt' => 0]);
        $collection->addFieldToFilter('bid_end_date', ['gteq' => $dateTime->format('Y-m-d H:i:s')]);
        $dateTime->modify(self::$AVAILABILITY_RANGE);
        $collection->addFieldToFilter('bid_end_date', ['lteq' => $dateTime->format('Y-m-d H:i:s')]);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param DateTime $dateTime
     */
    private function getClosedBidProducts(Collection $collection, DateTime $dateTime){
        $collection->addFieldToFilter('bid_target', ['gt' => 0]);
        $collection->addFieldToFilter('bid_end_date', ['lteq' => $dateTime->format('Y-m-d H:i:s')]);
    }



    /**
     * Retrieve sort by
     *
     * @return int
     */
    public function getSortBy()
    {
        if (!$this->hasData('collection_sort_by')) {
            $this->setData('collection_sort_by', self::DEFAULT_COLLECTION_SORT_BY);
        }
        return $this->getData('collection_sort_by');
    }

    /**
     * Retrieve sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        if (!$this->hasData('collection_sort_order')) {
            $this->setData('collection_sort_order', self::DEFAULT_COLLECTION_ORDER);
        }
        return $this->getData('collection_sort_order');
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function getQty($productId)
    {
        return $this->_stockRegistry->getStockItem($productId)->getQty();
    }

    /**
     * @param $productType
     * @return mixed|string
     */
    public function getButtonText($productType){
        if ($this->isValutationProduct($productType)) return "Visualizza";
        return $this->scopeConfig->getValue('drc_preorder_setting/display/button_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $productType
     * @return bool
     */
    public function isValutationProduct($productType){
        return $productType === "valutation_product";
    }

    /**
     * @param $product
     * @return bool
     */
    public function showTimerBar(Product $product){
        $now = new \DateTime();
        $now->modify(self::$AVAILABILITY_RANGE);
        return new \DateTime($product->getData('bid_end_date')) <= $now && !$this->isValutationProduct($product->getTypeId());
    }

    /**
     * @param $product
     * @return bool
     */
    public function isBidOver(Product $product){
        return  $product->getData('bid_target') ===  $this->getQty($product->getTypeId());
    }

    /**
     *
     * @param Product $product
     * @return mixed
     */
    public function getMembershipNumber(Product $product){
        if($this->isValutationProduct($product->getTypeId()))
            return $product->getData('valutation_product_likes');
        return $product->getData('bid_target');
    }




}
