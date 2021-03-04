<?php
namespace Vsourz\Imagegallery\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
      
        if (version_compare($context->getVersion(), '1.0.2', '<=')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('vsourz_imagegallery_image'),
                'sort_order',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'sort_order'
                ]
            );
        }
        $installer->endSetup();
    }
}