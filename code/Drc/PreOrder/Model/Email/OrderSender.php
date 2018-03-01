<?php

namespace Drc\PreOrder\Model\Email;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    
    public function send(Order $order, $forceSyncMode = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $check=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        $scopeConfig=$objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $active=$scopeConfig->getValue('drc_preorder_setting/preorderorderemail/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $flag=false;
        $storeId=$order->getData('store_id');
        if ($active==1 && $check==1) {
            $oid=$order->getId();
            $orderCollection=$objectManager->create('Magento\Sales\Model\Order')->load($oid);
            $items = $orderCollection->getAllVisibleItems();
            foreach ($items as $i) {
                 $type=$i->getData('product_type');
                if ($type=='configurable') {
                    $sku=$i->getData('sku');
                    $selectProduct=$objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->get($sku);
                    $id=$selectProduct->getId();
                } elseif ($type=='bundle') {
                    $collection = $i->getProduct()->getTypeInstance(true)
                    ->getSelectionsCollection($i->getProduct()->getTypeInstance(true)
                    ->getOptionsIds($i->getProduct()), $i->getProduct());
                    $sku=$i->getData('sku');
                    foreach ($collection as $item) {
                        $childSku=$item->getSku();
                        $pos = strpos($sku, $childSku);
                        if ($pos) {
                            $id=$item->getId();
                            $obj=$this->stockStatus->get($id);
                            $backorderStatusBundle=$obj->getData('backorders');
                            $stockStatusBundle=$obj->getIsInStock();
                            if (($backorderStatusBundle=='3' && $stockStatusBundle=='1')) {
                                break;
                            }
                        }
                    }
                } else {
                    $id=$i->getProductId();
                }
                $obj=$objectManager->create('Magento\CatalogInventory\Model\Stock\StockItemRepository')->get($id);
                        $backorderStatus=$obj->getData('backorders');
                        $stockStatus=$obj->getIsInStock();
                if (($backorderStatus=='3' && $stockStatus=='1') || ($backorderStatus=='4' && $stockStatus==null )) {
                    $flag=true;
                    $emailId=$orderCollection->getData('customer_email');
                    break;
                }
            }
        }
        if ($flag) {
            if (isset($emailId)) {
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $scopeConfig=$objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
                $customer_email_template =$scopeConfig->getValue('drc_preorder_setting/preorderorderemail/preordertemplate', $storeScope, $storeId);
                $senderEmail = $scopeConfig->getValue("sales_email/order/identity", $storeScope, $storeId);
                $email = $scopeConfig->getValue('trans_email/ident_'.$senderEmail.'/email', $storeScope, $storeId);
                $name  = $scopeConfig->getValue('trans_email/ident_'.$senderEmail.'/name', $storeScope, $storeId);
                $inlineTranslation=$objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
                $_escaper=$objectManager->create('Magento\Framework\Escaper');
                $_transportBuilder=$objectManager->create('Magento\Framework\Mail\Template\TransportBuilder');
                $messageManager=$objectManager->create('Magento\Framework\Message\ManagerInterface');
                $inlineTranslation->suspend();
                try {
                    $error = false;
                    $templateVars = [
                        'order' => $order,
                        'billing' => $order->getBillingAddress(),
                        'payment_html' => $this->getPaymentHtml($order),
                        'store' => $order->getStore(),
                        'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                        'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                
                    ];
                    $sender = [
                        'name' => $_escaper->escapeHtml($name),
                        'email' => $_escaper->escapeHtml($email),
                    ];
         
                    $transport = $_transportBuilder
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
                        $inlineTranslation->resume();
                } catch (\Exception $e) {
                    $inlineTranslation->resume();
                    $messageManager->addError(
                        __('We can\'t send email to customers for product stock status.')
                    );
                }
            }
        } else {
            $order->setSendEmail(true);

            if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
                if ($this->checkAndSend($order)) {
                    $order->setEmailSent(true);
                    $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                    return true;
                }
            }

            $this->orderResource->saveAttribute($order, 'send_email');
        }

        return false;
    }
}
