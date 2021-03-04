<?php

namespace Sttl\Feature\Block\Feature;

use Magento\Customer\Model\Context as CustomerContext;

class Featured extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_featureHelper;
    protected $_feature;
    protected $httpContext;
    protected $_catalogProductVisibility;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sttl\Feature\Helper\Data $featureHelper,
        \Sttl\Feature\Model\Feature $feature,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        array $data = []
    )
    {
        $this->_feature = $feature;
        $this->_coreRegistry = $registry;
        $this->_featureHelper = $featureHelper;
        $this->httpContext = $httpContext;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();        
        $feature = $this->_feature;
        $featureCollection = $feature->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('is_featured', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('sort_order', 'ASC');
        $this->setCollection($featureCollection);
    }    

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    public function getConfig($key, $default = '')
    {
        $result = $this->_featureHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getProductCount(\Sttl\Feature\Model\Feature $feature)
    {
        $collection = $feature->getProductCollection();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        return count($collection);
    }

}
