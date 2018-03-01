<?php
namespace Syrus\Stores\Block\Adminhtml\Product;

class Edit extends \Magento\Catalog\Block\Adminhtml\Product\Edit
{
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'select_all',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Seleziona tuttti i negozi'),
                'title' => __('Seleziona tuttti i negozi'),
                'onclick' => 'jQuery(\'.website-name [id^="product_website_"]\').attr("checked", "true");',
                'class' => 'action-secondary'
            ]
        );
        return parent::_prepareLayout();
    }
}
