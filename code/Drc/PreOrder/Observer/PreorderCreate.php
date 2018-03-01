<?php

namespace Drc\PreOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreorderCreate implements ObserverInterface
{
    
    protected $logger;
    protected $order;
    protected $stockStatus;
    protected $product;
    protected $scopeConfig;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order $order,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockStatus,
        \Magento\Catalog\Api\ProductRepositoryInterface $product,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->product = $product;
        $this->stockStatus = $stockStatus;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $active = $this->scopeConfig->getValue('drc_preorder_setting/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($active == 1) {
            $flag = false;
            $count1 = 0;
            $count2 = 0;
            $bundle = false;
            $backorderStatus = '';
            $stockStatus = '';
            $oid = $observer->getData('order_ids');
            $orderCollection = $this->order->load($oid);
            $items = $orderCollection->getAllVisibleItems();
            foreach ($items as $item) {
                $obj=$this->stockStatus->get($item->getProductId());
                $type=$item->getData('product_type');

                if ($type == 'configurable') {
                    $sku = $item->getData('sku');
                    $selectProduct = $this->product->get($sku);
                    $id = $selectProduct->getId();
                    $obj = $this->stockStatus->get($id);
                    $backorderStatus = $obj->getData('backorders');
                    $stockStatus=$obj->getIsInStock();
                } elseif ($type == 'bundle') {
                       $collection = $item->getProduct()->getTypeInstance(true)
                    ->getSelectionsCollection($item->getProduct()->getTypeInstance(true)
                    ->getOptionsIds($item->getProduct()), $item->getProduct());
                    $sku = $item->getData('sku');
                    foreach ($collection as $item) {
                        $childSku=  $item->getSku();
                        //update bid target
                        $sku = $item->getData('sku');
                        $selectProduct = $this->product->get($sku);
                        $bid = $selectProduct->getData('bid_target') + $item->getQtyOrdered();
                        $obj->setQty($obj->getQty() + $item->getQtyOrdered());
                        $selectProduct->setData('bid_target', $bid);
                        $obj->save();
                        $selectProduct->save();
                        //end update
                        $pos = strpos($sku, $childSku);
                        if ($pos) {
                            $count1++;
                            $id = $item->getId();
                            $obj = $this->stockStatus->get($id);
                            $backorderStatusBundle = $obj->getData('backorders');
                            $stockStatusBundle = $obj->getIsInStock();
                            if (($backorderStatusBundle == '3' && $stockStatusBundle = '1') || ($backorderStatusBundle == '4' && $stockStatusBundle == null )) {
                                $bundle = true;
                                $count2++;
                            } else {
                                $bundle = false;
                            }
                        }
                    }
                } else {
                    $backorderStatus = $obj->getData('backorders');
                    //update bid target
                    $sku = $item->getData('sku');
                    $selectProduct = $this->product->get($sku);
                    $bid = $selectProduct->getData('bid_target') + $item->getQtyOrdered();
                    $obj->setQty($obj->getQty() + $item->getQtyOrdered());
                    $selectProduct->setData('bid_target', $bid);
                    $obj->save();
                    $selectProduct->save();
                    //end update
                    $stockStatus = $obj->getIsInStock();
                }
                if (($backorderStatus== '3' && $stockStatus == '1') || ($backorderStatus == '4' && $stockStatus == null ) || ($bundle == true && $count1 == $count2)) {
                    $flag=true;
                } else {
                    $flag=false;
                    break;
                }
            }
            if ($flag) {

                $orderstatus = $orderCollection->getStatus();
                if ($orderstatus == 'processing') {
                    $orderCollection->setStatus("pre-order_processing");
                    $orderCollection->save();
                } else {
                    $orderCollection->setStatus("pre-order_pending");
                    $orderCollection->save();
                }
            }
        }
    }
}
