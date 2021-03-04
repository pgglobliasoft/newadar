<?php
namespace Globalia\Attribute\Setup;

use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class UpgradeData implements UpgradeDataInterface
{
    protected $customerSetupFactory;

    private $eavConfig;

    private $eavSetupFactory;

    private $attributeSetFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

      // Set your module version
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'login_browser',
                [
                    'type'         => 'varchar',
                    'label'        => 'Login Browser',
                    'input'        => 'text',
                    'source'       => '',
                    'required'     => false,
                    'visible'      => true,
                    'user_defined' => false,
                    'sort_order'   => 200,
                    'position'     => 400,
                    'system'       => false,
                    'is_used_in_grid'       => true,
                    'is_visible_in_grid'    => true
                ]
            );   
        }

        $setup->endSetup();
    }
}