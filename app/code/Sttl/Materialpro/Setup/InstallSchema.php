<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Materialpro\Setup;

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
			->newTable($installer->getTable('Materialpro_rules'))
			->addColumn(
				'id',
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
				'name'
			)
			->addColumn(
				'description',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'Description'
			)
			->addColumn(
				'from',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'From'
			)
			->addColumn(
                'to',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'To'
            )
			->addColumn(
				'priority',
				Table::TYPE_INTEGER,
				null,
				['nullable' => false, 'default' => 0],
				'Priority'
			)
			->addColumn(
				'frontend_description',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'Frontend_description'
			)
			->addColumn(
				'condition',
				Table::TYPE_TEXT,
				null,
				['nullable' => false, 'default' => ''],
				'condition'
			)->addColumn(
				'cart_total',
				Table::TYPE_INTEGER,
				null,
				['nullable' => false, 'default' => 0],
				'cart total'
			)->addColumn(
				'qty_total',
				Table::TYPE_INTEGER,
				null,
				['nullable' => false, 'default' => 0],
				'qty total'
			)
			->setComment('Material Product Rules');
		$installer->getConnection()->createTable($table);

		$installer->endSetup();
	}
}
