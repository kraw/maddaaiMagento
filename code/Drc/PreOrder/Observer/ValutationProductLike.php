<?php

namespace Drc\PreOrder\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;

class ValutationProductLike implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */

    const REDIRECT_URL = "//www.maddaai.it/login/";
    const ALREADY_LIKE_IT = "Hai gia' aggiunto il like per questo prodotto";
    const LIKE_ADDED = "Like aggiunto con successo";

    protected $_cart;
    protected $_context;
    protected $_messageManager;
    protected $_responseFactory;
    protected $_session;
    protected $_objectManager;
    protected $_productLoader;

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
        \Magento\Checkout\Model\Cart $cart
    )
    {
        $this->_likeFactory = $likeFactory;
        $this->_context = $context;
        $this->_session = $session;
        $this->_likeResource = $likeResource;
        $this->_responseFactory = $responseFactory;
        $this->_messageManager = $messageManager;
        $this->_cart = $cart;
        $this->_objectManager = ObjectManager::getInstance();

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

        if (!$this->isUserLogged()) {
            $this->_responseFactory->create()->setRedirect(self::REDIRECT_URL)->sendResponse();
            return $this;
        }

        $customer_id = $this->_session->getCustomerData()->getId();
        $store_id = $this->_session->getCustomerData()->getStoreId();

        $product = $observer->getRequest()->getParam("product", null);

        $product = $this->_objectManager->create("Magento\Catalog\Model\Product")->load($product);

        if(!$this->isValutationProduct($product)){
            $this->_messageManager->addError(self::ALREADY_LIKE_IT);
            $observer->getRequest()->setParam('product', false);
            return $this;
        }

        $_item = $this->_likeResource->getLikeByProductIdCustomerId($product->getId(), $customer_id);

        if (!$_item) {

            //getting previous likes amount
            $likes = intval($product->getData('valutation_product_likes'));

            //updating likes amount
            $product->addAttributeUpdate("valutation_product_likes", ($likes + 1), $product->getStoreId());

            $like = $this->_likeFactory->create();
            $like->setProductId($product->getId());
            $like->setCustomerId($customer_id);
            $like->setStoreId($store_id);
            $like->save();

            //retrieving likes limit
            $thresold = intval($product->getData("valutation_product_thresold"));
            if (($likes + 1) == $thresold) {
                $event_data_array = ['product_id' => $product->getId()];
                $eventManager = $this->_objectManager->create('\Magento\Framework\Event\Manager');
                $eventManager->dispatch('drc_preorder_valutation_product_thresold', $event_data_array);
            }
            $this->_messageManager->addSuccess(self::LIKE_ADDED);
            $observer->getRequest()->setParam('product', false);
        } else {
            $this->_messageManager->addError(self::ALREADY_LIKE_IT);
            $observer->getRequest()->setParam('product', false);
        }


        return $this;
    }


    /**
     * Check if customers is autenticate
     * @return bool
     */
    public function isUserLogged()
    {
        return $this->_session->isLoggedIn();
    }

    /**
     * Check if a product is an instance of Valutation Product
     * @param $product
     * @return bool
     */
    public function isValutationProduct($product)
    {
        return $product->getTypeId() === "valutation_product";
    }


}

