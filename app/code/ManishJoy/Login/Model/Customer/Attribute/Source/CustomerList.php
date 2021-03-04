<?php
namespace ManishJoy\Login\Model\Customer\Attribute\Source;

class CustomerList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();    
        $helper = $objectManager->get('Sttl\Adaruniforms\Helper\Sap');         
        $Customer_data =  $helper->getCustomercode();

        if (!$this->_options) {
            foreach ($Customer_data as $manufacturer) {
                $this->_options[] = [
                        'label' => __($manufacturer['CardCode'].'-'.$manufacturer['CardName']),
                        'value' => $manufacturer['CardCode'],
                    ];
            }
            
        }
      
        return $this->_options;
    }
}
