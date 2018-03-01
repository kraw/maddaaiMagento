<?php

namespace Drc\PreOrder\Block;
use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;
use \Zend_Debug;
/**
 *
 */
class ValutationProductForm extends Template
{
  protected $customerSession;

  public function __construct(
    Template\Context $context,
    Session $session,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    )
  {
    $this->customerSession = $session;
    $this->request = $request;
    $this->customerRepository = $customerRepository;
    parent::__construct($context);
  }


  public function isLogged()
  {
    return $this->customerSession->isLoggedIn();
  }

  public function getCustomerName() {
    return $this->customerSession->getCustomerData()->getFirstName();
  }

  public function getCustomerSurname()
  {
    return $this->customerSession->getCustomerData()->getLastName();
  }

  public function getStoreName()
  {
    return $this->customerSession->getCustomer()->getStore()->getName();
  }

}
