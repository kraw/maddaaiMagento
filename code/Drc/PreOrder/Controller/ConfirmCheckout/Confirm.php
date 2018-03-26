<?php
namespace Drc\PreOrder\Controller\ConfirmCheckout;

use Drc\PreOrder\Model\PreorderPendingFactory;

use Zend_Debug;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Confirm extends \Magento\Framework\App\Action\Action
{
	
	
	    private $cookieMetadataManager;
	    private $cookieMetadataFactory;
	    private $scopeConfig;



	
  public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $session,
    \Magento\Framework\App\ResponseFactory $responseFactory,
    \Magento\Catalog\Model\Product $product,
    \Magento\Checkout\Model\Cart $cart,
    \Magento\Framework\View\Result\PageFactory $pageFactory,
    \Drc\PreOrder\Model\PreorderPending $preModel,
    \Magento\Catalog\Model\ProductFactory $productLoader,
	          
	  	AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect

)
  {
    $this->_context = $context;
    $this->_session = $session;
    $this->_responseFactory = $responseFactory;
    $this->cart = $cart;
    $this->product = $product;
    $this->_pageFactory = $pageFactory;
    $this->_preModel = $preModel;
    $this->_productLoader = $productLoader;
	  
	    $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;



    return parent::__construct($context);
  }

  public function deleteQuoteItems(){
  $checkoutSession = $this->getCheckoutSession();
  //$quote = $this->$checkoutSession->getQuote();


      $checkoutSession->clearQuote()->clearStorage();
      $checkoutSession->clearQuote();
      $checkoutSession->clearStorage();
      $checkoutSession->clearHelperData();
      $checkoutSession->resetCheckout();
      $checkoutSession->restoreQuote();

      $checkoutSession->start();

      $checkoutSession->setMinicartNeedsRefresh(true);
	  
	     $quote_Id= $this->cart->getQuote()->getId();

  //print_r($checkoutSession->getQuote()->getData());

  $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all teh items in session
  foreach ($allItems as $item) {
      $itemId = $item->getItemId();//item id of particular item
      $quoteItem=$this->getItemModel()->load($itemId);//load particular item which you want to delete by his item id
      $quoteItem->delete();
  }
  }

public function getCheckoutSession(){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();//instance of object manager
    $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');//checkout session
    return $checkoutSession;
}

public function getItemModel(){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();//instance of object manager
    $itemModel = $objectManager->create('Magento\Quote\Model\Quote\Item');//Quote item model to load quote item
    return $itemModel;
}

  public function execute()
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();


    //arrivo dall'email
    if(isset($_GET['t'])){
      $token = $_GET['t'];

      $preorder = $this->_preModel->getPreorderPending($token);

      $prodotti= array();
      $quantita = array();
      $email = null;
      $id_customer = null;


      foreach ($preorder as $pre) {
        $prodotti[] = $pre['id_prodotto'];
        $quantita[] = $pre['quantita'];
        $email = $pre['email'];
        $id_customer = $pre['id_customer'];
		  
		$attivo = $pre['acquistabile'];
        $deleted = $pre['deleted'];

     //   Zend_Debug::dump($attivo);
      //  Zend_Debug::dump($deleted);
      //  Zend_Debug::dump($token);

        if($deleted == "0"){

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
          $connection = $resource->getConnection();
          $tableName = $resource->getTableName('drc_preorder_pending');

          $sql = "UPDATE " . $tableName . " SET acquistabile = 1 WHERE token = '".$token."'";
          $connection->query($sql);
			
          setcookie("$id_customer", "$token", time() + (86400 * 30), "/"); // 86400 = 1 day


        }else{
          return $this->resultRedirectFactory->create()->setUrl("/preorder/confirmcheckout/annull");
        }
      }
		
		
						
			   //login
					 $logged = $this->_session->isLoggedIn();
					// Zend_Debug::dump($_SESSION);exit;
		
					if(!$logged || !isset($_SESSION['customer_base']['customer_id'])){
						Zend_Debug::dump("not logged");
						$this->_session->regenerateId();
						$this->_session->loginById($id_customer);

						
					//	return $this->resultRedirectFactory->create()->setUrl("/index.php/preorder/confirmcheckout/confirm?t=$token");
						return $this->resultRedirectFactory->create()->setUrl("/index.php/checkout/cart?test=1");
					}else{
						$this->_session->logout();
						$this->_session->loginById($id_customer);
      					$this->_session->regenerateId();

					}
		
							
	  

       //flush del carrello
       $this->cart->truncate();
		
	 //  $this->deleteQuoteItems();
	  // $this->cart->save();


      //ripopolo il carrello
      for($i=0;$i<count($prodotti); $i++){
        $params=array();
        $params['qty'] = $quantita[$i];
        $pId = $prodotti[$i];

        $_product = $this->product->load($pId);

           if ($_product) {
               $this->cart->addProduct($_product, $params);
               $this->cart->save();
               $this->messageManager->addSuccess(__('Ora finalmente puoi acquistare il prodotto.'));
           }

      }

			   $this->cart->save();


      return $this->resultRedirectFactory->create()->setUrl("/checkout/cart/index");
    }else{
      //arrivo dalla fine del preorderdine
      return $this->_pageFactory->create();
    }
  }
	
	    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }
	    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }
	    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }
}
