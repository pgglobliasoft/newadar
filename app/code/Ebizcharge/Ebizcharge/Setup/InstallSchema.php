<?php
/**
 * Create the 'token' table, add proper fields to the 'sales_order_payment' table,
 * and add proper fields to the 'quote_payment' table.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2016 Century Business Solutions  (www.centurybizsolutions.com)
 */
namespace Ebizcharge\Ebizcharge\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Create 'ebizcharge_token' table.
        $tableName = $installer->getTable('ebizcharge_token');
        $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'token_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'id'
                    )
                    ->addColumn(
                        'mage_cust_id',
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'nullable' => false
                        ],
                        'Magento Customer ID'
                    )
                    ->addColumn(
                        'ebzc_cust_id',
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'nullable' => false
                        ],
                        'Ebizcharge Customer ID/Token'
                    );


        $installer->getConnection()->createTable($table);
        
        // Add columns to 'sales_order_payment' table.
        $installer->getConnection()->addColumn($installer->getTable('sales_order_payment'),
            'ebzc_option',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 100,
                'nullable' => false,
                'comment' => 'Ebizcharge Payment Option'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_payment'),
            'ebzc_cust_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Ebizcharge Customer ID'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_payment'),
            'ebzc_method_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Ebizcharge Payment Method ID'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_payment'),
            'ebzc_avs_street',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'AVS Street'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_payment'),
            'ebzc_avs_zip',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'AVS Zip'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('sales_order_payment'),
            'ebzc_save_payment',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 1,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Ebizcharge - Save Payment Info'
            ]);

        // Add columns to 'quote_payment' table.
        $installer->getConnection()->addColumn($installer->getTable('quote_payment'),
            'ebzc_option',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 100,
                'nullable' => false,
                'comment' => 'Ebizcharge Payment Option'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('quote_payment'),
            'ebzc_cust_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Ebizcharge Customer ID'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('quote_payment'),
            'ebzc_method_id',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 11,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Ebizcharge Payment Method ID'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('quote_payment'),
            'ebzc_avs_street',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'AVS Street'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('quote_payment'),
            'ebzc_avs_zip',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'AVS Zip'
            ]);
        $installer->getConnection()->addColumn($installer->getTable('quote_payment'),
            'ebzc_save_payment',
            [
                'type' => Table::TYPE_INTEGER,
                'length' => 1,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Ebizcharge - Save Payment Info'
            ]);

        $installer->endSetup();
    }
}