<?php
namespace Drc\PreOrder\Model;
class PreorderPending extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        $this->_init('Drc\PreOrder\Model\ResourceModel\PreorderPending');
    }

    public function getId(){
      return $this->_getResource()->getId();
    }

    public function insertPreorderPending($id_customer, $email, $id_prodotto, $token, $quantita){
      return $this->_getResource()->insertPreorderPending($id_customer, $email, $id_prodotto, $token, $quantita);
    }

    public function getPreorderPending($token){
      return $this->_getResource()->getPreorderPending($token);
    }

    public function getAllCustomerByIdProduct($id){
      return $this->_getResource()->getAllCustomerByIdProduct($id);
    }
	
	public function getProductsByIdCustomer($customer_id){
      return $this->_getResource()->getProductsByIdCustomer($customer_id);	
	}
	


}

