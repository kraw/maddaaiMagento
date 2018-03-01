<?php
namespace Drc\PreOrder\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

	
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,'bid_target',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bid Target',
                'input' => 'text',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'sort_order' => 0,
                'group' => 'Informazioni Bid',
                'apply_to' => ''
            ]
        );

	//numero di pagamenti effettuati
	$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,'bid_payments',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bid Payments',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'sort_order' => 0,
                'group' => 'Informazioni Bid',
                'apply_to' => ''
            ]
        );

	$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,'bid_concluso',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bid Concluso',
                'input' => 'text',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'sort_order' => 0,
                'group' => 'Informazioni Bid',
                'apply_to' => ''
            ]
        );




        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'bid_start_date',
            [
                'type' => 'datetime',
                'backend' => '',
                'frontend' => '',
                'label' => 'Data Inizio Bid',
                'input' => 'date',
                'class' => '',
                'source' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Informazioni Bid',
                'apply_to' => ''
            ]
        );
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'bid_end_date',
            [
                'type' => 'datetime',
                'backend' => '',
                'frontend' => '',
                'label' => 'Data Fine Bid',
                'input' => 'date',
                'class' => '',
                'source' => '',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Informazioni Bid',
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,'preorder note',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Bid Note',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Informazioni Bid',
                'apply_to' => ''
            ]
        );

	$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,'valutation_product_likes',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Valutation Product Likes',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'sort_order' => 0,
                    'group' => 'Informazioni Valutation Product',
                    'apply_to' => 'valutation_product'
                ]
            );

		$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY,'valutation_product_thresold',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Valutation Product Thresold',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'sort_order' => 0,
                    'group' => 'Informazioni Valutation Product',
                    'apply_to' => 'valutation_product'
                ]
            );

    		$eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'valutation_product_end_date',
                [
                    'type' => 'datetime',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Data Fine Valutation Product',
                    'input' => 'date',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'group' => 'Informazioni Valutation Product',
                    'apply_to' => 'valutation_product'
                ]
            );



	//adding attributes for valutation_product product type
	$fieldList = [
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'minimal_price',
            'cost',
            'tier_price',
        ];

        // make these attributes applicable to downloadable products
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array('valutation_product', $applyTo)) {
                $applyTo[] = 'valutation_product';
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }



    		// Adding new product category for valutation product
    		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //retrieving product root categoty
        $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;

        //getting root category
        $parentCategory = $objectManager
                          ->create('Magento\Catalog\Model\Category')
                          ->load($parentId);
        //creating new category
        $category = $objectManager
                    ->create('Magento\Catalog\Model\Category');
        //Check exist category
        $cate = $category->getCollection()
                ->addAttributeToFilter('name','Valutation Products')
                ->getFirstItem();
        //if category doesn't exists
        if($cate->getId() == null) {
            //creating new category as son of root category
            $category->setPath($parentCategory->getPath())
                 ->setParentId($parentId)
                 ->setName('Valutation Products')
                 ->setIsActive(true);
            $category->save();
        }




         $installer = $setup;

    // Required tables
        $statusTable = $installer->getTable('sales_order_status');
        $statusStateTable = $installer->getTable('sales_order_status_state');

    // Insert statuses
        $installer->getConnection()->insertArray(
            $statusTable,
            ['status', 'label'],
            [
            [
                'status' => 'pre-order_processing',
                'label' => 'Bid Processing'
            ],
            [
                'status' => 'pre-order_pending',
                'label' => 'Bid Pending'
            ]
            ]
        );

    // Insert states and mapping of statuses to states
        $installer->getConnection()->insertArray(
            $statusStateTable,
            ['status', 'state', 'is_default', 'visible_on_front'],
            [
                [
                    'status' => 'pre-order_processing',
                    'state' => 'pre-order_processing',
                    'is_default' => 1,
                    'visible_on_front' => true
                ],
                [
                    'status' => 'pre-order_pending',
                    'state' => 'pre-order_pending',
                    'is_default' => 1,
                    'visible_on_front' => true
                ]
            ]
        );


    }
}
