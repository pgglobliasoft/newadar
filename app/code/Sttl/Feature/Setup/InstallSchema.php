<?php

namespace Sttl\Feature\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()
                ->newTable($installer->getTable('sttl_feature'))
                ->addColumn(
                        'feature_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Feature Id'
                )
                ->addColumn(
                        'name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, ['nullable' => false], 'Name'
                )
                ->addColumn(
                        'url_key', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 250, ['nullable' => false], 'Url Key'
                )
                ->addColumn(
                        'small_image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['default' => null], 'Small Image'
                )
                ->addColumn(
                        'image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['default' => null], 'Image'
                )
                ->addColumn(
                        'description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['default' => null], 'Description'
                )
                ->addColumn(
                        'meta_keywords', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['default' => null], 'Meta Keywords'
                )
                ->addColumn(
                        'meta_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['default' => null], 'Meta Description'
                )
                ->addColumn(
                        'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status'
                )
                ->addColumn(
                        'is_featured', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Featured Feature'
                )
                ->addColumn(
                        'sort_order', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => '0'], 'Sort Order'
                )
                ->addColumn(
                        'option_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false, 'default' => '0'], 'Option Id'
                )
                ->setComment('Features');
        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()
                ->newTable($installer->getTable('sttl_feature_product'))
                ->addColumn(
                        'entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Entity Id'
                )
                ->addColumn(
                        'feature_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Feature Id'
                )
                ->addColumn(
                        'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Product Id'
                )
                ->addColumn(
                        'position', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Position'
                )
                ->setComment('Products of Feature');
        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()
                ->newTable($installer->getTable('sttl_feature_store'))
                ->addColumn(
                        'feature_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false, 'primary' => true], 'Feature Id'
                )
                ->addColumn(
                        'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false, 'primary' => true], 'Store Id'
                )
                ->addIndex(
                        $setup->getIdxName('sttl_feature_store', ['store_id']), ['store_id']
                )
                ->addForeignKey(
                        $setup->getFkName('sttl_feature_store', 'feature_id', 'sttl_feature', 'feature_id'), 'feature_id', $setup->getTable('sttl_feature'), 'feature_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                        $setup->getFkName('sttl_feature_store', 'store_id', 'store', 'store_id'), 'store_id', $setup->getTable('store'), 'store_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Features of Store');
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }

}
