<?php


namespace Sttl\Importproductbrand\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $table_sttl_importproductbrand_importproductbrand = $setup->getConnection()->newTable($setup->getTable('sttl_importproductbrand_importproductbrand'));

        $table_sttl_importproductbrand_importproductbrand->addColumn(
            'importproductbrand_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table_sttl_importproductbrand_importproductbrand->addColumn(
            'brand_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'brand_id'
        );

        $table_sttl_importproductbrand_importproductbrand->addColumn(
            'brand_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'brand_url'
        );

        $table_sttl_importproductbrand_importproductbrand->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'sku'
        );

        $table_sttl_importproductbrand_importproductbrand->addColumn(
            'ipmort_file',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'ipmort_file'
        );

        $setup->getConnection()->createTable($table_sttl_importproductbrand_importproductbrand);
    }
}
