<?php

namespace Drc\PreOrder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreorderInvoice implements ObserverInterface
{
    
    protected $logger;
    protected $order;
    protected $stockStatus;
    protected $scopeConfig;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order $order,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockStatus,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->order = $order;
        $this->scopeConfig=$scopeConfig;
        $this->stockStatus = $stockStatus;
    }
    public function execute(Observer $observer)
    {
      
        $active=$this->scopeConfig->getValue('drc_preorder_setting/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($active==1) {
            $invoice = $observer->getInvoice();
            $order=$invoice->getOrder();
            $orderStatus=$order->getStatus();
            if ($orderStatus=='pre-order_pending') {
                 $order->setStatus('pre-order_processing');
                 $order->setState('pre-order_processing', true);
                 $order->save();
            }
        }
    }
}
