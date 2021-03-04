<?php

namespace Sttl\Brand\Model\Resource;

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sttl_brand_product', 'entity_id');
    }
}
