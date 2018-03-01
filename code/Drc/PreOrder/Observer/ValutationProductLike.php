<?php
namespace Drc\PreOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class ValutationProductLike implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    protected $_responseFactory;
    protected $likeFactory;
    protected $likeResource;
    protected $customerSession;


    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Drc\PreOrder\Model\LikeFactory $likeFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Drc\PreOrder\Model\ResourceModel\Like $likeResource,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Checkout\Model\Cart $cart

    )
    {
      $this->likeFactory = $likeFactory;
	  $this->_context = $context;
	  $this->customerSession = $session;
      $this->likeResource = $likeResource;
      $this->_responseFactory = $responseFactory;
      $this->_messageManager = $messageManager;
      $this->cart = $cart;

    }

    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {


      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
      $customerData = $this->customerSession->getCustomerData();
		
		//l'utente non è loggato
	  if(!$customerData){
			$urlBuilder = $this->_context->getUrlBuilder();
			$CustomRedirectionUrl = $urlBuilder->getUrl('customer/account/login');
			$this->_responseFactory->create()->setRedirect("http://www.maddaai.it/login/")->sendResponse();
			exit;
	  };
		
      $id_customer = $customerData->getId();
	  $product = $observer->getRequest()->getParam("product", null);
	  $product_id = $product;
	  $product = $objectManager->create("Magento\Catalog\Model\Product")->load($product);
      $prezzo = round($product->getPrice(),2);
		
		
		
        //checking whether considering a valutation product
	$isValutationProduct = false;
	//retrieving product type
	$type = $product->getTypeId();
	if(is_array($type)) {
        	foreach($type as $it) {
                	if(strcmp($it, "valutation_product") == 0)
                        	$isValutationProduct = true;
        	}
	}
	else {
        	if(strcmp($type, "valutation_product") == 0)
                	$isValutationProduct = true;
	}

        if($isValutationProduct) {
          //checking if user is logged in
          if($this->customerSession->isLoggedIn()) {
            //checking if user already added like for this product
            $customer_id = $this->customerSession->getCustomerId();
            $store_id = $this->customerSession->getCustomer()->getStoreId();
            $item = $this->likeResource->getLikeByProductIdCustomerId($product_id, $customer_id);
            if(!$item) {
              //getting previous likes amount
              $likes = intval($product->getData('valutation_product_likes'));
              //updating likes amount
              $product->addAttributeUpdate("valutation_product_likes", ($likes+1), $product->getStoreId());
              //saving like into database
              $like = $this->likeFactory->create();
              $like->setProductId($product_id);
              $like->setCustomerId($customer_id);
              $like->setStoreId($store_id);
              $like->save();
	      //retrieving likes limit
	      $thresold = intval($product->getData("valutation_product_thresold"));
	      if(($likes+1) == $thresold) {
		//custom event to signal reached thresold for valutation product 
		$event_data_array  =  ['product_id' => $product->getId()];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$eventManager = $objectManager->create('\Magento\Framework\Event\Manager');
		$eventManager->dispatch('drc_preorder_valutation_product_thresold', $event_data_array);	
	      }	
              //checking if ajax request
              $om = \Magento\Framework\App\ObjectManager::getInstance();
               $request = $om->get('Magento\Framework\App\RequestInterface');

               //setting product parama to null to avoid being added to the cart
               $this->_messageManager->addSuccess("Like aggiunto con successo");
               $observer->getRequest()->setParam('product', false);
                // return $this;
              //if receiving from product category list
                              /* die use for stop excaution */
            }
            else {
              //returning message pointing out user is not logged in
              $this->_messageManager->addError("Hai gia' aggiunto il like per questo prodotto");
              $observer->getRequest()->setParam('product', false);
            }

          }
          else {
            //returning message pointing out user is not logged in
            $this->_messageManager->addError("Non puoi effettuare l'operazione se non sei loggato");
            $observer->getRequest()->setParam('product', false);
          }
		$redirectUrl = $product->getProductUrl();
         // $this->_messageManager->getMessages(true);

        $this->_responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
		exit();

        }
		
		
		
	  $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
      $connection = $resource->getConnection();
      $tableName = $resource->getTableName('drc_credit_limit');
      $urlBuilder = $this->_context->getUrlBuilder();
		
	  $products = $this->cart->getItems();
			
     //CONTROLLO SE L'UTENTE HA GIA' ADERITO AL GDA
	 $customer_gda = $this->getCustomerGDA($id_customer,$connection, "drc_preorder_pending");
		foreach($customer_gda as $gda){
			$id_gda = $gda['id_prodotto'];
			if($id_gda == $product_id){
		      $CustomRedirectionUrl = $urlBuilder->getUrl('preorder/confirmcheckout/annull?er=5');
              $this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
              exit;
			}
		}

	 //END CONTROLLO DOPPIA ADESIONE
		
	  
	 		
	  $sum = $prezzo;
		
		foreach ($products as $p) {
			 $id = $p->getProductId();
	         $qty = $p->getQty();
			 $price =  round($p->getPrice(),2) * $qty;
		     $sum+=$price;

				\Zend_Debug::dump("ID = $id - QTY = $qty PREZZO $price" );	
		}


			//CREDIT LIMIT
		$sectionId = "drc_preorder_setting";
		$groupId = "creditlimit";
		$fieldId = "creditlimit";
		$configPath = $sectionId.'/'.$groupId.'/'.$fieldId;
		$value =  $scopeConfig->getValue(
			$configPath,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);

		$credit_limit = intval($value);
		
		\Zend_Debug::dump("credit limit =".$credit_limit); 
		
	
       if($prezzo > $credit_limit){
              $CustomRedirectionUrl = $urlBuilder->getUrl('preorder/confirmcheckout/annull?er=4');
              $this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
              exit;
          }

		
		$t=date('d-m-Y');
        $mese = intval(date("m",strtotime($t)));
			
			
        $current_credit = floatval($this->existsCustomer(intval($id_customer), $mese, $connection, $tableName));
		
		if(($sum + $current_credit) > $credit_limit){
			$CustomRedirectionUrl = $urlBuilder->getUrl('preorder/confirmcheckout/annull?er=3');
			$this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
			exit;	
		}
		
	//	\Zend_Debug::dump("customer credit =".$current_credit." SOMMA CARRELLO E NUOVI PRODOTTI $sum"); 
		
		
		//END CREDIT LIMIT
		
		
		
		
        return $this;
      }
	
	
	
			  //controlla se esiste il cliente nella tabella, se si ritorna il credit
      private function existsCustomer($customer_id, $mese, $connection, $tableName){
        $sql = "SELECT credit, data FROM " . $tableName . " WHERE id_customer = $customer_id LIMIT 1";
        $result = $connection->fetchAll($sql);
        if(isset($result[0])){

          if($mese != intval(date("m",strtotime($result[0]['data'])))){
            $this->resetCredit($customer_id,$connection, $tableName);
          }
          return $result[0]['credit'];
        }else{ return 0; }
      }

      private function addCredit($customer_id, $credit, $connection, $tableName){
        $data = date('Y/m/d');
        $sql = "UPDATE " . $tableName . " SET credit = $credit, data = '".$data."' WHERE id_customer = $customer_id";
        $connection->query($sql);
      }

      private function resetCredit($customer_id,$connection, $tableName){
        $data = date('Y/m/d');
        $sql = "UPDATE " . $tableName . " SET credit = 0, data = '".$data."' WHERE id_customer = $customer_id";
        $connection->query($sql);
      }

      private function addCustomer($customer_id, $credit, $connection, $tableName){
        $data = date('Y/m/d');
        $sql = "INSERT INTO " . $tableName . " (id_customer, credit, data) VALUES ( $customer_id, $credit, '".$data."')";
        $connection->query($sql);
      }
	
	
	  private function getCustomerGDA($customer_id,$connection, $tableName){
        $sql = "SELECT id_prodotto FROM " . $tableName . " WHERE id_customer = ".intval($customer_id);
		return $connection->fetchAll($sql);
	  }


   

}

