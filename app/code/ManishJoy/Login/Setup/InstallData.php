<?php 
namespace ManishJoy\Login\Setup;

  
use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;


class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    
    private $eavSetupFactory;
    
    private $eavConfig;
    
    private $attributeResource;
    
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeResource = $attributeResource;
    }
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);
        // $eavSetup->removeAttribute(Customer::ENTITY, "admin_all_custom");
        // $eavSetup->removeAttribute(Customer::ENTITY, "account_id");       


        $eavSetup->addAttribute(Customer::ENTITY, 'allow_custom', [
            // Attribute parameters
            'type' => 'varchar',
            'label' => 'allow image Admin Account',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 3,
            'position' => 3,
            'system' => 0,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',         
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'note' => 'enable if give customer admin access'
        ]);
        
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'allow_custom');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);       

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($attribute);  

    }
} 


        
