<?php

namespace Sttl\Feature\Model\Source;

class Template implements \Magento\Framework\Option\ArrayInterface
{
    protected $featureModel;

    public function __construct(\Sttl\Feature\Model\Feature $featureModel)
    {
        $this->featureModel = $featureModel;
    }

    public function toOptionArray()
    {
        $options = array(
            array(
                'label' => __('Default Template'),
                'value' => 'widget/default.phtml'
            )
        );
        return $options;
    }
}