<?php

namespace Drc\PreOrder\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Framework\DataObject\Factory as ObjectFactory;

/**
 * Interface StockStateProvider
 */
class StockStateProvider extends \Magento\CatalogInventory\Model\StockStateProvider
{
    public function checkQuoteItemQty(StockItemInterface $stockItem, $qty, $summaryQty, $origQty = 0)
    {
        $result = $this->objectFactory->create();
        $result->setHasError(false);

        $qty = $this->getNumber($qty);

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($stockItem->getIsQtyDecimal());
        if (!$stockItem->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);
            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);
            $qty = $this->getNumber($qty);
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($stockItem->getMinSaleQty() && $qty < $stockItem->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The fewest you may purchase is %1.', $stockItem->getMinSaleQty() * 1))
                ->setErrorCode('qty_min')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The most you may purchase is %1.', $stockItem->getMaxSaleQty() * 1))
                ->setErrorCode('qty_max')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($stockItem, $qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$stockItem->getManageStock()) {
            return $result;
        }
         $id=$stockItem->getId();
         $data=$this->check($id);
        if (!$stockItem->getIsInStock() && !$data[0]=='4' && $data[1]==false && $data[2]==0) {
            $result->setHasError(true)
                ->setMessage(__('This product is out of stock.'))
                ->setQuoteMessage(__('Some of the products are out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }
        if ((!$this->checkQty($stockItem, $summaryQty) || !$this->checkQty($stockItem, $qty)) && (($data[3] != 1))) {
            $message = __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName());
            $result->setHasError(true)->setMessage($message)->setQuoteMessage($message)->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if ($stockItem->getQty() - $summaryQty < 0) {
                if ($stockItem->getProductName()) {
                    if ($stockItem->getIsChildItem()) {
                        $backOrderQty = $stockItem->getQty() > 0 ? ($summaryQty - $stockItem->getQty()) * 1 : $qty * 1;
                        if ($backOrderQty > $qty) {
                            $backOrderQty = $qty;
                        }

                        $result->setItemBackorders($backOrderQty);
                    } else {
                        $orderedItems = (int)$stockItem->getOrderedItems();

                        // Available item qty in stock excluding item qty in other quotes
                        $qtyAvailable = ($stockItem->getQty() - ($summaryQty - $qty)) * 1;
                        if ($qtyAvailable > 0) {
                            $backOrderQty = $qty * 1 - $qtyAvailable;
                        } else {
                            $backOrderQty = $qty * 1;
                        }

                        if ($backOrderQty > 0) {
                            $result->setItemBackorders($backOrderQty);
                        }
                        $stockItem->setOrderedItems($orderedItems + $qty);
                    }

                    if ($stockItem->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$stockItem->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, so we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        }
                    } elseif ($stockItem->getShowDefaultNotificationMessage()) {
                        $result->setMessage(
                            __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName())
                        );
                    }
                }
            } else {
                if (!$stockItem->getIsChildItem()) {
                    $stockItem->setOrderedItems($qty + (int)$stockItem->getOrderedItems());
                }
            }
        }
        return $result;
    }
    public function check($id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
           $groupProduct=$objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped');
            $gid=$groupProduct->getParentIdsByChild($id);
            $flag=false;
            $count=0;
            $qtyBelow=false;
            $active=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        if (isset($gid[0])) {
            $pid=$gid[0];
            $_product = $objectManager->get('Magento\Catalog\Model\Product')->load($pid);
            $type=$_product->getTypeId();
            if ($type == 'grouped') {
                $associatedProducts =$_product->getTypeInstance()->getAssociatedProducts($_product);
                foreach ($associatedProducts as $_item) {
                    $cid = $_item->getEntityId();
                     $obj=$objectManager->create('Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($cid);
                    $backorderStatus=$obj->getData('backorders');
                    if (!$_item->IsSalable()) {
                        if ($backorderStatus=='3') {
                            $qtyBelow=true;
                        }
                        if ($backorderStatus=='4') {
                             $flag=true;
                        } else {
                            $flag=false;
                        }
                    }
                }
            }
        }
           $obj=$objectManager->create('Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($id);
         $backorderStatus=$obj->getData('backorders');
        if ($backorderStatus=='3') {
            $qtyBelow=true;
        }
        $data[0]=$backorderStatus;
        $data[1]=$flag;
        $data[2]=$active;
        $data[3]=$qtyBelow;
        return $data;
    }
}
