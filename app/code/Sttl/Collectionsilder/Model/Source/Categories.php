<?php

namespace Sttl\Collectionsilder\Model\Source;

use Sttl\Collectionsilder\Model\PostFactory;

class Categories implements \Magento\Framework\Option\ArrayInterface
{	
	protected $request;
	protected $_postFactory;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        PostFactory $postFactory
    ) {
       $this->request = $request;
       $this->_postFactory = $postFactory;
    }
    public function toOptionArray()
    {
        $params = $this->request->getParam('id');
        $this->_options = [];
        if($params){
	    	$post = $this->_postFactory->create();
			$collections = $post->getCollection()->addFieldToSelect('name')->addFieldToFilter('entity_id', array('eq' => $params))->getData();
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();    
	        $helper = $objectManager->get('Sttl\Adaruniforms\Helper\Sap'); 
	        $getcollection =  $helper->getCategories($collections[0]['name']);

	            foreach ($getcollection as $manufacturer) {
	                $this->_options[] = [
	                        'label' => __($manufacturer['GroupName']),
	                        'value' => $manufacturer['GroupName'],
	                    ];
	            }
        }
        return $this->_options;
    }
}