<?php

namespace Sttl\Feature\Block\Widget;

class Feature extends AbstractWidget
{
    protected $_feature;
    protected $_coreRegistry = null;
    protected $_featureHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sttl\Feature\Helper\Data $featureHelper,
        \Sttl\Feature\Model\Feature $feature,
        array $data = []
    )
    {
        $this->_feature = $feature;
        $this->_featureHelper = $featureHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $featureHelper);
    }

    public function _toHtml()
    {
        if (!$this->_featureHelper->getConfig('general_settings/enabled')) return;
        $template = $this->getConfig('template');
        $this->setTemplate($template);
        return parent::_toHtml();
    }

    public function getFeatureCollection()
    {
        $featureIds = $this->getConfig('feature_ids');
        $collection = $this->_feature->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId());
        $featureIds = explode(',', $featureIds);
        if (is_array($featureIds)) {
            $collection->addFieldToFilter('feature_id', array('in' => $featureIds));
        }
        $collection->setOrder('sort_order', 'ASC');
        return $collection;
    }
}