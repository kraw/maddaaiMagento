<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Drc\PreOrder\Model\Email;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as InvoiceResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class InvoiceSender
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    public function send(Invoice $invoice, $forceSyncMode = false)
    {
        $invoice->setSendEmail(true);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $check=$objectManager->create('Drc\PreOrder\Helper\Check')->checkEnable();
        $order = $invoice->getOrder();
        $scopeConfig=$objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $active=$scopeConfig->getValue('drc_preorder_setting/preorderinvoiceemail/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
                $customer_email_template =$scopeConfig->getValue('drc_preorder_setting/preorderinvoiceemail/preordertemplate', $storeScope, $storeId);
                $senderEmail = $scopeConfig->getValue("sales_email/invoice/identity", $storeScope, $storeId);
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
                        'invoice' => $invoice,
                        'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
                        'billing' => $order->getBillingAddress(),
                        'payment_html' => $this->getPaymentHtml($order),
                        'store' => $order->getStore(),
                        'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                        'formattedBillingAddress' => $this->getFormattedBillingAddress($order)
                
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
            if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
                $order = $invoice->getOrder();

                $transport = [
                'order' => $order,
                'invoice' => $invoice,
                'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order)
                ];

                $this->eventManager->dispatch(
                    'email_invoice_set_template_vars_before',
                    ['sender' => $this, 'transport' => $transport]
                );

                $this->templateContainer->setTemplateVars($transport);

                if ($this->checkAndSend($order)) {
                        $invoice->setEmailSent(true);
                        $this->invoiceResource->saveAttribute($invoice, ['send_email', 'email_sent']);
                        return true;
                }
            }

            $this->invoiceResource->saveAttribute($invoice, 'send_email');
        }

        return false;
    }
}
