<?php
namespace SR\CategoryImage\Model\Category\Attribute\Source;


    class Category extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource


    {
        protected $_options;

        public function getAllOptions()
        {   
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('DR\Gallery\Model\ImageFactory');
            // $collection = $resource->create()->getCollection()->addFieldToFilter('status', 1);
            $collection = $resource->create()->getCollection();

                if (!$this->_options) {
                     $this->_options[] =['label' => 'Select View Calalog',
                                'value' => ' ',];
                    foreach($collection as $value) {

                        if (filter_var($value['custom_url'], FILTER_VALIDATE_URL) !== false) {
                        $path = parse_url($value['custom_url'], PHP_URL_PATH);
                        $this->_options[] = [
                                'label' => __($value['custom_url']),
                                'value' => $value['custom_url'],
                            ];
                        }
                    }
                }
                return $this->_options;
            }
    }