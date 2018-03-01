<?php

namespace Syrus\Navigation\Plugin;

class Topmenu
{


    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
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

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {
        $categories = $this->getCategoryCollection(true, false, 'name', false);
        
        $html .= "<li class=\"level0 nav-6 last level-top\">";
        $html .= "<a href=\"" . "/" . "\" class=\"level-top\" >" .
            "<span>" . __("Home") . "</span>" .
            "</a>";
        $html .= "</li>";
        $html .= "<li class=\"level0 nav-6 last level-top\">";
        $html .= "<a    class=\"level-top\" " .
            "<span data-toggle=\"collapse\" data-target=\"#test\" >" . __("Categorie") . "</span>" .
            "</a>";

        $html .= "<ul class=\"level1 submenu\" >";
        foreach ($categories as $category) {
            if ($category->getIncludeInMenu())
                $html .= "<li class=\"level2 nav-3-1-2\"><a href=\"" . $category->getUrl() . "\" title=\"Show list of tickets\">" . $category->getName() . "</a></li>";
        }
        $html .= "</ul>";
        $html .= "</li>";
        $html .= "<li class=\"level0 nav-6 last level-top\">";
        $html .= "<a href=\"" . "/in-scadenza" . "\" class=\"level-top\" >" .
            "<span>" . __("GDA in scadenza") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 last level-top\">";
        $html .= "<a href=\"" . "/conclusi" . "\" class=\"level-top\" >" .
            "<span>" . __("GDA conclusi") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 last level-top\">";
        $html .= "<a href=\"" . "/bidrequest/bidrequest/form" . "\" class=\"level-top\" >" .
            "<span>" . __("Crea il tuo GDA") . "</span>" .
            "</a>";
        $html .= "</li>";

        $html .= "<li class=\"level0 nav-6 last level-top\"> ";
        $html .= "<a href=\"" . "/valutation-products" . "\" class=\"level-top\" >" .
            "<span>" . __("GDA proposte") . "</span>" .
            "</a>";
        $html .= "</li>";
        $html .= "<li class=\"level0 nav-6 last level-top\">";
        $html .= "<a href=\"" . "/bidrequest/bidrequest/form" . "\" class=\"level-top\" >" .
            "<span>" . __("GDA seguiti") . "</span>" .
            "</a>";
        $html .= "</li>";
      

        return $html;
    }
}
