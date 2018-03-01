<?php

namespace Syrus\Bidrequest\Controller\Bidrequest;
use \Zend_Debug;
/**
 *
 */
class Form extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;
    protected $_messageManager;

    public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\Message\Manager $messageManager,
      \Magento\Framework\View\Result\PageFactory $pageFactory
      ) {
      $this->_pageFactory = $pageFactory;
      $this->_messageManager = $messageManager;
      return parent::__construct($context);
    }

    public function execute() {

      if($_SERVER['REQUEST_METHOD'] == "POST") {
        //recupero il link e il prezzo
        if(isset($_POST['link'])) {
          $link = $_POST['link'];
        }
        if(isset($_POST['price'])) {
          $price = $_POST['price'];
        }
        if(isset($_POST['name'])) {
          $name = $_POST['name'];
        }
        if(isset($_POST['surname'])) {
          $surname = $_POST['surname'];
        }
        if(isset($_POST['store'])) {
          $store = $_POST['store'];
        }
        //setto l'array da inviare nella richiesta
        $data['price'] = $price;
        $data['link'] = $link;
        $data['name'] = $name;
        $data['surname'] = $surname;
        $data['store'] = $store;
        //invio una richiesta alle API REST di WordPress personalizzate
        $ch = curl_init( "http://maddaai.it/wp-json/syrus-buy-group/v1/notification");
        // $ch = curl_init( "http://wordpressprova.local/wp-json/syrus-buy-group/v1/notification");
        //aggiungo i campi alla richiesta POST
        //setto il tipo della richiesta
        curl_setopt($ch, CURLOPT_POST, true);
        //setto il tipo del contenuto della richiesta
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($data))));
        //aggiungo il body della richiesta
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        //setto l'attesa della risposta
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //invio la richiesta
        $ret = curl_exec($ch);
        if($ret == false) {
          echo "Errore nell'invio del form";
        }
        //recupero eventuale risposta
        $ret = json_decode($ret);

        //controllo i campi della risposta
        if(isset($ret->status)) {
          //controllo lo status della risposta
          if($ret->status == 1) {
		$this->_messageManager->addSuccess("Richiesta inviata con successo");
          }
          else {
          	$this->_messageManager->adError("Invio della richiesta fallito");
          }
        }
        else {
          echo "Errore nel server di destinazione";
        }
      }
      return $this->_pageFactory->create();
    }
}
