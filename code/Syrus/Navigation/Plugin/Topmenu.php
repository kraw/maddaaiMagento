<?php

namespace Syrus\Navigation\Plugin;
use  Magento\Framework\App\ObjectManager;

class Topmenu
{


    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $objectManager = ObjectManager::getInstance();
        $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory')->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    public function isUserLogged(){
        $objectManager = ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Checkout\Helper\Cart')->getCart()->getCustomerSession();
        return $customerSession->isLoggedIn();
    }

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html){

        $html .= "<li class=\"level0 nav-6 level-top\">";
        $html .= "<a href=\"/\" class=\"level-top\" >" .
            "<span>" . __("Home") . "</span>" .
            "</a>";
        $html .= "</li>";
        $html .= "<li class=\"level0 nav-6 level-top\">";
        $html .= "<a  href=\"/categorie\" class=\"level-top\" >" .
            "<span  title=\"Elenco categorie\">" . __("Categorie") . "</span>" .
            "</a>";
        $html .= "</li>";
        $html .= "<li class=\"level0 nav-6 level-top\">";
        $html .= "<a href=\"/in-scadenza\" class=\"level-top\" >" .
            "<span  title=\"Gruppo di acquisti\">" . __("GDA in scadenza") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 level-top\">";
        $html .= "<a href=\"/conclusi\" class=\"level-top\" >" .
            "<span  title=\"Gruppo di acquisti\">" . __("GDA conclusi") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 level-top\">";
        $html .= "<a href=\"" . "/bidrequest/bidrequest/form" . "\" class=\"level-top\" >" .
            "<span  title=\"Gruppo di acquisti\">" . __("Crea il tuo GDA") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 level-top\"> ";
        $html .= "<a href=\"/valutation-products\" class=\"level-top\" >" .
            "<span  title=\"Gruppo di acquisti\">" . __("GDA proposte") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 level-top\" style=\"float:right\">" .
            "<a class=\"level-top\" href=\"/customer/account/\"><span>" . __("Il mio account") . "</span>" .
            "</a>";
        $html .= "<ul class=\"level1 submenu\" >";
        $html .= "<li class=\"level2 nav-3-1-2\"><a href=\"/index.php/preorder/gda/index/\">" . __("I miei GDA") . "</a></li>";
        $html .= "<li class=\"level2 nav-3-1-2\"><a href=\"/customer/account/logout/\">" . __("Esci") . "</a></li>";
        $html .= "</ul>";
        $html .= "</li>";


       // if($this->isUserLogged()) {

        /*  }
          else {
              $html .= "<li class=\"level0 nav-6 level-top\" style=\"float:right\">";
              $html .= "<a href=\"//www.maddaai.it/login/\" class=\"level-top\" >" .
                  "<span  title=\"Effettua il login\">" . __("Effettua il login") . "</span>" .
                  "</a>";
              $html .= "</li>";
          }*/

        return $html;
    }
}
