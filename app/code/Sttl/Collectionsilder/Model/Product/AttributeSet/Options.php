<?php
namespace Sttl\Collectionsilder\Model\Product\AttributeSet;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();    
        $helper = $objectManager->get('Sttl\Adaruniforms\Helper\Sap');         
        $getcollection =  $helper->getcollection();

        if (!$this->_options) {
            foreach ($getcollection as $manufacturer) {
                $this->_options[] = [
                        'label' => __($manufacturer['Collection']),
                        'value' => $manufacturer['Collection'],
                    ];
            }
            
        }
      
        return $this->_options;
    }
}
