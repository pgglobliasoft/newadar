<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sttl\Childskus\Setup;

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
			->newTable($installer->getTable('childskus'))
			->addColumn(
				'entity_id',
				Table::TYPE_INTEGER,
				null,
				['identity' => true, 'nullable' => false, 'primary' => true],
				'ID'
			)
			->addColumn(
				'parantsku',
				Table::TYPE_TEXT,
				20,
				['nullable' => false],
				'parantsku'
			)
			->addColumn(
				'childsku1',
				Table::TYPE_TEXT,
				20,
				['nullable' => false],
				'childsku1'
			)
			->addColumn(
				'childsku2',
				Table::TYPE_TEXT,
				20,
				['nullable' => false],
				'childsku2'
			)
			
			->setComment('childsku');
		$installer->getConnection()->createTable($table);

		$installer->endSetup();
	}
}
