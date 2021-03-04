<?php

namespace Vendor\Rules\Model;

use Vendor\Rules\Api\Data\GridInterface;

class Grid extends \Magento\Framework\Model\AbstractModel implements GridInterface
{

    const CACHE_TAG = 'configurable_product';

    protected $_cacheTag = 'configurable_product';

    protected $_eventPrefix = 'configurable_product';

    protected function _construct()
    {
        $this->_init('Vendor\Rules\Model\ResourceModel\Grid');
    }

    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function setSKu($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }
}
