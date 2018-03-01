<?php
namespace Drc\PreOrder\Block\Adminhtml;
 
class ValutationProductGrid extends \Magento\Backend\Block\Widget\Grid\Container
{
  protected function _construct()
  {
    $this->_controller = 'adminhtml_valutationproduct';
    $this->_blockGroup = 'Drc_PreOrder';
    $this->_headerText = __('Staffing Grid');
 
    parent::_construct();
 
  }
 
}
