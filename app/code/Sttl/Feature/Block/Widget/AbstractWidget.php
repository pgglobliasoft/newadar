<?php

namespace Sttl\Feature\Block\Widget;

class AbstractWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_featureHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Sttl\Feature\Helper\Data $featureHelper,
        array $data = []
    )
    {
        $this->_featureHelper = $featureHelper;
        parent::__construct($context, $data);
    }

    public function getConfig($key, $default = '')
    {
        if ($this->hasData($key)) {
            return $this->getData($key);
        }
        return $default;
    }
}