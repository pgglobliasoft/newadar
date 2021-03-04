<?php

namespace Sttl\Feature\Block\Feature\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            $feature = $this->_coreRegistry->registry('current_feature');
            if ($feature) {
                $layer->setCurrentFeature($feature);
            }
            $collection = $layer->getProductCollection();
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }
}