<?php
namespace Drc\PreOrder\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        //installo la tabella per tenere conto dei like
        if (!$installer->tableExists('drc_preorder_relationship_like_customer_product')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('drc_preorder_relationship_like_customer_product')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'ID'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                  'nullable' => false,
                  'unsigned' => true
                ],
                'Customer ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                  'nullable' => false,
                  'unsigned' => true
                ],
                'Store ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                  'nullable' => false,
                  'unsigned' => true
                ],
                'Product ID'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Notification Created At'
            )
            ->setComment('Relationship Like Customer Product Table');
            $installer->getConnection()->createTable($table);

            // $installer->getConnection()->addIndex(
            //     $installer->getTable('drc_preorder_notification'),
            //     $setup->getIdxName(
            //         $installer->getTable('drc_preorder_notification'),
            //         ['link','price','deleted','read', 'created_at'],
            //         \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            //     ),
            //     ['link','price','deleted','read', 'created_at'],
            //     \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            // );
        }
        $installer->endSetup();
    }
  }

