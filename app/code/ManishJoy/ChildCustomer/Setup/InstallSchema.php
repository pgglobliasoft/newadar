<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ManishJoy\ChildCustomer\Setup;

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
			->newTable($installer->getTable('under_child_customer'))   
			->addColumn(
				'entity_id',
				Table::TYPE_INTEGER,
				null,
				['identity' => true, 'nullable' => false, 'primary' => true],
				'ID'
			)
			->addColumn(
				'parent_id',
				Table::TYPE_TEXT,
				null,
				['identity' => false, 'default' => 'false'],
				'parent customer id'
			)
			->addColumn(
				'permission',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'User Email'
			)
			->addColumn(
				'customercode',
				Table::TYPE_DECIMAL,
				'12,4',
				[],
				'Value'
			)
			->addColumn(
				'webscesscode',
				Table::TYPE_DECIMAL,
				'12,4',
				[],
				'Value'
			)
			->setComment('data for child customer deatils store');
		$installer->getConnection()->createTable($table);

		$installer->startSetup();

		$table = $installer->getConnection()
			->newTable($installer->getTable('admin_customer1'))
			->addColumn(
				'entity_id',
				Table::TYPE_INTEGER,
				null,
				['identity' => true, 'nullable' => false, 'primary' => true],
				'ID'
			)
			->addColumn(
				'email',
				Table::TYPE_TEXT,
				null,
				['identity' => false, 'default' => 'false'],
				'parent email'
			)
			->setComment('data for admin customer');
		$installer->getConnection()->createTable($table);


		$installer->endSetup();
	}
}
