<?php

namespace Sttl\Feature\Model\Source;

class Features implements \Magento\Framework\Option\ArrayInterface
{
    protected $featureModel;

    public function __construct(\Sttl\Feature\Model\Feature $featureModel)
    {
        $this->featureModel = $featureModel;
    }

    public function toOptionArray()
    {
        $options = [];
        $features = $this->featureModel->getCollection()
            ->addFieldToFilter('status', '1');
        foreach ($features as $feature) {
            $options[] = [
                'label' => $feature->getName(),
                'value' => $feature->getId(),
            ];
        }
        return $options;
    }
}