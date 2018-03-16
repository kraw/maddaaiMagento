<?php

namespace EasyNolo\BancaSellaPro\Cron;

use EasyNolo\BancaSellaPro\Model\Gestpay;
use Magento\Sales\Model\Order;

/**
 * Payment cron model
 *
 * Used for retrieving guaranteed payments status
 */
class CheckPendingReview {

    protected $salesOrderCollectionFactory = null;
    protected $helper = null;
    protected $s2s = null;
    protected $emulation = null;
    protected $storeManager;
    protected $scopeConfig;

    public function __construct(
        \Magento\Store\Model\App\Emulation $emulation,
        \EasyNolo\BancaSellaPro\Model\WS\WS2S $s2s,
        \EasyNolo\BancaSellaPro\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
    ) {
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
        $this->s2s = $s2s;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->salesOrderCollectionFactory = $collectionFactory;
    }

    public function getSalesOrderCollection(array $filters = [])
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $salesOrderCollection */
        $salesOrderCollection = $this->salesOrderCollectionFactory->create();

        foreach ($filters as $field => $condition) {

            $salesOrderCollection->addFieldToFilter($field, $condition);
        }

        return $salesOrderCollection->load();
    }

    public function execute() {

        $stores = $this->storeManager->getStores($withDefault = false);

        foreach ($stores as $store)
        {
            $this->emulation->startEnvironmentEmulation($store->getId());

            $statuses = array(Order::STATE_PAYMENT_REVIEW);
            if ($this->getConfigData('payment/easynolo_bancasellapro_riskified/enable', $store->getStoreId())) {
                $status = $this->getConfigData('payment/gestpaypro_riskified/submitted_order_status', $store->getStoreId());
                if ($status) {
                    $statuses[$status] = $status;
                }
            }
            if ($this->getConfigData('payment/gestpaypro_red/enable', $store->getStoreId())) {
                $status = $this->getConfigData('payment/gestpaypro_red/challenge_order_status', $store->getStoreId());
                if ($status) {
                    $statuses[$status] = $status;
                }
            }

            if (!empty($statuses)) {
                $collection = $this->getSalesOrderCollection([
                    'store_id' => $store->getId(),
                    'status' => $statuses
                ]);

                $collection->getSelect()->join(
                    ['payment' => $collection->getTable('sales_order_payment')],
                    'main_table.entity_id = payment.parent_id'
                )->where(
                    'payment.method = \''.Gestpay::CODE.'\''
                );

                foreach ($collection as $order) {

                    $params = array();

                    $params['shopLogin'] = $this->getConfigData('payment/easynolo_bancasellapro/merchant_id', $store->getStoreId());
                    $params['shopTransactionId'] = $order->getIncrementId();
                    $params['bankTransactionId'] = $order->getPayment()->getData('bankTransactionId');

                    $result = $this->s2s->executePaymentS2S($params);

                    switch ($result->getTransactionResult()) {

                        case 'OK':

                            if ($this->isRedEnabled()):
                                switch ($result->getRedResponseCode()) {
                                    case 'ACCEPT':
                                        $state = Order::STATE_PROCESSING;
                                        $message = __("Authorizing amount of %1 approved.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                        $order->setState($state);
                                        $order->addStatusHistoryComment($message, $this->getConfigData('order_status_ok_gestpay', $store->getStoreId()));
                                        $order->save();
                                        break;
                                    case 'DENY':
                                        $message = __("Authorizing amount of %1 declined by RED.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                        $order->cancel();

                                        $order->addStatusHistoryComment($message, $this->getConfigData('payment/gestpaypro_red/deny_order_status', $store->getStoreId()));
                                        $order->save();
                                        break;
                                }
                            elseif ($this->isRiskifiedEnabled()):
                                switch ($result->getRiskResponseCode()) {
                                    case 'approved':
                                        $state = Order::STATE_PROCESSING;
                                        $message = __("Authorizing amount of %1 approved.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                        $order->setState($state);
                                        $order->addStatusHistoryComment($message, $this->getConfigData('order_status_ok_gestpay', $store->getStoreId()));
                                        $order->save();
                                        break;
                                    case 'declined':
                                        $message = __("Authorizing amount of %1 declined by riskified.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                        $order->cancel();

                                        $order->addStatusHistoryComment($message, $this->getConfigData('payment/gestpaypro_red/declined_order_status', $store->getStoreId()));
                                        $order->save();
                                        break;
                                }
                            else:
                                $state = Order::STATE_PROCESSING;
                                $message = __("Authorizing amount of %1 approved.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                                $order->setState($state);
                                $order->addStatusHistoryComment($message, $this->getConfigData('order_status_ok_gestpay', $store->getStoreId()));
                                $order->save();
                                break;
                            endif;
                            break;

                        case 'KO':
                            $message = __("Authorizing amount of %1 failed.", $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal()));
                            $order->cancel();

                            $order->addStatusHistoryComment($message, $this->getConfigData('order_status_ko_gestpay', $store->getStoreId()));
                            $order->save();
                            break;
                    }
                }
            }

            // Stop store emulation process
            $this->emulation->stopEnvironmentEmulation();
        }

        return $this;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     */
    protected function getConfigData($field, $storeId)
    {
        if (strpos($field, '/') === false) {
            $path = 'payment/easynolo_bancasellapro/' . $field;
        } else {
            $path = $field;
        }
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}