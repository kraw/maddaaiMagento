<?php

/**
 * Copyright 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Syrus\Passwordrecover\Api;


interface ResetpasswordInterface
{
    /**
    * reset customer password
    * @param string $email
    * @param string $password 
    * @return null
    */
    public function reset($email, $password);
}
