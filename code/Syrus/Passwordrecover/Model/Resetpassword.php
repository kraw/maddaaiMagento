<?php

/**
 * Copyright 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Syrus\Passwordrecover\Model;

use Syrus\Passwordrecover\Api\ResetpasswordInterface;


/**
 * Defines the implementaiton class of the calculator service contract.
 */
class Resetpassword implements ResetpasswordInterface
{
    public function reset($email, $password) {
	$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
	$resource = $object_manager->get('Magento\Framework\App\ResourceConnection');
	$connection = $resource->getConnection();
	$tableName = $resource->getTableName('customer_entity'); 
	$storeManager = $object_manager->get('\Magento\Store\Model\StoreManagerInterface');
	$website_id = $storeManager->getWebsite()->getWebsiteId();
	$customer_factory = $object_manager->get("\Magento\Customer\Model\CustomerFactory");
	$customer_data = $customer_factory->create();
	$customer_data->setWebsiteId($website_id);
	$customer_data->loadByEmail($email);
	$customer_id = $customer_data->getId();
	if($customer_id) {
		$sql = "UPDATE $tableName SET `password_hash` = CONCAT(SHA2('d6c5289983598574b309c27618d57687".$password."', 256), ':d6c5289983598574b309c27618d57687:1')  WHERE `entity_id` = $customer_id";
		$ret = $connection->query($sql);
		return json_encode(1);
	}
	else {
		return json_encode(0);
	}
    }
}
