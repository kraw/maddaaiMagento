<?php

namespace Syrus\DateTime\Framework;

class Timezone extends \Magento\Framework\Stdlib\DateTime\Timezone {

    public function getDateFormat($type = \IntlDateFormatter::SHORT)
    {
        return (new \IntlDateFormatter(
            'en_US',
            $type,
            \IntlDateFormatter::NONE
        ))->getPattern();
    }
}
