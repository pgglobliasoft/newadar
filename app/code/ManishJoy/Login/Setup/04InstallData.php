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
        $eavSetup->removeAttribute(Customer::ENTITY, "admin_custom");
        $eavSetup->removeAttribute(Customer::ENTITY, "admin_all_custom");
        $eavSetup->removeAttribute(Customer::ENTITY, "account_id");       

        $eavSetup->addAttribute(Customer::ENTITY, 'admin_custom', [
            // Attribute parameters
            'type' => 'varchar',
            'label' => 'Admin Account',
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
        
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'admin_custom');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);       

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($attribute);
        

        $eavSetup->addAttribute(Customer::ENTITY, 'admin_all_custom', [
            // Attribute parameters
            'type' => 'varchar',
            'label' => 'all admin customer',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 3,
            'position' => 3,
            'system' => 0
        ]);
        
        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'admin_all_custom');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);       

        $attribute->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);

        $this->attributeResource->save($attribute);

        
         $attributeCode = 'account_id';
        $eavSetup->addAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'account_id', [
            'label' => 'Account Id',
            'required' => false,
            'user_defined' => 1,
            'system' => 0,
            'position' => 100,
            'input' => 'text'
        ]);

        $eavSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            $attributeCode);

        $amountId = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode);
        $amountId->setData('used_in_forms', [
            'adminhtml_customer',
            'customer_account_create',
            'customer_account_edit'
        ]);
        $amountId->getResource()->save($amountId);        

    }
} 


        
