<?php
/**
 * Created by PhpStorm.
 * User: maintux
 * Date: 22/01/17
 * Time: 09:30
 */
namespace EasyNolo\BancaSellaPro\Model\Config\Source;

class Currency implements \Magento\Framework\Option\ArrayInterface {
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label'=>'USD Dollari Usa'],
            ['value' => 2, 'label'=>'GBP Sterlina Gran Bretagna'],
            ['value' => 3, 'label'=>'CHF Franco Svizzero'],
            ['value' => 7, 'label'=>'DKK Corone Danesi'],
            ['value' => 8, 'label'=>'NOK Corona Norvegese'],
            ['value' => 9, 'label'=>'SEK Corona Svedese'],
            ['value' => 12, 'label'=>'CAD Dollari Canadesi'],
            ['value' => 18, 'label'=>'ITL Lira Italiana'],
            ['value' => 71, 'label'=>'JPY Yen Giapponese'],
            ['value' => 103, 'label'=>'HKD Dollaro Hong Kong'],
            ['value' => 234, 'label'=>'BRL Real'],
            ['value' => 242, 'label'=>'EUR Euro']
        ];
    }
}