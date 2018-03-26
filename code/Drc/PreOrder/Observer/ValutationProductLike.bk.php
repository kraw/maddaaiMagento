<?php
namespace Drc\PreOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class ValutationProductLike implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */

    const REDIRECT_URL = "//www.maddaai.it/login/";

    protected $_cart;
    protected $_context;
    protected $_messageManager;
    protected $_responseFactory;
    protected $_session;
    protected $_objectManager;
    
    protected $_likeFactory;
    protected $_likeResource;

    /**
     * ValutationProductLike constructor.
     * @param \Drc\PreOrder\Model\LikeFactory $likeFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Drc\PreOrder\Model\ResourceModel\Like $likeResource
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\App\ObjectManager $objectManager
     */
    public function __construct(
        \Drc\PreOrder\Model\LikeFactory $likeFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Drc\PreOrder\Model\ResourceModel\Like $likeResource,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\ObjectManager $objectManager
    )
    {
      $this->_likeFactory = $likeFactory;
	  $this->_context = $context;
	  $this->_session = $session;
      $this->_likeResource = $likeResource;
      $this->_responseFactory = $responseFactory;
      $this->_messageManager = $messageManager;
      $this->_cart = $cart;
      $this->_objectManager = $objectManager;

    }

    /**
     *
     * Add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if(!$this->isUserLogged()){
            return $this->_responseFactory->create()->setRedirect(self::REDIRECT_URL)->sendResponse();
        }




      $scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
      $customerData = $this->_session->getCustomerData();

		
      $id_customer = $customerData->getId();
	  $product = $observer->getRequest()->getParam("product", null);
	  $product_id = $product;
	  $product = $this->_objectManager->create("Magento\Catalog\Model\Product")->load($product);
      $prezzo = round($product->getPrice(),2);

      $store_id = $this->_session->getCustomer()->getStoreId();
		

        if($this->isValutationProduct()) {
          //checking if user is logged in
          if($this->_session->isLoggedIn()) {
            //checking if user already added like for this product

            $item = $this->_likeResource->getLikeByProductIdCustomerId($product_id, $customer_id);
            if(!$item) {
              //getting previous likes amount
              $likes = intval($product->getData('valutation_product_likes'));
              //updating likes amount
              $product->addAttributeUpdate("valutation_product_likes", ($likes+1), $product->getStoreId());
              //saving like into database
              $like = $this->_likeFactory->create();
              $like->setProductId($product_id);
              $like->setCustomerId($customer_id);
              $like->setStoreId($store_id);
              $like->save();
	      //retrieving likes limit
	      $thresold = intval($product->getData("valutation_product_thresold"));
	      if(($likes+1) == $thresold) {
		//custom event to signal reached thresold for valutation product 
		$event_data_array  =  ['product_id' => $product->getId()];
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$eventManager = $this->_objectManager->create('\Magento\Framework\Event\Manager');
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
        return $this;
      }
	



    /**
     * Check if customers is autenticate
     * @return bool
     */
    public function isUserLogged(){
        return $this->_session->isLoggedIn();
    }

    /**
     * Check if a product is an instance of Valutation Product
     * @param $product
     * @return bool
     */
    public function isValutationProduct($product){
        return $product->getTypeId() === "valutation_product";
    }
   

}

