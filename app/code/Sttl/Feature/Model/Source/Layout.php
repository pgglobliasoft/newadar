<?php

namespace Sttl\Feature\Model\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
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
                'label' => __('Default'),
                'value' => 'default'
            ),
            array(
                'label' => __('Owl Carousel'),
                'value' => 'owl_carousel'
            )
        );
        return $options;
    }
}