<?php
namespace DR\Gallery\Setup;

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
        if (version_compare($context->getVersion(), "1.0.0", "<")) {
        //Your upgrade script
        }
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('dr_gallery_image'),
                'publish',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'publish'
                ]
            );
          $installer->getConnection()->addColumn(
                $installer->getTable('dr_gallery_image'),
                'custom_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'URL'
                ]
            );
        }
         if (version_compare($context->getVersion(), '1.0.2', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('dr_gallery_image'),
                'download',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'download'
                ]
            );
         
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('dr_gallery_image'),
                'download_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'download_url'
                ]
            );
         
        }
        $installer->endSetup();
    }
}