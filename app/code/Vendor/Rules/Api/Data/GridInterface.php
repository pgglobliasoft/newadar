<?php

namespace Vendor\Rules\Api\Data;

interface GridInterface
{
    const ENTITY_ID = 'entity_id';
    const SKU = 'sku';
    const NAME = 'name';
    const IS_ACTIVE = 'is_active';
    const SORT_ORDER = 'sort_order';

    public function getEntityId();

    public function setEntityId($entityId);

    public function getSku();

    public function setSku($sku);

    public function getName();

    public function setName($name);

    public function getIsActive();

    public function setIsActive($isActive);

    public function getSortOrder();

    public function setSortOrder($sortOrder);
}
