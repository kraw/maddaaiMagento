<?php

namespace Drc\PreOrder\Controller\Valutationproducts;
use \Zend_Debug;
/**
 *
 */
class Form extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;
    public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $pageFactory
      ) {
      $this->_pageFactory = $pageFactory;
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
	$data = json_encode($data);
        //invio una richiesta alle API REST di WordPress personalizzate
        $ch = curl_init( "http://roberto:alessandro@maddaai.it/wp-json/syrus-buy-group/v1/notification");
        //$ch = curl_init( "http://wordpressprova.local/wp-json/syrus-buy-group/v1/notification");
        //aggiungo i campi alla richiesta POST
        //setto il tipo della richiesta
        curl_setopt($ch, CURLOPT_POST, true);
        //setto il tipo del contenuto della richiesta
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($data))));
        //aggiungo il body della richiesta
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //setto l'attesa della risposta
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //invio la richiesta
        $ret = curl_exec($ch);
        if($ret == false) {
	  $this->messageManager->addError("Errore nell'invio del form");
          echo "Errore nell'invio del form";
        }
        //recupero eventuale risposta
        $ret = json_decode($ret);


        //controllo i campi della risposta
        if(isset($ret->status)) {
          //controllo lo status della risposta
          if($ret->status == 1) {
	    $this->messageManager->addSuccess("Richiesta Inviata");
          }
          else {
	    $this->messageManager->addError("Salvataggio della richiesta fallito");
            echo "Salvataggio della richiesta fallito";
          }
        }
        else {
          $this->messageManager->addError("Errore nel server di destinazione");
          echo "Errore nel server di destinazione";
        }
      }
      return $this->_pageFactory->create();
    }
}
