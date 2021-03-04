<?php

namespace Globalia\Attribute\Setup;


use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->eavConfig       = $eavConfig;
	}

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$eavSetup->addAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'you_are_viewing',
			[
				'type'         => 'varchar',
				'label'        => 'You are viewing',
				'input'        => 'text',
				'source' 	   => '',
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
		$sampleAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'you_are_viewing');

		// more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
		$sampleAttribute->setData(
			'used_in_forms',
			['adminhtml_customer']

		);
		$sampleAttribute->save();

		


		
	}
}