<?php

namespace Sttl\Brand\Model\Resource\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Sttl\Brand\Model\Product', 'Sttl\Brand\Model\Resource\Product');
    }
}
