<?php

namespace Sttl\Brand\Model\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    protected $brandModel;

    public function __construct(\Sttl\Brand\Model\Brand $brandModel)
    {
        $this->brandModel = $brandModel;
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