<?php

namespace Sttl\Feature\Block;

class Search extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_featureHelper;
    protected $_feature;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Sttl\Feature\Helper\Data $featureHelper,
        \Sttl\Feature\Model\Feature $feature,
        array $data = []
    )
    {
        $this->_feature = $feature;
        $this->_coreRegistry = $registry;
        $this->_featureHelper = $featureHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();
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

}