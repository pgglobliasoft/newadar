<?php
 
namespace Sttl\Adaruniforms\Setup;
 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
 
    /**
     * EAV setup factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;
 
    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }
 
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        
        /**
         * run this code if the module version stored in database is less than 1.0.1
         * i.e. the code is run while upgrading the module from version 1.0.0 to 1.0.1
         * 
         * you can write the version_compare function in the following way as well:
         * if(version_compare($context->getVersion(), '1.0.1', '<')) { 
         * 
         * the syntax is only different
         * output is the same
         */ 
        /**if(version_compare($context->getVersion(), '1.0.2', '<')) { 
 			$attributeCode = 'sap_name';
            $customerSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                $attributeCode // attribute code to remove
          	  );
       	 	}**/
        if (version_compare($context->getVersion(), '1.0.1') < 0) { 
 
            $attributeCode = 'sap_name';
 			
            $customerSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY, 
                $attributeCode, 
                [
                    'type' => 'varchar', // backend type
                    'label' => 'Sap Name',
                    'input' => 'text', // frontend input
                    'source' => '', // source model
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'sort_order' => 200,
                    'position' => 300,
                    'system' => false,
                    'is_used_in_grid'       => true,
    				'is_visible_in_grid'    => true
                ]
            ); 
 
            // show the attribute in the following forms
            $attribute = $customerSetup
                            ->getEavConfig()
                            ->getAttribute(
                                \Magento\Customer\Model\Customer::ENTITY, 
                                $attributeCode
                            )
                            ->addData(
                                ['used_in_forms' => [
                                    'adminhtml_customer',
                                ]
                            ]);
 
            $attribute->save();
        }
 
       

            
     


        
 
        
 
        $setup->endSetup();
    }
}