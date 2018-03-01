<?php
namespace Drc\PreOrder\Controller\Sendmail;

use Drc\PreOrder\Model\PreorderPendingFactory;
use Magento\Framework\UrlInterface;
use Zend_Debug;

class Utenti extends \Magento\Framework\App\Action\Action
{
  public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $session,
    \Magento\Framework\App\ResponseFactory $responseFactory,
    \Magento\Catalog\Model\Product $product,
    \Magento\Checkout\Model\Cart $cart,
    \Magento\Framework\View\Result\PageFactory $pageFactory,
    \Drc\PreOrder\Model\PreorderPending $preModel,
    \Magento\Customer\Api\CustomerRepositoryInterface\Proxy $customerRepository,
    \Magento\Catalog\Model\ProductFactory $productLoader,
	UrlInterface $urlBuilder

)
  {
    $this->_context = $context;
    $this->_session = $session;
    $this->_responseFactory = $responseFactory;
    $this->cart = $cart;
    $this->product = $product;
    $this->_pageFactory = $pageFactory;
    $this->_preModel = $preModel;
    $this->customerRepository = $customerRepository;
    $this->_productLoader = $productLoader;
	$this->urlBuilder = $urlBuilder;


    return parent::__construct($context);
  }

  public function execute()
  {

	$id_prodotto = $_POST['id_product'];
	$utenti_gda = $this->_preModel->getAllCustomerByIdProduct($id_prodotto);    

	$output = array();

	foreach($utenti_gda as $utente){

		$email = $utente['email'];
		$id_customer = $utente['id_customer'];
	
		$customer = $this->customerRepository->getById(intval($id_customer));
		$nome = $customer->getFirstname();
		$cognome = $customer->getLastname();
		$url = $this->urlBuilder->getUrl('customer/index/edit', ['id' => 1]);
		
		$output[$id_customer] = array(
			'nome' => $nome,
			'cognome' => $cognome,
			'email' => $email,
			'url' => $url
		);
	}


	echo json_encode(array('data' => $output));
	exit;
  }








}

