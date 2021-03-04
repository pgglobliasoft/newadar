<?php

namespace Sttl\Feature\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Resource;

class Feature extends \Magento\Catalog\Model\Layer
{
    public function getProductCollection()
    {
        $feature = $this->getCurrentFeature();
        if (isset($this->_productCollections[$feature->getId()])) {
            $collection = $this->_productCollections;
        } else {
            $collection = $feature->getProductCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections[$feature->getId()] = $collection;
        }
        return $collection;
    }

    public function getCurrentFeature()
    {
        $feature = $this->getData('current_feature');
        if ($feature === null) {
            $feature = $this->registry->registry('current_feature');
            if ($feature) {
                $this->setData('current_feature', $feature);
            }
        }
        return $feature;
    }
}