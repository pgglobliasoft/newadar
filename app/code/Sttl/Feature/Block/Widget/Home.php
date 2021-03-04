<?php

namespace Sttl\Feature\Block\Widget;

class Home extends \Magento\Framework\View\Element\Template
{
    protected $_feature;
    protected $_coreRegistry = null;
    protected $_featureHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Sttl\Feature\Model\Feature $feature,
        array $data = []
    )
    {
        $this->_feature = $feature;
        parent::__construct($context, $data);
    }

    public function getFeatureCollection()
    {
        $collection = $this->_feature->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
			->setPageSize($this->getLimit());
		
		if($this->getFeatureBy()=='featured'){
			$collection->addFieldToFilter('is_featured', 1);
		}
       
        $collection->setOrder('sort_order', 'ASC');
        return $collection;
    }
}