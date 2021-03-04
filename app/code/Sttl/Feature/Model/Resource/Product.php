<?php

namespace Sttl\Feature\Model\Resource;

class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sttl_feature_product', 'entity_id');
    }
}
