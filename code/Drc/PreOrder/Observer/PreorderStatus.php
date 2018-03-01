<?php

namespace Drc\PreOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreorderStatus implements ObserverInterface
{
    protected $scopeConfig;
    protected $logger;
    protected $orderFactory;
    protected $order;
    protected $stockStatus;
    protected $configProduct;
    protected $product;
    protected $inlineTranslation;
    protected $_transportBuilder;
    protected $messageManager;
    protected $_escaper;
    protected $stockRegistry;
    protected $resource;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\ResourceModel\Order\Item\Collection $orderFactory,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockStatus,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configProduct,
        \Magento\Catalog\Api\ProductRepositoryInterface $product,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Escaper $escaper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $StockRegistryInterface,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_escaper = $escaper;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->order = $order;
        $this->stockStatus = $stockStatus;
        $this->configProduct = $configProduct;
         $this->product=$product;
         $this->inlineTranslation = $inlineTranslation;
         $this->_transportBuilder = $transportBuilder;
          $this->messageManager = $messageManager;
          $this->stockRegistry=$StockRegistryInterface;
          $this->resource=$resource;
    }
    public function execute(Observer $observer)
    {
        $observer=$observer;
        $active=$this->scopeConfig->getValue('drc_preorder_setting/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($active==1) {
            $product_id = $observer->getProduct()->getId();
            $productSku= $observer->getProduct()->getSku();
            $pname=$observer->getProduct()->getName();
            $productStock=$observer->getProduct();
            $storeId=$productStock->getData('store_id');
            $backorderStatus=$productStock->getStockData('backorders');
            $stockStatus=$productStock->getStockData('is_in_stock');
            $product = $this->configProduct->getParentIdsByChild($product_id);

            if (isset($product[0])) {
                $product_id=$product[0];
            }
            $tbl = $this->resource->getTableName('catalog_product_bundle_selection');
            $select = $this->resource->getConnection()->select()->from($tbl, ['parent_product_id'])
            ->where(
                'product_id = ?',
                (int)$product_id
            );
            $parent=$this->resource->getConnection()->fetchCol($select);
            if (isset($parent[0])) {
                $product_id=$parent[0];
            }
            $productStockData = $this->stockRegistry->getStockItem($product_id);
            if ($productStockData) {
                $backorderStatusOld=$productStockData->getData('backorders');
                $stockStatusOld=$productStockData->getData('is_in_stock');

            }
            $orderData=$this->orderFactory->addAttributeToFilter('product_id', ['in' => $product_id])->load();

            $this->checkProduct($orderData, $product_id, $productSku, $backorderStatus, $stockStatus, $backorderStatusOld, $stockStatusOld, $pname, $storeId, $observer);
        }
    }
    public function checkProduct($orderData, $product_id, $productSku, $backorderStatus, $stockStatus, $backorderStatusOld, $stockStatusOld, $pname, $storeId, $observer)
    {

        foreach ($orderData as $order) {
            $oid=$order->getOrderId();
            $orderCollection=$this->order->load($oid);
            $items = $orderCollection->getAllVisibleItems();
            $orderStatus=$orderCollection->getStatus();
            $beforeSaveStatus=$orderStatus;
            foreach ($items as $i) {
                $flag=false;
                $type=$i->getData('product_type');
                $id=$i->getData('product_id');
                if ($id==$product_id) {
                    if ($type=='configurable') {
                        $sku=$i->getData('sku');
                        if ($sku==$productSku) {
                            if (($backorderStatus=='3' && $stockStatus=='1') || ($backorderStatus=='4' && $stockStatus==null )) {
                                $flag=true;
                            }
                        }
                    } elseif ($type=='bundle') {
                        $count1=0;
                        $count2=0;
                        $bundle=false;
                        $collection = $i->getProduct()->getTypeInstance(true)->getSelectionsCollection($i->getProduct()->getTypeInstance(true)->getOptionsIds($i->getProduct()), $i->getProduct());
                        $sku=$i->getData('sku');
                        foreach ($collection as $item) {
                            $childSku=$item->getSku();
                            $pos = strpos($sku, $childSku);
                            if ($pos) {
                                $count1++;
                                $id = $item->getId();
                                $productStockData = $this->stockRegistry->getStockItem($id);
                                $backorderStatusBundle=$productStockData->getData('backorders');
                                $stockStatusBundle=$productStockData->getData('is_in_stock');
                                if ($id != $observer->getProduct()->getId()) {
                                    if (($backorderStatusBundle=='3' && $stockStatusBundle=='1') || ($backorderStatusBundle=='4' && $stockStatusBundle==null )) {
                                        $bundle=true;
                                        $count2++;
                                    } else {
                                        $bundle=false;
                                    }
                                } else {
                                    if (($backorderStatus=='3' && $stockStatus == '1') || ($backorderStatus=='4' && $stockStatus==null)) {
                                          $bundle=true;
                                           $count2++;
                                    }
                                }
                            }
                        }
                        if ($bundle==true && $count2==$count1) {
                            $flag=true;
                        }
                    } else {
                        if (($backorderStatus=='3' && $stockStatus=='1') || ($backorderStatus=='4' && $stockStatus==null )) {
                              $flag=true;
                        }
                    }
                    if ((($backorderStatus=='0' && $stockStatus=='1') && ($backorderStatusOld=='3' && $stockStatusOld=='1')) || (($backorderStatusOld=='4' && $stockStatusOld==null) && ($backorderStatus=='0' && $stockStatus=='1') )) {
                        $emailId=$orderCollection->getData('customer_email');
                        $cname=$orderCollection->getData('customer_firstname');
                    }
                }
            }
            $this->checkFlag($flag, $orderStatus, $orderCollection, $observer);
            if (isset($emailId)) {
                $this->sendEmail($cname, $pname, $emailId, $storeId);
            }
        }
    }
    public function checkFlag($flag, $orderStatus, $orderCollection, $observer)
    {
        if ($flag) {
            if ($orderStatus=='pending') {
                $orderCollection->setStatus('pre-order_pending');
                $orderCollection->setState('pre-order_pending', true);
                $orderCollection->save();
            } elseif ($orderStatus=='processing') {
                $orderCollection->setStatus('pre-order_processing');
                $orderCollection->setState('pre-order_processing', true);
                $orderCollection->save();
            }
        } else {
            if ($orderStatus=='pre-order_pending') {
                $orderCollection->setStatus('pending');
                $orderCollection->setState('new', true);
                $orderCollection->save();
            } elseif ($orderStatus=='pre-order_processing') {
                $orderCollection->setStatus('processing');
                $orderCollection->setState('processing', true);
                $orderCollection->save();
            }
        }
    }
    public function sendEmail($cname, $pname, $emailId, $storeId)
    {
     
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $customer_email_template =$this->scopeConfig->getValue('drc_preorder_setting/preorderemail/preordertemplate', $storeScope, $storeId);
        $senderEmail = $this->scopeConfig->getValue("drc_preorder_setting/preorderemail/preorderidentity", $storeScope, $storeId);
        $email = $this->scopeConfig->getValue('trans_email/ident_'.$senderEmail.'/email', $storeScope, $storeId);
        $name  = $this->scopeConfig->getValue('trans_email/ident_'.$senderEmail.'/name', $storeScope, $storeId);
        if ($this->scopeConfig->getValue('drc_preorder_setting/preorderemail/active', $storeScope, $storeId) ==1) {
            $this->inlineTranslation->suspend();
            try {
                $error = false;
                $templateVars = [
                'cname' => $cname,
                'pname' => $pname];
                 $sender = [
                    'name' => $this->_escaper->escapeHtml($name),
                    'email' => $this->_escaper->escapeHtml($email),
                  ];
                 $transport = $this->_transportBuilder
                                ->setTemplateIdentifier($customer_email_template) // this code we have mentioned in the email_templates.xml
                                ->setTemplateOptions(
                                    [
                                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                    ]
                                )
                                ->setTemplateVars($templateVars)
                                ->setFrom($sender)
                                ->addTo($emailId)
                                ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->inlineTranslation->resume();
                $this->messageManager->addError(
                    __('We can\'t send email to customers for product stock status.')
                );
            }
        }
    }
}
