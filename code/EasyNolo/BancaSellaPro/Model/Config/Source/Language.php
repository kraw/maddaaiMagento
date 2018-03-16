<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 22/01/17
 * Time: 09:30
 */
namespace EasyNolo\BancaSellaPro\Model\Config\Source;

class Language implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => '--NOT ENABLED--'],
            ['value' => 1, 'label' => 'Italian'],
            ['value' => 2, 'label' => 'English'],
            ['value' => 3, 'label' => 'Spanish'],
            ['value' => 4, 'label' => 'French'],
            ['value' => 5, 'label' => 'German'],
        ];
    }
}