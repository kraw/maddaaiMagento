<?php
/**
 * Created by PhpStorm.
 * User: georgeson
 * Date: 01/02/17
 * Time: 15:53
 */

namespace Drc\PreOrder\Block\Adminhtml\Grid;


class Severity implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        //return \Drc\PreOrder\Model\Ticket::getSeveritiesOptionArray();
        return ["TEST SEVERITY REnder"];
    }
}