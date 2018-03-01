<?php
namespace Drc\PreOrder\Model\ResourceModel;


class PreorderPending extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        $this->_date = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('drc_preorder_pending', 'id');
    }

    /**
     * Retrieves Post Name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getId()
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getMainTable(), 'id');
        return $conn->fetchAll($select);
    }

    public function insertPreorderPending($id_customer, $email, $id_prodotto, $token, $quantita){
        $conn = $this->getConnection();
        $conn->insert($this->getMainTable(),array(
          'id_customer' => $id_customer,
          'email' => $email,
          'token' => $token,
          'id_prodotto' => $id_prodotto,
          'quantita' => $quantita
        ));
    }


    public function getPreorderPending($token){
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getMainTable())
            ->where('token = ? ', $token)
            ->limit(1);
        return $conn->fetchAll($select);
    }
	


    public function getAllCustomerByIdProduct($id){
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getMainTable())
            ->where('id_prodotto = ? AND deleted = 0', $id);
        return $conn->fetchAll($select);
    }
	
   public function getProductsByIdCustomer($customer_id){
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getMainTable())
            ->where('id_customer = ?', $customer_id);
        return $conn->fetchAll($select);
    }

}

