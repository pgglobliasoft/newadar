<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Collectionsilder\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$installer = $setup;

		$installer->startSetup();

		$table = $installer->getConnection()
			->newTable($installer->getTable('collection_silder'))
			->addColumn(
				'entity_id',
				Table::TYPE_INTEGER,
				null,
				['identity' => true, 'nullable' => false, 'primary' => true],
				'ID'
			)
			->addColumn(
				'name',
				Table::TYPE_TEXT,
				null,
				['identity' => false, 'default' => 'false'],
				'collection name'
			)
			->addColumn(
				'image',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'collection image'
			)
			->addColumn(
				'product_collection',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'product collection'
			)
			->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Form Status'
            )
			->addColumn(
				'orders',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'Orders'
			)
			->addColumn(
				'allow_all_customer',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'Allow All Customer'
			)
			->addColumn(
				'allow_customer',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'Allow Customer'
			)->addColumn(
				'categories',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'categories'
			)
			->setComment('new order page collection silder');
		$installer->getConnection()->createTable($table);

		$installer->endSetup();
	}
}
