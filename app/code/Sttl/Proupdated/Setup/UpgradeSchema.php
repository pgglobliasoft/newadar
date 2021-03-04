<?php

namespace Sttl\Proupdated\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

	/**
	 * {@inheritdoc}
	 */
	public function upgrade(
		SchemaSetupInterface $setup,
		ModuleContextInterface $context
	) {
		$installer = $setup;

		$installer->startSetup();

		$table = $installer->getConnection()->newTable(
			$installer->getTable('sttl_note_read')
		)
			->addColumn(
				'id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				[
					'identity' => true,
					'nullable' => false,
					'primary' => true,
					'unsigned' => true,
				],
				'ID'
			)
			->addColumn(
				'customer_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['nullable => false'],
				'ID'
			)
			->addColumn(
				'read_json',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable => false'],
				'notes read json'
			)
			->addColumn(
				'updated_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
				'Updated At')
			->setComment('Post Table');
		$installer->getConnection()->createTable($table);

		$installer->getConnection()->addIndex(
			$installer->getTable('sttl_import_note'),
			$setup->getIdxName(
				$installer->getTable('sttl_import_note'),
				['title', 'url_key', 'post_content'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			),
			['title', 'url_key', 'post_content'],
			\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
		);

		$installer->endSetup();


		// $setup = $setup;
		// $setup->startSetup();
		// $tableName = $setup->getTable('au_materail_product');
		// if ($setup->getConnection()->isTableExists($tableName) == true)
		// {
		// 	$setup->getConnection()->addColumn($tableName, 
		// 		'shortorder', [
	 //            		        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
		// 	                    'length' => 10,
		// 	                    'nullable' => true,
		// 	                    'comment' => 'short_order'
	 //            			]);

		// 	$setup->endSetup();			
		// } 
	}
}