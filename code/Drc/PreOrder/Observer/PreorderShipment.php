<?php

namespace Drc\PreOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreorderShipment implements ObserverInterface
{
    
    protected $logger;
    protected $order;
    protected $stockStatus;
    protected $scopeConfig;
    protected $configurableProduct;
    protected $stockRegistry;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order $order,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockStatus,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $configurableProduct,
        \Magento\CatalogInventory\Api\StockRegistryInterface $StockRegistryInterface
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->scopeConfig=$scopeConfig;
        $this->stockStatus = $stockStatus;
        $this->configurableProduct = $configurableProduct;
        $this->stockRegistry=$StockRegistryInterface;
    }
    public function execute(Observer $observer)
    {

        $active=$this->scopeConfig->getValue('drc_preorder_setting/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($active==1) {
            $flag=false;
            $shipment = $observer->getShipment();
            $order = $shipment->getOrder();
            $order->getIncrementId();
            $items = $order->getAllItems();
            foreach ($order->getAllItems() as $i) {
                if ($i->getQtyShipped() != 1) {
                    $type=$i->getData('product_type');
                    $id=$i->getData('product_id');
                    if ($type=='configurable') {
                        $sku=$i->getData('sku');
                        $selectProduct=$configurableProduct->get('$sku');
                        $id=$selectProduct->getId();
                    } elseif ($type=='bundle') {
                        $count1=0;
                        $count2=0;
                        $bundle=false;
                        $collection = $i->getProduct()->getTypeInstance(true)
                        ->getSelectionsCollection($i->getProduct()->getTypeInstance(true)
                        ->getOptionsIds($i->getProduct()), $i->getProduct());
                        $sku=$i->getData('sku');
                        foreach ($collection as $item) {
                            $childSku=$item->getSku();
                            $pos = strpos($sku, $childSku);
                            if ($pos) {
                                $count1++;
                                $id=$item->getId();
                                $productStockData = $this->stockRegistry->getStockItem($id);
                                $backorderStatusBundle=$productStockData->getData('backorders');
                                $stockStatusBundle=$productStockData->getData('is_in_stock');
                          
                                if (($backorderStatusBundle=='3' && $stockStatusBundle=='1') || ($backorderStatusBundle=='4' && $stockStatusBundle==null )) {
                                    $bundle=true;
                                    $count2++;
                                } else {
                                    $bundle=false;
                                }
                            }
                        }
                        if ($bundle==true && $count2==$count1) {
                            $flag=true;
                        }
                 
                        $id=null;
                    } else {
                        $id=$i->getData('product_id');
                    }
                        $productStockData = $this->stockRegistry->getStockItem($id);
                        $backorderStatus=$productStockData->getData('backorders');
                        $stockStatus=$productStockData->getData('is_in_stock');
                    if (($backorderStatus=='3' && $stockStatus=='1') || ($backorderStatus=='4' && $stockStatus==null )) {
                        $flag=true;
                    }
                }
            }
       
            if ($flag) {
                     $orderStatus=$order->getStatus();
                if ($orderStatus=='pending') {
                    $order->setStatus('pre-order_pending');
                    $order->setState('pre-order_pending', true);
                    $order->save();
                } elseif ($orderStatus=='processing') {
                    $order->setStatus('pre-order_processing');
                    $order->setState('pre-order_processing', true);
                    $order->save();
                }
            }
        }
    }
}
