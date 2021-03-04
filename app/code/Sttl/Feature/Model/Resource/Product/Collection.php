<?php

namespace Sttl\Feature\Model\Resource\Product;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Sttl\Feature\Model\Product', 'Sttl\Feature\Model\Resource\Product');
    }
}
